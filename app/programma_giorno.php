<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Ordini in Scadenza Oggi - DeliBread";

// Ottieni l'ID della panetteria dell'utente corrente
$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

// Data odierna
$dataOggi = date('Y-m-d');

// Query per ottenere gli ordini in scadenza oggi
$stmt = $conn->prepare("
    SELECT 
        o.IdOrdine,
        o.DataCreazione,
        o.DataConsegna,
        o.Stato,
        o.Note,
        u.Nome as NomeCliente,
        u.Email,
        u.Telefono,
        p.Nome as NomePanetteria
    FROM ordine o 
    INNER JOIN ordine_panetteria op ON o.IdOrdine = op.IdOrdine
    INNER JOIN panetteria p ON op.IdPanetteria = p.IdPanetteria 
    INNER JOIN utente u ON o.IdUtente = u.IdUtente
    WHERE DATE(o.DataConsegna) = ? 
    AND p.IdPanetteria = ?
    AND o.Stato NOT LIKE 'consegnato'
    ORDER BY o.DataConsegna ASC, o.Stato ASC
");

$stmt->bind_param("si", $dataOggi, $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$ordiniScadenza = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcola statistiche
$totalOrdini = count($ordiniScadenza);
$ordiniUrgenti = 0;
$ordiniPronti = 0;
$ordiniInPreparazione = 0;
$totaleValore = 0;

foreach ($ordiniScadenza as $ordine) {
    // Dato che non abbiamo il prezzo dei prodotti, contiamo solo il numero di ordini
    switch(strtolower($ordine['Stato'])) {
        case 'in attesa':
            $ordiniUrgenti++;
            break;
        case 'pronto':
            $ordiniPronti++;
            break;
        case 'in preparazione':
        case 'confermato':
            $ordiniInPreparazione++;
            break;
    }
}

// Raggruppa ordini per ID (senza prodotti per ora)
$ordiniRaggruppati = [];
foreach ($ordiniScadenza as $ordine) {
    $idOrdine = $ordine['IdOrdine'];
    if (!isset($ordiniRaggruppati[$idOrdine])) {
        $ordiniRaggruppati[$idOrdine] = [
            'ordine' => $ordine,
            'prodotti' => []
        ];
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
}

.content-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
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

.urgent-badge {
    background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.order-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
    border-left: 5px solid #dc3545;
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.order-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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

.priority-high {
    border-left-color: #dc3545 !important;
    border-left-width: 5px !important;
}

.priority-medium {
    border-left-color: #ffc107 !important;
    border-left-width: 5px !important;
}

.priority-low {
    border-left-color: #28a745 !important;
    border-left-width: 5px !important;
}

.time-remaining {
    font-weight: bold;
}

.time-critical {
    color: #dc3545;
}

.time-warning {
    color: #ffc107;
}

.time-ok {
    color: #28a745;
}

.product-list {
    max-height: 200px;
    overflow-y: auto;
}

.product-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.product-item:last-child {
    border-bottom: none;
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
</style>

<!-- Include Bootstrap CSS e Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="bakery-container">
    <?php include('templates/sidebar.php'); ?>
    <main class="bakery-main">
        <div class="content-header">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-exclamation-triangle me-3" style="font-size: 2rem;"></i>
                <div>
                    <h1 class="mb-0">Ordini in Scadenza Oggi</h1>
                    <div class="d-flex align-items-center mt-2">
                        <span class="urgent-badge me-2">URGENTE</span>
                        <span><?= date('d F Y') ?></span>
                    </div>
                </div>
            </div>
            <p class="mb-0">Monitora e gestisci gli ordini che devono essere consegnati oggi</p>
        </div>

        <!-- Statistiche Urgenti -->
        <div class="row stats-cards">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100 priority-high">
                    <div class="card-body text-center">
                        <i class="bi bi-list-ul text-danger stat-icon"></i>
                        <h3 class="card-title text-danger fw-bold"><?= $totalOrdini ?></h3>
                        <p class="card-text text-muted">Totale Ordini Oggi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100 priority-high">
                    <div class="card-body text-center">
                        <i class="bi bi-exclamation-triangle text-danger stat-icon"></i>
                        <h3 class="card-title text-danger fw-bold"><?= $ordiniUrgenti ?></h3>
                        <p class="card-text text-muted">Urgenti (In Attesa)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100 priority-medium">
                    <div class="card-body text-center">
                        <i class="bi bi-gear-fill text-warning stat-icon"></i>
                        <h3 class="card-title text-warning fw-bold"><?= $ordiniInPreparazione ?></h3>
                        <p class="card-text text-muted">In Lavorazione</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100 priority-low">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle-fill text-success stat-icon"></i>
                        <h3 class="card-title text-success fw-bold"><?= $ordiniPronti ?></h3>
                        <p class="card-text text-muted">Pronti</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Valore Totale - Rimosso per ora -->
        <!-- 
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-euro text-success stat-icon"></i>
                        <h3 class="text-success fw-bold">€ <?= number_format($totaleValore, 2) ?></h3>
                        <p class="text-muted mb-0">Valore Totale Ordini Oggi</p>
                    </div>
                </div>
            </div>
        </div>
        -->

        <!-- Tabella Ordini in Scadenza -->
        <div class="card order-card">
            <div class="order-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-alarm me-3" style="font-size: 1.5rem;"></i>
                        <h4 class="mb-0">Dettaglio Ordini in Scadenza</h4>
                    </div>
                    <span class="badge bg-light text-dark"><?= $totalOrdini ?> ordini</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($ordiniRaggruppati) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-2"></i>Ordine</th>
                                <th><i class="bi bi-person me-2"></i>Cliente</th>
                                <th><i class="bi bi-clock me-2"></i>Ora Consegna</th>
                                <th><i class="bi bi-flag me-2"></i>Stato</th>
                                <th><i class="bi bi-chat-dots me-2"></i>Note</th>
                                <th><i class="bi bi-telephone me-2"></i>Contatto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordiniRaggruppati as $gruppo): 
                                $ordine = $gruppo['ordine'];
                                $prodotti = $gruppo['prodotti'];
                                $oraConsegna = date('H:i', strtotime($ordine['DataConsegna']));
                                $orarioCorrente = date('H:i');
                                $priorityClass = '';
                                $timeClass = '';
                                
                                // Determina priorità e colore in base all'orario
                                if ($oraConsegna <= $orarioCorrente) {
                                    $priorityClass = 'table-danger';
                                    $timeClass = 'time-critical';
                                } else if (strtotime($oraConsegna) - strtotime($orarioCorrente) <= 3600) { // 1 ora
                                    $priorityClass = 'table-warning';
                                    $timeClass = 'time-warning';
                                } else {
                                    $timeClass = 'time-ok';
                                }
                            ?>
                            <tr class="<?= $priorityClass ?>">
                                <td>
                                    <strong>#<?= $ordine['IdOrdine'] ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($ordine['DataCreazione'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                            <?= strtoupper(substr($ordine['NomeCliente'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($ordine['NomeCliente'] ?? 'Cliente') ?></strong>
                                            <?php if ($ordine['Telefono']): ?>
                                                <br><small class="text-muted">
                                                    <i class="bi bi-telephone me-1"></i>
                                                    <?= htmlspecialchars($ordine['Telefono']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="time-remaining <?= $timeClass ?>">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= $oraConsegna ?>
                                    </span>
                                    <?php if ($oraConsegna <= $orarioCorrente): ?>
                                        <br><small class="text-danger">
                                            <i class="bi bi-exclamation-triangle me-1"></i>
                                            IN RITARDO
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge status-badge
                                        <?= match(strtolower($ordine['Stato'])) {
                                            'in attesa' => 'bg-danger',
                                            'confermato' => 'bg-info',
                                            'in preparazione' => 'bg-warning text-dark',
                                            'pronto' => 'bg-success',
                                            default => 'bg-secondary'
                                        } ?>">
                                        <i class="bi 
                                            <?= match(strtolower($ordine['Stato'])) {
                                                'in attesa' => 'bi-exclamation-triangle',
                                                'confermato' => 'bi-check-circle',
                                                'in preparazione' => 'bi-gear-fill',
                                                'pronto' => 'bi-check-circle-fill',
                                                default => 'bi-question-circle'
                                            } ?> me-1"></i>
                                        <?= ucfirst($ordine['Stato']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ordine['Note']): ?>
                                        <span class="text-info" title="<?= htmlspecialchars($ordine['Note']) ?>">
                                            <i class="bi bi-chat-quote"></i>
                                            <?= strlen($ordine['Note']) > 20 ? substr(htmlspecialchars($ordine['Note']), 0, 20) . '...' : htmlspecialchars($ordine['Note']) ?>
                                        </span>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($ordine['Telefono']): ?>
                                        <a href="tel:<?= htmlspecialchars($ordine['Telefono']) ?>" class="text-primary">
                                            <i class="bi bi-telephone me-1"></i>
                                            <?= htmlspecialchars($ordine['Telefono']) ?>
                                        </a>
                                    <?php elseif ($ordine['Email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($ordine['Email']) ?>" class="text-primary">
                                            <i class="bi bi-envelope me-1"></i>
                                            Email
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle display-1 text-success mb-3"></i>
                    <h5 class="text-success">Ottimo lavoro!</h5>
                    <p class="text-muted">Non ci sono ordini in scadenza per oggi</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Alert per ordini urgenti -->
        <?php if ($ordiniUrgenti > 0): ?>
        <div class="alert alert-danger mt-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h5 class="alert-heading mb-1">Attenzione! Ordini Urgenti</h5>
                    <p class="mb-0">Ci sono <strong><?= $ordiniUrgenti ?></strong> ordini ancora in attesa che dovrebbero essere consegnati oggi. Inizia la preparazione immediatamente!</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include('templates/footer.php'); ?>

</body>
</html>