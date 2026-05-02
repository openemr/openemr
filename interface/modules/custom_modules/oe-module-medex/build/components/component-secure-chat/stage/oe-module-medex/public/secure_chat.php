<?php
/**
 * MedEx Secure Chat - Patient Search and Link Sender
 *
 * This page allows staff to:
 * 1. Search for patients
 * 2. Send secure chat links via SMS, email, or copy to clipboard
 * 3. View chat history with patients
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018-2025 MedEx
 * @license   Proprietary - All Rights Reserved
 */

require_once __DIR__ . '/../../../../globals.php';
require_once $GLOBALS['srcdir'] . '/patient.inc.php';
require_once __DIR__ . '/../src/MedExAPI.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Modules\MedEx\MedExAPI;

// Check ACL - must have patient access
if (!AclMain::aclCheckCore('patients', 'demo')) {
    echo xlt('Access denied');
    exit;
}

// Initialize MedEx API
$medex = new MedExAPI();

// Get current user info (needed for logging)
$currentUser = sqlQuery("SELECT * FROM users WHERE id = ?", [$_SESSION['authUserID'] ?? 0]);

// Always verify entitlement against MedEx server (not stale local cache).
if (!$medex->hasServiceEntitlement('secure_chat')) {
    echo '<div class="alert alert-warning">' . xlt('Secure Chat is not enabled. Please subscribe to this service in the MedEx Admin Dashboard.') . '</div>';
    exit;
}

require_once __DIR__ . '/../src/MedExConfig.php';
$medexApiUrl = \OpenEMR\Modules\MedEx\MedExConfig::publicBaseUrl();
$medexApiUrl = rtrim($medexApiUrl, '/');
$medexPrefs = sqlQuery("SELECT * FROM medex_prefs LIMIT 1");
$apiKey = $medexPrefs['MedEx_apikey'] ?? '';

// If a patient is currently active in OpenEMR session, preload them.
$initialPid = intval($_GET['pid'] ?? 0);
if ($initialPid <= 0) {
    $initialPid = intval($_SESSION['pid'] ?? 0);
}

$initialPatient = null;
if ($initialPid > 0) {
    $initialRow = sqlQuery(
        "SELECT pid, fname, lname, mname, DOB, phone_cell, email, sex
         FROM patient_data
         WHERE pid = ?
         LIMIT 1",
        [$initialPid]
    );

    if (!empty($initialRow)) {
        $initialPatient = [
            'pid' => (int)($initialRow['pid'] ?? 0),
            'fname' => (string)($initialRow['fname'] ?? ''),
            'lname' => (string)($initialRow['lname'] ?? ''),
            'mname' => (string)($initialRow['mname'] ?? ''),
            'dob' => (string)($initialRow['DOB'] ?? ''),
            'phone' => (string)($initialRow['phone_cell'] ?? ''),
            'email' => (string)($initialRow['email'] ?? ''),
            'sex' => (string)($initialRow['sex'] ?? '')
        ];
    }
}

// Ensure csrf_private_key exists — may be absent when arriving via MedEx SSO
// without going through the normal OpenEMR login flow.
if ($session && empty($session->get('csrf_private_key', null))) {
    CsrfUtils::setupCsrfKey($session);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token'] ?? '', session: $session)) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'search_patients':
            $searchTerm = trim($_POST['search'] ?? '');
            if (strlen($searchTerm) < 2) {
                echo json_encode(['success' => true, 'patients' => []]);
                exit;
            }

            // Search patients by name (both orders), phone, DOB, or PID.
            $normalized = preg_replace('/\s+/', ' ', trim(str_replace(',', ' ', $searchTerm)));
            $searchLike = '%' . $normalized . '%';

            $whereClauses = [];
            $params = [];

            $whereClauses[] = "p.fname LIKE ?";
            $params[] = $searchLike;

            $whereClauses[] = "p.lname LIKE ?";
            $params[] = $searchLike;

            $whereClauses[] = "p.phone_cell LIKE ?";
            $params[] = '%' . $searchTerm . '%';

            $whereClauses[] = "p.phone_home LIKE ?";
            $params[] = '%' . $searchTerm . '%';

            $whereClauses[] = "p.DOB LIKE ?";
            $params[] = '%' . $searchTerm . '%';

            $whereClauses[] = "CONCAT(p.fname, ' ', p.lname) LIKE ?";
            $params[] = $searchLike;

            $whereClauses[] = "CONCAT(p.lname, ' ', p.fname) LIKE ?";
            $params[] = $searchLike;

            $whereClauses[] = "CONCAT(p.lname, ', ', p.fname) LIKE ?";
            $params[] = '%' . $searchTerm . '%';

            if (ctype_digit($searchTerm)) {
                $whereClauses[] = "p.pid = ?";
                $params[] = (int)$searchTerm;
            }

            $parts = array_values(array_filter(explode(' ', $normalized), function ($v) {
                return $v !== '';
            }));

            if (count($parts) >= 2) {
                $first = '%' . $parts[0] . '%';
                $second = '%' . $parts[1] . '%';
                $whereClauses[] = "((p.fname LIKE ? AND p.lname LIKE ?) OR (p.fname LIKE ? AND p.lname LIKE ?))";
                $params[] = $first;
                $params[] = $second;
                $params[] = $second;
                $params[] = $first;
            }

            $query = "SELECT p.pid, p.fname, p.lname, p.mname, p.DOB, p.phone_cell, p.email, p.sex
                      FROM patient_data p
                      WHERE " . implode(' OR ', $whereClauses) . "
                      ORDER BY p.lname, p.fname
                      LIMIT 50";

            $patients = [];
            $result = sqlStatement($query, $params);
            while ($row = sqlFetchArray($result)) {
                $patients[] = [
                    'pid' => $row['pid'],
                    'fname' => $row['fname'],
                    'lname' => $row['lname'],
                    'mname' => $row['mname'],
                    'dob' => $row['DOB'],
                    'phone' => $row['phone_cell'],
                    'email' => $row['email'],
                    'sex' => $row['sex']
                ];
            }
            echo json_encode(['success' => true, 'patients' => $patients]);
            exit;
            
        case 'send_chat_link':
            $pid = intval($_POST['pid'] ?? 0);
            $method = $_POST['method'] ?? ''; // sms, email, or copy
            
            if (!$pid) {
                echo json_encode(['success' => false, 'error' => 'Invalid patient ID']);
                exit;
            }
            
            // Get patient data
            $patient = sqlQuery("SELECT * FROM patient_data WHERE pid = ?", [$pid]);
            if (!$patient) {
                echo json_encode(['success' => false, 'error' => 'Patient not found']);
                exit;
            }
            
            // Generate secure chat token
            $chatToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+72 hours'));
            
            try {
                // Store token in database (no ON DUPLICATE KEY - token is unique)
                sqlStatement("INSERT INTO medex_secure_chat_tokens (pid, token, expires_at, created_by, method) 
                              VALUES (?, ?, ?, ?, ?)",
                    [$pid, $chatToken, $expiresAt, $_SESSION['authUserID'] ?? 0, $method]);
                
                // Also generate a provider access token for the provider to view the same chat
                $providerToken = bin2hex(random_bytes(32));
                sqlStatement("INSERT INTO medex_secure_chat_tokens (pid, token, expires_at, created_by, method, is_provider) 
                              VALUES (?, ?, ?, ?, ?, 1)",
                    [$pid, $providerToken, $expiresAt, $_SESSION['authUserID'] ?? 0, 'provider']);
            } catch (Exception $e) {
                error_log("[MedEx Secure Chat] Database error: " . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Failed to create chat tokens']);
                exit;
            }
            
            // Build secure chat URLs using the canonical rewrite path.
            // This works on public deployments where index.php route links may 404.
            $chatUrl = $medexApiUrl . '/chat/secure/' . rawurlencode($chatToken);
            $providerChatUrl = $medexApiUrl . '/chat/secure/' . rawurlencode($providerToken);
            
            // First, register the tokens on MedEx SaaS side
            try {
                // Get user initials and name for message identification
                $userInitials = strtoupper(substr($currentUser['fname'] ?? '', 0, 1) . substr($currentUser['lname'] ?? '', 0, 1));
                $providerName = trim(($currentUser['fname'] ?? '') . ' ' . ($currentUser['lname'] ?? ''));
                
                // Prepare provider info
                $providerInfo = [
                    'fname' => $currentUser['fname'] ?? '',
                    'lname' => $currentUser['lname'] ?? '',
                    'initials' => $userInitials,
                    'npi' => $currentUser['npi'] ?? '',
                    'id' => $_SESSION['authUserID'] ?? 0
                ];
                
                $tokenRegistered = $medex->registerSecureChatToken($pid, $chatToken, $expiresAt, false, 'patient', $providerInfo);
                $providerTokenRegistered = $medex->registerSecureChatToken($pid, $providerToken, $expiresAt, true, 'provider', $providerInfo);
                if (!$tokenRegistered) {
                    error_log("[MedEx Secure Chat] Warning: Failed to register patient token on MedEx side, but continuing with send");
                }
                if (!$providerTokenRegistered) {
                    error_log("[MedEx Secure Chat] Warning: Failed to register provider token on MedEx side");
                }
            } catch (Exception $e) {
                error_log("[MedEx Secure Chat] Exception registering tokens: " . $e->getMessage());
                // Continue anyway - tokens stored locally
                $tokenRegistered = false;
                $providerTokenRegistered = false;
                // Ensure variables are still set
                if (!isset($userInitials)) {
                    $userInitials = strtoupper(substr($currentUser['fname'] ?? '', 0, 1) . substr($currentUser['lname'] ?? '', 0, 1));
                }
                if (!isset($providerName)) {
                    $providerName = trim(($currentUser['fname'] ?? '') . ' ' . ($currentUser['lname'] ?? ''));
                }
            }
            
            $result = [
                'success' => true, 
                'url' => $chatUrl, 
                'provider_url' => $providerChatUrl, 
                'token' => $chatToken, 
                'provider_token' => $providerToken, 
                'token_registered' => $tokenRegistered, 
                'user_initials' => $userInitials,
                'provider_name' => $providerName
            ];
            
            if ($method === 'sms' && !empty($patient['phone_cell'])) {
                // Send SMS via MedEx API
                $smsResult = $medex->sendSecureChatLink($pid, $patient['phone_cell'], $chatUrl, 'sms', $chatToken, $userInitials);
                $result['sms_sent'] = $smsResult;
                if ($smsResult) {
                    // Log the activity with user information
                    sqlStatement("INSERT INTO medex_secure_chat_log (pid, action, method, created_by, user_initials, details) 
                                  VALUES (?, 'link_sent', 'sms', ?, ?, ?)",
                        [$pid, $_SESSION['authUserID'] ?? 0, $userInitials, json_encode(['phone' => $patient['phone_cell']])]);
                }
            } elseif ($method === 'email' && !empty($patient['email'])) {
                // Send email via MedEx API
                $emailResult = $medex->sendSecureChatLink($pid, $patient['email'], $chatUrl, 'email', $chatToken, $userInitials);
                $result['email_sent'] = $emailResult;
                if ($emailResult) {
                    // Log the activity with user information
                    sqlStatement("INSERT INTO medex_secure_chat_log (pid, action, method, created_by, user_initials, details) 
                                  VALUES (?, 'link_sent', 'email', ?, ?, ?)",
                        [$pid, $_SESSION['authUserID'] ?? 0, $userInitials, json_encode(['email' => $patient['email']])]);
                }
            } elseif ($method === 'copy') {
                // Just return URL for copying - log it
                sqlStatement("INSERT INTO medex_secure_chat_log (pid, action, method, created_by, user_initials, details) 
                              VALUES (?, 'link_copied', 'manual', ?, ?, ?)",
                    [$pid, $_SESSION['authUserID'] ?? 0, $userInitials, json_encode(['url' => $chatUrl])]);
            }
            
            echo json_encode($result);
            exit;
            
        case 'get_chat_history':
            $pid = intval($_POST['pid'] ?? 0);
            $page = intval($_POST['page'] ?? 1);
            $perPage = intval($_POST['per_page'] ?? 25);
            
            if (!$pid) {
                echo json_encode(['success' => false, 'error' => 'Invalid patient ID']);
                exit;
            }
            
            $offset = ($page - 1) * $perPage;
            
            // Get chat history from MedEx API
            $history = $medex->getSecureChatHistory($pid, $perPage, $offset);
            
            // Get local logs too
            $logs = [];
            $logResult = sqlStatement(
                "SELECT l.*, u.fname as user_fname, u.lname as user_lname
                 FROM medex_secure_chat_log l
                 LEFT JOIN users u ON l.created_by = u.id
                 WHERE l.pid = ?
                 ORDER BY l.created_at DESC
                 LIMIT ? OFFSET ?",
                [$pid, $perPage, $offset]
            );
            while ($row = sqlFetchArray($logResult)) {
                $logs[] = $row;
            }
            
            // Get total count
            $totalCount = sqlQuery("SELECT COUNT(*) as cnt FROM medex_secure_chat_log WHERE pid = ?", [$pid]);
            
            echo json_encode([
                'success' => true,
                'history' => $history,
                'logs' => $logs,
                'total' => $totalCount['cnt'] ?? 0,
                'page' => $page,
                'per_page' => $perPage
            ]);
            exit;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('MedEx Secure Chat'); ?></title>
    <?php Header::setupHeader(['common', 'datetime-picker']); ?>
    <style>
        body {
            background: radial-gradient(circle at 8% 8%, rgba(14, 165, 233, 0.18) 0%, rgba(14, 165, 233, 0) 35%), linear-gradient(180deg, #f5fbff 0%, #f2f8fe 100%);
        }
        .secure-chat-container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 24px;
            font-family: "Avenir Next", "Segoe UI", sans-serif;
        }
        .search-box {
            background: #ffffff;
            border: 1px solid #d9e7f5;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 10px 28px rgba(15, 74, 123, 0.08);
        }
        .patient-card {
            background: white;
            border: 1px solid #d9e7f5;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .patient-card:hover {
            border-color: #0e83cd;
            box-shadow: 0 8px 18px rgba(14, 131, 205, 0.16);
        }
        .patient-card.selected {
            border-color: #0e83cd;
            background: #ecf7ff;
        }
        .patient-details {
            display: none;
        }
        .patient-details.active {
            display: block;
        }
        .send-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .send-option {
            background: #f6fbff;
            border: 2px solid #d9e7f5;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .send-option:hover {
            border-color: #0e83cd;
            background: #e8f6ff;
        }
        .send-option i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #0e83cd;
        }
        .send-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .send-option.disabled:hover {
            border-color: #dee2e6;
            background: #f8f9fa;
        }
        .chat-history {
            margin-top: 30px;
        }
        .history-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        .history-item:last-child {
            border-bottom: none;
        }
        .link-result {
            display: none;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .link-result.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        .copy-input {
            display: flex;
            gap: 10px;
        }
        .copy-input input {
            flex: 1;
        }
        .pagination-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        .page-size-select {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .session-patient-banner {
            border: 1px solid #b9dbf4;
            background: #eaf6ff;
            color: #0a4b78;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        /* Chat Bubble Styles */
        .chat-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            background: #f8f9fa;
            margin-bottom: 20px;
        }
        .message-bubble {
            max-width: 80%;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
            font-size: 0.95rem;
        }
        .message-bubble.from-patient {
            background: #ffffff;
            border: 1px solid #ced4da;
            margin-right: auto;
            border-bottom-left-radius: 2px;
        }
        .message-bubble.from-provider {
            background: #dcf8c6; /* WhatsApp-like green for provider/sent */
            margin-left: auto;
            border-bottom-right-radius: 2px;
            text-align: right;
        }
        .message-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 5px;
        }
        .channel-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .channel-whatsapp { background: #25D366; color: white; }
        .channel-sms { background: #007bff; color: white; }
        .channel-web { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="secure-chat-container">
        <h2><i class="fa fa-comment-medical"></i> <?php echo xlt('Secure Patient Chat'); ?></h2>
        <p class="text-muted"><?php echo xlt('Search for a patient and send them a secure chat link via SMS, email, or copy the link manually.'); ?></p>

        <?php if (!empty($initialPatient)) { ?>
        <div class="session-patient-banner">
            <strong><?php echo xlt('Current patient in session:'); ?></strong>
            <?php echo text(trim(($initialPatient['fname'] ?? '') . ' ' . ($initialPatient['lname'] ?? ''))); ?>
            (PID: <?php echo attr((string)($initialPatient['pid'] ?? '')); ?>).
            <?php echo xlt('You can still search and switch to a different patient below.'); ?>
        </div>
        <?php } ?>
        
        <!-- Search Box -->
        <div class="search-box">
            <div class="form-group">
                <label for="patientSearch"><strong><?php echo xlt('Search Patients'); ?></strong></label>
                <div class="input-group">
                    <input type="text" id="patientSearch" class="form-control form-control-lg" 
                           placeholder="<?php echo xla('Enter patient name, phone, or DOB...'); ?>"
                           autocomplete="off">
                    <div class="input-group-append">
                        <button class="btn btn-primary btn-lg" type="button" onclick="searchPatients()">
                            <i class="fa fa-search"></i> <?php echo xlt('Search'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <div id="searchResults" style="display: none;">
            <h4><?php echo xlt('Search Results'); ?></h4>
            <div id="patientList"></div>
        </div>
        
        <!-- Selected Patient Details -->
        <div id="patientDetails" class="patient-details">
            <hr>
            <h4><i class="fa fa-user"></i> <span id="selectedPatientName"></span></h4>
            <div class="row">
                <div class="col-md-6">
                    <p><strong><?php echo xlt('Date of Birth'); ?>:</strong> <span id="selectedPatientDOB"></span></p>
                    <p><strong><?php echo xlt('Phone'); ?>:</strong> <span id="selectedPatientPhone"></span></p>
                    <p><strong><?php echo xlt('Email'); ?>:</strong> <span id="selectedPatientEmail"></span></p>
                </div>
                <div class="col-md-6">
                    <div id="sendStatusMessage"></div>
                </div>
            </div>
            
            <h5><?php echo xlt('Send Secure Chat Link'); ?></h5>
            <div class="send-options">
                <div class="send-option" id="sendSMS" onclick="sendChatLink('sms')">
                    <i class="fa fa-sms"></i>
                    <div><strong><?php echo xlt('Send via SMS'); ?></strong></div>
                    <small class="text-muted"><?php echo xlt('Text message to phone'); ?></small>
                </div>
                <div class="send-option" id="sendEmail" onclick="sendChatLink('email')">
                    <i class="fa fa-envelope"></i>
                    <div><strong><?php echo xlt('Send via Email'); ?></strong></div>
                    <small class="text-muted"><?php echo xlt('Email with link'); ?></small>
                </div>
                <div class="send-option" onclick="sendChatLink('copy')">
                    <i class="fa fa-copy"></i>
                    <div><strong><?php echo xlt('Copy Link'); ?></strong></div>
                    <small class="text-muted"><?php echo xlt('Copy to clipboard'); ?></small>
                </div>
            </div>
            
            <!-- Link Result -->
            <div id="linkResult" class="link-result">
                <div id="linkResultContent"></div>
            </div>
            
            <!-- Chat History -->
            <div class="chat-history">
                <h5><i class="fa fa-history"></i> <?php echo xlt('Communication History'); ?></h5>
                <div class="card">
                    <div class="card-body">
                        <div id="chatHistoryList">
                            <div class="no-results">
                                <?php echo xlt('Select a patient to view their chat history'); ?>
                            </div>
                        </div>
                        <div class="pagination-controls" id="historyPagination" style="display: none;">
                            <div class="page-size-select">
                                <label><?php echo xlt('Show'); ?></label>
                                <select id="historyPerPage" class="form-control form-control-sm" style="width: auto;" onchange="loadChatHistory(1)">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label><?php echo xlt('per page'); ?></label>
                            </div>
                            <div>
                                <span id="historyPageInfo"></span>
                                <button class="btn btn-sm btn-outline-primary" id="historyPrevBtn" onclick="loadChatHistory(currentHistoryPage - 1)">
                                    <i class="fa fa-chevron-left"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="historyNextBtn" onclick="loadChatHistory(currentHistoryPage + 1)">
                                    <i class="fa fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    var csrfToken = <?php echo json_encode(CsrfUtils::collectCsrfToken(session: $session)); ?>;
    var selectedPatient = null;
    var currentHistoryPage = 1;
    var totalHistoryPages = 1;
    var initialPatient = <?php echo json_encode($initialPatient); ?>;
    
    // Search on enter key
    document.getElementById('patientSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchPatients();
        }
    });
    
    // Debounce search
    var searchTimeout;
    document.getElementById('patientSearch').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (document.getElementById('patientSearch').value.length >= 2) {
                searchPatients();
            }
        }, 300);
    });
    
    function searchPatients() {
        var search = document.getElementById('patientSearch').value;
        if (search.length < 2) {
            alert(<?php echo json_encode(xl('Please enter at least 2 characters to search')); ?>);
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'search_patients');
        formData.append('search', search);
        formData.append('csrf_token', csrfToken);
        
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatientResults(data.patients);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
    }

    // Initialize from active OpenEMR patient context, if available.
    if (initialPatient && initialPatient.pid) {
        displayPatientResults([initialPatient]);
        selectPatient(initialPatient);
    }
    
    function displayPatientResults(patients) {
        var resultsDiv = document.getElementById('searchResults');
        var listDiv = document.getElementById('patientList');
        
        if (patients.length === 0) {
            listDiv.innerHTML = '<div class="no-results"><i class="fa fa-search"></i> ' + 
                               <?php echo json_encode(xl('No patients found')); ?> + '</div>';
        } else {
            var html = '';
            patients.forEach(function(p) {
                var phone = p.phone || <?php echo json_encode(xl('No phone')); ?>;
                var email = p.email || <?php echo json_encode(xl('No email')); ?>;
                html += '<div class="patient-card" data-pid="' + p.pid + '" onclick="selectPatient(' + JSON.stringify(p).replace(/"/g, '&quot;') + ')">' +
                        '<div class="d-flex justify-content-between">' +
                        '<div><strong>' + escapeHtml(p.lname) + ', ' + escapeHtml(p.fname) + '</strong></div>' +
                        '<div><small class="text-muted">PID: ' + p.pid + '</small></div>' +
                        '</div>' +
                        '<div class="text-muted small">' +
                        '<span><i class="fa fa-birthday-cake"></i> ' + escapeHtml(p.dob) + '</span> &nbsp; ' +
                        '<span><i class="fa fa-phone"></i> ' + escapeHtml(phone) + '</span> &nbsp; ' +
                        '<span><i class="fa fa-envelope"></i> ' + escapeHtml(email) + '</span>' +
                        '</div>' +
                        '</div>';
            });
            listDiv.innerHTML = html;
        }
        resultsDiv.style.display = 'block';
    }
    
    function selectPatient(patient) {
        selectedPatient = patient;
        
        // Update UI
        document.querySelectorAll('.patient-card').forEach(function(card) {
            card.classList.remove('selected');
        });
        document.querySelector('.patient-card[data-pid="' + patient.pid + '"]').classList.add('selected');
        
        // Show patient details
        document.getElementById('selectedPatientName').textContent = patient.fname + ' ' + patient.lname;
        document.getElementById('selectedPatientDOB').textContent = patient.dob || '-';
        document.getElementById('selectedPatientPhone').textContent = patient.phone || '-';
        document.getElementById('selectedPatientEmail').textContent = patient.email || '-';
        
        // Update send options
        var smsOption = document.getElementById('sendSMS');
        var emailOption = document.getElementById('sendEmail');
        
        if (patient.phone) {
            smsOption.classList.remove('disabled');
        } else {
            smsOption.classList.add('disabled');
        }
        
        if (patient.email) {
            emailOption.classList.remove('disabled');
        } else {
            emailOption.classList.add('disabled');
        }
        
        // Clear previous results
        document.getElementById('linkResult').style.display = 'none';
        document.getElementById('sendStatusMessage').innerHTML = '';
        
        // Show details section
        document.getElementById('patientDetails').classList.add('active');
        
        // Load chat history
        loadChatHistory(1);
    }
    
    function sendChatLink(method) {
        if (!selectedPatient) {
            alert(<?php echo json_encode(xl('Please select a patient first')); ?>);
            return;
        }
        
        // Check if method is available
        if (method === 'sms' && !selectedPatient.phone) {
            alert(<?php echo json_encode(xl('Patient has no phone number on file')); ?>);
            return;
        }
        if (method === 'email' && !selectedPatient.email) {
            alert(<?php echo json_encode(xl('Patient has no email on file')); ?>);
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'send_chat_link');
        formData.append('pid', selectedPatient.pid);
        formData.append('method', method);
        formData.append('csrf_token', csrfToken);
        
        // Show loading state
        document.getElementById('sendStatusMessage').innerHTML = '<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> ' +
            <?php echo json_encode(xl('Generating secure link...')); ?> + '</div>';
        
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            var resultDiv = document.getElementById('linkResult');
            var contentDiv = document.getElementById('linkResultContent');
            var statusDiv = document.getElementById('sendStatusMessage');
            
            if (data.success) {
                resultDiv.classList.remove('error');
                
                var statusMsg = '';
                if (method === 'sms' && data.sms_sent) {
                    statusMsg = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + 
                                <?php echo json_encode(xl('SMS sent successfully!')); ?> + '</div>';
                } else if (method === 'email' && data.email_sent) {
                    statusMsg = '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + 
                                <?php echo json_encode(xl('Email sent successfully!')); ?> + '</div>';
                } else if (method === 'copy' || (!data.sms_sent && !data.email_sent)) {
                    statusMsg = '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' + 
                                <?php echo json_encode(xl('Link generated. Copy the link below.')); ?> + '</div>';
                }
                statusDiv.innerHTML = statusMsg;
                
                // Build patient link HTML
                var patientLinkHtml = '<p><strong>' + <?php echo json_encode(xl('Patient Secure Chat Link')); ?> + ':</strong></p>' +
                    '<div class="copy-input">' +
                    '<input type="text" class="form-control" id="chatLinkUrl" value="' + escapeHtml(data.url) + '" readonly>' +
                    '<button class="btn btn-outline-primary" onclick="copyToClipboard()"><i class="fa fa-copy"></i> ' + <?php echo json_encode(xl('Copy')); ?> + '</button>' +
                    '</div>' +
                    '<small class="text-muted">' + <?php echo json_encode(xl('Share this link with the patient. Expires in 72 hours.')); ?> + '</small>';
                
                // Add provider link HTML if available
                var providerLinkHtml = '';
                if (data.provider_url) {
                    providerLinkHtml = '<hr class="my-3">' +
                        '<p><strong>' + <?php echo json_encode(xl('Your Provider Secure Chat Link')); ?> + ':</strong></p>' +
                        '<div class="alert alert-info"><i class="fa fa-info-circle"></i> ' +
                        <?php echo json_encode(xl('Click below to open your chat session. You\'ll see all messages in real-time.')); ?> + '</div>' +
                        '<a href="' + escapeHtml(data.provider_url) + '" target="_blank" class="btn btn-primary"><i class="fa fa-comments"></i> ' +
                        <?php echo json_encode(xl('Open My Chat')); ?> + '</a>' +
                        '<div class="copy-input mt-2">' +
                        '<input type="text" class="form-control" id="providerLinkUrl" value="' + escapeHtml(data.provider_url) + '" readonly>' +
                        '<button class="btn btn-outline-secondary" onclick="copyProviderLink()"><i class="fa fa-copy"></i> ' + <?php echo json_encode(xl('Copy')); ?> + '</button>' +
                        '</div>';
                }
                
                contentDiv.innerHTML = patientLinkHtml + providerLinkHtml;
                resultDiv.style.display = 'block';
                
                // Reload history
                loadChatHistory(currentHistoryPage);
            } else {
                resultDiv.classList.add('error');
                contentDiv.innerHTML = '<i class="fa fa-exclamation-triangle"></i> ' + escapeHtml(data.error || <?php echo json_encode(xl('An error occurred')); ?>);
                resultDiv.style.display = 'block';
                statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + 
                                     escapeHtml(data.error || <?php echo json_encode(xl('Failed to generate link')); ?>) + '</div>';
            }
        })
        .catch(error => {
            console.error('Send error:', error);
            document.getElementById('sendStatusMessage').innerHTML = '<div class="alert alert-danger"><i class="fa fa-times-circle"></i> ' + 
                <?php echo json_encode(xl('Network error. Please try again.')); ?> + '</div>';
        });
    }
    
    function copyToClipboard() {
        var urlInput = document.getElementById('chatLinkUrl');
        urlInput.select();
        document.execCommand('copy');
        
        // Show copied feedback
        var copyBtn = urlInput.nextElementSibling;
        var originalHtml = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fa fa-check"></i> ' + <?php echo json_encode(xl('Copied!')); ?>;
        copyBtn.classList.remove('btn-outline-primary');
        copyBtn.classList.add('btn-success');
        
        setTimeout(function() {
            copyBtn.innerHTML = originalHtml;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-primary');
        }, 2000);
    }
    
    function copyProviderLink() {
        var urlInput = document.getElementById('providerLinkUrl');
        urlInput.select();
        document.execCommand('copy');
        
        // Show copied feedback
        var copyBtn = urlInput.nextElementSibling;
        var originalHtml = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="fa fa-check"></i> ' + <?php echo json_encode(xl('Copied!')); ?>;
        copyBtn.classList.remove('btn-outline-secondary');
        copyBtn.classList.add('btn-success');
        
        setTimeout(function() {
            copyBtn.innerHTML = originalHtml;
            copyBtn.classList.remove('btn-success');
            copyBtn.classList.add('btn-outline-secondary');
        }, 2000);
    }
    
    function loadChatHistory(page) {
        if (!selectedPatient) return;
        
        currentHistoryPage = page;
        var perPage = parseInt(document.getElementById('historyPerPage').value);
        
        var formData = new FormData();
        formData.append('action', 'get_chat_history');
        formData.append('pid', selectedPatient.pid);
        formData.append('page', page);
        formData.append('per_page', perPage);
        formData.append('csrf_token', csrfToken);
        
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') top.restoreSession();
        fetch('<?php echo $GLOBALS['webroot']; ?>/interface/modules/custom_modules/oe-module-medex/public/secure_chat.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayChatHistory(data);
            }
        })
        .catch(error => {
            console.error('History error:', error);
        });
    }
    
    function displayChatHistory(data) {
        var listDiv = document.getElementById('chatHistoryList');
        var paginationDiv = document.getElementById('historyPagination');
        
        var hasHistory = data.history && data.history.length > 0;
        var hasLogs = data.logs && data.logs.length > 0;

        if (!hasHistory && !hasLogs) {
            listDiv.innerHTML = '<div class="no-results"><i class="fa fa-info-circle"></i> ' + 
                               <?php echo json_encode(xl('No communication history for this patient')); ?> + '</div>';
            paginationDiv.style.display = 'none';
            return;
        }
        
        var html = '';

        // Render Chat History (Unified Timeline)
        if (hasHistory) {
            html += '<h6><i class="fa fa-comments"></i> ' + <?php echo json_encode(xl('Message Timeline')); ?> + '</h6>';
            html += '<div class="chat-container">';
            
            data.history.forEach(function(msg) {
                var isPatient = (msg.is_from_patient == 1); // Ensure boolean comparison
                var directionClass = isPatient ? 'from-patient' : 'from-provider';
                var senderName = isPatient ? 'Patient' : (msg.provider_fname ? msg.provider_fname : 'Provider');
                
                // Determine Channel Badge
                var channel = msg.channel_type ? msg.channel_type.toLowerCase() : 'sms';
                var badgeClass = 'channel-sms';
                var iconClass = 'fa-sms';
                
                if (channel === 'whatsapp') {
                    badgeClass = 'channel-whatsapp';
                    iconClass = 'fa-whatsapp';
                } else if (channel === 'web') {
                    badgeClass = 'channel-web';
                    iconClass = 'fa-globe';
                }

                html += '<div class="message-bubble ' + directionClass + '">';
                html += '<div><strong>' + escapeHtml(senderName) + '</strong></div>';
                html += '<div>' + escapeHtml(msg.msg_body).replace(/\n/g, '<br>') + '</div>';
                html += '<div class="message-meta">';
                html += '<span>' + escapeHtml(msg.msg_date) + '</span>';
                html += '<span class="channel-badge ' + badgeClass + '"><i class="fa ' + iconClass + '"></i> ' + escapeHtml(channel.toUpperCase()) + '</span>';
                html += '</div></div>';
            });
            
            html += '</div>';
        }

        // Render System Logs
        if (hasLogs) {
            html += '<h6 class="mt-4"><i class="fa fa-list"></i> ' + <?php echo json_encode(xl('System Activity Log')); ?> + '</h6>';
            html += '<table class="table table-sm table-striped">' +
                    '<thead><tr>' +
                    '<th>' + <?php echo json_encode(xl('Date/Time')); ?> + '</th>' +
                    '<th>' + <?php echo json_encode(xl('Action')); ?> + '</th>' +
                    '<th>' + <?php echo json_encode(xl('Method')); ?> + '</th>' +
                    '<th>' + <?php echo json_encode(xl('User')); ?> + '</th>' +
                    '</tr></thead><tbody>';
            
            data.logs.forEach(function(log) {
                var user = log.user_fname ? (log.user_fname + ' ' + log.user_lname) : <?php echo json_encode(xl('System')); ?>;
                var action = log.action.replace(/_/g, ' ');
                html += '<tr>' +
                        '<td>' + escapeHtml(log.created_at) + '</td>' +
                        '<td><span class="badge badge-info">' + escapeHtml(action) + '</span></td>' +
                        '<td>' + escapeHtml(log.method || '-') + '</td>' +
                        '<td>' + escapeHtml(user) + '</td>' +
                        '</tr>';
            });
            
            html += '</tbody></table>';
        }

        listDiv.innerHTML = html;
        
        // Scroll to bottom of chat container if present
        var chatContainer = listDiv.querySelector('.chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        
        // Update pagination (primary for logs, but also history if paginated)
        // Note: Currently pagination controls affect both queries via page parameter
        var total = parseInt(data.total); // Total logs
        var perPage = parseInt(data.per_page);
        totalHistoryPages = Math.ceil(total / perPage);
        
        // Only show pagination if there are logs to paginate
        if (hasLogs) {
             document.getElementById('historyPageInfo').textContent = 
                <?php echo json_encode(xl('Page')); ?> + ' ' + data.page + ' ' + <?php echo json_encode(xl('of')); ?> + ' ' + totalHistoryPages;
            
            document.getElementById('historyPrevBtn').disabled = data.page <= 1;
            document.getElementById('historyNextBtn').disabled = data.page >= totalHistoryPages;
            
            paginationDiv.style.display = total > perPage ? 'flex' : 'none';
        } else {
            paginationDiv.style.display = 'none';
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
</body>
</html>
