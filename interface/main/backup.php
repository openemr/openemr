<?php
/* $Id$ */
// Copyright (C) 2008-2014, 2016 Rod Roark <rod@sunsetsystems.com>
// Adapted for cross-platform operation by Bill Cernansky (www.mi-squared.com)
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This script creates a backup tarball and sends it to the users's
// browser for download.  The tarball includes:
//
// * an OpenEMR database dump (gzipped)
// * a phpGACL database dump (gzipped), if phpGACL is used and has
//   its own database
// * the OpenEMR web directory (.tar.gz)
// * the phpGACL web directory (.tar.gz), if phpGACL is used
//
// The OpenEMR web directory is important because it includes config-
// uration files, patient documents, and possible customizations, and
// also because the database structure is dependent on the installed
// OpenEMR version.
//
// This script depends on execution of some external programs:
// mysqldump & pg_dump.  It has been tested with Debian and Ubuntu
// Linux and with Windows XP.
// Do not assume that it works for you until you have successfully
// tested a restore!
use OpenEMR\Core\Header;

set_time_limit(0);
require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/log.inc");

if (!extension_loaded('zlib')) {
      die('Abort '.basename(__FILE__).' : Missing zlib extensions');
}

if (!function_exists('gzopen') && function_exists('gzopen64')) {
    function gzopen($filename, $mode, $use_include_path = 0)
    {
        return gzopen64($filename, $mode, $use_include_path);
    }
}
    
    
if (!acl_check('admin', 'super')) {
    die(xl('Not authorized', '', '', '!'));
}

include_once("Archive/Tar.php");

// Set up method, which will depend on OS and if pear tar.php is installed
if (class_exists('Archive_Tar')) {
 # pear tar.php is installed so can use os independent method
    $newBackupMethod = true;
} elseif (IS_WINDOWS) {
 # without the tar.php module, can't run backup in windows
    die(xl("Error. You need to install the Archive/Tar.php php module."));
} else {
 # without the tar.php module, can run via system commands in non-windows
    $newBackupMethod = false;
}
//variables used
$BTN_TEXT_CREATE = xla('Create Full Backup');
$BTN_TEXT_EXPORT = xla('Export Configuration');
$BTN_TEXT_IMPORT = xla('Import Configuration');
$create_download_full_backup = "&nbsp;". xlt('Create and download a full backup');
$download_configuration_data = "&nbsp;". xlt('Download configuration data');
$upload_configuration_data = "&nbsp;". xlt('Upload configuration data');
$create_eventlog_backup = "&nbsp;". xlt('Create Eventlog Backup');
$continue = xlt('Continue');
$tables = xlt('Tables');
$services = xlt('Services');
$products = xlt('Products');
$prices = xlt('Prices');
$document_categories = xlt('Document Categories');
$fee_sheet_options = xlt('Fee Sheet Options');
$translations = xlt('Translations');
$lists = xlt('Lists');
$layouts = xlt('Layouts');
$done = xlt('Done.  Will now send download.');
$warning = xlt('WARNING: This will overwrite configuration information with data from the uploaded file');
$use_feature = xlt('Use this feature only with newly installed sites, otherwise you will destroy references to/from existing data');
$click_browse_and_select_one_configuration_file = xlt('Click Browse and select one configuration file usually named') . " openemr_config.sql";
$browse = xlt('Browse'); 
$success = "&nbsp;<i class='fa fa-check-circle text-success' aria-hidden='true'></i>&nbsp;";
$failure = "&nbsp;<i class='a fa fa-times-circle text-danger' aria-hidden='true'></i>&nbsp;";


// rViSolve: Create Log  Backup button
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
    readfile($TAR_FILE_PATH);
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

if ($form_step == 0) {
    $legend_text = xl('Select type of backup');
} elseif ($form_step == 1) {
    $legend_text = xl('Backing up the data');
} elseif ($form_step == 2 &&(!empty($phpgacl_location) && $gacl_object->_db_name != $sqlconf["dbase"])) {
    $legend_text = xl('Backing the phpGACL database');
} elseif ($form_step == 4 || $form_step == 103 || $form_step == 203 ) {
    $legend_text = xl('Success') . " !!";
}elseif ($form_step == 3 || ($form_step > 1 && $form_step < 6) ) {
    $legend_text = xl('Backing the openEMR website');
} elseif ($form_step == 4 && (!empty($phpgacl_location)) && ($phpgacl_location != $srcdir."/../gacl")) {
    $legend_text = xl('Backing the phpGACL web directory');
}  elseif ($form_step == 101) {
    $legend_text = xl('Select the configuration items to export');
} elseif ($form_step == 103) {
    $legend_text = xl('Exported items sent to download');
} elseif ($form_step == 201) {
    $legend_text = xl('Uploading configuration data');
} elseif ($form_step == 301) {
    $legend_text = xl('Backing up the log file table');
} else {
    $legend_text = xl('Select type of backup');
}

 // echo "<pre> <br>";
// var_dump($GLOBALS);
 // echo "WEBSERVER ROOT: " . $webserver_root . "<br>";
 // echo "WEBROOT: ". substr($GLOBALS["web_root"], 1) . "<br>";
 // echo "WEBROOT: ". substr($web_root, 1) . "<br>";
//echo "<pre> <br>";
?>
<html>

<head>
<?php Header::setupHeader();?>

<title><?php xl('Backup', 'e'); ?></title>
<style>
    .oe-small{
       font-size:0.8em;
    }
    .oe-checkbox{
        margin-top:10px !Important;
    }
    @media only screen and (max-width: 1024px) {
        [class*="col-"] {
          width: 100%;
          text-align:left!Important;
        }
    }
</style>
</head>

<body class="body_top">
<div class="container">
<div class='row'>
    <div class='col-xs-12'>
         <div class='page-header clearfix'>
           <h2 class='clearfix'><span id='header_text'><?php echo xlt('Backup'); ?></span>  &nbsp;<a href="backup.php" onclick="top.restoreSession()"><i class="fa fa-undo fa-2x small" aria-hidden="true" title="Back to backup start"></i></a> <a class='pull-right' data-target='#myModal' data-toggle='modal' href='#' id='help-href' name='help-href' style='color:#000000'><i class='fa fa-question-circle' aria-hidden='true'></i></a></h2>
        </div>
    </div>
</div>
<div class='row'>
    
    <div class='col-xs-12'>
        <form  method='post' action='backup.php' enctype='multipart/form-data' style="display:inline">
        
            <fieldset>
                <legend><?php echo $legend_text; ?></legend>
                <?php
                $cmd = '';
                $mysql_cmd = $MYSQL_PATH . DIRECTORY_SEPARATOR . 'mysql';
                $mysql_dump_cmd = $mysql_cmd . 'dump';
                $file_to_compress = '';  // if named, this iteration's file will be gzipped after it is created
                $eventlog=0;  // Eventlog Flag
                if ($form_step == 0) {
                  
               $div = <<<EOF
                <div class='col-xs-12'> 
                    <div class='form-group col-xs-12'> 
                    <button type='submit' id='form_create' name='form_create' class='btn btn-default btn-save' value='$BTN_TEXT_CREATE' />$BTN_TEXT_CREATE</button>
                       <label  for='form_create'>$create_download_full_backup </label>
                    </div>
                </div>
EOF;
echo $div; 
                // The config import/export feature is optional.
                if (!empty($GLOBALS['configuration_import_export'])) {
                    $div = <<<EOF
                <div class='col-xs-12'>
                    <div class='form-group col-xs-12'>
                       <button type='submit' name='form_export' class='btn btn-default btn-transmit' value='$BTN_TEXT_EXPORT'>$BTN_TEXT_EXPORT</button>
                       <label  for='form_create'>$download_configuration_data</label>
                    </div>
                </div>
                <div class='col-xs-12'>
                    <div class='form-group col-xs-12'>
                       <button type='submit' name='form_import' class='btn btn-default btn-receive' value='$BTN_TEXT_IMPORT'>$BTN_TEXT_IMPORT</button>
                       <label  for='form_create'>$upload_configuration_data</label>
                    </div>
                </div>
EOF;
echo $div;                 
                }
                $div = <<<EOF
                <div class='col-xs-12'>
                    <div class='form-group col-xs-12'>
                        <button type='submit' id='form_backup' name='form_backup' class='btn btn-default btn-save' value='$BTN_TEXT_CREATE_EVENTLOG'>$BTN_TEXT_CREATE_EVENTLOG</button>
                        <label class='' for="form_backup">$create_eventlog_backup</label>
                    </div>
                </div>
EOF;
echo $div;              
               } 
               if ($form_step == 1) {
                    $form_status .= "&nbsp;&nbsp;". xl('Dumping OpenEMR database') . "...<br />";
                    echo nl2br($form_status);
                    if (file_exists($TAR_FILE_PATH)) {
                        if (! unlink($TAR_FILE_PATH)) {
                            die($failure . xl("Couldn't remove old backup file:") . " " . $TAR_FILE_PATH);
                        }
                    }

                    if (! obliterate_dir($TMP_BASE)) {
                        die($failure . xl("Couldn't remove dir:"). " " . $TMP_BASE);
                    }

                    if (! mkdir($BACKUP_DIR, 0777, true)) {
                        die($failure . xl("Couldn't create backup dir:") . " " . $BACKUP_DIR);
                    }

                    //$file_to_compress = "$BACKUP_DIR/openemr.sql";   // gzip this file after creation
                     $file_to_compress = "$BACKUP_DIR/".$sqlconf["dbase"].".sql";   // gzip this file after creation

                    if ($GLOBALS['include_de_identification']==1) {
                        //include routines during backup when de-identification is enabled
                        $cmd = "$mysql_dump_cmd -u " . escapeshellarg($sqlconf["login"]) .
                        " -p" . escapeshellarg($sqlconf["pass"]) .
                        " -h " . escapeshellarg($sqlconf["host"]) .
                        " --port=".escapeshellarg($sqlconf["port"]) .
                        " --routines".
                        " --opt --quote-names -r $file_to_compress " .
                        escapeshellarg($sqlconf["dbase"]);
                    } else {
                        $cmd = "$mysql_dump_cmd -u " . escapeshellarg($sqlconf["login"]) .
                        " -p" . escapeshellarg($sqlconf["pass"]) .
                        " -h " . escapeshellarg($sqlconf["host"]) .
                        " --port=".escapeshellarg($sqlconf["port"]) .
                        " --opt --quote-names -r $file_to_compress " .
                        escapeshellarg($sqlconf["dbase"]);
                    }

                    $auto_continue = true;
                }
                if ($form_step == 2) {
                    if (!empty($phpgacl_location) && $gacl_object->_db_name != $sqlconf["dbase"]) {
                        $form_status .= "&nbsp;&nbsp;" . xl('Dumping phpGACL database') . "...<br />";
                        echo nl2br($form_status);
                        $file_to_compress = "$BACKUP_DIR/phpgacl.sql";   // gzip this file after creation
                        $cmd = "$mysql_dump_cmd -u " . escapeshellarg($gacl_object->_db_user) .
                        " -p" . escapeshellarg($gacl_object->_db_password) .
                        " --opt --quote-names -r $file_to_compress " .
                        escapeshellarg($gacl_object->_db_name);
                        $auto_continue = true;
                    } else {
                        ++$form_step;
                    }
                }
                if ($form_step == 3) {
                    $form_status .="&nbsp;&nbsp;". xl('Dumping OpenEMR web directory tree') . "...<br />";
                    echo nl2br($form_status);
                    $cur_dir = getcwd();
                    chdir($webserver_root);

                  // Select the files and directories to archive.  Basically everything
                  // except site-specific data for other sites.
                    $file_list = array();
                    $dh = opendir($webserver_root);
                    if (!$dh) {
                        die($failure .  "Cannot read directory '$webserver_root'.");
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
                    $web_root_name = substr($web_root, 1);
                    //$arch_file = $BACKUP_DIR . DIRECTORY_SEPARATOR . "openemr.tar.gz";
                    $arch_file = $BACKUP_DIR . DIRECTORY_SEPARATOR . $web_root_name .".tar.gz";
                    if (!create_tar_archive($arch_file, "gz", $file_list)) {
                        die($failure .  xl("An error occurred while dumping OpenEMR web directory tree"));
                    }
                    
                    chdir($cur_dir);
                    $auto_continue = true;
                }

                if ($form_step == 4) {
                    if ((!empty($phpgacl_location)) && ($phpgacl_location != $srcdir."/../gacl")) {
                        $form_status .= xl('Dumping phpGACL web directory tree') . "...<br />";
                        echo nl2br($form_status);
                        $cur_dir = getcwd();
                        chdir($phpgacl_location);
                        $file_list = array('.');    // archive entire directory
                        $arch_file = $BACKUP_DIR . DIRECTORY_SEPARATOR . "phpgacl.tar.gz";
                        if (!create_tar_archive($arch_file, "gz", $file_list)) {
                            die($failure . xl("An error occurred while dumping phpGACL web directory tree"));
                        }

                        chdir($cur_dir);
                        $auto_continue = true;
                    } else {
                        ++$form_step;
                    }
                }

                if ($form_step == 5) {   // create the final compressed tar containing all files
                    $form_status .= "&nbsp;&nbsp;". xl('Backup file has been created. Starting to download') . "<br />";
                    echo nl2br($form_status);
                    
                    $cur_dir = getcwd();
                    chdir($BACKUP_DIR);
                    $file_list = array('.');
                    
                    // Creates a openemr_setup file containing setup details
                    $myfile = fopen("openemr_setup.txt", "w") or die ($failure . xl("Unable to open file!"));
                    $txt = "ORIGINAL WEBSITE AND DATABASE DETAILS"."\n";
                    fwrite($myfile, $txt);
                    $txt = "WEBSERVER_ROOT:". $GLOBALS['webserver_root'] ."\n";
                    fwrite($myfile, $txt);
                    $txt = "WEB_ROOT_NAME:". substr($GLOBALS["web_root"], 1) ."\n";
                    fwrite($myfile, $txt);
                    $txt = "SITE_ID:" . $_SESSION['site_id'] ."\n";
                    fwrite($myfile, $txt);
                    $txt = "DB_NAME:" . $GLOBALS['dbase'] ."\n";
                    fwrite($myfile, $txt);
                    $txt = "DB_USER:" . $GLOBALS['login'] ."\n";
                    fwrite($myfile, $txt);
                    $txt = "DB_PWD:". $GLOBALS['pass'] ."\n";
                    fwrite($myfile, $txt);
                    $txt = "DB_PORT:" . $GLOBALS['port'] ."\n";
                    fwrite($myfile, $txt);
                    fclose($myfile);
                    
                    //copy the restore shell script
                    
                    $file = "$webserver_root/contrib/util/restore"; $newfile = "restore";
                    copy($file,$newfile);
                                      
                    if (!create_tar_archive($TAR_FILE_PATH, '', $file_list)) {
                        die($failure . xl("Error: Unable to create downloadable archive"));
                    }
                    
                    chdir($cur_dir);
                   /* To log the backup event */
                    if ($GLOBALS['audit_events_backup']) {
                        newEvent("backup", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "Backup is completed");
                    }
                    echo nl2br($success . xl('Successfully backed up the website and database, please wait for the compressed emr_backup.tar archive to finish downloading') . "<br />");
                    $auto_continue = true;
                }
                if ($form_step == 101) {
                $div = <<<EOF
                <div class='col-xs-12'>
                    <div class='col-xs-12'>
                        <button type='submit' value= '$continue' class='btn btn-default btn-transmit'>$continue</button>
                    </div>
                </div>
                <div class='col-xs-12'>
                    <div class='col-xs-4'>
                        <h4 class='head clearfix ' style='padding:5px 10px'>$tables  <i id='tables-tooltip' class='fa fa-info-circle text-primary h5 oe-small' title='' aria-hidden='true' data-original-title=''></i><input type='checkbox'  id='sel_tables_checkbox' class='pull-right'  style='margin-top:10px !Important' value='1'></h4>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_services' value='1'>$services</label>
                        </div>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_products' value='1'>$products</label>
                        </div>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_prices' value='1'>$prices</label>
                        </div>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_categories' value='1'>$document_categories</label>
                        </div>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_feesheet' value='1'>$fee_sheet_options</label>
                        </div>
                        <div class='checkbox'>
                          <label><input type='checkbox' name='form_cb_lang' value='1'>$translations</label>
                        </div>
                    </div>
                    <div class='col-xs-4'>
                        <h4 class='head clearfix ' style='padding:5px 10px'>$lists  <i id='lists-tooltip' class='fa fa-info-circle text-primary h5 oe-small' title='' aria-hidden='true' data-original-title=''></i><input type='checkbox'  id='sel_lists_checkbox' class='pull-right'  style='margin-top:10px !Important' value='1'></h4>
                        <select multiple id='sel_lists' name='form_sel_lists[]'  style='width:100%;' size='15'>
EOF;
echo $div;
                        $lres = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
                                "list_id = 'lists' AND activity = 1 ORDER BY title, seq");
                                while ($lrow = sqlFetchArray($lres)) {
                                    echo "<option value='" . attr($lrow['option_id']) . "'";
                                    echo ">" . text(xl_list_label($lrow['title'])) . "</option>\n";
                                } 
                $div = <<<EOF
                        </select>
                    </div>
                    <div class='col-xs-4'>
                        <h4 class='head clearfix ' style='padding:5px 10px'>$layouts  <i id='layouts-tooltip' class='fa fa-info-circle text-primary h5 oe-small' title='' aria-hidden='true' data-original-title=''></i><input type='checkbox'  id='sel_layouts_checkbox' class='pull-right'  style='margin-top:10px !Important' value='1'></h4>
                        <select multiple id='sel_layouts' name='form_sel_layouts[]' style='width:100%' size='15'>
EOF;
echo $div;
                        $lres = sqlStatement("SELECT grp_form_id, grp_title FROM layout_group_properties WHERE " .
                              "grp_group_id = '' AND grp_activity = 1 ORDER BY grp_form_id");
                            while ($lrow = sqlFetchArray($lres)) {
                                $key = $lrow['grp_form_id'];
                                echo "<option value='" . attr($key) . "'";
                                echo ">" . text($key) . ": " . text(xl_layout_label($lrow['grp_title'])) . "</option>\n";
                            }
                        
                $div = <<<EOF
                        </select>
                    </div>
                </div>
EOF;
echo $div;
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
                        $form_status .= "&nbsp;&nbsp;". xl('Creating export file') . "...<br />";
                        echo nl2br($form_status);
                        if (file_exists($EXPORT_FILE)) {
                            if (! unlink($EXPORT_FILE)) {
                                die($failure . xl("Couldn't remove old export file: ") . $EXPORT_FILE);
                            }
                        }

                        // The substitutions below use perl because sed's not usually on windows systems.
                        $perl = $PERL_PATH . DIRECTORY_SEPARATOR . 'perl';


                        # This condition was added because the windows operating system uses different syntax for the shell commands.
                        # The test is if it is the windows operating system.
                        if (IS_WINDOWS) {
                            # This section sets the character_set_client to utf8 in the sql file as part or the import property.
                            # windows will place the quotes in the outputted code if they are there. we removed them here.
                            $cmd = "echo SET character_set_client = utf8; > $EXPORT_FILE & ";
                        } else {
                            $cmd = "echo 'SET character_set_client = utf8;' > $EXPORT_FILE;";
                        }

                        if ($tables) {
                            $cmd .= "$mysql_dump_cmd -u " . escapeshellarg($sqlconf["login"]) .
                                " -p" . escapeshellarg($sqlconf["pass"]) .
                                " -h " . escapeshellarg($sqlconf["host"]) .
                                " --port=".escapeshellarg($sqlconf["port"]) .
                                " --opt --quote-names " .
                                escapeshellarg($sqlconf["dbase"]) . " $tables";
                            if (IS_WINDOWS) {
                              # The Perl script differs in windows also.
                                $cmd .= " | $perl -pe \"s/ DEFAULT CHARSET=utf8//i; s/ collate[ =][^ ;,]*//i;\"" .
                                " >> $EXPORT_FILE & ";
                            } else {
                                $cmd .= " | $perl -pe 's/ DEFAULT CHARSET=utf8//i; s/ collate[ =][^ ;,]*//i;'" .
                                " > $EXPORT_FILE;";
                            }
                        }

                        $dumppfx = "$mysql_dump_cmd -u " . escapeshellarg($sqlconf["login"]) .
                                 " -p" . escapeshellarg($sqlconf["pass"]) .
                                 " -h " . escapeshellarg($sqlconf["host"]) .
                                 " --port=".escapeshellarg($sqlconf["port"]) .
                                 " --skip-opt --quote-names --complete-insert --no-create-info";
                        // Individual lists.
                        if (is_array($_POST['form_sel_lists'])) {
                            foreach ($_POST['form_sel_lists'] as $listid) {
                                if (IS_WINDOWS) {
                                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                                    $cmd .= " echo DELETE FROM list_options WHERE list_id = '$listid'; >> $EXPORT_FILE & ";
                                    $cmd .= " echo DELETE FROM list_options WHERE list_id = 'lists' AND option_id = '$listid'; >> $EXPORT_FILE & ";
                                } else {
                                    $cmd .= "echo \"DELETE FROM list_options WHERE list_id = '$listid';\" >> $EXPORT_FILE;";
                                    $cmd .= "echo \"DELETE FROM list_options WHERE list_id = 'lists' AND option_id = '$listid';\" >> $EXPORT_FILE;";
                                }
                                $cmd .= $dumppfx .
                                " --where=\"list_id = 'lists' AND option_id = '$listid' OR list_id = '$listid' " .
                                "ORDER BY list_id != 'lists', seq, title\" " .
                                escapeshellarg($sqlconf["dbase"]) . " list_options";
                                if (IS_WINDOWS) {
                                  # windows uses the & to join statements.
                                    $cmd .=  " >> $EXPORT_FILE & ";
                                } else {
                                    $cmd .=  " >> $EXPORT_FILE;";
                                }
                            }
                        }

                        // Individual layouts.
                        if (is_array($_POST['form_sel_layouts'])) {
                            foreach ($_POST['form_sel_layouts'] as $layoutid) {
                                if (IS_WINDOWS) {
                                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                                    $cmd .= " echo DELETE FROM layout_options WHERE form_id = '$layoutid'; >> $EXPORT_FILE & ";
                                } else {
                                    $cmd .= "echo \"DELETE FROM layout_options WHERE form_id = '$layoutid';\" >> $EXPORT_FILE;";
                                }
                                if (IS_WINDOWS) {
                                    # windows will place the quotes in the outputted code if they are there. we removed them here.
                                    $cmd .= "echo \"DELETE FROM layout_group_properties WHERE grp_form_id = '$layoutid';\" >> $EXPORT_FILE &;";
                                } else {
                                    $cmd .= "echo \"DELETE FROM layout_group_properties WHERE grp_form_id = '$layoutid';\" >> $EXPORT_FILE;";
                                }
                                $cmd .= $dumppfx .
                                    " --where=\"grp_form_id = '$layoutid'\" " .
                                    escapeshellarg($sqlconf["dbase"]) . " layout_group_properties";
                                if (IS_WINDOWS) {
                                    # windows uses the & to join statements.
                                    $cmd .= " >> $EXPORT_FILE & ";
                                } else {
                                    $cmd .= " >> $EXPORT_FILE;";
                                }
                                $cmd .= $dumppfx .
                                " --where=\"form_id = '$layoutid' ORDER BY group_id, seq, title\" " .
                                escapeshellarg($sqlconf["dbase"]) . " layout_options" ;
                                if (IS_WINDOWS) {
                                    # windows uses the & to join statements.
                                    $cmd .=  " >> $EXPORT_FILE & ";
                                } else {
                                    $cmd .=  " >> $EXPORT_FILE;";
                                }
                            }
                        }
                    } else {
                        echo $failure . xl('No items were selected!');
                        $form_step = -1;
                    }

                    $auto_continue = true;
                }

                if ($form_step == 103) {
                    $form_status .= "&nbsp;&nbsp;". xl('Done.  Will now send download.') . "<br />";
                    echo nl2br($form_status);
                    echo nl2br($success . xl('Successfully backed selected configuration data, please wait for the openemr_config.sql file to finish downloading') . "<br />");
                    $auto_continue = true;
                }

                if ($form_step == 201) {
                $div= <<<EOF
                <div class='col-xs-12'>
                    <div class='col-xs-11 col-xs-offset-1'> <p><i class='fa fa-exclamation-triangle' style='color:red' aria-hidden='true'></i> <strong>$warning</strong>
                        <p><i class="fa fa-exclamation-triangle" style="color:red" aria-hidden="true"></i> <strong>$use_feature</strong></p>
                    </div>
                    <div class='col-xs-12'>
                    <div class='form-group'>
                        
                        <div class='input-group'> 
                            <label class='input-group-btn'>
                                <span class="btn btn-default">
                                    $browse &hellip;<input type="file" id="userfile" name="userfile" style="display: none;" >
                                    <input name="MAX_FILE_SIZE" type="hidden" value="5000000"> 
                                </span>
                                
                            </label>
                            <input type='text' id='selected-config' class='form-control' placeholder='$click_browse_and_select_one_configuration_file ...' readonly=''>
                        </div>
                    </div>
                    </div>    
                </div>
                <div class='col-xs-12'>
                    <div class='col-xs-12'>
                        <br>
                        <button type='submit' value= '$continue' class='btn btn-default btn-transmit'>$continue</button>
                    </div>
                </div>
EOF;
echo $div; 
                }

                if ($form_step == 202) {
                  // Process uploaded config file.
                    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $EXPORT_FILE)) {
                            $form_status .= "&nbsp;&nbsp;". xl('Applying') . "...<br />";
                            echo nl2br($form_status);
                            $cmd = "$mysql_cmd -u " . escapeshellarg($sqlconf["login"]) .
                            " -p" . escapeshellarg($sqlconf["pass"]) .
                            " -h " . escapeshellarg($sqlconf["host"]) .
                            " --port=".escapeshellarg($sqlconf["port"]) .
                            " " .
                            escapeshellarg($sqlconf["dbase"]) .
                            " < $EXPORT_FILE";
                        } else {
                            echo xl('Internal error accessing uploaded file!');
                            $form_step = -1;
                        }
                    } else {
                        echo $failure . xl('Upload failed!');
                        $form_step = -1;
                    }

                    $auto_continue = true;
                }

                if ($form_step == 203) {
                    $form_status .= "&nbsp;&nbsp;" . xl('Done') . "." . "<br />";
                    echo nl2br($form_status);
                    echo nl2br($success . xl('Successfully imported and applied the configuration changes to the OpenEMR database') . "<br />");
                }

                /// ViSolve : EventLog Backup
                if ($form_step == 301) {
                # Get the Current Timestamp, to attach with the log backup file
                    $backuptime=date("Ymd_His");
                # Eventlog backup directory
                    $BACKUP_EVENTLOG_DIR = $GLOBALS['backup_log_dir'] . "/emr_eventlog_backup";

                # Check if Eventlog Backup directory exists, if not create it with Write permission
                    if (!file_exists($BACKUP_EVENTLOG_DIR)) {
                        mkdir($BACKUP_EVENTLOG_DIR);
                        chmod($BACKUP_EVENTLOG_DIR, 0777);
                    }

                # Frame the Eventlog Backup File Name
                    $BACKUP_EVENTLOG_FILE=$BACKUP_EVENTLOG_DIR.'/eventlog_'.$backuptime.'.sql';
                # Create a new table similar to event table, rename the existing table as backup table, and rename the new table to event log table.  Then export the contents of the table into a text file and drop the table.
                    $res=sqlStatement("create table if not exists log_comment_encrypt_new like log_comment_encrypt");
                    $res=sqlStatement("rename table log_comment_encrypt to log_comment_encrypt_backup,log_comment_encrypt_new to log_comment_encrypt");
                    $res=sqlStatement("create table if not exists log_new like log");
                    $res=sqlStatement("rename table log to log_backup,log_new to log");
                    $res=sqlStatement("create table if not exists log_validator_new like log_validator");
                    $res=sqlStatement("rename table log_validator to log_validator_backup, log_validator_new to log_validator");
                    echo "<br>";
                    $cmd = "$mysql_dump_cmd -u " . escapeshellarg($sqlconf["login"]) .
                    " -p" . escapeshellarg($sqlconf["pass"]) .
                    " -h " . escapeshellarg($sqlconf["host"]) .
                    " --port=".escapeshellarg($sqlconf["port"]) .
                    " --opt --quote-names -r $BACKUP_EVENTLOG_FILE " .
                    escapeshellarg($sqlconf["dbase"]) ." --tables log_comment_encrypt_backup log_backup log_validator_backup";
                # Set Eventlog Flag when it is done
                    $eventlog=1;
                // 301 If ends here.
                }

                ++$form_step;
?>               
            </fieldset>
         <input type='hidden' name='form_step' value='<?php echo $form_step; ?>' />
        <input type='hidden' name='form_status' value='<?php echo $form_status; ?>' />   
        </form> 
    </div>
</div>

<?php
ob_flush();
flush();
if ($cmd) {
    $tmp0 = exec($cmd, $tmp1, $tmp2);

    if ($tmp2) {
        if ($eventlog==1) {
          // ViSolve : Restore previous state, if backup fails.
             $res=sqlStatement("drop table if exists log_comment_encrypt");
             $res=sqlStatement("rename table log_comment_encrypt_backup to log_comment_encrypt");
             $res=sqlStatement("drop table if exists log");
             $res=sqlStatement("rename table log_backup to log");
             $res=sqlStatement("drop table if exists log_validator");
             $res=sqlStatement("rename table log_validator_backup to log_validator");
        }

        die($failure . "\"$cmd\" returned $tmp2: $tmp0");
    }

  //  ViSolve:  If the Eventlog is set, then clear the temporary table  -- Start here
    if ($eventlog==1) {
        $res=sqlStatement("drop table if exists log_backup");
        $res=sqlStatement("drop table if exists log_comment_encrypt_backup");
        $res=sqlStatement("drop table if exists log_validator_backup");
        echo "<fieldset>";
        echo "<br>";
        echo $success . xl('Event log successfully backed up in')." ";
        echo  $BACKUP_EVENTLOG_DIR;
        echo "</fieldset> <br>";
    }

 //  ViSolve:  If the Eventlog is set, then clear the temporary table  -- Ends here
}

// If a file was flagged to be gzip-compressed after this cmd, do it.
if ($file_to_compress) {
    if (!gz_compress_file($file_to_compress)) {
        die($failure . xl("Error in gzip compression of file: ") . $file_to_compress);
    }
}
?>

</div><!--end of container-div-->
<div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content oe-modal-content">
                    <div class="modal-header clearfix"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button></div>
                    <div class="modal-body">
                        <iframe src="" id="targetiframe" style="height:75%; width:100%; overflow-x: hidden; border:none" allowtransparency="true"></iframe>  
                    </div>
                    <div class="modal-footer" style="margin-top:0px;">
                       <button class="btn btn-link btn-cancel pull-right" data-dismiss="modal" type="button"><?php echo xlt('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $('#help-href').click (function(){
                document.getElementById('targetiframe').src ='backup_help.php';
            })
       
        });
    </script>

<?php if ($auto_continue) { ?>
<script language="JavaScript">
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
    global $newBackupMethod;
    
    if ($newBackupMethod) {
       // Create a tar object using the pear library
       //  (this is the preferred method)
        $tar = new Archive_Tar($archiveName, $compressMethod);
        if ($tar->create($itemArray)) {
            return true;
        }
    } else {
       // Create the tar files via command line tools
       //  (this method used when the tar pear library is not available)
        $files = '"' . implode('" "', $itemArray) . '"';
        if ($compressMethod == "gz") {
            $command = "tar --same-owner --ignore-failed-read -zcphf $archiveName $files";
        } else {
            $command = "tar -cpf $archiveName $files";
        }

        $temp0 = exec($command, $temp1, $temp2);
        if ($temp2) {
            die("\"$command\" returned $temp2: $temp0");
        }

        return true;
    }

    return false;
}

// Compress a file using gzip. Source file removed, leaving only the compressed
// *.gz file, just like gzip command line would behave.
function gz_compress_file($source)
{
    $dest=$source.'.gz';
    $error=false;
    if ($fp_in=fopen($source, 'rb')) {
        if ($fp_out=gzopen($dest, 'wb')) {
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 1024*512));
            }

            gzclose($fp_out);
            fclose($fp_in);
            unlink($source);
        } else {
            $error=true;
        }
    } else {
        $error=true;
    }

    if ($error) {
        return false;
    } else {
        return $dest;
    }
}
?>
 <script>
        $(function() {
            //https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
            // We can attach the `fileselect` event to all file inputs on the page
            $(document).on('change', ':file', function() {
                var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
                input.trigger('fileselect', [numFiles, label]);
            });

            // We can watch for our custom `fileselect` event like this
            $(document).ready( function() {
                $(':file').on('fileselect', function(event, numFiles, label) {
                    var input = $(this).parents('.input-group').find(':text'),
                    log = numFiles > 1 ? numFiles + ' files selected' : label;
                    
                    if( input.length ) {
                    input.val(log);
                    } 
                    else {
                    if( log ) alert(log);
                    }
                });
            });

            });
         </script>
        <script>
        $(document).ready(function(){
            $('#sel_tables_checkbox').click(function() {
                if ($('#sel_tables_checkbox').prop("checked")) {
                    $("input:checkbox[name*='form_cb']").prop("checked", true)
                } else {
                    $("input:checkbox[name*='form_cb']").prop("checked", false)
                }
            });
             
            $('#sel_lists_checkbox').click(function() {
                if ($('#sel_lists_checkbox').prop("checked")) {
                    $('#sel_lists option').prop('selected', true);
                } else {
                    $('#sel_lists option').prop('selected', false);
                }
            });
            $('#sel_layouts_checkbox').click(function() {
                if ($('#sel_layouts_checkbox').prop("checked")) {
                    $('#sel_layouts option').prop('selected', true);
                } else {
                    $('#sel_layouts option').prop('selected', false);
                }
            });
        });
    </script>
   <script>
       $(document).ready(function(){
            $('#tables-tooltip').tooltip({title: "<?php echo xla('Export the selected tables from the database as a sql file'); ?>"});
            $('#lists-tooltip').tooltip({title: "<?php echo xla('Export the selected options from the table \'list_options\' in the database as a sql file. Useful to when replicating customized entires in a new install'); ?>"}); 
            $('#layouts-tooltip').tooltip({title: "<?php echo xla('Export the selected options from the table \'layout_options\' in the database as a sql file. Useful to when replicating customized Layout Based Forms in a new install'); ?>"}); 
        });
</script>

</body>
</html>
