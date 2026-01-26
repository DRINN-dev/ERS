<?php

$pageTitle = 'Emergency Call Center';
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
    <link rel="stylesheet" href="CSS/call.css">
    <link rel="stylesheet" href="CSS/review.css">
</head>
<body>
    <!-- Include Sidebar Component -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Admin Header Component -->
    <?php include 'includes/admin-header.php'; ?>
    
    <div class="main-content review-main-content">
        <h1 class="review-title">Incident Review & Feedback</h1>
        <div class="card">
            <div id="incident-list"></div>
        </div>
        <div class="card">
            <h2 class="card-title">Team Feedback</h2>
            <div id="feedback-list-section"></div>
        </div>
        <div id="feedback-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.25);z-index:1000;align-items:center;justify-content:center;">
            <div class="modal-content">
                <button onclick="closeFeedbackModal()" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:1.3rem;cursor:pointer;color:#e3e8ee;">&times;</button>
                <h2>Submit Feedback</h2>
                <form id="feedback-form" onsubmit="submitFeedback(event)">
                    <input type="hidden" id="feedback-incident-id" name="incident_id">
                    <div class="form-group">
                        <label for="feedback-author">Your Name</label>
                        <input type="text" id="feedback-author" name="author_name" placeholder="Anonymous">
                    </div>
                    <div class="form-group">
                        <label for="feedback-note">Feedback <span style="color:#dc3545">*</span></label>
                        <textarea id="feedback-note" name="note" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="quick-action-btn" style="width:100%;background:#2ecc71;color:#fff;border:none;border-radius:8px;">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Fetch and display recent incidents
    function fetchIncidents() {
        fetch('api/incidents_list.php')
        .then(r=>r.json()).then(data=>{
            if(!data.ok||!data.items){document.getElementById('incident-list').innerHTML='<div style="color:red">Failed to load incidents.</div>';return;}
            let html = `<table class="review-table">
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
                <tbody>`;
            for(const inc of data.items) {
                // Priority color and row background
                let prioColor = inc.priority==='High'?'#dc3545':(inc.priority==='Medium'?'#fd7e14':'#28a745');
                let prioBg = inc.priority==='High'?'#fde2e2':(inc.priority==='Medium'?'#fffbe7':'#eafaf1');
                let prioText = inc.priority==='High'?'#dc3545':(inc.priority==='Medium'?'#fd7e14':'#218838');
                let prioLabel = inc.priority.charAt(0).toUpperCase()+inc.priority.slice(1);
                let prioBtn = `<span style=\"background:${prioColor};color:#fff;border-radius:6px;padding:0.3em 1em;font-size:0.98em;font-weight:600;display:inline-block;\">${prioLabel} Priority</span>`;
                // Status badge
                let statusBadge = `<span style=\"background:#fff3cd;color:#856404;padding:0.2em 0.8em;border-radius:16px;font-weight:600;font-size:0.98em;\">${inc.status?inc.status.toUpperCase():''}</span>`;
                html += `<tr style=\"background:${prioBg};\">
                    <td style=\"font-weight:600;color:${prioText};\">${inc.incident_code}</td>
                    <td style=\"color:${prioText};font-weight:600;\">${inc.type}</td>
                    <td>${prioBtn}</td>
                    <td>${inc.description||''}</td>
                    <td>${statusBadge}</td>
                    <td>${inc.location_address||''}</td>
                    <td>${inc.created_at?new Date(inc.created_at).toLocaleString():''}</td>
                    <td>
                        <div class="review-actions">
                            <button onclick="showFeedbackModal(${inc.id})" title="Add Feedback" style="background:#fff;border:1px solid #007bff;color:#007bff;border-radius:6px;padding:0.12em 0.35em;font-size:0.95em;"><i class=\"fas fa-edit\" style=\"font-size:1em;\"></i></button>
                            <button onclick="viewFeedback(${inc.id})" title="View Feedback" style="background:#fff;border:1px solid #28a745;color:#28a745;border-radius:6px;padding:0.12em 0.35em;font-size:0.95em;"><i class=\"fas fa-comments\" style=\"font-size:1em;\"></i></button>
                            <button title="Call" style="background:#fff;border:1px solid #fd7e14;color:#fd7e14;border-radius:6px;padding:0.12em 0.35em;font-size:0.95em;"><i class=\"fas fa-phone\" style=\"font-size:1em;\"></i></button>
                            <button title="Acknowledge" style="background:#fff;border:1px solid #28a745;color:#28a745;border-radius:6px;padding:0.12em 0.35em;font-size:0.95em;"><i class=\"fas fa-check\" style=\"font-size:1em;\"></i></button>
                        </div>
                    </td>
                </tr>`;
            }
            html += '</tbody></table>';
            document.getElementById('incident-list').innerHTML = html;
        });
    }
    fetchIncidents();

    // Feedback modal logic
    function showFeedbackModal(incidentId) {
        document.getElementById('feedback-incident-id').value = incidentId;
        document.getElementById('feedback-author').value = '';
        document.getElementById('feedback-note').value = '';
        document.getElementById('feedback-modal').style.display = 'flex';
    }
    function closeFeedbackModal() {
        document.getElementById('feedback-modal').style.display = 'none';
    }
    function submitFeedback(e) {
        e.preventDefault();
        const incident_id = document.getElementById('feedback-incident-id').value;
        const author_name = document.getElementById('feedback-author').value;
        const note = document.getElementById('feedback-note').value;
        fetch('api/incident_feedback.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({incident_id,author_name,note})
        }).then(r=>r.json()).then(data=>{
            if(data.ok){
                closeFeedbackModal();
                alert('Feedback submitted!');
            }else{
                alert('Failed: '+(data.error||'Unknown error'));
            }
        });
    }
    // View feedback for an incident
    function viewFeedback(incidentId) {
        fetch('api/incident_feedback.php?incident_id='+incidentId)
        .then(r=>r.json()).then(data=>{
            let html = '<h3 style="margin:0.5em 0 0.5em 0.2em;">Feedback & Notes</h3>';
            if(!data.ok||!data.data||!data.data.length){html+='<div style="color:#888;margin:0.5em 0 1em 0.2em;">No feedback yet.</div>';}else{
                html+='<ul style="list-style:none;padding:0;">';
                for(const n of data.data){
                    html+=`<li style="margin-bottom:0.7em;padding:0.7em 1em;background:#f7f7f7;border-radius:8px;"><b>${n.author_name||'Anonymous'}</b> <span style="color:#888;font-size:0.95em;">${n.created_at? n.created_at.substr(0,16):''}</span><br>${n.note}</li>`;
                }
                html+='</ul>';
            }
            document.getElementById('feedback-list-section').innerHTML = html;
        });
    }
    </script>
</body>
</html>