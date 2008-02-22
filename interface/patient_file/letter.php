<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../globals.php");
include_once("$srcdir/patient.inc");

$template_dir = "$webserver_root/custom/letter_templates";

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB " .
  "FROM patient_data AS p " .
  "WHERE p.pid = '$pid' LIMIT 1");

$alertmsg = ''; // anything here pops up in an alert box

// If the Generate button was clicked...
if ($_POST['form_generate']) {
  $form_pid      = $_POST['form_pid'];
  $form_from     = $_POST['form_from'];
  $form_to       = $_POST['form_to'];
  $form_date     = $_POST['form_date'];
  $form_template = $_POST['form_template'];
  $form_format   = $_POST['form_format'];
  $form_body     = $_POST['form_body'];

  // Set variables that depend on the output format.
  $lang = 'PostScript';
  $mimetype = 'application/postscript';
  $postprocess = '';
  if ($form_format == 'pdf') {
    $mimetype = 'application/pdf';
    $postprocess = ' | ps2pdf - -';
  }
  /****
  else if ($form_format == 'html') {
    $lang = 'html';
    $mimetype = 'text/html';
  }
  else if ($form_format == 'rtf') {
    $lang = 'rtf';
    $mimetype = 'application/rtf';
  }
  ****/

  $frow = sqlQuery("SELECT * FROM users WHERE id = '$form_from'");
  $trow = sqlQuery("SELECT * FROM users WHERE id = '$form_to'");

  ob_start();

  $datestr = date('j F Y', strtotime($form_date));
  $from_title = $frow['title'] ? $frow['title'] . ' ' : '';
  $to_title   = $trow['title'] ? $trow['title'] . ' ' : '';

  // Create the temporary data file and process it with enscript.
  $tmpfn = tempnam("/tmp", "oemr_letter");
  $tmpfh = fopen($tmpfn, "w");
  $cpstring = '';
  $fh = fopen("$template_dir/$form_template", 'r');
  while (!feof($fh)) $cpstring .= fread($fh, 8192);
  fclose($fh);
  $cpstring = str_replace('{DATE}'            , $datestr, $cpstring);
  $cpstring = str_replace('{FROM_TITLE}'      , $from_title, $cpstring);
  $cpstring = str_replace('{FROM_FNAME}'      , $frow['fname'], $cpstring);
  $cpstring = str_replace('{FROM_LNAME}'      , $frow['lname'], $cpstring);
  $cpstring = str_replace('{FROM_MNAME}'      , $frow['mname'], $cpstring);
  $cpstring = str_replace('{FROM_STREET}'     , $frow['street'], $cpstring);
  $cpstring = str_replace('{FROM_CITY}'       , $frow['city'], $cpstring);
  $cpstring = str_replace('{FROM_STATE}'      , $frow['state'], $cpstring);
  $cpstring = str_replace('{FROM_POSTAL}'     , $frow['zip'], $cpstring);
  $cpstring = str_replace('{FROM_VALEDICTORY}', $frow['valedictory'], $cpstring);
  $cpstring = str_replace('{FROM_PHONECELL}'  , $frow['phonecell'], $cpstring);
  $cpstring = str_replace('{TO_TITLE}'        , $to_title, $cpstring);
  $cpstring = str_replace('{TO_FNAME}'        , $trow['fname'], $cpstring);
  $cpstring = str_replace('{TO_LNAME}'        , $trow['lname'], $cpstring);
  $cpstring = str_replace('{TO_MNAME}'        , $trow['mname'], $cpstring);
  $cpstring = str_replace('{TO_STREET}'       , $trow['street'], $cpstring);
  $cpstring = str_replace('{TO_CITY}'         , $trow['city'], $cpstring);
  $cpstring = str_replace('{TO_STATE}'        , $trow['state'], $cpstring);
  $cpstring = str_replace('{TO_POSTAL}'       , $trow['zip'], $cpstring);
  $cpstring = str_replace('{TO_VALEDICTORY}'  , $trow['valedictory'], $cpstring);
  $cpstring = str_replace('{TO_FAX}'          , $trow['fax'], $cpstring);
  $cpstring = str_replace('{TO_ORGANIZATION}' , $trow['organization'], $cpstring);
  $cpstring = str_replace('{PT_FNAME}'        , $patdata['fname'], $cpstring);
  $cpstring = str_replace('{PT_LNAME}'        , $patdata['lname'], $cpstring);
  $cpstring = str_replace('{PT_MNAME}'        , $patdata['mname'], $cpstring);
  $cpstring = str_replace('{PT_DOB}'          , $patdata['DOB'], $cpstring);
  $cpstring = str_replace('{MESSAGE}'         , $form_body, $cpstring);
  fwrite($tmpfh, $cpstring);
  fclose($tmpfh);
  $tmp0 = passthru("cd $template_dir; enscript -M A4 -B -e^ " .
   "--margins=54:54:54:18 --word-wrap -w $lang -o - '$tmpfn'$postprocess");
  unlink($tmpfn);

  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: $mimetype");
  header("Content-Length: " . ob_get_length());
  header("Content-Disposition: inline; filename=letter.$form_format");

  ob_end_flush();

  exit;
}

// This is the case where we display the form for data entry.

// Get the users list.
$ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY lname, fname");
$i = 0;
$optfrom = '';
$optto = '';
$ulist = "var ulist = new Array();\n";
while ($urow = sqlFetchArray($ures)) {
  $uname = $urow['lname'];
  if ($urow['fname']) $uname .= ", " . $urow['fname'];
  $tmp1 = " <option value='" . $urow['id'] . "'";
  $tmp2 = ">$uname</option>\n";
  $optto .= $tmp1 . $tmp2;
  if ($urow['id'] == $_SESSION['authUserID']) $tmp1 .= " selected";
  $optfrom .= $tmp1 . $tmp2;
  $ulist .= "ulist[$i] = '" . addslashes($uname) . "|" .
    $urow['id'] . "|" . addslashes($urow['specialty']) . "';\n";
  ++$i;
}

// Get the unique specialties.
$sres = sqlStatement("SELECT DISTINCT specialty FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY specialty");
$optspec = "<option value='All'>All</option>\n";
while ($srow = sqlFetchArray($sres)) {
  $optspec .= " <option value='" . $srow['specialty'] . "'>" .
    $srow['specialty'] . "</option>\n";
}
?>
<html>
<head>
<? html_header_show();?>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<title><? xl('Letter Generator','e'); ?></title>

<style>
</style>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">
<?php echo $ulist; ?>

 // React to selection of a specialty.  This rebuilds the "to" users list
 // with users having that specialty, or all users if "All" is selected.
 function newspecialty() {
  var f = document.forms[0];
  var s = f.form_specialty.value;
  var theopts = f.form_to.options;
  theopts.length = 0;
  var j = 0;
  for (var i = 0; i < ulist.length; ++i) {
   tmp = ulist[i].split("|");
   if (s != 'All' && s != tmp[2]) continue;
   theopts[j++] = new Option(tmp[0], tmp[1], false, false);
  }
 }

</script>

</head>

<body <?echo $top_bg_line;?> leftmargin='0' topmargin='0' marginwidth='0'
 marginheight='0' onunload='imclosing()'>

<!-- <form method='post' action='letter.php' onsubmit='return top.restoreSession()'> -->
<form method='post' action='letter.php'>

<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>
<p>
<table border='0' cellspacing='8' width='98%'>

 <tr>
  <td colspan='4' align='center'>
   &nbsp;<br>
   <b><?php xl('Generate Letter regarding ','e'); echo $patdata['fname'] . " " .
    $patdata['lname'] . " (" . $patdata['pubpid'] . ")" ?></b>
    <br>&nbsp;
  </td>
 </tr>

 <tr>

  <td>
   <?php xl('From','e'); ?>:
  </td>

  <td>
   <select name='form_from'>
<?php echo $optfrom; ?>
   </select>
  </td>

  <td>
   <?php xl('Date','e'); ?>:
  </td>

  <td>
   <input type='text' size='10' name='form_date' id='form_date'
    value='<?php echo date('Y-m-d'); ?>'
    title='<?php xl('yyyy-mm-dd date of this letter','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
  </td>

 </tr>

 <tr>

  <td>
   <?php xl('Specialty','e'); ?>:
  </td>

  <td>
   <select name='form_specialty' onchange='newspecialty()'>
<?php echo $optspec; ?>
   </select>
  </td>

  <td>
   <?php xl('Template','e'); ?>:
  </td>

  <td>
   <select name='form_template'>
<?php
$tpldir = "$webserver_root/custom/letter_templates";
$dh = opendir($tpldir);
if (! $dh) die("Cannot read $tpldir");
while (false !== ($tfname = readdir($dh))) {
  if (preg_match('/^(.*)\.t[a-z]*$/', $tfname, $matches)) {
    echo " <option value='$tfname'>" . $matches[1] . "</option>\n";
  }
}
closedir($dh);
?>
   </select>
  </td>

 </tr>

 </tr>

 <tr>

  <td>
   <?php xl('To','e'); ?>:
  </td>

  <td>
   <select name='form_to'>
<?php echo $optto; ?>
   </select>
  </td>

  <td>
   <?php xl('Format','e'); ?>:
  </td>

  <td>
   <select name='form_format'>
    <option value='pdf'>PDF</option>
    <option value='ps'>PostScript</option>
   </select>
  </td>

 </tr>

 <tr>
  <td colspan='4'>
   <textarea name='form_body' rows='20' cols='30' style='width:100%'
    title='Enter body of letter here' /></textarea>
  </td>
 </tr>

</table>

<input type='submit' name='form_generate' value='Generate'>

</center>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});
</script>

</body>
</html>
