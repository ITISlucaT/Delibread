<?php
include 'conf/db_config.php'; 
session_start();
if (!isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit();
}

$idUtente = $_SESSION['IdUtente'];

$query = "SELECT o.IdOrdine, o.DataCreazione, o.DataConsegna, o.Stato, o.Note, p.Nome AS NomePanetteria
          FROM Ordine o
          LEFT JOIN Panetteria p ON o.IdPanetteria = p.IdPanetteria
          WHERE o.IdUtente = ?
          ORDER BY o.DataCreazione DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();

$ordini = [];
while ($row = $result->fetch_assoc()) {
    $ordini[] = $row;
}

$stmt->close();
$conn->close();

include 'templates/header.php';
?>

<!-- CSS personalizzato -->
<style>
@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.client-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f1f3f4 0%, #e8eaf6 100%);
}

.client-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
}

.content-header {
    background: linear-gradient(-45deg, #7b1fa2, #9c27b0, #673ab7, #512da8);
    background-size: 400% 400%;
    animation: gradient-shift 8s ease infinite;
    color: white;
    padding: 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    box-shadow: 0 12px 35px rgba(123, 31, 162, 0.4);
    position: relative;
    overflow: hidden;
}

.content-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/><circle cx="25" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.content-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    position: relative;
    z-index: 1;
}

.content-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.1rem;
    position: relative;
    z-index: 1;
}

.content-header .bi-clock-history {
    animation: float 3s ease-in-out infinite;
}

.order-card {
    background: white;
    border: none;
    border-radius: 18px;
    box-shadow: 0 8px 30px rgba(123, 31, 162, 0.12);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.4s ease;
    border-left: 4px solid transparent;
}

.order-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 45px rgba(123, 31, 162, 0.2);
    border-left-color: #9c27b0;
}

.order-header {
    background: linear-gradient(135deg, #26a69a 0%, #00bcd4 100%);
    color: white;
    padding: 1.5rem;
}

.order-body {
    padding: 1.5rem;
}

.order-footer {
    background: #fafafa;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e0e0e0;
}

.badge-status {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
}

.badge-status.completato {
    background-color: #e8f5e8;
    color: #2e7d32;
    border: 1px solid #4caf50;
}

.badge-status.in-elaborazione {
    background-color: #fff8e1;
    color: #f57c00;
    border: 1px solid #ff9800;
}

.badge-status.annullato {
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #f44336;
}

.content-body h2 {
    color: #4a148c;
}

.btn-outline-primary {
    border-color: #9c27b0;
    color: #9c27b0;
}

.btn-outline-primary:hover {
    background-color: #9c27b0;
    border-color: #9c27b0;
}

.btn-outline-success {
    border-color: #26a69a;
    color: #26a69a;
}

.btn-outline-success:hover {
    background-color: #26a69a;
    border-color: #26a69a;
}

.btn-primary {
    background: linear-gradient(135deg, #7b1fa2, #9c27b0);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #6a1b9a, #8e24aa);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(123, 31, 162, 0.3);
}

.text-primary {
    color: #7b1fa2 !important;
}

@media (max-width: 768px) {
    .client-main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 2rem;
    }
    
    .content-header {
        border-radius: 15px;
    }
}
</style>

<!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="client-container">     
    <?php include 'templates/client_nav.php'; ?>          
    
    <main class="client-main">         
        <div class="content-header">             
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-clock-history me-3" style="font-size: 2rem;"></i>
                <h1 class="mb-0">Storico Ordini</h1>
            </div>
            <p class="mb-0">Visualizza tutti i tuoi ordini precedenti</p>         
        </div>
        
        <div class="content-body">
            <?php if (count($ordini) > 0): ?>
                <!-- Header sezione -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-dark">
                        <i class="bi bi-list-check me-2"></i>
                        I tuoi ordini effettuati
                    </h2>
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control" placeholder="Cerca ordini..." style="width: 250px;">
                        <button class="btn btn-outline-secondary">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Lista ordini -->
                <?php foreach ($ordini as $ordine): ?>
                    <div class="card order-card">
                        <div class="order-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1">
                                        <i class="bi bi-basket me-2"></i>
                                        Ordine #<?php echo $ordine['IdOrdine']; ?>
                                    </h4>
                                    <h5 class="mb-0 opacity-75"><?php echo htmlspecialchars($ordine['NomePanetteria']); ?></h5>
                                </div>
                                <span class="badge-status <?php echo strtolower(str_replace(' ', '-', $ordine['Stato'])); ?>">
                                    <?php echo $ordine['Stato']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="order-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-calendar me-2 text-primary"></i>
                                        Dettagli ordine:
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-1">
                                            <i class="bi bi-dot"></i>
                                            <strong>Creato il:</strong> <?php echo date("d/m/Y H:i", strtotime($ordine['DataCreazione'])); ?>
                                        </li>
                                        <li class="mb-1">
                                            <i class="bi bi-dot"></i>
                                            <strong>Consegna prevista:</strong> <?php echo date("d/m/Y", strtotime($ordine['DataConsegna'])); ?>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <?php if (!empty($ordine['Note'])): ?>
                                        <h6 class="fw-bold mb-2">
                                            <i class="bi bi-chat-left-text me-2 text-primary"></i>
                                            Note:
                                        </h6>
                                        <p><?php echo nl2br(htmlspecialchars($ordine['Note'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="order-footer">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-outline-primary me-2">
                                    <i class="bi bi-eye me-1"></i>
                                    Dettagli
                                </button>
                                <button class="btn btn-outline-success">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    Ripeti ordine
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card border-2 border-dashed" style="border-color: #dee2e6;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-basket display-1 text-muted mb-3"></i>
                        <h3 class="text-muted mb-3">Nessun ordine effettuato</h3>
                        <p class="text-muted mb-4">Non hai ancora effettuato ordini con il tuo account</p>
                        <a href="ordini.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-plus me-2"></i>
                            Effettua il primo ordine
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'templates/footer.php'; ?>