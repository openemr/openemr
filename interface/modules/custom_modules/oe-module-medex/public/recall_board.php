<?php
/**
 * MedEx Recall Board View
 * Fully standalone replacement for the core Recall Board
 */

// if(!empty($_GET['debug'])) { echo "<div style='background:white;padding:10px;border:1px solid red;'><strong>DEBUG:</strong> from=$from, to=$to, matches=".count($recalls)."</div>"; }

require_once(__DIR__ . "/../../../../globals.php");
require_once(__DIR__ . "/../src/Services/RecallsBoardService.php");
require_once(__DIR__ . "/../src/Services/ModalityService.php");
require_once(__DIR__ . "/../src/MedExAPI.php");

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\MedEx\MedExAPI;
use OpenEMR\Modules\MedEx\Services\RecallsBoardService;

// Init Services
$service = new RecallsBoardService();
$globals = OEGlobalsBag::getInstance();
$entitlementApi = new MedExAPI();

if (!$entitlementApi->hasAnyServiceEntitlement(['appointment_reminders', 'medex_messages'])) {
    echo "<div class='alert alert-warning' style='margin:16px;'>" .
        xlt('Appointment reminders service is not enabled. Please subscribe in MedEx Admin Dashboard.') .
        "</div>";
    exit;
}

// Filters from POST/GET
$fac = $_REQUEST['form_facility'] ?? '';
$prov = $_REQUEST['form_provider'] ?? '';
$pid = $_REQUEST['form_patient_id'] ?? '';
$name = $_REQUEST['form_patient_name'] ?? '';
$from = $_REQUEST['form_from_date'] ?? date('Y-m-d', strtotime('-6 months'));
$to = $_REQUEST['form_to_date'] ?? date('Y-m-d', strtotime('+12 months'));

// Fetch Data & Preferences
$recalls = $service->getRecallsData($from, $to, $fac, $prov, $pid, $name);

// (Legacy POST handling removed in favor of AJAX)

// DEBUG ON SCREEN
if (!empty($_REQUEST['debug'])) {
    echo "<div class='alert alert-info'>DEBUG: Searching Range: <b> " . text($from) . " to " . text($to) . "</b>. ";
    echo "Found " . count($recalls) . " records. ";
    if (!empty($fac)) echo "Facility: $fac. ";
    if (!empty($prov)) echo "Provider: $prov. ";
    if (!empty($name)) echo "Name: $name. ";
    echo "</div>";
}

$prefs = $service->getPreferences();
$facilities = $service->getFacilities();
$providers = $service->getProviders();

// Check for updates (admins only)
$updateInfo = null;
if (\OpenEMR\Common\Acl\AclMain::aclCheckCore('admin', 'super')) {
    require_once(__DIR__ . '/../src/MedExAPI.php');
    require_once(__DIR__ . '/../src/UpdateManager.php');
    $updateManager = new \OpenEMR\Modules\MedEx\UpdateManager();
    $updateInfo = $updateManager->checkForUpdates();
}

?>

<!-- MedEx Recall Board UI -->
<style>
    .medex-board { width: 100%; border-collapse: collapse; font-family: sans-serif; font-size: 14px; }
    .medex-board th { background: #f5f5f5; padding: 10px; border: 1px solid #ddd; text-align: left; }
    .medex-board td { padding: 8px; border: 1px solid #ddd; vertical-align: top; }

    /* Row Colors based on Status */
    .whitish { background-color: rgba(255, 255, 255, 0.5); }
    .greenish { background-color: rgba(211, 242, 211, 0.5); }
    .yellowish { background-color: rgba(245, 248, 152, 0.5); }
    .reddish { background-color: rgba(246, 224, 224, 0.5); }
    .bluish { background-color: rgba(134, 212, 233, 0.5); }

    /* Tab colors to match row colors */
    #medex-recall-nav li[data-tab="whitish"].active a { border-top: 3px solid #ddd; background: rgba(255, 255, 255, 0.8) !important; }
    #medex-recall-nav li[data-tab="greenish"].active a { border-top: 3px solid #dff0d8; background: rgba(211, 242, 211, 0.8) !important; }
    #medex-recall-nav li[data-tab="yellowish"].active a { border-top: 3px solid #fcf8e3; background: rgba(245, 248, 152, 0.8) !important; }
    #medex-recall-nav li[data-tab="reddish"].active a { border-top: 3px solid #f2dede; background: rgba(246, 224, 224, 0.8) !important; }
    #medex-recall-nav li[data-tab="bluish"].active a { border-top: 3px solid #d9edf7; background: rgba(134, 212, 233, 0.8) !important; }

    /* Non-active tab subtle colors */
    #medex-recall-nav li[data-tab="whitish"] a { border-bottom: 2px solid #ddd; }
    #medex-recall-nav li[data-tab="greenish"] a { border-bottom: 2px solid #dff0d8; }
    #medex-recall-nav li[data-tab="yellowish"] a { border-bottom: 2px solid #fcf8e3; }
    #medex-recall-nav li[data-tab="reddish"] a { border-bottom: 2px solid #f2dede; }
    #medex-recall-nav li[data-tab="bluish"] a { border-bottom: 2px solid #d9edf7; }

    .medex-container { padding: 15px; }

    /* Tabs */
    .nav-tabs { list-style: none; padding: 0; margin: 0 0 20px 0; border-bottom: 1px solid #ddd; }
    .nav-tabs li { display: inline-block; margin-bottom: -1px; }
    .nav-tabs a { display: block; padding: 10px 15px; text-decoration: none; color: #555; cursor: pointer; border: 1px solid transparent; border-bottom: none; border-radius: 4px 4px 0 0; }
    .nav-tabs a:hover { background: #f8f8f8; border-color: #eee #eee #ddd; }
    .nav-tabs li.active a { border-color: #ddd; border-bottom-color: #fff; background: #fff; color: #333; font-weight: bold; }

    /* Switches */
    .switch { position: relative; display: inline-block; width: 40px; height: 20px; vertical-align: middle; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: #2196F3; }
    input:checked + .slider:before { transform: translateX(20px); }

    /* Buttons */
    .btn { display: inline-block; padding: 6px 10px; margin-bottom: 0; font-size: 13px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; cursor: pointer; border: 1px solid transparent; border-radius: 4px; color: #fff; }
    .btn-sm { padding: 4px 8px; font-size: 12px; }
    .btn-primary { background-color: #337ab7; border-color: #2e6da4; }
    .btn-success { background-color: #5cb85c; border-color: #4cae4c; }
    .btn-info { background-color: #5bc0de; border-color: #46b8da; }
    .btn-warning { background-color: #f0ad4e; border-color: #eea236; }
    .btn-danger { background-color: #d9534f; border-color: #d43f3a; }
    .btn-secondary { background-color: #777; border-color: #555; }
</style>

<div class="medex-container">
    <!-- Filters -->
    <div style="background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:20px; border-radius:4px;">
        <?php if ($updateInfo && $updateInfo['update_available']): ?>
            <?php
            $badgeColor = '#17a2b8';
            $badgeIcon = 'fa-info-circle';
            if ($updateInfo['priority'] === 'CRITICAL') {
                $badgeColor = '#dc3545';
                $badgeIcon = 'fa-exclamation-circle';
            } elseif ($updateInfo['priority'] === 'SECURITY') {
                $badgeColor = '#ffc107';
                $badgeIcon = 'fa-shield-alt';
            } elseif ($updateInfo['priority'] === 'IMPORTANT') {
                $badgeColor = '#ff9800';
                $badgeIcon = 'fa-exclamation-triangle';
            }
            ?>
            <div style="display: flex; align-items: center; justify-content: space-between; background: <?php echo attr($badgeColor); ?>; color: white; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; font-size: 13px;">
                <span>
                    <i class="fa <?php echo attr($badgeIcon); ?>"></i>
                    <strong><?php echo xlt('MedEx Update'); ?>:</strong> v<?php echo text($updateInfo['latest_version']); ?> <?php echo xlt('available'); ?>
                    <?php if ($updateInfo['priority'] === 'CRITICAL'): ?>
                        - <strong><?php echo xlt('CRITICAL'); ?></strong>
                    <?php endif; ?>
                </span>
                <a href="../admin/backups.php" style="color: white; text-decoration: underline; margin-left: 15px;">
                    <?php echo xlt('Install'); ?> <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
        <form method="GET" action="messages.php">
            <input type="hidden" name="go" value="Recalls">
            <div style="display:flex; flex-wrap:wrap; gap:15px; align-items:flex-end;">

                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Facility</label>
                    <select name="form_facility" class="form-control" style="padding:5px;">
                        <option value="">All Facilities</option>
                        <?php foreach($facilities as $f): ?>
                            <option value="<?php echo attr($f['id']); ?>" <?php if($fac == $f['id']) echo 'selected'; ?>><?php echo text($f['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Provider</label>
                    <select name="form_provider" class="form-control" style="padding:5px;">
                        <option value="">All Providers</option>
                        <?php foreach($providers as $p): ?>
                            <option value="<?php echo attr($p['id']); ?>" <?php if($prov == $p['id']) echo 'selected'; ?>><?php echo text($p['lname'] . ', ' . $p['fname']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Date Range</label>
                    <input type="date" name="form_from_date" value="<?php echo attr($from); ?>" class="form-control" style="padding:4px;">
                    <span style="color:#777">to</span>
                    <input type="date" name="form_to_date" value="<?php echo attr($to); ?>" class="form-control" style="padding:4px;">
                </div>

                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Patient Name</label>
                    <input type="text" name="form_patient_name" value="<?php echo attr($name); ?>" placeholder="Search..." class="form-control" style="padding:5px;">
                </div>

                <div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='messages.php?go=Recalls'">Reset</button>
                    <button type="button" class="btn btn-success" onclick="openAddRecallModal()"><i class="fa fa-plus"></i> Add Recall</button>
                </div>
            </div>
        </form>
    </div>

    <ul class="nav nav-tabs" id="medex-recall-nav">
        <li class="active" data-tab="all"><a onclick="filterStatus('all', this)">All</a></li>
        <li data-tab="whitish"><a onclick="filterStatus('whitish', this)">Scheduled</a></li>
        <li data-tab="yellowish"><a onclick="filterStatus('yellowish', this)">In-process</a></li>
        <li data-tab="bluish"><a onclick="filterStatus('bluish', this)">Pending Response</a></li>
        <li data-tab="greenish"><a onclick="filterStatus('greenish', this)">Recently Completed</a></li>
        <li data-tab="reddish"><a onclick="filterStatus('reddish', this)">Manual Required</a></li>
    </ul>

    <!-- Main Table -->
    <!-- Form removed in favor of AJAX -->

    <!-- Maintain filters for GET links if needed (not needed for AJAX layout unless we reload) -->

    <div style="margin-bottom:10px; text-align:right;">
         <span style="float:left; font-style:italic; color:#666;">
            Found <?php echo count($recalls); ?> records.
            <span id="ajax_status" style="margin-left:10px; font-weight:bold; color:green; display:none;">Saved!</span>
         </span>
         <!-- Save button removed -->
    </div>

    <table class="medex-board">
        <thead>
            <tr>
                <th>Patient</th>
                <th>Recall Info</th>
                <th>Contact</th>
                <?php if($prefs['show_postcards']): ?><th>Postcards</th><?php endif; ?>
                <?php if($prefs['show_labels']): ?><th>Labels</th><?php endif; ?>
                <th>Office</th>
                <th>Notes</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recalls as $r): ?>
                <tr id="row_<?php echo attr($r['pid']); ?>" class="recall-row <?php echo attr($r['status_class']); ?>" data-status="<?php echo attr($r['status_class']); ?>">

                    <!-- Patient -->
                    <td>
                        <a href="#" onclick="show_patient('<?php echo attr($r['pid']); ?>'); return false;">
                            <?php echo text($r['fname'] . ' ' . $r['lname']); ?>
                        </a>
                        <br><small>DOB: <?php echo text(oeFormatShortDate($r['DOB'])); ?></small>
                        <br><small>ID: <?php echo text($r['pid']); ?></small>
                    </td>

                    <!-- Info -->
                    <td>
                        <strong><?php echo text(oeFormatShortDate($r['r_eventDate'])); ?></strong>
                        <?php if($r['r_reason']) echo '<br><span style="color:#666">' . text($r['r_reason']) . '</span>'; ?>
                    </td>

                    <!-- Contact (Icons + Modalities) -->
                    <td id="contact_<?php echo attr($r['pid']); ?>">
                        <div class="btn-group">
                            <!-- SMS Bot -->
                            <?php if ($r['modalities']['ALLOWED']['SMS'] == 'YES'): ?>
                                <button type="button" class="btn btn-sm btn-success" title="Open SMS Bot" onclick="openSMSBot('<?php echo attr($r['pid']); ?>')"><i class="fa fa-comment"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-secondary" title="SMS Not Allowed" disabled><i class="fa fa-comment"></i></button>
                            <?php endif; ?>

                            <!-- Email -->
                            <?php if ($r['modalities']['ALLOWED']['EMAIL'] == 'YES'): ?>
                                <button type="button" class="btn btn-sm btn-primary" title="Send Email" onclick="window.location.href='mailto:<?php echo attr($r['email']); ?>'"><i class="fa fa-envelope"></i></button>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-secondary" title="Email Not Available" disabled><i class="fa fa-envelope"></i></button>
                            <?php endif; ?>

                            <!-- Phone (Manual Call Toggle) -->
                            <button type="button" class="btn btn-sm btn-secondary phone-btn-<?php echo attr($r['pid']); ?>" title="Click to Log Call" onclick="togglePhoneCall('<?php echo attr($r['pid']); ?>', this)">
                                <i class="fa fa-phone"></i>
                            </button>
                        </div>
                    </td>

                    <!-- Postcards (Toggle) -->
                    <?php if($prefs['show_postcards']): ?>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="postcards[<?php echo attr($r['pid']); ?>]" value="1">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <?php endif; ?>

                    <!-- Labels (Toggle) -->
                    <?php if($prefs['show_labels']): ?>
                    <td>
                         <label class="switch">
                            <input type="checkbox" name="labels[<?php echo attr($r['pid']); ?>]" value="1">
                            <span class="slider round"></span>
                        </label>
                    </td>
                    <?php endif; ?>

                    <!-- Office (Scheduler) -->
                    <td>
                         <button type="button" class="btn btn-info" onclick="scheduleAppt('<?php echo attr($r['pid']); ?>')" title="Schedule Appointment"><i class="fa fa-calendar-check-o"></i></button>
                    </td>

                    <!-- Notes -->
                    <td>
                        <textarea id="msg_notes_<?php echo attr($r['pid']); ?>"
                                  data-pid="<?php echo attr($r['pid']); ?>"
                                  data-orig="<?php echo attr($r['pid']); // unused for now ?>"
                                  onblur="saveNote(this)"
                                  class="form-control" style="height:60px; width:100%; box-sizing:border-box; padding:5px;"></textarea>
                    </td>

                    <!-- Status History -->
                    <td id="history_<?php echo attr($r['pid']); ?>">
                        <div class="history-content" style="max-height:80px; overflow-y:auto; font-size:11px;">
                        <?php
                        foreach ($r['history'] as $h) {
                           // Colorize status
                           $color = '#333';
                           if(stripos($h['msg_reply'], 'READ') !== false) $color = 'green';
                           if(stripos($h['msg_reply'], 'FAILED') !== false) $color = 'red';
                           if(stripos($h['msg_reply'], 'Start') !== false) $color = 'blue';

                           echo '<div style="margin-bottom:2px;">';
                           echo '<b>' . text($h['msg_type']) . ':</b> <span style="color:'.$color.'">' . text($h['msg_reply']) . '</span>';
                           if(!empty($h['msg_extra_text'])) echo '<br><span style="font-size:0.9em;color:#555;margin-left:5px;">&rdsh; '.text($h['msg_extra_text']).'</span>';
                           echo ' <span style="color:#888;font-size:0.85em;">(' . text(oeFormatShortDate($h['msg_date'])) . ')</span>';
                           echo '</div>';
                        }
                        ?>
                        </div>
                    </td>

                    <!-- Actions -->
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" onclick="editRecall('<?php echo attr($r['pid']); ?>')"><i class="fa fa-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteRecall('<?php echo attr($r['pid']); ?>')"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- No "Save Changes" Button anymore -->

    <?php if(empty($recalls)) echo '<div style="padding:20px; text-align:center; color:#777;">No recalls found for this period.</div>'; ?>
</div>

<!-- Add Recall Modal -->
<div id="addRecallModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999;">
    <div style="position:relative; width:800px; max-height:90vh; overflow-y:auto; margin:50px auto; background:white; border-radius:8px; padding:20px; box-shadow:0 4px 6px rgba(0,0,0,0.3);">
        <button type="button" onclick="closeAddRecallModal()" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        
        <h3 style="margin-top:0;">Add New Recall</h3>
        
        <form id="addRecallForm">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <!-- Left Column -->
                <div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Patient Name *</label>
                        <input type="text" id="recall_patient_name" name="recall_patient_name" readonly 
                               onclick="openPatientFinder()" 
                               placeholder="Click to select patient..."
                               style="width:100%; padding:8px; cursor:pointer; background:#f9f9f9;" required>
                        <input type="hidden" id="recall_pid" name="recall_pid">
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Recall Date *</label>
                        <input type="date" id="recall_date" name="recall_date" class="form-control" required>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Reason</label>
                        <input type="text" id="recall_reason" name="recall_reason" class="form-control" placeholder="e.g. Annual exam">
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Provider *</label>
                        <select id="recall_provider" name="recall_provider" class="form-control" required>
                            <option value="">Select Provider...</option>
                            <?php foreach($providers as $p): ?>
                                <option value="<?php echo attr($p['id']); ?>"><?php echo text($p['lname'] . ', ' . $p['fname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Facility *</label>
                        <select id="recall_facility" name="recall_facility" class="form-control" required>
                            <option value="">Select Facility...</option>
                            <?php foreach($facilities as $f): ?>
                                <option value="<?php echo attr($f['id']); ?>"><?php echo text($f['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div id="patientInfoColumn" style="background:#f9f9f9; padding:15px; border-radius:4px; display:none;">
                    <h4 style="margin-top:0;">Patient Information</h4>
                    <div id="patientInfoContent"></div>
                </div>
            </div>
            
            <div style="margin-top:20px; text-align:right;">
                <button type="button" class="btn btn-secondary" onclick="closeAddRecallModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Save Recall</button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Recall Board Logic ---

    // 1. Scroll Retention (UX Fix) - No longer strictly needed for data saves, but good for filters/reloads
    document.addEventListener("DOMContentLoaded", function() {
        var scrollPos = sessionStorage.getItem('medex_board_scroll');
        if (scrollPos) {
            window.scrollTo(0, parseInt(scrollPos));
            sessionStorage.removeItem('medex_board_scroll');
        }

        // Filter forms still reload page
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                sessionStorage.setItem('medex_board_scroll', window.scrollY);
            });
        });
    });

    /**
     * AJAX Helper
     */
    function sendAjax(data, callback) {
        // Robust Path Resolution:
        // Finds the root of the OpenEMR installation based on the current URL
        let ajaxUrl;
        if (window.location.pathname.indexOf('/interface/') !== -1) {
            let root = window.location.pathname.split('/interface/')[0]; // e.g. "/openemr" or ""
            ajaxUrl = root + '/interface/modules/custom_modules/oe-module-medex/public/ajax_handler.php';
        } else {
            // Fallback for unexpected URL structures
            ajaxUrl = '../../modules/custom_modules/oe-module-medex/public/ajax_handler.php';
        }

        // console.log("MedEx AJAX Target:", ajaxUrl);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', ajaxUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        let params = [];
        for (let key in data) {
            params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
        }

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    const res = JSON.parse(xhr.responseText);
                    callback(res);
                } catch(e) {
                    console.error("JSON Error", xhr.responseText);
                }
            }
        };
        xhr.send(params.join('&'));
    }

    function updateRowUI(pid, res) {
         if (res.success) {
            // Update History
            if (res.history_html) {
                const histDiv = document.querySelector('#history_' + pid + ' .history-content');
                if(histDiv) histDiv.innerHTML = res.history_html;
            }
            // Update Row Color
            if (res.status_class) {
                const tr = document.getElementById('row_' + pid);
                if(tr) {
                    tr.className = 'recall-row ' + res.status_class;
                    tr.dataset.status = res.status_class;
                }
            }
            // Show global saved indicator briefly
            const status = document.getElementById('ajax_status');
            status.style.display = 'inline';
            setTimeout(() => { status.style.display = 'none'; }, 2000);
        } else {
            alert('Error saving: ' + (res.message || 'Unknown error'));
        }
    }

    /**
     * Phone Call Toggle Logic
     * Turns button Red + Logs Call via AJAX
     */
    function togglePhoneCall(pid, btn) {
        const notes = document.getElementById('msg_notes_' + pid);
        let active = false;

        if (btn.classList.contains('btn-danger')) {
            // "Un-logging" - UI only? Or log a cancellation?
            // User flow: "Click logs call". Double click probably confuses it.
            // Let's toggle UI back to gray, but we can't 'unsave' database easily.
            // Just treat as UI toggle.
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-secondary');
            notes.style.backgroundColor = '';
            active = false;
        } else {
            // Logging call
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-danger'); // Red
            notes.style.backgroundColor = '#fff3cd'; // Yellow
            notes.focus();
            active = true;

            // AJAX Log
            sendAjax({
                action: 'log_phone',
                pid: pid,
                active: 1,
                note: notes.value
            }, function(res) {
                updateRowUI(pid, res);
            });
        }
    }

    /**
     * Save Note Logic
     */
    function saveNote(textarea) {
        const pid = textarea.dataset.pid;
        const val = textarea.value.trim();

        // Only save if not empty (or maybe we allow clearing?)
        // If empty, we usually don't log 'Empty Note Added'.
        if (val === '') return;

        sendAjax({
            action: 'save_note',
            pid: pid,
            note: val
        }, function(res) {
            updateRowUI(pid, res);
            // Optional: Clear note field after save? Or keep it?
            // Usually keep it visible so they see what they wrote.
            // But if they edit it again, it logs another note.
            // "Lose focus... note added".
             textarea.value = ''; // Clear it? Or leave it?
             // User said "staus_update notes should show in status content".
             // If we show it in history, we can clear the textarea to indicate it's "moved" to history.
             textarea.value = '';
        });
    }

    /**
     * Filter rows by Top Tabs
     */
    function filterStatus(status, el) {
        // Update Tabs UI
        document.querySelectorAll('#medex-recall-nav li').forEach(li => li.classList.remove('active'));
        el.closest('li').classList.add('active');

        // Filter Rows
        const rows = document.querySelectorAll('.recall-row');
        rows.forEach(row => {
            if (status === 'all') {
                row.style.display = '';
            } else {
                if (row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    /**
     * Open Patient Chart Logic (Core OpenEMR)
     */
    function show_patient(pid) {
        // Use top.restoreSession() if needed in future
        if (top.updatePatient) {
            top.updatePatient(pid);
        } else {
            // Fallback if not in frameset
            alert('Patient ID: ' + pid);
        }
    }

    /**
     * Open SMS Bot Window
     */
    function openSMSBot(pid) {
        const url = '../../modules/custom_modules/oe-module-medex/public/sms_bot_list.php?pid=' + pid;
        const features = 'width=450,height=800,resizable=1,scrollbars=1';
        const win = window.open(url, 'MedExSMSBot', features);
        if (win) {
            win.focus();
            win.moveTo((screen.width - 450) / 2, (screen.height - 800) / 2);
        }
    }

    /**
     * Manual Scheduler Open
     */
    function scheduleAppt(pid) {
        // In core, usually calls top.restoreSession, then load calendar
        if(top.restoreSession) {
             top.restoreSession();
             top.frames['left_nav'].setPatient(pid); // Attempt to switch patient
             // Redirect main frame to calendar? Or open modal?
             // For now, simpler alert to prove connection
             console.log("Opening scheduler for " + pid);
        }
    }

    function editRecall(pid) {
        // TODO: Open modal for editing recall details
        alert("Edit recall for " + pid + " (Not implemented yet)");
    }

    function deleteRecall(pid) {
        if(confirm("Are you sure you want to remove this recall?")) {
             // TODO: Ajax Call
             alert("Deleted recall for " + pid);
        }
    }

    // --- Add Recall Modal Functions ---
    
    function openAddRecallModal() {
        document.getElementById('addRecallModal').style.display = 'block';
        document.getElementById('addRecallForm').reset();
        document.getElementById('patientInfoColumn').style.display = 'none';
        document.getElementById('recall_pid').value = '';
    }
    
    function closeAddRecallModal() {
        document.getElementById('addRecallModal').style.display = 'none';
    }
    
    function openPatientFinder() {
        const url = '<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/find_patient_popup.php';
        dlgopen(url, '_blank', 700, 500, '', '', {
            buttons: [
                {text: '<?php echo xls('Close'); ?>', close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: 'patientFinder',
            type: 'iframe'
        });
    }
    
    // Callback function called by patient finder popup
    function setpatient(pid, lname, fname, dob) {
        console.log('Patient selected:', pid, lname, fname, dob);
        
        // Set the patient ID and name
        document.getElementById('recall_pid').value = pid;
        document.getElementById('recall_patient_name').value = lname + ', ' + fname;
        
        // Fetch full patient data via AJAX
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/ajax/get_patient_data.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'pid=' + encodeURIComponent(pid)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Display patient info in right column
                const infoHtml = `
                    <div style="font-size:13px; line-height:1.8;">
                        <strong>DOB:</strong> ${data.patient.DOB || 'N/A'}<br>
                        <strong>Age:</strong> ${data.patient.age || 'N/A'}<br>
                        <strong>Phone:</strong> ${data.patient.phone_cell || data.patient.phone_home || 'N/A'}<br>
                        <strong>Email:</strong> ${data.patient.email || 'N/A'}
                    </div>
                `;
                document.getElementById('patientInfoContent').innerHTML = infoHtml;
                document.getElementById('patientInfoColumn').style.display = 'block';
            }
        })
        .catch(err => console.error('Error fetching patient data:', err));
    }
    
    // Handle form submission
    document.getElementById('addRecallForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/ajax/save_recall.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Recall added successfully!');
                closeAddRecallModal();
                // Reload the page to show the new recall
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save recall'));
            }
        })
        .catch(err => {
            console.error('Error saving recall:', err);
            alert('Error saving recall. Please try again.');
        });
    });

</script>
