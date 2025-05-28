<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Gestione Cataloghi DeliBread";

// Ottieni ID panetteria dell'utente corrente
$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

// Gestione delle azioni AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_catalog':
            $nome = trim($_POST['nome']);
            $attivo = isset($_POST['attivo']) ? 1 : 0;
            
            if (!empty($nome)) {
                $stmt = $conn->prepare("INSERT INTO catalogo (IdPanetteria, Nome, Attivo) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $idPanetteria, $nome, $attivo);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Catalogo creato con successo']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Errore nella creazione del catalogo']);
                }
                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Nome catalogo richiesto']);
            }
            exit;
            
        case 'toggle_catalog':
            $idCatalogo = intval($_POST['id_catalogo']);
            $attivo = intval($_POST['attivo']);
            
            $stmt = $conn->prepare("UPDATE catalogo SET Attivo = ? WHERE IdCatalogo = ? AND IdPanetteria = ?");
            $stmt->bind_param("iii", $attivo, $idCatalogo, $idPanetteria);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Stato catalogo aggiornato']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Errore nell\'aggiornamento']);
            }
            $stmt->close();
            exit;
            
        case 'delete_catalog':
            $idCatalogo = intval($_POST['id_catalogo']);
            
            // Prima elimina i prodotti associati
            $stmt = $conn->prepare("DELETE FROM catalogo_prodotto WHERE IdCatalogo = ?");
            $stmt->bind_param("i", $idCatalogo);
            $stmt->execute();
            $stmt->close();
            
            // Poi elimina il catalogo
            $stmt = $conn->prepare("DELETE FROM catalogo WHERE IdCatalogo = ? AND IdPanetteria = ?");
            $stmt->bind_param("ii", $idCatalogo, $idPanetteria);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Catalogo eliminato con successo']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Errore nell\'eliminazione']);
            }
            $stmt->close();
            exit;
    }
}

// Recupera i cataloghi della panetteria
$stmt = $conn->prepare("
    SELECT c.*, 
           COUNT(cp.IdProdotto) as NumProdotti,
           GROUP_CONCAT(DISTINCT t.Nome SEPARATOR ', ') as Tipologie
    FROM catalogo c
    LEFT JOIN catalogo_prodotto cp ON c.IdCatalogo = cp.IdCatalogo
    LEFT JOIN prodotto p ON cp.IdProdotto = p.IdProdotto
    LEFT JOIN prodotto_tipologia pt ON p.IdProdotto = pt.IdProdotto
    LEFT JOIN tipologia t ON pt.IdTipologia = t.IdTipologia
    WHERE c.IdPanetteria = ?
    GROUP BY c.IdCatalogo
    ORDER BY c.Nome ASC
");
$stmt->bind_param("i", $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$cataloghi = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcola statistiche
$totalCataloghi = count($cataloghi);
$cataloghiAttivi = 0;
$cataloghiInattivi = 0;
$totalProdotti = 0;

foreach ($cataloghi as $catalogo) {
    if ($catalogo['Attivo']) {
        $cataloghiAttivi++;
    } else {
        $cataloghiInattivi++;
    }
    $totalProdotti += $catalogo['NumProdotti'];
}

// Recupera le tipologie disponibili per i filtri
$stmt = $conn->prepare("SELECT * FROM tipologia ORDER BY Nome ASC");
$stmt->execute();
$result = $stmt->get_result();
$tipologie = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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

.catalog-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.catalog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.catalog-header {
    background: linear-gradient(135deg, #d4a574 0%, #cd853f 100%);
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

.btn-action {
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin: 0 0.2rem;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

.modal-header {
    background: linear-gradient(135deg, #d4a574 0%, #8b4513 100%);
    color: white;
    border: none;
    border-radius: 15px 15px 0 0;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #d4a574;
    box-shadow: 0 0 0 0.2rem rgba(212, 165, 116, 0.25);
}

.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #d4a574;
}

input:checked + .slider:before {
    transform: translateX(26px);
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
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center">
                    <i class="bi bi-book me-3" style="font-size: 2rem;"></i>
                    <h1 class="mb-0">Gestione Cataloghi</h1>
                </div>
                <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#newCatalogModal">
                    <i class="bi bi-plus-circle me-2"></i>Nuovo Catalogo
                </button>
            </div>
            <p class="mb-0">Crea e gestisci i cataloghi dei tuoi prodotti</p>
        </div>

        <!-- Statistiche -->
        <div class="row stats-cards">
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-collection text-primary stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold"><?= $totalCataloghi ?></h3>
                        <p class="card-text text-muted">Cataloghi Totali</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle text-success stat-icon"></i>
                        <h3 class="card-title text-success fw-bold"><?= $cataloghiAttivi ?></h3>
                        <p class="card-text text-muted">Attivi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-pause-circle text-warning stat-icon"></i>
                        <h3 class="card-title text-warning fw-bold"><?= $cataloghiInattivi ?></h3>
                        <p class="card-text text-muted">Inattivi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-box text-info stat-icon"></i>
                        <h3 class="card-title text-info fw-bold"><?= $totalProdotti ?></h3>
                        <p class="card-text text-muted">Prodotti Totali</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista Cataloghi -->
        <div class="card catalog-card">
            <div class="catalog-header">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-grid-3x3-gap me-3" style="font-size: 1.5rem;"></i>
                        <h4 class="mb-0">I Tuoi Cataloghi</h4>
                    </div>
                    <div class="d-flex align-items-center">
                        <input type="text" id="searchCatalog" class="form-control form-control-sm me-2" placeholder="Cerca catalogo..." style="width: 200px;">
                        <select id="filterStatus" class="form-select form-select-sm" style="width: 120px;">
                            <option value="">Tutti</option>
                            <option value="1">Attivi</option>
                            <option value="0">Inattivi</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (count($cataloghi) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th><i class="bi bi-tag me-2"></i>Nome Catalogo</th>
                                <th><i class="bi bi-box me-2"></i>Prodotti</th>
                                <th><i class="bi bi-tags me-2"></i>Tipologie</th>
                                <th><i class="bi bi-toggle-on me-2"></i>Stato</th>
                                <th><i class="bi bi-gear me-2"></i>Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="catalogTable">
                            <?php foreach ($cataloghi as $catalogo): ?>
                            <tr data-name="<?= strtolower($catalogo['Nome']) ?>" data-status="<?= $catalogo['Attivo'] ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border-radius: 50%;">
                                            <i class="bi bi-book"></i>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($catalogo['Nome']) ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?= $catalogo['IdCatalogo'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-box me-1"></i>
                                        <?= $catalogo['NumProdotti'] ?> prodotti
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($catalogo['Tipologie'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach (array_slice(explode(', ', $catalogo['Tipologie']), 0, 3) as $tipologia): ?>
                                                <span class="badge bg-light text-dark"><?= htmlspecialchars($tipologia) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count(explode(', ', $catalogo['Tipologie'])) > 3): ?>
                                                <span class="badge bg-secondary">+<?= count(explode(', ', $catalogo['Tipologie'])) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Nessuna tipologia</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <label class="switch">
                                        <input type="checkbox" <?= $catalogo['Attivo'] ? 'checked' : '' ?> 
                                               onchange="toggleCatalog(<?= $catalogo['IdCatalogo'] ?>, this.checked ? 1 : 0)">
                                        <span class="slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <button class="btn btn-sm btn-outline-primary btn-action" 
                                                onclick="editCatalog(<?= $catalogo['IdCatalogo'] ?>)"
                                                title="Modifica">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success btn-action" 
                                                onclick="manageProdotti(<?= $catalogo['IdCatalogo'] ?>)"
                                                title="Gestisci Prodotti">
                                            <i class="bi bi-box-seam"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action" 
                                                onclick="deleteCatalog(<?= $catalogo['IdCatalogo'] ?>, '<?= htmlspecialchars($catalogo['Nome']) ?>')"
                                                title="Elimina">
                                            <i class="bi bi-trash"></i>
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
                    <i class="bi bi-collection display-1 text-muted mb-3"></i>
                    <h5 class="text-muted">Nessun catalogo trovato</h5>
                    <p class="text-muted">Crea il tuo primo catalogo per iniziare!</p>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#newCatalogModal">
                        <i class="bi bi-plus-circle me-2"></i>Crea Primo Catalogo
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Modal Nuovo Catalogo -->
<div class="modal fade" id="newCatalogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Nuovo Catalogo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="newCatalogForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="catalogName" class="form-label">Nome Catalogo *</label>
                        <input type="text" class="form-control" id="catalogName" name="nome" required
                               placeholder="Es: Catalogo Inverno 2024">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="catalogActive" name="attivo" checked>
                            <label class="form-check-label" for="catalogActive">
                                Catalogo attivo
                            </label>
                        </div>
                        <small class="text-muted">I cataloghi attivi sono visibili ai clienti</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Crea Catalogo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Gestione form nuovo catalogo
document.getElementById('newCatalogForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create_catalog');
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        alert('Errore di comunicazione: ' + error);
    });
});

// Toggle stato catalogo
function toggleCatalog(idCatalogo, attivo) {
    const formData = new FormData();
    formData.append('action', 'toggle_catalog');
    formData.append('id_catalogo', idCatalogo);
    formData.append('attivo', attivo);
    
    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Errore: ' + data.message);
            location.reload();
        }
    })
    .catch(error => {
        alert('Errore di comunicazione: ' + error);
        location.reload();
    });
}

// Elimina catalogo
function deleteCatalog(idCatalogo, nome) {
    if (confirm(`Sei sicuro di voler eliminare il catalogo "${nome}"?\nQuesta azione non può essere annullata.`)) {
        const formData = new FormData();
        formData.append('action', 'delete_catalog');
        formData.append('id_catalogo', idCatalogo);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => {
            alert('Errore di comunicazione: ' + error);
        });
    }
}

// Funzioni placeholder per azioni future
function editCatalog(idCatalogo) {
    alert('Funzione di modifica in sviluppo per catalogo ID: ' + idCatalogo);
}

function manageProdotti(idCatalogo) {
    // Reindirizza alla pagina di gestione prodotti del catalogo
    window.location.href = 'gestione_prodotti_catalogo.php?id=' + idCatalogo;
}

// Filtri e ricerca
document.getElementById('searchCatalog').addEventListener('input', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchCatalog').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#catalogTable tr');
    
    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const status = row.getAttribute('data-status');
        
        const matchesSearch = name.includes(searchTerm);
        const matchesStatus = statusFilter === '' || status === statusFilter;
        
        row.style.display = matchesSearch && matchesStatus ? '' : 'none';
    });
}
</script>

<?php include('templates/footer.php'); ?>

</body>
</html>