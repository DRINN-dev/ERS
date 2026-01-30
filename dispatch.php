<?php
$pageTitle = 'Emergency Dispatch Center';

// Initialize default values
$activeIncidents = 0;
$availableUnits = 0;
$pendingCalls = 0;
$systemStatus = 'All systems operational';

// Fetch accurate data from database
try {
    require_once __DIR__ . '/includes/db.php';
    $pdo = get_db_connection();
    
    if ($pdo) {
        // Get active incidents (pending or dispatched)
        $activeIncidents = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status IN ('pending','dispatched')")->fetch()['c'];
        
        // Get available units
        $availableUnits = (int)$pdo->query("SELECT COUNT(*) AS c FROM units WHERE status='available'")->fetch()['c'];
        
        // Get pending calls
        $pendingCalls = (int)$pdo->query("SELECT COUNT(*) AS c FROM incidents WHERE status='pending'")->fetch()['c'];
        
        // Determine system status based on available units and active incidents
        if ($availableUnits === 0 && $activeIncidents > 0) {
            $systemStatus = 'Warning: No available units';
        } elseif ($activeIncidents > 10) {
            $systemStatus = 'High load: Multiple active incidents';
        } elseif ($availableUnits < 3 && $activeIncidents > 0) {
            $systemStatus = 'Limited resources available';
        } else {
            $systemStatus = 'All systems operational';
        }
    }
} catch (Throwable $e) {
    // Keep default values if database query fails
    error_log('Dispatch page database error: ' . $e->getMessage());
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
    <link rel="stylesheet" href="css/dispatch.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"/>
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>

    <!-- ===================================
       MAIN CONTENT - Emergency Dispatch Center
       =================================== -->
    <div class="main-content">
        <div class="main-container">


            <!-- System Alerts -->
            <div class="alert-panel">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
                <div>
                    <strong>System Status:</strong> <?php echo htmlspecialchars($systemStatus); ?> | Active incidents: <?php echo $activeIncidents; ?> | Available units: <?php echo $availableUnits; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-action-btn" onclick="emergencyBroadcast()">
                    <i class="fas fa-bullhorn"></i>
                    Emergency Broadcast
                </button>
                <button class="quick-action-btn" onclick="lockdownProtocol()">
                    <i class="fas fa-shield-alt"></i>
                    Lockdown Protocol
                </button>
                <button class="quick-action-btn" onclick="massCasualty()">
                    <i class="fas fa-users"></i>
                    Mass Casualty Response
                </button>
                <button class="quick-action-btn" onclick="resourceRequest()">
                    <i class="fas fa-truck"></i>
                    Resource Request
                </button>
            </div>

            <!-- Main Dispatch Grid -->
            <div class="dispatch-grid">
                <!-- Active Calls Panel -->
                <div class="dispatch-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-phone"></i>
                            Active Emergency Calls
                        </h2>
                        <span style="background: #dc3545; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><?php echo $activeIncidents; ?> Active</span>
                    </div>

                    <div style="height: calc(100vh - 320px); overflow-y: auto; padding-right: 4px;">
                    <?php
                    // Fetch all active incidents (pending/dispatched)
                    $incidents = [];
                    if ($pdo) {
                        $stmt = $pdo->query("SELECT i.*, c.caller_name, c.caller_phone, c.priority AS call_priority, c.received_at FROM incidents i LEFT JOIN calls c ON c.id = i.reported_by_call_id WHERE i.status IN ('pending','dispatched') ORDER BY i.created_at DESC LIMIT 30");
                        $incidents = $stmt->fetchAll();
                    }
                    if ($incidents) {
                        foreach ($incidents as $incident) {
                            // Priority class
                            $prio = strtolower($incident['priority'] ?? 'medium');
                            $prioClass = $prio === 'high' ? 'high' : ($prio === 'low' ? 'low' : 'medium');
                            // Time ago
                            $created = strtotime($incident['created_at']);
                            $minsAgo = floor((time() - $created) / 60);
                            $timeAgo = $minsAgo < 1 ? 'Just now' : ($minsAgo . ' min ago');
                            // Caller
                            $caller = $incident['caller_name'] ?: 'Unknown';
                            $phone = $incident['caller_phone'] ?: '';
                            $title = $incident['title'] ?: $incident['type'];
                            echo '<div class="call-card ' . $prioClass . '">';
                            echo '  <div class="call-info">';
                            echo '    <div class="call-details">';
                            echo '      <div class="call-title">' . htmlspecialchars($title) . '</div>';
                            echo '      <div class="call-meta">';
                            echo '        <span><i class="fas fa-clock"></i> ' . htmlspecialchars($timeAgo) . '</span>';
                            echo '        <span><i class="fas fa-user"></i> ' . htmlspecialchars($caller) . '</span>';
                            echo '        <span class="status-indicator status-' . $prioClass . '"></span> ' . ucfirst($prio) . ' Priority';
                            echo '      </div>';
                            echo '    </div>';
                            echo '  </div>';
                            echo '  <div class="call-actions">';
                                echo '    <button class="btn-dispatch" onclick="openDispatchModal(' . (int)$incident['id'] . ')">Dispatch Unit</button>';
                                echo '    <button class="btn-action-small" onclick="viewDetails(this)"><i class="fas fa-eye"></i> Details</button>';
                            if ($phone) {
                                echo '    <button class="btn-action-small" onclick="contactCaller(this)"><i class="fas fa-phone"></i> Call</button>';
                            }
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="call-card"><div class="call-info"><div class="call-details"><div class="call-title">No active emergency calls.</div></div></div></div>';
                    }
                    ?>
                    </div>
                </div>

                <!-- Available Units Panel -->
                <div class="dispatch-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-ambulance"></i>
                            Available Units
                        </h2>
                        <span style="background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><?php echo $availableUnits; ?> Available</span>
                    </div>

                    <div style="height: calc(100vh - 320px); overflow-y: auto; padding-right: 4px;">
                    <?php
                    // Fetch only available units
                    $units = [];
                    if ($pdo) {
                        $stmt = $pdo->query("SELECT * FROM units WHERE status='available' ORDER BY unit_type, identifier");
                        $units = $stmt->fetchAll();
                    }
                    if ($units) {
                        foreach ($units as $unit) {
                            $meta = [];
                            if ($unit['unit_type']) $meta[] = ucfirst($unit['unit_type']);
                            echo '<div class="unit-card available">';
                            echo '  <div class="unit-info">';
                            echo '    <div class="unit-details">';
                            echo '      <div class="unit-name">' . htmlspecialchars($unit['identifier']) . '</div>';
                            echo '      <div class="unit-meta">';
                            echo '        <span><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($unit['location'] ?? ($unit['unit_type'] ? ucfirst($unit['unit_type']) : '')) . '</span>';
                            if (!empty($meta)) echo ' <span>' . implode(' | ', $meta) . '</span>';
                            echo '      </div>';
                            echo '    </div>';
                            echo '  </div>';
                            echo '  <div class="unit-actions">';
                            echo '    <button class="btn-action-small" onclick="unitStatus(' . (int)$unit['id'] . ', \'busy\')"><i class="fas fa-play"></i> Deploy</button>';
                            echo '    <button class="btn-action-small" onclick="unitLocation(this)"><i class="fas fa-location-arrow"></i> Track</button>';
                            echo '  </div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="unit-card"><div class="unit-info"><div class="unit-details"><div class="unit-name">No available units.</div></div></div></div>';
                    }
                    ?>
                    </div>
                </div>

                <!-- Map Panel -->
                <div class="dispatch-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-map"></i>
                            Live Map
                        </h2>
                        <div>
                            <button class="btn-action-small" onclick="refreshMap()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div class="map-container">
                        <div class="map-placeholder">
                        </div>

                        <!-- Simulated map markers -->
                        <div class="map-viewport" id="map" style="width:100%; height:100%;"></div>
                    </div>
                </div>
            </div>

            <!-- AI-Powered Dispatch Recommendations -->
            <div class="ai-recommendations-section">
                <div class="ai-recommendations-card">
                    <div class="ai-recommendations-header">
                        <h2><i class="fas fa-brain"></i> AI Dispatch Recommendations</h2>
                        <span class="ai-badge"><i class="fas fa-robot"></i> Powered by Gemini AI</span>
                    </div>
                    <div class="ai-recommendations-content" id="ai-recommendations-content">
                        <?php
                        include 'includes/gemini_helper.php';

                        // Real-time dispatch data from database
                        $dispatchData = [
                            'active_incidents' => $activeIncidents,
                            'available_units' => $availableUnits,
                            'pending_calls' => $pendingCalls,
                            'current_incident' => 'Cardiac Arrest - Downtown Hospital'
                        ];

                        $recommendations = getDispatchRecommendations($dispatchData);
                        if ($recommendations) {
                            echo '<div class="ai-recommendation-text">' . nl2br(htmlspecialchars($recommendations)) . '</div>';
                        } else {
                            echo '<div class="ai-error"><i class="fas fa-exclamation-triangle"></i> Unable to generate AI recommendations at this time.</div>';
                        }
                        ?>
                    </div>
                    <div class="ai-recommendations-actions">
                        <button class="btn-ai-refresh" onclick="refreshAIRecommendations()">
                            <i class="fas fa-sync"></i> Get Recommendations
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>


        <!-- Dispatch Modal -->
        <div id="dispatch-modal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
            <form class="modal-content" style="background:#fff; padding:2.5rem 2.5rem 2rem 2.5rem; border-radius:16px; max-width:600px; width:98%; position:relative; box-shadow:0 8px 32px rgba(0,0,0,0.18); display:flex; flex-direction:column; gap:1.2rem; min-height:350px;">
                <span class="close" onclick="closeDispatchModal()" style="position:absolute; top:10px; right:20px; font-size:2rem; cursor:pointer;">&times;</span>
                <h2 style="margin:0 0 1.2rem 0; text-align:left; font-size:2rem; font-weight:700;">Dispatch Unit</h2>
                <div style="display:flex; flex-direction:column; gap:1.1rem;">
                    <div style="display:flex; flex-direction:column; gap:0.3rem;">
                        <label style="font-weight:600;">Incident Details</label>
                        <div id="modal-incident-details" style="background:#f8f9fa; border-radius:7px; padding:0.75rem 1rem; font-size:1rem;"></div>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.3rem;">
                        <label for="unit-select" style="font-weight:600;">Available Units <span style="color:red">*</span></label>
                        <select id="unit-select" style="width:100%; padding:0.7rem; border-radius:6px; border:1.5px solid #bbb; font-size:1.08rem; background:#f9f9f9;">
                            <option value="">-- Select --</option>
                        </select>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:0.3rem;">
                        <label style="font-weight:600;">Unit Details</label>
                            <!-- Ilagay dito ang code para sa unit details -->
                            <div id="unit-details" style="background:#f1f3f4; border-radius:7px; padding:0.75rem 1rem; min-height:48px; font-size:0.98rem;"></div>
                    </div>
                </div>
                <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1.2rem;">
                    <button type="button" onclick="closeDispatchModal()" style="background:#f1f1f1; color:#333; border:none; border-radius:6px; padding:0.7rem 1.5rem; font-size:1rem; font-weight:500; cursor:pointer;">Cancel</button>
                    <button id="confirm-dispatch-btn" type="button" class="btn-dispatch" style="background:#007bff; color:#fff; border:none; border-radius:6px; padding:0.7rem 1.5rem; font-size:1rem; font-weight:600; cursor:pointer;">Confirm Dispatch</button>
                </div>
            </form>
        </div>

        <script>
        // Modal logic
        let currentIncidentId = null;
        function openDispatchModal(incidentId) {
            currentIncidentId = incidentId;
            document.getElementById('dispatch-modal').style.display = 'flex';
            // Fetch incident details and available units
            fetch('api/incident_details.php?id=' + encodeURIComponent(incidentId))
                .then(r => r.json())
                .then(data => {
                    if (data.incident) {
                        const inc = data.incident;
                        document.getElementById('modal-incident-details').innerHTML =
                            `<strong>Type:</strong> ${inc.type || ''}<br>` +
                            `<strong>Title:</strong> ${inc.title || ''}<br>` +
                            `<strong>Location:</strong> ${inc.location || inc.address || 'N/A'}<br>` +
                            (inc.latitude && inc.longitude ? `<strong>Coordinates:</strong> ${inc.latitude}, ${inc.longitude}<br>` : '') +
                            `<strong>Priority:</strong> ${inc.priority || ''}`;
                    } else {
                        document.getElementById('modal-incident-details').innerHTML = '<span style="color:red">Incident not found.</span>';
                    }
                    // Populate units
                    const select = document.getElementById('unit-select');
                    select.innerHTML = '<option value="">-- Select --</option>';
                    if (data.units && data.units.length) {
                        data.units.forEach(u => {
                            select.innerHTML += `<option value="${u.id}" data-type="${u.unit_type}" data-identifier="${u.identifier}">${u.identifier} (${u.unit_type})</option>`;
                        });
                    }
                    document.getElementById('unit-details').innerHTML = '';
                });
        }
        function closeDispatchModal() {
            document.getElementById('dispatch-modal').style.display = 'none';
            document.getElementById('modal-incident-details').innerHTML = '';
            document.getElementById('unit-select').innerHTML = '<option value="">-- Select --</option>';
            document.getElementById('unit-details').innerHTML = '';
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('unit-select').addEventListener('change', function() {
                const unitId = this.value;
                if (!unitId) {
                    document.getElementById('unit-details').innerHTML = '';
                    return;
                }
                fetch('api/unit_details.php?id=' + encodeURIComponent(unitId))
                    .then(r => r.json())
                    .then(data => {
                        if (data.unit) {
                            const u = data.unit;
                            document.getElementById('unit-details').innerHTML =
                                `<strong>Driver:</strong> ${u.driver_name || 'N/A'}<br>` +
                                `<strong>Plate #:</strong> ${u.plate_number || 'N/A'}<br>` +
                                `<strong>Type:</strong> ${u.unit_type || ''}<br>` +
                                `<strong>Status:</strong> ${u.status || ''}`;
                        } else {
                            document.getElementById('unit-details').innerHTML = '<span style="color:red">Unit not found.</span>';
                        }
                    });
            });
                        document.getElementById('confirm-dispatch-btn').onclick = function() {
                                const unitSelect = document.getElementById('unit-select');
                                const unitId = unitSelect.value;
                                const selectedOption = unitSelect.options[unitSelect.selectedIndex];
                                const unitIdentifier = selectedOption ? selectedOption.getAttribute('data-identifier') : '';
                                if (!unitId || !currentIncidentId) {
                                        alert('Please select a unit.');
                                        return;
                                }
                                // Fetch incident details for routing
                                fetch('api/incident_details.php?id=' + encodeURIComponent(currentIncidentId))
                                    .then(r => r.json())
                                    .then(data => {
                                        let inc = data.incident;
                                        // Try to get coordinates from incident
                                        let loc = inc && inc.location ? inc.location : (inc && inc.address ? inc.address : null);
                                        let coords = null;
                                        if (loc && loc.match(/\d+\.\d+,[ ]*\d+\.\d+/)) {
                                            coords = loc.split(',').map(Number);
                                        }
                                        // Get unit marker position
                                        let unitMarker = null;
                                        for (let k in markers) {
                                            if (markers[k].options && markers[k].options.title === unitIdentifier) {
                                                unitMarker = markers[k];
                                                break;
                                            }
                                        }
                                        // Fallback: use hardcoded positions for demo units
                                        if (!unitMarker) {
                                            if (unitIdentifier === 'Police Unit 1') unitMarker = { getLatLng: () => ({lat:14.6500, lng:121.0300}) };
                                            if (unitIdentifier === 'Fire Truck 1') unitMarker = { getLatLng: () => ({lat:14.6700, lng:121.0450}) };
                                            if (unitIdentifier === 'Ambulance 1') unitMarker = { getLatLng: () => ({lat:14.6900, lng:121.0600}) };
                                        }
                                        if (coords && unitMarker) {
                                            addRouteToIncident(unitMarker.getLatLng().lat, unitMarker.getLatLng().lng, coords[0], coords[1]);
                                        }
                                    });
                                // Continue with dispatch
                                fetch('api/dispatch_unit.php', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json' },
                                        body: JSON.stringify({ incident_id: currentIncidentId, unit_id: unitId })
                                })
                                .then(r => r.json())
                                .then(data => {
                                        if (data.ok) {
                                                // Redirect to GPS tracking page for the dispatched unit
                                                window.location.href = 'gps.php?unit_id=' + encodeURIComponent(unitId) + (unitIdentifier ? ('&unit=' + encodeURIComponent(unitIdentifier)) : '');
                                        } else {
                                                alert('Failed to dispatch unit: ' + (data.error || 'Unknown error'));
                                        }
                                })
                                .catch(() => alert('Network error.'));
                        };
        });
        </script>

        <!-- Uncomment if already have content -->
        <?php /* include('includes/admin-footer.php') */ ?>

    <script>
// Update unit status via AJAX
function unitStatus(unitId, status) {
    if (!unitId || !status) return;
    fetch('api/unit_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ unit_id: unitId, status: status })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            location.reload();
        } else {
            alert('Failed to update unit status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(() => alert('Network error.'));
}
let map;
let markers = {};
let incidentMarkers = {};
let QC_BOUNDS_GLOBAL;

// ===============================
// LEAFLET MAP INITIALIZATION
// ===============================
function initMap() {
    QC_BOUNDS_GLOBAL = L.latLngBounds(
        [14.6000, 121.0000],
        [14.7500, 121.1000]
    );
    map = L.map("map", {
        center: [14.6760, 121.0437],
        zoom: 13,
        maxBounds: QC_BOUNDS_GLOBAL,
        maxBoundsViscosity: 1.0
    });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors"
    }).addTo(map);
    // Pinpoint the three sample units added in the database
    addMarker("police-unit-1", 14.6500, 121.0300, "🚓 Police Unit 1 - Station 1", "police");
    addMarker("fire-truck-1", 14.6700, 121.0450, "🚒 Fire Truck 1 - Station 2", "fire");
    addMarker("ambulance-1", 14.6900, 121.0600, "🚑 Ambulance 1 - Station 3", "ambulance");
    // Load active incidents as markers
    fetch('api/incidents_list.php?status=active')
        .then(r => r.json())
        .then(data => {
            if (data.ok && data.items) {
                data.items.forEach(inc => {
                    // If incident has coordinates, use them; else skip (or geocode if you want)
                    if (inc.location && inc.location.match(/\d+\.\d+,[ ]*\d+\.\d+/)) {
                        const [lat, lng] = inc.location.split(',').map(Number);
                        addIncidentMarker(inc.incident_code, lat, lng, inc.type + ' - ' + (inc.description || ''));
                    }
                });
            }
        });
    console.log("✅ Dispatch map loaded (Leaflet)");
}

function addIncidentMarker(id, lat, lng, info) {
    if (incidentMarkers[id]) {
        map.removeLayer(incidentMarkers[id]);
    }
    const marker = L.marker([lat, lng], { icon: getIncidentIcon() })
        .addTo(map)
        .bindPopup(`<strong>${info}</strong>`);
    incidentMarkers[id] = marker;
}

function getIncidentIcon() {
    return L.icon({
        iconUrl: 'https://cdn-icons-png.flaticon.com/512/1828/1828884.png',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
}

// ===============================
// ICONS
// ===============================
function getIcon(type) {
  const icons = {
    ambulance: "https://cdn-icons-png.flaticon.com/512/2967/2967350.png",
    police: "https://cdn-icons-png.flaticon.com/512/2991/2991120.png",
    fire: "https://cdn-icons-png.flaticon.com/512/482/482244.png"
  };

  return L.icon({
    iconUrl: icons[type],
    iconSize: [32, 32],
    iconAnchor: [16, 32]
  });
}

// ===============================
// MARKERS
// ===============================
function addMarker(id, lat, lng, info, type) {
  const marker = L.marker([lat, lng], { icon: getIcon(type) })
    .addTo(map)
    .bindPopup(`<strong>${info}</strong>`);

  markers[id] = marker;
}

// ===============================
// MAP ACTIONS
// ===============================
function refreshMap() {
  Object.values(markers).forEach(marker => {
    const pos = marker.getLatLng();
    const newLat = pos.lat + (Math.random() - 0.5) * 0.001;
    const newLng = pos.lng + (Math.random() - 0.5) * 0.001;
    const clamped = clampToBounds(newLat, newLng);
    marker.setLatLng(clamped);
  });
  showNotification("Live map refreshed", "info");
}

function clampToBounds(lat, lng) {
  const sw = QC_BOUNDS_GLOBAL.getSouthWest();
  const ne = QC_BOUNDS_GLOBAL.getNorthEast();
  return [
    Math.min(Math.max(lat, sw.lat), ne.lat),
    Math.min(Math.max(lng, sw.lng), ne.lng)
  ];
}
</script>


    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <script src="js/routing.js"></script>
<script>
// Lightweight URL param handling for context
document.addEventListener('DOMContentLoaded', () => {
    try {
        if (typeof initMap === 'function') initMap();
    } catch (e) {}
    try {
        const params = new URLSearchParams(window.location.search);
        const code = params.get('code');
        const period = params.get('period');
        if (code) {
            alert('Viewing incident context: ' + code);
        }
        if (period) {
            console.log('Dispatch period:', period);
        }
    } catch (e) {}
});
</script>
</body>
</html>