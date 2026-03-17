<?php
// api/get_bookings.php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$start_of_day = $date . ' 00:00:00';
$end_of_day = $date . ' 23:59:59';
$user_id = $_SESSION['user_id'];

// Get all active bookings for this day
$stmt_b = $pdo->prepare("
    SELECT id, user_id, resource_id, start_time, end_time, 'booking' as type
    FROM bookings 
    WHERE status = 'attiva' 
      AND start_time >= ? 
      AND start_time <= ?
");
$stmt_b->execute([$start_of_day, $end_of_day]);
$bookings = $stmt_b->fetchAll();

// Get active locks for this day
$stmt_l = $pdo->prepare("
    SELECT id, user_id, resource_id, start_time, DATE_ADD(start_time, INTERVAL 60 MINUTE) as end_time, 'lock' as type
    FROM locks
    WHERE expiration_time > NOW()
      AND start_time >= ?
      AND start_time <= ?
");
$stmt_l->execute([$start_of_day, $end_of_day]);
$locks = $stmt_l->fetchAll();

$merged = array_merge($bookings, $locks);

// Add is_mine flag for frontend
$result = array_map(function($item) use ($user_id) {
    $item['is_mine'] = ($item['user_id'] == $user_id);
    return $item;
}, $merged);

echo json_encode($result);
?>
