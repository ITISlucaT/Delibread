<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Ordini Risolti - DeliBread";

// Ottieni l'ID della panetteria dell'utente loggato
$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

// Filtri per ricerca
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'desc'; // desc = più recenti prima

// Query base per ordini consegnati
$query = "SELECT o.*, u.Nome, u.Cognome, u.Email, u.Telefono,
                 COUNT(op.IdProdotto) as NumProdotti,
                 GROUP_CONCAT(CONCAT(p.Nome, ' (', op.Quantita, ')') SEPARATOR ', ') as Prodotti
          FROM ordine o 
          INNER JOIN ordine_panetteria opr ON o.IdOrdine = opr.IdOrdine
          INNER JOIN panetteria pa ON opr.IdPanetteria = pa.IdPanetteria 
          INNER JOIN utente u ON o.IdUtente = u.IdUtente
          LEFT JOIN ordine_prodotto op ON o.IdOrdine = op.IdOrdine
          LEFT JOIN prodotto p ON op.IdProdotto = p.IdProdotto
          WHERE o.Stato = 'Consegnato' AND pa.IdPanetteria = ?";

$params = [$idPanetteria];
$types = "i";

// Aggiungi filtri alla query
if (!empty($searchTerm)) {
    $query .= " AND (u.Nome LIKE ? OR u.Cognome LIKE ? OR u.Email LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
    $types .= "sss";
}

if (!empty($dateFrom)) {
    $query .= " AND o.DataConsegna >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if (!empty($dateTo)) {
    $query .= " AND o.DataConsegna <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

$query .= " GROUP BY o.IdOrdine";
$query .= " ORDER BY o.DataConsegna " . ($sortBy === 'asc' ? 'ASC' : 'DESC');

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$completedOrders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcola statistiche
$totalCompleted = count($completedOrders);
$thisMonth = 0;
$thisWeek = 0;
$today = 0;

$currentMonth = date('Y-m');
$currentWeek = date('Y-W');
$currentDate = date('Y-m-d');

foreach ($completedOrders as $order) {
    $orderMonth = date('Y-m', strtotime($order['DataConsegna']));
    $orderWeek = date('Y-W', strtotime($order['DataConsegna']));
    $orderDate = date('Y-m-d', strtotime($order['DataConsegna']));
    
    if ($orderMonth === $currentMonth) $thisMonth++;
    if ($orderWeek === $currentWeek) $thisWeek++;
    if ($orderDate === $currentDate) $today++;
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
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
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 1.5rem;
}

.filter-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filter-header {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 15px 15px 0 0;
}

.stats-cards {
    margin-bottom: 2rem;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
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

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.order-details {
    font-size: 0.9rem;
    color: #6c757d;
}

.btn-filter {
    border-radius: 10px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
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
                <i class="bi bi-check-circle-fill me-3" style="font-size: 2rem;"></i>
                <h1 class="mb-0">Ordini Risolti</h1>
            </div>
            <p class="mb-0">Visualizza tutti gli ordini completati e consegnati</p>
        </div>

        <!-- Statistiche -->
        <div class="row stats-cards">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle-fill text-success stat-icon"></i>
                        <h3 class="card-title text-success fw-bold"><?= $totalCompleted ?></h3>
                        <p class="card-text text-muted">Totale Completati</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-month text-primary stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold"><?= $thisMonth ?></h3>
                        <p class="card-text text-muted">Questo Mese</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-week text-info stat-icon"></i>
                        <h3 class="card-title text-info fw-bold"><?= $thisWeek ?></h3>
                        <p class="card-text text-muted">Questa Settimana</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-day text-warning stat-icon"></i>
                        <h3 class="card-title text-warning fw-bold"><?= $today ?></h3>
                        <p class="card-text text-muted">Oggi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtri di Ricerca -->
        <div class="card filter-card">
            <div class="filter-header">
                <div class="d-flex align-items-center">
                    <i class="bi bi-funnel me-2"></i>
                    <h5 class="mb-0">Filtri di Ricerca</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">
                                <i class="bi bi-search me-1"></i>Cerca Cliente
                            </label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Nome, cognome o email..." value="<?= htmlspecialchars($searchTerm) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">
                                <i class="bi bi-calendar-range me-1"></i>Data Da
                            </label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?= $dateFrom ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">
                                <i class="bi bi-calendar-range me-1"></i>Data A
                            </label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?= $dateTo ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="sort" class="form-label">
                                <i class="bi bi-sort-down me-1"></i>Ordina
                            </label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="desc" <?= $sortBy === 'desc' ? 'selected' : '' ?>>Più Recenti</option>
                                <option value="asc" <?= $sortBy === 'asc' ? 'selected' : '' ?>>Più Vecchi</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-filter me-2">
                                <i class="bi bi-search me-1"></i>Filtra
                            </button>
                            <a href="ordini_risolti.php" class="btn btn-outline-secondary btn-filter">
                                <i class="bi bi-x-circle me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista Ordini Completati -->
        <div class="card order-card">
            <div class="order-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-archive me-3" style="font-size: 1.5rem;"></i>
                        <h4 class="mb-0">Storico Ordini Completati</h4>
                    </div>
                    <span class="badge bg-light text-dark fs-6"><?= count($completedOrders) ?> ordini</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($completedOrders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>Ordine</th>
                                <th><i class="bi bi-person me-2"></i>Cliente</th>
                                <th><i class="bi bi-calendar-date me-2"></i>Data Consegna</th>
                                <th><i class="bi bi-box me-2"></i>Prodotti</th>
                                <th><i class="bi bi-flag me-2"></i>Stato</th>
                                <th><i class="bi bi-three-dots me-2"></i>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedOrders as $order): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-success text-white me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border-radius: 10px; font-weight: bold;">
                                            #<?= $order['IdOrdine'] ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($order['Nome'] . ' ' . $order['Cognome']) ?></strong>
                                        <div class="order-details">
                                            <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($order['Email']) ?>
                                        </div>
                                        <?php if (!empty($order['Telefono'])): ?>
                                        <div class="order-details">
                                            <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['Telefono']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-calendar-check me-2 text-success"></i>
                                        <div>
                                            <strong><?= date('d/m/Y', strtotime($order['DataConsegna'])) ?></strong>
                                            <div class="order-details">
                                                Ordinato il <?= date('d/m/Y', strtotime($order['DataCreazione'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-info text-white">
                                            <?= $order['NumProdotti'] ?> prodotti
                                        </span>
                                        <?php if (!empty($order['Prodotti'])): ?>
                                        <div class="order-details mt-1" title="<?= htmlspecialchars($order['Prodotti']) ?>">
                                            <?= htmlspecialchars(strlen($order['Prodotti']) > 50 ? substr($order['Prodotti'], 0, 50) . '...' : $order['Prodotti']) ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge status-badge bg-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        Consegnato
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewOrderDetails(<?= $order['IdOrdine'] ?>)" 
                                                title="Visualizza dettagli">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="printOrder(<?= $order['IdOrdine'] ?>)" 
                                                title="Stampa ricevuta">
                                            <i class="bi bi-printer"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun ordine completato trovato</h5>
                    <p class="text-muted">
                        <?php if (!empty($searchTerm) || !empty($dateFrom) || !empty($dateTo)): ?>
                            Prova a modificare i filtri di ricerca per trovare altri ordini.
                        <?php else: ?>
                            Non ci sono ancora ordini completati da visualizzare.
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function viewOrderDetails(orderId) {
    // Implementa la visualizzazione dei dettagli dell'ordine
    alert('Visualizza dettagli ordine #' + orderId);
    // Qui potresti aprire un modal o reindirizzare a una pagina di dettaglio
}

function printOrder(orderId) {
    // Implementa la stampa della ricevuta
    alert('Stampa ricevuta ordine #' + orderId);
    // Qui potresti aprire una finestra di stampa o generare un PDF
}

// Auto-submit del form quando cambia l'ordinamento
document.getElementById('sort').addEventListener('change', function() {
    this.form.submit();
});
</script>

<?php include('templates/footer.php'); ?>

</body>
</html>