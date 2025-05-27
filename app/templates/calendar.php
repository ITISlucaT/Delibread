<?php
/**
 * Modern Calendar Component with Date Range Selection
 * 
 * Usage:
 * $calendarConfig = [
 *     'orders' => $orders_array,
 *     'date_field' => 'DataConsegna', // or 'DataOrdine'
 *     'status_filter' => ['pronto', 'consegnato'], // optional
 *     'show_navigation' => true,
 *     'show_filters' => true,
 *     'show_range_selector' => true,
 *     'ajax_enabled' => true
 * ];
 * include('templates/modern_calendar.php');
 */

// Default configuration
$defaultConfig = [
    'orders' => [],
    'date_field' => 'DataConsegna',
    'status_filter' => null,
    'show_navigation' => true,
    'show_filters' => true,
    'show_range_selector' => true,
    'ajax_enabled' => false,
    'current_month' => date('m'),
    'current_year' => date('Y'),
    'date_range_from' => null,
    'date_range_to' => null
];

// Merge with provided config
$config = array_merge($defaultConfig, $calendarConfig ?? []);

// Get parameters from GET/POST
if ($config['show_navigation']) {
    $config['current_month'] = $_GET['month'] ?? $config['current_month'];
    $config['current_year'] = $_GET['year'] ?? $config['current_year'];
}

// Handle date range from GET parameters
$config['date_range_from'] = $_GET['date_from'] ?? $config['date_range_from'];
$config['date_range_to'] = $_GET['date_to'] ?? $config['date_range_to'];

// Handle status filter from GET
$activeStatusFilter = $_GET['status_filter'] ?? '';

$currentMonth = (int)$config['current_month'];
$currentYear = (int)$config['current_year'];

// Calendar calculations
$firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$daysInMonth = date('t', $firstDay);
$startDay = date('w', $firstDay);
$startDay = ($startDay == 0) ? 6 : $startDay - 1; // Convert Sunday=0 to Monday=0

// Generate weeks
$weeks = [];
$week = array_fill(0, 7, null);

// Fill previous month days
$prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
$prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
$daysInPrevMonth = date('t', mktime(0, 0, 0, $prevMonth, 1, $prevYear));

for ($i = $startDay - 1; $i >= 0; $i--) {
    $week[$i] = [
        'day' => $daysInPrevMonth - ($startDay - 1 - $i),
        'month' => $prevMonth,
        'year' => $prevYear,
        'is_current_month' => false,
        'is_today' => false
    ];
}

// Fill current month days
for ($day = 1; $day <= $daysInMonth; $day++) {
    $weekIndex = ($startDay + $day - 1) % 7;
    
    $week[$weekIndex] = [
        'day' => $day,
        'month' => $currentMonth,
        'year' => $currentYear,
        'is_current_month' => true,
        'is_today' => (date('Y-m-d') === sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day))
    ];
    
    if ($weekIndex == 6) {
        $weeks[] = $week;
        $week = array_fill(0, 7, null);
    }
}

// Fill next month days if needed
if (array_filter($week, function($cell) { return $cell !== null; })) {
    $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
    $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
    $nextDay = 1;
    
    for ($i = 0; $i < 7; $i++) {
        if ($week[$i] === null) {
            $week[$i] = [
                'day' => $nextDay++,
                'month' => $nextMonth,
                'year' => $nextYear,
                'is_current_month' => false,
                'is_today' => false
            ];
        }
    }
    $weeks[] = $week;
}

// Filter and process orders for calendar
$processedOrders = [];
$allStatuses = [];
if (!empty($config['orders'])) {
    foreach ($config['orders'] as $order) {
        // Collect all unique statuses
        $status = trim($order['Stato']);
        if (!in_array($status, $allStatuses)) {
            $allStatuses[] = $status;
        }
        
        // Apply status filter if provided
        if ($activeStatusFilter && strtolower($order['Stato']) !== strtolower($activeStatusFilter)) {
            continue;
        }
        
        $dateField = $config['date_field'];
        if (isset($order[$dateField]) && !empty($order[$dateField])) {
            $orderDate = date('Y-m-d', strtotime($order[$dateField]));
            
            // Apply date range filter if provided
            if ($config['date_range_from'] && $orderDate < $config['date_range_from']) {
                continue;
            }
            if ($config['date_range_to'] && $orderDate > $config['date_range_to']) {
                continue;
            }
            
            if (!isset($processedOrders[$orderDate])) {
                $processedOrders[$orderDate] = [];
            }
            $processedOrders[$orderDate][] = $order;
        }
    }
}

// Navigation URLs
$prevMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
$prevYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;
$nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
$nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;

$baseUrl = $_SERVER['PHP_SELF'];
$queryParams = $_GET;
unset($queryParams['month'], $queryParams['year']);
$queryString = http_build_query($queryParams);
$queryString = $queryString ? '&' . $queryString : '';

$prevUrl = $baseUrl . "?month=$prevMonth&year=$prevYear" . $queryString;
$nextUrl = $baseUrl . "?month=$nextMonth&year=$nextYear" . $queryString;
$todayUrl = $baseUrl . "?month=" . date('m') . "&year=" . date('Y') . $queryString;

$monthNames = [
    1 => 'Gennaio', 2 => 'Febbraio', 3 => 'Marzo', 4 => 'Aprile',
    5 => 'Maggio', 6 => 'Giugno', 7 => 'Luglio', 8 => 'Agosto',
    9 => 'Settembre', 10 => 'Ottobre', 11 => 'Novembre', 12 => 'Dicembre'
];

// Calculate stats
$totalOrders = array_sum(array_map('count', $processedOrders));
$totalRevenue = 0;
foreach ($processedOrders as $dayOrders) {
    foreach ($dayOrders as $order) {
        $totalRevenue += floatval($order['Totale']);
    }
}
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    --glass-bg: rgba(255, 255, 255, 0.15);
    --glass-border: rgba(255, 255, 255, 0.2);
    --shadow-primary: 0 8px 32px rgba(31, 38, 135, 0.15);
    --shadow-hover: 0 15px 35px rgba(31, 38, 135, 0.25);
}

.modern-calendar {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-primary);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    transition: all 0.3s ease;
}

.modern-calendar:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.calendar-header {
    background: var(--primary-gradient);
    color: white;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}

.calendar-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
    0%, 100% { opacity: 0; }
    50% { opacity: 1; }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.stat-card {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 15px;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: scale(1.05);
    background: rgba(255, 255, 255, 0.25);
}

.filters-section {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.modern-input {
    border: none;
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    color: #333;
}

.modern-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: rgba(255, 255, 255, 0.9);
}

.modern-btn {
    border: none;
    border-radius: 12px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.modern-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.modern-btn:hover::before {
    left: 100%;
}

.btn-primary-modern {
    background: var(--primary-gradient);
    color: white;
}

.btn-success-modern {
    background: var(--success-gradient);
    color: white;
}

.btn-outline-modern {
    background: transparent;
    border: 2px solid #667eea;
    color: #667eea;
}

.btn-outline-modern:hover {
    background: var(--primary-gradient);
    color: white;
    border-color: transparent;
}

.calendar-grid {
    background: white;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.calendar-grid th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #495057;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    padding: 1rem;
    text-align: center;
    border-bottom: 2px solid #dee2e6;
}

.calendar-grid td {
    padding: 0;
    border: 1px solid #f0f0f0;
    height: 100px;
    position: relative;
    transition: all 0.2s ease;
}

.calendar-day-cell {
    width: 100%;
    height: 100%;
    padding: 0.75rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.calendar-day-cell:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    transform: scale(1.02);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.calendar-day-cell.other-month {
    background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 100%);
    color: #999;
}

.calendar-day-cell.today {
    background: var(--primary-gradient);
    color: white;
    font-weight: bold;
}

.calendar-day-cell.in-range {
    background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
    border-color: #4caf50;
}

.calendar-day-cell.range-start,
.calendar-day-cell.range-end {
    background: var(--success-gradient);
    color: white;
    font-weight: bold;
}

.day-number {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    position: relative;
    z-index: 2;
}

.orders-indicator {
    position: absolute;
    top: 8px;
    right: 8px;
    background: var(--warning-gradient);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    z-index: 3;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.status-dots {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 3px;
    z-index: 2;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.status-in-attesa { background: #ffc107; }
.status-confermato { background: #17a2b8; }
.status-in-preparazione { background: #007bff; }
.status-pronto { background: #28a745; }
.status-consegnato { background: #6c757d; }

.modal-modern .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-hover);
    backdrop-filter: blur(10px);
}

.modal-modern .modal-header {
    background: var(--primary-gradient);
    color: white;
    border: none;
    padding: 1.5rem 2rem;
}

.modal-modern .modal-body {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.order-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    border: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.range-selector {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.date-input-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #666;
    margin: 0;
}

@media (max-width: 768px) {
    .calendar-header {
        padding: 1.5rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-section {
        padding: 1rem;
    }
    
    .range-selector {
        flex-direction: column;
        align-items: stretch;
    }
    
    .calendar-grid td {
        height: 80px;
    }
    
    .calendar-day-cell {
        padding: 0.5rem;
    }
    
    .day-number {
        font-size: 0.9rem;
    }
    
    .orders-indicator {
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
    }
}
</style>

<div class="modern-calendar">
    <!-- Header with Stats -->
    <div class="calendar-header">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <i class="bi bi-calendar-month me-3" style="font-size: 2rem;"></i>
                <div>
                    <h3 class="mb-0">Calendario Ordini</h3>
                    <p class="mb-0 opacity-75"><?= $monthNames[$currentMonth] ?> <?= $currentYear ?></p>
                </div>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="h4 mb-1 fw-bold"><?= $totalOrders ?></div>
                <div class="small opacity-75">Ordini Totali</div>
            </div>
            <div class="stat-card">
                <div class="h4 mb-1 fw-bold">€<?= number_format($totalRevenue, 2) ?></div>
                <div class="small opacity-75">Ricavi</div>
            </div>
            <div class="stat-card">
                <div class="h4 mb-1 fw-bold"><?= count($processedOrders) ?></div>
                <div class="small opacity-75">Giorni Attivi</div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <?php if ($config['show_filters'] || $config['show_range_selector']): ?>
    <div class="filters-section">
        <form method="GET" action="<?= $_SERVER['PHP_SELF'] ?>" class="row g-3 align-items-end">
            <!-- Preserve current month/year -->
            <input type="hidden" name="month" value="<?= $currentMonth ?>">
            <input type="hidden" name="year" value="<?= $currentYear ?>">
            
            <?php if ($config['show_range_selector']): ?>
            <div class="col-md-8">
                <label class="form-label fw-semibold mb-2">
                    <i class="bi bi-calendar-range me-2"></i>Seleziona Intervallo Date
                </label>
                <div class="range-selector">
                    <div class="date-input-group">
                        <label for="date_from">Da:</label>
                        <input type="date" 
                               id="date_from" 
                               name="date_from" 
                               class="modern-input"
                               value="<?= htmlspecialchars($config['date_range_from'] ?? '') ?>">
                    </div>
                    <div class="date-input-group">
                        <label for="date_to">A:</label>
                        <input type="date" 
                               id="date_to" 
                               name="date_to" 
                               class="modern-input"
                               value="<?= htmlspecialchars($config['date_range_to'] ?? '') ?>">
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($config['show_filters'] && !empty($allStatuses)): ?>
            <div class="col-md-3">
                <label class="form-label fw-semibold mb-2">
                    <i class="bi bi-funnel me-2"></i>Stato Ordine
                </label>
                <select name="status_filter" class="modern-input w-100">
                    <option value="">Tutti gli stati</option>
                    <?php foreach ($allStatuses as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" 
                            <?= $activeStatusFilter === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="col-md-1">
                <div class="d-flex gap-2">
                    <button type="submit" class="modern-btn btn-primary-modern">
                        <i class="bi bi-search"></i>
                    </button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?month=<?= $currentMonth ?>&year=<?= $currentYear ?>" 
                       class="modern-btn btn-outline-modern">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Quick Actions -->
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div class="d-flex gap-2">
                <?php if ($config['show_navigation']): ?>
                <a href="<?= htmlspecialchars($prevUrl) ?>" class="modern-btn btn-outline-modern">
                    <i class="bi bi-chevron-left me-1"></i><?= $monthNames[$prevMonth] ?>
                </a>
                <a href="<?= htmlspecialchars($todayUrl) ?>" class="modern-btn btn-success-modern">
                    <i class="bi bi-calendar-day me-1"></i>Oggi
                </a>
                <a href="<?= htmlspecialchars($nextUrl) ?>" class="modern-btn btn-outline-modern">
                    <?= $monthNames[$nextMonth] ?><i class="bi bi-chevron-right ms-1"></i>
                </a>
                <?php endif; ?>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <small class="text-muted">Legenda:</small>
                <div class="d-flex gap-2">
                    <div class="d-flex align-items-center gap-1">
                        <span class="status-dot status-in-attesa"></span>
                        <small>In Attesa</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="status-dot status-pronto"></span>
                        <small>Pronto</small>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <span class="status-dot status-consegnato"></span>
                        <small>Consegnato</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Calendar Grid -->
    <div class="table-responsive">
        <table class="calendar-grid">
            <thead>
                <tr>
                    <th>Lun</th>
                    <th>Mar</th>
                    <th>Mer</th>
                    <th>Gio</th>
                    <th>Ven</th>
                    <th>Sab</th>
                    <th>Dom</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($weeks as $week): ?>
                <tr>
                    <?php foreach ($week as $dayData): ?>
                    <td>
                        <?php if ($dayData): ?>
                        <?php
                        $currentDate = sprintf('%04d-%02d-%02d', $dayData['year'], $dayData['month'], $dayData['day']);
                        $dayOrders = $processedOrders[$currentDate] ?? [];
                        
                        $cellClasses = ['calendar-day-cell'];
                        if (!$dayData['is_current_month']) {
                            $cellClasses[] = 'other-month';
                        }
                        if ($dayData['is_today']) {
                            $cellClasses[] = 'today';
                        }
                        
                        // Check if date is in range
                        $inRange = false;
                        if ($config['date_range_from'] && $config['date_range_to']) {
                            if ($currentDate >= $config['date_range_from'] && $currentDate <= $config['date_range_to']) {
                                $cellClasses[] = 'in-range';
                                $inRange = true;
                            }
                            if ($currentDate === $config['date_range_from']) {
                                $cellClasses[] = 'range-start';
                            }
                            if ($currentDate === $config['date_range_to']) {
                                $cellClasses[] = 'range-end';
                            }
                        }
                        ?>
                        <div class="<?= implode(' ', $cellClasses) ?>" 
                             data-date="<?= $currentDate ?>"
                             onclick="showDayDetails('<?= $currentDate ?>')">
                            
                            <div class="day-number"><?= $dayData['day'] ?></div>
                            
                            <?php if (!empty($dayOrders)): ?>
                                <div class="orders-indicator">
                                    <?= count($dayOrders) ?>
                                </div>
                                
                                <div class="status-dots">
                                    <?php 
                                    $statusCounts = [];
                                    foreach ($dayOrders as $order) {
                                        $status = str_replace([' ', 'à'], ['_', 'a'], strtolower($order['Stato']));
                                        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                                    }
                                    
                                    $maxDots = 4;
                                    $dotCount = 0;
                                    foreach ($statusCounts as $status => $count):
                                        if ($dotCount >= $maxDots) break;
                                        $displayCount = min($count, $maxDots - $dotCount);
                                        for ($i = 0; $i < $displayCount; $i++):
                                            if ($dotCount >= $maxDots) break;
                                    ?>
                                    <span class="status-dot status-<?= str_replace('_', '-', $status) ?>"></span>
                                    <?php 
                                            $dotCount++;
                                        endfor;
                                    endforeach;
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modern Modal -->
<div class="modal fade modal-modern" id="dayDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-calendar-day me-3"></i>
                    <div>
                        <div>Dettagli Giornata</div>
                        <small class="opacity-75" id="modalDateDisplay"></small>
                    </div>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced Calendar functionality
const calendarOrders = <?= json_encode($processedOrders) ?>;
const monthNames = <?= json_encode($monthNames) ?>;

function showDayDetails(date) {
    const orders = calendarOrders[date] || [];
    const modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
    
    // Format date for display
    const dateObj = new Date(date + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('it-IT', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    document.getElementById('modalDateDisplay').textContent = formattedDate;
    
    if (orders.length === 0) {
        document.getElementById('dayDetailsContent').innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">Nessun ordine per questa data</h5>
                <p class="text-muted">Non ci sono ordini programmati per ${formattedDate}</p>
            </div>
        `;
    } else {
        // Calculate daily stats
        const totalDaily = orders.reduce((sum, order) => sum + parseFloat(order.Totale || 0), 0);
        const statusStats = {};
        orders.forEach(order => {
            const status = order.Stato;
            statusStats[status] = (statusStats[status] || 0) + 1;
        });
        
        let content = `
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="h3 mb-1 text-primary">${orders.length}</div>
                        <div class="text-muted">Ordini Totali</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card text-center">
                        <div class="h3 mb-1 text-success">€${totalDaily.toFixed(2)}</div>
                        <div class="text-muted">Ricavi Giornalieri</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card">
                        <div class="text-muted mb-2">Stati Ordini:</div>
                        <div class="d-flex flex-wrap gap-2">
        `;
        
        Object.entries(statusStats).forEach(([status, count]) => {
            const badgeClass = getStatusBadgeClass(status);
            content += `<span class="badge ${badgeClass}">${status}: ${count}</span>`;
        });
        
        content += `
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
        `;
        
        orders.forEach((order, index) => {
            const statusClass = getStatusBadgeClass(order.Stato);
            const avatarLetter = (order.Nome || order.Username || 'U').charAt(0).toUpperCase();
            
            content += `
                <div class="col-md-6 mb-3">
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" 
                                     style="width: 45px; height: 45px; border-radius: 50%; font-size: 1.2rem; font-weight: bold;">
                                    ${avatarLetter}
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold">${order.Nome || order.Username || 'Cliente'}</h6>
                                    ${order.Email ? `<small class="text-muted">${order.Email}</small>` : ''}
                                </div>
                            </div>
                            <span class="badge ${statusClass} fs-6">${order.Stato}</span>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Orario</small>
                                        <strong>${order.OraConsegna || 'Non specificato'}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-currency-euro text-success me-2"></i>
                                    <div>
                                        <small class="text-muted d-block">Totale</small>
                                        <strong class="text-success">€${parseFloat(order.Totale || 0).toFixed(2)}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${order.Note ? `
                            <div class="mt-3 pt-3 border-top">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-chat-dots text-muted me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Note</small>
                                        <p class="mb-0 small">${order.Note}</p>
                                    </div>
                                </div>
                            </div>
                        ` : ''}
                        
                        <div class="mt-3 d-flex justify-content-end gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(${order.ID || index})">
                                <i class="bi bi-eye me-1"></i>Dettagli
                            </button>
                            ${order.Stato !== 'Consegnato' ? `
                                <button class="btn btn-success btn-sm" onclick="updateOrderStatus(${order.ID || index}, 'Consegnato')">
                                    <i class="bi bi-check-circle me-1"></i>Segna Consegnato
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        content += '</div>';
        document.getElementById('dayDetailsContent').innerHTML = content;
    }
    
    modal.show();
}

function getStatusBadgeClass(status) {
    const statusLower = status.toLowerCase();
    switch(statusLower) {
        case 'in attesa': return 'bg-warning text-dark';
        case 'confermato': return 'bg-info text-white';
        case 'in preparazione': return 'bg-primary text-white';
        case 'pronto': return 'bg-success text-white';
        case 'consegnato': return 'bg-secondary text-white';
        case 'annullato': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
    }
}

function viewOrderDetails(orderId) {
    // Implement order details view
    console.log('Viewing order details for ID:', orderId);
    // You can implement AJAX call to load full order details
}

function updateOrderStatus(orderId, newStatus) {
    if (confirm(`Confermi di voler aggiornare lo stato dell'ordine a "${newStatus}"?`)) {
        // Implement AJAX call to update order status
        console.log('Updating order', orderId, 'to status', newStatus);
        
        // Example AJAX call (uncomment and modify as needed)
        /*
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Reload to show updated status
            } else {
                alert('Errore nell\'aggiornamento dello stato');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore di connessione');
        });
        */
        
        // For now, just show success message
        alert('Stato aggiornato con successo! (Implementare chiamata AJAX)');
    }
}

// Date range validation
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            dateToInput.min = this.value;
            if (dateToInput.value && dateToInput.value < this.value) {
                dateToInput.value = this.value;
            }
        });
        
        dateToInput.addEventListener('change', function() {
            dateFromInput.max = this.value;
            if (dateFromInput.value && dateFromInput.value > this.value) {
                dateFromInput.value = this.value;
            }
        });
    }
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add smooth animations to calendar cells
    const calendarCells = document.querySelectorAll('.calendar-day-cell');
    calendarCells.forEach(cell => {
        cell.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05) translateZ(0)';
        });
        
        cell.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) translateZ(0)';
        });
    });
});

// Export functionality (optional)
function exportCalendarData(format = 'csv') {
    const data = [];
    Object.entries(calendarOrders).forEach(([date, orders]) => {
        orders.forEach(order => {
            data.push({
                data: date,
                cliente: order.Nome || order.Username,
                stato: order.Stato,
                totale: order.Totale,
                ora_consegna: order.OraConsegna || '',
                note: order.Note || ''
            });
        });
    });
    
    if (format === 'csv') {
        exportToCSV(data, 'calendario_ordini.csv');
    } else if (format === 'json') {
        exportToJSON(data, 'calendario_ordini.json');
    }
}

function exportToCSV(data, filename) {
    const csvContent = "data:text/csv;charset=utf-8," 
        + "Data,Cliente,Stato,Totale,Ora Consegna,Note\n"
        + data.map(row => 
            `${row.data},${row.cliente},${row.stato},${row.totale},${row.ora_consegna},"${row.note}"`
        ).join("\n");
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToJSON(data, filename) {
    const jsonContent = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data, null, 2));
    const link = document.createElement("a");
    link.setAttribute("href", jsonContent);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print functionality
function printCalendar() {
    window.print();
}

// Add print styles
const printStyles = `
@media print {
    .filters-section,
    .modal,
    .btn {
        display: none !important;
    }
    
    .modern-calendar {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .calendar-header {
        background: #f0f0f0 !important;
        color: #000 !important;
    }
    
    .calendar-grid {
        font-size: 12px;
    }
    
    .calendar-day-cell {
        background: white !important;
        color: #000 !important;
    }
}
`;

// Inject print styles
const printStyleSheet = document.createElement('style');
printStyleSheet.textContent = printStyles;
document.head.appendChild(printStyleSheet);
</script>

<?php
// Optional: Add export buttons if needed
if (isset($_GET['export'])) {
    $exportFormat = $_GET['export'];
    $exportData = [];
    
    foreach ($processedOrders as $date => $orders) {
        foreach ($orders as $order) {
            $exportData[] = [
                'Data' => $date,
                'Cliente' => $order['Nome'] ?? $order['Username'] ?? '',
                'Stato' => $order['Stato'],
                'Totale' => $order['Totale'],
                'Ora_Consegna' => $order['OraConsegna'] ?? '',
                'Note' => $order['Note'] ?? ''
            ];
        }
    }
    
    if ($exportFormat === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="calendario_ordini_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Data', 'Cliente', 'Stato', 'Totale', 'Ora Consegna', 'Note']);
        
        foreach ($exportData as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    } elseif ($exportFormat === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="calendario_ordini_' . date('Y-m-d') . '.json"');
        
        echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>