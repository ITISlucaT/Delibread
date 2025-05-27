<!DOCTYPE html> 
<html lang="it"> 
<head>     
    <meta charset="UTF-8">     
    <title>Menu Laterale</title>     
    <!-- Bootstrap CSS -->     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">     
    <style>         
        .sidebar {             
            width: 280px;             
            height: 100vh;             
            background: #f8f9fa;             
            border-right: 1px solid #dee2e6;             
            transition: all 0.3s;         
        }          
        
        .menu-link {             
            color: #212529;             
            padding: 12px 20px;             
            border-radius: 8px;             
            transition: all 0.2s;             
            display: flex;             
            align-items: center;             
            gap: 12px;             
            text-decoration: none;         
        }          
        
        .menu-link:hover {             
            background: #e9ecef;             
            transform: translateX(5px);             
            color: #0d6efd;         
        }          
        
        .menu-link.active {             
            background: #e9ecef;             
            border-left: 4px solid #0d6efd;             
            font-weight: 500;         
        }          
        
        .logo-container {             
            padding: 1.5rem;             
            border-bottom: 1px solid #dee2e6;         
        }     
    </style>     
    <!-- Icone Bootstrap -->     
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> 
</head> 
<body>     
    <div class="sidebar position-fixed vh-100">         
        <div class="logo-container">             
            <div class="d-flex align-items-center gap-3">                 
                <img src="logo.png" alt="Logo" class="rounded-circle" style="width: 50px; height: 50px;">                 
                <h5 class="mb-0">Delibread</h5>             
            </div>         
        </div>          
        
        <div class="p-3">             
            <nav class="nav flex-column gap-2">                 
                <a href="profilo.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'profilo') ? 'active' : ''; ?>">                     
                    <i class="bi bi-person fs-5"></i>                     
                    Profilo                 
                </a>                                  
                
                <a href="ordini.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'ordini') ? 'active' : ''; ?>">                     
                    <i class="bi bi-cart fs-5"></i>                     
                    Effettua Ordini                 
                </a>                  
                
                <a href="ordini_ricorrenti.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'ordini_ricorrenti') ? 'active' : ''; ?>">                     
                    <i class="bi bi-arrow-repeat fs-5"></i>                     
                    Ordini Ricorrenti                 
                </a>                  
                
                <a href="storico.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'storico') ? 'active' : ''; ?>">                     
                    <i class="bi bi-clock-history fs-5"></i>                     
                    Storico Ordini                 
                </a>                  
                
                <a href="prodotti.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'prodotti') ? 'active' : ''; ?>">                     
                    <i class="bi bi-basket fs-5"></i>                     
                    Prodotti                 
                </a>                  
                
                <a href="impostazioni.php" class="menu-link <?php echo (isset($current_page) && $current_page == 'impostazioni') ? 'active' : ''; ?>">                     
                    <i class="bi bi-gear fs-5"></i>                     
                    Impostazioni                 
                </a>             
            </nav>         
        </div>     
    </div>      
    
    <!-- Bootstrap JS -->     
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> 
</body> 
</html>
