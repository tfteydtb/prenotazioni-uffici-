<?php
// actions/cancel_booking.php
session_start();
require_once '../config/database.php';
require_once '../models/booking.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autenticato']);
    exit;
}

$booking_id = $_POST['booking_id'] ?? null;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'ID prenotazione mancante']);
    exit;
}

$bookingModel = new Booking($pdo);
$success = $bookingModel->cancelBooking($booking_id, $_SESSION['user_id']);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Prenotazione cancellata con successo.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Impossibile cancellare la prenotazione.']);
}
?>