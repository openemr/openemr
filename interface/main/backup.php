<?php

/**
 * This script creates a backup tarball, emr_backup.tar, and sends
 * it to the user's browser for download.  The tarball includes:
 *
 * an OpenEMR database dump  (openemr.sql.gz)
 * the OpenEMR web directory (openemr.tar.gz)
 *
 * The OpenEMR web directory is important because it includes config-
 * uration files, patient documents, and possible customizations, and
 * also because the database structure is dependent on the installed
 * OpenEMR version.
 *
 * This script depends on execution of some external programs:
 * mysqldump & pg_dump.  It has been tested with Debian and Ubuntu
 * Linux and with Windows XP.
 *
 * DO NOT PRESUME THAT IT WORKS FOR YOU until you have successfully
 * tested a restore!
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Bill Cernansky (www.mi-squared.com)
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2008-2014, 2016, 2021-2022 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

set_time_limit(0);
require_once("../globals.php");
require_once("$srcdir/layout.inc.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!extension_loaded('zlib')) {
      die('Abort ' . basename(__FILE__) . ' : Missing zlib extensions');
}

if (!function_exists('gzopen') && function_exists('gzopen64')) {
    function gzopen($filename, $mode, $use_include_path = 0)
    {
        return gzopen64($filename, $mode, $use_include_path);
    }
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Backup")]);
    exit;
}

// When automatically including lists used in selected layouts, these lists are not included.
$excluded_lists = array(
    'allergy_issue_list',
    'boolean',
    'education_level',
    'ethrace',
    'Gender',
    'genhivhist',
    'occupations',
    'Relation_to_Client',
    'sex',
    'Sexual_Orientation',
    'yesno',
);

$BTN_TEXT_CREATE = xl('Create Backup');
$BTN_TEXT_EXPORT = xl('Export Configuration');
$BTN_TEXT_IMPORT = xl('Import Configuration');
$BTN_TEXT_LOG = xl('Backup/Delete Log Data');
// ViSolve: Create Log  Backup button
$BTN_TEXT_CREATE_EVENTLOG = xl('Create Eventlog Backup');

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_export'])) {
    $form_step = 101;
}

if (!empty($_POST['form_import'])) {
    $form_step = 201;
}

//ViSolve: Assign Unique Number for the Log Creation
if (!empty($_POST['form_backup'])) {
    $form_step = 301;
}

if (!empty($_POST['form_logarchive'])) {
    $form_step = 401;
}

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

# set up main paths
$backup_file_prefix = "emr_backup";
$backup_file_suffix = ".tar";
$TMP_BASE = $GLOBALS['temporary_files_dir'] . "/openemr_web_backup";
$BACKUP_DIR = $TMP_BASE . "/emr_backup";
$TAR_FILE_PATH = $TMP_BASE . DIRECTORY_SEPARATOR . $backup_file_prefix . $backup_file_suffix;
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
$MYSQL_PATH = realpath($GLOBALS['mysql_bin_dir']);
$PERL_PATH = realpath($GLOBALS['perl_bin_dir']);

if ($form_step == 6) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Length: " . filesize($TAR_FILE_PATH));
    header("Content-Disposition: attachment; filename=" . basename($TAR_FILE_PATH));
    header("Content-Description: File Transfer");

    if (is_file($TAR_FILE_PATH)) {
        $chunkSize = 1024 * 1024;
        $handle = fopen($TAR_FILE_PATH, 'rb');
        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSize);
            echo $buffer;
            ob_flush();
            flush();
        }
        fclose($handle);
    } else {
        obliterate_dir($BACKUP_DIR);
        $dieMsg = xlt("Backup Failed missing generated file");
        die($dieMsg);
    }
    unlink($TAR_FILE_PATH);
    obliterate_dir($BACKUP_DIR);
    exit(0);
}

if ($form_step == 104) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Length: " . filesize($EXPORT_FILE));
    header("Content-Disposition: attachment; filename=" . basename($EXPORT_FILE));
    header("Content-Description: File Transfer");
    readfile($EXPORT_FILE);
    unlink($EXPORT_FILE);
    exit(0);
}

// CSV export of lists.
//
if ($form_step == 102.1) {
    if (is_array($_POST['form_sel_lists'] ?? '')) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download; charset=utf-8");
        header("Content-Disposition: attachment; filename=lists.csv");
        header("Content-Description: File Transfer");
        // Prepend a BOM (Byte Order Mark) header to mark the data as UTF-8.  See:
        // http://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files
        // http://crashcoursing.blogspot.com/2011/05/exporting-csv-with-special-characters.html
        echo "\xEF\xBB\xBF";
        // CSV headers:
        echo csvEscape(xl('List')) . ',';
        echo csvEscape(xl('ID')) . ',';
        echo csvEscape(xl('Title')) . ',';
        echo csvEscape(xl('Translated')) . ',';
        echo csvEscape(xl('Order')) . ',';
        echo csvEscape(xl('Default')) . ',';
        echo csvEscape(xl('Active')) . ',';
        echo csvEscape(xl('Global ID')) . ',';
        echo csvEscape(xl('Notes')) . ',';
        echo csvEscape(xl('Codes')) . '';
        echo "\n";
        foreach ($_POST['form_sel_lists'] as $listid) {
            $res = sqlStatement(
                "SELECT * FROM list_options WHERE list_id = ? ORDER BY seq, title",
                array($listid)
            );
            while ($row = sqlFetchArray($res)) {
                $xtitle = xl_list_label($row['title']);
                if ($xtitle === $row['title']) {
                    $xtitle = '';
                }
                echo csvEscape($row['list_id']) . ',';
                echo csvEscape($row['option_id']) . ',';
                echo csvEscape($row['title']) . ',';
                echo csvEscape($xtitle) . ',';
                echo csvEscape($row['seq']) . ',';
                echo csvEscape($row['is_default']) . ',';
                echo csvEscape($row['activity']) . ',';
                echo csvEscape($row['mapping']) . ',';
                echo csvEscape($row['notes']) . ',';
                echo csvEscape($row['codes']) . '';
                echo "\n";
            }
        }
    }
    exit(0);
}

// CSV export of layouts.
//
if ($form_step == 102.2) {
    if (is_array($_POST['form_sel_layouts'] ?? '')) {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download; charset=utf-8");
        header("Content-Disposition: attachment; filename=layouts.csv");
        header("Content-Description: File Transfer");
        // Prepend a BOM (Byte Order Mark) header to mark the data as UTF-8.  See:
        // http://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files
        // http://crashcoursing.blogspot.com/2011/05/exporting-csv-with-special-characters.html
        echo "\xEF\xBB\xBF";
        // CSV headers:
        echo csvEscape(xl('Form')) . ',';
        echo csvEscape(xl('Order')) . ',';
        echo csvEscape(xl('Source')) . ',';
        echo csvEscape(xl('Group')) . ',';
        echo csvEscape(xl('ID')) . ',';
        echo csvEscape(xl('Label')) . ',';
        echo csvEscape(xl('Translated')) . ',';
        echo csvEscape(xl('UOR')) . ',';
        echo csvEscape(xl('Type')) . ',';
        echo csvEscape(xl('Width')) . ',';
        echo csvEscape(xl('Height')) . ',';
        echo csvEscape(xl('Max')) . ',';
        echo csvEscape(xl('List')) . ',';
        echo csvEscape(xl('Label Cols')) . ',';
        echo csvEscape(xl('Data Cols')) . ',';
        echo csvEscape(xl('Options')) . ',';
        echo csvEscape(xl('Description')) . ',';
        echo csvEscape(xl('Translated')) . ',';
        echo csvEscape(xl('Conditions')) . '';
        echo "\n";
        foreach ($_POST['form_sel_layouts'] as $layoutid) {
            $res = sqlStatement(
                "SELECT l.*, p.grp_title FROM layout_options AS l " .
                "JOIN layout_group_properties AS p ON p.grp_form_id = l.form_id AND " .
                "p.grp_group_id = l.group_id AND p.grp_activity = 1 " .
                "WHERE l.form_id = ? ORDER BY l.group_id, l.seq, l.title",
                array($layoutid)
            );
            while ($row = sqlFetchArray($res)) {
                $xtitle = xl_layout_label($row['title']);
                if ($xtitle === $row['title']) {
                    $xtitle = '';
                }
                $xdesc = $row['description'];
                if (substr($xdesc, 0, 1) != '<') {
                    $xdesc = xl_layout_label($xdesc);
                }
                if ($xdesc === $row['description']) {
                    $xdesc = '';
                }
                echo csvEscape($row['form_id'     ]) . ',';
                echo csvEscape($row['seq'         ]) . ',';
                echo csvEscape($sources[$row['source']]) . ',';
                echo csvEscape($row['grp_title'   ]) . ',';
                echo csvEscape($row['field_id'    ]) . ',';
                echo csvEscape($row['title'       ]) . ',';
                echo csvEscape($xtitle) . ',';
                echo csvEscape($UOR[$row['uor']]) . ',';
                echo csvEscape($datatypes[$row['data_type']]) . ',';
                echo csvEscape($row['fld_length'  ]) . ',';
                echo csvEscape($row['fld_rows'    ]) . ',';
                echo csvEscape($row['max_length'  ]) . ',';
                echo csvEscape($row['list_id'     ]) . ',';
                echo csvEscape($row['titlecols'   ]) . ',';
                echo csvEscape($row['datacols'    ]) . ',';
                echo csvEscape($row['edit_options']) . ',';
                echo csvEscape($row['description' ]) . ',';
                echo csvEscape($xdesc) . ',';
                echo csvEscape($row['conditions'  ]) . '';
                echo "\n";
            }
        }
    }
    exit(0);
}

// CSV export of old log entries.
//
if ($form_step == 402) {
    if (!empty($_POST['form_end_date'])) {
        $end_date = DateToYYYYMMDD($_POST['form_end_date']);
        // This is the "filename" for the Content-Disposition header.
        $filename = "log_archive_{$end_date}.csv";

        $outfile = tempnam($GLOBALS['temporary_files_dir'], 'OET');
        if ($outfile === false) {
            die("tempnam('" . text($GLOBALS['temporary_files_dir']) . "','OET') failed.\n");
        }
        $hout = fopen($outfile, "w");
        $wcount = 0;

        // Prepend a BOM (Byte Order Mark) header to mark the data as UTF-8.  See:
        // http://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files
        // http://crashcoursing.blogspot.com/2011/05/exporting-csv-with-special-characters.html
        $out = "\xEF\xBB\xBF";
        // CSV headers:
        $out .= csvEscape(xl('id')) . ',';
        $out .= csvEscape(xl('date')) . ',';
        $out .= csvEscape(xl('event')) . ',';
        $out .= csvEscape(xl('user')) . ',';
        $out .= csvEscape(xl('groupname')) . ',';
        $out .= csvEscape(xl('comments')) . ',';
        $out .= csvEscape(xl('user_notes')) . ',';
        $out .= csvEscape(xl('patient_id')) . ',';
        $out .= csvEscape(xl('success')) . ',';
        $out .= csvEscape(xl('checksum')) . ',';
        $out .= csvEscape(xl('crt_user')) . '';
        $out .= "\n";
        fwrite($hout, $out);

        // Somewhere there's a memory leak in the ADODB stuff. We do multiple selects to
        // work around this.
        $lastid = 0;
        while (true) {
            $res = sqlStatementNoLog(
                "SELECT * FROM `log` WHERE `date` <= ? AND `id` > ? ORDER BY `id` LIMIT 50000",
                array("$end_date 23:59:59", $lastid)
            );
            if (!sqlNumRows($res)) {
                break;
            }
            while ($row = sqlFetchArray($res)) {
                $out  = csvEscape($row['id'        ]) . ',' .
                        csvEscape($row['date'      ]) . ',' .
                        csvEscape($row['event'     ]) . ',' .
                        csvEscape($row['user'      ]) . ',' .
                        csvEscape($row['groupname' ]) . ',' .
                        csvEscape($row['comments'  ]) . ',' .
                        csvEscape($row['user_notes']) . ',' .
                        csvEscape($row['patient_id']) . ',' .
                        csvEscape($row['success'   ]) . ',' .
                        csvEscape($row['checksum'  ]) . ',' .
                        csvEscape($row['crt_user'  ]) . '' .
                        "\n";
                if (!fwrite($hout, $out)) {
                    die("fwrite() failed!");
                }
                $lastid = $row['id'];
            }
        }

        fclose($hout);

        // Do compression if requested (it is!)
        if (true) {
            $zip = new ZipArchive();
            $zippedoutfile = tempnam($GLOBALS['temporary_files_dir'], 'OEZ');
            if ($zippedoutfile === false) {
                die("tempnam('" . text($GLOBALS['temporary_files_dir']) . "','OEZ') failed.\n");
            }
            if ($zip->open($zippedoutfile, ZIPARCHIVE::OVERWRITE) !== true) {
                die(xlt('Cannot create file') . " '$zipname'\n");
            }
            if (!$zip->addFile($outfile, $filename)) {
                die(xlt('Cannot add to archive') . " '$zipname'\n");
            }
            $zip->close();
            $filename .= '.zip';
            unlink($outfile);
            $outfile = $zippedoutfile;
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Description: File Transfer");
        header("Content-Length: " . filesize($outfile));
        readfile($outfile);
        unlink($outfile);
    } else {
        die(xlt("End date is missing!"));
    }
    exit(0);
}

?>
<html>

<head>
<?php Header::setupHeader(['datetime-picker']); ?>
<title><?php echo xlt('Backup'); ?></title>

<script>

$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

// Called from export page or log archive page to specify what it will do.
//   102   = SQL export of selected tables, lists and layouts
//   102.1 = Download selected lists as CSV
//   102.2 = download selected layouts as CSV
//   402   = CSV export of log archive
//   405   = Delete from the log
//
function export_submit(step) {
    var f = document.forms[0];
    f.form_step.value = step;
    top.restoreSession();
    f.submit();
}

</script>

</head>

<body class="body_top">
<center>
&nbsp;<br />
<form method='post' action='backup.php' enctype='multipart/form-data' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table<?php echo ($form_step != 101) ? " style='width:50em'" : ""; ?>>
 <tr>
  <td>

<?php
$cmd = '';
// $cmdarr exists because some commands may be too long for a single exec.
$cmdarr = array();
$mysql_cmd = $MYSQL_PATH . DIRECTORY_SEPARATOR . 'mysql';
$mysql_dump_cmd = $mysql_cmd . 'dump';
$mysql_ssl = '';
if (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca")) {
    // Support for mysql SSL encryption
    $mysql_ssl = " --ssl-ca=" . escapeshellarg($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-ca") . " ";
    if (
        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") &&
        file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert")
    ) {
        // Support for mysql SSL client based cert authentication
        $mysql_ssl .= "--ssl-cert=" . escapeshellarg($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-cert") . " ";
        $mysql_ssl .= "--ssl-key=" . escapeshellarg($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/mysql-key") . " ";
    }
}

$file_to_compress = '';  // if named, this iteration's file will be gzipped after it is created
$eventlog = 0;  // Eventlog Flag

if ($form_step == 0) {
    echo "<table>\n";
    echo " <tr>\n";
    echo "  <td><input class='btn btn-secondary' type='submit' name='form_create' value='" . attr($BTN_TEXT_CREATE) . "' /></td>\n";
    echo "  <td>" . xlt('Create and download a full backup') . "</td>\n";
    echo " </tr>\n";
  // The config import/export feature is optional.
    if (!empty($GLOBALS['configuration_import_export'])) {
        echo " <tr>\n";
        echo "  <td><input class='btn btn-secondary' type='submit' name='form_export' value='" . attr($BTN_TEXT_EXPORT) . "' /></td>\n";
        echo "  <td>" . xlt('Download configuration data') . "</td>\n";
        echo " </tr>\n";
        echo " <tr>\n";
        echo "  <td><input class='btn btn-secondary' type='submit' name='form_import' value='" . attr($BTN_TEXT_IMPORT) . "' /></td>\n";
        echo "  <td>" . xlt('Upload configuration data') . "</td>\n";
        echo " </tr>\n";
        echo " <tr>\n";
        echo "  <td><input class='btn btn-secondary' type='submit' name='form_logarchive' value='" . attr($BTN_TEXT_LOG) . "' /></td>\n";
        echo "  <td>" . xlt('Download and/or delete log data') . "</td>\n";
        echo " </tr>\n";
    }

// ViSolve : Add ' Create Log table backup Button'
    echo " <tr>\n";
    echo "  <td><input class='btn btn-secondary' type='submit' name='form_backup' value='" . attr($BTN_TEXT_CREATE_EVENTLOG) . "' /></td>\n";
    echo "  <td>" . xlt('Create Eventlog Backup') . "</td>\n";
    echo " </tr>\n";
    echo " <tr>\n";
    echo "  <td></td><td class='text'>" . xlt('Note that the Eventlog Backup is currently set to save in the following folder') . ": " . text($GLOBALS['backup_log_dir']) . " . " . xlt('Recommend setting the Path for Event Log Backup in Globals settings in the Miscellaneous section to something other than your tmp/temp directory.') . " " . xlt('Please refer to') . ' README-Log-Backup.txt ' . xlt('file in the Documentation directory to learn how to automate the process of creating log backups') . ".</td>\n";
    echo " </tr>\n";
    echo "</table>\n";
}

if ($form_step == 1) {
    $form_status .= xl('Dumping OpenEMR database') . "...||br-placeholder||";
    echo brCustomPlaceholder(text($form_status));
    if (file_exists($TAR_FILE_PATH)) {
        if (! unlink($TAR_FILE_PATH)) {
            die(xlt("Couldn't remove old backup file:") . " " . text($TAR_FILE_PATH));
        }
    }

    if (! obliterate_dir($TMP_BASE)) {
        die(xlt("Couldn't remove dir:") . " " . text($TMP_BASE));
    }

    if (! mkdir($BACKUP_DIR, 0777, true)) {
        die(xlt("Couldn't create backup dir:") . " " . text($BACKUP_DIR));
    }

    $file_to_compress = "$BACKUP_DIR/openemr.sql";   // gzip this file after creation

    if ($GLOBALS['include_de_identification'] == 1) {
        //include routines during backup when de-identification is enabled
        $cmd = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
        " -p" . escapeshellarg($sqlconf["pass"]) .
        " -h " . escapeshellarg($sqlconf["host"]) .
        " --port=" . escapeshellarg($sqlconf["port"]) .
        " --routines" .
        " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
        " --hex-blob --opt --quote-names --no-tablespaces -r " . escapeshellarg($file_to_compress) . " $mysql_ssl " .
        escapeshellarg($sqlconf["dbase"]);
    } else {
        $cmd = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
        " -p" . escapeshellarg($sqlconf["pass"]) .
        " -h " . escapeshellarg($sqlconf["host"]) .
        " --port=" . escapeshellarg($sqlconf["port"]) .
        " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
        " --hex-blob --opt --quote-names --no-tablespaces -r " . escapeshellarg($file_to_compress) . " $mysql_ssl " .
        escapeshellarg($sqlconf["dbase"]);
    }

    $auto_continue = true;
}

if ($form_step == 2) {
    ++$form_step;
}

if ($form_step == 3) {
    $form_status .= xl('Dumping OpenEMR web directory tree') . "...||br-placeholder||";
    echo brCustomPlaceholder(text($form_status));
    $cur_dir = getcwd();
    chdir($webserver_root);

    // Select the files and directories to archive.  Basically everything
    // except site-specific data for other sites.
    $file_list = array();
    $dh = opendir($webserver_root);
    if (!$dh) {
        die("Cannot read directory '" . text($webserver_root) . "'.");
    }

    while (false !== ($filename = readdir($dh))) {
        if ($filename == '.' || $filename == '..') {
            continue;
        }

        if ($filename == 'sites') {
            // Omit other sites.
            $file_list[] = "$filename/" . $_SESSION['site_id'];
        } else {
            $file_list[] = $filename;
        }
    }

    closedir($dh);

    $arch_file = $BACKUP_DIR . DIRECTORY_SEPARATOR . "openemr.tar.gz";
    if (!create_tar_archive($arch_file, "gz", $file_list)) {
        die(xlt("An error occurred while dumping OpenEMR web directory tree"));
    }

    chdir($cur_dir);
    $auto_continue = true;
}

if ($form_step == 4) {
     ++$form_step;
}

if ($form_step == 5) {   // create the final compressed tar containing all files
    $form_status .= xl('Backup file has been created. Will now send download.') . "||br-placeholder||";
    echo brCustomPlaceholder(text($form_status));
    $cur_dir = getcwd();
    chdir($BACKUP_DIR);
    $file_list = array('.');
    if (!create_tar_archive($TAR_FILE_PATH, '', $file_list)) {
        die(xlt("Error: Unable to create downloadable archive"));
    }

    chdir($cur_dir);
    /* To log the backup event */
    if ($GLOBALS['audit_events_backup']) {
        EventAuditLogger::instance()->newEvent("backup", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Backup is completed");
    }

    $auto_continue = true;
}

if ($form_step == 101) {
    echo "<p class='font-weight-bold'>&nbsp;" . xlt('Select the configuration items to export') . ":</p>";

    echo "<table cellspacing='10' cellpadding='0'>\n<tr>\n<td valign='top' nowrap>\n";

    echo "<strong>" . xlt('Tables') . "</strong><br />\n";
    echo "<input type='checkbox' name='form_cb_services' value='1' />\n";
    echo " " . xlt('Services') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_products' value='1' />\n";
    echo " " . xlt('Products') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_prices' value='1' />\n";
    echo " " . xlt('Prices') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_categories' value='1' />\n";
    echo " " . xlt('Document Categories') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_feesheet' value='1' />\n";
    echo " " . xlt('Fee Sheet Options') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_lab_config' value='1' />\n";
    echo " " . xlt('Lab Configuration') . "<br />\n";
    echo "<input type='checkbox' name='form_cb_lang' value='1' />\n";
    echo " " . xlt('Translations') . "<br />\n";

  // Multi-select for lists.
    echo "</td><td valign='top'>\n";
    echo "<strong>" . xlt('Lists') . "</strong><br />\n";
    echo "<select class='form-control' multiple name='form_sel_lists[]' size='15'>";
    $lres = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'lists' AND activity = 1 ORDER BY title, seq");
    while ($lrow = sqlFetchArray($lres)) {
        echo "<option value='" . attr($lrow['option_id']) . "'";
        echo ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
    }
    echo "</select>\n";
    echo "<br /><a href='#' onclick='export_submit(102.1)'>" . xlt('Download CSV') . "</a>";

    // Multi-select for layouts.
    echo "</td><td valign='top'>\n";
    echo "<strong>" . xlt('Layouts') . "</strong><br />\n";
    echo "<select class='form-control' multiple name='form_sel_layouts[]' size='15'>";
    $lres = sqlStatement("SELECT grp_form_id, grp_title FROM layout_group_properties WHERE " .
      "grp_group_id = '' AND grp_activity = 1 ORDER BY grp_form_id");
    while ($lrow = sqlFetchArray($lres)) {
        $key = $lrow['grp_form_id'];
        echo "<option value='" . attr($key) . "'";
        echo ">" . text($key) . ": " . text(xl_layout_label($lrow['grp_title'])) . "</option>\n";
    }
    echo "</select>\n";
    echo "<br /><a href='#' onclick='export_submit(102.2)'>" . xlt('Download CSV') . "</a>";
    echo "</td>\n</tr>\n</table>\n";

    // Option to auto-export lists referenced by the chosen layouts.
    echo "&nbsp;<br /><input type='checkbox' name='form_cb_addlists' value='1' />\n";
    echo " " . xlt('Include all lists referenced in chosen layouts') . "<br />\n";

    echo "<br /><input class='btn btn-primary' type='submit' onclick='export_submit(102)' value='" . xla('Continue') . "' />\n";
}

if ($form_step == 102) {
    $tables = '';
    if (!empty($_POST['form_cb_services'  ])) {
        $tables .= ' codes';
    }

    if (!empty($_POST['form_cb_products'  ])) {
        $tables .= ' drugs drug_templates';
    }

    if (!empty($_POST['form_cb_prices'    ])) {
        $tables .= ' prices';
    }

    if (!empty($_POST['form_cb_categories'])) {
        $tables .= ' categories categories_seq';
    }

    if (!empty($_POST['form_cb_feesheet'  ])) {
        $tables .= ' fee_sheet_options';
    }

    if (!empty($_POST['form_cb_lab_config'])) {
        $tables .= ' procedure_type procedure_providers procedure_questions';
    }

    if (!empty($_POST['form_cb_lang'      ])) {
        $tables .= ' lang_languages lang_constants lang_definitions';
    }

    if ($tables || is_array($_POST['form_sel_lists'] ?? '') || is_array($_POST['form_sel_layouts'] ?? '')) {
        $form_status .= xl('Creating export file') . "...||br-placeholder||";
        echo brCustomPlaceholder(text($form_status));
        if (file_exists($EXPORT_FILE)) {
            if (! unlink($EXPORT_FILE)) {
                die(xlt("Couldn't remove old export file: ") . text($EXPORT_FILE));
            }
        }

        // The substitutions below use perl because sed's not usually on windows systems.
        $perl = $PERL_PATH . DIRECTORY_SEPARATOR . 'perl';

        # This condition was added because the windows operating system uses different syntax for the shell commands.
        # The test is if it is the windows operating system.
        if (IS_WINDOWS) {
            # This section sets the character_set_client to utf8 in the sql file as part or the import property.
            # windows will place the quotes in the outputted code if they are there. we removed them here.
            $cmd = "echo SET character_set_client = utf8; > " . escapeshellarg($EXPORT_FILE) . " & ";
        } else {
            $cmd = "echo 'SET character_set_client = utf8;' > " . escapeshellarg($EXPORT_FILE) . ";";
        }

        if ($tables) {
            if (IS_WINDOWS) {
                $cmd .= escapeshellcmd('"' . $mysql_dump_cmd . '"') . " -u " . escapeshellarg($sqlconf["login"]) .
                    " -p" . escapeshellarg($sqlconf["pass"]) .
                    " -h " . escapeshellarg($sqlconf["host"]) .
                    " --port=" . escapeshellarg($sqlconf["port"]) .
                    " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
                    " --hex-blob --opt --quote-names --skip-comments --no-tablespaces $mysql_ssl " .
                    escapeshellarg($sqlconf["dbase"]) . " $tables";
            } else {
                $cmd .= escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
                    " -p" . escapeshellarg($sqlconf["pass"]) .
                    " -h " . escapeshellarg($sqlconf["host"]) .
                    " --port=" . escapeshellarg($sqlconf["port"]) .
                    " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
                    " --hex-blob --opt --quote-names --skip-comments --no-tablespaces $mysql_ssl " .
                    escapeshellarg($sqlconf["dbase"]) . " $tables";
            }
            if (IS_WINDOWS) {
                # The Perl script differs in windows also.
                $cmd .= " | " . escapeshellcmd('"' . $perl . '"') . " -pe \"s/ DEFAULT CHARSET=[A-Za-z0-9]*//i; s/ collate[ =][^ ;,]*//i;\"" .
                    " >> " . escapeshellarg($EXPORT_FILE) . " & ";
            } else {
                $cmd .= " | " . escapeshellcmd($perl) . " -pe 's/ DEFAULT CHARSET=[A-Za-z0-9]*//i; s/ collate[ =][^ ;,]*//i;'" .
                    " > " . escapeshellarg($EXPORT_FILE) . ";";
            }
        }

        $dumppfx = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
                 " -p" . escapeshellarg($sqlconf["pass"]) .
                 " -h " . escapeshellarg($sqlconf["host"]) .
                 " --port=" . escapeshellarg($sqlconf["port"]) .
                 " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
                 " --hex-blob --skip-opt --quote-names --no-tablespaces --complete-insert" .
                 " --no-create-info --skip-comments $mysql_ssl";

        // Individual lists.
        $form_sel_lists = is_array($_POST['form_sel_lists'] ?? '') ? $_POST['form_sel_lists'] : array();
        if (!empty($_POST['form_cb_addlists']) && is_array($_POST['form_sel_layouts'] ?? '')) {
            // Include all lists referenced by the exported layouts.
            foreach ($_POST['form_sel_layouts'] as $layoutid) {
                $tmpres = sqlStatement(
                    "SELECT a.list_id FROM layout_options AS a " .
                    "JOIN list_options AS i ON i.list_id = 'lists' AND i.option_id = a.list_id AND " .
                    "i.activity = 1 AND i.option_value = 0 " .
                    "WHERE a.form_id = ? AND a.list_id != '' AND a.uor > 0",
                    array($layoutid)
                );
                while ($tmprow = sqlFetchArray($tmpres)) {
                    if (!in_array($tmprow['list_id'], $form_sel_lists) && !in_array($tmprow['list_id'], $excluded_lists)) {
                        $form_sel_lists[] = $tmprow['list_id'];
                    }
                }
            }
        }
        if (!empty($form_sel_lists)) {
            foreach ($form_sel_lists as $listid) {
                // skip if have backtic(s)
                if (strpos($listid, '`') !== false) {
                    echo xlt("Skipping illegal list name") . ": " . text($listid) . "<br>";
                    continue;
                }
                // whitelist the $listid
                $listid_check = sqlQuery("SELECT `list_id` FROM `list_options` WHERE `list_id` = ? OR `option_id` = ?", [$listid, $listid]);
                if (empty($listid_check['list_id'])) {
                    echo xlt("Skipping missing list name") . ": " . text($listid) . "<br>";
                    continue;
                }
                if (IS_WINDOWS) {
                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                    $cmd .= " echo 'DELETE FROM list_options WHERE list_id = \"" . add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . " & ";
                    $cmd .= " echo 'DELETE FROM list_options WHERE list_id = 'lists' AND option_id = \"" . add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . " & ";
                    # windows uses the & to join statements.
                    $cmd .= $dumppfx . " --where=\"list_id = 'lists' AND option_id = '$listid' OR list_id = '$listid' " .
                        "ORDER BY list_id != 'lists', seq, title\" " .
                        escapeshellarg($sqlconf["dbase"]) . " list_options";
                    $cmd .=  " >> " . escapeshellarg($EXPORT_FILE) . " & ";
                } else {
                    $cmdarr[] = "echo 'DELETE FROM list_options WHERE list_id = \"" .
                        add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";" .
                        "echo 'DELETE FROM list_options WHERE list_id = \"lists\" AND option_id = \"" .
                        add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";" .
                        $dumppfx . " --where='list_id = \"lists\" AND option_id = \"" .
                        add_escape_custom($listid) . "\" OR list_id = \"" .
                        add_escape_custom($listid) . "\" " . "ORDER BY list_id != \"lists\", seq, title' " .
                        escapeshellarg($sqlconf["dbase"]) . " list_options" .
                        " >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
            }
        }

        // Individual layouts.
        if (is_array($_POST['form_sel_layouts'] ?? '')) {
            $do_history_repair = false;
            $do_demographics_repair = false;
            foreach ($_POST['form_sel_layouts'] as $layoutid) {
                // skip if have backtic(s)
                if (strpos($layoutid, '`') !== false) {
                    echo xlt("Skipping illegal layout name") . ": " . text($layoutid) . "<br>";
                    continue;
                }
                // whitelist the $layoutid
                $layoutid_check_one = sqlQuery("SELECT `form_id` FROM `layout_options` WHERE `form_id` = ?", [$layoutid]);
                $layoutid_check_two = sqlQuery("SELECT `grp_form_id` FROM `layout_group_properties` WHERE `grp_form_id` = ?", [$layoutid]);
                if (empty($layoutid_check_one['list_id']) && empty($layoutid_check_two['grp_form_id'])) {
                    echo xlt("Skipping missing layout name") . ": " . text($layoutid) . "<br>";
                    continue;
                }
                // Beware and keep in mind that Windows requires double quotes around arguments.
                if (IS_WINDOWS) {
                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                    $cmd .= " echo 'DELETE FROM layout_options WHERE form_id = \"" . add_escape_custom($layoutid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . " & ";
                } else {
                    $cmd .= "echo 'DELETE FROM layout_options WHERE form_id = \"" . add_escape_custom($layoutid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
                if (IS_WINDOWS) {
                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                    $cmd .= "echo 'DELETE FROM layout_group_properties WHERE grp_form_id = \"" . add_escape_custom($layoutid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . " &;";
                } else {
                    $cmd .= "echo 'DELETE FROM layout_group_properties WHERE grp_form_id = \"" . add_escape_custom($layoutid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
                if (IS_WINDOWS) {
                    # windows uses the & to join statements.
                    $cmd .= $dumppfx . ' --where="grp_form_id = \'' . add_escape_custom($layoutid) . "'\" " .
                        escapeshellarg($sqlconf["dbase"]) . " layout_group_properties";
                    $cmd .= " >> " . escapeshellarg($EXPORT_FILE) . " & ";
                    $cmd .= $dumppfx . ' --where="form_id = \'' . add_escape_custom($layoutid) . '\' ORDER BY group_id, seq, title" '  .
                        escapeshellarg($sqlconf["dbase"]) . " layout_options" ;
                    $cmd .= " >> " . escapeshellarg($EXPORT_FILE) . " & ";
                } else {
                    $cmd .= $dumppfx . " --where='grp_form_id = \"" . add_escape_custom($layoutid) . "\"' " .
                        escapeshellarg($sqlconf["dbase"]) . " layout_group_properties";
                    $cmd .= " >> " . escapeshellarg($EXPORT_FILE) . ";";
                    $cmd .= $dumppfx . " --where='form_id = \"" . add_escape_custom($layoutid) . "\" ORDER BY group_id, seq, title' " .
                        escapeshellarg($sqlconf["dbase"]) . " layout_options" ;
                    $cmd .= " >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
                // History and demographics exports will get special treatment.
                if (substr($layoutid, 0, 3) == 'HIS') {
                    $do_history_repair = true;
                }
                if (substr($layoutid, 0, 3) == 'DEM') {
                    $do_demographics_repair = true;
                }
            }
            // If any HIS* layouts were exported then also write SQL to add missing history_data columns.
            if ($do_history_repair) {
                $cmd .= "echo \"SET sql_mode = '';\"                  >> $EXPORT_FILE;";
                $cmd .= "echo \"SET group_concat_max_len = 1000000;\" >> $EXPORT_FILE;";
                $cmd .= "echo \"SELECT CONCAT(\"                      >> $EXPORT_FILE;";
                $cmd .= "echo \"'ALTER TABLE history_data ',\"        >> $EXPORT_FILE;";
                $cmd .= "echo \"COALESCE(GROUP_CONCAT(DISTINCT ' ADD \`', lo.field_id, '\` TEXT NOT NULL' ORDER BY lo.field_id), '')\" >> $EXPORT_FILE;";
                $cmd .= "echo \")\"                                   >> $EXPORT_FILE;";
                $cmd .= "echo \"FROM layout_options AS lo WHERE\"     >> $EXPORT_FILE;";
                $cmd .= "echo \"(lo.form_id LIKE 'HIS%' OR lo.source = 'H') AND lo.field_id NOT IN\" >> $EXPORT_FILE;";
                $cmd .= "echo \"(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = 'history_data')\" >> $EXPORT_FILE;";
                $cmd .= "echo \"INTO @sql;\"                          >> $EXPORT_FILE;";
                $cmd .= "echo \"PREPARE stmt FROM @sql;\"             >> $EXPORT_FILE;";
                $cmd .= "echo \"EXECUTE stmt;\"                       >> $EXPORT_FILE;";
            }
            // If the DEM layout was exported then also write SQL to add missing patient_data columns.
            if ($do_demographics_repair) {
                $cmd .= "echo \"SET sql_mode = '';\"                  >> $EXPORT_FILE;";
                $cmd .= "echo \"SET group_concat_max_len = 1000000;\" >> $EXPORT_FILE;";
                $cmd .= "echo \"SELECT CONCAT(\"                      >> $EXPORT_FILE;";
                $cmd .= "echo \"'ALTER TABLE patient_data ',\"        >> $EXPORT_FILE;";
                $cmd .= "echo \"COALESCE(GROUP_CONCAT(DISTINCT ' ADD \`', lo.field_id, '\` TEXT NOT NULL' ORDER BY lo.field_id), '')\" >> $EXPORT_FILE;";
                $cmd .= "echo \")\"                                   >> $EXPORT_FILE;";
                $cmd .= "echo \"FROM layout_options AS lo WHERE\"     >> $EXPORT_FILE;";
                $cmd .= "echo \"(lo.form_id LIKE 'DEM%' OR lo.source = 'D') AND lo.field_id NOT IN\" >> $EXPORT_FILE;";
                $cmd .= "echo \"(SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME = 'patient_data')\" >> $EXPORT_FILE;";
                $cmd .= "echo \"INTO @sql;\"                          >> $EXPORT_FILE;";
                $cmd .= "echo \"PREPARE stmt FROM @sql;\"             >> $EXPORT_FILE;";
                $cmd .= "echo \"EXECUTE stmt;\"                       >> $EXPORT_FILE;";
            }
        }
    } else {
        echo xlt('No items were selected!');
        $form_step = -1;
    }

    $auto_continue = true;
}

if ($form_step == 103) {
    $form_status .= xl('Done.  Will now send download.') . "||br-placeholder||";
    echo brCustomPlaceholder(text($form_status));
    $auto_continue = true;
}

if ($form_step == 201) {
    echo xlt('WARNING: This will overwrite configuration information with data from the uploaded file!') . " \n";
    echo xlt('Use this feature only with newly installed sites, ');
    echo xlt('otherwise you will destroy references to/from existing data.') . "\n";
    echo "<br />&nbsp;<br />\n";
    echo xlt('File to upload') . ":\n";
    echo "<input type='hidden' name='MAX_FILE_SIZE' value='32000000' />\n";
    echo "<input type='file' name='userfile' /><br />&nbsp;<br />\n";
    echo "<input class='btn btn-primary' type='submit' value='" . xla('Continue') . "' />\n";
}

if ($form_step == 202) {
  // Process uploaded config file.
    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $EXPORT_FILE)) {
            $form_status .= xl('Applying') . "...||br-placeholder||";
            echo brCustomPlaceholder(text($form_status));
            $cmd = escapeshellcmd($mysql_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
            " -p" . escapeshellarg($sqlconf["pass"]) .
            " -h " . escapeshellarg($sqlconf["host"]) .
            " --port=" . escapeshellarg($sqlconf["port"]) .
            " $mysql_ssl " .
            escapeshellarg($sqlconf["dbase"]) .
            " < " . escapeshellarg($EXPORT_FILE);
        } else {
            echo xlt('Internal error accessing uploaded file!');
            $form_step = -1;
        }
    } else {
        echo xlt('Upload failed!');
        $form_step = -1;
    }

    $auto_continue = true;
}

if ($form_step == 203) {
    $form_status .= xl('Done') . ".";
    echo brCustomPlaceholder(text($form_status));
}

/// ViSolve : EventLog Backup
if ($form_step == 301) {
# Get the Current Timestamp, to attach with the log backup file
    $backuptime = date("Ymd_His");
# Eventlog backup directory
    $BACKUP_EVENTLOG_DIR = $GLOBALS['backup_log_dir'];

# Check if Eventlog Backup directory exists, if not create it with Write permission
    if (!file_exists($BACKUP_EVENTLOG_DIR)) {
        mkdir($BACKUP_EVENTLOG_DIR);
        chmod($BACKUP_EVENTLOG_DIR, 0777);
    }

# Frame the Eventlog Backup File Name
    $BACKUP_EVENTLOG_FILE = $BACKUP_EVENTLOG_DIR . '/eventlog_' . $backuptime . '.sql';
# Create a new table similar to event table, rename the existing table as backup table, and rename the new table to event log table.  Then export the contents of the table into a text file and drop the table.
    $res = sqlStatement("create table if not exists log_comment_encrypt_new like log_comment_encrypt");
    $res = sqlStatement("rename table log_comment_encrypt to log_comment_encrypt_backup,log_comment_encrypt_new to log_comment_encrypt");
    $res = sqlStatement("create table if not exists log_new like log");
    $res = sqlStatement("rename table log to log_backup,log_new to log");
    $res = sqlStatement("create table if not exists api_log_new like api_log");
    $res = sqlStatement("rename table api_log to api_log_backup, api_log_new to api_log");
    echo "<br />";
    $cmd = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
    " -p" . escapeshellarg($sqlconf["pass"]) .
    " -h " . escapeshellarg($sqlconf["host"]) .
    " --port=" . escapeshellarg($sqlconf["port"]) .
    " --ignore-table=" . escapeshellarg($sqlconf["dbase"] . ".onsite_activity_view") .
    " --hex-blob --opt --quote-names --no-tablespaces -r " . escapeshellarg($BACKUP_EVENTLOG_FILE) . " $mysql_ssl " .
    escapeshellarg($sqlconf["dbase"]) . " --tables log_comment_encrypt_backup log_backup api_log_backup";
# Set Eventlog Flag when it is done
    $eventlog = 1;
// 301 If ends here.
}

if ($form_step == 401) {
    echo "<p><b>&nbsp;" . xlt('Download or Delete Old Log Entries') . ":</b></p>";
    $tmprow = sqlQuery("SELECT COUNT(*) AS count, MIN(date) AS date FROM log");
    echo "<p>&nbsp;" . xlt('The log has') . ' ' . $tmprow['count'] . ' '  .
        xlt('entries with the oldest dated') . ' ' . $tmprow['date'] . ".</p>";
    // Default end date is end of year 2 years ago, ensuring 1 full year of log remaining.
    $end_date = (date('Y') - 2) . '-12-31';
    if (!empty($_POST['form_end_date'])) {
        $end_date = DateToYYYYMMDD($_POST['form_end_date']);
    }
    echo "<p>&nbsp;" . xlt('Select an end date. Entries after this date will not be downloaded or deleted.') . " ";
    echo "<input type='text' class='datepicker' name='form_end_date' id='form_end_date' size='10' " .
        "value='" . attr(oeFormatShortDate($end_date)) . "' " .
        "onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd' />";
    echo "</p>\n";
    echo "<p><input type='button' onclick='export_submit(402)' value='" . xla('Download Log Entries as Zipped CSV') . "' />&nbsp;\n";
    echo "<input type='button' onclick='export_submit(405)' value='" . xla('Delete Log Entries') . "' /></p>\n";
}

if ($form_step == 405) {
    // Process log delete, then optimize to reclaim the file space.
    if (!empty($_POST['form_end_date'])) {
        $end_date = DateToYYYYMMDD($_POST['form_end_date']);
        sqlStatement(
            "DELETE log, lce, al FROM log " .
            "LEFT JOIN log_comment_encrypt AS lce ON lce.log_id = log.id " .
            "LEFT JOIN api_log AS al ON al.log_id = log.id " .
            "WHERE log.date <= ?",
            array("$end_date 23:59:59")
        );
        sqlStatement("OPTIMIZE TABLE log");
    } else {
        die(xlt("End date is missing!"));
    }
    $form_step = -1;
    $auto_continue = true;
}

++$form_step;
?>

  </td>
 </tr>
</table>

<input type='hidden' name='form_step' value='<?php echo attr($form_step); ?>' />
<input type='hidden' name='form_status' value='<?php echo attr($form_status); ?>' />

</form>

<?php
ob_flush();
flush();
if ($cmd) {
    $tmp0 = exec($cmd, $tmp1, $tmp2);

    if ($tmp2) {
        if ($eventlog == 1) {
          // ViSolve : Restore previous state, if backup fails.
             $res = sqlStatement("drop table if exists log_comment_encrypt");
             $res = sqlStatement("rename table log_comment_encrypt_backup to log_comment_encrypt");
             $res = sqlStatement("drop table if exists log");
             $res = sqlStatement("rename table log_backup to log");
             $res = sqlStatement("drop table if exists api_log");
             $res = sqlStatement("rename table api_log_backup to api_log");
        }
        //Removed the connection details as it exposes all the database credentials

        die("There was an error on the backup");
    }

  //  ViSolve:  If the Eventlog is set, then clear the temporary table  -- Start here
    if ($eventlog == 1) {
        $res = sqlStatement("drop table if exists log_backup");
        $res = sqlStatement("drop table if exists log_comment_encrypt_backup");
        $res = sqlStatement("drop table if exists api_log_backup");
        echo "<br /><b>";
        echo xlt('Backup Successfully taken in') . " ";
        echo text($BACKUP_EVENTLOG_DIR);
        echo "</b>";
    }

 //  ViSolve:  If the Eventlog is set, then clear the temporary table  -- Ends here
}

// $cmdarr exists because some commands may be too long for a single exec.
// Note eventlog stuff does not apply here.
foreach ($cmdarr as $acmd) {
    $tmp0 = exec($acmd, $tmp1, $tmp2);
    if ($tmp2) {
        die("Error $tmp2 in: " . text($acmd));
    }
}

// If a file was flagged to be gzip-compressed after this cmd, do it.
if ($file_to_compress) {
    if (!gz_compress_file($file_to_compress)) {
        die(xlt("Error in gzip compression of file: ") . text($file_to_compress));
    }
}
?>

</center>

<?php if ($auto_continue) { ?>
<script>
    setTimeout("document.forms[0].submit();", 500);
</script>
<?php }

// convert ||br-placeholder|| to <br>
// (this is because the nl2br was not working for a reason I couldn't figure out)
function brCustomPlaceholder(string $str): string
{
    return str_replace("||br-placeholder||", "<br />", $str);
}

// Recursive directory remove (like an O/S insensitive "rm -rf dirname")
function obliterate_dir($dir)
{
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir) || is_link($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!obliterate_dir($dir . DIRECTORY_SEPARATOR . $item)) {
            chmod($dir . DIRECTORY_SEPARATOR . $item, 0777);
            if (!obliterate_dir($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        };
    }

    return rmdir($dir);
}

// Create a tar archive given the archive file name, compression method if any, and the
// array of file/directory names to archive
function create_tar_archive($archiveName, $compressMethod, $itemArray)
{
    // Create a tar object using the pear library
    $tar = new Archive_Tar($archiveName, $compressMethod);
    if ($tar->create($itemArray)) {
        return true;
    }

    return false;
}

// Compress a file using gzip. Source file removed, leaving only the compressed
// *.gz file, just like gzip command line would behave.
function gz_compress_file($source)
{
    $dest = $source . '.gz';
    $error = false;
    if ($fp_in = fopen($source, 'rb')) {
        if ($fp_out = gzopen($dest, 'wb')) {
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 1024 * 512));
            }

            gzclose($fp_out);
            fclose($fp_in);
            unlink($source);
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }

    if ($error) {
        return false;
    } else {
        return $dest;
    }
}
?>

</body>
</html>
