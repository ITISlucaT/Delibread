<?php 
session_start(); 
include 'conf/db_config.php'; 
include 'templates/header.php';  

$user = [     
    'nome' => 'Mario',     
    'cognome' => 'Rossi' 
]; 

$current_page = 'ordini_ricorrenti';
?>  

<style>
.client-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.client-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
}

.content-header {
    background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
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
    background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
    color: white;
    padding: 1.5rem;
}

.order-header.monthly {
    background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
}

.order-header.weekly {
    background: linear-gradient(135deg, #e83e8c 0%, #6f42c1 100%);
}

.badge-frequency {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
}

.order-body {
    padding: 1.5rem;
}

.order-footer {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-top: 1px solid #dee2e6;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
}

.btn-outline-danger:hover {
    transform: translateY(-2px);
}

.stats-cards {
    margin-bottom: 2rem;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .client-main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 2rem;
    }
}
</style>

<!-- Include Bootstrap CSS se non già incluso -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="client-container">     
    <?php include 'templates/client_nav.php'; ?>          
    
    <main class="client-main">         
        <div class="content-header">             
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-arrow-repeat me-3" style="font-size: 2rem;"></i>
                <h1 class="mb-0">Ordini Ricorrenti</h1>
            </div>
            <p class="mb-0">Gestisci i tuoi ordini automatici e programmati</p>         
        </div>
        
        <!-- Statistiche con Bootstrap -->
        <div class="row stats-cards">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle-fill text-success stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold">2</h3>
                        <p class="card-text text-muted">Ordini Attivi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-euro text-warning stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold">€45.50</h3>
                        <p class="card-text text-muted">Valore Mensile</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check text-info stat-icon"></i>
                        <h3 class="card-title text-primary fw-bold">6</h3>
                        <p class="card-text text-muted">Prossime Consegne</p>
                    </div>
                </div>
            </div>
        </div>                 
        
        <div class="content-body">     
            <!-- Header sezione -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">
                    <i class="bi bi-list-check me-2"></i>
                    I tuoi ordini programmati
                </h2>
                <button class="btn btn-primary btn-lg shadow">
                    <i class="bi bi-plus-circle me-2"></i>
                    Nuovo ordine ricorrente
                </button>
            </div>
                    
            <!-- Ordine 1 - Settimanale -->                     
            <div class="card order-card">
                <div class="order-header weekly">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-calendar-week me-2"></i>
                                Ordine Settimanale
                            </h4>
                            <h5 class="mb-0 opacity-75">Panetteria Mario</h5>
                        </div>
                        <span class="badge bg-light text-dark badge-frequency">
                            <i class="bi bi-clock me-1"></i>
                            Ogni Lunedì
                        </span>
                    </div>
                </div>
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-basket me-2 text-primary"></i>
                                Prodotti ordinati:
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>2 kg</strong> pane integrale
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>3x</strong> cornetti freschi
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>1x</strong> burro biologico
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <p class="mb-1">
                                    <i class="bi bi-currency-euro me-1"></i>
                                    <span class="fw-bold fs-5 text-success">€18.50</span>
                                </p>
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    Ore 8:00
                                </p>
                                <p class="mb-0 text-primary fw-bold">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Prossima: Lun 29 Mag
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-footer">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i>
                                Modifica
                            </button>
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>
                                Elimina
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Attivo
                            </span>
                        </div>
                    </div>
                </div>
            </div>
                    
            <!-- Ordine 2 - Mensile -->                     
            <div class="card order-card">
                <div class="order-header monthly">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bi bi-calendar-month me-2"></i>
                                Ordine Mensile
                            </h4>
                            <h5 class="mb-0 opacity-75">Pane e Vino</h5>
                        </div>
                        <span class="badge bg-light text-dark badge-frequency">
                            <i class="bi bi-calendar me-1"></i>
                            1° del mese
                        </span>
                    </div>
                </div>
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-basket me-2 text-primary"></i>
                                Prodotti ordinati:
                            </h6>
                            <ul class="list-unstyled">
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>5 kg</strong> pane di segale
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>10x</strong> brioche assortite
                                </li>
                                <li class="mb-1">
                                    <i class="bi bi-dot"></i>
                                    <strong>1x</strong> miele biologico (500g)
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <div class="text-end">
                                <p class="mb-1">
                                    <i class="bi bi-currency-euro me-1"></i>
                                    <span class="fw-bold fs-5 text-success">€27.00</span>
                                </p>
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    Ore 9:30
                                </p>
                                <p class="mb-0 text-primary fw-bold">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Prossima: Gio 1 Giu
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-footer">
                    <div class="d-flex justify-content-between">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i>
                                Modifica
                            </button>
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>
                                Elimina
                            </button>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Attivo
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card per aggiungere nuovo ordine -->
            <div class="card border-2 border-dashed" style="border-color: #dee2e6;">
                <div class="card-body text-center py-5">
                    <i class="bi bi-plus-circle display-1 text-muted mb-3"></i>
                    <h5 class="text-muted mb-3">Vuoi aggiungere un nuovo ordine ricorrente?</h5>
                    <button class="btn btn-outline-primary btn-lg">
                        <i class="bi bi-plus me-2"></i>
                        Crea nuovo ordine
                    </button>
                </div>
            </div>
        </div>     
    </main> 
</div>  

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'templates/footer.php'; ?>
