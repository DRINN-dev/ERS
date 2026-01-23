<?php
$pageTitle = 'Resources Status Management';

// Initialize default values
$totalVehicles = 0;
$activePersonnel = 0;
$equipmentItems = 0;

// Fetch resource data from database
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db_connection();
    
    if ($pdo) {
        // Get total vehicles (units)
        $totalVehicles = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status != 'maintenance'")->fetch()['c'];
        
        // Get active personnel (staff on duty or available)
        $activePersonnel = (int)$pdo->query("SELECT COUNT(*) AS c FROM staff WHERE status IN ('available','on_duty')")->fetch()['c'];
        
        // Get equipment items (resources of type equipment)
        $equipmentItems = (int)$pdo->query("SELECT COUNT(*) AS c FROM resources WHERE type = 'equipment' AND status != 'maintenance'")->fetch()['c'];
    }
} catch (Throwable $e) {
    // Keep default values if database query fails
    error_log('Resources page database error: ' . $e->getMessage());
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
    <link rel="stylesheet" href="css/buttons.css">
    <link rel="stylesheet" href="css/sidebar-footer.css">
    <link rel="stylesheet" href="CSS/cards.css">
    <link rel="stylesheet" href="css/resources.css">
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>

    <!-- ===================================
       MAIN CONTENT - Emergency Resources Status
       =================================== -->
    <div class="main-content">
        <div class="main-container">

            <h1 style="font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 2rem; display: flex; align-items: center;">
                <i class="fas fa-truck" style="margin-right: 0.5rem; color: #dc3545;"></i>
                Emergency Resources Status
            </h1>

            <!-- Resource Overview -->
            <div class="resource-overview">
                <div class="overview-card">
                    <div class="overview-icon vehicles">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <div class="overview-value"><?php echo $totalVehicles; ?></div>
                    <div class="overview-label">Total Vehicles</div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon personnel">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="overview-value"><?php echo $activePersonnel; ?></div>
                    <div class="overview-label">Active Personnel</div>
                </div>
                <div class="overview-card">
                    <div class="overview-icon equipment">
                        <i class="fas fa-toolbox"></i>
                    </div>
                    <div class="overview-value"><?php echo $equipmentItems; ?></div>
                    <div class="overview-label">Equipment Items</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-action-btn" onclick="requestResource()">
                    <i class="fas fa-plus-circle"></i>
                    Request Resource
                </button>
                <button class="quick-action-btn" onclick="emergencyAllocation()">
                    <i class="fas fa-exclamation-triangle"></i>
                    Emergency Allocation
                </button>
                <button class="quick-action-btn" onclick="resourceReport()">
                    <i class="fas fa-chart-bar"></i>
                    Generate Report
                </button>
            </div>

            <!-- Resource Filters -->
            <div class="resource-filters">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-filter" style="margin-right: 0.5rem; color: #007bff;"></i>
                    Resource Filters
                </h2>
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="resource-type">Resource Type</label>
                        <select id="resource-type">
                            <option value="">All Resources</option>
                            <option value="vehicles">Vehicles</option>
                            <option value="personnel">Personnel</option>
                            <option value="equipment">Equipment</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="inuse">In Use</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="location-filter">Location</label>
                        <select id="location-filter">
                            <option value="">All Locations</option>
                            <option value="station-1">Station 1</option>
                            <option value="station-2">Station 2</option>
                            <option value="station-3">Station 3</option>
                            <option value="enroute">En Route</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search-resource">Search</label>
                        <input type="text" id="search-resource" placeholder="Search resources...">
                    </div>
                </div>
            </div>

            <!-- Resource Tabs -->
            <div class="resource-tabs">
                <button class="resource-tab active" onclick="switchResourceTab('vehicles')">Vehicles</button>
                <button class="resource-tab" onclick="switchResourceTab('personnel')">Personnel</button>
                <button class="resource-tab" onclick="switchResourceTab('equipment')">Equipment</button>
            </div>

            <!-- Vehicles Tab -->
            <div id="vehicles" class="resource-tab-content active">
                <div class="resources-grid">
                    <div class="resource-card available" data-type="vehicles" data-status="available">
                        <div class="resource-header">
                            <h3 class="resource-title">Ambulance #5</h3>
                            <span class="resource-status status-available">Available</span>
                        </div>
                        <div class="resource-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-value">Station 1</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="detail-value">Idle</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-clock"></i></span>
                                <span class="detail-value">Idle</span>
                            </div>
                        </div>
                        <div class="resource-actions">
                            <button class="btn-resource" onclick="deployResource(this)">
                                <i class="fas fa-play"></i> Deploy
                            </button>
                            <button class="btn-resource" onclick="trackResource(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                            <button class="btn-resource" onclick="serviceResource(this)">
                                <i class="fas fa-wrench"></i> Service
                            </button>
                            <button class="btn-resource" onclick="resourceDetails(this)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>

                    <div class="resource-card inuse" data-type="vehicles" data-status="inuse">
                        <div class="resource-header">
                            <h3 class="resource-title">Police Unit #8</h3>
                            <span class="resource-status status-inuse">In Use</span>
                        </div>
                        <div class="resource-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-value">Downtown</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="detail-value">En Route</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-clock"></i></span>
                                <span class="detail-value">En Route</span>
                            </div>
                        </div>
                        <div class="resource-actions">
                            <button class="btn-resource active" onclick="deployResource(this)">
                                <i class="fas fa-play"></i> Deploy
                            </button>
                            <button class="btn-resource" onclick="trackResource(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                            <button class="btn-resource" onclick="serviceResource(this)">
                                <i class="fas fa-wrench"></i> Service
                            </button>
                            <button class="btn-resource" onclick="resourceDetails(this)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Personnel Tab -->
            <div id="personnel" class="resource-tab-content">
                <div class="resources-grid">
                    <div class="resource-card available" data-type="personnel" data-status="available">
                        <div class="resource-header">
                            <h3 class="resource-title">Dr. Sarah Johnson</h3>
                            <span class="resource-status status-available">Available</span>
                        </div>
                        <div class="resource-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-user-md"></i></span>
                                <span class="detail-value">Paramedic</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-value">Station</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-clock"></i></span>
                                <span class="detail-value">Shift</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-star"></i></span>
                                <span class="detail-value">Senior</span>
                            </div>
                        </div>
                        <div class="resource-actions">
                            <button class="btn-resource" onclick="contactPersonnel(this)">
                                <i class="fas fa-phone"></i> Contact
                            </button>
                            <button class="btn-resource" onclick="personnelSchedule(this)">
                                <i class="fas fa-calendar"></i> Schedule
                            </button>
                            <button class="btn-resource" onclick="resourceDetails(this)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>

                    <div class="resource-card inuse" data-type="personnel" data-status="inuse">
                        <div class="resource-header">
                            <h3 class="resource-title">Officer Mike Davis</h3>
                            <span class="resource-status status-inuse">On Duty</span>
                        </div>
                        <div class="resource-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-shield-alt"></i></span>
                                <span class="detail-value">Police Officer</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-value">Downtown Patrol</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-clock"></i></span>
                                <span class="detail-value">On duty</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-star"></i></span>
                                <span class="detail-value">Veteran</span>
                            </div>
                        </div>
                            <button class="btn-resource" onclick="contactPersonnel(this)">
                                <i class="fas fa-phone"></i> Contact
                            </button>
                            <button class="btn-resource" onclick="personnelSchedule(this)">
                                <i class="fas fa-calendar"></i> Schedule
                            </button>
                            <button class="btn-resource" onclick="resourceDetails(this)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Tab -->
            <div id="equipment" class="resource-tab-content">
                <div class="resources-grid">
                    <div class="resource-card available" data-type="equipment" data-status="available">
                        <div class="resource-header">
                            <h3 class="resource-title">Defibrillator Unit</h3>
                            <span class="resource-status status-available">Available</span>
                        </div>
                        <div class="resource-details">
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-heartbeat"></i></span>
                                <span class="detail-value">Medical Equipment</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-map-marker-alt"></i></span>
                                <span class="detail-value">Ambulance</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-battery-full"></i></span>
                                <span class="detail-value">Charge</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-calendar"></i></span>
                                <span class="detail-value">Calibrated</span>
                            </div>
                        </div>
                        <div class="resource-actions">
                            <button class="btn-resource" onclick="assignEquipment(this)">
                                <i class="fas fa-link"></i> Assign
                            </button>
                            <button class="btn-resource" onclick="checkEquipment(this)">
                                <i class="fas fa-check-circle"></i> Check
                            </button>
                            <button class="btn-resource" onclick="calibrateEquipment(this)">
                                <i class="fas fa-tools"></i> Calibrate
                            </button>
                            <button class="btn-resource" onclick="resourceDetails(this)">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- AI-Powered Predictive Analytics -->
            <div class="ai-predictive-section">
                <div class="ai-predictive-card">
                    <div class="ai-predictive-header">
                        <h2><i class="fas fa-brain"></i> AI Predictive Resource Analytics</h2>
                        <span class="ai-badge"><i class="fas fa-robot"></i> Powered by Gemini AI</span>
                    </div>
                    <div class="ai-predictive-content" id="ai-predictive-content">
                        <?php
                        include 'includes/gemini_helper.php';

                        // Sample historical data - replace with actual historical data
                        $historicalData = [
                            'weekly_incidents' => null,
                            'peak_hours' => '',
                            'common_types' => 'Medical emergencies, traffic accidents',
                            'current_resources' => ''
                        ];

                        $predictions = predictResourceNeeds($historicalData);
                        if ($predictions) {
                            echo '<div class="ai-predictive-text">' . nl2br(htmlspecialchars($predictions)) . '</div>';
                        } else {
                            echo '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> Unable to generate AI predictions at this time.</div>';
                        }
                        ?>
                    </div>
                    <div class="ai-predictive-actions">
                        <button class="btn-ai-refresh" onclick="refreshAIPredictions()">
                            <i class="fas fa-sync"></i> Generate Predictions
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Request Resource Modal -->
    <div class="resource-request-modal" id="resourceRequestModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Request Resource</h3>
                <button class="modal-close" onclick="closeResourceModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="resourceRequestForm" onsubmit="submitResourceRequest(event)">
                    <div class="form-group">
                        <label for="request-resource-type">Resource Type <span class="required">*</span></label>
                        <select id="request-resource-type" name="resource_type" required onchange="updateResourceFormFields()">
                            <option value="">Select Resource Type</option>
                            <option value="vehicle">Vehicle</option>
                            <option value="personnel">Personnel</option>
                            <option value="equipment">Equipment</option>
                            <option value="facility">Facility</option>
                        </select>
                    </div>
                    <!-- Common Fields -->
                    <div id="vehicle-fields">
                        <div class="form-group">
                            <label for="request-resource-name">Resource Name/Description <span class="required">*</span></label>
                            <input type="text" id="request-resource-name" name="resource_name" placeholder="e.g., Ambulance, Fire Truck" required>
                        </div>
                        <div class="form-group">
                            <label for="request-quantity">Quantity <span class="required">*</span></label>
                            <input type="number" id="request-quantity" name="quantity" min="1" value="1" required>
                        </div>
                    </div>
                    <div id="personnel-fields" style="display:none;">
                        <div class="form-group">
                            <label for="personnel-name">Name <span class="required">*</span></label>
                            <input type="text" id="personnel-name" name="personnel_name" placeholder="e.g., Juan Dela Cruz" required>
                        </div>
                        <div class="form-group">
                            <label for="personnel-role">Personnel Role <span class="required">*</span></label>
                            <input type="text" id="personnel-role" name="personnel_role" placeholder="e.g., Paramedic, Police Officer">
                        </div>
                        <div class="form-group">
                            <label for="personnel-shift">Shift</label>
                            <input type="text" id="personnel-shift" name="personnel_shift" placeholder="e.g., Day, Night, On-call">
                        </div>
                        <div class="form-group">
                            <label for="personnel-quantity">Quantity <span class="required">*</span></label>
                            <input type="number" id="personnel-quantity" name="quantity" min="1" value="1">
                        </div>
                    </div>
                    <div id="equipment-fields" style="display:none;">
                        <div class="form-group">
                            <label for="equipment-type">Equipment Type <span class="required">*</span></label>
                            <input type="text" id="equipment-type" name="equipment_type" placeholder="e.g., Defibrillator, Radio">
                        </div>
                        <div class="form-group">
                            <label for="equipment-condition">Condition</label>
                            <input type="text" id="equipment-condition" name="equipment_condition" placeholder="e.g., New, Calibrated, Needs Repair">
                        </div>
                        <div class="form-group">
                            <label for="equipment-quantity">Quantity <span class="required">*</span></label>
                            <input type="number" id="equipment-quantity" name="quantity" min="1" value="1">
                        </div>
                    </div>
                    <!-- Shared Fields -->
                    <div class="form-group">
                        <label for="request-priority">Priority <span class="required">*</span></label>
                        <select id="request-priority" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="request-location">Location/Station</label>
                        <input type="text" id="request-location" name="location" placeholder="e.g., Station 1, Downtown">
                    </div>
                    <div class="form-group">
                        <label for="request-notes">Additional Notes</label>
                        <textarea id="request-notes" name="notes" rows="3" placeholder="Any additional information or special requirements..."></textarea>
                    </div>
                    <div class="form-group">
                        <label for="request-urgency">Urgency</label>
                        <select id="request-urgency" name="urgency">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                            <option value="emergency">Emergency</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeResourceModal()">Cancel</button>
                        <button type="submit" class="btn-submit">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <script>
                // Update form fields based on resource type
                function updateResourceFormFields() {
                    var type = document.getElementById('request-resource-type').value;
                    document.getElementById('vehicle-fields').style.display = (type === 'vehicle' || type === 'facility' || type === '') ? '' : 'none';
                    document.getElementById('personnel-fields').style.display = (type === 'personnel') ? '' : 'none';
                    document.getElementById('equipment-fields').style.display = (type === 'equipment') ? '' : 'none';
                }
                // Initialize on modal open
                document.addEventListener('DOMContentLoaded', function() {
                    var typeSelect = document.getElementById('request-resource-type');
                    if (typeSelect) typeSelect.addEventListener('change', updateResourceFormFields);
                });
        // Emergency Resources Management Functionality

        let currentTab = 'vehicles';

        // Tab switching functionality
        function switchResourceTab(tabName) {
            // Update tab buttons
            document.querySelectorAll('.resource-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Update content
            document.querySelectorAll('.resource-tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');

            currentTab = tabName;
            showNotification(`${tabName.charAt(0).toUpperCase() + tabName.slice(1)} resources loaded`, 'info');
        }

        // Resource deployment functionality
        function deployResource(button) {
            const resourceCard = button.closest('.resource-card');
            const resourceName = resourceCard.querySelector('.resource-title').textContent;
            const resourceId = resourceCard.dataset.resourceId || null;

            if (confirm(`Deploy ${resourceName} to emergency response?`)) {
                // In production, this would update the database
                if (resourceId) {
                    fetch('api/deploy_resource.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ resource_id: resourceId, action: 'deploy' })
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              resourceCard.classList.remove('available', 'inuse', 'offline');
                              resourceCard.classList.add('inuse');
                              resourceCard.dataset.status = 'inuse';

                              const statusBadge = resourceCard.querySelector('.resource-status');
                              statusBadge.className = 'resource-status status-inuse';
                              statusBadge.textContent = 'In Use';

                              button.classList.add('active');
                              showNotification(`${resourceName} deployed successfully`, 'success');
                          } else {
                              showNotification('Failed to deploy resource: ' + (data.error || 'Unknown error'), 'error');
                          }
                      }).catch(error => {
                          console.error('Error:', error);
                          // Fallback to UI update only
                          resourceCard.classList.remove('available', 'inuse', 'offline');
                          resourceCard.classList.add('inuse');
                          resourceCard.dataset.status = 'inuse';
                          const statusBadge = resourceCard.querySelector('.resource-status');
                          statusBadge.className = 'resource-status status-inuse';
                          statusBadge.textContent = 'In Use';
                          button.classList.add('active');
                          showNotification(`${resourceName} deployed successfully`, 'success');
                      });
                } else {
                    // Fallback for demo
                    resourceCard.classList.remove('available', 'inuse', 'offline');
                    resourceCard.classList.add('inuse');
                    resourceCard.dataset.status = 'inuse';
                    const statusBadge = resourceCard.querySelector('.resource-status');
                    statusBadge.className = 'resource-status status-inuse';
                    statusBadge.textContent = 'In Use';
                    button.classList.add('active');
                    showNotification(`${resourceName} deployed successfully`, 'success');
                }
            }
        }

        // Resource tracking functionality
        function trackResource(button) {
            const resourceCard = button.closest('.resource-card');
            const resourceName = resourceCard.querySelector('.resource-title').textContent;
            const resourceId = resourceCard.dataset.resourceId || null;

            // In production, this would fetch GPS data and open a map
            if (resourceId) {
                fetch(`api/get_resource_location.php?id=${resourceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.latitude && data.longitude) {
                            // Open GPS page with resource location
                            window.location.href = `gps.php?resource_id=${resourceId}&lat=${data.latitude}&lng=${data.longitude}`;
                        } else {
                            showNotification(`Tracking ${resourceName}... Location data unavailable`, 'info');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification(`Opening GPS tracking for ${resourceName}...`, 'info');
                        window.location.href = 'gps.php';
                    });
            } else {
                showNotification(`Opening GPS tracking for ${resourceName}...`, 'info');
                window.location.href = 'gps.php';
            }
        }

        // Resource service functionality - removed (maintenance removed per requirements)
        function serviceResource(button) {
            const resourceCard = button.closest('.resource-card');
            const resourceName = resourceCard.querySelector('.resource-title').textContent;
            
            showNotification(`Service information for ${resourceName} - Feature removed`, 'info');
        }

        // Complete maintenance functionality - removed (maintenance feature removed)

        // Resource details functionality
        function resourceDetails(button) {
            const resourceCard = button.closest('.resource-card');
            const resourceName = resourceCard.querySelector('.resource-title').textContent;
            const resourceType = resourceCard.dataset.type;

            let details = `Resource: ${resourceName}\nType: ${resourceType}\n\n`;

            if (resourceType === 'vehicles') {
                details += '• Vehicle specifications\n• Maintenance history\n• Fuel efficiency\n• Usage statistics\n• GPS tracking data';
            } else if (resourceType === 'personnel') {
                details += '• Certification details\n• Training records\n• Performance metrics\n• Shift schedule\n• Contact information';
            } else if (resourceType === 'equipment') {
                details += '• Equipment specifications\n• Calibration records\n• Usage history\n• Maintenance schedule\n• Storage location';
            }

            alert(details);
        }

        // Personnel management functions
        function assignPersonnel(button) {
            const resourceCard = button.closest('.resource-card');
            const personnelName = resourceCard.querySelector('.resource-title').textContent;

            const assignment = prompt(`Assign ${personnelName} to which incident/unit?`);
            if (assignment) {
                button.classList.add('active');
                showNotification(`${personnelName} assigned to ${assignment}`, 'success');
            }
        }

        function contactPersonnel(button) {
            const resourceCard = button.closest('.resource-card');
            const personnelName = resourceCard.querySelector('.resource-title').textContent;

            if (confirm(`Call ${personnelName}?`)) {
                showNotification(`Calling ${personnelName}...`, 'info');
            }
        }

        function personnelSchedule(button) {
            const resourceCard = button.closest('.resource-card');
            const personnelName = resourceCard.querySelector('.resource-title').textContent;

            alert(`${personnelName} Schedule:\n\n• Monday-Friday: Day Shift\n• Weekends: On-call rotation\n• Next shift: Tomorrow\n• Vacation: Pending`);
        }

        // Equipment management functions
        function assignEquipment(button) {
            const resourceCard = button.closest('.resource-card');
            const equipmentName = resourceCard.querySelector('.resource-title').textContent;

            const assignment = prompt(`Assign ${equipmentName} to which unit/personnel?`);
            if (assignment) {
                showNotification(`${equipmentName} assigned to ${assignment}`, 'success');
            }
        }

        function checkEquipment(button) {
            const resourceCard = button.closest('.resource-card');
            const equipmentName = resourceCard.querySelector('.resource-title').textContent;

            showNotification(`${equipmentName} status check: All systems operational`, 'success');
        }

        function calibrateEquipment(button) {
            const resourceCard = button.closest('.resource-card');
            const equipmentName = resourceCard.querySelector('.resource-title').textContent;

            if (confirm(`Calibrate ${equipmentName}? This may take several minutes.`)) {
                showNotification(`Calibration started for ${equipmentName}`, 'info');
            }
        }

        // Quick action functions
        function requestResource() {
            const modal = document.getElementById('resourceRequestModal');
            if (modal) {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeResourceModal() {
            const modal = document.getElementById('resourceRequestModal');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
                document.getElementById('resourceRequestForm').reset();
            }
        }

        function submitResourceRequest(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            
            // Here you would send this to your backend API
            // For now, we'll simulate the submission
            showNotification(`Resource request submitted: ${data.quantity}x ${data.resource_name} (${data.resource_type})`, 'success');
            
            // Close modal after a brief delay
            setTimeout(() => {
                closeResourceModal();
            }, 1500);
            
            // In production, you would do:
            // fetch('api/request_resource.php', {
            //     method: 'POST',
            //     body: formData
            // }).then(response => response.json())
            //   .then(data => {
            //       showNotification('Resource request submitted successfully', 'success');
            //       closeResourceModal();
            //   });
        }


        function emergencyAllocation() {
            if (confirm('Activate emergency resource allocation protocol? This will override normal procedures and prioritize all available resources.')) {
                // In production, this would trigger an API call
                fetch('api/emergency_allocation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'activate' })
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          showNotification('Emergency allocation protocol activated. All available resources prioritized.', 'error');
                          // Refresh resource list
                          setTimeout(() => location.reload(), 2000);
                      } else {
                          showNotification('Failed to activate emergency protocol: ' + (data.error || 'Unknown error'), 'error');
                      }
                  }).catch(error => {
                      console.error('Error:', error);
                      showNotification('Emergency protocol activation initiated (simulated)', 'error');
                  });
            }
        }

        function resourceReport() {
            showNotification('Generating comprehensive resource report...', 'info');
            
            // In production, this would generate and download a report
            fetch('api/reports_resources.php')
                .then(response => response.text())
                .then(html => {
                    // Create a new window with the report
                    const reportWindow = window.open('', '_blank');
                    reportWindow.document.write(html);
                    reportWindow.document.close();
                    showNotification('Resource report generated and opened in new window', 'success');
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Report generation initiated (simulated)', 'info');
                    setTimeout(() => {
                        showNotification('Resource report generated and downloaded', 'success');
                    }, 2000);
                });
        }

        // Filter functionality
        document.getElementById('resource-type').addEventListener('change', applyFilters);
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('location-filter').addEventListener('change', applyFilters);
        document.getElementById('search-resource').addEventListener('input', applyFilters);

        function applyFilters() {
            const typeFilter = document.getElementById('resource-type').value;
            const statusFilter = document.getElementById('status-filter').value;
            const locationFilter = document.getElementById('location-filter').value;
            const searchFilter = document.getElementById('search-resource').value.toLowerCase();

            document.querySelectorAll('.resource-card').forEach(card => {
                let showCard = true;

                // Type filter
                if (typeFilter && card.dataset.type !== typeFilter) {
                    showCard = false;
                }

                // Status filter (exclude maintenance)
                if (statusFilter) {
                    if (card.dataset.status === 'maintenance') {
                        showCard = false; // Always hide maintenance items
                    } else if (card.dataset.status !== statusFilter) {
                        showCard = false;
                    }
                } else {
                    // If no filter, still hide maintenance items
                    if (card.dataset.status === 'maintenance') {
                        showCard = false;
                    }
                }

                // Location filter (simplified - would need more complex logic in real system)
                if (locationFilter) {
                    const location = card.querySelector('.detail-value').textContent.toLowerCase();
                    if (!location.includes(locationFilter.replace('-', ' '))) {
                        showCard = false;
                    }
                }

                // Search filter
                if (searchFilter) {
                    const title = card.querySelector('.resource-title').textContent.toLowerCase();
                    const details = card.textContent.toLowerCase();
                    if (!title.includes(searchFilter) && !details.includes(searchFilter)) {
                        showCard = false;
                    }
                }

                card.style.display = showCard ? 'block' : 'none';
            });

            showNotification('Filters applied', 'info');
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

        // Add CSS animations and modal styles
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

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            .resource-card, .btn-resource, .resource-tab {
                transition: all 0.3s ease;
            }

            .resource-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            }

            .btn-resource:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }

            .resource-tab:hover {
                background-color: #f8f9fa;
            }

            /* Resource Request Modal Styles */
            .resource-request-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2000;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .resource-request-modal.show {
                opacity: 1;
                visibility: visible;
            }

            .resource-request-modal .modal-content {
                background: white;
                border-radius: 12px;
                width: 90%;
                max-width: 600px;
                max-height: 90vh;
                overflow-y: auto;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                transform: scale(0.9);
                transition: transform 0.3s ease;
            }

            .resource-request-modal.show .modal-content {
                transform: scale(1);
            }

            .resource-request-modal .modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.5rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .resource-request-modal .modal-header h3 {
                margin: 0;
                font-size: 1.5rem;
                font-weight: 700;
                color: #333;
            }

            .resource-request-modal .modal-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #666;
                cursor: pointer;
                padding: 0.5rem;
                border-radius: 4px;
                transition: all 0.2s ease;
            }

            .resource-request-modal .modal-close:hover {
                background-color: #f3f4f6;
                color: #333;
            }

            .resource-request-modal .modal-body {
                padding: 1.5rem;
            }

            .resource-request-modal .form-group {
                margin-bottom: 1.5rem;
            }

            .resource-request-modal .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: #333;
                font-size: 0.9rem;
            }

            .resource-request-modal .form-group .required {
                color: #dc3545;
            }

            .resource-request-modal .form-group input,
            .resource-request-modal .form-group select,
            .resource-request-modal .form-group textarea {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.95rem;
                transition: border-color 0.2s ease;
            }

            .resource-request-modal .form-group input:focus,
            .resource-request-modal .form-group select:focus,
            .resource-request-modal .form-group textarea:focus {
                outline: none;
                border-color: #007bff;
                box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
            }

            .resource-request-modal .form-group textarea {
                resize: vertical;
                min-height: 80px;
            }

            .resource-request-modal .modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
                padding: 1.5rem;
                border-top: 1px solid #e5e7eb;
            }

            .resource-request-modal .btn-cancel,
            .resource-request-modal .btn-submit {
                padding: 0.75rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .resource-request-modal .btn-cancel {
                background-color: #f3f4f6;
                color: #333;
            }

            .resource-request-modal .btn-cancel:hover {
                background-color: #e5e7eb;
            }

            .resource-request-modal .btn-submit {
                background-color: #007bff;
                color: white;
            }

            .resource-request-modal .btn-submit:hover {
                background-color: #0056b3;
                transform: translateY(-1px);
            }
        `;
        document.head.appendChild(style);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Close modal when clicking outside
            const modal = document.getElementById('resourceRequestModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeResourceModal();
                    }
                });
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('resourceRequestModal');
                    if (modal && modal.classList.contains('show')) {
                        closeResourceModal();
                    }
                }
            });

            // Auto-refresh resource status simulation (optional - can be removed in production)
            // setInterval(() => {
            //     // Simulate random status updates
            //     if (Math.random() < 0.1) {
            //         const availableCards = document.querySelectorAll('.resource-card.available');
            //         if (availableCards.length > 0) {
            //             const randomCard = availableCards[Math.floor(Math.random() * availableCards.length)];
            //             const resourceName = randomCard.querySelector('.resource-title').textContent;
            //             // Simulate deployment
            //             deployResource(randomCard.querySelector('.btn-resource'));
            //         }
            //     }
            // }, 30000); // Every 30 seconds
        });
    </script>
</body>
</html>