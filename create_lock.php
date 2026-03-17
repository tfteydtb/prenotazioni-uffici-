<?php
// actions/create_lock.php
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

if (!$resource_id || !$start_time) {
    echo json_encode(['success' => false, 'message' => 'Dati mancanti']);
    exit;
}

$bookingModel = new Booking($pdo);
$lock_id = $bookingModel->createLock($resource_id, $_SESSION['user_id'], $start_time);

if ($lock_id) {
    echo json_encode(['success' => true, 'lock_id' => $lock_id, 'message' => 'Lock creato con successo. Hai 2 minuti per confermare.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Risorsa già occupata o in fase di prenotazione.']);
}
?>