<?php
// Includi la configurazione del database
require_once 'conf/db_config.php';

// Variabili di default
$nomeUtente = '';
$cognomeUtente = '';
$tipoUtente = '';
$nomePanetteria = 'Dashboard Panetteria';
$cittaPanetteria = '';
$inizialeUtente = 'U';

// Controlla se l'utente è loggato
if (isset($_SESSION['IdUtente'])) {
    // Query per ottenere i dati dell'utente
    $query = "
        SELECT u.Nome, u.Cognome, u.Tipo, u.IdPanetteria, u.IdRivendita,
               p.Nome as NomePanetteria, p.Citta as CittaPanetteria,
               r.Nome as NomeRivendita, r.Citta as CittaRivendita
        FROM Utente u
        LEFT JOIN Panetteria p ON u.IdPanetteria = p.IdPanetteria
        LEFT JOIN Rivendita r ON u.IdRivendita = r.IdRivendita
        WHERE u.IdUtente = ? AND u.Attivo = TRUE
    ";
    
    // Prepara la query
    if ($stmt = $conn->prepare($query)) {
        // Bind del parametro
        $stmt->bind_param("i", $_SESSION['IdUtente']);
        
        // Esegui la query
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            if ($userData = $result->fetch_assoc()) {
                $nomeUtente = $userData['Nome'] ?? '';
                $cognomeUtente = $userData['Cognome'] ?? '';
                $tipoUtente = $userData['Tipo'] ?? '';
                $inizialeUtente = !empty($nomeUtente) ? strtoupper(substr($nomeUtente, 0, 1)) : 'U';
                
                // Determina se è una panetteria o rivendita
                if (!empty($userData['IdPanetteria']) && !empty($userData['NomePanetteria'])) {
                    $nomePanetteria = $userData['NomePanetteria'];
                    $cittaPanetteria = $userData['CittaPanetteria'] ?? '';
                } elseif (!empty($userData['IdRivendita']) && !empty($userData['NomeRivendita'])) {
                    $nomePanetteria = $userData['NomeRivendita'];
                    $cittaPanetteria = $userData['CittaRivendita'] ?? '';
                }
            }
        } else {
            // Log dell'errore
            error_log("Errore nell'esecuzione della query: " . $stmt->error);
        }
        
        // Chiudi lo statement
        $stmt->close();
    } else {
        // Log dell'errore nella preparazione della query
        error_log("Errore nella preparazione della query: " . $conn->error);
    }
}
?>

<header class="navbar navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">
        <!-- Sezione sinistra: Informazioni utente e panetteria -->
        <div class="d-flex align-items-center">
            <!-- Icona account con info utente -->
            <div class="d-flex align-items-center me-3">
                <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                     style="width: 40px; height: 40px; font-weight: bold;">
                    <?php echo htmlspecialchars($inizialeUtente); ?>
                </div>
                <div class="user-info">
                    <div class="user-name fw-semibold text-dark" style="font-size: 14px; line-height: 1.2;">
                        <?php echo htmlspecialchars(trim($nomeUtente . ' ' . $cognomeUtente)); ?>
                    </div>
                    <div class="user-role text-muted" style="font-size: 12px; line-height: 1.2;">
                        <?php echo htmlspecialchars($tipoUtente); ?>
                    </div>
                </div>
            </div>
            
            <!-- Separatore verticale e info panetteria (solo se esistono) -->
            <?php if (!empty($nomePanetteria) && $nomePanetteria !== 'Dashboard Panetteria'): ?>
            <div class="vr me-3" style="height: 40px;"></div>
            
            <!-- Informazioni panetteria/rivendita -->
            <div class="bakery-info">
                <div class="bakery-name text-dark fw-medium" style="font-size: 14px; line-height: 1.2;">
                    <i class="fas fa-store me-1 text-primary"></i>
                    <?php echo htmlspecialchars($nomePanetteria); ?>
                </div>
                <?php if (!empty($cittaPanetteria)): ?>
                <div class="bakery-location text-muted" style="font-size: 12px; line-height: 1.2;">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    <?php echo htmlspecialchars($cittaPanetteria); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Sezione destra: Logout -->
        <div class="d-flex align-items-center">
            <a href="functions/logout.php" 
               class="btn btn-outline-danger btn-sm d-flex align-items-center">
                <i class="fas fa-sign-out-alt me-1"></i>
                Logout
            </a>
        </div>
    </div>
</header>

<!-- CSS aggiuntivo per migliorare l'aspetto -->
<style>
.user-avatar {
    font-size: 16px;
    transition: all 0.2s ease;
    cursor: default;
}

.user-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.vr {
    opacity: 0.3;
    background-color: rgba(0,0,0,.1);
    width: 1px;
}

.bakery-info i {
    font-size: 12px;
    opacity: 0.7;
}

/* Assicura che l'header stia accanto alla sidebar e rimanga sticky */
header.navbar {
    margin-left: 0;
    position: sticky !important;
    top: 0;
    z-index: 1020;
    border-bottom: 1px solid rgba(0,0,0,.125);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

/* Layout con sidebar per desktop */
@media (min-width: 992px) {
    header.navbar {
        margin-left: 280px; /* Modifica questo valore con la larghezza della tua sidebar */
        width: calc(100% - 280px); /* Assicura che l'header abbia la larghezza corretta */
    }
}

/* Hover effects */
.user-info .user-name:hover {
    color: #0056b3 !important;
    transition: color 0.2s ease;
}

.bakery-info .bakery-name:hover {
    color: #0056b3 !important;
    transition: color 0.2s ease;
}

/* Responsive Design */
@media (max-width: 991px) {
    .bakery-info {
        display: none;
    }
    
    .vr {
        display: none;
    }
    
    header.navbar {
        margin-left: 0;
    }
    
    .user-info {
        display: none;
    }
    
    .user-avatar {
        margin-right: 0 !important;
    }
}

@media (max-width: 576px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }
    
    .user-avatar {
        width: 35px !important;
        height: 35px !important;
        font-size: 14px !important;
    }
}

/* Animazioni fluide */
.d-flex.align-items-center {
    transition: all 0.3s ease;
}

/* Stili per quando non ci sono dati */
.user-info .user-name:empty:before {
    content: "Utente";
    color: #6c757d;
    font-style: italic;
}

.user-info .user-role:empty:before {
    content: "Ruolo non definito";
    color: #6c757d;
    font-style: italic;
}
</style>

<!-- Font Awesome (se non già incluso nel tuo layout) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// Script per migliorare l'esperienza utente
document.addEventListener('DOMContentLoaded', function() {
    // Effetto hover sulla sezione utente
    const userSection = document.querySelector('.user-info');
    if (userSection) {
        userSection.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        userSection.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    }
    
    // Debug info (rimuovi in produzione)
    console.log('Header caricato per utente:', {
        nome: '<?php echo addslashes($nomeUtente); ?>',
        cognome: '<?php echo addslashes($cognomeUtente); ?>',
        tipo: '<?php echo addslashes($tipoUtente); ?>',
        panetteria: '<?php echo addslashes($nomePanetteria); ?>'
    });
});
</script>