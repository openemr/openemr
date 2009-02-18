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
if ($_POST['formaction']=="generate") {
    // documentation for ezpdf is here --> http://www.ros.co.nz/pdf/
    require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
    $pdf =& new Cezpdf($GLOBALS['oer_config']['prescriptions']['paper_size']);
    $pdf->ezSetMargins($GLOBALS['oer_config']['prescriptions']['top']
                        ,$GLOBALS['oer_config']['prescriptions']['bottom']
                        ,$GLOBALS['oer_config']['prescriptions']['left']
                        ,$GLOBALS['oer_config']['prescriptions']['right']
                        );
    $pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");
    if(!empty($this->pconfig['logo'])) {
        $pdf->ezImage($this->pconfig['logo'],"","","none","left");
    }

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

    //$fh = fopen("$template_dir/$form_template", 'r');
    //while (!feof($fh)) $cpstring .= fread($fh, 8192);
    //fclose($fh);

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
    //$cpstring = str_replace('{MESSAGE}'         , $form_body, $cpstring);
    
    $pdf->ezText($cpstring, 12);
  
    $pdf->ezStream();
    exit;

}
else if ($_POST['formaction'] == "loadtemplate" && $_POST['form_template'] != "") {
    $bodytext = "";
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
}
else if ($_POST['formaction'] == "newtemplate" && $_POST['newtemplatename'] != "") {
    // attempt to save the template
    $fh = fopen("$template_dir/".$_POST['newtemplatename'], 'w');
    if (! fwrite($fh, $_POST['form_body'])) {
        echo "Error while writing to file ".$template_dir."/".$_POST['newtemplatename'];
        die;
    }
    fclose($fh);

    // read the saved file back
    $_POST['form_template'] = $_POST['newtemplatename'];
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
}
else if ($_POST['formaction'] == "savetemplate" && $_POST['form_template'] != "") {
    // attempt to save the template
    $fh = fopen("$template_dir/".$_POST['form_template'], 'w');
    if (! fwrite($fh, $_POST['form_body'])) {
        echo "Error while writing to file ".$template_dir."/".$_POST['form_template'];
        die;
    }
    fclose($fh);

    // read the saved file back
    $fh = fopen("$template_dir/".$_POST['form_template'], 'r');
    while (!feof($fh)) $bodytext.= fread($fh, 8192);
    fclose($fh);
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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
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
   <select name="form_template" id="form_template">
   <option value="">(none)</option>
<?php
$tpldir = "$webserver_root/custom/letter_templates";
$dh = opendir($tpldir);
if (! $dh) die("Cannot read $tpldir");
while (false !== ($tfname = readdir($dh))) {
    // skip dot-files
    if (preg_match("/^\./", $tfname)) { continue; }
    echo "<option value=".$tfname;
    if ($tfname == $_POST['form_template']) echo " SELECTED";
    echo ">";
    echo $tfname;
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
    <div id="letter_toolbar" class='text' style="width: 100%; background-color: #ddd; padding: 5px; margin: 0px;">
    Insert special field:
    <select id="letter_field">
    <option value="">- Choose -</option>
    <option value="{DATE}">Today's Date</option>
    <option value="{FROM_TITLE}">FROM - Title</option>
    <option value="{FROM_FNAME}">FROM - First name</option>
    <option value="{FROM_MNAME}">FROM - Middle name</option>
    <option value="{FROM_LNAME}">FROM - Last name</option>
    <option value="{FROM_STREET}">FROM - Street</option>
    <option value="{FROM_CITY}">FROM - City</option>
    <option value="{FROM_STATE}">FROM - State</option>
    <option value="{FROM_POSTAL}">FROM - Postal Code</option>
    <option value="{FROM_VALEDICTORY}">FROM - Valedictory</option>
    <option value="{FROM_PHONECELL}">FROM - Cell Phone</option>
    <option value="{TO_TITLE}">TO - Title</option>
    <option value="{TO_FNAME}">TO - First name</option>
    <option value="{TO_MNAME}">TO - Middle name</option>
    <option value="{TO_LNAME}">TO - Last name</option>
    <option value="{TO_STREET}">TO - Street</option>
    <option value="{TO_CITY}">TO - City</option>
    <option value="{TO_STATE}">TO - State</option>
    <option value="{TO_POSTAL}">TO - Postal Code</option>
    <option value="{TO_VALEDICTORY}">TO - Valedictory</option>
    <option value="{TO_ORGANIZATION}">TO - Organization</option>
    <option value="{TO_FAX}">TO - Fax number</option>
    <option value="{PT_FNAME}">PATIENT - First name</option>
    <option value="{PT_MNAME}">PATIENT - Middle name</option>
    <option value="{PT_LNAME}">PATIENT - Last name</option>
    <option value="{PT_DOB}">PATIENT - Date of birth</option>
    </select>
    </div>
   <textarea name='form_body' id="form_body" rows='20' cols='30' style='width:100%'
    title='Enter body of letter here' /><?php echo $bodytext; ?></textarea>
  </td>
 </tr>

</table>

<input type='button' class="addtemplate" value='Save as New '>
<input type='button' name='savetemplate' id="savetemplate" value='Save Changes'>
<input type='button' name='form_generate' id="form_generate" value='Generate Letter'>

</center>

<!-- template DIV that appears when user chooses to add a new letter template -->
<div id="newtemplatedetail" style="border: 1px solid black; padding: 3px; display: none; visibility: hidden; background-color: lightgrey;">
Template Name: <input type="textbox" size="20" maxlength="30" name="newtemplatename" id="newtemplatename">
<br>
<input type="button" class="savenewtemplate" value="Save new template">
<input type="button" class="cancelnewtemplate" value="Cancel">
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
    
    $("#letter_field").change(function() { insertAtCursor($("#form_body"), $(this).val()); $(this).attr("selectedIndex", "0"); });
    
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
            alert("Template names cannot start with numbers.");
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
        if (! confirm("You are about to permanently replace the existing template. Are you sure you wish to continue?")) {
            return false;
        }
        $("#formaction").val("savetemplate");
        $("#theform").submit();
    }
});

</script>

</html>
