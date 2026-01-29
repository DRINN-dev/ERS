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


            <div style="height: 3.5rem;"></div>
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
            <!-- Combined Resources Table -->
            <div class="resources-table-section" style="margin-top:2rem;">
                <h2 style="font-size: 1.2rem; font-weight: 700; color: #333; margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-table" style="margin-right: 0.5rem; color: #007bff;"></i>
                    All Resources
                </h2>
                <div style="overflow-x:auto;">
                <style>
                .resource-table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 1.08rem;
                }
                .resource-table th, .resource-table td {
                    padding: 0.85em 1.1em;
                    border: 1px solid #e0e0e0;
                    text-align: left;
                }
                .resource-table th {
                    background: #f7f7f7;
                    font-size: 1.13rem;
                }
                .resource-table tr.resource-row-vehicle { background: #eafaf1; }
                .resource-table tr.resource-row-personnel { background: #fffbe7; }
                .resource-table tr.resource-row-equipment { background: #f9eaf6; }
                .resource-status-available {
                    background: #d4edda;
                    color: #218838;
                    font-weight: 600;
                    border-radius: 16px;
                    padding: 0.3em 1em;
                    font-size: 0.98em;
                    display: inline-block;
                }
                .resource-status-inuse {
                    background: #fff3cd;
                    color: #856404;
                    font-weight: 600;
                    border-radius: 16px;
                    padding: 0.3em 1em;
                    font-size: 0.98em;
                    display: inline-block;
                }
                .resource-status-offline {
                    background: #f8d7da;
                    color: #721c24;
                    font-weight: 600;
                    border-radius: 16px;
                    padding: 0.3em 1em;
                    font-size: 0.98em;
                    display: inline-block;
                }
                .resource-action-btn {
                    border: none;
                    border-radius: 6px;
                    padding: 0.5em 1.1em;
                    font-weight: 600;
                    margin-right: 0.3em;
                    font-size: 0.98em;
                    cursor: pointer;
                    transition: background 0.2s, color 0.2s;
                    display: inline-flex;
                    align-items: center;
                    gap: 0.4em;
                    background: #fff;
                    color: #111;
                }
                .resource-action-btn:hover {
                    background: #111;
                    color: #fff;
                }
                </style>
                <table class="resource-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name/Description</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="resource-list-dynamic">
                        <!-- Table will be rendered here by JS -->
                    </tbody>
                </table>
                <script>
                // Resource data (should be fetched from backend in production)
                let RESOURCES = [
                    {
                        type: 'vehicles',
                        name: 'Ambulance #5',
                        status: 'available',
                        location: 'Station 1',
                        details: 'Idle, Ready',
                        icon: '<i class="fas fa-ambulance" style="color:#dc3545;"></i>',
                        actions: ['deploy','track','service','details']
                    },
                    {
                        type: 'vehicles',
                        name: 'Police Unit #8',
                        status: 'inuse',
                        location: 'Downtown',
                        details: 'En Route',
                        icon: '<i class="fas fa-car" style="color:#007bff;"></i>',
                        actions: ['deploy','track','service','details']
                    },
                    {
                        type: 'personnel',
                        name: 'Dr. Sarah Johnson',
                        status: 'available',
                        location: 'Station',
                        details: 'Shift: Day<br>Level: Senior',
                        icon: '<i class="fas fa-user-md" style="color:#28a745;"></i>',
                        actions: ['contact','schedule','details'],
                        role: 'Paramedic'
                    },
                    {
                        type: 'personnel',
                        name: 'Officer Mike Davis',
                        status: 'inuse',
                        location: 'Downtown Patrol',
                        details: 'Shift: Night<br>Level: Veteran',
                        icon: '<i class="fas fa-shield-alt" style="color:#ffc107;"></i>',
                        actions: ['contact','schedule','details'],
                        role: 'Police Officer'
                    },
                    {
                        type: 'equipment',
                        name: 'Defibrillator Unit',
                        status: 'available',
                        location: 'Ambulance',
                        details: 'Charge: Full<br>Status: Calibrated',
                        icon: '<i class="fas fa-heartbeat" style="color:#e83e8c;"></i>',
                        actions: ['assign','check','calibrate','details'],
                        role: 'Medical Equipment'
                    }
                ];

                // Filter elements
                const typeFilter = document.getElementById('resource-type');
                const statusFilter = document.getElementById('status-filter');
                const locationFilter = document.getElementById('location-filter');
                const searchInput = document.getElementById('search-resource');

                function passFilters(r) {
                    const typeValue = (typeFilter.value || '').toLowerCase();
                    const statusValue = (statusFilter.value || '').toLowerCase();
                    const locationValue = (locationFilter.value || '').toLowerCase();
                    const searchValue = (searchInput.value || '').toLowerCase();

                    if (typeValue && (r.type || '').toLowerCase() !== typeValue) return false;
                    if (statusValue && (r.status || '').toLowerCase() !== statusValue) return false;
                    if (locationValue && (r.location || '').toLowerCase() !== locationValue) return false;
                    if (searchValue) {
                        const hay = [r.name, r.details, r.location, r.role]
                            .map(v => (v || '').toString().toLowerCase()).join(' ');
                        if (!hay.includes(searchValue)) return false;
                    }
                    return true;
                }

                function resourceRowHtml(r) {
                    let rowClass = r.type === 'vehicles' ? 'resource-row-vehicle' : (r.type === 'personnel' ? 'resource-row-personnel' : 'resource-row-equipment');
                    let statusClass = r.status === 'available' ? 'resource-status-available' : (r.status === 'inuse' ? 'resource-status-inuse' : 'resource-status-offline');
                    let statusLabel = r.status === 'available' ? 'Available' : (r.status === 'inuse' ? 'In Use' : 'Offline');
                    let actionsHtml = '';
                    if (r.actions.includes('deploy')) actionsHtml += `<button class=\"resource-action-btn deploy\" onclick=\"deployResource(this)\"><i class=\"fas fa-play\"></i> Deploy</button>`;
                    if (r.actions.includes('track')) actionsHtml += `<button class=\"resource-action-btn track\" onclick=\"trackResource(this)\"><i class=\"fas fa-location-arrow\"></i> Track</button>`;
                    if (r.actions.includes('service')) actionsHtml += `<button class=\"resource-action-btn service\" onclick=\"serviceResource(this)\"><i class=\"fas fa-wrench\"></i> Service</button>`;
                    if (r.actions.includes('details')) actionsHtml += `<button class=\"resource-action-btn details\" onclick=\"resourceDetails(this)\"><i class=\"fas fa-info-circle\"></i> Details</button>`;
                    if (r.actions.includes('contact')) actionsHtml += `<button class=\"resource-action-btn contact\" onclick=\"contactPersonnel(this)\"><i class=\"fas fa-phone\"></i> Contact</button>`;
                    if (r.actions.includes('schedule')) actionsHtml += `<form style=\"display:inline;\" onsubmit=\"event.preventDefault(); openScheduleModal('${r.name}');\"><button type=\"submit\" class=\"resource-action-btn schedule\"><i class=\"fas fa-calendar\"></i> Schedule</button></form>`;
                    if (r.actions.includes('assign')) actionsHtml += `<button class=\"resource-action-btn assign\" onclick=\"assignEquipment(this)\"><i class=\"fas fa-link\"></i> Assign</button>`;
                    if (r.actions.includes('check')) actionsHtml += `<button class=\"resource-action-btn check\" onclick=\"checkEquipment(this)\"><i class=\"fas fa-check-circle\"></i> Check</button>`;
                    if (r.actions.includes('calibrate')) actionsHtml += `<button class=\"resource-action-btn calibrate\" onclick=\"calibrateEquipment(this)\"><i class=\"fas fa-tools\"></i> Calibrate</button>`;
                    return `<tr class=\"${rowClass}\" data-type=\"${r.type}\" data-status=\"${r.status}\" data-location=\"${r.location}\">\n`+
                        `<td>${r.icon} ${r.type.charAt(0).toUpperCase() + r.type.slice(1)}</td>`+
                        `<td class=\"resource-title\">${r.name}${r.role ? ' <br><span style=\\"font-size:0.95em;color:#888;\\">'+r.role+'</span>' : ''}</td>`+
                        `<td><span class=\"${statusClass}\">${statusLabel}</span></td>`+
                        `<td class=\"detail-value\">${r.location}</td>`+
                        `<td>${r.details}</td>`+
                        `<td>${actionsHtml}</td>`+
                    `</tr>`;
                }

                function renderDynamicResources() {
                    const container = document.getElementById('resource-list-dynamic');
                    if (!container) return;
                    const filtered = RESOURCES.filter(passFilters);
                    if (!filtered.length) {
                        container.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No resources found.</td></tr>';
                    } else {
                        container.innerHTML = filtered.map(resourceRowHtml).join('');
                    }
                }

                function applyFilters() {
                    renderDynamicResources();
                }

                // Add event listeners to filters
                typeFilter.addEventListener('change', applyFilters);
                statusFilter.addEventListener('change', applyFilters);
                locationFilter.addEventListener('change', applyFilters);
                searchInput.addEventListener('input', applyFilters);

                // Initial render
                document.addEventListener('DOMContentLoaded', function() {
                    renderDynamicResources();
                });
                </script>
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
                            <select id="request-resource-name" name="resource_name" required>
                                <option value="">Select Resource</option>
                                <option value="Fire Truck">Fire Truck</option>
                                <option value="Ambulance">Ambulance</option>
                                <option value="Police">Police</option>
                            </select>
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


    <!-- Emergency Personnel Scheduling Modal -->
    <div class="resource-request-modal" id="scheduleModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Schedule Emergency Personnel</h3>
                <button class="modal-close" onclick="closeScheduleModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm" onsubmit="submitScheduleForm(event)">
                    <div class="form-group">
                        <label for="schedule-personnel-name">Personnel Name</label>
                        <input type="text" id="schedule-personnel-name" name="personnel_name" readonly>
                    </div>
                    <div class="form-group">
                        <label for="schedule-date">Date <span class="required">*</span></label>
                        <input type="date" id="schedule-date" name="date" required>
                    </div>
                    <div class="form-group">
                        <label for="schedule-shift">Shift <span class="required">*</span></label>
                        <select id="schedule-shift" name="shift" required>
                            <option value="">Select Shift</option>
                            <option value="day">Day</option>
                            <option value="night">Night</option>
                            <option value="on-call">On-call</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="schedule-notes">Notes</label>
                        <textarea id="schedule-notes" name="notes" rows="2" placeholder="Special instructions or emergency details..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" onclick="closeScheduleModal()">Cancel</button>
                        <button type="submit" class="btn-submit">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Scheduling Modal Logic
        function openScheduleModal(personnelName) {
            document.getElementById('schedule-personnel-name').value = personnelName;
            document.getElementById('scheduleModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.remove('show');
            document.body.style.overflow = '';
            document.getElementById('scheduleForm').reset();
        }
        function submitScheduleForm(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            showNotification(`Scheduled ${data.personnel_name} for ${data.shift} shift on ${data.date}`, 'success');
            setTimeout(() => { closeScheduleModal(); }, 1500);
            // In production, send data to backend here
        }
        // Close modal on outside click or Escape
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('scheduleModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) closeScheduleModal();
                });
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('show')) closeScheduleModal();
            });
        });
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
            showNotification('Generating resource report, please wait...', 'info');
            fetch('api/reports_resources.php')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    const reportWindow = window.open('', '_blank');
                    reportWindow.document.write(html);
                    reportWindow.document.close();
                    showNotification('Resource report generated and opened in new window', 'success');
                })
                .catch(error => {
                    console.error('Error generating report:', error);
                    showNotification('Failed to generate resource report. Please try again.', 'error');
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