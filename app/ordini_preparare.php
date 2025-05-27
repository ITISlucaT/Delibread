<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Ordini di Oggi - DeliBread";

// Recupera l'ID della panetteria dell'utente corrente
$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

// Query per recuperare gli ordini di oggi
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT 
    o.IdOrdine,
    o.DataCreazione,
    o.DataConsegna,
    o.Stato,
    o.Note,
    u.Nome as NomeCliente,
    u.Cognome as CognomeCliente,
    u.Email,
    u.Telefono,
    p.Nome as NomeProdotto,
    op.Quantita,
    pan.Nome as NomePanetteria
FROM ordine o 
INNER JOIN ordine_panetteria op_pan ON o.IdOrdine = op_pan.IdOrdine
INNER JOIN panetteria pan ON op_pan.IdPanetteria = pan.IdPanetteria 
INNER JOIN utente u ON o.IdUtente = u.IdUtente
INNER JOIN ordine_prodotto op ON o.IdOrdine = op.IdOrdine
INNER JOIN prodotto p ON op.IdProdotto = p.IdProdotto
WHERE DATE(o.DataCreazione) = ? 
AND pan.IdPanetteria = ?
ORDER BY o.DataCreazione DESC, o.IdOrdine, p.Nome");

$stmt->bind_param("si", $today, $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$ordersData = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Raggruppa gli ordini per ID ordine per gestire ordini con più prodotti
$orders = [];
foreach ($ordersData as $row) {
    $orderId = $row['IdOrdine'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'IdOrdine' => $row['IdOrdine'],
            'DataCreazione' => $row['DataCreazione'],
            'DataConsegna' => $row['DataConsegna'],
            'Stato' => $row['Stato'],
            'Note' => $row['Note'],
            'NomeCliente' => $row['NomeCliente'],
            'CognomeCliente' => $row['CognomeCliente'],
            'Username' => $row['Username'],
            'Email' => $row['Email'],
            'Telefono' => $row['Telefono'],
            'prodotti' => []
        ];
    }
    $orders[$orderId]['prodotti'][] = [
        'nome' => $row['NomeProdotto'],
        'quantita' => $row['Quantita']
    ];
}

// Calcola statistiche per le card
$totalOrders = count($orders);
$pendingOrders = 0;
$processingOrders = 0;
$readyOrders = 0;
$deliveredOrders = 0;

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
        case 'consegnato':
            $deliveredOrders++;
            break;
    }
}

// Funzione per ottenere la classe CSS dello stato
function getStatusClass($stato) {
    switch(strtolower($stato)) {
        case 'in attesa':
            return 'bg-warning text-dark';
        case 'confermato':
            return 'bg-info text-white';
        case 'in preparazione':
            return 'bg-primary text-white';
        case 'pronto':
            return 'bg-success text-white';
        case 'consegnato':
            return 'bg-secondary text-white';
        default:
            return 'bg-light text-dark';
    }
}

// Funzione per ottenere l'icona dello stato
function getStatusIcon($stato) {
    switch(strtolower($stato)) {
        case 'in attesa':
            return 'bi-clock';
        case 'confermato':
            return 'bi-check-circle';
        case 'in preparazione':
            return 'bi-gear';
        case 'pronto':
            return 'bi-check2-all';
        case 'consegnato':
            return 'bi-truck';
        default:
            return 'bi-question-circle';
    }
}
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
    min-height: 100vh;
    width: calc(100% - 280px);
    overflow-x: auto;
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

.stats-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
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

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
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

.product-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.product-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid #e9ecef;
}

.product-item:last-child {
    border-bottom: none;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content-center;
    font-weight: bold;
    font-size: 1.1rem;
}

.date-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
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
    
    .order-header h4 {
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
                <i class="bi bi-calendar-day me-3" style="font-size: 2rem;"></i>
                <div>
                    <h1 class="mb-0">Ordini di Oggi</h1>
                    <p class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?= date('d F Y') ?>
                    </p>
                </div>
            </div>
            <div class="mt-3">
                <span class="date-badge">
                    <i class="bi bi-clock me-2"></i>
                    Aggiornato in tempo reale
                </span>
            </div>
        </div>

        <!-- Statistiche Ordini di Oggi -->
        <div class="row stats-cards mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-list-check stat-icon text-primary"></i>
                        <div class="stat-number text-primary"><?= $totalOrders ?></div>
                        <h6 class="text-muted mb-0">Totale Ordini</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-clock stat-icon text-warning"></i>
                        <div class="stat-number text-warning"><?= $pendingOrders ?></div>
                        <h6 class="text-muted mb-0">In Attesa</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-gear stat-icon text-info"></i>
                        <div class="stat-number text-info"><?= $processingOrders ?></div>
                        <h6 class="text-muted mb-0">In Lavorazione</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-check2-all stat-icon text-success"></i>
                        <div class="stat-number text-success"><?= $readyOrders ?></div>
                        <h6 class="text-muted mb-0">Pronti</h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Ordini di Oggi -->
        <div class="card order-card">
            <div class="order-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-clipboard-check me-3" style="font-size: 1.5rem;"></i>
                        <h4 class="mb-0">Dettaglio Ordini di Oggi</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-light text-dark me-2">
                            <i class="bi bi-calendar-day me-1"></i>
                            <?= date('d/m/Y') ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <?= $totalOrders ?> ordini
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>ID Ordine</th>
                                <th><i class="bi bi-person me-2"></i>Cliente</th>
                                <th><i class="bi bi-telephone me-2"></i>Contatto</th>
                                <th><i class="bi bi-clock me-2"></i>Ora Ordine</th>
                                <th><i class="bi bi-truck me-2"></i>Consegna</th>
                                <th><i class="bi bi-box-seam me-2"></i>Prodotti</th>
                                <th><i class="bi bi-flag me-2"></i>Stato</th>
                                <th><i class="bi bi-sticky me-2"></i>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?= $order['IdOrdine'] ?></strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-3">
                                            <?= strtoupper(substr($order['NomeCliente'] ?: $order['Username'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($order['NomeCliente'] . ' ' . $order['CognomeCliente']) ?></strong>
                                            <?php if (!$order['NomeCliente']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($order['Username']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="bi bi-envelope me-1 text-muted"></i>
                                        <small><?= htmlspecialchars($order['Email']) ?></small>
                                    </div>
                                    <?php if ($order['Telefono']): ?>
                                    <div>
                                        <i class="bi bi-telephone me-1 text-muted"></i>
                                        <small><?= htmlspecialchars($order['Telefono']) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <i class="bi bi-clock me-2 text-muted"></i>
                                    <strong><?= date('H:i', strtotime($order['DataCreazione'])) ?></strong>
                                </td>
                                <td>
                                    <?php if ($order['DataConsegna']): ?>
                                        <i class="bi bi-calendar-check me-2 text-success"></i>
                                        <?= date('d/m H:i', strtotime($order['DataConsegna'])) ?>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-dash-circle me-2"></i>
                                            Non specificata
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <ul class="product-list">
                                        <?php foreach ($order['prodotti'] as $prodotto): ?>
                                        <li class="product-item">
                                            <strong><?= htmlspecialchars($prodotto['nome']) ?></strong>
                                            <span class="badge bg-secondary ms-2">×<?= $prodotto['quantita'] ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                                <td>
                                    <span class="status-badge <?= getStatusClass($order['Stato']) ?>">
                                        <i class="bi <?= getStatusIcon($order['Stato']) ?> me-1"></i>
                                        <?= ucfirst($order['Stato']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($order['Note']): ?>
                                        <div class="text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($order['Note']) ?>">
                                            <i class="bi bi-sticky me-1 text-muted"></i>
                                            <?= htmlspecialchars($order['Note']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">
                                            <i class="bi bi-dash me-1"></i>
                                            Nessuna nota
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun ordine oggi</h5>
                    <p class="text-muted">Non ci sono ordini per la data di oggi.</p>
                    <div class="mt-4">
                        <a href="dashboard_panetteria.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Torna alla Dashboard
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (count($orders) > 0): ?>
        <!-- Riepilogo Rapido -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-pie-chart me-2"></i>
                            Riepilogo Stati
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="text-warning">
                                    <i class="bi bi-clock display-6"></i>
                                    <div class="mt-2">
                                        <strong><?= $pendingOrders ?></strong>
                                        <br><small>In Attesa</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-info">
                                    <i class="bi bi-gear display-6"></i>
                                    <div class="mt-2">
                                        <strong><?= $processingOrders ?></strong>
                                        <br><small>In Lavorazione</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-success">
                                    <i class="bi bi-check2-all display-6"></i>
                                    <div class="mt-2">
                                        <strong><?= $readyOrders ?></strong>
                                        <br><small>Pronti</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-secondary">
                                    <i class="bi bi-truck display-6"></i>
                                    <div class="mt-2">
                                        <strong><?= $deliveredOrders ?></strong>
                                        <br><small>Consegnati</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Informazioni Utili
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="bi bi-clock text-primary me-2"></i>
                                <strong>Ultima sincronizzazione:</strong> <?= date('H:i:s') ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-calendar3 text-success me-2"></i>
                                <strong>Data di riferimento:</strong> <?= date('d F Y') ?>
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-graph-up text-info me-2"></i>
                                <strong>Totale ordini:</strong> <?= $totalOrders ?>
                            </li>
                            <li>
                                <i class="bi bi-arrow-clockwise text-warning me-2"></i>
                                <small class="text-muted">La pagina si aggiorna automaticamente</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script per aggiornamento automatico -->
<script>
// Aggiorna la pagina ogni 5 minuti per mantenere i dati aggiornati
setTimeout(function() {
    location.reload();
}, 300000); // 5 minuti

// Mostra l'ora corrente nel header
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('it-IT');
    document.querySelectorAll('.current-time').forEach(el => {
        el.textContent = timeString;
    });
}

// Aggiorna l'ora ogni secondo
setInterval(updateCurrentTime, 1000);
updateCurrentTime();
</script>

<?php include('templates/footer.php'); ?>

</body>
</html>