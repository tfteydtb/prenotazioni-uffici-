<?php
// models/booking.php

class Booking {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- LOCK LOGIC ---
    
    // Check if slot overlaps with existing bookings OR active locks
    public function isSlotAvailable($resource_id, $start_time, $end_time) {
        // Clean up expired locks first
        $this->cleanupExpiredLocks();
        
        $sql = "
            SELECT 1 FROM bookings 
            WHERE resource_id = ? 
              AND status IN ('attiva')
              AND ((start_time < ? AND end_time > ?) OR (start_time >= ? AND start_time < ?))
              
            UNION
            
            SELECT 1 FROM locks
            WHERE resource_id = ?
              AND expiration_time > NOW()
              AND ((start_time < ? AND DATE_ADD(start_time, INTERVAL TIMESTAMPDIFF(MINUTE, start_time, expiration_time) MINUTE) > ?) 
                   OR (start_time >= ? AND start_time < ?))
        ";
        
        // This is a simplified check assuming exactly matched slots for now
        $sql_simple = "
            SELECT 1 FROM bookings 
            WHERE resource_id = ? 
              AND status = 'attiva'
              AND (
                  (start_time < ? AND end_time > ?) 
              )
            UNION
            SELECT 1 FROM locks
            WHERE resource_id = ?
              AND expiration_time > NOW()
              AND (
                  (start_time <= ? AND DATE_ADD(start_time, INTERVAL 60 MINUTE) > ?)
              )
        ";
        
        // Let's use a robust overlap check
        // A overlaps B if (StartA < EndB) and (EndA > StartB)
        $q = "
            SELECT 1 FROM bookings
            WHERE resource_id = :resource_id
              AND status = 'attiva'
              AND start_time < :end_time
              AND end_time > :start_time
            UNION
            SELECT 1 FROM locks
            WHERE resource_id = :resource_id_lock
              AND expiration_time > NOW()
              AND start_time < :end_time_lock
              AND DATE_ADD(start_time, INTERVAL 60 MINUTE) > :start_time_lock
        ";
        // Assuming slots are exactly 60 minutes for locks if we don't store end_time in locks
        // Actually, schema.sql doesn't have lock end_time. Let's alter our query to handle this.
        // Wait, schema has: resource_id, user_id, start_time, expiration_time.
        // We will assume a lock covers the slot starting at start_time for a specific duration (e.g. 1 hour).
        
        $stmt = $this->pdo->prepare("
            SELECT 1 FROM bookings
            WHERE resource_id = ?
              AND status = 'attiva'
              AND (start_time < ? AND end_time > ?)
            UNION
            SELECT 1 FROM locks
            WHERE resource_id = ?
              AND expiration_time > NOW()
              AND start_time = ? 
        ");
        
        $stmt->execute([
            $resource_id, $end_time, $start_time,
            $resource_id, $start_time
        ]);
        
        return $stmt->rowCount() === 0;
    }

    public function createLock($resource_id, $user_id, $start_time) {
        $this->cleanupExpiredLocks();
        
        // Standard check
        if (!$this->isSlotAvailable($resource_id, $start_time, date('Y-m-d H:i:s', strtotime($start_time . ' +1 hour')))) {
            return false;
        }

        // Lock for 2 minutes
        $expiration = date('Y-m-d H:i:s', strtotime('+2 minutes'));
        
        $stmt = $this->pdo->prepare("INSERT INTO locks (resource_id, user_id, start_time, expiration_time) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$resource_id, $user_id, $start_time, $expiration])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    public function cleanupExpiredLocks() {
        $this->pdo->exec("DELETE FROM locks WHERE expiration_time <= NOW()");
    }

    // --- BOOKING LOGIC ---

    public function createBooking($user_id, $resource_id, $start_time, $end_time, $lock_id = null) {
        try {
            $this->pdo->beginTransaction();

            // If a lock was provided, verify it belongs to user and is valid
            if ($lock_id) {
                $stmt = $this->pdo->prepare("SELECT id FROM locks WHERE id = ? AND user_id = ? AND expiration_time > NOW()");
                $stmt->execute([$lock_id, $user_id]);
                if (!$stmt->fetch()) {
                    throw new Exception("Lock expired or invalid.");
                }
                // Delete the valid lock since we're converting it
                $del = $this->pdo->prepare("DELETE FROM locks WHERE id = ?");
                $del->execute([$lock_id]);
            } else {
                // Direct booking without lock - Double check availability
                if (!$this->isSlotAvailable($resource_id, $start_time, $end_time)) {
                    throw new Exception("Slot not available.");
                }
            }

            $stmt = $this->pdo->prepare("INSERT INTO bookings (user_id, resource_id, start_time, end_time, status) VALUES (?, ?, ?, ?, 'attiva')");
            $stmt->execute([$user_id, $resource_id, $start_time, $end_time]);
            $booking_id = $this->pdo->lastInsertId();

            $this->pdo->commit();
            return $booking_id;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getUserBookings($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT b.*, r.nome as resource_name, r.tipo as resource_type 
            FROM bookings b
            JOIN resources r ON b.resource_id = r.id
            WHERE b.user_id = ?
            ORDER BY b.start_time DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public function cancelBooking($booking_id, $user_id) {
        $stmt = $this->pdo->prepare("UPDATE bookings SET status = 'cancellata' WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$booking_id, $user_id]);
        
        if ($result && $stmt->rowCount() > 0) {
            // Check waitlist
            $this->notifyWaitlist($booking_id);
            return true;
        }
        return false;
    }

    // --- WAITLIST LOGIC ---
    
    public function joinWaitlist($user_id, $resource_id) {
        // Prevent duplicate entries
        $stmt = $this->pdo->prepare("SELECT id FROM waitlist WHERE user_id = ? AND resource_id = ?");
        $stmt->execute([$user_id, $resource_id]);
        if ($stmt->fetch()) {
            return false; // Already in waitlist
        }

        $stmt = $this->pdo->prepare("INSERT INTO waitlist (resource_id, user_id, request_time) VALUES (?, ?, NOW())");
        return $stmt->execute([$resource_id, $user_id]);
    }

    private function notifyWaitlist($cancelled_booking_id) {
        // Find resource id of cancelled booking
        $stmt = $this->pdo->prepare("SELECT resource_id FROM bookings WHERE id = ?");
        $stmt->execute([$cancelled_booking_id]);
        $booking = $stmt->fetch();
        
        if ($booking) {
            // Get first person on waitlist
            $stmt = $this->pdo->prepare("
                SELECT w.id, w.user_id, u.email 
                FROM waitlist w
                JOIN users u ON w.user_id = u.id
                WHERE w.resource_id = ?
                ORDER BY w.request_time ASC
                LIMIT 1
            ");
            $stmt->execute([$booking['resource_id']]);
            $waitlister = $stmt->fetch();

            if ($waitlister) {
                // In a real app, send email. Here we just log or queue it.
                // Remove from waitlist
                $del = $this->pdo->prepare("DELETE FROM waitlist WHERE id = ?");
                $del->execute([$waitlister['id']]);
            }
        }
    }

    // --- REPORTING LOGIC ---
    
    public function getResourceUsageReport() {
        $stmt = $this->pdo->query("
            SELECT r.id, r.nome, COUNT(b.id) as total_bookings
            FROM resources r
            LEFT JOIN bookings b ON r.id = b.resource_id AND b.status = 'attiva'
            GROUP BY r.id, r.nome
            ORDER BY total_bookings DESC
        ");
        return $stmt->fetchAll();
    }
}
?>