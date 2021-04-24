<?php

/**
 * letter.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007-2011 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once($GLOBALS['srcdir'] . "/patient.inc");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Set up crypto object
$cryptoGen = new CryptoGen();

$template_dir = $GLOBALS['OE_SITE_DIR'] . "/documents/letter_templates";

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

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
    'FROM_PHONE'       => xl('FROM_PHONE'),
    'FROM_PHONECELL'   => xl('FROM_PHONECELL'),
    'FROM_EMAIL'       => xl('FROM_EMAIL'),
    'TO_TITLE'         => xl('TO_TITLE'),
    'TO_FNAME'         => xl('TO_FNAME'),
    'TO_LNAME'         => xl('TO_LNAME'),
    'TO_MNAME'         => xl('TO_MNAME'),
    'TO_STREET'        => xl('TO_STREET'),
    'TO_CITY'          => xl('TO_CITY'),
    'TO_STATE'         => xl('TO_STATE'),
    'TO_POSTAL'        => xl('TO_POSTAL'),
    'TO_VALEDICTORY'   => xl('TO_VALEDICTORY'),
    'TO_PHONE'         => xl('TO_PHONE'),
    'TO_PHONECELL'     => xl('TO_PHONECELL'),
    'TO_FAX'           => xl('TO_FAX'),
    'TO_ORGANIZATION'  => xl('TO_ORGANIZATION'),
    'PT_FNAME'         => xl('PT_FNAME'),
    'PT_LNAME'         => xl('PT_LNAME'),
    'PT_MNAME'         => xl('PT_MNAME'),
    'PT_STREET'        => xl('PT_STREET'),
    'PT_CITY'          => xl('PT_CITY'),
    'PT_STATE'         => xl('PT_STATE'),
    'PT_POSTAL'        => xl('PT_POSTAL'),
    'PT_PHONE_HOME'    => xl('PT_PHONE_HOME'),
    'PT_PHONE_CELL'    => xl('PT_PHONE_CELL'),
    'PT_SSN'           => xl('PT_SSN'),
    'PT_EMAIL'         => xl('PT_EMAIL'),
    'PT_DOB'           => xl('PT_DOB')

);

$patdata = sqlQuery("SELECT " .
  "p.fname, p.mname, p.lname, p.pubpid, p.DOB, " .
  "p.street, p.city, p.state, p.phone_home, p.phone_cell, p.ss, p.email, p.postal_code " .
  "FROM patient_data AS p " .
  "WHERE p.pid = ? LIMIT 1", array($pid));

$alertmsg = ''; // anything here pops up in an alert box

// If the Generate button was clicked...
if (!empty($_POST['formaction']) && ($_POST['formaction'] == "generate")) {
    $form_pid      = $_POST['form_pid'];
    $form_from     = $_POST['form_from'];
    $form_to       = $_POST['form_to'];
    $form_date     = $_POST['form_date'];
    $form_template = $_POST['form_template'];
    $form_format   = $_POST['form_format'];
    $form_body     = $_POST['form_body'];

    $frow = sqlQuery("SELECT * FROM users WHERE id = ?", array($form_from));
    $trow = sqlQuery("SELECT * FROM users WHERE id = ?", array($form_to));

    $datestr = $form_date;
    $from_title = $frow['title'] ? $frow['title'] . ' ' : '';
    $to_title   = $trow['title'] ? $trow['title'] . ' ' : '';

    $cpstring = $_POST['form_body'];

    // attempt to save to the autosaved template
    $fh = fopen("$template_dir/autosaved", 'w');
    // translate from definition to the constant
    $temp_bodytext = $cpstring;

    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{" . $value . "}", "{" . $key . "}", $temp_bodytext);
    }

    if ($GLOBALS['drive_encryption']) {
        $temp_bodytext = $cryptoGen->encryptStandard($temp_bodytext, null, 'database');
    }

    if (! fwrite($fh, $temp_bodytext)) {
        echo xlt('Error while saving to the file') . ' ' . text($template_dir) . "/autosaved" . ' . ' .
             xlt('Ensure OpenEMR has write privileges to directory') . ' ' . text($template_dir)  . "/ ." ;
        die;
    }

    fclose($fh);

    $cpstring = str_replace('{' . $FIELD_TAG['DATE'] . '}', $datestr, $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_TITLE'] . '}', $from_title, $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_FNAME'] . '}', $frow['fname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_LNAME'] . '}', $frow['lname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_MNAME'] . '}', $frow['mname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_STREET'] . '}', $frow['street'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_CITY'] . '}', $frow['city'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_STATE'] . '}', $frow['state'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_POSTAL'] . '}', $frow['zip'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_VALEDICTORY'] . '}', $frow['valedictory'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_PHONECELL'] . '}', $frow['phonecell'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_PHONE'] . '}', $frow['phone'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['FROM_EMAIL'] . '}', $frow['email'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_TITLE'] . '}', $to_title, $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_FNAME'] . '}', $trow['fname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_LNAME'] . '}', $trow['lname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_MNAME'] . '}', $trow['mname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_STREET'] . '}', $trow['street'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_CITY'] . '}', $trow['city'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_STATE'] . '}', $trow['state'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_POSTAL'] . '}', $trow['zip'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_VALEDICTORY'] . '}', $trow['valedictory'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_FAX'] . '}', $trow['fax'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_PHONE'] . '}', $trow['phone'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_PHONECELL'] . '}', $trow['phonecell'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['TO_ORGANIZATION'] . '}', $trow['organization'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_FNAME'] . '}', $patdata['fname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_LNAME'] . '}', $patdata['lname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_MNAME'] . '}', $patdata['mname'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_STREET'] . '}', $patdata['street'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_CITY'] . '}', $patdata['city'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_STATE'] . '}', $patdata['state'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_POSTAL'] . '}', $patdata['postal_code'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_PHONE_HOME'] . '}', $patdata['phone_home'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_PHONE_CELL'] . '}', $patdata['phone_cell'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_SSN'] . '}', $patdata['ss'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_EMAIL'] . '}', $patdata['email'], $cpstring);
    $cpstring = str_replace('{' . $FIELD_TAG['PT_DOB'] . '}', $patdata['DOB'], $cpstring);

    if ($form_format == "pdf") {
        $pdf = new Cezpdf($GLOBALS['rx_paper_size']);
        $pdf->ezSetMargins($GLOBALS['rx_top_margin'], $GLOBALS['rx_bottom_margin'], $GLOBALS['rx_left_margin'], $GLOBALS['rx_right_margin']);
        if (file_exists($GLOBALS['OE_SITE_DIR'] . "/custom_pdf.php")) {
            include($GLOBALS['OE_SITE_DIR'] . "/custom_pdf.php");
        } else {
            $pdf->selectFont('Helvetica');
            $pdf->ezText($cpstring, 12);
        }

        $pdf->ezStream();
        exit;
    } else { // $form_format = html
        $cpstring = text($cpstring); //escape to prevent stored cross script attack
        $cpstring = str_replace("\n", "<br />", $cpstring);
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
    <title><?php echo xlt('Letter'); ?></title>
    </head>
        <body>
    <div class='paddingdiv'>
        <?php echo $cpstring; ?>
        <div class="navigate">
    <a href='<?php echo $GLOBALS['rootdir'] . '/patient_file/letter.php?template=autosaved&csrf_token_form=' . attr_url(CsrfUtils::collectCsrfToken()); ?>' onclick='top.restoreSession()'>(<?php echo xlt('Back'); ?>)</a>
    </div>
    <script>
    window.print();
    </script>
    </body>
    </div>
        <?php
        exit;
    }
} elseif (isset($_GET['template']) && $_GET['template'] != "") {
    // utilized to go back to autosaved template
    $bodytext = "";
    $fh = fopen("$template_dir/" . convert_very_strict_label($_GET['template']), 'r');

    if (!$fh) {
        die(xlt("Requested template does not exist"));
    }

    while (!feof($fh)) {
        $bodytext .= fread($fh, 8192);
    }

    fclose($fh);

    if ($cryptoGen->cryptCheckStandard($bodytext)) {
        $bodytext = $cryptoGen->decryptStandard($bodytext, null, 'database');
    }

    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{" . $key . "}", "{" . $value . "}", $bodytext);
    }
} elseif (!empty($_POST['formaction']) && (($_POST['formaction'] == "loadtemplate") && !empty($_POST['form_template']))) {
    $bodytext = "";
    $fh = fopen("$template_dir/" . convert_very_strict_label($_POST['form_template']), 'r');

    if (!$fh) {
        die(xlt("Requested template does not exist"));
    }

    while (!feof($fh)) {
        $bodytext .= fread($fh, 8192);
    }

    fclose($fh);

    if ($cryptoGen->cryptCheckStandard($bodytext)) {
        $bodytext = $cryptoGen->decryptStandard($bodytext, null, 'database');
    }

    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{" . $key . "}", "{" . $value . "}", $bodytext);
    }
} elseif (!empty($_POST['formaction']) && (($_POST['formaction'] == "newtemplate") && !empty($_POST['newtemplatename']))) {
    // attempt to save the template
    $fh = fopen("$template_dir/" . convert_very_strict_label($_POST['newtemplatename']), 'w');
    // translate from definition to the constant
    $temp_bodytext = $_POST['form_body'];
    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{" . $value . "}", "{" . $key . "}", $temp_bodytext);
    }

    if ($GLOBALS['drive_encryption']) {
        $temp_bodytext = $cryptoGen->encryptStandard($temp_bodytext, null, 'database');
    }

    if (! fwrite($fh, $temp_bodytext)) {
        echo xlt('Error while writing to file') . ' ' . text($template_dir) . "/" . text($_POST['newtemplatename']);
        die;
    }

    fclose($fh);

    // read the saved file back
    $_POST['form_template'] = $_POST['newtemplatename'];
    $fh = fopen("$template_dir/" . convert_very_strict_label($_POST['form_template']), 'r');

    if (!$fh) {
        die(xlt("Requested template does not exist"));
    }

    while (!feof($fh)) {
        $bodytext = fread($fh, 8192);
    }

    fclose($fh);

    if ($cryptoGen->cryptCheckStandard($bodytext)) {
        $bodytext = $cryptoGen->decryptStandard($bodytext, null, 'database');
    }

    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{" . $key . "}", "{" . $value . "}", $bodytext);
    }
} elseif (!empty($_POST['formaction']) && (($_POST['formaction'] == "savetemplate") && !empty($_POST['form_template']))) {
    // attempt to save the template
    $fh = fopen("$template_dir/" . convert_very_strict_label($_POST['form_template']), 'w');
    // translate from definition to the constant
    $temp_bodytext = $_POST['form_body'];
    foreach ($FIELD_TAG as $key => $value) {
        $temp_bodytext = str_replace("{" . $value . "}", "{" . $key . "}", $temp_bodytext);
    }

    if ($GLOBALS['drive_encryption']) {
        $temp_bodytext = $cryptoGen->encryptStandard($temp_bodytext, null, 'database');
    }

    if (! fwrite($fh, $temp_bodytext)) {
        echo xlt('Error while writing to file') . ' ' . text($template_dir) . "/" . text($_POST['form_template']);
        die;
    }

    fclose($fh);

    // read the saved file back
    $fh = fopen("$template_dir/" . convert_very_strict_label($_POST['form_template']), 'r');

    if (!$fh) {
        die(xlt("Requested template does not exist"));
    }

    while (!feof($fh)) {
        $bodytext = fread($fh, 8192);
    }

    fclose($fh);

    if ($cryptoGen->cryptCheckStandard($bodytext)) {
        $bodytext = $cryptoGen->decryptStandard($bodytext, null, 'database');
    }

    // translate from constant to the definition
    foreach ($FIELD_TAG as $key => $value) {
        $bodytext = str_replace("{" . $key . "}", "{" . $value . "}", $bodytext);
    }
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
    if ($urow['fname']) {
        $uname .= ", " . $urow['fname'];
    }

    $tmp1 = " <option value='" . attr($urow['id']) . "'";
    $tmp2 = ">" . text($uname) . "</option>\n";
    $optto .= $tmp1 . $tmp2;
    if ($urow['id'] == $_SESSION['authUserID']) {
        $tmp1 .= " selected";
    }

    $optfrom .= $tmp1 . $tmp2;
    $ulist .= "ulist[" . attr($i) . "] = " . js_escape($uname . "|" . $urow['id'] . "|" . $urow['specialty']) . ";\n";
    ++$i;
}

// Get the unique specialties.
$sres = sqlStatement("SELECT DISTINCT specialty FROM users " .
  "WHERE active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
  "ORDER BY specialty");
$optspec = "<option value='All'>" . xlt('All') . "</option>\n";
while ($srow = sqlFetchArray($sres)) {
    $optspec .= " <option value='" . attr($srow['specialty']) . "'>" .
    text($srow['specialty']) . "</option>\n";
}
?>

<html>
<head>
<title><?php echo xlt('Letter Generator'); ?></title>
<?php Header::setupHeader(['datetime-picker', 'topdialog']); ?>

<script>
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

<form method='post' action='letter.php' id="theform" name="theform" onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="formaction" id="formaction" value="">
<input type='hidden' name='form_pid' value='<?php echo attr($pid) ?>' />

<center>
<p>
<table width='98%'>

 <tr>
  <td colspan='4' align='center'>
   &nbsp;<br />
   <b><?php echo xlt('Generate Letter regarding ') . text($patdata['fname']) . " " .
    text($patdata['lname']) . " (" . text($patdata['pubpid']) . ")" ?></b>
    <br />&nbsp;
  </td>
 </tr>

 <tr>

  <td class='col-form-label'>
    <?php echo xlt('From'); ?>:
  </td>

  <td>
   <select name='form_from' class='form-control'>
<?php echo $optfrom; ?>
   </select>
  </td>

  <td class='col-form-label'>
    <?php echo xlt('Date'); ?>:
  </td>

  <td>
   <input type='text' size='10' name='form_date' id='form_date' class='datepicker form-control'
    value='<?php echo attr(oeFormatShortDate(date('Y-m-d'))); ?>'
    title='<?php echo xla('Date of this letter'); ?>' />
  </td>

 </tr>

 <tr>

  <td class='col-form-label'>
    <?php echo xlt('Specialty'); ?>:
  </td>

  <td>
   <select name='form_specialty' onchange='newspecialty()' class='form-control'>
<?php echo $optspec; ?>
   </select>
  </td>

  <td class='col-form-label'>
    <?php echo xlt('Template'); ?>:
  </td>

  <td>
   <select name="form_template" id="form_template" class='form-control'>
   <option value="">(<?php echo xlt('none{{Template}}'); ?>)</option>
<?php
$tpldir = $GLOBALS['OE_SITE_DIR'] . "/documents/letter_templates";
$dh = opendir($tpldir);
if (! $dh) {
    die(xlt('Cannot read') . ' ' . text($tpldir));
}

while (false !== ($tfname = readdir($dh))) {
  // skip dot-files, scripts and images
    if (preg_match("/^\./", $tfname)) {
        continue;
    }

    if (preg_match("/\.php$/", $tfname)) {
        continue;
    }

    if (preg_match("/\.jpg$/", $tfname)) {
        continue;
    }

    if (preg_match("/\.png$/", $tfname)) {
        continue;
    }

    echo "<option value='" . attr($tfname) . "'";
    if ((!empty($_POST['form_template']) && ($tfname == $_POST['form_template'])) || (!empty($_GET['template']) && ($tfname == $_GET['template']))) {
        echo " SELECTED";
    }

    echo ">";
    if ($tfname == 'autosaved') {
        echo xlt($tfname);
    } else {
        echo text($tfname);
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

  <td class='col-form-label'>
    <?php echo xlt('To{{Destination}}'); ?>:
  </td>

  <td>
   <select name='form_to' class='form-control'>
<?php echo $optto; ?>
   </select>
  </td>

  <td class='col-form-label'>
    <?php echo xlt('Print Format'); ?>:
  </td>

  <td>
   <select name='form_format' class='form-control'>
    <option value='html'><?php echo xlt('HTML'); ?></option>
    <option value='pdf'><?php echo xlt('PDF'); ?></option>
   </select>
  </td>

 </tr>

 <tr>
  <td colspan='4'>
    <div id="letter_toolbar" class='text' style="width: 100%; background-color: #ddd; padding: 5px; margin: 0px;">
    <span class='col-form-label'><?php echo xlt('Insert special field'); ?>:</span>
    <select id="letter_field" class='form-control'>
    <option value="">- <?php echo xlt('Choose'); ?> -</option>
    <option value="<?php echo '{' . attr($FIELD_TAG['DATE']) . '}'; ?>"><?php echo xlt('Today\'s Date'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_TITLE']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Title'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_FNAME']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('First name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_MNAME']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Middle name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_LNAME']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Last name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_STREET']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Street'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_CITY']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('City'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_STATE']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('State'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_POSTAL']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Postal Code'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_VALEDICTORY']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Valedictory'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_PHONECELL']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Cell Phone'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_PHONE']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('Phone'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['FROM_EMAIL']) . '}'; ?>"><?php echo xlt('FROM'); ?> - <?php echo xlt('email'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_TITLE']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Title'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_FNAME']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('First name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_MNAME']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Middle name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_LNAME']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Last name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_STREET']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Street'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_CITY']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('City'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_STATE']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('State'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_POSTAL']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Postal Code'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_VALEDICTORY']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Valedictory'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_ORGANIZATION']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Organization'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_FAX']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Fax number'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_PHONE']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Phone number'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['TO_PHONECELL']) . '}'; ?>"><?php echo xlt('TO{{Destination}}'); ?> - <?php echo xlt('Cell phone number'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_FNAME']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('First name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_MNAME']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Middle name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_LNAME']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Last name'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_STREET']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Street'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_CITY']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('City'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_STATE']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('State'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_POSTAL']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Postal Code'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_PHONE_HOME']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Phone Home'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_PHONE_CELL']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Phone Cell'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_SSN']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('SSN'); ?></option>
    <option value="<?php echo '{' . attr($FIELD_TAG['PT_DOB']) . '}'; ?>"><?php echo xlt('PATIENT'); ?> - <?php echo xlt('Date of birth'); ?></option>
    </select>
    </div>
   <textarea name='form_body' id="form_body" class='form-control' rows='20' cols='30' style='width:100%'
    title='<?php echo xla('Enter body of letter here'); ?>' /><?php echo text($bodytext ?? ''); ?></textarea>
  </td>
 </tr>

</table>

<div class="btn-group" role="group">
    <button type='button' class='addtemplate btn btn-secondary btn-save'><?php echo xlt('Save as New'); ?></button>
    <button type='button' class='btn btn-secondary btn-save' name='savetemplate' id="savetemplate"><?php echo xlt('Save Changes'); ?></button>
    <button type='button' class='btn btn-secondary btn-transmit' name='form_generate' id="form_generate"><?php echo xlt('Generate Letter'); ?></button>
</div>

</center>

<!-- template DIV that appears when user chooses to add a new letter template -->
<div id="newtemplatedetail" style="margin-top: 4em; display: none; visibility: hidden;">
    <span class='col-form-label'><?php echo xlt('Template Name'); ?>:</span> <input type="textbox" size="20" maxlength="30" name="newtemplatename" id="newtemplatename" class="form-control">
    <br />
    <div class="btn-group" role="group">
        <button type="button" class="savenewtemplate btn btn-secondary btn-save"><?php echo xlt('Save new template'); ?></button>
        <button type="button" class="cancelnewtemplate btn btn-link btn-cancel"><?php echo xlt('Cancel'); ?></button>
    </div>
</div>

</form>
</body>

<script>

// jQuery stuff to make the page a little easier to use

$(function () {
    $("#form_generate").click(function() { $("#formaction").val("generate"); $("#theform").submit(); });
    $("#form_template").change(function() { $("#formaction").val("loadtemplate"); $("#theform").submit(); });

    $("#savetemplate").click(function() { SaveTemplate(this); });

    $("#letter_field").change(function() { insertAtCursor(document.getElementById("form_body"), $(this).val()); $(this).attr("selectedIndex", "0"); });

    $(".addtemplate").click(function() { AddTemplate(this); });
    $(".savenewtemplate").click(function() { SaveNewTemplate(this); });
    $(".deletetemplate").click(function() { DeleteTemplate(this); });
    $(".cancelnewtemplate").click(function() { CancelNewTemplate(this); });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

    // display the 'new group' DIV
    var AddTemplate = function(btnObj) {
        // show the field details DIV
        $('#newtemplatedetail').css('visibility', 'visible');
        $('#newtemplatedetail').css('display', 'block');
        $(btnObj).parent().append($("#newtemplatedetail"));
        $('#newtemplatedetail > #newtemplatename').focus();
        $(window).scrollTop($(document).height());
    };

    // save the new template
    var SaveNewTemplate = function(btnObj) {
        // the template name can only have letters, numbers, spaces and underscores
        // AND it cannot start with a number
        if ($("#newtemplatename").val().match(/^\d+/)) {
            alert(<?php echo xlj('Template names cannot start with numbers.'); ?>);
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
        if (! confirm(<?php echo xlj('You are about to permanently replace the existing template. Are you sure you wish to continue?'); ?>)) {
            return false;
        }
        $("#formaction").val("savetemplate");
        $("#theform").submit();
    }
});

</script>

</html>
