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
 * @copyright Copyright (c) 2008-2014, 2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

set_time_limit(0);
require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
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
    die(xlt('Not authorized'));
}

$BTN_TEXT_CREATE = xl('Create Backup');
$BTN_TEXT_EXPORT = xl('Export Configuration');
$BTN_TEXT_IMPORT = xl('Import Configuration');
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

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

# set up main paths
$backup_file_prefix = "emr_backup";
$backup_file_suffix = ".tar";
$TMP_BASE = $GLOBALS['temporary_files_dir'] . "/openemr_web_backup";
$BACKUP_DIR = $TMP_BASE . "/emr_backup";
$TAR_FILE_PATH = $TMP_BASE . DIRECTORY_SEPARATOR . $backup_file_prefix . $backup_file_suffix;
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
$MYSQL_PATH = $GLOBALS['mysql_bin_dir'];
$PERL_PATH = $GLOBALS['perl_bin_dir'];

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
?>
<html>

<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Backup'); ?></title>
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
    $form_status .= xla('Dumping OpenEMR database') . "...<br />";
    echo nl2br($form_status);
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
        " --hex-blob --opt --quote-names -r " . escapeshellarg($file_to_compress) . " $mysql_ssl " .
        escapeshellarg($sqlconf["dbase"]);
    } else {
        $cmd = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
        " -p" . escapeshellarg($sqlconf["pass"]) .
        " -h " . escapeshellarg($sqlconf["host"]) .
        " --port=" . escapeshellarg($sqlconf["port"]) .
        " --hex-blob --opt --quote-names -r " . escapeshellarg($file_to_compress) . " $mysql_ssl " .
        escapeshellarg($sqlconf["dbase"]);
    }

    $auto_continue = true;
}

if ($form_step == 2) {
    ++$form_step;
}

if ($form_step == 3) {
    $form_status .= xla('Dumping OpenEMR web directory tree') . "...<br />";
    echo nl2br($form_status);
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
    $form_status .= xla('Backup file has been created. Will now send download.') . "<br />";
    echo nl2br($form_status);
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

    echo "</td>\n</tr>\n</table>\n";
    echo "&nbsp;<br /><input class='btn btn-primary' type='submit' value='" . xla('Continue') . "' />\n";
}

if ($form_step == 102) {
    $tables = '';
    if ($_POST['form_cb_services'  ]) {
        $tables .= ' codes';
    }

    if ($_POST['form_cb_products'  ]) {
        $tables .= ' drugs drug_templates';
    }

    if ($_POST['form_cb_prices'    ]) {
        $tables .= ' prices';
    }

    if ($_POST['form_cb_categories']) {
        $tables .= ' categories categories_seq';
    }

    if ($_POST['form_cb_feesheet'  ]) {
        $tables .= ' fee_sheet_options';
    }

    if ($_POST['form_cb_lang'      ]) {
        $tables .= ' lang_languages lang_constants lang_definitions';
    }

    if ($tables || is_array($_POST['form_sel_lists']) || is_array($_POST['form_sel_layouts'])) {
        $form_status .= xla('Creating export file') . "...<br />";
        echo nl2br($form_status);
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
                    " --hex-blob --opt --quote-names $mysql_ssl " .
                    escapeshellarg($sqlconf["dbase"]) . " $tables";
            } else {
                $cmd .= escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
                    " -p" . escapeshellarg($sqlconf["pass"]) .
                    " -h " . escapeshellarg($sqlconf["host"]) .
                    " --port=" . escapeshellarg($sqlconf["port"]) .
                    " --hex-blob --opt --quote-names $mysql_ssl " .
                    escapeshellarg($sqlconf["dbase"]) . " $tables";
            }
            if (IS_WINDOWS) {
                # The Perl script differs in windows also.
                $cmd .= " | " . escapeshellcmd('"' . $perl . '"') . " -pe \"s/ DEFAULT CHARSET=utf8//i; s/ collate[ =][^ ;,]*//i;\"" .
                    " >> " . escapeshellarg($EXPORT_FILE) . " & ";
            } else {
                $cmd .= " | " . escapeshellcmd($perl) . " -pe 's/ DEFAULT CHARSET=utf8//i; s/ collate[ =][^ ;,]*//i;'" .
                    " > " . escapeshellarg($EXPORT_FILE) . ";";
            }
        }

        $dumppfx = escapeshellcmd($mysql_dump_cmd) . " -u " . escapeshellarg($sqlconf["login"]) .
                 " -p" . escapeshellarg($sqlconf["pass"]) .
                 " -h " . escapeshellarg($sqlconf["host"]) .
                 " --port=" . escapeshellarg($sqlconf["port"]) .
                 " --hex-blob --skip-opt --quote-names --complete-insert --no-create-info $mysql_ssl";
        // Individual lists.
        if (is_array($_POST['form_sel_lists'])) {
            foreach ($_POST['form_sel_lists'] as $listid) {
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
                } else {
                    $cmd .= "echo 'DELETE FROM list_options WHERE list_id = \"" . add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";";
                    $cmd .= "echo 'DELETE FROM list_options WHERE list_id = \"lists\" AND option_id = \"" . add_escape_custom($listid) . "\";' >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
                if (IS_WINDOWS) {
                    # windows uses the & to join statements.
                    $cmd .= $dumppfx . " --where=\"list_id = 'lists' AND option_id = '$listid' OR list_id = '$listid' " .
                        "ORDER BY list_id != 'lists', seq, title\" " .
                        escapeshellarg($sqlconf["dbase"]) . " list_options";
                    $cmd .=  " >> " . escapeshellarg($EXPORT_FILE) . " & ";
                } else {
                    $cmd .= $dumppfx . " --where='list_id = \"lists\" AND option_id = \"" .
                        add_escape_custom($listid) . "\" OR list_id = \"" .
                        add_escape_custom($listid) . "\" " . "ORDER BY list_id != \"lists\", seq, title' " .
                        escapeshellarg($sqlconf["dbase"]) . " list_options";
                    $cmd .=  " >> " . escapeshellarg($EXPORT_FILE) . ";";
                }
            }
        }

        // Individual layouts.
        if (is_array($_POST['form_sel_layouts'])) {
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
            }
        }
    } else {
        echo xlt('No items were selected!');
        $form_step = -1;
    }

    $auto_continue = true;
}

if ($form_step == 103) {
    $form_status .= xla('Done.  Will now send download.') . "<br />";
    echo nl2br($form_status);
    $auto_continue = true;
}

if ($form_step == 201) {
    echo xlt('WARNING: This will overwrite configuration information with data from the uploaded file!') . " \n";
    echo xlt('Use this feature only with newly installed sites, ');
    echo xlt('otherwise you will destroy references to/from existing data.') . "\n";
    echo "<br />&nbsp;<br />\n";
    echo xlt('File to upload') . ":\n";
    echo "<input type='hidden' name='MAX_FILE_SIZE' value='4000000' />\n";
    echo "<input type='file' name='userfile' /><br />&nbsp;<br />\n";
    echo "<input class='btn btn-primary' type='submit' value='" . xla('Continue') . "' />\n";
}

if ($form_step == 202) {
  // Process uploaded config file.
    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $EXPORT_FILE)) {
            $form_status .= xla('Applying') . "...<br />";
            echo nl2br($form_status);
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
    $form_status .= xla('Done') . ".";
    echo nl2br($form_status);
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
    " --hex-blob --opt --quote-names -r " . escapeshellarg($BACKUP_EVENTLOG_FILE) . " $mysql_ssl " .
    escapeshellarg($sqlconf["dbase"]) . " --tables log_comment_encrypt_backup log_backup api_log_backup";
# Set Eventlog Flag when it is done
    $eventlog = 1;
// 301 If ends here.
}

++$form_step;
?>

  </td>
 </tr>
</table>

<input type='hidden' name='form_step' value='<?php echo attr($form_step); ?>' />
<input type='hidden' name='form_status' value='<?php echo $form_status; ?>' />

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
