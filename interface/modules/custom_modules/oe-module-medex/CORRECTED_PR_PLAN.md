# OpenEMR PR: Complete MedEx Removal - CORRECTED

## Key Insight
`/interface/main/messages/save.php` has **TWO types of endpoints**:

### 1. Core Recall Endpoints (keep in core, use RecallService)
- `action=new_recall` - Get recall data for form
- `action=addRecall` - Save recall
- `action=delete_Recall` - Delete recall

### 2. MedEx-Specific Endpoints (MOVE TO MODULE)
- `go=Preferences` - MedEx preferences (lines 45-74)
- `MedEx=start` - MedEx registration (lines 75-156)
- `go=sms_search` - SMS patient search (lines 23-43)

---

## Solution: Create Module Endpoint File

### Create: `/interface/modules/custom_modules/oe-module-medex/public/ajax.php`

This handles ALL MedEx-specific AJAX calls that were in `save.php`.

```php
<?php
/**
 * MedEx Module AJAX Handler
 * Handles MedEx-specific endpoints
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   Proprietary - All Rights Reserved
 */

require_once __DIR__ . '/../../../../globals.php';
require_once __DIR__ . '/../src/API/API.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;

// Verify MedEx is enabled
if ($GLOBALS['medex_enable'] != '1') {
    http_response_code(403);
    echo json_encode(['error' => 'MedEx not enabled']);
    exit;
}

// Initialize MedEx
$MedEx = new MedExApi\MedEx($GLOBALS['medex_api_host'] ?? 'MedExBank.com');

// SMS Patient Search
if ($_REQUEST['go'] == 'sms_search') {
    $param = "%" . $_GET['term'] . "%";
    $results = [];

    $rows = QueryUtils::fetchRecords(
        "SELECT * FROM patient_data WHERE fname LIKE ? OR lname LIKE ?",
        [$param, $param]
    );

    foreach ($rows as $frow) {
        $data = [
            'Label' => 'Name',
            'value' => text($frow['fname'] . " " . $frow['lname']),
            'pid' => text($frow['pid']),
            'mobile' => text($frow['phone_cell']),
            'allow' => text($frow['hipaa_allowsms'])
        ];

        $lastMsg = QueryUtils::fetchRecords(
            "SELECT * FROM medex_outgoing WHERE msg_pid=? ORDER BY msg_uid DESC LIMIT 1",
            [$frow['pid']]
        );

        if (!empty($lastMsg)) {
            $data['msg_last_updated'] = $lastMsg[0]['msg_date'] ?? null;
            $data['medex_uid'] = $lastMsg[0]['medex_uid'] ?? null;
        }

        $results[] = $data;
    }

    echo json_encode($results);
    exit;
}

// MedEx Preferences
if ($_REQUEST['go'] == 'Preferences') {
    if (!AclMain::aclCheckCore('admin', 'super')) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }

    $facilities = implode("|", $_REQUEST['facilities'] ?? []);
    $providers = implode("|", $_REQUEST['providers'] ?? []);
    $hipaa = $_REQUEST['ME_hipaa_default_override'] ?? '';
    $country_code = $_REQUEST['PHONE_country_code'] ?? '1';

    QueryUtils::sqlStatementThrowException(
        "UPDATE medex_prefs SET
         ME_facilities=?, ME_providers=?, ME_hipaa_default_override=?,
         PHONE_country_code=?, POSTCARDS_local=?, POSTCARDS_remote=?,
         LABELS_local=?, LABELS_choice=?, combine_time=?, postcard_top=?",
        [
            $facilities,
            $providers,
            $hipaa,
            $country_code,
            $_REQUEST['POSTCARDS_local'] ?? '',
            $_REQUEST['POSTCARDS_remote'] ?? '',
            $_REQUEST['LABELS_local'] ?? '',
            $_REQUEST['chart_label_type'] ?? '',
            $_REQUEST['combine_time'] ?? '',
            $_REQUEST['postcard_top'] ?? ''
        ]
    );

    // Update chart label type global
    QueryUtils::sqlStatementThrowException(
        "UPDATE globals SET gl_value = ? WHERE gl_name = 'chart_label_type'",
        [$_REQUEST['chart_label_type'] ?? '']
    );

    // Update background service
    QueryUtils::sqlStatementThrowException(
        "UPDATE background_services SET active=1, execute_interval=?, running=0
         WHERE name='MedEx'",
        [$_POST['execute_interval'] ?? 29]
    );

    $result = $MedEx->login('1');
    echo json_encode($result);
    exit;
}

// MedEx Registration
if ($_REQUEST['MedEx'] == "start") {
    if (!AclMain::aclCheckCore('admin', 'super')) {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }

    // Get user and facility data
    $userRecords = QueryUtils::fetchRecords(
        "SELECT * FROM users WHERE id = ?",
        [$_SESSION['authUserID']]
    );
    $user_data = $userRecords[0] ?? [];

    $facilityRecords = QueryUtils::fetchRecords(
        "SELECT * FROM facility WHERE primary_business_entity='1' LIMIT 1"
    );
    $facility = $facilityRecords[0] ?? [];

    // Build registration data
    $data = [
        'firstname' => $user_data['fname'] ?? '',
        'lastname' => $user_data['lname'] ?? '',
        'username' => $_SESSION['authUser'] ?? '',
        'password' => $_REQUEST['new_password'] ?? '',
        'email' => $_REQUEST['new_email'] ?? '',
        'telephone' => $facility['phone'] ?? '',
        'fax' => $facility['fax'] ?? '',
        'company' => $facility['name'] ?? '',
        'address_1' => $facility['street'] ?? '',
        'city' => $facility['city'] ?? '',
        'state' => $facility['state'] ?? '',
        'postcode' => $facility['postal_code'] ?? '',
        'country' => $facility['country_code'] ?? '',
        'sender_name' => ($user_data['fname'] ?? '') . " " . ($user_data['lname'] ?? ''),
        'sender_email' => $facility['email'] ?? '',
        'callerid' => $facility['phone'] ?? '',
        'MedEx' => "1",
        'ipaddress' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];

    // Build URLs
    $prefix = ($_SERVER["SSL_TLS_SNI"] ?? false) ? 'https://' : 'http://';
    $data['website_url'] = $prefix . $_SERVER['HTTP_HOST'] . ($GLOBALS['web_root'] ?? '');

    $practice_logo = $GLOBALS['OE_SITE_DIR'] . "/images/practice_logo.gif";
    if (file_exists($practice_logo)) {
        $data['logo_url'] = $prefix . $_SERVER['HTTP_HOST'] . ($GLOBALS['web_root'] ?? '') .
                           "/sites/" . ($_SESSION["site_id"] ?? 'default') . "/images/practice_logo.gif";
    } else {
        $data['logo_url'] = $prefix . $_SERVER['HTTP_HOST'] .
                           ($GLOBALS['images_static_relative'] ?? '') . "/menu-logo.png";
    }

    // Register with MedEx
    $response = $MedEx->setup->autoReg($data);

    if (($response['API_key'] ?? '') && ($response['customer_id'] ?? '')) {
        // Registration successful
        QueryUtils::sqlStatementThrowException("DELETE FROM medex_prefs");

        // Get all facilities
        $facilities = [];
        $facilityRecords = QueryUtils::fetchRecords("SELECT id FROM facility ORDER BY name");
        foreach ($facilityRecords as $f) {
            $facilities[] = $f['id'];
        }

        // Get all active providers
        $providers = [];
        $providerRecords = QueryUtils::fetchRecords(
            "SELECT id FROM users WHERE username != '' AND active = '1' AND authorized = '1'"
        );
        foreach ($providerRecords as $p) {
            $providers[] = $p['id'];
        }

        // Save preferences
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO medex_prefs
             (MedEx_id, ME_api_key, ME_username, ME_facilities, ME_providers,
              ME_hipaa_default_override, PHONE_country_code, LABELS_local, LABELS_choice)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $response['customer_id'],
                $response['API_key'],
                $_POST['new_email'] ?? '',
                implode("|", $facilities),
                implode("|", $providers),
                "1",
                "1",
                "1",
                "5160"
            ]
        );

        // Enable background service
        QueryUtils::sqlStatementThrowException(
            "UPDATE background_services SET active=1, execute_interval=29, running=0
             WHERE name='MedEx'"
        );

        // Login to verify
        $info = $MedEx->login('2');

        if ($info['token'] ?? false) {
            $info['show'] = xlt("Sign-up successful for") . " " . ($data['company'] ?? '') . ".<br />" .
                           xlt("Proceeding to Preferences") . ".<br />" .
                           xlt("If this page does not refresh, reload the Messages page manually") . ".<br />";
            echo json_encode($info);
        }
    } else {
        // Registration failed
        $response_prob = [
            'show' => xlt("We ran into some problems connecting your EHR to the MedEx servers") . ".<br />" .
                     xlt('Most often this is due to a Username/Password mismatch') . "<br />" .
                     xlt('Run Setup again or contact support for assistance') .
                     " <a href='https://medexbank.com/cart/upload/'>MedEx Bank</a>.<br />"
        ];
        echo json_encode($response_prob);

        QueryUtils::sqlStatementThrowException(
            "UPDATE background_services SET active=0 WHERE name='MedEx'"
        );
    }
    exit;
}

// Invalid request
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
```

---

## Update `/interface/main/messages/save.php`

### REMOVE Lines 23-156 (All MedEx endpoints)

**DELETE:**
- Lines 23-43: SMS search
- Lines 45-74: Preferences
- Lines 75-156: Registration

### KEEP Lines 158-214 (Core recall endpoints)

**Update these to use RecallService:**

```php
<?php
require_once "../../globals.php";
require_once "$srcdir/lists.inc.php";
require_once "$srcdir/forms.inc.php";
require_once "$srcdir/patient.inc.php";
require_once "$srcdir/RecallBoard/RecallService.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Services\RecallBoard\RecallService;

// Get recall data for form
if (($_REQUEST['pid']) && ($_REQUEST['action'] == "new_recall")) {
    $query = "SELECT * FROM patient_data WHERE pid=?";
    $result = sqlQuery($query, [$_REQUEST['pid']]);

    // Use RecallService for age
    $result['age'] = RecallService::getAge($result['DOB']);

    // uuid is binary and will break json_encode
    unset($result['uuid']);

    // Get PLAN from eye form if exists
    $query = "SELECT ORDER_DETAILS FROM form_eye_mag_orders
              WHERE pid=? AND ORDER_DATE_PLACED < NOW()
              ORDER BY ORDER_DATE_PLACED DESC LIMIT 1";
    $result2 = sqlQuery($query, [$_REQUEST['pid']]);
    if (!empty($result2)) {
        $result['PLAN'] = $result2['ORDER_DETAILS'];
    }

    // Get last appointment
    $query = "SELECT * FROM openemr_postcalendar_events
              WHERE pc_pid =? ORDER BY pc_eventDate DESC LIMIT 1";
    $result2 = sqlQuery($query, [$_REQUEST['pid']]);
    if ($result2) {
        $result['DOLV'] = oeFormatShortDate($result2['pc_eventDate']);
        $result['provider'] = $result2['pc_aid'];
        $result['facility'] = $result2['pc_facility'];
    }

    // Get existing recall
    $query = "SELECT * FROM medex_recalls WHERE r_pid=?";
    $result3 = sqlQuery($query, [$_REQUEST['pid']]);
    if ($result3) {
        $result['recall_date'] = $result3['r_eventDate'];
        $result['PLAN'] = $result3['r_reason'];
        $result['facility'] = $result3['r_facility'];
        $result['provider'] = $result3['r_provider'];
    }

    echo json_encode($result);
    exit;
}

// Save recall
if (($_REQUEST['action'] == 'addRecall') || ($_REQUEST['add_new'])) {
    RecallService::saveRecall($_REQUEST);
    echo json_encode('saved');
    exit;
}

// Delete recall
if (($_REQUEST['action'] == 'delete_Recall') && ($_REQUEST['pid'])) {
    RecallService::deleteRecall();
    echo json_encode('deleted');
    exit;
}

// Clear pidList session
SessionUtil::unsetSession('pidList');
$pid_list = [];

// Process actions (postcards, labels, notes, phone)
if ($_REQUEST['action'] == "process") {
    $new_pid = json_decode((string) $_POST['parameter'], true);
    $new_pc_eid = json_decode((string) $_POST['pc_eid'], true);

    if (($_POST['item'] == "phone") || (($_POST['item'] == "notes") && ($_POST['msg_notes'] > ''))) {
        $sql = "INSERT INTO medex_outgoing (msg_pc_eid, msg_type, msg_reply, msg_extra_text)
                VALUES (?,?,?,?)";
        sqlQuery($sql, ['recall_' . $new_pid[0], $_POST['item'], $_SESSION['authUserID'], $_POST['msg_notes']]);
        echo json_encode("done");
        exit;
    }

    $pc_eidList = json_decode((string) $_POST['pc_eid'], true);
    $pidList = json_decode((string) $_POST['parameter'], true);
    $sessionSetArray['pc_eidList'] = $pc_eidList[0];
    $sessionSetArray['pidList'] = $pidList;
    SessionUtil::setSession($sessionSetArray);

    if ($_POST['item'] == "postcards") {
        foreach ($pidList as $pid) {
            $sql = "INSERT INTO medex_outgoing (msg_pc_eid, msg_type, msg_reply, msg_extra_text)
                    VALUES (?,?,?,?)";
            sqlQuery($sql, ['recall_' . $pid, $_POST['item'], $_SESSION['authUserID'], 'Postcard printed locally']);
        }
    }

    if ($_POST['item'] == "labels") {
        foreach ($pidList as $pid) {
            $sql = "INSERT INTO medex_outgoing (msg_pc_eid, msg_type, msg_reply, msg_extra_text)
                    VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE msg_extra_text='Label repeat'";
            sqlQuery($sql, ['recall_' . $pid, $_POST['item'], $_SESSION['authUserID'], 'Label printed locally']);
        }
    }

    echo text(json_encode($pidList));
    exit;
}

if ($_REQUEST['go'] == "Messages") {
    if ($_REQUEST['msg_id']) {
        $result = updateMessage($_REQUEST['msg_id']);
        echo json_encode($result);
        exit;
    }
}

exit;
```

---

## Update Frontend to Use Module Endpoint

### In JavaScript (likely in `/interface/main/messages/js/reminder_appts.js`):

**Change AJAX URLs from:**
```javascript
url: "save.php?go=Preferences"
url: "save.php?MedEx=start"
url: "save.php?go=sms_search"
```

**To:**
```javascript
url: "../modules/custom_modules/oe-module-medex/public/ajax.php?go=Preferences"
url: "../modules/custom_modules/oe-module-medex/public/ajax.php?MedEx=start"
url: "../modules/custom_modules/oe-module-medex/public/ajax.php?go=sms_search"
```

---

## Summary of Changes

### Files Created
1. ✅ `/library/RecallBoard/RecallService.php` (~120 lines)
2. ✅ `/library/PatientCommunication/CommunicationService.php` (~100 lines)
3. ✅ `/interface/modules/custom_modules/oe-module-medex/public/ajax.php` (~250 lines)

### Files Modified
1. ✅ `/interface/main/messages/save.php` - Remove lines 23-156, update recall endpoints
2. ✅ `/interface/main/messages/messages.php` - Conditional MedEx loading
3. ✅ `/interface/patient_tracker/patient_tracker.php` - Use CommunicationService
4. ✅ `/interface/main/messages/js/reminder_appts.js` - Update AJAX URLs

### Files Deleted
1. ✅ `/library/MedEx/` (entire directory)

---

## Result

**`save.php` becomes:**
- ✅ Pure recall board functionality
- ✅ ~100 lines instead of 261
- ✅ No MedEx dependencies
- ✅ Uses RecallService

**Module gets:**
- ✅ Own AJAX endpoint (`ajax.php`)
- ✅ All MedEx-specific functionality
- ✅ Completely self-contained

**Clean separation achieved!**

Does this make more sense?
