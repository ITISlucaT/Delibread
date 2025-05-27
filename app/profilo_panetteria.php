<?php 
session_start();

if (!isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit();
}

include 'conf/db_config.php'; 
include 'templates/header.php';  

$id_utente = $_SESSION['IdUtente'];

$stmt = $conn ->prepare("SELECT IdPanetteria FROM Utente WHERE IdUtente = ?");
$stmt -> bind_param("i", $id_utente);
$stmt -> execute();
$result = $stmt ->get_result();
$row = $result->fetch_assoc();

$IdPanetteria = $row['IdPanetteria'];

$stmt = $conn ->prepare("SELECT Nome, PIva,RagioneSociale,Via,Citta,CAP,NCiv,Provincia,Regione,OrarioLimiteOrdine,Logo FROM Panetteria WHERE IdPanetteria = ?");
$stmt -> bind_param("i", $IdPanetteria);
$stmt -> execute();
$result = $stmt ->get_result();
$row = $result->fetch_assoc();

$panetteria = [     
    'IdPanetteria' => $IdPanetteria,
    'Nome' => $row['Nome'],     
    'PIva' => $row['PIva'],     
    'RagioneSociale' => $row['RagioneSociale'],     
    'Via' => $row['Via'],     
    'Citta' => $row['Citta'],
    'CAP' => $row['CAP'],
    'NCiv' => $row['NCiv'],
    'Provincia' => $row['Provincia'],
    'Regione' => $row['Regione'],
    'OrarioLimiteOrdine' => $row['OrarioLimiteOrdine'],
    'Logo' => $row['Logo']
]; 

// Calcola le iniziali per l'avatar
$iniziali = '';
$parole = explode(' ', $panetteria['Nome']);
foreach($parole as $parola) {
    if(strlen($parola) > 0) {
        $iniziali .= strtoupper($parola[0]);
    }
    if(strlen($iniziali) >= 2) break;
}
if(strlen($iniziali) < 2 && count($parole) > 0) {
    $iniziali = strtoupper(substr($parole[0], 0, 2));
}
?>

<style>
.gradient-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 2rem 2.5rem;
    margin: 0.5rem 0 1.5rem 4rem;
    border-radius: 24px;
    box-shadow: 0 12px 40px rgba(245, 158, 11, 0.25);
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

.profile-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 1px solid rgba(245, 158, 11, 0.1);
    margin: 0 0 2rem 0;
    backdrop-filter: blur(10px);
    position: relative;
    width: calc(100% - 1rem);
}

.profile-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
    border-radius: 20px 20px 0 0;
}

.profile-header {
    text-align: center;
    margin-bottom: 3rem;
    position: relative;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin: 0 auto 1.5rem auto;
    box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
    position: relative;
    overflow: hidden;
}

.profile-avatar::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0.05) 100%);
    pointer-events: none;
}

.profile-header h2 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    letter-spacing: -0.02em;
}

.profile-header .subtitle {
    font-size: 1.1rem;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 1rem;
}

.info-sections {
    display: grid;
    gap: 2rem;
    margin-bottom: 3rem;
}

.info-section {
    background: #f8fafc;
    border-radius: 16px;
    padding: 2rem;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.info-section::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 0 2px 2px 0;
}

.section-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-icon {
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    color: white;
    font-weight: 800;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.info-row:last-child {
    border-bottom: none;
}

.info-row:hover {
    background: rgba(245, 158, 11, 0.05);
    margin: 0 -1rem;
    padding: 1rem;
    border-radius: 8px;
}

.info-label {
    font-weight: 600;
    color: #64748b;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-label::before {
    content: '';
    width: 6px;
    height: 6px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 50%;
    display: inline-block;
}

.info-value {
    font-weight: 500;
    color: #1e293b;
    font-size: 0.95rem;
    text-align: right;
}

.info-value.highlight {
    color: #f59e0b;
    font-weight: 600;
}

.profile-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    padding: 1rem 2.5rem;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(245, 158, 11, 0.5);
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
}

.btn-secondary {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    color: #64748b;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.btn-secondary:hover {
    background: #e2e8f0;
    color: #475569;
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.client-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
}

.client-main {
    flex: 1;
    padding: 1.5rem 2rem 2rem 2rem;
    margin-left: 250px;
}

.content-body {
    display: flex;
    justify-content: center;
}

.content-body .profile-card {
    max-width: 900px;
    width: 100%;
}

@media (max-width: 992px) {
    .client-main {
        margin-left: 0;
        padding: 1rem;
    }
    
    .gradient-header {
        margin: 0.5rem 0 1.5rem 0;
        width: calc(100% - 1rem);
    }
}

@media (max-width: 768px) {
    .gradient-header {
        margin: 0.5rem 0 1.5rem 0;
        padding: 2rem 1.5rem;
        border-radius: 16px;
        width: calc(100% - 1rem);
    }
    
    .gradient-header h1 {
        font-size: 2rem;
    }
    
    .profile-card {
        padding: 2rem 1.5rem;
        border-radius: 12px;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        font-size: 2.5rem;
    }
    
    .profile-header h2 {
        font-size: 1.8rem;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 1rem 0;
    }
    
    .info-value {
        text-align: left;
        font-weight: 600;
        color: #f59e0b;
    }
    
    .profile-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        padding: 1rem;
    }

    .info-sections {
        gap: 1.5rem;
    }
    
    .info-section {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .gradient-header {
        padding: 1.5rem 1rem;
    }
    
    .profile-card {
        padding: 1.5rem 1rem;
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        font-size: 2rem;
    }
    
    .profile-header h2 {
        font-size: 1.6rem;
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
                        <h1 class="mb-1">🥖 Profilo Panetteria</h1>
                        <p class="mb-0">Informazioni complete della tua attività</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <section class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo $iniziali; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($panetteria['Nome']); ?></h2>
                    <p class="subtitle"><?php echo htmlspecialchars($panetteria['RagioneSociale']); ?></p>
                </div>
                
                <div class="info-sections">
                    <!-- Sezione Dati Fiscali -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="section-icon">💼</span>
                            Dati Fiscali
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Partita IVA</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['PIva']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ragione Sociale</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['RagioneSociale']); ?></span>
                        </div>
                    </div>

                    <!-- Sezione Indirizzo -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="section-icon">📍</span>
                            Indirizzo
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Via</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['Via'] . ', ' . $panetteria['NCiv']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Città</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['Citta']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">CAP</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['CAP']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Provincia</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['Provincia']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Regione</span>
                            <span class="info-value"><?php echo htmlspecialchars($panetteria['Regione']); ?></span>
                        </div>
                    </div>

                    <!-- Sezione Operativa -->
                    <div class="info-section">
                        <h3 class="section-title">
                            <span class="section-icon">⏰</span>
                            Informazioni Operative
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Orario Limite Ordine</span>
                            <span class="info-value highlight"><?php echo date('H:i', strtotime($panetteria['OrarioLimiteOrdine'])); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Logo</span>
                            <span class="info-value">
                                <?php echo $panetteria['Logo'] ? 'Presente' : 'Non caricato'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="btn-primary">Modifica Informazioni</button>
                    <button class="btn-secondary">Gestisci Logo</button>
                    <button class="btn-secondary">Orari Apertura</button>
                </div>
            </section>
        </div>
    </main>
</div>

<?php include 'templates/footer.php'; ?>