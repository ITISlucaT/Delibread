<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Ordini DeliBread";

$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

$stmt = $conn->prepare("SELECT *
FROM ordine o inner join ordine_panetteria op On o.IdOrdine = op.IdOrdine
        inner join panetteria p ON op.IdPanetteria = p.IdPanetteria 
        INNER JOIN utente u ON o.IdUtente = u.IdUtente
WHERE o.Stato not like 'consegnato' and p.IdPanetteria = ? ");

$stmt->bind_param("i", $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcola statistiche per le card
$totalOrders = count($orders);
$pendingOrders = 0;
$processingOrders = 0;
$readyOrders = 0;

foreach ($orders as $order) {
    switch(strtolower($order['Stato'])) {
        case 'in attesa':
            $pendingOrders++;
            break;
        case 'confermato':
        case 'in preparazione':
            $processingOrders++;
            break;
        case 'pronto':
            $readyOrders++;
            break;
    }
}

// Genera calendario (esempio base per le settimane del mese corrente)
$year = date('Y');
$month = date('m');
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDay = date('w', $firstDay);
$weeks = [];
$week = array_fill(0, 7, '');

for ($day = 1; $day <= $daysInMonth; $day++) {
    $weekDay = ($day + $startDay - 2) % 7;
    if ($weekDay < 0) $weekDay = 6;
    
    $week[$weekDay] = $day;
    
    if ($weekDay == 6 || $day == $daysInMonth) {
        $weeks[] = $week;
        $week = array_fill(0, 7, '');
    }
}

$deliveredOrders = []; // Placeholder per ordini consegnati
print_r($order)
?>

<style>
.bakery-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bakery-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
}

.content-header {
    background: linear-gradient(135deg, #d4a574 0%, #8b4513 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(212, 165, 116, 0.3);
}

.content-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.content-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.order-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.order-header {
    background: linear-gradient(135deg, #d4a574 0%, #cd853f 100%);
    color: white;
    padding: 1.5rem;
}

.calendar-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: all 0.3s ease;
}

.calendar-header {
    background: linear-gradient(135deg, #8b4513 0%, #654321 100%);
    color: white;
    padding: 1.5rem;
}

.stats-cards {
    margin-bottom: 2rem;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.table-modern {
    border: none;
}

.table-modern th {
    border: none;
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    padding: 1rem;
}

.table-modern td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
}

.table-modern tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}

.calendar-table {
    border: none;
}

.calendar-table th,
.calendar-table td {
    border: 1px solid #e9ecef;
    padding: 0.75rem;
    text-align: center;
    vertical-align: top;
    min-height: 60px;
    position: relative;
}

.calendar-table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
}

.calendar-day {
    font-weight: bold;
    margin-bottom: 0.25rem;
}

.delivery-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.7rem;
}

@media (max-width: 768px) {
    .bakery-main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 2rem;
    }
}

/* Add this to your existing CSS in dashboard_panetteria.php */

.bakery-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    min-height: 100vh;
    width: calc(100% - 280px);
    overflow-x: auto;
}

.bakery-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
}

@media (max-width: 768px) {
    .bakery-main {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 2rem;
    }
    
    .stats-cards .col-md-3 {
        margin-bottom: 1rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .calendar-table th,
    .calendar-table td {
        padding: 0.5rem;
        font-size: 0.8rem;
    }
}

@media (max-width: 576px) {
    .bakery-main {
        padding: 0.5rem;
    }
    
    .content-header {
        padding: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .content-header h1 {
        font-size: 1.5rem;
    }
    
    .order-header h4,
    .calendar-header h4 {
        font-size: 1.1rem;
    }
}
</style>

<!-- Include Bootstrap CSS e Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="bakery-container">
    <?php include('templates/sidebar.php'); ?>
    <main class="bakery-main">
        <div class="content-header">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-shop me-3" style="font-size: 2rem;"></i>
                <h1 class="mb-0">Dashboard Panetteria</h1>
            </div>
            <p class="mb-0">Gestisci i tuoi ordini e le consegne programmate</p>
        </div>

        <!-- Statistiche con Bootstrap -->
        <div class="row stats-cards">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-list-ul text-primary stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold"><?= $totalOrders ?></h3>
                        <p class="card-text text-muted">Ordini Totali</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-hourglass-split text-warning stat-icon"></i>
                        <h3 class="card-title text-warning fw-bold"><?= $pendingOrders ?></h3>
                        <p class="card-text text-muted">In Attesa</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-gear-fill text-info stat-icon"></i>
                        <h3 class="card-title text-info fw-bold"><?= $processingOrders ?></h3>
                        <p class="card-text text-muted">In Lavorazione</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle-fill text-success stat-icon"></i>
                        <h3 class="card-title text-success fw-bold"><?= $readyOrders ?></h3>
                        <p class="card-text text-muted">Pronti</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Ordini -->
        <div class="card order-card mb-4">
            <div class="order-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-clipboard-check me-3" style="font-size: 1.5rem;"></i>
                    <h4 class="mb-0">Ordini in Lavorazione</h4>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                 <table class="table table-modern mb-0">
                    <thead>
                        <tr>
                            <th><i class="bi bi-person me-2"></i>Cliente</th>
                            <th><i class="bi bi-calendar-date me-2"></i>Data Ordine</th>
                            <th><i class="bi bi-flag me-2"></i>Stato</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                        <?= strtoupper(substr($order['Nome'] ?? $order['Username'], 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($order['Nome'] ?? $order['Username']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <i class="bi bi-calendar3 me-2 text-muted"></i>
                                <?= date('d M Y', strtotime($order['DataCreazione'])) ?>
                            </td>
                            <td>
                                <span class="badge status-badge
                                    <?= match(strtolower($order['Stato'])) {
                                        'in attesa' => 'bg-warning text-dark',
                                        'confermato' => 'bg-info',
                                        'in preparazione' => 'bg-primary',
                                        'pronto' => 'bg-success',
                                        default => 'bg-secondary'
                                    } ?>">
                                    <i class="bi 
                                        <?= match(strtolower($order['Stato'])) {
                                            'in attesa' => 'bi-hourglass-split',
                                            'confermato' => 'bi-check-circle',
                                            'in preparazione' => 'bi-gear-fill',
                                            'pronto' => 'bi-check-circle-fill',
                                            default => 'bi-question-circle'
                                        } ?> me-1"></i>
                                    <?= ucfirst($order['Stato']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun ordine in lavorazione</h5>
                    <p class="text-muted">Tutti gli ordini sono stati completati!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>


                <?php include('templates/calendar.php'); ?>

        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include('templates/footer.php'); ?>

</body>
</html>