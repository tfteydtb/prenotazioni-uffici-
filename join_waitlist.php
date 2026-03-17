<?php
// actions/join_waitlist.php
session_start();
require_once '../config/database.php';
require_once '../models/booking.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non autenticato']);
    exit;
}

$resource_id = $_POST['resource_id'] ?? null;

if (!$resource_id) {
    echo json_encode(['success' => false, 'message' => 'Risorsa mancante']);
    exit;
}

$bookingModel = new Booking($pdo);
$success = $bookingModel->joinWaitlist($_SESSION['user_id'], $resource_id);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Aggiunto alla lista di attesa con successo.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Sei già in lista di attesa per questa risorsa.']);
}
?>