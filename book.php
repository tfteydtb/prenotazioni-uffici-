<?php
session_start();
require_once "../config/database.php";

$user = $_SESSION['user_id'];

$resource = $_POST['resource'];
$start = $_POST['start'];
$end = $_POST['end'];

$stmt = $conn->prepare("INSERT INTO bookings(user_id,resource_id,start_time,end_time,status)
VALUES (?,?,?,?, 'attiva')");

$stmt->bind_param("iiss",$user,$resource,$start,$end);
$stmt->execute();

echo "Prenotazione confermata";

?>