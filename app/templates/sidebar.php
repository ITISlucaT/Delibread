<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>

<style>
.sidebar-panetteria {
    width: 280px;
    height: 100vh;
    background: linear-gradient(180deg, #8b4513 0%, #d4a574 100%);
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 4px 0 20px rgba(0,0,0,0.1);
    overflow-y: auto;
    overflow-x: hidden;
}

/* Personalizza scrollbar per webkit browsers */
.sidebar-panetteria::-webkit-scrollbar {
    width: 6px;
}

.sidebar-panetteria::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.sidebar-panetteria::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.sidebar-panetteria::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

.sidebar-panetteria .logo-container {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(10px);
}

.sidebar-panetteria .logo-container h5 {
    color: white;
    font-weight: 700;
    margin: 0;
}

.sidebar-panetteria .logo-container small {
    color: rgba(255,255,255,0.8);
    font-size: 0.85rem;
}

.sidebar-content {
    /* Rimosso padding-bottom non più necessario */
}

.sidebar-panetteria .menu-section {
    padding: 1.5rem 0;
}

.sidebar-panetteria .menu-section-title {
    color: rgba(255,255,255,0.7);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 0 1.5rem;
    margin-bottom: 1rem;
}

.sidebar-panetteria .menu-link {
    color: rgba(255,255,255,0.9);
    padding: 0.75rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
    font-weight: 500;
    position: relative;
}

.sidebar-panetteria .menu-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    border-left-color: #f8f9fa;
    transform: translateX(3px);
}

.sidebar-panetteria .menu-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    border-left-color: #fff;
    font-weight: 600;
}

.sidebar-panetteria .menu-link i {
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
}

/* Dropdown styles - FIXED */
.dropdown-menu-wrapper {
    position: relative;
}

.dropdown-submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, opacity 0.3s ease;
    opacity: 0;
    background: rgba(0,0,0,0.1);
}

.dropdown-menu-wrapper.active .dropdown-submenu {
    max-height: 300px;
    opacity: 1;
}

.dropdown-menu-wrapper.active .dropdown-icon {
    transform: rotate(180deg);
}

.dropdown-submenu .submenu-item {
    padding: 0.6rem 1.5rem 0.6rem 3rem;
    font-size: 0.9rem;
    border-left: 2px solid rgba(255,255,255,0.1);
    margin-left: 1rem;
}

.dropdown-submenu .submenu-item:hover {
    background: rgba(255,255,255,0.08);
    transform: translateX(2px);
}

.dropdown-submenu .submenu-item.active {
    background: rgba(255,255,255,0.12);
    border-left-color: #fff;
}

.dropdown-toggle {
    position: relative;
    cursor: pointer;
    user-select: none;
}

.dropdown-icon {
    transition: transform 0.3s ease;
    font-size: 0.8rem;
    margin-left: auto;
    position: absolute;
    right: 1.5rem;
}

/* Nasconde eventuali altre frecce di Bootstrap */
.dropdown-toggle::after {
    display: none !important;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .sidebar-panetteria {
        width: 100%;
        height: auto;
        position: relative;
        transform: translateX(-100%);
    }
    
    .sidebar-panetteria.show {
        transform: translateX(0);
    }
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Prevent horizontal scroll */
.sidebar-panetteria * {
    box-sizing: border-box;
}
</style>

<div class="sidebar-panetteria">
    <div class="logo-container">             
        <div class="d-flex align-items-center gap-3">                 
            <img src="assets/logo.png" alt="Logo" class="rounded-circle" style="width: 50px; height: 50px;">                 
            <h5 class="mb-0">Delibread</h5>             
        </div>         
    </div>  

    <div class="sidebar-content">
        <div class="menu-section">
            <div class="menu-section-title">Dashboard</div>
            <nav class="nav flex-column">
                <a href="dashboard_panetteria.php" class="menu-link <?= ($current_page == 'dashboard_panetteria') ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    Resoconto Panetteria
                </a>
            </nav>
        </div>

        <div class="menu-section">
            <div class="menu-section-title">Gestione Ordini</div>
            <nav class="nav flex-column">
                <a href="ordini_preparare.php" class="menu-link <?= ($current_page == 'ordini_preparare') ? 'active' : '' ?>">
                    <i class="bi bi-list-task"></i>
                    Ordini da Preparare
                </a>
                <a href="prodotti_ordine.php" class="menu-link <?= ($current_page == 'prodotti_ordine') ? 'active' : '' ?>">
                    <i class="bi bi-list-task"></i>
                        Alimenti da produrre
                    </a>
                <a href="programma_giorno.php" class="menu-link <?= ($current_page == 'programma_giorno') ? 'active' : '' ?>">
                    <i class="bi bi-calendar-day"></i>
                    Programma del Giorno
                </a>
                <a href="ordini_risolti.php" class="menu-link <?= ($current_page == 'ordini_risolti') ? 'active' : '' ?>">
                    <i class="bi bi-check2-square"></i>
                    Ordini Risolti
                </a>
            </nav>
        </div>

        <!-- Cataloghi Dropdown -->
        <div class="menu-section">
            <div class="menu-section-title">Cataloghi</div>
            <nav class="nav flex-column">
                <div class="dropdown-menu-wrapper">
                    <a href="#" class="menu-link dropdown-toggle">
                        <i class="bi bi-journal-bookmark"></i>
                        Gestione Cataloghi
                        <i class="bi bi-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="dropdown-submenu">
                        <a href="gestione_cataloghi.php" class="menu-link submenu-item <?= ($current_page == 'gestione_cataloghi') ? 'active' : '' ?>">
                            <i class="bi bi-folder"></i>
                            Gestisci Cataloghi
                        </a>
                        <a href="aggiungi_prodotto.php" class="menu-link submenu-item <?= ($current_page == 'aggiungi_prodotto') ? 'active' : '' ?>">
                            <i class="bi bi-plus-circle"></i>
                            Aggiungi Prodotto
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Consegne Dropdown -->
        <div class="menu-section">
            <div class="menu-section-title">Consegne</div>
            <nav class="nav flex-column">
                <div class="dropdown-menu-wrapper">
                    <a href="#" class="menu-link dropdown-toggle">
                        <i class="bi bi-truck"></i>
                        Gestione Consegne
                        <i class="bi bi-chevron-down dropdown-icon"></i>
                    </a>
                    <div class="dropdown-submenu">
                        <a href="visualizza_consegne.php" class="menu-link submenu-item <?= ($current_page == 'visualizza_consegne') ? 'active' : '' ?>">
                            <i class="bi bi-eye"></i>
                            Visualizza Consegne
                        </a>
                        <a href="traccia_consegne.php" class="menu-link submenu-item <?= ($current_page == 'traccia_consegne') ? 'active' : '' ?>">
                            <i class="bi bi-geo-alt"></i>
                            Traccia Consegne
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Impostazioni -->
        <div class="menu-section">
            <div class="menu-section-title">Impostazioni</div>
            <nav class="nav flex-column">
                <a href="profilo_panetteria.php" class="menu-link <?= ($current_page == 'profilo_panetteria') ? 'active' : '' ?>">
                    <i class="bi bi-person-gear"></i>
                    Profilo Panetteria
                </a>
            </nav>
        </div>
    </div>
</div>

<script>
// Gestione dropdown menu - MIGLIORATO
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest('.dropdown-menu-wrapper');
            const isActive = parent.classList.contains('active');
            
            // Chiudi tutti gli altri dropdown
            document.querySelectorAll('.dropdown-menu-wrapper.active').forEach(wrapper => {
                if (wrapper !== parent) {
                    wrapper.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            parent.classList.toggle('active', !isActive);
        });
    });
    
    // Chiudi dropdown quando si clicca fuori
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu-wrapper')) {
            document.querySelectorAll('.dropdown-menu-wrapper.active').forEach(wrapper => {
                wrapper.classList.remove('active');
            });
        }
    });
    
    // Mantieni dropdown aperto se la pagina corrente è nel submenu
    const currentPage = '<?= $current_page ?>';
    const submenuLinks = document.querySelectorAll('.submenu-item');
    
    submenuLinks.forEach(link => {
        if (link.classList.contains('active')) {
            const dropdown = link.closest('.dropdown-menu-wrapper');
            if (dropdown) {
                dropdown.classList.add('active');
            }
        }
    });
});
</script>