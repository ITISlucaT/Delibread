<?php 
include 'templates/header.php'; 
require_once 'conf/db_config.php';
session_start();

// Controlla se l'utente è già loggato
if (isset($_SESSION['IdUtente'])) {
    if ($_SESSION['Tipo'] == 'Panettiere') {
        header("Location: dashboard_panetteria.php");
    } else {
        header("Location: ordini.php");
    }
    exit();
}

// Gestione del form di login
if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica connessione database
    if (!$conn) {
        die("Errore di connessione al database");
    }

    $stmt = $conn->prepare("SELECT * FROM Utente WHERE Email = ? AND pwd = ?");
    
    if (!$stmt) {
        die("Errore nella preparazione della query: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {

        // LOGIN RIUSCITO - Imposta le variabili di sessione
        $_SESSION['IdUtente'] = $row['IdUtente'];
        $_SESSION['Tipo'] = $row['Tipo'];
        $_SESSION['Nome'] = $row['Nome'];
        $_SESSION['Cognome'] = $row['Cognome'];
        
        // Redirect in base al tipo di utente
        if ($row['Tipo'] == 'Panettiere') {
           header("Location: dashboard_panetteria.php");
        } else {
            header("Location: ordini.php");
        }
        exit();
    } else {
        // LOGIN FALLITO
        $error_message = "Email o password errati.";
    }
}


?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <img src="assets/logo.png" alt="DeliBread Logo" class="logo">
            <h1>Accedi a DeliBread</h1>
        </div>

        <form action="index.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>


            <button type="submit" class="btn-primary">Accedi</button>
        </form>

        <div class="auth-footer">
            <p>Non hai un account? <a href="register.php">Registrati come utente</a> o <a href="register_panetteria.php">Registra la tua panetteria</a></p>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>