<?php
// pages/calendar.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'models/resource.php';
$resourceModel = new Resource($pdo);
$resources = $resourceModel->getAllResources();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Prenotazioni</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <div class="logo">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        BookSystem
    </div>
    <nav>
        <ul>
            <li><a href="index.php?page=calendar" class="active">Calendario</a></li>
            <li><a href="index.php?page=my_bookings">Le mie prenotazioni</a></li>
            <li><a href="index.php?page=report">Report</a></li>
            <li><a href="actions/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Esci</a></li>
        </ul>
    </nav>
</header>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Prenota Risorse</h1>
            <p class="text-muted">Seleziona uno slot libero per effettuare una prenotazione.</p>
        </div>
        <div class="d-flex gap-1 align-center">
            <button id="prev-day" class="btn btn-secondary">&lt; Giorno pre.</button>
            <button id="today" class="btn btn-primary">Oggi</button>
            <button id="next-day" class="btn btn-secondary">Giorno suc. &gt;</button>
        </div>
    </div>

    <div class="calendar-wrapper">
        <div class="calendar-header">
            <h2 id="current-date-display">Caricamento...</h2>
            <div>
                <span class="badge badge-attiva" style="margin-right: 10px;">Libero</span>
                <span class="badge" style="background: rgba(74, 144, 226, 0.1); border: 1px solid var(--primary-color); color: var(--primary-color); margin-right: 10px;">Prenotato</span>
                <span class="badge badge-attesa">In approvazione</span>
            </div>
        </div>
        
        <div id="calendar-grid" class="calendar-grid">
            <!-- Rendered by JS -->
        </div>
    </div>
</main>

<!-- Booking Modal -->
<div class="modal-overlay" id="booking-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Conferma Prenotazione</h3>
            <button class="modal-close" id="modal-close">&times;</button>
        </div>
        
        <div class="alert alert-info" style="font-size: 0.9rem;">
            Risorsa bloccata temporaneamente! Hai 2 minuti per confermare.
        </div>
        
        <div class="form-group">
            <label>Risorsa</label>
            <div id="modal-resource-name" style="font-weight: 600; font-size: 1.1rem;"></div>
        </div>
        
        <div class="form-group">
            <label>Orario</label>
            <div id="modal-time" style="font-weight: 600;"></div>
        </div>
        
        <div class="lock-timer-container">
            <div class="lock-timer-bar" id="lock-timer-bar"></div>
        </div>
        <div id="lock-timer-text" style="text-align: right; font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;"></div>
        
        <div class="d-flex gap-1 mt-2 justify-between">
            <button class="btn btn-secondary" id="btn-cancel-modal">Annulla</button>
            <button class="btn btn-primary" id="btn-confirm-booking">Conferma Prenotazione</button>
        </div>
    </div>
</div>

<div id="toast-container" class="toast-container"></div>

<script>
    // Pass PHP data to JS
    const resources = <?php echo json_encode($resources); ?>;
</script>
<script src="js/calendar.js"></script>
<script src="js/booking.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        Calendar.init(resources);
    });
</script>

</body>
</html>