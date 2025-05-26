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
$orders = $result->fetch_all(MYSQLI_ASSOC); // Recupera TUTTE le righe
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

?>
<!-- dashboard_panetteria.php (aggiornato) -->
<?php include('templates/head.php'); ?>
<body>
    <?php include('templates/sidebar.php'); ?>
    
    <div class="main-content">
        <?php include('templates/header.php'); ?>
        
        <div class="container-fluid mt-4">
            <div class="card shadow-sm mb-4">
    <div class="card-header bg-beige">
        <h5 class="mb-0">Ordini in Lavorazione</h5>
    </div>
    <div class="card-body">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Data Ordine</th>
                    <th>Stato</th>
                    <th>Totale</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['Nome'] ?? $order['Username']) ?></td>
                    <td><?= date('d M Y', strtotime($order['DataOrdine'])) ?></td>
                    <td>
                        <span class="badge 
                            <?= match(strtolower($order['Stato'])) {
                                'in attesa' => 'bg-warning',
                                'confermato', 'in preparazione' => 'bg-info',
                                'pronto' => 'bg-success',
                                default => 'bg-secondary'
                            } ?>">
                            <?= ucfirst($order['Stato']) ?>
                        </span>
                    </td>
                    <td>€<?= number_format($order['Totale'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Sezione Calendario -->
<div class="card shadow-sm">
    <div class="card-header bg-beige">
        <h5 class="mb-0">Calendario Consegne</h5>
    </div>
    <div class="card-body">
        <div class="calendar">
            <div class="d-flex justify-content-between mb-3">
                <h6><?= date('F Y') ?></h6>
            </div>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th scope="col">Lun</th>
                        <th scope="col">Mar</th>
                        <th scope="col">Mer</th>
                        <th scope="col">Gio</th>
                        <th scope="col">Ven</th>
                        <th scope="col">Sab</th>
                        <th scope="col">Dom</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeks as $week): ?>
                    <tr>
                        <?php foreach ($week as $day): ?>
                        <td class="<?= $day ? 'fw-bold' : 'text-muted' ?>">
                            <?= $day ?: '' ?>
                            <?php if ($day): ?>
                            <div class="small">
                                <?php
                                $currentDate = date("$year-$month-$day");
                                $deliveries = array_filter($deliveredOrders, function($o) use ($currentDate) {
                                    return $o['DataConsegna'] == $currentDate;
                                });
                                ?>
                                <?php if(count($deliveries) > 0): ?>
                                <span class="badge bg-danger rounded-pill">
                                    <?= count($deliveries) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-sm btn-outline-secondary">Mese Precedente</button>
                            <button class="btn btn-sm btn-primary">Mese Successivo</button>
                        </div>
                    </div>
                </div>
            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('templates/footer.php'); ?>

</body>
</html>