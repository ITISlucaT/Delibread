<?php
session_start();

if (!isset($_SESSION['IdUtente'])) {
    header("Location: index.php");
    exit();
}

include 'conf/db_config.php';
include 'templates/header.php';

// Dati utente di esempio
$user = [
    'nome' => 'Mario',
    'cognome' => 'Rossi'
];

$current_page = 'ordini';
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

.order-form-card {
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

.order-form-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
}

.form-label {
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.form-select, .form-control {
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    padding: 0.875rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-select:focus, .form-control:focus {
    border-color: #4f70ff;
    box-shadow: 0 0 0 4px rgba(79, 112, 255, 0.1);
    outline: none;
}

.btn-primary-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 1rem 3rem;
    border-radius: 16px;
    font-weight: 700;
    font-size: 1.1rem;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-primary-custom::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.btn-primary-custom:hover::before {
    left: 100%;
}

.btn-primary-custom:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
}

.btn-secondary-custom {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    color: #64748b;
    padding: 0.875rem 2.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.btn-secondary-custom:hover {
    background: #e2e8f0;
    color: #475569;
    transform: translateY(-1px);
}

.response-card {
    background: white;
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    border-left: 5px solid #667eea;
    margin: 2rem 0;
    position: relative;
    overflow: hidden;
    width: calc(100% - 1rem);
}

.response-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.status-badge.processing {
    background: linear-gradient(135deg, #fff3e0 0%, #ffcc80 20%);
    color: #f57c00;
    border: 1px solid #ffcc80;
}

.status-badge.completed {
    background: linear-gradient(135deg, #e8f5e8 0%, #a5d6a7 20%);
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.status-badge.error {
    background: linear-gradient(135deg, #ffebee 0%, #ef9a9a 20%);
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.response-content {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0;
    margin-top: 1.5rem;
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
}

.analysis-table {
    width: 100%;
    margin: 0;
    font-size: 0.9rem;
}

.analysis-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.25rem;
    font-weight: 700;
    text-align: center;
    border: none;
    width: 33.33%;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.analysis-table th:first-child {
    border-top-left-radius: 12px;
}

.analysis-table th:last-child {
    border-top-right-radius: 12px;
}

.analysis-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: top;
    text-align: center;
    font-weight: 500;
}

.analysis-table tr:last-child td {
    border-bottom: none;
}

.analysis-table tr:nth-child(even) {
    background-color: #f9fafb;
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

.form-title {
    color: #1e293b;
    font-weight: 700;
    font-size: 1.5rem;
    margin-bottom: 2rem;
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
    
    .order-form-card {
        padding: 2rem 1.5rem;
        margin: 0 0 2rem 0;
        border-radius: 12px;
    }
    
    .response-card {
        margin: 2rem 0;
        border-radius: 12px;
    }
    
    .btn-group-mobile {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .btn-primary-custom, .btn-secondary-custom {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .gradient-header {
        padding: 1.5rem 1rem;
    }
    
    .order-form-card {
        padding: 1.5rem 1rem;
    }
    
    .analysis-table th, .analysis-table td {
        padding: 0.75rem 0.5rem;
        font-size: 0.85rem;
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
                        <h1 class="mb-1">🛒 Effettua un ordine</h1>
                        <!-- <p class="mb-0">Descrivi il tuo ordine</p> -->
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid px-0">
            <div class="row justify-content-center mx-0">
                <div class="col-lg-8 px-0">
                    <div class="order-form-card">
                        <h2 class="form-title">Descrivi il tuo ordine</h2>

                        <form id="orderForm">
                            <div class="mb-4">
                                <label for="panetteria" class="form-label">Seleziona panetteria</label>
                                <select id="panetteria" name="panetteria" class="form-select" required>
                                    <option value="">-- Seleziona --</option>
                                    <option value="1">Panetteria Mario</option>
                                    <option value="2">Pane e Vino</option>
                                    <option value="3">Il Fornaio</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label for="ordine" class="form-label">Cosa desideri ordinare?</label>
                                <textarea id="ordine" name="ordine" rows="5" class="form-control"
                                          placeholder="Es: 2 kg di pane integrale per domani, 5 cornetti alla crema per il 30 di maggio..."
                                          required></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary-custom">
                                    Effettua richiesta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box di risposta -->
        <div id="responseBox" style="display: none;">
            <div class="container-fluid px-0">
                <div class="row justify-content-center mx-0">
                    <div class="col-lg-8 px-0">
                        <div class="response-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="h5 mb-0 fw-bold">📊 Analisi Ordine</h3>
                                <span id="statusBadge" class="status-badge processing">In elaborazione</span>
                            </div>
                            <div class="response-content" id="responseContent">
                                <table class="analysis-table">
                                    <thead>
                                        <tr>
                                            <th>📊 Quantità</th>
                                            <th>🥖 Tipologia</th>
                                            <th>📅 Data Scadenza</th>
                                        </tr>
                                    </thead>
                                    <tbody id="analysisTableBody">
                                        <!-- Contenuto dinamico -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Azioni finali -->
        <div id="finalOrderActions" style="display: none;">
            <div class="container-fluid px-0">
                <div class="row justify-content-center mx-0">
                    <div class="col-lg-8 px-0">
                        <div class="text-center">
                            <div class="d-flex gap-3 justify-content-center btn-group-mobile">
                                <button type="button" class="btn btn-secondary-custom" onclick="resetForm()">
                                    Annulla
                                </button>
                                <button type="button" id="effettuaOrdine" class="btn btn-primary-custom">
                                    Effettua Ordine
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const ordineText = document.getElementById('ordine').value;

    // Mostra il box di risposta
    const responseBox = document.getElementById('responseBox');
    const responseContent = document.getElementById('responseContent');
    const statusBadge = document.getElementById('statusBadge');
    
    responseBox.style.display = 'block';
    
    // Reset della tabella
    const tableBody = document.getElementById('analysisTableBody');
    tableBody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 2rem;"><em>Analizzando il tuo ordine...</em></td></tr>';
    
    statusBadge.textContent = 'In elaborazione';
    statusBadge.className = 'status-badge processing';

    try {
        const response = await fetch('http://localhost:5000/analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ text: ordineText })
        });

        const data = await response.json();

        // Popola la tabella con i dati
        const tableBody = document.getElementById('analysisTableBody');
        tableBody.innerHTML = '';
        
        // Ottieni i dati dai vari array
        const quantita = data.quantita || [];
        const prodotti = data.prodotti || [];
        const scadenze = data.scadenza || [];
        
        // Trova il numero massimo di righe necessarie
        const maxRows = Math.max(quantita.length, prodotti.length, scadenze.length, 1);
        
        // Crea le righe della tabella
        for (let i = 0; i < maxRows; i++) {
            const row = tableBody.insertRow();
            
            // Colonna Quantità
            const qtyCell = row.insertCell();
            qtyCell.textContent = quantita[i] || '-';
            
            // Colonna Tipologia
            const prodCell = row.insertCell();
            prodCell.textContent = prodotti[i] || '-';
            
            // Colonna Data Scadenza
            const dateCell = row.insertCell();
            if (scadenze[i]) {
                const formattedDate = new Date(scadenze[i]).toLocaleDateString('it-IT', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                });
                dateCell.textContent = formattedDate;
            } else {
                dateCell.textContent = '-';
            }
        }

        // Aggiorna lo status
        
        // Cambia lo status a "Completato"
        statusBadge.textContent = 'Completato';
        statusBadge.className = 'status-badge completed';

        // Mostra i pulsanti finali
        document.getElementById('finalOrderActions').style.display = 'block';

    } catch (error) {
        // Gestione errore
        const tableBody = document.getElementById('analysisTableBody');
        tableBody.innerHTML = `
            <tr>
                <td colspan="3" style="color: #c62828; text-align: center; padding: 2rem;">
                    ❌ Errore nell'invio della richiesta: ${error.message}
                </td>
            </tr>
        `;
        
        statusBadge.textContent = 'Errore';
        statusBadge.className = 'status-badge error';
        
        // Mostra comunque i pulsanti per permettere di riprovare
        document.getElementById('finalOrderActions').style.display = 'block';
    }
});

document.getElementById('effettuaOrdine').addEventListener('click', function() {
    alert('Ordine effettuato con successo!');
    resetForm();
});

function resetForm() {
    document.getElementById('orderForm').reset();
    document.getElementById('responseBox').style.display = 'none';
    document.getElementById('finalOrderActions').style.display = 'none';
}
</script>

<?php include 'templates/footer.php'; ?>