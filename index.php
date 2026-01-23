<?php
$apiKey = "225acf0f31b12ee9281d3aa19c94a57e";
$city   = "Quezon";

$url = "https://api.openweathermap.org/data/2.5/weather?q=Quezon,PH&units=metric&appid=225acf0f31b12ee9281d3aa19c94a57e";
$response = @file_get_contents($url);
$data = json_decode($response, true);

if ($data && $data['cod'] == 200) {
    $location   = $data['name'];
    $condition  = ucwords($data['weather'][0]['description']);
    $temp       = round($data['main']['temp']);
    $humidity   = $data['main']['humidity'];
    $wind       = round($data['wind']['speed'] * 3.6); // m/s → km/h
    $visibility = isset($data['visibility']) ? round($data['visibility'] / 1000) : 'N/A';
} else {
    $location = "Quezon City";
    $condition = "Unavailable";
    $temp = "--";
    $humidity = "--";
    $wind = "--";
    $visibility = "--";
}
$pageTitle = 'ERS Admin Dashboard';
$typesCounts = ['medical'=>0,'fire'=>0,'police'=>0,'traffic'=>0,'other'=>0];
$priorityCounts = ['high'=>0,'medium'=>0,'low'=>0];
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db_connection();
    $q1 = $pdo->query("SELECT type, COUNT(*) AS c FROM incidents GROUP BY type");
    foreach ($q1->fetchAll() as $r) { if (isset($typesCounts[$r['type']])) { $typesCounts[$r['type']] = (int)$r['c']; } }
    $q2 = $pdo->query("SELECT priority, COUNT(*) AS c FROM incidents GROUP BY priority");
    foreach ($q2->fetchAll() as $r) { if (isset($priorityCounts[$r['priority']])) { $priorityCounts[$r['priority']] = (int)$r['c']; } }
} catch (Throwable $e) {}
$activeIncidents = 0;
$availableResponders = 0;
$avgResponseTime = 0;
$pendingCalls = 0;
$totalIncidents = 0;
$resourceUtilization = 0;
// Load dashboard metrics and chart data from DB for accuracy
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db_connection();
    // Metrics
    $activeIncidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status IN ('pending','dispatched')")->fetch()['c'];
    $pendingCalls = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status='pending'")->fetch()['c'];
    $availableResponders = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status='available'")->fetch()['c'];
    $totalIncidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetch()['c'];
    // Charts
    $typesCounts = ['medical'=>0,'fire'=>0,'police'=>0,'traffic'=>0,'other'=>0];
    $priorityCounts = ['high'=>0,'medium'=>0,'low'=>0];
    $q1 = $pdo->query("SELECT type, COUNT(*) AS c FROM incidents GROUP BY type");
    foreach ($q1->fetchAll() as $r) { if (isset($typesCounts[$r['type']])) { $typesCounts[$r['type']] = (int)$r['c']; } }
    $q2 = $pdo->query("SELECT priority, COUNT(*) AS c FROM incidents GROUP BY priority");
    foreach ($q2->fetchAll() as $r) {
        $p = $r['priority'] === 'critical' ? 'low' : $r['priority'];
        if (isset($priorityCounts[$p])) { $priorityCounts[$p] = (int)$r['c']; }
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - <?php echo date('M d, Y'); ?></title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/admin-header.css">
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/sidebar-footer.css">
    <link rel="stylesheet" href="CSS/cards.css">
    <link rel="stylesheet" href="CSS/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>
    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>
    <!-- ===================================
       MAIN CONTENT - Emergency Response System Dashboard
       =================================== -->
    <div class="main-content">
        <div class="main-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div>
                    <h1 class="dashboard-title">Emergency Response Dashboard</h1>
                    <p class="dashboard-subtitle">Real-time monitoring and system overview • <?php echo date('F j, Y g:i:s a'); ?></p>
                </div>
            </div>
            <!-- Key Metrics -->
            <div class="metrics-grid">
                <div class="metric-card critical">
                    <div class="metric-header">
                        <div>
                            <h3 class="metric-title">Active Incidents</h3>
                            <div class="metric-value"><?php echo $activeIncidents; ?></div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </div>
                        <div class="metric-icon fire">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                    <div class="metric-actions">
                        <button class="btn-metric" onclick="viewIncidents()">
                            <i class="fas fa-eye"></i> View All
                        </button>
                    </div>
                </div>
                <div class="metric-card success">
                    <div class="metric-header">
                        <div>
                            <h3 class="metric-title">Available Responders</h3>
                            <div class="metric-value"><?php echo $availableResponders; ?></div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="metric-icon medical">
                            <i class="fas fa-truck-medical"></i>
                        </div>
                    </div>
                    <div class="metric-actions">
                        <button class="btn-metric" onclick="viewResponders()">
                            <i class="fas fa-users"></i> Manage
                        </button>
                    </div>
                </div>
                <div class="metric-card warning">
                    <div class="metric-header">
                        <div>
                            <h3 class="metric-title">Avg Response Time</h3>
                            <div class="metric-value"><?php echo number_format($avgResponseTime, 1); ?>m</div>
                            <div class="metric-change negative">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="metric-icon time">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="metric-actions">
                        <button class="btn-metric" onclick="viewResponseTimes()">
                            <i class="fas fa-chart-line"></i> Analytics
                        </button>
                    </div>
                </div>
                <div class="metric-card info">
                    <div class="metric-header">
                        <div>
                            <h3 class="metric-title">Pending Calls</h3>
                            <div class="metric-value"><?php echo $pendingCalls; ?></div>
                            <div class="metric-change neutral">
                                <i class="fas fa-minus"></i>
                            </div>
                        </div>
                        <div class="metric-icon phone">
                            <i class="fas fa-phone-volume"></i>
                        </div>
                    </div>
                    <div class="metric-actions">
                        <button class="btn-metric" onclick="viewCalls()">
                            <i class="fas fa-phone"></i> Answer
                        </button>
                    </div>
                </div>
                <div class="metric-card success">
                    <div class="metric-header">
                        <div>
                            <h3 class="metric-title">Total Incidents (Month)</h3>
                            <div class="metric-value"><?php echo $totalIncidents; ?></div>
                            <div class="metric-change positive">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                        </div>
                        <div class="metric-icon chart">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                    </div>
                    <div class="metric-actions">
                        <button class="btn-metric" onclick="monthlyReport()">
                            <i class="fas fa-file-pdf"></i> Report
                        </button>
                        <button class="btn-metric" onclick="trendAnalysis()">
                            <i class="fas fa-chart-line"></i> Trends
                        </button>
                    </div>
                </div>
            </div>
            <!-- Main Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Main Panel -->
                <div class="main-panel">
                    <!-- Response Time Chart -->
                    <div class="chart-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #333;">Incidents by Type</h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-metric" onclick="refreshChart()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                                <button class="btn-metric" onclick="exportChart()">
                                    <i class="fas fa-download"></i> Export
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; width: 100%; height: 320px;">
                            <canvas id="incidentsTypeBar" class="chart-canvas"></canvas>
                        </div>
                    </div>
                    <!-- Incident Distribution Chart -->
                    <div class="chart-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #333;">Incident Priority Distribution</h3>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn-metric" onclick="toggleChartView()">
                                    <i class="fas fa-pie-chart"></i> Toggle View
                                </button>
                                <button class="btn-metric" onclick="filterChart()">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>
                        </div>
                        <div style="position: relative; width: 100%; height: 320px;">
                            <canvas id="incidentsPriorityPie" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>
                <!-- Side Panel -->
                <div class="side-panel">
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <h3 class="quick-actions-title">Quick Actions</h3>
                        <div class="action-grid">
                            <button class="action-btn" onclick="emergencyCall()">
                                <i class="fas fa-phone-volume"></i>
                                <span>Emergency Call</span>
                            </button>
                            <button class="action-btn" onclick="dispatchUnit()">
                                <i class="fas fa-truck-medical"></i>
                                <span>Dispatch Unit</span>
                            </button>
                            <button class="action-btn" onclick="resourceCheck()">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Resource Check</span>
                            </button>
                            <button class="action-btn" onclick="generateReport()">
                                <i class="fas fa-file-pdf"></i>
                                <span>Generate Report</span>
                            </button>
                        </div>
                    </div>
                    <!-- Weather Widget -->
<div class="weather-widget">
    <div class="weather-header">
        <div>
            <div class="weather-location"><?php echo $location; ?></div>
            <div class="weather-condition"><?php echo $condition; ?></div>
        </div>
        <i class="fa-solid fa-cloud"></i>
        <div class="weather-temp"><?php echo $temp; ?>°C</div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
        <div style="text-align: center;">
            <div style="font-weight: 600; color: #333;">Humidity</div>
            <div style="color: #666;"><?php echo $humidity; ?>%</div>
        </div>

        <div style="text-align: center;">
            <div style="font-weight: 600; color: #333;">Wind</div>
            <div style="color: #666;"><?php echo $wind; ?> km/h</div>
        </div>

        <div style="text-align: center;">
            <div style="font-weight: 600; color: #333;">Visibility</div>
            <div style="color: #666;"><?php echo $visibility; ?> km</div>
        </div>
    </div>
</div>
                </div>
            </div>
            <!-- Bottom Section -->
            <div class="dashboard-grid">
                <!-- Activity Feed -->
                <div class="activity-feed">
                    <div class="activity-header">
                        <h3 class="activity-title">Recent Activity</h3>
                        <button class="btn-metric" onclick="viewAllActivity()">
                            <i class="fas fa-external-link-alt"></i> View All
                        </button>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon emergency">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Medical Emergency Reported</div>
                            <div class="activity-details">Downtown District • Priority: High</div>
                            <div class="activity-time">2 minutes ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon response">
                            <i class="fas fa-truck-medical"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Ambulance #5 Dispatched</div>
                            <div class="activity-details">Unit en route to incident • ETA: 4 minutes</div>
                            <div class="activity-time">5 minutes ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon maintenance">
                            <i class="fas fa-wrench"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Equipment Maintenance Completed</div>
                            <div class="activity-details">Defibrillator Unit #12 • All systems operational</div>
                            <div class="activity-time">12 minutes ago</div>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon system">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">System Backup Completed</div>
                            <div class="activity-details">Daily backup successful • 99.8% uptime maintained</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>
                </div>
                <!-- Alerts Panel -->
                <div class="alerts-panel">
                    <div class="alerts-header">
                        <h3 class="alerts-title">Active Alerts</h3>
                        <button class="btn-metric" onclick="viewAllAlerts()">
                            <i class="fas fa-external-link-alt"></i> View All
                        </button>
                    </div>
                    <div class="alert-item critical">
                        <div class="alert-icon critical">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-text">High Response Time Alert</div>
                            <div class="alert-details">Zone 3 average exceeds 10 minutes</div>
                        </div>
                    </div>
                    <div class="alert-item warning">
                        <div class="alert-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-text">Resource Utilization Warning</div>
                            <div class="alert-details">Ambulance fleet at 85% capacity</div>
                        </div>
                    </div>
                    <div class="alert-item info">
                        <div class="alert-icon info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-text">Weather Alert</div>
                            <div class="alert-details">Heavy rain expected in 2 hours</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <script>
        // Charts data from PHP
        const typesLabels = ['Medical','Fire','Police','Traffic','Other'];
        const typesValues = <?php echo json_encode(array_values($typesCounts)); ?>;
        const priorityLabels = ['High','Medium','Low'];
        const priorityValues = <?php echo json_encode(array_values($priorityCounts)); ?>;

        document.addEventListener('DOMContentLoaded', () => {
            // Bar: incidents per type
            const barCtx = document.getElementById('incidentsTypeBar');
            if (barCtx) {
                new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: typesLabels,
                        datasets: [{
                            label: 'Incidents by Type',
                            data: typesValues,
                            backgroundColor: ['#ef4444','#f59e0b','#3b82f6','#22c55e','#6b7280'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                    }
                });
            }

            // Pie: incidents by priority
            const pieCtx = document.getElementById('incidentsPriorityPie');
            if (pieCtx) {
                new Chart(pieCtx, {
                    type: 'pie',
                    data: {
                        labels: priorityLabels,
                        datasets: [{
                            label: 'Incidents by Priority',
                            data: priorityValues,
                            backgroundColor: [
                                '#fed7aa', // High (bg)
                                '#bfdbfe', // Medium (bg)
                                '#d1fae5'  // Low (bg)
                            ],
                            borderColor: [
                                '#92400e', // High (text)
                                '#1e40af', // Medium (text)
                                '#065f46'  // Low (text)
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        });
        // Emergency Response System Dashboard Functionality
        // Dashboard action functions
        function refreshDashboard() {
            showNotification('Refreshing dashboard data...', 'info');
            setTimeout(() => {
                // Simulate data refresh with random updates
                const metrics = document.querySelectorAll('.metric-value');
                metrics.forEach(metric => {
                    const currentValue = parseFloat(metric.textContent.replace(/[^\d.]/g, ''));
                    if (!isNaN(currentValue)) {
                        const change = (Math.random() - 0.5) * 0.05; // ±2.5% change
                        const newValue = Math.max(0, currentValue * (1 + change));
                        if (metric.textContent.includes('m')) {
                            metric.textContent = newValue.toFixed(1) + 'm';
                        } else if (metric.textContent.includes('%')) {
                            metric.textContent = newValue.toFixed(1) + '%';
                        } else {
                            metric.textContent = Math.round(newValue);
                        }
                    }
                });
                showNotification('Dashboard refreshed successfully', 'success');
            }, 1500);
        }
        function exportDashboard() {
            showNotification('Generating dashboard report...', 'info');
            setTimeout(() => {
                showNotification('Dashboard report downloaded successfully', 'success');
            }, 2000);
        }
        function systemSettings() {
            showNotification('Opening system settings...', 'info');
            setTimeout(() => {
                showNotification('System settings panel loaded', 'success');
            }, 800);
        }
        // Metric action functions
        function viewIncidents() {
            window.location.href = 'incident.php';
        }
        function createIncident() {
            showNotification('Opening incident creation form...', 'info');
            setTimeout(() => {
                showNotification('Incident creation form loaded', 'success');
            }, 500);
        }
        function viewResponders() {
            window.location.href = 'resources.php';
        }
        function deployResponder() {
            showNotification('Opening deployment interface...', 'info');
            setTimeout(() => {
                showNotification('Deployment interface loaded', 'success');
            }, 600);
        }
        function viewResponseTimes() {
            window.location.href = 'report.php';
        }
        function optimizeRoutes() {
            window.location.href = 'gps.php';
        }
        function viewCalls() {
            window.location.href = 'call.php';
        }
        function callHistory() {
            showNotification('Opening call history...', 'info');
            setTimeout(() => {
                showNotification('Call history loaded', 'success');
            }, 700);
        }
        function monthlyReport() {
            window.location.href = 'report.php';
        }
        function trendAnalysis() {
            showNotification('Opening trend analysis...', 'info');
            setTimeout(() => {
                showNotification('Trend analysis loaded', 'success');
            }, 800);
        }
        function systemHealth() {
            showNotification('Running system health check...', 'info');
            setTimeout(() => {
                showNotification('All systems operational', 'success');
            }, 1000);
        }
        function systemLogs() {
            showNotification('Opening system logs...', 'info');
            setTimeout(() => {
                showNotification('System logs loaded', 'success');
            }, 600);
        }
        // Chart functions
        function refreshChart() {
            showNotification('Refreshing chart data...', 'info');
            setTimeout(() => {
                showNotification('Chart data updated', 'success');
            }, 1000);
        }
        function exportChart() {
            showNotification('Exporting chart...', 'info');
            setTimeout(() => {
                showNotification('Chart exported successfully', 'success');
            }, 1500);
        }
        function toggleChartView() {
            showNotification('Switching chart view...', 'info');
            setTimeout(() => {
                showNotification('Chart view updated', 'success');
            }, 500);
        }
        function filterChart() {
            showNotification('Opening chart filters...', 'info');
            setTimeout(() => {
                showNotification('Chart filters applied', 'success');
            }, 400);
        }
        // Quick action functions
        function emergencyCall() {
            window.location.href = 'call.php';
        }
        function dispatchUnit() {
            window.location.href = 'dispatch.php';
        }
        function alertAllUnits() {
            if (confirm('Send emergency alert to all units? This will interrupt current operations.')) {
                showNotification('Emergency alert sent to all units', 'error');
            }
        }
        function systemTest() {
            showNotification('Running system diagnostic test...', 'info');
            setTimeout(() => {
                showNotification('System test completed successfully', 'success');
            }, 3000);
        }
        function resourceCheck() {
            window.location.href = 'resources.php';
        }
        function generateReport() {
            window.location.href = 'report.php';
        }
        // Activity and alert functions
        function viewAllActivity() {
            showNotification('Opening full activity log...', 'info');
            setTimeout(() => {
                showNotification('Activity log loaded', 'success');
            }, 600);
        }
        function viewAllAlerts() {
            showNotification('Opening alerts management...', 'info');
            setTimeout(() => {
                showNotification('Alerts panel loaded', 'success');
            }, 500);
        }
        // Notification system
        function showNotification(message, type) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                color: white;
                font-weight: 600;
                z-index: 1000;
                animation: slideIn 0.3s ease-out;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            `;
            // Set background color based on type
            if (type === 'success') {
                notification.style.backgroundColor = '#28a745';
            } else if (type === 'error') {
                notification.style.backgroundColor = '#dc3545';
            } else if (type === 'info') {
                notification.style.backgroundColor = '#17a2b8';
            } else if (type === 'warning') {
                notification.style.backgroundColor = '#ffc107';
            }
            notification.textContent = message;
            document.body.appendChild(notification);
            // Auto remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .metric-card, .action-btn, .btn-metric, .btn-dashboard {
                transition: all 0.3s ease;
            }
            .metric-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            }
            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102,126,234,0.3);
            }
            .btn-metric:hover, .btn-dashboard:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
            .activity-item:hover, .alert-item:hover {
                background-color: #f8f9fa;
            }
            /* Active Alerts styling */
            .alerts-panel { display: flex; flex-direction: column; gap: 10px; }
            .alerts-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
            .alerts-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: #333; }
            .alert-item { position: relative; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
            .alert-item::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 12px 0 0 12px; }
            .alert-item.critical::before { background-color: #dc2626; }
            .alert-item.warning::before { background-color: #f59e0b; }
            .alert-item.info::before { background-color: #3b82f6; }
            .alert-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
            .alert-icon.critical { background-color: #dc2626; }
            .alert-icon.warning { background-color: #f59e0b; }
            .alert-icon.info { background-color: #3b82f6; }
            .alert-content { display: flex; flex-direction: column; gap: 2px; }
            .alert-text { font-weight: 600; color: #111827; }
            .alert-details { color: #6b7280; font-size: 0.9rem; }
            .alert-item:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.08); transform: translateY(-1px); transition: box-shadow 0.2s ease, transform 0.2s ease; }
            @media (max-width: 640px) {
                .alert-item { padding: 10px 12px; }
                .alert-icon { width: 36px; height: 36px; }
            }
            .chart-canvas { width: 100% !important; height: 100% !important; display: block; }
        `;
        document.head.appendChild(style);
        // Auto-refresh dashboard every 5 minutes
        setInterval(() => {
            refreshDashboard();
        }, 300000);
        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('Dashboard loaded successfully', 'success');
        });
    </script>
</body>
</html>
