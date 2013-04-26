<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module provides for editing site-specific text files and
// for uploading site-specific image files.

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
/* for formData() */
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

if (!acl_check('admin', 'super')) die(htmlspecialchars(xl('Not authorized')));

// Prepare array of names of editable files, relative to the site directory.
$my_files = array(
  'config.php',
  'faxcover.txt',
  'faxtitle.eps',
  'referral_template.html',
  'statement.inc.php',
  'letter_templates/custom_pdf.php',
);
// Append LBF plugin filenames to the array.
$lres = sqlStatement('SELECT * FROM list_options ' .
  "WHERE list_id = 'lbfnames' ORDER BY seq, title");
while ($lrow = sqlFetchArray($lres)) {
  $option_id = $lrow['option_id']; // should start with LBF
  $title = $lrow['title'];
  $my_files[] = "LBF/$option_id.plugin.php";
}

$form_filename = strip_escape_custom($_REQUEST['form_filename']);
// Sanity check to prevent evildoing.
if (!in_array($form_filename, $my_files)) $form_filename = '';
$filepath = "$OE_SITE_DIR/$form_filename";

$imagedir = "$OE_SITE_DIR/images";

if (!empty($_POST['bn_save'])) {
  if ($form_filename) {
    // Textareas, at least in Firefox, return a \r\n at the end of each line
    // even though only \n was originally there.  For consistency with
    // normal OpenEMR usage we translate those back.
    file_put_contents($filepath, str_replace("\r\n", "\n",
      $_POST['form_filedata']));
    $form_filename = '';
  }

  // Handle uploads.
  if (is_uploaded_file($_FILES['form_image']['tmp_name']) && $_FILES['form_image']['size']) {
    $form_dest_filename = $_POST['form_dest_filename'];
    if ($form_dest_filename == '') {
      $form_dest_filename = $_FILES['form_image']['name'];
    }
    $form_dest_filename = basename($form_dest_filename);
    if ($form_dest_filename == '') {
      die(htmlspecialchars(xl('Cannot find a destination filename')));
    }
    $imagepath = "$imagedir/$form_dest_filename";
    // If the site's image directory does not yet exist, create it.
    if (!is_dir($imagedir)) {
      mkdir($imagedir);
    }
    if (is_file($imagepath)) unlink($imagepath);
    $tmp_name = $_FILES['form_image']['tmp_name'];
    if (!move_uploaded_file($_FILES['form_image']['tmp_name'], $imagepath)) {
      die(htmlspecialchars(xl('Unable to create') . " '$imagepath'"));
    }
  }
}
?>
<html>

<head>
<title><?php echo xlt('File management'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>

<script language="JavaScript">
// This is invoked when a filename selection changes in the drop-list.
// In this case anything else entered into the form is discarded.
function msfFileChanged() {
 top.restoreSession();
 document.forms[0].submit();
}
</script>

</head>

<body class="body_top">
<form method='post' action='manage_site_files.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>

<center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'><?php echo htmlspecialchars(xl('Edit File in') . " $OE_SITE_DIR"); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' nowrap>
   <select name='form_filename' onchange='msfFileChanged()'>
    <option value=''></option>
<?php
  foreach ($my_files as $filename) {
    echo "    <option value='" . htmlspecialchars($filename, ENT_QUOTES) . "'";
    if ($filename == $form_filename) echo " selected";
    echo ">" . htmlspecialchars($filename) . "</option>\n";
  }
?>
   </select>
   <br />
   <textarea name='form_filedata' rows='30' style='width:100%'><?php
  if ($form_filename) {
    echo htmlspecialchars(@file_get_contents($filepath));
  }
?></textarea>
  </td>
 </tr>

 <tr bgcolor='#dddddd' class='dehead'>
  <td colspan='2' align='center'><?php echo htmlspecialchars(xl('Upload Image to') . " $imagedir"); ?></td>
 </tr>

 <tr>
  <td valign='top' class='detail' nowrap>
   <?php echo htmlspecialchars(xl('Source File')); ?>:
   <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
   <input type="file" name="form_image" size="40" />&nbsp;
   <?php echo htmlspecialchars(xl('Destination Filename')) ?>:
   <select name='form_dest_filename'>
    <option value=''>(<?php echo htmlspecialchars(xl('Use source filename')) ?>)</option>
<?php
  // Generate an <option> for each file already in the images directory.
  $dh = opendir($imagedir);
  if (!$dh) die(htmlspecialchars(xl('Cannot read directory') . " '$imagedir'"));
  $imagesslist = array();
  while (false !== ($sfname = readdir($dh))) {
    if (substr($sfname, 0, 1) == '.') continue;
    if ($sfname == 'CVS'            ) continue;
    $imageslist[$sfname] = $sfname;
  }
  closedir($dh);
  ksort($imageslist);
  foreach ($imageslist as $sfname) {
    echo "    <option value='" . htmlspecialchars($sfname, ENT_QUOTES) . "'";
    echo ">" . htmlspecialchars($sfname) . "</option>\n";
  }
?>
   </select>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='<?php echo htmlspecialchars(xl('Save')) ?>' />
</p>

</center>

</form>
</body>
</html>

