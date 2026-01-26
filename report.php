<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Analytics & Reporting';

// Initial metrics (server-side for first render)
$avgResponseTime = 0.0;
$totalIncidentsMonth = 0;
$lastMonthIncidents = 0;
$resourceUtilization = 0.0;
$successRate = 0.0;
try {
    $pdo = get_db_connection();
    if ($pdo) {
        $totalIncidentsMonth = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())")->fetch()['c'];
        $lastMonthIncidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE YEAR(created_at)=YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at)=MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetch()['c'];
        $resolvedCount = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status='resolved'")->fetch()['c'];
        $totalIncidentsAll = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents")->fetch()['c'];
        $successRate = $totalIncidentsAll > 0 ? round(($resolvedCount / $totalIncidentsAll) * 100, 1) : 0.0;
        $totalUnits = (int)$pdo->query("SELECT COUNT(*) AS c FROM units")->fetch()['c'];
        $busyUnits = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status IN ('assigned','enroute','on_scene')")->fetch()['c'];
        $resourceUtilization = $totalUnits > 0 ? round(($busyUnits / $totalUnits) * 100, 1) : 0.0;
        $row = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, assigned_at, on_scene_at)) AS avg_min FROM dispatches WHERE assigned_at IS NOT NULL AND on_scene_at IS NOT NULL AND YEAR(assigned_at)=YEAR(CURDATE()) AND MONTH(assigned_at)=MONTH(CURDATE())")->fetch();
        if ($row && $row['avg_min'] !== null) { $avgResponseTime = round((float)$row['avg_min'], 1); }
    }
} catch (Throwable $e) {
    // keep defaults if any error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/admin-header.css">
    <link rel="stylesheet" href="css/sidebar-footer.css">
    <link rel="stylesheet" href="css/report.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>

    <!-- ===================================
       MAIN CONTENT - Emergency Response Analytics & Reporting
       =================================== -->
    <div class="main-content">
        <div class="main-container">

            <h1 style="font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 2rem; display: flex; align-items: center;">
                <i class="fas fa-chart-line" style="margin-right: 0.5rem; color: #007bff;"></i>
                Analytics & Reporting
            </h1>

            <!-- Key Metrics Overview -->
            <div class="analytics-grid">
                <div class="analytics-card response-time">
                    <div class="metric-label">Average Response Time</div>
                    <div class="metric-display">
                        <div class="metric-value" id="metricAvgResponse"><?php echo number_format($avgResponseTime, 1); ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-down"></i>
                            
                        </div>
                    </div>
                    <div style="color: #666; font-size: 0.9rem;">Target: &lt; 10m</div>
                </div>

                <div class="analytics-card incidents">
                    <div class="metric-label">Total Incidents (This Month)</div>
                    <div class="metric-display">
                        <div class="metric-value" id="metricIncidentsMonth"><?php echo (int)$totalIncidentsMonth; ?></div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up" id="metricIncidentsDelta"><?php echo max(0, (int)$totalIncidentsMonth - (int)$lastMonthIncidents); ?></i>
                            
                        </div>
                    </div>
                    <div style="color: #666; font-size: 0.9rem;">Last month: <span id="metricLastMonth"><?php echo (int)$lastMonthIncidents; ?></span></div>
                </div>

                <div class="analytics-card resources">
                    <div class="metric-label">Resource Utilization</div>
                    <div class="metric-display">
                        <div class="metric-value" id="metricUtilization"><?php echo number_format($resourceUtilization, 1); ?>%</div>
                        <div class="metric-change neutral">
                            <i class="fas fa-minus"></i>
                            
                        </div>
                    </div>
                    <div style="color: #666; font-size: 0.9rem;">Target: 70–85%</div>
                </div>

                <div class="analytics-card performance">
                    <div class="metric-label">Success Rate</div>
                    <div class="metric-display">
                        <div class="metric-value" id="metricSuccess"><?php echo number_format($successRate, 1); ?>%</div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up"></i>
                            
                        </div>
                    </div>
                    <div style="color: #666; font-size: 0.9rem;">Industry average: 92–96%</div>
                </div>
            </div>

            <!-- Report Filters -->
            <div class="report-filters">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-filter" style="margin-right: 0.5rem; color: #007bff;"></i>
                    Report Filters
                </h2>
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="report-type">Report Type</label>
                        <select id="report-type">
                            <option value="">All Reports</option>
                            <option value="incident">Incident Reports</option>
                            <option value="performance">Performance Reports</option>
                            <option value="resource">Resource Reports</option>
                            <option value="trend">Trend Analysis</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="time-period">Time Period</label>
                        <select id="time-period">
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="incident-type">Incident Type</label>
                        <select id="incident-type">
                            <option value="">All Types</option>
                            <option value="medical">Medical Emergency</option>
                            <option value="fire">Fire</option>
                            <option value="accident">Traffic Accident</option>
                            <option value="crime">Crime</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="priority-level">Priority Level</label>
                        <select id="priority-level">
                            <option value="">All Priorities</option>
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                </div>
                <div class="filter-row">
                    <div class="date-range">
                        <div class="filter-group">
                            <label for="start-date">Start Date</label>
                            <input type="date" id="start-date" value="">
                        </div>
                        <div class="filter-group">
                            <label for="end-date">End Date</label>
                            <input type="date" id="end-date" value="">
                        </div>
                        <button class="btn-report primary" onclick="applyFilters()">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- AI-Powered Insights -->
            <div class="ai-insights-section">
                <div class="ai-insights-card">
                    <div class="ai-insights-header">
                        <h2><i class="fas fa-brain"></i> AI-Powered Insights</h2>
                        <span class="ai-badge"><i class="fas fa-robot"></i> Powered by Gemini AI</span>
                    </div>
                    <div class="ai-insights-content" id="ai-insights-content">
                        <?php
                        include 'includes/gemini_helper.php';

                        // Sample data - replace with actual data from your database
                        $reportData = [
                            'total_incidents' => null,
                            'avg_response_time' => '',
                            'resource_utilization' => '',
                            'active_responders' => null
                        ];

                        $insights = generateReportInsights($reportData);
                        if ($insights) {
                            echo '<div class="ai-insight-text">' . nl2br(htmlspecialchars($insights)) . '</div>';
                        } else {
                            echo '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> Unable to generate AI insights at this time.</div>';
                        }
                        ?>
                    </div>
                    <div class="ai-insights-actions">
                        <button class="btn-ai-refresh" onclick="refreshAIInsights()">
                            <i class="fas fa-sync"></i> Refresh Insights
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Reports -->
            <div class="quick-reports">
                <div class="report-card" onclick="generateIncidentReport()">
                    <div class="report-icon incident">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="report-title">Incident Summary Report</div>
                    <div class="report-description">Comprehensive overview of all incidents with response times and outcomes</div>
                    <div class="report-actions">
                        <button class="btn-report primary" onclick="generateIncidentReport()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                        <button class="btn-report" onclick="viewIncidentReport()">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="generatePerformanceReport()">
                    <div class="report-icon performance">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="report-title">Performance Analytics</div>
                    <div class="report-description">Team performance metrics, response times, and success rates</div>
                    <div class="report-actions">
                        <button class="btn-report primary" onclick="generatePerformanceReport()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                        <button class="btn-report" onclick="viewPerformanceReport()">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>

                <div class="report-card" onclick="generateResourceReport()">
                    <div class="report-icon resource">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="report-title">Resource Utilization Report</div>
                    <div class="report-description">Equipment and personnel usage statistics and efficiency metrics</div>
                    <div class="report-actions">
                        <button class="btn-report primary" onclick="generateResourceReport()">
                            <i class="fas fa-file-pdf"></i> Generate
                        </button>
                        <button class="btn-report" onclick="viewResourceReport()">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Incident Response Times</h3>
                    <div class="chart-controls">
                        <button class="btn-report" onclick="refreshChart()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button class="btn-report" onclick="exportChart()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div style="position: relative; width: 100%; height: 320px;">
                    <canvas id="responseTimeChart" class="chart-canvas"></canvas>
                </div>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Incident Types Distribution</h3>
                    <div class="chart-controls">
                        <button class="btn-report" onclick="toggleChartView()">
                            <i class="fas fa-pie-chart"></i> Toggle View
                        </button>
                        <button class="btn-report" onclick="exportChart()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                <div style="position: relative; width: 100%; height: 320px;">
                    <canvas id="incidentsTypesChart" class="chart-canvas"></canvas>
                </div>
            </div>

            <!-- Recent Incidents Table -->
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Recent Incidents</h3>
                </div>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Incident ID</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Response Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentIncidentsBody">
                            <tr><td colspan="7" style="color:#6b7280">Loading incidents…</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Performance Metrics Table -->
            <div class="data-table">
                <div class="table-header">
                    <h3 class="table-title">Performance Metrics</h3>
                </div>
                <div class="table-container">
                    <table class="analytics-table">
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>Current Value</th>
                                <th>Target</th>
                                <th>Trend</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Average Response Time</td>
                                <td></td>
                                <td></td>
                                <td><div class="trend-indicator trend-up"><i class="fas fa-arrow-up"></i> Improving</div></td>
                                <td><span class="status-badge status-resolved">On Target</span></td>
                            </tr>
                            <tr>
                                <td>Incident Resolution Rate</td>
                                <td></td>
                                <td></td>
                                <td><div class="trend-indicator trend-up"><i class="fas fa-arrow-up"></i> Improving</div></td>
                                <td><span class="status-badge status-resolved">Excellent</span></td>
                            </tr>
                            <tr>
                                <td>Resource Utilization</td>
                                <td></td>
                                <td></td>
                                <td><div class="trend-indicator trend-neutral"><i class="fas fa-minus"></i> Stable</div></td>
                                <td><span class="status-badge status-resolved">Optimal</span></td>
                            </tr>
                            <tr>
                                <td>Equipment Downtime</td>
                                <td></td>
                                <td></td>
                                <td><div class="trend-indicator trend-down"><i class="fas fa-arrow-down"></i> Improving</div></td>
                                <td><span class="status-badge status-resolved">Excellent</span></td>
                            </tr>
                            <tr>
                                <td>Personnel Overtime</td>
                                <td></td>
                                <td></td>
                                <td><div class="trend-indicator trend-neutral"><i class="fas fa-minus"></i> Stable</div></td>
                                <td><span class="status-badge status-resolved">Acceptable</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="alerts-section">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-bell" style="margin-right: 0.5rem; color: #ffc107;"></i>
                    System Alerts & Notifications
                </h2>
                <div id="alerts-dynamic"></div>
            </div>
    <script>
    // --- Dynamic Alerts & Notifications ---
    let LAST_ALERTS = [];
    async function fetchAlerts() {
        try {
            const res = await fetch('api/alerts_active.php');
            const data = await res.json();
            if (!data.ok) return [];
            return data.data || [];
        } catch (e) { return []; }
    }

    function alertHtml(alert, idx) {
        let icon = '<i class="fas fa-info-circle"></i>';
        let cls = '';
        if (alert.type === 'critical') { icon = '<i class="fas fa-exclamation-triangle"></i>'; cls = 'critical'; }
        else if (alert.type === 'warning') { icon = '<i class="fas fa-exclamation-circle"></i>'; cls = 'warning'; }
        else if (alert.type === 'info') { icon = '<i class="fas fa-info-circle"></i>'; cls = 'info'; }
        return `
            <div class="alert-item ${cls}" data-alert-idx="${idx}">
                <div class="alert-info">
                    <div class="alert-title">${icon} ${alert.title || 'Alert'}</div>
                    <div class="alert-details">${alert.details || ''}</div>
                </div>
                <div class="alert-actions">
                    <button class="btn-report" onclick="dismissAlert(event)"><i class="fas fa-times"></i> Dismiss</button>
                </div>
            </div>
        `;
    }

    function showAlertPopup(alert) {
        showNotification(`${alert.title}: ${alert.details}`, alert.type || 'info');
    }

    async function renderAlerts() {
        const alerts = await fetchAlerts();
        const container = document.getElementById('alerts-dynamic');
        if (!container) return;
        if (!alerts.length) {
            container.innerHTML = '<div style="color:#888;padding:1em;">No active alerts at this time.</div>';
        } else {
            container.innerHTML = alerts.map(alertHtml).join('');
        }
        // Show popup for new alerts
        if (LAST_ALERTS.length) {
            alerts.forEach(a => {
                if (!LAST_ALERTS.find(b => b.title === a.title && b.details === a.details)) {
                    showAlertPopup(a);
                }
            });
        } else {
            alerts.forEach(showAlertPopup);
        }
        LAST_ALERTS = alerts;
    }

    // Dismiss alert (removes from UI only)
    function dismissAlert(e) {
        const item = e.target.closest('.alert-item');
        if (item) item.style.display = 'none';
        showNotification('Alert dismissed', 'info');
    }

    // Poll for new alerts every 10s
    document.addEventListener('DOMContentLoaded', function() {
        renderAlerts();
        setInterval(renderAlerts, 10000);
    });
    </script>

        </div>
    </div>

    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <script>
        // Emergency Response Analytics & Reporting Functionality
        let currentFilters = {};
        function navigateTo(page, params = {}) {
            const qs = new URLSearchParams(Object.entries(params).filter(([k,v]) => v !== undefined && v !== null && v !== '')).toString();
            window.location.href = qs ? `${page}?${qs}` : page;
        }
        function buildQuery(params) {
            const entries = Object.entries(params).filter(function(p){ return p[1] !== undefined && p[1] !== null && p[1] !== ''; });
            const qs = new URLSearchParams(entries).toString();
            return qs ? ('?' + qs) : '';
        }
        function getFilters() {
            const reportType = document.getElementById('report-type') ? document.getElementById('report-type').value : '';
            const timePeriod = document.getElementById('time-period') ? document.getElementById('time-period').value : '';
            const incidentType = document.getElementById('incident-type') ? document.getElementById('incident-type').value : '';
            const priorityLevel = document.getElementById('priority-level') ? document.getElementById('priority-level').value : '';
            const startDate = document.getElementById('start-date') ? document.getElementById('start-date').value : '';
            const endDate = document.getElementById('end-date') ? document.getElementById('end-date').value : '';
            // Use API param names: period, type, priority, start, end
            const filters = {};
            if (timePeriod) filters.period = timePeriod;
            if (incidentType) filters.type = incidentType;
            if (priorityLevel) filters.priority = priorityLevel;
            if (startDate) filters.start = startDate;
            if (endDate) filters.end = endDate;
            return filters;
        }

        // Report generation functions
        function generateIncidentReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_incident_summary.php' + qs, '_blank');
        }

        function generatePerformanceReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_performance.php' + qs, '_blank');
        }

        function generateResourceReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_resources.php' + qs, '_blank');
        }

        function generateTrendReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_trends.php' + qs, '_blank');
        }

        // View report functions
        function viewIncidentReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_incident_summary.php' + qs, '_blank');
        }

        function viewPerformanceReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_performance.php' + qs, '_blank');
        }

        function viewResourceReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_resources.php' + qs, '_blank');
        }

        function viewTrendReport() {
            const qs = buildQuery(currentFilters);
            window.open('api/reports_trends.php' + qs, '_blank');
        }

        // Chart functions
        function refreshChart() {
            showNotification('Refreshing chart data...', 'info');
            setTimeout(() => {
                showNotification('Chart data updated', 'success');
            }, 1000);
        }

        function exportChart() {
            showNotification('Exporting chart as image...', 'info');
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

        // Incident details
        function viewIncidentDetails(incidentId) {
            const details = `Incident Details: ${incidentId}\n\n` +
                          `• Type: Medical Emergency\n` +
                          `• Location: Downtown District\n` +
                          `• Priority: High\n` +
                          `• Response Time: \n` +
                          `• Units Dispatched: Ambulance, Police Unit\n` +
                          `• Status: Resolved\n` +
                          `• Outcome: Patient transported to hospital\n\n` +
                          `View full incident log and timeline?`;

            if (confirm(details)) {
                showNotification(`Opening detailed view for ${incidentId}`, 'info');
            }
        }

        // Filter functions
        function applyFilters() {
            currentFilters = getFilters();
            showNotification('Applying filters to reports...', 'info');
            refreshMetrics(currentFilters);
            refreshCharts(currentFilters);
            loadRecentIncidents(currentFilters);
            setTimeout(() => {
                showNotification('Filters applied', 'success');
            }, 500);
        }

        // Export functions
        function exportPDF() {
            showNotification('Generating PDF report...', 'info');
            setTimeout(() => {
                showNotification('PDF report downloaded successfully', 'success');
            }, 3000);
        }

        function exportExcel() {
            showNotification('Exporting data to Excel...', 'info');
            setTimeout(() => {
                showNotification('Excel file downloaded successfully', 'success');
            }, 2000);
        }

        function exportCSV() {
            showNotification('Exporting data to CSV...', 'info');
            setTimeout(() => {
                showNotification('CSV file downloaded successfully', 'success');
            }, 1500);
        }

        function exportJSON() {
            showNotification('Exporting data to JSON...', 'info');
            setTimeout(() => {
                showNotification('JSON file downloaded successfully', 'success');
            }, 1000);
        }

        function scheduleReport() {
            const frequency = prompt('How often should this report be generated?\n• Daily\n• Weekly\n• Monthly\n• Quarterly');
            if (frequency) {
                const email = prompt('Enter email address for report delivery:');
                if (email) {
                    showNotification(`Report scheduled for ${frequency.toLowerCase()} delivery to ${email}`, 'success');
                }
            }
        }

        function emailReport() {
            const email = prompt('Enter email address to send report to:');
            if (email) {
                showNotification(`Report sent to ${email}`, 'success');
            }
        }

        // Alert functions
        function investigateAlert() {
            showNotification('Opening investigation dashboard...', 'info');
            setTimeout(() => {
                showNotification('Investigation dashboard loaded', 'success');
            }, 1000);
        }

        function dismissAlert() {
            if (confirm('Dismiss this alert?')) {
                event.target.closest('.alert-item').style.display = 'none';
                showNotification('Alert dismissed', 'info');
            }
        }

        function viewResourceDetails() {
            showNotification('Opening resource utilization details...', 'info');
            setTimeout(() => {
                showNotification('Resource details loaded', 'success');
            }, 800);
        }

        function viewMonthlyReport() {
            showNotification('Opening monthly performance report...', 'info');
            setTimeout(() => {
                showNotification('Monthly report loaded', 'success');
            }, 1200);
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

            .report-card, .btn-report, .export-btn {
                transition: all 0.3s ease;
            }

            .report-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            }

            .btn-report:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }

            .export-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102,126,234,0.3);
            }

            .analytics-table tr:hover {
                background-color: #f8f9fa;
            }
            .chart-canvas { width: 100% !important; height: 100% !important; display: block; }
        `;
        document.head.appendChild(style);
        // Charts
        let responseChart = null;
        let typesChart = null;

        async function refreshCharts(filters = {}) {
            try {
                const qs = buildQuery(filters);
                const [respRes, metricsRes] = await Promise.all([
                    fetch('api/report_response_times_daily.php' + qs),
                    fetch('api/report_metrics.php' + qs)
                ]);
                const respData = await respRes.json();
                const metricsData = await metricsRes.json();
                if (respData.ok) {
                    const labels = respData.labels || [];
                    const data = respData.data || [];
                    const ctx = document.getElementById('responseTimeChart');
                    if (ctx) {
                        if (!responseChart) {
                            responseChart = new Chart(ctx, {
                                type: 'line',
                                data: { labels, datasets: [{
                                    label: 'Avg Response Time (min)',
                                    data,
                                    borderColor: '#3b82f6',
                                    backgroundColor: 'rgba(59,130,246,0.15)',
                                    tension: 0.3,
                                    fill: true,
                                }]},
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: { y: { beginAtZero: true } },
                                    plugins: { legend: { display: true } }
                                }
                            });
                        } else {
                            responseChart.data.labels = labels;
                            responseChart.data.datasets[0].data = data;
                            responseChart.update();
                        }
                    }
                }
                if (metricsData.ok) {
                    const typeCounts = metricsData.metrics?.incidents_by_type || {};
                    let labels = ['Medical','Fire','Police','Traffic','Other'];
                    let values = [
                        typeCounts.medical || 0,
                        typeCounts.fire || 0,
                        typeCounts.police || 0,
                        typeCounts.traffic || 0,
                        typeCounts.other || 0,
                    ];
                    if (filters.incident_type) {
                        const map = { medical: 'Medical', fire: 'Fire', police: 'Police', traffic: 'Traffic', accident: 'Traffic', crime: 'Police', other: 'Other' };
                        const wanted = map[filters.incident_type] || filters.incident_type;
                        const idx = labels.indexOf(wanted);
                        if (idx >= 0) { labels = [labels[idx]]; values = [values[idx]]; }
                    }
                    const ctx2 = document.getElementById('incidentsTypesChart');
                    if (ctx2) {
                        if (!typesChart) {
                            typesChart = new Chart(ctx2, {
                                type: 'doughnut',
                                data: {
                                    labels,
                                    datasets: [{
                                        label: 'Incidents by Type',
                                        data: values,
                                        backgroundColor: ['#ef4444','#f59e0b','#3b82f6','#22c55e','#6b7280'],
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });
                        } else {
                            typesChart.data.labels = labels;
                            typesChart.data.datasets[0].data = values;
                            typesChart.update();
                        }
                    }
                }
            } catch (e) { /* silent */ }
        }

        async function refreshMetrics(filters = {}) {
            try {
            const qs = buildQuery(filters);
            const res = await fetch('api/report_metrics.php' + qs);
                const data = await res.json();
                if (!data.ok) return;
                const m = data.metrics || {};
                const avgEl = document.getElementById('metricAvgResponse');
                const monthEl = document.getElementById('metricIncidentsMonth');
                const lastEl = document.getElementById('metricLastMonth');
                const deltaEl = document.getElementById('metricIncidentsDelta');
                const utilEl = document.getElementById('metricUtilization');
                const successEl = document.getElementById('metricSuccess');
                if (avgEl) avgEl.textContent = (m.avg_response_time_min ?? 0).toFixed(1);
                if (monthEl) monthEl.textContent = m.total_incidents_month ?? 0;
                if (lastEl) lastEl.textContent = m.total_incidents_last_month ?? 0;
                if (deltaEl) deltaEl.textContent = Math.max(0, (m.total_incidents_month ?? 0) - (m.total_incidents_last_month ?? 0));
                if (utilEl) utilEl.textContent = ((m.resource_utilization ?? 0)).toFixed(1) + '%';
                if (successEl) successEl.textContent = ((m.success_rate ?? 0)).toFixed(1) + '%';
            } catch (e) {
                // silent fail
            }
        }

        // Recent Incidents loader
        function periodToParams(period) {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            if (period === 'today') return { day: `${yyyy}-${mm}-${dd}` };
            if (period === 'month') return { month: `${yyyy}-${mm}` };
            return {};
        }

        async function loadRecentIncidents(filters = {}) {
            try {
                const extra = periodToParams(filters.period || '');
                const qs = buildQuery(extra);
                const res = await fetch('api/incidents_list.php' + qs);
                const data = await res.json();
                const items = data.ok ? (data.items || []) : [];
                renderRecentIncidents(items);
            } catch (e) {
                renderRecentIncidents([]);
            }
        }

        function labelForType(t) {
            switch (t) {
                case 'medical': return 'Medical Emergency';
                case 'fire': return 'Fire';
                case 'police': return 'Police Emergency';
                case 'traffic':
                case 'accident': return 'Traffic Accident';
                default: return 'Other';
            }
        }

        function renderRecentIncidents(items) {
            const tbody = document.getElementById('recentIncidentsBody');
            if (!tbody) return;
            if (!items.length) {
                tbody.innerHTML = `<tr><td colspan="7" style="color:#6b7280">No incidents found for selected period</td></tr>`;
                return;
            }
            function unitNameToParam(name){
                return (name||'').toLowerCase().replace(/\s+/g,'-').replace(/#/g,'').replace(/[^a-z0-9\-]/g,'');
            }
            tbody.innerHTML = items.slice(0, 10).map(i => {
                const code = i.incident_code || '';
                const type = labelForType(i.type);
                const loc = i.location || '';
                const pr = (i.priority || '').toUpperCase();
                const status = (i.status || '').replace('_',' ');
                const badgeClass = status.includes('resolve') ? 'status-resolved' : (status.includes('progress')||status.includes('dispatch')) ? 'status-pending' : (pr === 'CRITICAL' ? 'status-critical' : '');
                const unitBtn = i.assigned_unit ? `<button class=\"btn-report\" onclick=\"navigateTo('gps.php', { unit: '${unitNameToParam(i.assigned_unit)}' })\"><i class=\"fas fa-location-arrow\"></i> Track</button>` : '';
                return `
                <tr>
                    <td>${code}</td>
                    <td>${type}</td>
                    <td>${loc}</td>
                    <td>${pr || ''}</td>
                    <td>${i.response_time_min != null ? i.response_time_min + ' min' : ''}</td>
                    <td><span class="status-badge ${badgeClass}">${status || ''}</span></td>
                    <td>
                        <button class="btn-report" onclick="navigateTo('incident.php', { code: '${code}' })"><i class="fas fa-list"></i> Incident</button>
                        <button class="btn-report" onclick="navigateTo('dispatch.php', { code: '${code}' })"><i class="fas fa-headset"></i> Dispatch</button>
                        <button class="btn-report" onclick="navigateTo('resources.php', { tab: 'vehicles' })"><i class="fas fa-truck"></i> Resources</button>
                        ${unitBtn}
                    </td>
                </tr>`;
            }).join('');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            currentFilters = getFilters();
            refreshMetrics(currentFilters);
            refreshCharts(currentFilters);
            loadRecentIncidents(currentFilters);
            // KPI deep links
            const cardResp = document.querySelector('.analytics-card.response-time');
            const cardInc = document.querySelector('.analytics-card.incidents');
            const cardRes = document.querySelector('.analytics-card.resources');
            const cardPerf = document.querySelector('.analytics-card.performance');
            if (cardResp) cardResp.style.cursor='pointer', cardResp.addEventListener('click', () => navigateTo('dispatch.php', { period: currentFilters.period || 'month' }));
            if (cardInc) cardInc.style.cursor='pointer', cardInc.addEventListener('click', () => navigateTo('incident.php', { period: currentFilters.period || 'month' }));
            if (cardRes) cardRes.style.cursor='pointer', cardRes.addEventListener('click', () => window.open('api/reports_resources.php' + buildQuery(currentFilters), '_blank'));
            if (cardPerf) cardPerf.style.cursor='pointer', cardPerf.addEventListener('click', () => navigateTo('incident.php', { period: currentFilters.period || 'month', status: 'resolved' }));
            setInterval(() => { refreshMetrics(currentFilters); refreshCharts(currentFilters); }, 15000);
        });
    </script>
</body>
</html>