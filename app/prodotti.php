<?php
session_start();

if (!isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit();
}

include 'conf/db_config.php';
include 'templates/header.php';

// Dati utente di esempio
$user = [
    'nome' => 'Mario',
    'cognome' => 'Rossi'
];

$current_page = 'prodotti';

// Parametri per la paginazione e filtri
$records_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filtri
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_temporaneo = isset($_GET['temporaneo']) ? $_GET['temporaneo'] : '';
$filter_scadenza = isset($_GET['scadenza']) ? $_GET['scadenza'] : '';

// Costruzione query con filtri
$where_conditions = [];
$search_param = "";
$temporaneo_param = "";

if (!empty($search)) {
    $where_conditions[] = "(Nome LIKE '%$search%' OR DataInserimento LIKE '%$search%')";
}

if ($filter_temporaneo !== '') {
    $where_conditions[] = "Temporaneo = $filter_temporaneo";
}

if (!empty($filter_scadenza)) {
    if ($filter_scadenza === 'scaduti') {
        $where_conditions[] = "DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) < CURDATE()";
    } elseif ($filter_scadenza === 'scadenza_vicina') {
        $where_conditions[] = "DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    } elseif ($filter_scadenza === 'validi') {
        $where_conditions[] = "DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) > DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
    }
}

$where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Query per contare il totale dei record
$count_sql = "SELECT COUNT(*) as total FROM prodotto $where_sql";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Query principale per i dati
$sql = "SELECT 
    IdProdotto,
    Quantità,
    DataInserimento,
    Nome,
    Temporaneo,
    Durata,
    GiorniDiScadenza,
    Img,
    DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) as DataScadenza,
    CASE 
        WHEN DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) < CURDATE() THEN 'scaduto'
        WHEN DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'scadenza_vicina'
        ELSE 'valido'
    END as StatoScadenza
FROM prodotto 
$where_sql 
ORDER BY DataInserimento DESC 
LIMIT $records_per_page OFFSET $offset";

$result = $conn->query($sql);
$prodotti = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $prodotti[] = $row;
    }
}

// Query per le statistiche
$scaduti_result = $conn->query("SELECT COUNT(*) as count FROM prodotto WHERE DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) < CURDATE()");
$scaduti_count = $scaduti_result->fetch_assoc()['count'];

$vicini_result = $conn->query("SELECT COUNT(*) as count FROM prodotto WHERE DATE_ADD(DataInserimento, INTERVAL GiorniDiScadenza DAY) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)");
$vicini_count = $vicini_result->fetch_assoc()['count'];

$temporanei_result = $conn->query("SELECT COUNT(*) as count FROM prodotto WHERE Temporaneo = 1");
$temporanei_count = $temporanei_result->fetch_assoc()['count'];
?>

<style>
.gradient-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 2.5rem;
    margin: 0.5rem 0 1.5rem 4rem;
    border-radius: 24px;
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.25);
    position: relative;
    overflow: hidden;
    width: calc(100% - 5rem);
}

.gradient-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
    pointer-events: none;
}

.gradient-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    pointer-events: none;
    animation: shimmer 6s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0.3; transform: rotate(0deg) scale(1); }
    50% { opacity: 0.6; transform: rotate(180deg) scale(1.1); }
}

.gradient-header .container {
    position: relative;
    z-index: 1;
}

.gradient-header h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
    letter-spacing: -0.02em;
}

.gradient-header p {
    font-size: 1.1rem;
    opacity: 0.95;
    font-weight: 400;
    text-shadow: 0 1px 5px rgba(0,0,0,0.1);
}

.products-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
    margin: 0 0 2rem 0;
    backdrop-filter: blur(10px);
    position: relative;
    width: 100%;
}

.products-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
}

.filters-section {
    background: #f8fafc;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.form-select, .form-control {
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-select:focus, .form-control:focus {
    border-color: #4f70ff;
    box-shadow: 0 0 0 4px rgba(79, 112, 255, 0.1);
    outline: none;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary-custom {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    color: #64748b;
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.btn-secondary-custom:hover {
    background: #e2e8f0;
    color: #475569;
    transform: translateY(-1px);
}

.products-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.products-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.products-table th {
    color: white;
    padding: 1.25rem 1rem;
    font-weight: 700;
    text-align: left;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
}

.products-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
    font-size: 0.9rem;
}

.products-table tr:last-child td {
    border-bottom: none;
}

.products-table tbody tr:nth-child(even) {
    background-color: #f9fafb;
}

.products-table tbody tr:hover {
    background-color: #f3f4f6;
    transform: scale(1.01);
    transition: all 0.2s ease;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.status-badge.valido {
    background: linear-gradient(135deg, #e8f5e8 0%, #a5d6a7 20%);
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.status-badge.scadenza_vicina {
    background: linear-gradient(135deg, #fff3e0 0%, #ffcc80 20%);
    color: #f57c00;
    border: 1px solid #ffcc80;
}

.status-badge.scaduto {
    background: linear-gradient(135deg, #ffebee 0%, #ef9a9a 20%);
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.temporaneo-badge {
    background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 20%);
    color: #1565c0;
    border: 1px solid #90caf9;
}

.permanente-badge {
    background: linear-gradient(135deg, #f3e5f5 0%, #ce93d8 20%);
    color: #7b1fa2;
    border: 1px solid #ce93d8;
}

.product-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #e2e8f0;
}

.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 2rem;
    gap: 0.5rem;
}

.pagination-btn {
    padding: 0.5rem 1rem;
    border: 2px solid #e2e8f0;
    background: white;
    color: #64748b;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination-btn:hover {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.client-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.client-main {
    flex: 1;
    padding: 1.5rem 2rem 2rem 2rem;
    margin-left: 250px;
}

.stats-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    flex: 1;
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border-left: 4px solid #667eea;
}

.stat-number {
    font-size: 2rem;
    font-weight: 800;
    color: #667eea;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #64748b;
    font-size: 0.9rem;
    font-weight: 600;
}

@media (max-width: 992px) {
    .client-main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .gradient-header {
        margin: 0.5rem 0 1.5rem 0;
        width: 100%;
    }
    
    .stats-row {
        flex-direction: column;
    }
    
    .container-fluid.px-4 {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .col-lg-10.col-xl-8 {
        max-width: 100%;
        flex: 0 0 100%;
    }
}

@media (max-width: 768px) {
    .products-table {
        font-size: 0.8rem;
    }
    
    .products-table th,
    .products-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .product-image {
        width: 40px;
        height: 40px;
    }
}
</style>

<div class="client-container">
    <?php include 'templates/client_nav.php'; ?>
    
    <main class="client-main">
        <!-- Header con gradiente arrotondato -->
        <div class="gradient-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h1 class="mb-1">🍞 Gestione Prodotti</h1>
                        <p class="mb-0">Visualizza e cerca tutti i prodotti</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-4">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <div class="products-card">
                        <!-- Filtri -->
                        <div class="filters-section">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">🔍 Cerca prodotto</label>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Nome prodotto o data..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">⏰ Tipologia</label>
                                    <select name="temporaneo" class="form-select">
                                        <option value="">Tutti</option>
                                        <option value="1" <?php echo $filter_temporaneo === '1' ? 'selected' : ''; ?>>Temporanei</option>
                                        <option value="0" <?php echo $filter_temporaneo === '0' ? 'selected' : ''; ?>>Permanenti</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary-custom w-100">Filtra</button>
                                </div>
                            </form>
                            <?php if (!empty($search) || $filter_temporaneo !== '' || !empty($filter_scadenza)): ?>
                                <div class="mt-3">
                                    <a href="?" class="btn btn-secondary-custom">Rimuovi Filtri</a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Tabella Prodotti -->
                        <div class="table-responsive">
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Immagine</th>
                                        <th>Nome Prodotto</th>
                                        <th>Tipologia</th>
                                        <th>Durata (gg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($prodotti)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <em>Nessun prodotto trovato con i filtri selezionati</em>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($prodotti as $prodotto): ?>
                                            <tr>
                                                <td><strong>#<?php echo $prodotto['IdProdotto']; ?></strong></td>
                                                <td>
                                                    <?php if (!empty($prodotto['Img'])): ?>
                                                        <img src="<?php echo htmlspecialchars($prodotto['Img']); ?>"
                                                             alt="<?php echo htmlspecialchars($prodotto['Nome']); ?>"
                                                             class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image d-flex align-items-center justify-content-center bg-light">
                                                            🍞
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($prodotto['Nome']); ?></strong>
                                                </td>
                                                
                                                <td>
                                                    <span class="status-badge <?php echo $prodotto['Temporaneo'] ? 'temporaneo-badge' : 'permanente-badge'; ?>">
                                                        <?php echo $prodotto['Temporaneo'] ? 'Temporaneo' : 'Permanente'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $prodotto['GiorniDiScadenza']; ?> giorni
                                                </td>
                                
                                                
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginazione -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-container">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&temporaneo=<?php echo $filter_temporaneo; ?>&scadenza=<?php echo $filter_scadenza; ?>" 
                                       class="pagination-btn">« Precedente</a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&temporaneo=<?php echo $filter_temporaneo; ?>&scadenza=<?php echo $filter_scadenza; ?>" 
                                       class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&temporaneo=<?php echo $filter_temporaneo; ?>&scadenza=<?php echo $filter_scadenza; ?>" 
                                       class="pagination-btn">Successiva »</a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    Pagina <?php echo $page; ?> di <?php echo $total_pages; ?> 
                                    (<?php echo $total_records; ?> prodotti totali)
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'templates/footer.php'; ?>