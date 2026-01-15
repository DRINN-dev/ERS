<?php


$pageTitle = 'Emergency Dispatch Center';
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
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
                    <strong>System Status:</strong> All systems operational | Active incidents: 3 | Available units: 8
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
                        <span style="background: #dc3545; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">3 Active</span>
                    </div>

                    <div class="call-card high">
                        <div class="call-info">
                            <div class="call-details">
                                <div class="call-title">Cardiac Arrest - Downtown Hospital</div>
                                <div class="call-meta">
                                    <span><i class="fas fa-clock"></i> 2 min ago</span>
                                    <span><i class="fas fa-user"></i> John Smith</span>
                                    <span class="status-indicator status-active"></span> High Priority
                                </div>
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="btn-dispatch" onclick="dispatchUnit(this, 'Ambulance #5')">Dispatch Ambulance</button>
                            <button class="btn-action-small" onclick="viewDetails(this)">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <button class="btn-action-small" onclick="contactCaller(this)">
                                <i class="fas fa-phone"></i> Call
                            </button>
                        </div>
                    </div>

                    <div class="call-card medium">
                        <div class="call-info">
                            <div class="call-details">
                                <div class="call-title">Multi-Vehicle Accident - Highway 101</div>
                                <div class="call-meta">
                                    <span><i class="fas fa-clock"></i> 8 min ago</span>
                                    <span><i class="fas fa-user"></i> Sarah Johnson</span>
                                    <span class="status-indicator status-busy"></span> Medium Priority
                                </div>
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="btn-dispatch" onclick="dispatchUnit(this, 'Police Unit #8')">Dispatch Police</button>
                            <button class="btn-action-small" onclick="viewDetails(this)">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <button class="btn-action-small" onclick="contactCaller(this)">
                                <i class="fas fa-phone"></i> Call
                            </button>
                        </div>
                    </div>

                    <div class="call-card low">
                        <div class="call-info">
                            <div class="call-details">
                                <div class="call-title">Suspicious Person Report</div>
                                <div class="call-meta">
                                    <span><i class="fas fa-clock"></i> 15 min ago</span>
                                    <span><i class="fas fa-user"></i> Mike Davis</span>
                                    <span class="status-indicator status-available"></span> Low Priority
                                </div>
                            </div>
                        </div>
                        <div class="call-actions">
                            <button class="btn-dispatch" onclick="dispatchUnit(this, 'Police Unit #15')">Dispatch Police</button>
                            <button class="btn-action-small" onclick="viewDetails(this)">
                                <i class="fas fa-eye"></i> Details
                            </button>
                            <button class="btn-action-small" onclick="contactCaller(this)">
                                <i class="fas fa-phone"></i> Call
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Available Units Panel -->
                <div class="dispatch-panel">
                    <div class="panel-header">
                        <h2 class="panel-title">
                            <i class="fas fa-ambulance"></i>
                            Available Units
                        </h2>
                        <span style="background: #28a745; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">4 Available</span>
                    </div>

                    <div class="unit-card available">
                        <div class="unit-info">
                            <div class="unit-details">
                                <div class="unit-name">Ambulance #5</div>
                                <div class="unit-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> Station 1</span>
                                </div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-action-small" onclick="unitStatus(this, 'busy')">
                                <i class="fas fa-play"></i> Deploy
                            </button>
                            <button class="btn-action-small" onclick="unitLocation(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                        </div>
                    </div>

                    <div class="unit-card available">
                        <div class="unit-info">
                            <div class="unit-details">
                                <div class="unit-name">Police Unit #8</div>
                                <div class="unit-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> Downtown</span>
                                </div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-action-small" onclick="unitStatus(this, 'busy')">
                                <i class="fas fa-play"></i> Deploy
                            </button>
                            <button class="btn-action-small" onclick="unitLocation(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                        </div>
                    </div>

                    <div class="unit-card busy">
                        <div class="unit-info">
                            <div class="unit-details">
                                <div class="unit-name">Engine #12</div>
                                <div class="unit-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> En Route</span>
                                    <span><i class="fas fa-clock"></i> 5 min ETA</span>
                                </div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-action-small" onclick="unitStatus(this, 'available')">
                                <i class="fas fa-stop"></i> Stand Down
                            </button>
                            <button class="btn-action-small" onclick="unitLocation(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                        </div>
                    </div>

                    <div class="unit-card available">
                        <div class="unit-info">
                            <div class="unit-details">
                                <div class="unit-name">Police Unit #15</div>
                                <div class="unit-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> Station 3</span>
                                </div>
                            </div>
                        </div>
                        <div class="unit-actions">
                            <button class="btn-action-small" onclick="unitStatus(this, 'busy')">
                                <i class="fas fa-play"></i> Deploy
                            </button>
                            <button class="btn-action-small" onclick="unitLocation(this)">
                                <i class="fas fa-location-arrow"></i> Track
                            </button>
                        </div>
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

                        // Sample dispatch data - replace with actual real-time data
                        $dispatchData = [
                            'active_incidents' => 3,
                            'available_units' => 8,
                            'pending_calls' => 2,
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

    <!-- Uncomment if already have content -->
    <?php /* include('includes/admin-footer.php') */ ?>

    <script>
let map;
let markers = {};
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

  // Initial units
  addMarker("ambulance-5", 14.6825, 121.0505, "🚑 Ambulance #5 - Available", "ambulance");
  addMarker("police-8", 14.6672, 121.0603, "🚓 Police Unit #8 - En Route", "police");
  addMarker("engine-12", 14.6954, 121.0321, "🚒 Engine #12 - Fire Emergency", "fire");

  console.log("✅ Dispatch map loaded (Leaflet)");
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