<?php
// Copyright (C) 2007-2011 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// Undo magic quotes and do not allow fake register globals.
$sanitize_all_escapes  = true;
$fake_register_globals = false;

include_once("../globals.php");
include_once($GLOBALS['srcdir'] . "/patient.inc");

$template_dir = $GLOBALS['OE_SITE_DIR'] . "/letter_templates";

// array of field name tags to allow internationalization
//  of templates
$FIELD_TAG = array(
    'DATE'             => xl('DATE'),
    'FROM_TITLE'       => xl('FROM_TITLE'),
    'FROM_FNAME'       => xl('FROM_FNAME'),
    'FROM_LNAME'       => xl('FROM_LNAME'),
    'FROM_MNAME'       => xl('FROM_MNAME'),
    'FROM_STREET'      => xl('FROM_STREET'),
    'FROM_CITY'        => xl('FROM_CITY'),
    'FROM_STATE'       => xl('FROM_STATE'),
    'FROM_POSTAL'      => xl('FROM_POSTAL'),
    'FROM_VALEDICTORY' => xl('FROM_VALEDICTORY'),
    'FROM_PHONE'   => xl('FROM_PHONE'),
 'FROM_PHONECELL'   => xl('FROM_PHONECELL'),
 'FROM_EMAIL'   => xl('FROM_EMAIL'),
    'TO_TITLE'         => xl('TO_TITLE'),
    'TO_FNAME'         => xl('TO_FNAME'),
    'TO_LNAME'         => xl('TO_LNAME'),
    'TO_MNAME'         => xl('TO_MNAME'),
    'TO_STREET'        => xl('TO_STREET'),
    'TO_CITY'          => xl('TO_CITY'),
    'TO_STATE'         => xl('TO_STATE'),
    'TO_POSTAL'        => xl('TO_POSTAL'),
    'TO_VALEDICTORY'   => xl('TO_VALEDICTORY'),
 'TO_PHONE'           => xl('TO_PHONE'),
 'TO_PHONECELL'           => xl('TO_PHONECELL'),
    'TO_FAX'           => xl('TO_FAX'),
    'TO_ORGANIZATION'  => xl('TO_ORGANIZATION'),
    'PT_FNAME'         => xl('PT_FNAME'),
    'PT_LNAME'         => xl('PT_LNAME'),
    'PT_MNAME'         => xl('PT_MNAME'),
    'PT_STREET'        => xl('PT_STREET'),
    'PT_CITY'          => xl('PT_CITY'),
    'PT_STATE'         => xl('PT_STATE'),
    'PT_POSTAL'        => xl('PT_POSTAL'),
    'PT_PHONE_HOME'        => xl('PT_PHONE_HOME'),
    'PT_PHONE_CELL'        => xl('PT_PHONE_CELL'),
'PT_SSN'           => xl('PT_SSN'),
'PT_EMAIL'           => xl('PT_EMAIL'),
    'PT_DOB'           => xl('PT_DOB')
    
);

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.phone_home, p.phone_cell, p.ss, p.email, p.postal_code " .
  "FROM patient_data AS p " .
  "WHERE p.pid = '$pid' LIMIT 1");

$alertmsg = ''; // anything here pops up in an alert box

// If the Generate button was clicked...
if ($_POST['formaction']=="generate") {    
    
    $form_pid      = $_POST['form_pid'];
    $form_from     = $_POST['form_from'];
    $form_to       = $_POST['form_to'];
    $form_date     = $_POST['form_date'];
    $form_template = $_POST['form_template'];
    $form_format   = $_POST['form_format'];
    $form_body     = $_POST['form_body'];

    $frow = sqlQuery("SELECT * FROM users WHERE id = '$form_from'");
    $trow = sqlQuery("SELECT * FROM users WHERE id = '$form_to'");

    $datestr = date('j F Y', strtotime($form_date));
    $from_title = $frow['title'] ? $frow['title'] . ' ' : '';
    $to_title   = $trow['title'] ? $trow['title'] . ' ' : '';

    $cpstring = $_POST['form_body'];

    // attempt to save to the autosaved template
    $fh = fopen("$template_dir/autosaved", 'w');
    // translate from definition to the constant
    $temp_bodytext = $cpstring;
    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{".$value."}", "{".$key."}", $temp_bodytext);
    }
    if (! fwrite($fh, $temp_bodytext)) {
        echo xl('Error while saving to the file','','',' ') . $template_dir."/autosaved" .
             xl('Ensure OpenEMR has write privileges to directory','',' . ',' ') . $template_dir  . "/ ." ;
        die;
    }
    fclose($fh);

    $cpstring = str_replace('{'.$FIELD_TAG['DATE'].'}'            , $datestr, $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_TITLE'].'}'      , $from_title, $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_FNAME'].'}'      , $frow['fname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_LNAME'].'}'      , $frow['lname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_MNAME'].'}'      , $frow['mname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_STREET'].'}'     , $frow['street'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_CITY'].'}'       , $frow['city'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_STATE'].'}'      , $frow['state'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_POSTAL'].'}'     , $frow['zip'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_VALEDICTORY'].'}', $frow['valedictory'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['FROM_PHONECELL'].'}'  , $frow['phonecell'], $cpstring);
 $cpstring = str_replace('{'.$FIELD_TAG['FROM_PHONE'].'}'  , $frow['phone'], $cpstring);
 $cpstring = str_replace('{'.$FIELD_TAG['FROM_EMAIL'].'}'  , $frow['email'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_TITLE'].'}'        , $to_title, $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_FNAME'].'}'        , $trow['fname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_LNAME'].'}'        , $trow['lname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_MNAME'].'}'        , $trow['mname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_STREET'].'}'       , $trow['street'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_CITY'].'}'         , $trow['city'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_STATE'].'}'        , $trow['state'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_POSTAL'].'}'       , $trow['zip'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_VALEDICTORY'].'}'  , $trow['valedictory'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_FAX'].'}'          , $trow['fax'], $cpstring);
  $cpstring = str_replace('{'.$FIELD_TAG['TO_PHONE'].'}'          , $trow['phone'], $cpstring);
  $cpstring = str_replace('{'.$FIELD_TAG['TO_PHONECELL'].'}'          , $trow['phonecell'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['TO_ORGANIZATION'].'}' , $trow['organization'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_FNAME'].'}'        , $patdata['fname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_LNAME'].'}'        , $patdata['lname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_MNAME'].'}'        , $patdata['mname'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_STREET'].'}'       , $patdata['street'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_CITY'].'}'         , $patdata['city'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_STATE'].'}'        , $patdata['state'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_POSTAL'].'}'       , $patdata['postal_code'], $cpstring);
   $cpstring = str_replace('{'.$FIELD_TAG['PT_PHONE_HOME'].'}'        , $patdata['phone_home'], $cpstring);
   $cpstring = str_replace('{'.$FIELD_TAG['PT_PHONE_CELL'].'}'        , $patdata['phone_cell'], $cpstring);
   $cpstring = str_replace('{'.$FIELD_TAG['PT_SSN'].'}'        , $patdata['ss'], $cpstring);
   $cpstring = str_replace('{'.$FIELD_TAG['PT_EMAIL'].'}'        , $patdata['email'], $cpstring);
    $cpstring = str_replace('{'.$FIELD_TAG['PT_DOB'].'}'          , $patdata['DOB'], $cpstring);
    
    if ($form_format == "pdf") {
      // documentation for ezpdf is here --> http://www.ros.co.nz/pdf/
      require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
      $pdf =& new Cezpdf($GLOBALS['rx_paper_size']);
      $pdf->ezSetMargins($GLOBALS['rx_top_margin']
                      ,$GLOBALS['rx_bottom_margin']
                      ,$GLOBALS['rx_left_margin']
                      ,$GLOBALS['rx_right_margin']
                      );
      if (file_exists("$template_dir/custom_pdf.php")) {
        include("$template_dir/custom_pdf.php");
      }
      else {
        $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");
        $pdf->ezText($cpstring, 12); 
      }
      $pdf->ezStream();
      exit;
    }
    else { // $form_format = html
	$cpstring = str_replace("\n", "<br>", $cpstring);
	$cpstring = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $cpstring);
    ?>
        <html>
        <head>
        <style>
        body {
	 font-family: sans-serif;
	 font-weight: normal;
	 font-size: 12pt;
	 background: white;
	 color: black;
	}	
	.paddingdiv {
	 width: 524pt;
	 padding: 0pt;
	 margin-top: 50pt;
	}
	.navigate {
	 margin-top: 2.5em;
	}	
	@media print {
	 .navigate {
	  display: none;
	 }	
	}	
	</style>	
	<title><?php xl('Letter','e'); ?></title>
	</head>
        <body>
	<div class='paddingdiv'>
	<?php echo $cpstring; ?>
        <div class="navigate">
	<a href="<?php echo $GLOBALS['rootdir'] . '/patient_file/letter.php?template=autosaved'; ?>">(<?php xl('Back','e'); ?>)</a>
	</div>
	<script language='JavaScript'>
	window.print();
	</script>
	</body>
	</div>
	<?php
	exit;
    }
}
else if (isset($_GET['template']) && $_GET['template'] != "") {
    // utilized to go back to autosaved template
    $bodytext = "";
    $fh = fopen("$template_dir/".$_GET['template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{".$key."}", "{".$value."}", $bodytext);
    }
}
else if ($_POST['formaction'] == "loadtemplate" && $_POST['form_template'] != "") {
    $bodytext = "";
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{".$key."}", "{".$value."}", $bodytext);	
    } 
}
else if ($_POST['formaction'] == "newtemplate" && $_POST['newtemplatename'] != "") {
    // attempt to save the template
    $fh = fopen("$template_dir/".$_POST['newtemplatename'], 'w');
    // translate from definition to the constant
    $temp_bodytext = $_POST['form_body'];
    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{".$value."}", "{".$key."}", $temp_bodytext);
    }
    if (! fwrite($fh, $temp_bodytext)) {
        echo xl('Error while writing to file','','',' ') . $template_dir."/".$_POST['newtemplatename'];
        die;
    }
    fclose($fh);

    // read the saved file back
    $_POST['form_template'] = $_POST['newtemplatename'];
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{".$key."}", "{".$value."}" , $bodytext);
    }
}
else if ($_POST['formaction'] == "savetemplate" && $_POST['form_template'] != "") {
    // attempt to save the template
    $fh = fopen("$template_dir/".$_POST['form_template'], 'w');
    // translate from definition to the constant
    $temp_bodytext = $_POST['form_body'];
    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{".$value."}", "{".$key."}", $temp_bodytext);
    }
    if (! fwrite($fh, $temp_bodytext)) {
        echo xl('Error while writing to file','','',' ') . $template_dir."/".$_POST['form_template'];
        die;
    }
    fclose($fh);

    // read the saved file back
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{".$key."}", "{".$value."}", $bodytext);
    }
}

// This is the case where we display the form for data entry.
// Get only authorized USERS with "AND authorized = 1" included if left out ALL can bechoosen form
$ures = sqlStatement("SELECT id, fname, lname, specialty FROM users " .
  "WHERE active = 1 AND( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY authorized DESC , lname, fname");
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
$optspec = "<option value='All'>" . xl('All') . "</option>\n";
while ($srow = sqlFetchArray($sres)) {
  $optspec .= " <option value='" . $srow['specialty'] . "'>" .
    $srow['specialty'] . "</option>\n";
}
?>

<html>
<head>
<?php html_header_show();?>
<title><?php xl('Letter Generator','e'); ?></title>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/topdialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

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


// insert text into a textarea where the cursor is
function insertAtCaret(areaId,text) {
    var txtarea = document.getElementById(areaId);
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
            "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;
                                                                            
    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}

function insertAtCursor(myField, myValue) {
    //IE support
    if (document.selection) {
        myField.focus();
        sel = document.selection.createRange();
        sel.text = myValue;
    }
    //MOZILLA/NETSCAPE support
    else if (myField.selectionStart || myField.selectionStart == '0') {
        var startPos = myField.selectionStart;
        var endPos = myField.selectionEnd;
        myField.value = myField.value.substring(0, startPos)
                        + myValue
                        + myField.value.substring(endPos, myField.value.length);
    } else {
        myField.value += myValue;
    }
}


</script>

</head>

<body class="body_top" onunload='imclosing()'>

<!-- <form method='post' action='letter.php' onsubmit='return top.restoreSession()'> -->
<form method='post' action='letter.php' id="theform" name="theform">
<input type="hidden" name="formaction" id="formaction" value="">
<input type='hidden' name='form_pid' value='<?php echo $pid ?>' />

<center>
<p>
<table border='0' cellspacing='8' width='98%'>

 <tr>
  <td colspan='4' align='center'>
   &nbsp;<br>
   <b><?php xl('Generate Letter regarding ','e','',' '); echo $patdata['fname'] . " " .
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
   <select name="form_template" id="form_template">
   <option value="">(<?php xl('none','e'); ?>)</option>
<?php
$tpldir = $GLOBALS['OE_SITE_DIR'] . "/letter_templates";
$dh = opendir($tpldir);
if (! $dh) die(xl('Cannot read','','',' ') . $tpldir);
while (false !== ($tfname = readdir($dh))) {
  // skip dot-files, scripts and images
  if (preg_match("/^\./"   , $tfname)) { continue; }
  if (preg_match("/\.php$/", $tfname)) { continue; }
  if (preg_match("/\.jpg$/", $tfname)) { continue; }
  if (preg_match("/\.png$/", $tfname)) { continue; }
  echo "<option value=".$tfname;
  if (($tfname == $_POST['form_template']) || ($tfname == $_GET['template'])) echo " SELECTED";
  echo ">";
  if ($tfname == 'autosaved') {
    echo xl($tfname);
  }
  else {
    echo $tfname;
  }
  echo "</option>";
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
   <?php xl('Print Format','e'); ?>:
  </td>

  <td>
   <select name='form_format'>
    <option value='html'><?php xl('HTML','e'); ?></option>
    <option value='pdf'><?php xl('PDF','e'); ?></option>
   </select>
  </td>

 </tr>

 <tr>
  <td colspan='4'>
    <div id="letter_toolbar" class='text' style="width: 100%; background-color: #ddd; padding: 5px; margin: 0px;">
    Insert special field:
    <select id="letter_field">
    <option value="">- <?php xl('Choose','e'); ?> -</option>
    <option value="<?php echo '{'.$FIELD_TAG['DATE'].'}'; ?>"><?php xl('Today\'s Date','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_TITLE'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Title','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_FNAME'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('First name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_MNAME'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Middle name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_LNAME'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Last name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_STREET'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Street','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_CITY'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('City','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_STATE'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('State','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_POSTAL'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Postal Code','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_VALEDICTORY'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Valedictory','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['FROM_PHONECELL'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Cell Phone','e'); ?></option>
 <option value="<?php echo '{'.$FIELD_TAG['FROM_PHONE'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('Phone','e'); ?></option>
 <option value="<?php echo '{'.$FIELD_TAG['FROM_EMAIL'].'}'; ?>"><?php xl('FROM','e'); ?> - <?php xl('email','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_TITLE'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Title','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_FNAME'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('First name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_MNAME'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Middle name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_LNAME'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Last name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_STREET'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Street','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_CITY'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('City','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_STATE'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('State','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_POSTAL'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Postal Code','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_VALEDICTORY'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Valedictory','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_ORGANIZATION'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Organization','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_FAX'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Fax number','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_PHONE'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Phone number','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['TO_PHONECELL'].'}'; ?>"><?php xl('TO','e'); ?> - <?php xl('Cell phone number','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_FNAME'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('First name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_MNAME'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Middle name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_LNAME'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Last name','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_STREET'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Street','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_CITY'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('City','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_STATE'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('State','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_POSTAL'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Postal Code','e'); ?></option>
 <option value="<?php echo '{'.$FIELD_TAG['PT_PHONE_HOME'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Phone Home','e'); ?></option>
 <option value="<?php echo '{'.$FIELD_TAG['PT_PHONE_CELL'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Phone Cell','e'); ?></option>
 <option value="<?php echo '{'.$FIELD_TAG['PT_SSN'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('SSN','e'); ?></option>
    <option value="<?php echo '{'.$FIELD_TAG['PT_DOB'].'}'; ?>"><?php xl('PATIENT','e'); ?> - <?php xl('Date of birth','e'); ?></option>
    </select>
    </div>
   <textarea name='form_body' id="form_body" rows='20' cols='30' style='width:100%'
    title=<?php xl('Enter body of letter here','e','\'','\''); ?> /><?php echo $bodytext; ?></textarea>
  </td>
 </tr>

</table>

<input type='button' class="addtemplate" value=<?php xl('Save as New','e','\'','\''); ?>>
<input type='button' name='savetemplate' id="savetemplate" value=<?php xl('Save Changes','e','\'','\''); ?>>
<input type='button' name='form_generate' id="form_generate" value=<?php xl('Generate Letter','e','\'','\''); ?>>

</center>

<!-- template DIV that appears when user chooses to add a new letter template -->
<div id="newtemplatedetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
<?php xl('Template Name','e'); ?>: <input type="textbox" size="20" maxlength="30" name="newtemplatename" id="newtemplatename">
<br>
<input type="button" class="savenewtemplate" value=<?php xl('Save new template','e','\'','\''); ?>>
<input type="button" class="cancelnewtemplate" value=<?php xl('Cancel','e','\'','\''); ?>>
</div>

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $("#form_generate").click(function() { $("#formaction").val("generate"); $("#theform").submit(); });
    $("#form_template").change(function() { $("#formaction").val("loadtemplate"); $("#theform").submit(); });

    $("#savetemplate").click(function() { SaveTemplate(this); });
    
    $("#letter_field").change(function() { insertAtCursor(document.getElementById("form_body"), $(this).val()); $(this).attr("selectedIndex", "0"); });
    
    $(".addtemplate").click(function() { AddTemplate(this); });
    $(".savenewtemplate").click(function() { SaveNewTemplate(this); });
    $(".deletetemplate").click(function() { DeleteTemplate(this); });
    $(".cancelnewtemplate").click(function() { CancelNewTemplate(this); });
    
    // display the 'new group' DIV
    var AddTemplate = function(btnObj) {
        // show the field details DIV
        $('#newtemplatedetail').css('visibility', 'visible');
        $('#newtemplatedetail').css('display', 'block');
        $(btnObj).parent().append($("#newtemplatedetail"));
        $('#newtemplatedetail > #newtemplatename').focus();
    };
    
    // save the new template 
    var SaveNewTemplate = function(btnObj) {
        // the template name can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newtemplatename").val().match(/^\d+/)) {
            alert("<?php xl('Template names cannot start with numbers.','e'); ?>");
            return false;
        }
        var validname = $("#newtemplatename").val().replace(/[^A-za-z0-9]/g, "_"); // match any non-word characters and replace them
        $("#newtemplatename").val(validname);

        // submit the form to add a new field to a specific group
        $("#formaction").val("newtemplate");
        $("#theform").submit();
    }
    
    // actually delete a template file
/*
    var DeleteTemplate = function(btnObj) {
        var parts = $(btnObj).attr("id");
        var groupname = parts.replace(/^\d+/, "");
        if (confirm("WARNING - This action cannot be undone.\n Are you sure you wish to delete the entire group named '"+groupname+"'?")) {
            // submit the form to add a new field to a specific group
            $("#formaction").val("deletegroup");
            $("#deletegroupname").val(parts);
            $("#theform").submit();
        }
    };
*/

    // just hide the new template DIV
    var CancelNewTemplate = function(btnObj) {
        // hide the field details DIV
        $('#newtemplatedetail').css('visibility', 'hidden');
        $('#newtemplatedetail').css('display', 'none');
        // reset the new group values to a default
        $('#newtemplatedetail > #newtemplatename').val("");
    };
    
    
    // save the template, overwriting the older version
    var SaveTemplate = function(btnObj) {
        if (! confirm("<?php xl('You are about to permanently replace the existing template. Are you sure you wish to continue?','e'); ?>")) {
            return false;
        }
        $("#formaction").val("savetemplate");
        $("#theform").submit();
    }
});

</script>

</html>
