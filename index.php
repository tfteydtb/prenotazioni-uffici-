<?php
session_start();
$_SESSION['user_id']=1;
?>

<!DOCTYPE html>
<html>
<head>
<title>Resource Booking</title>
<link rel="stylesheet" href="../css/style.css">
</head>

<body>

<h1>Resource Booking System</h1>

<a href="calendar.php">Calendario Prenotazioni</a>
<br><br>

<a href="my_bookings.php">Le mie prenotazioni</a>
<br><br>

<a href="report.php">Report utilizzo</a>

</body>
</html>