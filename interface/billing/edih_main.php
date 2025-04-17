<?php

/**
 * edi_history_main.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin McCormick Longview, Texas
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Kevin McCormick Longview, Texas
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

/**
 * this define is used to prevent direct access to the included scripts
 * which have the corresponding definition commented for now
 */
define('SITE_IN', 1);

// define constants
// since enounter digits are sequential, digit length should rarely change
// however for a startup they may, or a "mask" value of 1000 or 10000
// would be a good idea if there are problems with deciphering the pid-encounter
// same idea for pid value, but since encounter is unique and always last, it is essential
// possibly check the mask value in OpenEMR globals to set this


// Try to prevent search for too short encounter value
if (!defined("ENCOUNTER_MIN_DIGIT_LENGTH")) {
    define("ENCOUNTER_MIN_DIGIT_LENGTH", 1);
}

// these delimiters are hardcoded into OpenEMR batch files
if (!defined("SEG_ELEM_DELIM")) {
    define("SEG_ELEM_DELIM", "*");
}

if (!defined("SEG_TERM_DELIM")) {
    define("SEG_TERM_DELIM", "~");
}

//clearinghouse practice
if (!defined("IBR_DELIMITER")) {
    define("IBR_DELIMITER", "|");
}

//
if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

//
// path will be "$srcdir/edihistory/filename.php"
require_once("$srcdir/edihistory/edih_csv_inc.php");    //dirname(__FILE__) . "/edihist/csv_record_include.php");
require_once("$srcdir/edihistory/edih_io.php");         //dirname(__FILE__) . "/edihist/ibr_io.php");
require_once("$srcdir/edihistory/edih_x12file_class.php");
require_once("$srcdir/edihistory/edih_uploads.php");         //dirname(__FILE__) . "/edihist/ibr_uploads.php");
require_once("$srcdir/edihistory/edih_csv_parse.php");          //dirname(__FILE__) . "/edihist/ibr_997_read.php");
require_once("$srcdir/edihistory/edih_csv_data.php");          //dirname(__FILE__) . "/edihist/ibr_277_read.php");
require_once("$srcdir/edihistory/edih_997_error.php");
require_once("$srcdir/edihistory/edih_segments.php");
require_once("$srcdir/edihistory/edih_archive.php");        //dirname(__FILE__) . "/edihist/ibr_batch_read.php");
require_once("$srcdir/edihistory/edih_271_html.php");          //dirname(__FILE__) . "/edihist/ibr_ack_read.php");
require_once("$srcdir/edihistory/edih_277_html.php");
require_once("$srcdir/edihistory/edih_278_html.php");
require_once("$srcdir/edihistory/edih_835_html.php");           //dirname(__FILE__) . "/edihist/ibr_era_read.php");
require_once("$srcdir/edihistory/codes/edih_271_code_class.php");      //dirname(__FILE__) . "/edihist/ibr_code_arrays.php");
require_once("$srcdir/edihistory/codes/edih_835_code_class.php"); //dirname(__FILE__) . "/edihist/ibr_status_code_arrays.php");
require_once("$srcdir/edihistory/codes/edih_997_codes.php");
//
// php may output line endings with included files
ob_clean();

if (isset($GLOBALS['OE_SITE_DIR'])) {
    $edih_base_dir = csv_edih_basedir();
    $edih_tmp_dir = csv_edih_tmpdir();
} else {
    die("EDI History: Did not get directory path information!");
}

// if we are not set up, create directories and csv files
//if (!is_dir(dirname(__FILE__) . '/edihist' . IBR_HISTORY_DIR) ) {
if (!is_dir($edih_tmp_dir)) {
    //
    //echo "setup with base directory: $edih_base_dir <br />" .PHP_EOL;
    if (csv_setup() == true) {
        $html_str = '';
        if (is_dir($edih_tmp_dir)) {
            csv_clear_tmpdir();
        }
    } else {
        print $html_str;
        exit;
    }
}

// avoid unitialized variable error
$html_str = '';
// debug
if (count($_GET)) {
    $dbg_str = "_GET request " . PHP_EOL;
    foreach ($_GET as $k => $v) {
        $dbg_str .= " $k => $v ";
    }

    csv_edihist_log($dbg_str);
}

if (count($_POST)) {
    $dbg_str = "_POST request " . PHP_EOL;
    foreach ($_POST as $k => $v) {
        $dbg_str .= " $k => $v ";
    }

    csv_edihist_log($dbg_str);
}

//
/* ******* remove functions to separate file ******* */
/*
 * functions called in the if stanzas are now in edih_io.php
 */
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //
    // === log user access on POST requests ===========
    csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
    // =====================================
    if (isset($_POST['NewFiles']) && isset($_FILES['fileUplMulti'])) {
        // process new files button clicked
        $html_str = edih_disp_file_upload();
    } elseif (isset($_POST['viewx12Files']) && isset($_FILES['fileUplx12'])) {
        // process new files button clicked
        $html_str = edih_disp_x12file();
    } elseif (isset($_POST['notes_hidden'])) {
        // user notes
        $html_str = edih_user_notes();
    } elseif (isset($_POST['ArchiveRequest'])) {
        // request to archive edi files
        $req = filter_input(INPUT_POST, 'ArchiveRequest', FILTER_DEFAULT);
        if ($req == 'requested') {
            $html_str = edih_disp_archive();
        } else {
            $html_str .= "<p>Input Error: for edi files archive function</p>" . PHP_EOL;
        }
    } elseif (isset($_POST['ArchiveRestore'])) {
        // request to restore an archive of edi files
        $req = filter_input(INPUT_POST, 'ArchiveRestore', FILTER_DEFAULT);
        if ($req == 'restore') {
            $html_str = edih_disp_archive_restore();
        } else {
            $html_str .= "<p>Input Error: for edi files archive restore function</p>" . PHP_EOL;
        }
    } else {
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // ===========
        $html_str .= "<p>Error: unrecognized value in request</p>" . PHP_EOL;
        // debug
        $bg_str = "Unknown POST value: " . PHP_EOL;
        foreach ($_POST as $ky => $val) {
            $bg_str .= "$ky : $val " . PHP_EOL;
        }

        csv_edihist_log($bg_str);
    }  // end if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
    //
} elseif (strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //
    if (isset($_GET['srvinfo']) && $_GET['srvinfo'] == 'yes') {
        // initial ajax request
        $html_str = edih_php_inivals();
    } elseif (isset($_GET['csvtbllist'])) {
        // list of available csv tables
        $tbl = filter_input(INPUT_GET, 'csvtbllist', FILTER_DEFAULT);
        //
        if ($tbl == 'yes') {
            $html_str = csv_table_select_list();
        } else {
            $html_str = json_encode('error');
            csv_edihist_log("GET error: missing parameter for csv table list");
            return $html_str;
        }
    } elseif (isset($_GET['archlist'])) {
        // list of csv archive files
        $tbl = filter_input(INPUT_GET, 'archlist', FILTER_DEFAULT);
        if ($tbl == 'yes') {
            $html_str = csv_archive_select_list();
        } else {
            $html_str = json_encode('error');
            csv_edihist_log("GET error: missing parameter for csv archive list");
            return $html_str;
        }
    } elseif (isset($_GET['loglist'])) {
        // initial setup -- populate log file select { loglist: 'yes' },
        $la = filter_input(INPUT_GET, 'loglist', FILTER_DEFAULT);
        $html_str = edih_disp_logfiles();
    } elseif (isset($_GET['archivelog'])) {
        // Notes tab  [archive log files if older than 7 days]
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =====================================
        $html_str = edih_disp_logfiles();
    } elseif (isset($_GET['logshowfile'])) {
        // New Files tab  [ Process New ]
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =====================================
        $html_str = edih_disp_log();
    } elseif (isset($_GET['ProcessFiles'])) {
        // New Files tab  [ Process New ]
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =====================================
        $html_str = edih_disp_file_process();
    } elseif (isset($_GET['gtbl'])) {
        // get from a csv_table
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // ======================================
        $gtb = filter_input(INPUT_GET, 'gtbl', FILTER_DEFAULT);
        //
        if ($gtb == 'file') {
            $html_str = edih_disp_x12file();
        } elseif ($gtb == 'claim') {
            $html_str = edih_disp_x12trans();
        } elseif ($gtb == 'hist') {
            $chkd = (isset($_GET['chkdenied'])) ? filter_input(INPUT_GET, 'chkdenied', FILTER_DEFAULT) : '';
            if ($chkd == 'yes') {
                $html_str = edih_disp_denied_claims();
            } else {
                $html_str = edih_disp_x12trans();
            }
        } else {
            $html_str = '<p>Input error: missing parameter</p>';
            csv_edihist_log("GET error: missing parameter, no 'gtbl' value");
        }
    } elseif (isset($_GET['csvShowTable'])) {
        // view a csv table
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $html_str = edih_disp_csvtable();
    } elseif (isset($_GET['hist_enctr'])) {
        // history for encounter
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $html_str = edih_disp_clmhist();
    } elseif (isset($_GET['ckprocessed'])) {
        // whether 835 payment file has been applied to pt balance
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $la = filter_input(INPUT_GET, 'ckprocessed', FILTER_DEFAULT);
        if ($la == 'yes') {
            // ajax request on page load
            $html_str = edih_disp_era_processed();
            $html_str = "alert('$html_str')";
        }
    } elseif (isset($_GET['chkdenied'])) {
        // files csv table
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =====================================
        $chkd = filter_input(INPUT_GET, 'chkdenied', FILTER_DEFAULT);
        if ($chkd == 'yes') {
            $html_str = edih_disp_denied_claims();
        } else {
            $html_str = '<p>Input error: invalid parameter</p>';
            csv_edihist_log("GET error: missing parameter, invalid 'chkdenied' value");
        }
    } elseif (isset($_GET['showlog'])) {
        // show the edi_history log
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $lgnm = (isset($_GET['log_select'])) ? filter_input(INPUT_GET, 'log_select', FILTER_DEFAULT) : '';
        $la = (isset($_GET['logshowfile'])) ? filter_input(INPUT_GET, 'logshowfile', FILTER_DEFAULT) : '';
        if ($la == 'getlog' && $lgnm) {
            $html_str = csv_log_html($lgnm);
        } else {
            $html_str = "Show Log: input parameter error<br />" ;
        }
    } elseif (isset($_GET['getnotes'])) {
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $la = filter_input(INPUT_GET, 'getnotes', FILTER_DEFAULT);
        $html_str = ($la) ? edih_user_notes() : "input parameter error<br />";
    } elseif (isset($_GET['archivereport'])) {
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        // data: { period: prd, archivereport: 'yes'),
        $html_str = edih_disp_archive_report();
    } else {
        // ========= log user access for user commands ===========
        csv_edihist_log("User: " . $_SERVER['REMOTE_ADDR'] . ' - ' . date("F j, Y, g:i a"));
        // =======================================
        $html_str = "Error: unknown parameter in request<br />" . PHP_EOL;
        $bg_str = "Error GET unknown value ";
        foreach ($_GET as $ky => $val) {
            $bg_str .= "$ky : $val " . PHP_EOL;
        }

        csv_edihist_log($bg_str);
        //$html_str .= var_dump($_GET) . PHP_EOL;
    }
} else {
    die("EDI History: invalid input method <br />");
}

//
$isclear = csv_clear_tmpdir();
if (!$isclear) {
    //echo "file contents remain in $edih_tmp_dir <br />".PHP_EOL;
    csv_edihist_log("file contents remain in $edih_tmp_dir");
}

//
if (!$html_str) {
    csv_edihist_log("no html output!");
    die("No content in response <br />" . PHP_EOL);
}

//
print $html_str;
