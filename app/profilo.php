<?php 
session_start(); 
include 'conf/db_config.php'; 
include 'templates/header.php';  

// Dati utente di esempio (sostituire con query al DB) 
$user = [     
    'nome' => 'Mario',     
    'cognome' => 'Rossi',     
    'email' => 'mario.rossi@example.com',     
    'telefono' => '1234567890',     
    'indirizzo' => 'Via Roma 123, Milano' 
]; 
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

.profile-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 1px solid rgba(102, 126, 234, 0.1);
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
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin: 0 auto 1.5rem auto;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
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
    margin: 0;
    letter-spacing: -0.02em;
}

.profile-info {
    margin-bottom: 3rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    margin-bottom: 0.75rem;
    background: #f8fafc;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.info-row:hover {
    background: #f1f5f9;
    border-color: rgba(102, 126, 234, 0.2);
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.info-row::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 0 2px 2px 0;
}

.info-label {
    font-weight: 600;
    color: #64748b;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-label::before {
    content: '';
    width: 8px;
    height: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: inline-block;
}

.info-value {
    font-weight: 500;
    color: #1e293b;
    font-size: 1rem;
    text-align: right;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
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
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
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
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.client-main {
    flex: 1;
    padding: 1.5rem 2rem 2rem 2rem;
    margin-left: 250px; /* Larghezza della sidebar */
}

.content-body {
    display: flex;
    justify-content: center;
}

.content-body .profile-card {
    max-width: 800px;
    width: 100%;
}

/* Icone per i diversi campi */
.info-row:nth-child(1) .info-label::before {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.info-row:nth-child(2) .info-label::before {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.info-row:nth-child(3) .info-label::before {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
        padding: 1.25rem 1.5rem;
    }
    
    .info-value {
        text-align: left;
        font-weight: 600;
        color: #667eea;
    }
    
    .profile-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-primary, .btn-secondary {
        width: 100%;
        padding: 1rem;
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
                        <h1 class="mb-1">👤 Il tuo profilo</h1>
                        <p class="mb-0">Gestisci le tue informazioni personali</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-body">
            <section class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php
                        $iniziali = strtoupper(substr($user['nome'], 0, 1) . substr($user['cognome'], 0, 1));
                        echo $iniziali;
                        ?>
                    </div>
                    <h2><?php echo htmlspecialchars($user['nome'] . ' ' . $user['cognome']); ?></h2>
                </div>
                
                <div class="profile-info">
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Telefono</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['telefono']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Indirizzo</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['indirizzo']); ?></span>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <button class="btn-primary">Modifica profilo</button>
                    <button class="btn-secondary">Cambia password</button>
                </div>
            </section>
        </div>
    </main>
</div>

<?php include 'templates/footer.php'; ?>