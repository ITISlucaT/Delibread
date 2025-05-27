<?php
session_start();
include 'conf/db_config.php';
include 'templates/header.php';

// Dati utente di esempio
$user = [
    'nome' => 'Mario',
    'cognome' => 'Rossi'
];
?>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f5f5;
        margin: 0;
        padding: 0;
    }

    .client-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2rem;
    }

    .client-main {
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 2rem;
        width: 100%;
        max-width: 700px;
    }

    .content-header h1 {
        color: #ff6f00;
        text-align: center;
        margin-bottom: 1.5rem;
        font-size: 1.8rem;
    }

    .card {
        padding: 1.5rem;
        border-radius: 12px;
        background-color: #fffaf3;
        border: 1px solid #ffe0b2;
    }

    .card h2 {
        color: #ff6f00;
        font-size: 1.4rem;
        margin-bottom: 1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    select, textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 1rem;
        font-family: inherit;
        background-color: #fff;
        box-sizing: border-box;
    }

    textarea::placeholder {
        color: #999;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }

    .btn-primary {
        background-color: #ffa500;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #ff8c00;
    }

    .btn-secondary {
        background-color: #eeeeee;
        color: #333;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-secondary:hover {
        background-color: #ddd;
    }

    #responseBox {
        margin-top: 2rem;
        background: #e8f5e9;
        padding: 1rem;
        border-radius: 10px;
        border: 1px solid #c8e6c9;
        color: #2e7d32;
        font-family: monospace;
        white-space: pre-wrap;
    }
</style>

<div class="client-container">
    <?php include 'templates/client_nav.php'; ?>
    
    <main class="client-main">
        <div class="content-header">
            <h1>Effettua un ordine</h1>
        </div>
        
        <div class="content-body">
            <section class="card">
                <h2>Descrivi il tuo ordine</h2>
                <form id="orderForm" class="order-form">
                    <div class="form-group">
                        <label for="panetteria">Seleziona panetteria</label>
                        <select id="panetteria" name="panetteria" required>
                            <option value="">-- Seleziona --</option>
                            <option value="1">Panetteria Mario</option>
                            <option value="2">Pane e Vino</option>
                            <option value="3">Il Fornaio</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="ordine">Cosa desideri ordinare?</label>
                        <textarea id="ordine" name="ordine" rows="5" 
                                  placeholder="Es: 2 kg di pane integrale per domani, 5 cornetti alla crema per il 30 di maggio..." 
                                  required></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Effettua richiesta</button>
                        <button type="reset" class="btn-secondary">Annulla</button>
                    </div>

                    <div id="responseBox" style="display: none;"></div>

                    <div id="finalOrderActions" style="display: none;" class="form-actions">
                        <button type="reset" class="btn-secondary">Annulla</button>
                        <button type="button" id="effettuaOrdine" class="btn-primary">Effettua Ordine</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</div>

<script>
document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Previene il comportamento di submit classico

    const ordineText = document.getElementById('ordine').value;

    try {
        const response = await fetch('http://localhost:5000/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ text: ordineText })
        });

        const data = await response.json();

        const responseBox = document.getElementById('responseBox');
        responseBox.style.display = 'block';
        responseBox.textContent = JSON.stringify(data, null, 2); // Visualizza la risposta formattata

        // Mostra il pulsante "Effettua Ordine" dopo aver ricevuto la risposta
        const finalOrderActions = document.getElementById('finalOrderActions');
        finalOrderActions.style.display = 'flex';

    } catch (error) {
        alert("Errore nell'invio della richiesta: " + error);
    }
});

// Gestione del pulsante "Effettua Ordine"
document.getElementById('effettuaOrdine').addEventListener('click', function() {
    // Qui puoi aggiungere la logica per effettuare l'ordine finale
    alert('Ordine effettuato con successo!');
    // Esempio: potresti inviare i dati a un altro endpoint per salvare l'ordine
});
</script>

<?php include 'templates/footer.php'; ?>