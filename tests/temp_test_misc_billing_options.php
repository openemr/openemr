<?php

// Namespace for mocking OpenEMR specific classes if they are used with their namespace
namespace OpenEMR\Billing {
    if (!class_exists('OpenEMR\Billing\MiscBillingOptions')) {
        class MiscBillingOptions {
            public $box_14_qualifier_options = [];
            public $box_15_qualifier_options = [];
            public function __construct() { /* Mock constructor */ }
            public function generateDateQualifierSelect($name, $options, $obj) { return "<select name='$name'></select>"; }
            public function genReferringProviderSelect($name, $default_option_text, $selected_value) { return "<select name='$name'></select>"; }
        }
    }
}

// Return to global namespace for the rest of the script
namespace {

    // --- Mocking ADOdb connection ---
    if (!class_exists('MockADOConnection')) {
        class MockADOConnection {
            public $databaseType = 'mysql';
            public function Connect($host = "", $user = "", $password = "", $database = "", $forceNew = false) { return true; }
            public function PConnect($host = "", $user = "", $password = "", $database = "") { return true; }
            public function Execute($sql, $inputarr = false) {
                // Specific for globals.php initial check for 'globals' table
                if (is_string($sql) && stripos($sql, "SHOW TABLES LIKE 'globals'") !== false) {
                    // Simulate table exists, return a mock result object that has a non-empty rowCount or similar
                    $mockResult = new \stdClass();
                    $mockResult->fields = ['gl_name' => 'dummy']; // Make it seem like there's a row
                    $mockResult->_numOfRows = 1; // ADOdb uses this
                    return $mockResult;
                }
                // Specific for globals.php query for actual global values
                if (is_string($sql) && stripos($sql, "SELECT gl_name, gl_index, gl_value FROM globals") !== false) {
                    $mockResult = new \stdClass();
                    $mockResult->_numOfRows = 0; // No rows
                    $mockResult->fields = null;
                    return $mockResult;
                }
                 if (is_string($sql) && stripos($sql, "SELECT 1 FROM `modules`") !== false) {
                    $mockResult = new \stdClass();
                    $mockResult->_numOfRows = 1;
                    $mockResult->fields = ['1'=>'1'];
                    return $mockResult;
                }
                // Default for other queries (user_settings, patient_settings, lang_languages etc.)
                $mockResult = new \stdClass();
                $mockResult->_numOfRows = 0;
                $mockResult->fields = null;
                return $mockResult;
            }
            public function SelectLimit($sql, $nrows = -1, $offset = -1, $inputarr = false, $secs2cache = 0) {
                $mockResult = new \stdClass();
                $mockResult->_numOfRows = 0;
                $mockResult->fields = null;
                return $mockResult;
            }
            public function Affected_Rows() { return 0; }
            public function Insert_ID() { return 0; }
            public function ErrorMsg() { return ""; }
            public function ErrorNo() { return 0; }
            public function MetaTables() { return []; }
            public function MetaColumns($table) { return []; }
            public function Close() { return true; }
            // Add any other methods called by sql.inc.php or subsequent code
            public function SetFetchMode($mode) { return true; }
            public $fetchMode = 0; // Default fetch mode
        }
    }

    if (!function_exists('NewADOConnection')) {
        function NewADOConnection($db = 'mysql') {
            return new MockADOConnection();
        }
    }
    // --- End ADOdb Mocking ---

    // --- Other specific Mocks ---
    if (!function_exists('formFetch')) {
        function formFetch($table, $id) { return []; }
    }
    if (!function_exists('generate_select_list')) {
        function generate_select_list($name, $id, $value, $title, $default_value = ' ', $class = '', $attributes = '', $options = [], $selected_value = null, $use_empty_option = false, $empty_option_text = '', $disabled = false) {
            return "<select name='$name' id='$id'></select>";
        }
    }
    if (!function_exists('getPatientData')) {
        function getPatientData($pid, $field) { return ['ref_providerID' => null]; }
    }
    // --- End Other Mocks ---

    $_SERVER['HTTP_HOST'] = 'default';
    $_SERVER['REQUEST_URI'] = '/interface/forms/misc_billing_options/new.php';
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 1);
    $_SERVER['REQUEST_SCHEME'] = 'http';

    $ignoreAuth = true;
    $skip_auth_includes = true;

    $_SESSION['site_id'] = 'default';
    $_SESSION['encounter'] = "1";
    $_SESSION['pid'] = "1";
    $_SESSION['authUserID'] = '1';
    $_SESSION['language_direction'] = 'ltr';

    $GLOBALS['pid'] = $_SESSION['pid'];
    $GLOBALS['encounter'] = $_SESSION['encounter'];
    $GLOBALS['OE_SITES_BASE'] = $_SERVER['DOCUMENT_ROOT'] . "/sites";

    $GLOBALS['webserver_root'] = $_SERVER['DOCUMENT_ROOT'];
    $GLOBALS['web_root'] = "";
    $GLOBALS['srcdir'] = $GLOBALS['webserver_root'] . "/library"; // sql.inc.php needs $GLOBALS['srcdir'] for adodb path

    $_REQUEST['isBilling'] = true;
    $_REQUEST['pid'] = $GLOBALS['pid'];
    $_REQUEST['enc'] = $GLOBALS['encounter'];

    $v_js_includes = "1";

    // Include necessary OpenEMR global files
    require_once(dirname(__DIR__, 1) . "/interface/globals.php");

    $encounter = $_SESSION['encounter'];
    if (empty($encounter) && isset($GLOBALS['encounter'])) $encounter = $GLOBALS['encounter'];
    if (empty($encounter) && isset($_SESSION['encounter'])) $encounter = $_SESSION['encounter'];

    $pid = $_SESSION['pid'];
    if (empty($pid) && isset($GLOBALS['pid'])) $pid = $GLOBALS['pid'];
    if (empty($pid) && isset($_SESSION['pid'])) $pid = $_SESSION['pid'];

    $obj = [];

    ob_start();
    require(dirname(__DIR__, 1) . "/interface/forms/misc_billing_options/new.php");
    $output = ob_get_clean();

    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    @$dom->loadHTML($output);
    libxml_clear_errors();
    $xpath = new \DOMXPath($dom);

    $inputElement = $xpath->query("//input[@name='medicaid_resubmission_code']")->item(0);

    $value = '';
    if ($inputElement) {
        $value = $inputElement->getAttribute('value');
    }

    if ($value === '1') {
        echo "Test Passed: medicaid_resubmission_code default value is '1'.\n";
        exit(0);
    } else {
        echo "Test Failed: medicaid_resubmission_code default value is '" . $value . "', expected '1'.\n";
        // echo "Full output for debugging:\n" . htmlentities($output) . "\n";
        exit(1);
    }
} // End of global namespace block
?>
