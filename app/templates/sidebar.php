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
}

.sidebar-panetteria .logo-container {
    padding: 2rem 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.1);
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

.sidebar-panetteria .user-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 1.5rem;
    background: rgba(0,0,0,0.2);
    border-top: 1px solid rgba(255,255,255,0.1);
}

.sidebar-panetteria .user-avatar {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.1rem;
}

.sidebar-panetteria .user-details h6 {
    color: white;
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.sidebar-panetteria .user-details small {
    color: rgba(255,255,255,0.7);
    font-size: 0.75rem;
}

.logout-btn {
    color: rgba(255,255,255,0.8);
    font-size: 0.9rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 0.5rem;
    transition: color 0.3s ease;
}

.logout-btn:hover {
    color: #ff6b6b;
}

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
</style>

<div class="sidebar-panetteria">
    <div class="logo-container">
        <!-- ... existing logo code ... -->
    </div>

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
                    <i class="bi bi-chevron-down ms-auto dropdown-icon"></i>
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
                    <i class="bi bi-chevron-down ms-auto dropdown-icon"></i>
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

    <!-- ... remaining sections ... -->
</div>

<style>
/* Aggiungi questi stili CSS */
.dropdown-menu-wrapper {
    position: relative;
}

.dropdown-submenu {
    max-height: 0;
    overflow: hidden;
    transition: all 0.3s ease;
    padding-left: 2rem;
}

.dropdown-menu-wrapper.active .dropdown-submenu {
    max-height: 300px;
}

.dropdown-menu-wrapper.active .dropdown-icon {
    transform: rotate(180deg);
}

.dropdown-submenu .submenu-item {
    padding: 0.6rem 1.5rem;
    font-size: 0.9rem;
    border-left: 2px solid rgba(255,255,255,0.1);
}

.dropdown-toggle {
    position: relative;
    cursor: pointer;
}

.dropdown-icon {
    transition: transform 0.3s ease;
    font-size: 0.8rem;
}
</style>

<script>
// Aggiungi questo JavaScript
document.querySelectorAll('.dropdown-toggle').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const parent = this.closest('.dropdown-menu-wrapper');
        parent.classList.toggle('active');
    });
});
</script>