<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
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

if (!acl_check('admin', 'super')) die("Not authorized!");

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

$TMP_BASE = "/tmp/openemr_web_backup";
$BACKUP_DIR = "$TMP_BASE/emr_backup";
$TAR_FILE_PATH = "$TMP_BASE/emr_backup.tar";

if ($form_step > 7) {
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
?>
<html>

<head>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php xl('Backup','e'); ?></title>
</head>

<body class="body_top">
<center>
&nbsp;<br />
<form method='post' action='backup.php'>

<table style='width:30em'>
 <tr>
  <td>

<?php
$cmd = '';

if ($form_step == 0) {
  echo "This will create a backup in tar format and then send it to your " .
    "web browser so you can save it.  Press Continue to proceed.<br />\n";
  echo "&nbsp;<br /><center><input type='submit' value='Continue' /></center>\n";
}
if ($form_step == 1) {
  $form_status .= "Dumping OpenEMR database ...<br />";
  echo nl2br($form_status);
  $cmd = "rm -rf $TMP_BASE; mkdir -p $BACKUP_DIR; " .
    "mysqldump -u " . escapeshellarg($sqlconf["login"]) .
    " -p" . escapeshellarg($sqlconf["pass"]) .
    " --opt --quote-names -r $BACKUP_DIR/openemr.sql " .
    escapeshellarg($sqlconf["dbase"]) .
    "; gzip $BACKUP_DIR/openemr.sql";
}
if ($form_step == 2) {
  if (!empty($phpgacl_location) && $gacl_object->_db_name != $sqlconf["dbase"]) {
    $form_status .= "Dumping phpGACL database ...<br />";
    echo nl2br($form_status);
    $cmd = "mysqldump -u " . escapeshellarg($gacl_object->_db_user) .
      " -p" . escapeshellarg($gacl_object->_db_password) .
      " --opt --quote-names -r $BACKUP_DIR/phpgacl.sql " .
      escapeshellarg($gacl_object->_db_name) .
      "; gzip $BACKUP_DIR/phpgacl.sql";
  }
  else {
    ++$form_step;
  }
}
if ($form_step == 3) {
  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] &&
      $GLOBALS['oer_config']['ws_accounting']['enabled'] !== 2)
  {
    $form_status .= "Dumping SQL-Ledger database ...<br />";
    echo nl2br($form_status);
    $cmd = "PGPASSWORD=" . escapeshellarg($sl_dbpass) . " pg_dump -U " .
      escapeshellarg($sl_dbuser) . " -h localhost --format=c -f " .
      "$BACKUP_DIR/sql-ledger.sql " . escapeshellarg($sl_dbname);
  }
  else {
    ++$form_step;
  }
}
if ($form_step == 4) {
  $form_status .= "Dumping OpenEMR web directory tree ...<br />";
  echo nl2br($form_status);
  $cmd = "cd $webserver_root; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/openemr.tar.gz .";
}
if ($form_step == 5) {
  if (!empty($phpgacl_location)) {
    $form_status .= "Dumping phpGACL web directory tree ...<br />";
    echo nl2br($form_status);
    $cmd = "cd $phpgacl_location; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/phpgacl.tar.gz .";
  }
  else {
    ++$form_step;
  }
}
if ($form_step == 6) {
  if ($GLOBALS['oer_config']['ws_accounting']['enabled'] && is_dir("$webserver_root/../sql-ledger")) {
    $form_status .= "Dumping SQL-Ledger web directory tree ...<br />";
    echo nl2br($form_status);
    $cmd = "cd $webserver_root/../sql-ledger; tar --same-owner --ignore-failed-read -zcphf $BACKUP_DIR/sql-ledger.tar.gz .";
  }
  else {
    ++$form_step;
  }
}
if ($form_step == 7) {
  $form_status .= "Backup file has been created. Will now send download.<br />";
  echo nl2br($form_status);
  $cmd = "cd $BACKUP_DIR; tar -cpf $TAR_FILE_PATH .";
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

<?php if ($form_step > 1) { ?>
<script language="JavaScript">
 setTimeout("document.forms[0].submit();", 500);
</script>
<?php } ?>

</body>
</html>
