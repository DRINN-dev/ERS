<?php

$pageTitle = 'GPS Tracking System';
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
    <link rel="stylesheet" href="css/gps.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>

    <!-- ===================================
       MAIN CONTENT - GPS Tracking System
       =================================== -->
    <div class="main-content">
        <div class="main-container">

            <h1 style="font-size: 2rem; font-weight: 700; color: #333; margin-bottom: 2rem; display: flex; align-items: center;">
                <i class="fas fa-map-marked-alt" style="margin-right: 0.5rem; color: #dc3545;"></i>
                GPS Tracking System
            </h1>

            <!-- Tracking Controls -->
            <div class="tracking-controls">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                    <i class="fas fa-sliders-h" style="margin-right: 0.5rem; color: #007bff;"></i>
                    Tracking Controls
                </h2>
                <div class="control-grid">
                    <div class="control-group">
                        <label for="unit-filter">Track Unit</label>
                        <select id="unit-filter">
                            <option value="">All Units</option>
                            <option value="ambulance">Ambulance Units</option>
                            <option value="police">Police Units</option>
                            <option value="fire">Fire Units</option>
                            <option value="ambulance-5">Ambulance #5</option>
                            <option value="police-8">Police Unit #8</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label for="time-range">Time Range</label>
                        <select id="time-range">
                            <option value="live">Live Tracking</option>
                            <option value="1hour">Last Hour</option>
                            <option value="24hours">Last 24 Hours</option>
                            <option value="7days">Last 7 Days</option>
                        </select>
                    </div>
                    <div class="control-group">
                        <label for="search-location">Search Location</label>
                        <input type="text" id="search-location" placeholder="Enter address or coordinates">
                    </div>
                </div>
            </div>

            <!-- GPS Grid -->
            <div class="gps-grid">
                <!-- Map Panel -->
                <div class="map-container">
                    <div class="map-header">
                        <h3 style="margin: 0; color: #333;">Live GPS Tracking</h3>
                        <div class="map-controls">
                            <button class="map-btn active" onclick="toggleLayer('unit', this)">
                                <i class="fas fa-ambulance"></i> Units
                            </button>
                            <button class="map-btn active" onclick="toggleLayer('incident', this)">
                                <i class="fas fa-exclamation-triangle"></i> Incidents
                            </button>
                            <button class="map-btn" onclick="toggleLayer('routes', this)">
                                <i class="fas fa-route"></i> Routes
                            </button>
                            
                            <button class="map-btn" onclick="centerMap()">
                                <i class="fas fa-crosshairs"></i> Center
                            </button>
                            <button class="map-btn" onclick="refreshMap()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="map-viewport" id="map" style="width:100%; height:100%;">
                        
                    </div>
                </div>

                <!-- Units Panel -->
                <div class="unit-panel">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                        <i class="fas fa-truck" style="margin-right: 0.5rem; color: #28a745;"></i>
                        Unit Status
                    </h3>

                    <div class="unit-card active" data-unit="ambulance-5">
                        <div class="unit-header">
                            <div>
                                <h4 class="unit-name">Ambulance #5</h4>
                                <span class="unit-status status-active">Available</span>
                            </div>
                        </div>
                        <div class="unit-details">
                            <div><i class="fas fa-map-marker-alt"></i> Station 1</div>
                            <div><i class="fas fa-tachometer-alt"></i> 0 mph</div>
                            <div><i class="fas fa-gas-pump"></i> 85% Fuel</div>
                            <div><i class="fas fa-clock"></i> 15 min idle</div>
                        </div>
                        <div class="unit-metrics">
                            <div class="metric">
                                <div class="metric-value">12</div>
                                <div class="metric-label">Calls</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">98%</div>
                                <div class="metric-label">Uptime</div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-unit" onclick="trackUnit('ambulance-5')">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                            <button class="btn-unit" onclick="unitHistory('ambulance-5')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </div>
                    </div>

                    <div class="unit-card enroute" data-unit="police-8">
                        <div class="unit-header">
                            <div>
                                <h4 class="unit-name">Police Unit #8</h4>
                                <span class="unit-status status-enroute">En Route</span>
                            </div>
                        </div>
                        <div class="unit-details">
                            <div><i class="fas fa-map-marker-alt"></i> Downtown</div>
                            <div><i class="fas fa-tachometer-alt"></i> 35 mph</div>
                            <div><i class="fas fa-gas-pump"></i> 92% Fuel</div>
                            <div><i class="fas fa-clock"></i> ETA 8 min</div>
                        </div>
                        <div class="unit-metrics">
                            <div class="metric">
                                <div class="metric-value">8</div>
                                <div class="metric-label">Calls</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">95%</div>
                                <div class="metric-label">Uptime</div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-unit" onclick="trackUnit('police-8')">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                            <button class="btn-unit" onclick="unitHistory('police-8')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </div>
                    </div>

                    <div class="unit-card emergency" data-unit="engine-12">
                        <div class="unit-header">
                            <div>
                                <h4 class="unit-name">Engine #12</h4>
                                <span class="unit-status status-emergency">Emergency</span>
                            </div>
                        </div>
                        <div class="unit-details">
                            <div><i class="fas fa-map-marker-alt"></i> Residential Area</div>
                            <div><i class="fas fa-tachometer-alt"></i> 45 mph</div>
                            <div><i class="fas fa-gas-pump"></i> 67% Fuel</div>
                            <div><i class="fas fa-clock"></i> On Scene</div>
                        </div>
                        <div class="unit-metrics">
                            <div class="metric">
                                <div class="metric-value">15</div>
                                <div class="metric-label">Calls</div>
                            </div>
                            <div class="metric">
                                <div class="metric-value">89%</div>
                                <div class="metric-label">Uptime</div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-unit" onclick="trackUnit('engine-12')">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                            <button class="btn-unit" onclick="unitHistory('engine-12')">
                                <i class="fas fa-history"></i> History
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts and Routes Row -->
            <div class="gps-grid">
                <!-- Alerts Panel -->
                <div class="alerts-panel">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                        <i class="fas fa-bell" style="margin-right: 0.5rem; color: #ffc107;"></i>
                        GPS Alerts & Notifications
                    </h3>

                    <div class="alert-item warning">
                        <div class="alert-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <strong>Speed Alert:</strong> Police Unit #8 exceeded speed limit (45 mph in 30 zone)
                            <br><small>2 minutes ago • Downtown District</small>
                        </div>
                    </div>

                    <div class="alert-item danger">
                        <div class="alert-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <strong>GPS Signal Lost:</strong> Ambulance #3 lost GPS signal for 30 seconds
                            <br><small>5 minutes ago • Rural Route 45</small>
                        </div>
                    </div>

                    <div class="alert-item">
                        <div class="alert-icon info">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <strong>Geofence Alert:</strong> Engine #12 entered restricted zone
                            <br><small>8 minutes ago • Industrial Park</small>
                        </div>
                    </div>

                    <div class="alert-item">
                        <div class="alert-icon info">
                            <i class="fas fa-route"></i>
                        </div>
                        <div>
                            <strong>Route Deviation:</strong> Ambulance #5 took alternate route due to traffic
                            <br><small>12 minutes ago • Highway 101</small>
                        </div>
                    </div>
                </div>

                <!-- Routes Panel -->
                <div class="route-panel">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1.5rem; display: flex; align-items: center;">
                        <i class="fas fa-route" style="margin-right: 0.5rem; color: #2196f3;"></i>
                        Active Routes
                    </h3>

                    <div class="route-list">
                        <div class="route-item active" onclick="selectRoute('route-1')">
                            <div class="route-number">1</div>
                            <div class="route-details">
                                <div class="route-title">Ambulance #5 → Cardiac Emergency</div>
                                <div class="route-meta">Station 1 → Downtown Hospital • 8 min ETA • 3.2 miles</div>
                            </div>
                        </div>

                        <div class="route-item" onclick="selectRoute('route-2')">
                            <div class="route-number">2</div>
                            <div class="route-details">
                                <div class="route-title">Police Unit #8 → Traffic Accident</div>
                                <div class="route-meta">Downtown → Highway 101 • 6 min ETA • 4.1 miles</div>
                            </div>
                        </div>

                        <div class="route-item" onclick="selectRoute('route-3')">
                            <div class="route-number">3</div>
                            <div class="route-details">
                                <div class="route-title">Engine #12 → Structure Fire</div>
                                <div class="route-meta">Fire Station → Residential Area • On Scene • 2.8 miles</div>
                            </div>
                        </div>

                        <div class="route-item" onclick="selectRoute('route-4')">
                            <div class="route-number">4</div>
                            <div class="route-details">
                                <div class="route-title">Ambulance #3 → Hospital Transport</div>
                                <div class="route-meta">General Hospital → City Hospital • 12 min ETA • 5.5 miles</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <!-- ============================================
         COMPLETE FUNCTIONAL GPS TRACKING SYSTEM
         ============================================ -->
    <script>
let map;
let markers = {};
let activeLayers = ['unit', 'incident'];
let qcBoundaryLayers = { halo: null, line: null };
let routes = {};
let QC_BOUNDS_GLOBAL;

// ===============================
// LEAFLET MAP INITIALIZATION
// ===============================
function initMap() {

  // Quezon City bounds
  QC_BOUNDS_GLOBAL = L.latLngBounds(
    [14.6000, 121.0000],
    [14.7500, 121.1000]
  );

    map = L.map("map", {
        center: [14.6760, 121.0437], // QC Hall
        zoom: 13,
        worldCopyJump: true
    });

  // OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors"
  }).addTo(map);

  // === SAMPLE UNITS ===
  addUnitMarker("ambulance-5", 14.6825, 121.0505, "Ambulance #5", "ambulance");
  addUnitMarker("police-8", 14.6672, 121.0603, "Police Unit #8", "police");
  addUnitMarker("engine-12", 14.6954, 121.0321, "Engine #12", "fire");

  initRoutes();

    // Sample incidents so the Incidents button is meaningful
    addIncidentMarker('incident-1', 14.6700, 121.0300, 'Cardiac Emergency');
    addIncidentMarker('incident-2', 14.6900, 121.0600, 'Traffic Accident');

    // Add legend for marker colors
    addLegendControl();

    // Ensure visibility respects current activeLayers on load
    updateMapVisibility();

  console.log("✅ Leaflet map initialized");
}

// ===============================
// MARKERS
// ===============================
function getIcon(type) {
    const icons = {
        ambulance: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
        police: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
        fire: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
        incident: "https://maps.google.com/mapfiles/ms/icons/yellow-dot.png"
    };

    return L.icon({
        iconUrl: icons[type] || icons.incident,
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
}

function addUnitMarker(id, lat, lng, label, type) {
  const marker = L.marker([lat, lng], { icon: getIcon(type) })
    .addTo(map)
    .bindPopup(`<strong>${label}</strong><br>Status: Active`);

  markers[id] = { marker, type: "unit" };
}

function addIncidentMarker(id, lat, lng, label) {
  const marker = L.marker([lat, lng], { icon: getIcon("incident") })
    .addTo(map)
    .bindPopup(`<strong>${label}</strong><br>🚨 Active Incident`);

  markers[id] = { marker, type: "incident" };
}

// ===============================
// ROUTES (POLYLINES)
// ===============================
function initRoutes() {
  routes["route-1"] = L.polyline(
    [
      [14.6825, 121.0505],
      [14.6760, 121.0437],
      [14.6690, 121.0380]
    ],
    { color: "red", weight: 4 }
  );

  routes["route-2"] = L.polyline(
    [
      [14.6672, 121.0603],
      [14.6720, 121.0650],
      [14.6900, 121.0600]
    ],
    { color: "blue", weight: 4 }
  );

    // Additional routes to match the route list items
    routes["route-3"] = L.polyline(
        [
            [14.6954, 121.0321],
            [14.6900, 121.0400],
            [14.6800, 121.0450]
        ],
        { color: "orange", weight: 4 }
    );

    routes["route-4"] = L.polyline(
        [
            [14.6600, 121.0300],
            [14.6700, 121.0350],
            [14.6800, 121.0400]
        ],
        { color: "green", weight: 4 }
    );
}

// ===============================
// MAP CONTROLS
// ===============================
function toggleLayer(layer, el) {
    if (activeLayers.includes(layer)) {
        activeLayers.splice(activeLayers.indexOf(layer), 1);
        if (el) el.classList.remove('active');
    } else {
        activeLayers.push(layer);
        if (el) el.classList.add('active');
    }
    updateMapVisibility();
    showNotification(`${layer.charAt(0).toUpperCase() + layer.slice(1)} layer ${activeLayers.includes(layer) ? 'enabled' : 'disabled'}`, 'info');
}

function updateMapVisibility() {
  Object.values(markers).forEach(item => {
    if (activeLayers.includes(item.type)) {
      map.addLayer(item.marker);
    } else {
      map.removeLayer(item.marker);
    }
  });

  Object.values(routes).forEach(route => {
    activeLayers.includes("routes")
      ? route.addTo(map)
      : map.removeLayer(route);
  });
}

function centerMap() {
  map.setView([14.6760, 121.0437], 13);
}

function refreshMap() {
  Object.values(markers).forEach(item => {
    if (item.type === "unit") {
      const pos = item.marker.getLatLng();
      const newLat = pos.lat + (Math.random() - 0.5) * 0.001;
      const newLng = pos.lng + (Math.random() - 0.5) * 0.001;
      item.marker.setLatLng([newLat, newLng]);
    }
  });
}

function selectRoute(routeId) {
    // Hide all routes first
    Object.values(routes).forEach(r => map.removeLayer(r));
    if (routes[routeId]) {
        routes[routeId].addTo(map);
        if (!activeLayers.includes('routes')) activeLayers.push('routes');
        showNotification('Route selected', 'info');
    }
}

function trackUnit(unitId) {
    const entry = markers[unitId];
    if (!entry) { showNotification('Unit not found', 'error'); return; }
    const pos = entry.marker.getLatLng();
    map.setView(pos, 15);
    entry.marker.openPopup();
    showNotification(`Tracking ${unitId.toUpperCase()}`, 'success');
}

function unitHistory(unitId) {
    alert(
        `History for ${unitId.toUpperCase()}:\n\n` +
        `• Calls Today: 5\n` +
        `• GPS Uptime: 98%\n` +
        `• Last Service: 2 weeks ago`
    );
}

// Lightweight notification helper
function showNotification(msg, type) {
    const n = document.createElement('div');
    n.textContent = msg;
    n.style.cssText = 'position:fixed;top:20px;right:20px;padding:10px 14px;border-radius:8px;color:#fff;font-weight:600;z-index:9999;';
    n.style.background = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';
    document.body.appendChild(n);
    setTimeout(() => n.remove(), 2500);
}

// ===============================
// LEGEND CONTROL
// ===============================
function addLegendControl() {
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'map-legend');
        div.style.background = '#fff';
        div.style.border = '1px solid #dadce0';
        div.style.padding = '10px';
        div.style.borderRadius = '8px';
        div.style.boxShadow = '0 1px 3px rgba(0,0,0,0.2)';
        div.style.fontSize = '12px';
        div.innerHTML = `
            <div style="font-weight:600;margin-bottom:6px">Legend</div>
            <div style="display:flex;align-items:center;margin-bottom:4px"><img src="https://maps.google.com/mapfiles/ms/icons/green-dot.png" width="14" height="14" style="margin-right:6px">Ambulance</div>
            <div style="display:flex;align-items:center;margin-bottom:4px"><img src="https://maps.google.com/mapfiles/ms/icons/blue-dot.png" width="14" height="14" style="margin-right:6px">Police</div>
            <div style="display:flex;align-items:center;margin-bottom:4px"><img src="https://maps.google.com/mapfiles/ms/icons/red-dot.png" width="14" height="14" style="margin-right:6px">Fire</div>
            <div style="display:flex;align-items:center"><img src="https://maps.google.com/mapfiles/ms/icons/yellow-dot.png" width="14" height="14" style="margin-right:6px">Incident</div>
        `;
        return div;
    };
    legend.addTo(map);
}

// ===============================
// INIT MAP
// ===============================
document.addEventListener("DOMContentLoaded", initMap);
// Focus a unit from URL after init
document.addEventListener("DOMContentLoaded", () => {
    try {
        const params = new URLSearchParams(window.location.search);
        const unit = params.get('unit');
        if (unit) {
            setTimeout(() => { try { trackUnit(unit); } catch (e) {} }, 500);
        }
    } catch (e) {}
});
</script>


<script
  src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>


</body>
</html>
