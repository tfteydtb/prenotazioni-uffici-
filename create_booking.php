<?php
// actions/create_booking.php
session_start();
require_once '../config/database.php';
require_once '../models/booking.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autenticato']);
    exit;
}

$resource_id = $_POST['resource_id'] ?? null;
$start_time = $_POST['start_time'] ?? null;
$end_time = $_POST['end_time'] ?? null;
$lock_id = $_POST['lock_id'] ?? null;

if (!$resource_id || !$start_time || !$end_time) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$bookingModel = new Booking($pdo);
$booking_id = $bookingModel->createBooking($_SESSION['user_id'], $resource_id, $start_time, $end_time, $lock_id);

if ($booking_id) {
    echo json_encode(['success' => true, 'booking_id' => $booking_id, 'message' => 'Prenotazione confermata con successo!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore: slot non più disponibile o lock scaduto.']);
}
?>
