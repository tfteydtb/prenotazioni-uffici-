<?php
// pages/my_bookings.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'models/booking.php';
$bookingModel = new Booking($pdo);
$bookings = $bookingModel->getUserBookings($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le mie Prenotazioni</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        BookSystem
    </div>
    <nav>
        <ul>
            <li><a href="index.php?page=calendar">Calendario</a></li>
            <li><a href="index.php?page=my_bookings" class="active">Le mie prenotazioni</a></li>
            <li><a href="index.php?page=report">Report</a></li>
            <li><a href="actions/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Esci</a></li>
        </ul>
    </nav>
</header>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Le mie Prenotazioni</h1>
            <p class="text-muted">Gestisci i tuoi slot prenotati.</p>
        </div>
    </div>

    <div class="grid grid-2">
        <?php foreach ($bookings as $b): ?>
            <?php 
                $isFuture = strtotime($b['start_time']) > time();
                $isActive = $b['status'] === 'attiva';
            ?>
            <div class="card" style="border-left: 4px solid <?php echo $isActive ? 'var(--primary-color)' : 'var(--border-color)'; ?>">
                <div class="d-flex justify-between mb-1">
                    <h3 style="margin-bottom:0"><?php echo htmlspecialchars($b['resource_name']); ?></h3>
                    <span class="badge badge-<?php echo strtolower($b['status']); ?>">
                        <?php echo ucfirst(htmlspecialchars($b['status'])); ?>
                    </span>
                </div>
                
                <p class="text-muted mb-1" style="font-size: 0.9rem;">
                    Tipo: <?php echo ucfirst(htmlspecialchars($b['resource_type'])); ?>
                </p>
                
                <div style="background: #f8f9fa; padding: 0.75rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
                    <strong>Data:</strong> <?php echo date('d/m/Y', strtotime($b['start_time'])); ?><br>
                    <strong>Orario:</strong> <?php echo date('H:i', strtotime($b['start_time'])); ?> - <?php echo date('H:i', strtotime($b['end_time'])); ?>
                </div>

                <?php if ($isActive && $isFuture): ?>
                    <div class="text-center mt-1">
                        <button class="btn btn-danger" style="width:100%" onclick="BookingFlow.cancelBooking(<?php echo $b['id']; ?>)">
                            Cancella Prenotazione
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($bookings)): ?>
            <div class="card text-center" style="grid-column: 1 / -1; padding: 3rem;">
                <p class="text-muted">Non hai ancora effettuato prenotazioni.</p>
                <a href="index.php?page=calendar" class="btn btn-primary mt-1">Vai al Calendario</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="toast-container" class="toast-container"></div>
<script src="js/calendar.js"></script>
<script src="js/booking.js"></script>
</body>
</html>