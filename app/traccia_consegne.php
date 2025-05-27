<?php
define('BASE_PATH', realpath(__DIR__ . '/../public'));
require_once __DIR__ . '/conf/db_config.php';
require_once __DIR__ . '/functions/auth_check.php';

include __DIR__ . '/templates/header_panetteria.php';

checkUserType('Panettiere');

$idUtente = getCurrentUserId();
$pageTitle = "Percorso Consegne - DeliBread";

$config = require 'conf/config.php';

// Configurazione Google Maps API
$GOOGLE_MAPS_API_KEY = $config['google_maps_api_key'];

// Recupera l'ID della panetteria dell'utente corrente
$stmt = $conn->prepare("SELECT IdPanetteria FROM utente WHERE IdUtente = ?");
$stmt->bind_param("i", $idUtente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$idPanetteria = $row['IdPanetteria'];
$stmt->close();

// Funzione per ottenere le coordinate da un indirizzo
function getCoordinatesFromAddress($address, $apiKey) {
    $geocodeUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
    
    $response = file_get_contents($geocodeUrl);
    $data = json_decode($response, true);
    
    if ($data['status'] === 'OK' && !empty($data['results'])) {
        $location = $data['results'][0]['geometry']['location'];
        return [
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'formatted_address' => $data['results'][0]['formatted_address']
        ];
    }
    
    return null;
}

// Funzione per salvare/caricare coordinate da cache
function getCachedCoordinates($address) {
    $cacheFile = __DIR__ . '/cache/coordinates_cache.json';
    
    if (!file_exists(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0777, true);
    }
    
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
        return isset($cache[md5($address)]) ? $cache[md5($address)] : null;
    }
    
    return null;
}

function saveCachedCoordinates($address, $coordinates) {
    $cacheFile = __DIR__ . '/cache/coordinates_cache.json';
    
    if (!file_exists(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0777, true);
    }
    
    $cache = [];
    if (file_exists($cacheFile)) {
        $cache = json_decode(file_get_contents($cacheFile), true);
    }
    
    $cache[md5($address)] = $coordinates;
    file_put_contents($cacheFile, json_encode($cache));
}

// Recupera i dati della panetteria
$stmt = $conn->prepare("SELECT * FROM panetteria WHERE IdPanetteria = ?");
$stmt->bind_param("i", $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$panetteria = $result->fetch_assoc();
$stmt->close();

// Recupera le rivendite da servire
$stmt = $conn->prepare("
    SELECT r.* 
    FROM rivendita r 
    INNER JOIN panetteria_rivendita pr ON r.IdRivendita = pr.IdRivendita 
    WHERE pr.IdPanetteria = ?
    ORDER BY r.Nome
");
$stmt->bind_param("i", $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$rivendite = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Funzione per creare l'indirizzo completo
function createFullAddress($data) {
    return trim($data['Via'] . ' ' . $data['NCiv'] . ', ' . $data['CAP'] . ' ' . $data['Citta'] . ', ' . $data['Provincia'] . ', Italia');
}

// Ottieni coordinate per panetteria e rivendite
$locations = [];

// Aggiungi panetteria
$panetteriaAddress = createFullAddress($panetteria);
$panetteriaCoords = getCachedCoordinates($panetteriaAddress);

if (!$panetteriaCoords && !empty($GOOGLE_MAPS_API_KEY) && $GOOGLE_MAPS_API_KEY !== 'YOUR_GOOGLE_MAPS_API_KEY') {
    $panetteriaCoords = getCoordinatesFromAddress($panetteriaAddress, $GOOGLE_MAPS_API_KEY);
    if ($panetteriaCoords) {
        saveCachedCoordinates($panetteriaAddress, $panetteriaCoords);
    }
}

// Coordinate di default per Roma (per demo)
if (!$panetteriaCoords) {
    $panetteriaCoords = ['lat' => 41.9028, 'lng' => 12.4964, 'formatted_address' => $panetteriaAddress];
}

$locations[] = [
    'id' => 'panetteria_' . $panetteria['IdPanetteria'],
    'name' => $panetteria['Nome'],
    'address' => $panetteriaAddress,
    'type' => 'panetteria',
    'lat' => $panetteriaCoords['lat'],
    'lng' => $panetteriaCoords['lng']
];

// Aggiungi rivendite
foreach ($rivendite as $rivendita) {
    $rivenditaAddress = createFullAddress($rivendita);
    $rivenditaCoords = getCachedCoordinates($rivenditaAddress);
    
    if (!$rivenditaCoords && !empty($GOOGLE_MAPS_API_KEY) && $GOOGLE_MAPS_API_KEY !== 'YOUR_GOOGLE_MAPS_API_KEY') {
        $rivenditaCoords = getCoordinatesFromAddress($rivenditaAddress, $GOOGLE_MAPS_API_KEY);
        if ($rivenditaCoords) {
            saveCachedCoordinates($rivenditaAddress, $rivenditaCoords);
        }
    }
    
    // Coordinate casuali attorno a Roma per demo
    if (!$rivenditaCoords) {
        $rivenditaCoords = [
            'lat' => 41.9028 + (rand(-100, 100) / 1000),
            'lng' => 12.4964 + (rand(-100, 100) / 1000),
            'formatted_address' => $rivenditaAddress
        ];
    }
    
    $locations[] = [
        'id' => 'rivendita_' . $rivendita['IdRivendita'],
        'name' => $rivendita['Nome'],
        'address' => $rivenditaAddress,
        'type' => 'rivendita',
        'lat' => $rivenditaCoords['lat'],
        'lng' => $rivenditaCoords['lng']
    ];
}

// Calcola statistiche
$totalRivendite = count($rivendite);
$totalDistance = 0; // Sarà calcolato via JavaScript

// Recupera ordini del giorno per le rivendite
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT COUNT(*) as count_ordini, r.Nome as nome_rivendita, r.IdRivendita
    FROM ordine o
    INNER JOIN utente u ON o.IdUtente = u.IdUtente
    INNER JOIN rivendita r ON u.IdRivendita = r.IdRivendita
    WHERE DATE(o.DataCreazione) = ? AND o.IdPanetteria = ?
    GROUP BY r.IdRivendita, r.Nome
");
$stmt->bind_param("si", $today, $idPanetteria);
$stmt->execute();
$result = $stmt->get_result();
$ordiniPerRivendita = [];
while ($row = $result->fetch_assoc()) {
    $ordiniPerRivendita[$row['IdRivendita']] = $row['count_ordini'];
}
$stmt->close();
?>

<style>
.bakery-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bakery-main {
    flex: 1;
    margin-left: 280px;
    padding: 2rem;
    min-height: 100vh;
    width: calc(100% - 280px);
    overflow-x: auto;
}

.content-header {
    background: linear-gradient(135deg, #d4a574 0%, #8b4513 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    margin-bottom: 2rem;
    box-shadow: 0 10px 30px rgba(212, 165, 116, 0.3);
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

.stats-card {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.map-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 2rem;
}

.map-header {
    background: linear-gradient(135deg, #d4a574 0%, #cd853f 100%);
    color: white;
    padding: 1.5rem;
}

#map {
    height: 500px;
    width: 100%;
}

.route-info {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
}

.legend {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.legend-marker {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    margin-right: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
}

.marker-panetteria {
    background: #dc3545;
}

.marker-rivendita {
    background: #007bff;
}

.route-step {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.route-step:hover {
    background-color: #f8f9fa;
}

.route-step:last-child {
    border-bottom: none;
}

.distance-badge {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.time-badge {
    background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
}

.optimize-btn {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.optimize-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    color: white;
}

@media (max-width: 768px) {
    .bakery-main {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
    }
    
    .content-header h1 {
        font-size: 2rem;
    }
    
    #map {
        height: 400px;
    }
}
</style>

<!-- Include Bootstrap CSS e Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="bakery-container">
    <?php include('templates/sidebar.php'); ?>
    <main class="bakery-main">
        <div class="content-header">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-geo-alt me-3" style="font-size: 2rem;"></i>
                <div>
                    <h1 class="mb-0">Percorso Consegne</h1>
                    <p class="mb-0">
                        <i class="bi bi-truck me-2"></i>
                        Ottimizzazione del percorso di consegna giornaliero
                    </p>
                </div>
            </div>
            <div class="mt-3">
                <span class="distance-badge me-2">
                    <i class="bi bi-map me-2"></i>
                    <?= $totalRivendite ?> punti di consegna
                </span>
                <span class="time-badge">
                    <i class="bi bi-clock me-2"></i>
                    Aggiornato oggi
                </span>
            </div>
        </div>

        <!-- Statistiche -->
        <div class="row stats-cards mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-shop stat-icon text-danger"></i>
                        <div class="stat-number text-danger">1</div>
                        <h6 class="text-muted mb-0">Panetteria</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-pin-map stat-icon text-primary"></i>
                        <div class="stat-number text-primary"><?= $totalRivendite ?></div>
                        <h6 class="text-muted mb-0">Rivendite</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-speedometer2 stat-icon text-success"></i>
                        <div class="stat-number text-success" id="total-distance">-</div>
                        <h6 class="text-muted mb-0">Distanza Tot.</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-clock stat-icon text-info"></i>
                        <div class="stat-number text-info" id="total-time">-</div>
                        <h6 class="text-muted mb-0">Tempo Tot.</h6>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Mappa -->
                <div class="map-container">
                    <div class="map-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-map me-3" style="font-size: 1.5rem;"></i>
                                <h4 class="mb-0">Mappa Percorso Ottimizzato</h4>
                            </div>
                            <button class="btn optimize-btn" onclick="optimizeRoute()">
                                <i class="bi bi-arrow-repeat me-2"></i>
                                Ottimizza Percorso
                            </button>
                        </div>
                    </div>
                    <div id="map"></div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Legenda e Info Percorso -->
                <div class="route-info">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bi bi-list-ol me-2"></i>
                            Dettagli Percorso
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <!-- Legenda -->
                        <div class="legend">
                            <h6 class="mb-3">
                                <i class="bi bi-bookmark me-2"></i>
                                Legenda
                            </h6>
                            <div class="legend-item">
                                <div class="legend-marker marker-panetteria">P</div>
                                <span>Panetteria (Punto di partenza)</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-marker marker-rivendita">R</div>
                                <span>Rivendita (Punto di consegna)</span>
                            </div>
                        </div>
                        
                        <!-- Lista tappe -->
                        <div id="route-steps">
                            <div class="route-step">
                                <div class="d-flex align-items-center">
                                    <div class="legend-marker marker-panetteria me-3">P</div>
                                    <div>
                                        <strong><?= htmlspecialchars($panetteria['Nome']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($panetteriaAddress) ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php foreach ($rivendite as $index => $rivendita): ?>
                            <div class="route-step">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="legend-marker marker-rivendita me-3"><?= $index + 1 ?></div>
                                        <div>
                                            <strong><?= htmlspecialchars($rivendita['Nome']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars(createFullAddress($rivendita)) ?></small>
                                            <?php if (isset($ordiniPerRivendita[$rivendita['IdRivendita']])): ?>
                                            <br><span class="badge bg-warning text-dark">
                                                <?= $ordiniPerRivendita[$rivendita['IdRivendita']] ?> ordini oggi
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Google Maps API -->
<script>
// Dati delle location
const locations = <?= json_encode($locations) ?>;
const GOOGLE_MAPS_API_KEY = '<?= $GOOGLE_MAPS_API_KEY ?>';

let map;
let directionsService;
let directionsRenderer;
let markers = [];

// Inizializza la mappa
function initMap() {
    // Centro mappa sulla panetteria
    const center = {lat: locations[0].lat, lng: locations[0].lng};
    
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 11,
        center: center,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });
    
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        draggable: true,
        map: map,
        panel: null
    });
    
    // Aggiungi markers
    addMarkers();
    
    // Calcola percorso iniziale
    calculateOptimizedRoute();
}

// Aggiungi markers alla mappa
function addMarkers() {
    locations.forEach((location, index) => {
        const marker = new google.maps.Marker({
            position: {lat: location.lat, lng: location.lng},
            map: map,
            title: location.name,
            icon: {
                url: location.type === 'panetteria' 
                    ? 'data:image/svg+xml;base64,' + btoa(createMarkerSVG('P', '#dc3545'))
                    : 'data:image/svg+xml;base64,' + btoa(createMarkerSVG((index).toString(), '#007bff')),
                scaledSize: new google.maps.Size(40, 40),
                anchor: new google.maps.Point(20, 20)
            }
        });
        
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="padding: 10px;">
                    <h6 style="margin-bottom: 5px; color: #333;">${location.name}</h6>
                    <p style="margin: 0; font-size: 12px; color: #666;">${location.address}</p>
                    <span style="background: ${location.type === 'panetteria' ? '#dc3545' : '#007bff'}; 
                                 color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">
                        ${location.type === 'panetteria' ? 'PANETTERIA' : 'RIVENDITA'}
                    </span>
                </div>
            `
        });
        
        marker.addListener('click', () => {
            infoWindow.open(map, marker);
        });
        
        markers.push(marker);
    });
}

// Crea SVG per marker personalizzato
function createMarkerSVG(text, color) {
    return `<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="18" fill="${color}" stroke="white" stroke-width="2"/>
        <text x="20" y="26" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="14" font-weight="bold">${text}</text>
    </svg>`;
}

// Calcola percorso ottimizzato
function calculateOptimizedRoute() {
    if (locations.length < 2) return;
    
    const start = new google.maps.LatLng(locations[0].lat, locations[0].lng);
    const end = start; // Ritorna alla panetteria
    
    // Waypoints (rivendite)
    const waypoints = locations.slice(1).map(location => ({
        location: new google.maps.LatLng(location.lat, location.lng),
        stopover: true
    }));
    
    const request = {
        origin: start,
        destination: end,
        waypoints: waypoints,
        optimizeWaypoints: true,
        travelMode: google.maps.TravelMode.DRIVING,
        unitSystem: google.maps.UnitSystem.METRIC,
        avoidHighways: false,
        avoidTolls: false
    };
    
    directionsService.route(request, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
            updateRouteInfo(result);
        } else {
            console.error('Errore nel calcolo del percorso:', status);
            alert('Errore nel calcolo del percorso. Verificare le coordinate.');
        }
    });
}

// Aggiorna informazioni del percorso
function updateRouteInfo(result) {
    const route = result.routes[0];
    let totalDistance = 0;
    let totalTime = 0;
    
    // Calcola totali
    route.legs.forEach(leg => {
        totalDistance += leg.distance.value;
        totalTime += leg.duration.value;
    });
    
    // Aggiorna statistiche
    document.getElementById('total-distance').textContent = (totalDistance / 1000).toFixed(1) + ' km';
    document.getElementById('total-time').textContent = Math.round(totalTime / 60) + ' min';
    
    // Aggiorna lista tappe con ordine ottimizzato
    updateRouteSteps(result);
}

// Aggiorna lista tappe
function updateRouteSteps(result) {
    const waypoints = result.routes[0].waypoint_order;
    const legs = result.routes[0].legs;
    
    let html = `
        <div class="route-step">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="legend-marker marker-panetteria me-3">P</div>
                    <div>
                        <strong>${locations[0].name}</strong>
                        <br><small class="text-muted">${locations[0].address}</small>
                    </div>
                </div>
                <span class="badge bg-success">PARTENZA</span>
            </div>
        </div>
    `;
    
    waypoints.forEach((waypointIndex, legIndex) => {
        const location = locations[waypointIndex + 1]; // +1 perché waypoints esclude la panetteria
        const leg = legs[legIndex];
        
        html += `
            <div class="route-step">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="legend-marker marker-rivendita me-3">${legIndex + 1}</div>
                        <div>
                            <strong>${location.name}</strong>
                            <br><small class="text-muted">${location.address}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="distance-badge mb-1">${leg.distance.text}</div>
                        <div class="time-badge">${leg.duration.text}</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    // Tappa finale (ritorno alla panetteria)
    const lastLeg = legs[legs.length - 1];
    html += `
        <div class="route-step">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="legend-marker marker-panetteria me-3">P</div>
                    <div>
                        <strong>${locations[0].name}</strong>
                        <br><small class="text-muted">Ritorno alla base</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="distance-badge mb-1">${lastLeg.distance.text}</div>
                    <div class="time-badge">${lastLeg.duration.text}</div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('route-steps').innerHTML = html;
}

// Ottimizza percorso (ricarica)
function optimizeRoute() {
    calculateOptimizedRoute();
}

// Gestione errori API
window.gm_authFailure = function() {
    alert('Errore autenticazione Google Maps API. Verificare la chiave API.');
};
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=<?= $GOOGLE_MAPS_API_KEY ?>&callback=initMap&libraries=geometry" async defer></script>