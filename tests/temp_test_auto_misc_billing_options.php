<?php

// Test script for verifying automatic creation of Miscellaneous Billing Options form

// --- Initial DB Connection Test ---
$db_host = 'localhost';
$db_user = 'openemr';
$db_pass = 'openemr';
$db_name = 'openemr';
$db_port = '3306';

$test_conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$test_conn) {
    echo "Direct mysqli_connect failed: " . mysqli_connect_error() . "\n";
    echo "This indicates an issue with 'localhost' resolution or DB accessibility from CLI.\n";
    echo "The test cannot proceed without a working database connection.\n";
    exit(1); // Exit early
}
mysqli_close($test_conn);
echo "Direct mysqli_connect test to 'localhost' succeeded. Proceeding with test setup.\n";
// --- End Initial DB Connection Test ---


// --- Initial Setup & Mocking (if necessary) ---
$_SERVER['HTTP_HOST'] = 'default';
$_SERVER['REQUEST_URI'] = '/interface/forms/newpatient/save.php'; // Path being simulated
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 1); // /app
$_SERVER['REQUEST_SCHEME'] = 'http';
$GLOBALS['webserver_root'] = $_SERVER['DOCUMENT_ROOT'];
$GLOBALS['web_root'] = "";
$GLOBALS['srcdir'] = $GLOBALS['webserver_root'] . "/library";
$GLOBALS['OE_SITES_BASE'] = $_SERVER['DOCUMENT_ROOT'] . "/sites";

// SESSION variables must be set BEFORE globals.php is included
// Initialize session if not already started (important for CLI)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['site_id'] = 'default'; // CRITICAL: Set before globals.php
$_SESSION['authUserID'] = $_SESSION['authUserID'] ?? '1';
$_SESSION['authUser'] = $_SESSION['authUser'] ?? 'admin';
$_SESSION['authProvider'] = $_SESSION['authProvider'] ?? 'Default';
$userauthorized = 1;


$ignoreAuth = false;
$skip_auth_includes = false;

$test_outputs = [];
$test_outputs[] = "Starting test: temp_test_auto_misc_billing_options.php";


$pid = 0;
$encounter_id = 0;
$form_misc_options_id = 0;
$forms_record_id = 0;
$dummy_facility_id = 0;

$cleanup_callbacks = [];
function add_cleanup_callback($callback) {
    global $cleanup_callbacks, $test_outputs;
    array_unshift($cleanup_callbacks, $callback);
}

try {
    require_once(dirname(__DIR__, 1) . "/interface/globals.php");
    $test_outputs[] = "interface/globals.php included.";

    $userauthorized = $GLOBALS['userauthorized'] ?? $_SESSION['userauthorized'] ?? 1;
    $test_outputs[] = "User session variables set: authUserID=" . ($_SESSION['authUserID']) . ", userauthorized=" . $userauthorized;

    $patientData = [
        'fname' => 'TestPatientF',
        'lname' => 'TestPatientL',
        'DOB' => '2000-01-01',
        'sex' => 'Male',
        'date' => date('Y-m-d H:i:s'),
        'pubpid' => 'TestPID' . time() . rand(100,999)
    ];

    $tempPid = sqlInsert("INSERT INTO `patient_data` (fname, lname, DOB, sex, date, pubpid) VALUES (?, ?, ?, ?, NOW(), ?)",
        [$patientData['fname'], $patientData['lname'], $patientData['DOB'], $patientData['sex'], $patientData['pubpid']]
    );

    if (!$tempPid || $tempPid === false) {
        throw new Exception("Failed to create dummy patient. sqlInsert returned: " . var_export($tempPid, true) . " Error: " . sqlError());
    }
    $pid = $tempPid;
    $test_outputs[] = "Dummy patient created with PID: $pid";
    add_cleanup_callback(function() use ($pid) {
        global $test_outputs;
        if ($pid > 0) {
            $test_outputs[] = "CLEANUP: Deleting patient_data for PID: $pid";
            sqlStatement("DELETE FROM `patient_data` WHERE `pid` = ?", [$pid]);
        }
    });

    $facilityRes = sqlQuery("SELECT id FROM facility WHERE name != 'Default' AND name != '' LIMIT 1");
    if ($facilityRes && !empty($facilityRes['id'])) {
        $dummy_facility_id = $facilityRes['id'];
    } else {
        $facilityDefaultRes = sqlQuery("SELECT id FROM facility WHERE name = 'Default' LIMIT 1");
        if ($facilityDefaultRes && !empty($facilityDefaultRes['id'])) {
            $dummy_facility_id = $facilityDefaultRes['id'];
        } else {
            $tempFacilityName = 'Test Facility ' . time();
            $dummy_facility_id_inserted = sqlInsert("INSERT INTO facility (name, active, pos_code) VALUES (?, 1, ?)", [$tempFacilityName, '11']);
            if (!$dummy_facility_id_inserted) {
                throw new Exception("No suitable facility found and failed to create a temporary one. Error: " . sqlError());
            }
            $dummy_facility_id = $dummy_facility_id_inserted;
            $test_outputs[] = "Created temporary facility ID: $dummy_facility_id";
            add_cleanup_callback(function() use ($dummy_facility_id) {
                global $test_outputs;
                if ($dummy_facility_id > 0) {
                    $test_outputs[] = "CLEANUP: Deleting temporary facility ID: $dummy_facility_id";
                    sqlStatement("DELETE FROM facility WHERE id = ?", [$dummy_facility_id]);
                }
            });
        }
    }
    $test_outputs[] = "Using facility ID: $dummy_facility_id";

    $catExists = sqlQuery("SELECT pc_catid FROM openemr_postcalendar_categories WHERE pc_catid = 1");
    if (!$catExists) {
        sqlInsert("INSERT IGNORE INTO openemr_postcalendar_categories (pc_catid, pc_catname, pc_event_duration, pc_cattype) VALUES (1, 'Test Category', 15, 1)");
        $test_outputs[] = "Ensured pc_catid '1' exists.";
    }

    $_POST = [
        'mode' => 'new',
        'pid' => $pid,
        'facility_id' => $dummy_facility_id,
        'form_date' => date('Y-m-d H:i:s'),
        'reason' => 'Test encounter for misc billing options',
        'pc_catid' => '1',
        'pos_code' => sqlQuery("SELECT pos_code FROM facility WHERE id = ?", [$dummy_facility_id])['pos_code'] ?? '11',
        'encounter_type' => '',
        'csrf_token_form' => CsrfUtils::collectCsrfToken(),
        'form_referral_source' => '',
        'class_code' => '',
        'in_collection' => '0',
        'parent_enc_id' => null,
        'provider_id' => $_SESSION['authUserID'],
        'referring_provider_id' => null,
        'ordering_provider_id' => null,
        'discharge_disposition' => null,
        'form_gid' => '',
        'issues' => []
    ];

    $GLOBALS['pid'] = $pid;

    $test_outputs[] = "Simulating inclusion of interface/forms/newpatient/save.php...";
    ob_start();
    require(dirname(__DIR__, 1) . "/interface/forms/newpatient/save.php");
    $captured_output = ob_get_clean();
    $test_outputs[] = "interface/forms/newpatient/save.php included. Captured output (first 500 chars): " . substr($captured_output, 0, 500);

    if (!isset($_SESSION['encounter']) || empty($_SESSION['encounter'])) {
        if (isset($GLOBALS['encounter']) && !empty($GLOBALS['encounter'])) {
             $_SESSION['encounter'] = $GLOBALS['encounter'];
             $test_outputs[] = "Found encounter ID in GLOBALS['encounter'].";
        } else {
            throw new Exception("Encounter ID was not set in session by save.php.");
        }
    }
    $encounter_id = $_SESSION['encounter'];
    $test_outputs[] = "Encounter created with ID: $encounter_id";

    add_cleanup_callback(function() use ($encounter_id) {
        global $test_outputs;
        if ($encounter_id > 0) {
            $test_outputs[] = "CLEANUP: Deleting form_encounter for encounter_id: $encounter_id";
            sqlStatement("DELETE FROM `form_encounter` WHERE `encounter` = ?", [$encounter_id]);
        }
    });

    $test_outputs[] = "Starting database verification...";
    $miscOptionsRes = sqlQuery(
        "SELECT medicaid_resubmission_code, id FROM form_misc_billing_options WHERE pid = ? AND encounter = ? ORDER BY id DESC LIMIT 1",
        [$pid, $encounter_id]
    );

    if (!$miscOptionsRes || empty($miscOptionsRes['id'])) {
        throw new Exception("No 'form_misc_billing_options' record found for PID $pid and Encounter $encounter_id. SQL Error: " . sqlError());
    }
    $test_outputs[] = "form_misc_billing_options record found. ID: " . $miscOptionsRes['id'];
    $form_misc_options_id = $miscOptionsRes['id'];
    add_cleanup_callback(function() use ($form_misc_options_id) {
        global $test_outputs;
        if ($form_misc_options_id > 0) {
            $test_outputs[] = "CLEANUP: Deleting form_misc_billing_options for id: $form_misc_options_id";
            sqlStatement("DELETE FROM `form_misc_billing_options` WHERE `id` = ?", [$form_misc_options_id]);
        }
    });

    if ($miscOptionsRes['medicaid_resubmission_code'] !== '1') {
        throw new Exception("Assertion Failed: medicaid_resubmission_code is '" . $miscOptionsRes['medicaid_resubmission_code'] . "', expected '1'.");
    }
    $test_outputs[] = "Assertion Passed: medicaid_resubmission_code is '1'.";

    $forms_record_id = $form_misc_options_.id;
    $formsRes = sqlQuery(
        "SELECT form_name, formdir FROM forms WHERE form_id = ? AND pid = ? AND encounter = ? AND formdir = 'misc_billing_options'",
        [$forms_record_id, $pid, $encounter_id]
    );

    if (!$formsRes || empty($formsRes['form_name'])) {
        throw new Exception("No 'forms' record found for form_id $forms_record_id (type misc_billing_options), PID $pid, Encounter $encounter_id. SQL Error: " . sqlError());
    }
    $test_outputs[] = "forms record found.";
     add_cleanup_callback(function() use ($forms_record_id) {
        global $test_outputs;
        if ($forms_record_id > 0) {
            $test_outputs[] = "CLEANUP: Deleting forms for form_id: $forms_record_id and formdir misc_billing_options";
            sqlStatement("DELETE FROM `forms` WHERE `form_id` = ? AND `formdir` = 'misc_billing_options'", [$forms_record_id]);
        }
    });

    if ($formsRes['form_name'] !== 'Misc Billing Options') {
        throw new Exception("Assertion Failed: forms.form_name is '" . $formsRes['form_name'] . "', expected 'Misc Billing Options'.");
    }
    $test_outputs[] = "Assertion Passed: forms.form_name is 'Misc Billing Options'.";

    if ($formsRes['formdir'] !== 'misc_billing_options') {
        throw new Exception("Assertion Failed: forms.formdir is '" . $formsRes['formdir'] . "', expected 'misc_billing_options'.");
    }
    $test_outputs[] = "Assertion Passed: forms.formdir is 'misc_billing_options'.";

    $test_outputs[] = "Test Passed!";
    $exit_code = 0;

} catch (Throwable $e) {
    $test_outputs[] = "Test Failed: " . $e->getMessage();
    $test_outputs[] = "On line: " . $e->getLine();
    $exit_code = 1;
} finally {
    $test_outputs[] = "Executing cleanup...";
    foreach ($cleanup_callbacks as $callback) {
        try {
            $callback();
        } catch (Throwable $e) {
            $test_outputs[] = "Error during cleanup: " . $e->getMessage();
        }
    }
    unset($_SESSION['encounter']);
    $test_outputs[] = "Cleanup finished.";

    foreach ($test_outputs as $line) {
        echo $line . "\n";
    }
    exit($exit_code);
}

?>
