<?php
/**
 * Reusable Calendar Component
 * 
 * Usage:
 * $calendarConfig = [
 *     'orders' => $orders_array,
 *     'date_field' => 'DataConsegna', // or 'DataOrdine'
 *     'status_filter' => ['pronto', 'consegnato'], // optional
 *     'show_navigation' => true,
 *     'show_filters' => true,
 *     'ajax_enabled' => true
 * ];
 * include('templates/calendar.php');
 */

// Default configuration
$defaultConfig = [
    'orders' => [],
    'date_field' => 'DataConsegna',
    'status_filter' => null,
    'show_navigation' => true,
    'show_filters' => true,
    'ajax_enabled' => false,
    'current_month' => date('m'),
    'current_year' => date('Y')
];

// Merge with provided config
$config = array_merge($defaultConfig, $calendarConfig ?? []);

// Get current month/year from GET parameters if navigation is enabled
if ($config['show_navigation']) {
    $config['current_month'] = $_GET['month'] ?? $config['current_month'];
    $config['current_year'] = $_GET['year'] ?? $config['current_year'];
}

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
$dayCount = 1;

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
if ($week[0] !== null || $week[1] !== null || $week[2] !== null || $week[3] !== null || $week[4] !== null || $week[5] !== null || $week[6] !== null) {
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
if (!empty($config['orders'])) {
    foreach ($config['orders'] as $order) {
        // Apply status filter if provided
        if ($config['status_filter'] && !in_array(strtolower($order['Stato']), array_map('strtolower', $config['status_filter']))) {
            continue;
        }
        
        $dateField = $config['date_field'];
        if (isset($order[$dateField]) && !empty($order[$dateField])) {
            $orderDate = date('Y-m-d', strtotime($order[$dateField]));
            if (!isset($processedOrders[$orderDate])) {
                $processedOrders[$orderDate] = [];
            }
            $processedOrders[$orderDate][] = $order;
        }
    }
}

// Generate navigation URLs
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
?>

<style>
.calendar-component {
    background: white;
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    overflow: hidden;
}

.calendar-header {
    background: linear-gradient(135deg, #8b4513 0%, #654321 100%);
    color: white;
    padding: 1.5rem;
}

.calendar-nav {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.calendar-table {
    border: none;
    margin-bottom: 0;
}

.calendar-table th,
.calendar-table td {
    border: 1px solid #e9ecef;
    padding: 0.75rem;
    text-align: center;
    vertical-align: top;
    height: 80px;
    position: relative;
    transition: all 0.2s ease;
}

.calendar-table thead th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    height: auto;
    padding: 1rem 0.75rem;
}

.calendar-day {
    font-weight: bold;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.calendar-cell {
    position: relative;
    height: 100%;
}

.calendar-cell.other-month {
    background-color: #f8f9fa;
    color: #6c757d;
}

.calendar-cell.today {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.calendar-cell:hover {
    background-color: #f0f8ff;
    cursor: pointer;
}

.order-indicator {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 0.7rem;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.order-dots {
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 2px;
}

.order-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    display: inline-block;
}

.order-dot.status-pending { background-color: #ffc107; }
.order-dot.status-confirmed { background-color: #17a2b8; }
.order-dot.status-processing { background-color: #007bff; }
.order-dot.status-ready { background-color: #28a745; }
.order-dot.status-delivered { background-color: #6c757d; }

.calendar-filters {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.order-tooltip {
    position: absolute;
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    z-index: 1000;
    max-width: 200px;
    display: none;
}

@media (max-width: 768px) {
    .calendar-table th,
    .calendar-table td {
        padding: 0.5rem;
        font-size: 0.8rem;
        height: 60px;
    }
    
    .calendar-day {
        font-size: 0.8rem;
    }
    
    .order-indicator {
        font-size: 0.6rem;
        min-width: 16px;
        height: 16px;
    }
}
</style>

<div class="card calendar-component">
    <div class="calendar-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <i class="bi bi-calendar-month me-3" style="font-size: 1.5rem;"></i>
                <h4 class="mb-0">Calendario</h4>
            </div>
            <div class="d-flex align-items-center">
                <h5 class="mb-0 opacity-75"><?= $monthNames[$currentMonth] ?> <?= $currentYear ?></h5>
            </div>
        </div>
    </div>

    <?php if ($config['show_filters']): ?>
    <div class="calendar-filters">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <label class="form-label me-2 mb-0">Filtro stato:</label>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="filterCalendar(this.value)">
                        <option value="">Tutti gli stati</option>
                        <option value="in_attesa">In Attesa</option>
                        <option value="confermato">Confermato</option>
                        <option value="in_preparazione">In Preparazione</option>
                        <option value="pronto">Pronto</option>
                        <option value="consegnato">Consegnato</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex align-items-center justify-content-md-end gap-2">
                    <small class="text-muted">Legenda:</small>
                    <span class="order-dot status-pending" title="In Attesa"></span>
                    <span class="order-dot status-confirmed" title="Confermato"></span>
                    <span class="order-dot status-processing" title="In Preparazione"></span>
                    <span class="order-dot status-ready" title="Pronto"></span>
                    <span class="order-dot status-delivered" title="Consegnato"></span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($config['show_navigation']): ?>
    <div class="calendar-nav">
        <div class="d-flex justify-content-between align-items-center">
            <a href="<?= htmlspecialchars($prevUrl) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-chevron-left me-2"></i>
                <?= $monthNames[$prevMonth] ?>
            </a>
            <div class="d-flex gap-2">
                <a href="<?= htmlspecialchars($todayUrl) ?>" class="btn btn-primary btn-sm">
                    <i class="bi bi-calendar-day me-2"></i>
                    Oggi
                </a>
            </div>
            <a href="<?= htmlspecialchars($nextUrl) ?>" class="btn btn-outline-secondary btn-sm">
                <?= $monthNames[$nextMonth] ?>
                <i class="bi bi-chevron-right ms-2"></i>
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-body p-0">
        <table class="table calendar-table">
            <thead>
                <tr>
                    <th scope="col">Lun</th>
                    <th scope="col">Mar</th>
                    <th scope="col">Mer</th>
                    <th scope="col">Gio</th>
                    <th scope="col">Ven</th>
                    <th scope="col">Sab</th>
                    <th scope="col">Dom</th>
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
                        $cellClasses = ['calendar-cell'];
                        
                        if (!$dayData['is_current_month']) {
                            $cellClasses[] = 'other-month';
                        }
                        if ($dayData['is_today']) {
                            $cellClasses[] = 'today';
                        }
                        ?>
                        <div class="<?= implode(' ', $cellClasses) ?>" 
                             data-date="<?= $currentDate ?>"
                             <?php if (!empty($dayOrders)): ?>
                             onclick="showDayOrders('<?= $currentDate ?>')"
                             data-bs-toggle="tooltip" 
                             title="<?= count($dayOrders) ?> ordini"
                             <?php endif; ?>>
                            
                            <div class="calendar-day"><?= $dayData['day'] ?></div>
                            
                            <?php if (!empty($dayOrders)): ?>
                                <span class="badge bg-danger order-indicator">
                                    <?= count($dayOrders) ?>
                                </span>
                                
                                <div class="order-dots">
                                    <?php 
                                    $statusCounts = [];
                                    foreach ($dayOrders as $order) {
                                        $status = strtolower(str_replace(' ', '_', $order['Stato']));
                                        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                                    }
                                    
                                    $maxDots = 5;
                                    $dotCount = 0;
                                    foreach ($statusCounts as $status => $count):
                                        if ($dotCount >= $maxDots) break;
                                        for ($i = 0; $i < min($count, $maxDots - $dotCount); $i++):
                                            if ($dotCount >= $maxDots) break;
                                    ?>
                                    <span class="order-dot status-<?= $status ?>"></span>
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

<!-- Modal for day details -->
<div class="modal fade" id="dayOrdersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-day me-2"></i>
                    Ordini del <span id="modalDate"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayOrdersContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<script>
// Calendar functionality
const calendarOrders = <?= json_encode($processedOrders) ?>;

function showDayOrders(date) {
    const orders = calendarOrders[date] || [];
    const modal = new bootstrap.Modal(document.getElementById('dayOrdersModal'));
    
    // Format date for display
    const dateObj = new Date(date + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('it-IT', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    document.getElementById('modalDate').textContent = formattedDate;
    
    if (orders.length === 0) {
        document.getElementById('dayOrdersContent').innerHTML = 
            '<p class="text-center text-muted">Nessun ordine per questa data.</p>';
    } else {
        let content = '<div class="table-responsive"><table class="table table-striped">';
        content += '<thead><tr><th>Cliente</th><th>Stato</th><th>Totale</th></tr></thead><tbody>';
        
        orders.forEach(order => {
            const statusClass = getStatusClass(order.Stato);
            content += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white me-2 d-flex align-items-center justify-content-center" 
                                 style="width: 30px; height: 30px; border-radius: 50%; font-size: 0.8rem;">
                                ${(order.Nome || order.Username).charAt(0).toUpperCase()}
                            </div>
                            ${order.Nome || order.Username}
                        </div>
                    </td>
                    <td>
                        <span class="badge ${statusClass}">
                            ${order.Stato}
                        </span>
                    </td>
                    <td class="fw-bold text-success">€${parseFloat(order.Totale).toFixed(2)}</td>
                </tr>
            `;
        });
        
        content += '</tbody></table></div>';
        document.getElementById('dayOrdersContent').innerHTML = content;
    }
    
    modal.show();
}

function getStatusClass(status) {
    const statusLower = status.toLowerCase();
    switch(statusLower) {
        case 'in attesa': return 'bg-warning text-dark';
        case 'confermato': return 'bg-info';
        case 'in preparazione': return 'bg-primary';
        case 'pronto': return 'bg-success';
        case 'consegnato': return 'bg-secondary';
        default: return 'bg-secondary';
    }
}

function filterCalendar(status) {
    // This would implement AJAX filtering if enabled
    console.log('Filtering by status:', status);
    // For now, reload page with filter parameter
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status_filter', status);
    } else {
        url.searchParams.delete('status_filter');
    }
    window.location.href = url.toString();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>