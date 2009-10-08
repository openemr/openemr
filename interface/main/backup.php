<?php
// Copyright (C) 2008, 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This script creates a backup tarball and sends it to the users's
// browser for download.  The tarball includes:
//
// * an OpenEMR database dump
// * a phpGACL database dump, if phpGACL is used and has its own
//   database
// * a SQL-Ledger database dump, if SQL-Ledger is used
// * the OpenEMR web directory
// * the phpGACL web directory, if phpGACL is used
// * the SQL-Ledger web directory, if SQL-Ledger is used and its
//   web directory exists as a sister of the openemr directory and
//   has the name "sql-ledger" (otherwise we do not have enough
//   information to find it)
//
// The OpenEMR web directory is important because it includes config-
// uration files, patient documents, and possible customizations, and
// also because the database structure is dependent on the installed
// OpenEMR version.
//
// This script depends on execution of some external programs:
// rm, mkdir, mysqldump, pg_dump, tar, gzip.  It has been tested with
// Debian and Ubuntu Linux.  Currently it will not work with Windows.
// Do not assume that it works for you until you have successfully
// tested a restore!

require_once("../globals.php");
require_once("$srcdir/acl.inc");

if (!acl_check('admin', 'super')) die(xl('Not authorized','','','!'));

$BTN_TEXT_CREATE = xl('Create Backup');
$BTN_TEXT_EXPORT = xl('Export Configuration');
$BTN_TEXT_IMPORT = xl('Import Configuration');

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_export'])) $form_step = 101;
if (!empty($_POST['form_import'])) $form_step = 201;

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

$TMP_BASE = "/tmp/openemr_web_backup";
$BACKUP_DIR = "$TMP_BASE/emr_backup";
$TAR_FILE_PATH = "$TMP_BASE/emr_backup.tar";
$EXPORT_FILE = "/tmp/openemr_config.sql";

if ($form_step == 8) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Length: " . filesize($TAR_FILE_PATH));
  header("Content-Disposition: attachment; filename=" . basename($TAR_FILE_PATH));
  header("Content-Description: File Transfer");
  readfile($TAR_FILE_PATH);
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
  exit(0);
}
?>
<html>

<head>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php xl('Backup','e'); ?></title>
</head>

<body class="body_top">
<center>
&nbsp;<br />
<form method='post' action='backup.php' enctype='multipart/form-data'>

<table style='width:30em'>
 <tr>
  <td>

<?php
$cmd = '';

if ($form_step == 0) {
  echo "<table>\n";
  echo " <tr>\n";
  echo "  <td><input type='submit' name='form_create' value='$BTN_TEXT_CREATE' /></td>\n";
  echo "  <td>" . xl('Create and download a full backup') . "</td>\n";
  echo " </tr>\n";
  echo " <tr>\n";
  echo "  <td><input type='submit' name='form_export' value='$BTN_TEXT_EXPORT' /></td>\n";
  echo "  <td>" . xl('Download configuration data') . "</td>\n";
  echo " </tr>\n";
  echo " <tr>\n";
  echo "  <td><input type='submit' name='form_import' value='$BTN_TEXT_IMPORT' /></td>\n";
  echo "  <td>" . xl('Upload configuration data') . "</td>\n";
  echo " </tr>\n";
  echo "</table>\n";
}

if ($form_step == 1) {
  $form_status .= xl('Dumping OpenEMR database') . "...<br />";
  echo nl2br($form_status);
  $cmd = "rm -rf $TMP_BASE; mkdir -p $BACKUP_DIR; " .
    "mysqldump -u " . escapeshellarg($sqlconf["login"]) .
    " -p" . escapeshellarg($sqlconf["pass"]) .
    " --opt --quote-names -r $BACKUP_DIR/openemr.sql " .
    escapeshellarg($sqlconf["dbase"]) .
    "; gzip $BACKUP_DIR/openemr.sql";
  $auto_continue = true;
}

if ($form_step == 2) {
  if (!empty($phpgacl_location) && $gacl_object->_db_name != $sqlconf["dbase"]) {
    $form_status .= xl('Dumping phpGACL database') . "...<br />";
    echo nl2br($form_status);
    $cmd = "mysqldump -u " . escapeshellarg($gacl_object->_db_user) .
      " -p" . escapeshellarg($gacl_object->_db_password) .
      " --opt --quote-names -r $BACKUP_DIR/phpgacl.sql " .
      escapeshellarg($gacl_object->_db_name) .
      "; gzip $BACKUP_DIR/phpgacl.sql";
    $auto_continue = true;
  }
  else {
    ++$form_step;
  }
}

if ($form_step == 3) {
  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] &&
      $GLOBALS['oer_config']['ws_accounting']['enabled'] !== 2)
  {
    $form_status .= xl('Dumping SQL-Ledger database') . "...<br />";
    echo nl2br($form_status);
    $cmd = "PGPASSWORD=" . escapeshellarg($sl_dbpass) . " pg_dump -U " .
      escapeshellarg($sl_dbuser) . " -h localhost --format=c -f " .
      "$BACKUP_DIR/sql-ledger.sql " . escapeshellarg($sl_dbname);
    $auto_continue = true;
  }
  else {
    ++$form_step;
  }
}

if ($form_step == 4) {
  $form_status .= xl('Dumping OpenEMR web directory tree') . "...<br />";
  echo nl2br($form_status);
  $cmd = "cd $webserver_root; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/openemr.tar.gz .";
  $auto_continue = true;
}

if ($form_step == 5) {
  if ((!empty($phpgacl_location)) && ($phpgacl_location != $GLOBALS['fileroot']."/gacl")) {
    $form_status .= xl('Dumping phpGACL web directory tree') . "...<br />";
    echo nl2br($form_status);
    $cmd = "cd $phpgacl_location; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/phpgacl.tar.gz .";
    $auto_continue = true;
  }
  else {
    ++$form_step;
  }
}

if ($form_step == 6) {
  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] &&
    $GLOBALS['oer_config']['ws_accounting']['enabled'] !== 2 &&
    is_dir("$webserver_root/../sql-ledger"))
  {
    $form_status .= xl('Dumping SQL-Ledger web directory tree') . "...<br />";
    echo nl2br($form_status);
    $cmd = "cd $webserver_root/../sql-ledger; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/sql-ledger.tar.gz .";
    $auto_continue = true;
  }
  else {
    ++$form_step;
  }
}

if ($form_step == 7) {
  $form_status .= xl('Backup file has been created. Will now send download.') . "<br />";
  echo nl2br($form_status);
  $cmd = "cd $BACKUP_DIR; tar -cpf $TAR_FILE_PATH .";
  $auto_continue = true;
}

if ($form_step == 101) {
  echo xl('Select the configuration items to export') . ":";
  echo "<br />&nbsp;<br />\n";
  echo "<input type='checkbox' name='form_cb_services' value='1' />\n";
  echo " " . xl('Services') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_products' value='1' />\n";
  echo " " . xl('Products') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_lists' value='1' />\n";
  echo " " . xl('Lists') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_layouts' value='1' />\n";
  echo " " . xl('Layouts') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_prices' value='1' />\n";
  echo " " . xl('Prices') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_categories' value='1' />\n";
  echo " " . xl('Document Categories') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_feesheet' value='1' />\n";
  echo " " . xl('Fee Sheet Options') . "<br />\n";
  echo "<input type='checkbox' name='form_cb_lang' value='1' />\n";
  echo " " . xl('Translations') . "<br />\n";

  echo "&nbsp;<br /><input type='submit' value='" . xl('Continue') . "' />\n";
}

if ($form_step == 102) {
  $tables = '';
  if ($_POST['form_cb_services'  ]) $tables .= ' codes';
  if ($_POST['form_cb_products'  ]) $tables .= ' drugs drug_templates';
  if ($_POST['form_cb_lists'     ]) $tables .= ' list_options';
  if ($_POST['form_cb_layouts'   ]) $tables .= ' layout_options';
  if ($_POST['form_cb_prices'    ]) $tables .= ' prices';
  if ($_POST['form_cb_categories']) $tables .= ' categories categories_seq';
  if ($_POST['form_cb_feesheet'  ]) $tables .= ' fee_sheet_options';
  if ($_POST['form_cb_lang'      ]) $tables .= ' lang_languages lang_constants lang_definitions';
  if ($tables) {
    $form_status .= xl('Creating export file') . "...<br />";
    echo nl2br($form_status);
    $cmd = "rm -f $EXPORT_FILE; " .
      "mysqldump -u " . escapeshellarg($sqlconf["login"]) .
      " -p" . escapeshellarg($sqlconf["pass"]) .
      " --opt --quote-names " .
      escapeshellarg($sqlconf["dbase"]) . " $tables" .
      " | sed 's/ DEFAULT CHARSET=utf8//i'" .
      " | sed 's/ collate[ =][^ ;,]*//i'" .
      " > $EXPORT_FILE;";
  }
  else {
    echo xl('No items were selected!');
    $form_step = -1;
  }
  $auto_continue = true;
}

if ($form_step == 103) {
  $form_status .= xl('Done.  Will now send download.') . "<br />";
  echo nl2br($form_status);
  $auto_continue = true;
}

if ($form_step == 201) {
  echo xl('WARNING: This will overwrite configuration information with data from the uploaded file!') . " \n";
  echo xl('Use this feature only with newly installed sites, ');
  echo xl('otherwise you will destroy references to/from existing data.') . "\n";
  echo "<br />&nbsp;<br />\n";
  echo xl('File to upload') . ":\n";
  echo "<input type='hidden' name='MAX_FILE_SIZE' value='4000000' />\n";
  echo "<input type='file' name='userfile' /><br />&nbsp;<br />\n";
  echo "<input type='submit' value='" . xl('Continue') . "' />\n";
}

if ($form_step == 202) {
  // Process uploaded config file.
  if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $EXPORT_FILE)) {
      $form_status .= xl('Applying') . "...<br />";
      echo nl2br($form_status);
      $cmd = "mysql -u " . escapeshellarg($sqlconf["login"]) .
        " -p" . escapeshellarg($sqlconf["pass"]) . " " .
        escapeshellarg($sqlconf["dbase"]) .
        " < $EXPORT_FILE;";
    }
    else {
      echo xl('Internal error accessing uploaded file!');
      $form_step = -1;
    }
  }
  else {
    echo xl('Upload failed!');
    $form_step = -1;
  }
  $auto_continue = true;
}

if ($form_step == 203) {
  $form_status .= xl('Done') . ".";
  echo nl2br($form_status);
}

++$form_step;
?>

  </td>
 </tr>
</table>

<input type='hidden' name='form_step' value='<?php echo $form_step; ?>' />
<input type='hidden' name='form_status' value='<?php echo $form_status; ?>' />

</form>

<?php
ob_flush();
flush();
if ($cmd) {
  $tmp0 = exec($cmd, $tmp1, $tmp2);
  if ($tmp2) die("\"$cmd\" returned $tmp2: $tmp0");
}
?>

</center>

<?php if ($auto_continue) { ?>
<script language="JavaScript">
 setTimeout("document.forms[0].submit();", 500);
</script>
<?php } ?>

</body>
</html>
