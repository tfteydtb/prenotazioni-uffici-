<?php
// pages/report.php
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'models/booking.php';
$bookingModel = new Booking($pdo);
$reportData = $bookingModel->getResourceUsageReport();

// Fetch time analysis (basic example: active bookings per hour of day)
$stmt = $pdo->query("
    SELECT HOUR(start_time) as hour, COUNT(*) as count 
    FROM bookings 
    WHERE status = 'attiva'
    GROUP BY HOUR(start_time)
    ORDER BY count DESC
");
$timeAnalysis = $stmt->fetchAll();

// General Stats
$totalStmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'attiva'");
$totalBookings = $totalStmt->fetchColumn();

// Waitlist stats
$waitlistStmt = $pdo->query("SELECT COUNT(*) FROM waitlist");
$totalWaitlist = $waitlistStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Utilizzo</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <li><a href="index.php?page=my_bookings">Le mie prenotazioni</a></li>
            <li><a href="index.php?page=report" class="active">Report</a></li>
            <li><a href="actions/logout.php" class="btn btn-secondary" style="padding: 0.5rem 1rem;">Esci</a></li>
        </ul>
    </nav>
</header>

<main class="container">
    <div class="page-header">
        <div>
            <h1>Report d'Utilizzo</h1>
            <p class="text-muted">Analisi dell'utilizzo delle risorse condivise.</p>
        </div>
    </div>

    <div class="grid grid-3 mb-2">
        <div class="card text-center" style="background: linear-gradient(135deg, var(--primary-color), #6db5ff); color: white;">
            <h2 style="color: white; font-size: 3rem; margin-bottom: 0;"><?php echo $totalBookings; ?></h2>
            <p style="opacity: 0.9;">Prenotazioni Totali Attive</p>
        </div>
        
        <div class="card text-center" style="background: linear-gradient(135deg, var(--secondary-color), #7df0d6); color: white;">
            <h2 style="color: white; font-size: 3rem; margin-bottom: 0;"><?php echo count($reportData); ?></h2>
            <p style="opacity: 0.9;">Risorse Gestite</p>
        </div>
        
        <div class="card text-center" style="background: linear-gradient(135deg, var(--accent-color), #ff8a6a); color: white;">
            <h2 style="color: white; font-size: 3rem; margin-bottom: 0;"><?php echo $totalWaitlist; ?></h2>
            <p style="opacity: 0.9;">Richieste in Waitlist</p>
        </div>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3 class="mb-1">Utilizzo per Risorsa</h3>
            <canvas id="resourceChart"></canvas>
        </div>
        
        <div class="card">
            <h3 class="mb-1">Fasce Orarie più Utilizzate</h3>
            <canvas id="timeChart"></canvas>
        </div>
    </div>
    
    <div class="card mt-2">
        <h3 class="mb-1">Dettaglio Risorse</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border-color);">
                    <th style="padding: 1rem 0;">Risorsa</th>
                    <th style="padding: 1rem 0;">Prenotazioni Effettuate</th>
                    <th style="padding: 1rem 0;">Rate Utilizzo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reportData as $row): 
                    $rate = $totalBookings > 0 ? round(($row['total_bookings'] / $totalBookings) * 100) : 0;
                ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem 0;"><strong><?php echo htmlspecialchars($row['nome']); ?></strong></td>
                    <td style="padding: 1rem 0;"><?php echo $row['total_bookings']; ?></td>
                    <td style="padding: 1rem 0;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex-grow: 1; background: #eee; height: 8px; border-radius: 4px;">
                                <div style="width: <?php echo $rate; ?>%; background: var(--primary-color); height: 100%; border-radius: 4px;"></div>
                            </div>
                            <span style="font-size: 0.85rem; font-weight: 600; min-width: 40px;"><?php echo $rate; ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
    // Prepare data for charts
    const resLabels = <?php echo json_encode(array_column($reportData, 'nome')); ?>;
    const resData = <?php echo json_encode(array_column($reportData, 'total_bookings')); ?>;
    
    const timeLabels = <?php echo json_encode(array_map(function($h) { return $h['hour'] . ":00"; }, $timeAnalysis)); ?>;
    const timeData = <?php echo json_encode(array_column($timeAnalysis, 'count')); ?>;

    // Resource Chart (Bar)
    new Chart(document.getElementById('resourceChart'), {
        type: 'bar',
        data: {
            labels: resLabels,
            datasets: [{
                label: 'Numero Prenotazioni',
                data: resData,
                backgroundColor: 'rgba(74, 144, 226, 0.7)',
                borderColor: '#4a90e2',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Time Chart (Line/Area)
    new Chart(document.getElementById('timeChart'), {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: 'Frequenza',
                data: timeData,
                backgroundColor: 'rgba(80, 227, 194, 0.2)',
                borderColor: '#50e3c2',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
</body>
</html>