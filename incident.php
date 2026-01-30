<?php


$pageTitle = 'Incident Priority Management';
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
    <link rel="stylesheet" href="css/incident.css">

</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>

    <!-- ===================================
       MAIN CONTENT - Incident Priority Management
       =================================== -->
    <div class="main-content">
        <div class="main-container">

            <div style="height: 3.5rem;"></div>

            <!-- Statistics Cards -->
            <div class="stats-cards">
                <div class="stats-card">
                    <div class="stats-icon high">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stats-content">
                        <h3>12</h3>
                        <p>High Priority Incidents</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon medium">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <div class="stats-content">
                        <h3>8</h3>
                        <p>Medium Priority Incidents</p>
                    </div>
                </div>
                <div class="stats-card">
                    <div class="stats-icon low">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="stats-content">
                        <h3>15</h3>
                        <p>Low Priority Incidents</p>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <h2 class="section-title" style="font-size: 1.2rem; margin-bottom: 1rem;">
                    <i class="fas fa-filter"></i>
                    Filter Incidents
                </h2>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="priority-filter">Priority Level</label>
                        <select id="priority-filter">
                            <option value="">All Priorities</option>
                            <option value="high">High Priority</option>
                            <option value="medium">Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="status-filter">Status</label>
                        <select id="status-filter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="type-filter">Incident Type</label>
                        <select id="type-filter">
                            <option value="">All Types</option>
                            <option value="medical">Medical Emergency</option>
                            <option value="fire">Fire</option>
                            <option value="police">Police Emergency</option>
                            <option value="traffic">Traffic Accident</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" placeholder="Search incidents...">
                    </div>
                </div>
            </div>

            <!-- Logged Incidents (Dynamic) -->
            <div class="priority-section" style="margin-top: 1.5rem;">
                <h2 class="section-title" style="font-size: 1.2rem;">
                    <i class="fas fa-list"></i>
                    Logged Incidents
                </h2>
                <div id="incident-list-dynamic"></div>
                <!-- Table will be rendered here by JS -->
                <div id="incident-list-dynamic"></div>
            </div>

            <!-- AI-Powered Incident Analysis -->
            <div class="ai-analysis-section">
                <div class="ai-analysis-card">
                    <div class="ai-analysis-header">
                        <h2><i class="fas fa-brain"></i> AI Incident Analysis</h2>
                        <span class="ai-badge"><i class="fas fa-robot"></i> Powered by Gemini AI</span>
                    </div>
                    <div class="ai-analysis-content" id="ai-analysis-content">
                        <?php
                        include 'includes/gemini_helper.php';

                        // Sample incident data - replace with actual incident data
                        $incidentData = [
                            'type' => 'Cardiac Arrest',
                            'location' => 'Downtown Hospital',
                            'description' => 'Patient experiencing cardiac arrest in emergency room',
                            'severity' => 'Critical'
                        ];

                        $analysis = analyzeIncident($incidentData);
                        if ($analysis) {
                            echo '<div class="ai-analysis-text">' . nl2br(htmlspecialchars($analysis)) . '</div>';
                        } else {
                            echo '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> Unable to generate AI analysis at this time.</div>';
                        }
                        ?>
                    </div>
                    <div class="ai-analysis-actions">
                        <button class="btn-ai-refresh" onclick="refreshAIAnalysis()">
                            <i class="fas fa-sync"></i> Analyze Incidents
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <script>
        // Incident Priority Management Functionality
        let INCIDENTS = [];
        let REFRESH_TIMER = null;
        const API_LIST_URL = 'api/incidents_list.php';

        // Priority change functionality
        document.querySelectorAll('.btn-priority').forEach(button => {
            button.addEventListener('click', function() {
                const incidentCard = this.closest('.incident-card');
                const currentPriority = incidentCard.classList.contains('priority-high') ? 'high' :
                                      incidentCard.classList.contains('priority-medium') ? 'medium' : 'low';

                // Cycle through priorities: high -> medium -> low -> high
                let newPriority;
                if (currentPriority === 'high') {
                    newPriority = 'medium';
                } else if (currentPriority === 'medium') {
                    newPriority = 'low';
                } else {
                    newPriority = 'high';
                }

                // Update card styling
                incidentCard.classList.remove('priority-high', 'priority-medium', 'priority-low');
                incidentCard.classList.add(`priority-${newPriority}`);

                // Update button styling and text
                this.className = `btn-priority btn-${newPriority}`;
                this.textContent = `${newPriority.charAt(0).toUpperCase() + newPriority.slice(1)} Priority`;

                // Show confirmation
                showNotification(`Incident priority changed to ${newPriority.toUpperCase()}`, 'success');
            });
        });

        // Resolve incident functionality

        // Event delegation for all action buttons in the table
        document.addEventListener('click', function(e) {
            // Find the button and row
            const btn = e.target.closest('button');
            if (!btn) return;
            const tr = btn.closest('tr');
            if (!tr || !tr.hasAttribute('data-ref')) return;
            const ref = tr.getAttribute('data-ref');
            const incident = INCIDENTS.find(i => (i.incident_code || i.reference_no || '') == ref);
            if (!incident) return;

            // Priority button
            if (btn.classList.contains('btn-priority')) {
                // Cycle through priorities: high -> medium -> low -> high
                let current = (incident.priority || 'low').toLowerCase();
                let newPriority = current === 'high' ? 'medium' : (current === 'medium' ? 'low' : 'high');
                incident.priority = newPriority;
                renderDynamicIncidents();
                showNotification(`Incident priority changed to ${newPriority.toUpperCase()}`, 'success');
                return;
            }

            // Update button
            if (btn.querySelector('.fa-edit')) {
                showUpdateModal(incident);
                return;
            }

            // Contact button
            if (btn.querySelector('.fa-phone')) {
                // Try to get a phone number from incident (if available)
                let phone = '';
                if (incident.caller_phone) phone = incident.caller_phone;
                else if (incident.contact) phone = incident.contact;
                else if (incident.description) {
                    const match = incident.description.match(/(\+?\d{1,3}[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,4})/);
                    if (match) phone = match[1];
                }
                if (phone) {
                    if (confirm(`Call ${phone}?`)) {
                        showNotification(`Initiating call to ${phone}`, 'info');
                    }
                } else {
                    showNotification('Phone number not found', 'error');
                }
                return;
            }

            // Resolve button
            if (btn.querySelector('.fa-check')) {
                if (confirm('Are you sure you want to resolve this incident?')) {
                    incident.status = 'resolved';
                    renderDynamicIncidents();
                    showNotification('Incident marked as resolved', 'success');
                }
                return;
            }
        });

        // Contact functionality
        document.querySelectorAll('.btn-action .fa-phone').forEach(icon => {
            icon.parentElement.addEventListener('click', function() {
                const incidentCard = this.closest('.incident-card');
                const callerInfo = incidentCard.querySelector('.detail-value').textContent;
                const phoneMatch = callerInfo.match(/(\+?\d{1,3}[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,4})/);

                if (phoneMatch) {
                    const phoneNumber = phoneMatch[1];
                    if (confirm(`Call ${phoneNumber}?`)) {
                        // In a real system, this would initiate a phone call
                        showNotification(`Initiating call to ${phoneNumber}`, 'info');
                    }
                } else {
                    showNotification('Phone number not found', 'error');
                }
            });
        });

        // AI refresh for incident analysis
        function refreshAIAnalysis() {
            const payload = {
                type: 'General',
                location: 'Unknown',
                description: 'Summarize current incident list and provide recommendations',
                severity: 'Variable'
            };
            const container = document.getElementById('ai-analysis-content');
            container.innerHTML = '<div class="ai-loading"><i class="fas fa-spinner"></i> Generating analysis...</div>';
            fetch('api/ai.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'analyze_incident', payload })
            })
            .then(r => r.json())
            .then(json => {
                if (json.ok && json.text) {
                    container.innerHTML = '<div class="ai-analysis-text">' + json.text.replace(/\n/g,'<br>') + '</div>';
                } else {
                    container.innerHTML = '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> Unable to generate AI analysis at this time.</div>';
                }
            })
            .catch(() => {
                container.innerHTML = '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> AI request failed.</div>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            refreshAIAnalysis();
        });

        window.addEventListener('storage', function(e) {
            if (e.key === 'ers_incidents') {
                refreshAIAnalysis();
            }
        });

        // Update incident functionality
        document.querySelectorAll('.btn-action .fa-edit').forEach(icon => {
            icon.parentElement.addEventListener('click', function() {
                const incidentCard = this.closest('.incident-card');
                const incidentTitle = incidentCard.querySelector('.incident-title').textContent;

                // Simple update dialog (in a real system, this would open a modal)
                const newDescription = prompt('Update incident description:', incidentTitle);
                if (newDescription && newDescription !== incidentTitle) {
                    incidentCard.querySelector('.incident-title').textContent = newDescription;
                    showNotification('Incident updated successfully', 'success');
                }
            });
        });

        // Filter functionality
        const priorityFilter = document.getElementById('priority-filter');
        const statusFilter = document.getElementById('status-filter');
        const typeFilter = document.getElementById('type-filter');
        const searchInput = document.getElementById('search');
        let currentSearch = '';

        function applyFilters() {
            renderDynamicIncidents();
        }

        // Add event listeners to filters
        priorityFilter.addEventListener('change', fetchIncidents);
        statusFilter.addEventListener('change', fetchIncidents);
        typeFilter.addEventListener('change', fetchIncidents);
        searchInput.addEventListener('input', function(e) {
            currentSearch = e.target.value;
            renderDynamicIncidents();
        });

        // Update statistics
        function updateStats() {
            // Count based on currently filtered incidents
            const filtered = INCIDENTS.filter(passFilters);
            let highCount = 0, mediumCount = 0, lowCount = 0;
            filtered.forEach(i => {
                const p = (i.priority || 'low').toLowerCase();
                if (p === 'high') highCount++;
                else if (p === 'medium') mediumCount++;
                else lowCount++;
            });
            document.querySelector('.stats-content h3').textContent = highCount;
            document.querySelectorAll('.stats-content h3')[1].textContent = mediumCount;
            document.querySelectorAll('.stats-content h3')[2].textContent = lowCount;
        }

        function mapStatusToBadge(status) {
            const s = (status || '').toLowerCase();
            if (s === 'dispatched') return { cls: 'status-dispatched', label: 'Dispatched' };
            if (s === 'resolved' || s === 'cancelled') return { cls: 'status-resolved', label: 'Resolved' };
            return { cls: 'status-active', label: 'Active' }; // pending / default
        }

        function capitalize(s) { return (s || '').charAt(0).toUpperCase() + (s || '').slice(1); }

        function incidentCardHtml(i) {
            const priority = (i.priority || 'low').toLowerCase();
            const statusInfo = mapStatusToBadge(i.status);
            const created = new Date(i.created_at || Date.now());
            const location = i.location || i.location_address || 'Unknown location';
            const ref = i.incident_code || i.reference_no || '';
            return `
                <tr class="priority-${priority}" data-ref="${ref}">
                    <td>${ref}</td>
                    <td>${capitalize(i.type)}</td>
                    <td>${capitalize(priority)}</td>
                    <td>${(i.description || '').substring(0, 60)}${(i.description||'').length>60?'...':''}</td>
                    <td><span class="status-badge ${statusInfo.cls}">${statusInfo.label}</span></td>
                    <td>${location}</td>
                    <td>${created.toLocaleString()}</td>
                    <td>
                        <button class="btn-priority btn-${priority}">${capitalize(priority)} Priority</button>
                        <button class="btn-action"><i class="fas fa-edit"></i></button>
                        <button class="btn-action"><i class="fas fa-phone"></i></button>
                        <button class="btn-action"><i class="fas fa-check"></i></button>
                    </td>
                </tr>
            `;
        }

        function passFilters(i) {
            const priorityValue = (priorityFilter.value || '').toLowerCase();
            const statusValue = (statusFilter.value || '').toLowerCase();
            const typeValue = (typeFilter.value || '').toLowerCase();
            const searchValue = currentSearch.trim().toLowerCase();

            if (priorityValue && (i.priority || '').toLowerCase() !== priorityValue) return false;

            if (statusValue) {
                const s = (i.status || '').toLowerCase();
                const mapped = s === 'dispatched' ? 'dispatched' : (s === 'resolved' || s === 'cancelled' ? 'resolved' : 'active');
                if (mapped !== statusValue) return false;
            }

            if (typeValue && (i.type || '').toLowerCase() !== typeValue) return false;

            if (searchValue) {
                const hay = [i.reference_no, i.type, i.location, i.location_address, i.description]
                    .map(v => (v || '').toString().toLowerCase()).join(' ');
                if (!hay.includes(searchValue)) return false;
            }
            return true;
        }

        function renderDynamicIncidents() {
            const container = document.getElementById('incident-list-dynamic');
            if (!container) return;
            const filtered = INCIDENTS.filter(passFilters);
            if (!filtered.length) {
                container.innerHTML = '<div class="incident-card empty">No incidents yet. Logged calls will appear here.</div>';
            } else {
                let table = `<style>
                    .incident-table-wrapper {
                        width: 100%;
                        overflow-x: auto;
                        max-height: 420px;
                        border-radius: 10px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                        background: #fff;
                        margin-bottom: 1em;
                    }
                    .incident-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 1.08rem;
                        min-width: 900px;
                    }
                    .incident-table th, .incident-table td {
                        padding: 0.85em 1.1em;
                        border: 1px solid #e0e0e0;
                        text-align: left;
                    }
                    .incident-table th {
                        background: #f7f7f7;
                        font-size: 1.13rem;
                        position: sticky;
                        top: 0;
                        z-index: 2;
                    }
                    .incident-table tr:nth-child(even) {
                        background: #fafbfc;
                    }
                </style>
                <div class="incident-table-wrapper">
                  <table class=\"incident-table\">
                    <thead>
                        <tr>
                            <th>Reference No</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Date/Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${filtered.map(incidentCardHtml).join('')}
                    </tbody>
                  </table>
                </div>`;
                container.innerHTML = table;
            }
            updateStats();
        }

        async function fetchIncidents() {
            // Gather filter values
            const params = new URLSearchParams();
            const priorityValue = (priorityFilter.value || '').toLowerCase();
            const statusValue = (statusFilter.value || '').toLowerCase();
            const typeValue = (typeFilter.value || '').toLowerCase();
            const searchValue = (searchInput.value || '').toLowerCase();
            if (priorityValue) params.append('priority', priorityValue);
            if (statusValue) params.append('status', statusValue);
            if (typeValue) params.append('type', typeValue);
            if (searchValue) params.append('search', searchValue);
            try {
                const res = await fetch(API_LIST_URL + '?' + params.toString());
                const data = await res.json();
                if (data && data.ok) {
                    INCIDENTS = data.items || [];
                } else {
                    INCIDENTS = [];
                }
            } catch (e) {
                console.warn('Failed to fetch incidents', e);
                INCIDENTS = [];
            }
            renderDynamicIncidents();
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

        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }

            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }

            .notification {
                font-family: inherit;
            }

            .btn-priority, .btn-action {
                transition: all 0.3s ease;
            }

            .btn-priority:hover, .btn-action:hover {
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
        `;
        document.head.appendChild(style);

        // Initialize stats on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetchIncidents();
            if (REFRESH_TIMER) clearInterval(REFRESH_TIMER);
            REFRESH_TIMER = setInterval(fetchIncidents, 10000); // refresh every 10s
        });

        // Modal for updating incident description
        function showUpdateModal(incident) {
            let modal = document.getElementById('incident-update-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'incident-update-modal';
                // Ensure Leaflet CSS/JS is loaded
                if (!document.getElementById('leaflet-css')) {
                    var lcss = document.createElement('link');
                    lcss.rel = 'stylesheet';
                    lcss.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    lcss.id = 'leaflet-css';
                    document.head.appendChild(lcss);
                }
                if (!window.L) {
                    var ljs = document.createElement('script');
                    ljs.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    ljs.onload = function() { setTimeout(initIncidentMap, 200); };
                    document.body.appendChild(ljs);
                }
                modal.innerHTML = `
                    <div class="modal-backdrop"></div>
                    <div class="modal-content" style="min-width:480px;max-width:700px;">
                        <h3>Update Incident Details</h3>
                        <form id="modal-update-form">
                            <lab el>Type<br>
                                <input id="modal-type-input" type="text" required style="width:100%">
                            </label><br><br>
                            <label>Priority<br>
                                <select id="modal-priority-input" required style="width:100%">
                                    <option value="high">High</option>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                </select>
                            </label><br><br>
                            <label>Description<br>
                                <textarea id="modal-desc-input" rows="4" required style="width:100%"></textarea>
                            </label><br><br>
                            <label>Location<br>
                                <input id="modal-location-input" type="text" style="width:100%" placeholder="Enter coordinates or address">
                            </label><br>
                            <!-- Map picker removed: now in call.php only -->
                            <label>Status<br>
                                <select id="modal-status-input" style="width:100%">
                                    <option value="active">Active</option>
                                    <option value="dispatched">Dispatched</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </label><br><br>
                            <div style="margin-top:1em;text-align:right;">
                                <button type="button" id="modal-cancel-btn" style="margin-right:0.5em;">Cancel</button>
                                <button type="submit" id="modal-save-btn">Save</button>
                            </div>
                        </form>
                    </div>
                `;
                document.body.appendChild(modal);
                // Add styles
                const style = document.createElement('style');
                style.textContent = `
                    #incident-update-modal { position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:2000;display:flex;align-items:center;justify-content:center; }
                    #incident-update-modal .modal-backdrop { position:absolute;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35); }
                    #incident-update-modal .modal-content { position:relative;z-index:1;background:#fff;padding:2em 1.5em;border-radius:10px;box-shadow:0 8px 32px rgba(0,0,0,0.18);min-width:320px;max-width:95vw; }
                    #incident-update-modal h3 { margin-top:0; }
                    #incident-update-modal textarea, #incident-update-modal input, #incident-update-modal select { border:1px solid #ccc;border-radius:6px;padding:0.7em; font-size:1em; }
                    #incident-update-modal button { padding:0.5em 1.2em;font-size:1em;border-radius:6px;border:none;cursor:pointer; }
                    #modal-save-btn { background:#007bff;color:#fff; }
                    #modal-cancel-btn { background:#eee;color:#333; }
                    #modal-save-btn:hover { background:#0056b3; }
                    #modal-cancel-btn:hover { background:#ccc; }
                `;
                document.head.appendChild(style);
            }
            modal.style.display = 'flex';
            document.getElementById('modal-type-input').value = incident.type || '';
            document.getElementById('modal-priority-input').value = (incident.priority || 'low').toLowerCase();
            document.getElementById('modal-desc-input').value = incident.description || '';
            document.getElementById('modal-location-input').value = incident.location || incident.location_address || '';
            document.getElementById('modal-status-input').value = (incident.status || 'active').toLowerCase();
            // Add Leaflet map picker for location
            function initIncidentMap() {
                if (window.L && document.getElementById('incident-location-map')) {
                    var map = L.map('incident-location-map').setView([14.6760, 121.0437], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    var marker;
                    // If existing location, show marker
                    var locVal = document.getElementById('modal-location-input').value;
                    if (locVal && locVal.match(/\d+\.\d+,[ ]*\d+\.\d+/)) {
                        var coords = locVal.split(',');
                        marker = L.marker([parseFloat(coords[0]), parseFloat(coords[1])]).addTo(map);
                        map.setView([parseFloat(coords[0]), parseFloat(coords[1])], 15);
                    }
                    map.on('click', function(e) {
                        var latlng = e.latlng.lat.toFixed(6) + ',' + e.latlng.lng.toFixed(6);
                        document.getElementById('modal-location-input').value = latlng;
                        if (marker) {
                            marker.setLatLng(e.latlng);
                        } else {
                            marker = L.marker(e.latlng).addTo(map);
                        }
                    });
                }
            }
            setTimeout(function() {
                if (window.L) initIncidentMap();
            }, 400);

            // Cancel button
            document.getElementById('modal-cancel-btn').onclick = function() {
                modal.style.display = 'none';
            };
            // Save button (form submit)
            document.getElementById('modal-update-form').onsubmit = function(e) {
                e.preventDefault();
                incident.type = document.getElementById('modal-type-input').value;
                incident.priority = document.getElementById('modal-priority-input').value;
                incident.description = document.getElementById('modal-desc-input').value;
                let loc = document.getElementById('modal-location-input').value;
                if (incident.location !== undefined) incident.location = loc;
                else if (incident.location_address !== undefined) incident.location_address = loc;
                incident.status = document.getElementById('modal-status-input').value;
                renderDynamicIncidents();
                showNotification('Incident updated successfully', 'success');
                modal.style.display = 'none';
            };
        }
    </script>

    <script>
    // Handle URL params for deep linking from reports
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const params = new URLSearchParams(window.location.search);
            const code = params.get('code');
            const period = params.get('period');
            if (code) {
                alert('Opening incident details for: ' + code);
            }
            if (period) {
                console.log('Incident view period:', period);
            }
        } catch (e) {}
    });
    </script>
</body>
</html>