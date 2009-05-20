<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");

// Check authorization.
$thisauth = acl_check('patients', 'demo');
if ($thisauth != 'write' && $thisauth != 'addonly')
  die("Adding demographics is not authorized.");

$CPR = 4; // cells per row

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 " .
  "ORDER BY group_name, seq");
?>
<html>
<head>
<?php html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

<style>
body, td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.js"></script>

<SCRIPT LANGUAGE="JavaScript"><!--

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

//code used from http://tech.irt.org/articles/js037/
function replace(string,text,by) {
 // Replaces text with by in string
 var strLength = string.length, txtLength = text.length;
 if ((strLength == 0) || (txtLength == 0)) return string;

 var i = string.indexOf(text);
 if ((!i) && (text != string.substring(0,txtLength))) return string;
 if (i == -1) return string;

 var newstr = string.substring(0,i) + by;

 if (i+txtLength < strLength)
  newstr += replace(string.substring(i+txtLength,strLength),text,by);

 return newstr;
}

function upperFirst(string,text) {
 return replace(string,text,text.charAt(0).toUpperCase() + text.substring(1,text.length));
}

/*********************************************************************
function popUp(URL) {
 day = new Date();
 id = day.getTime();
 top.restoreSession();
 eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=300,left = 440,top = 362');");
}
*********************************************************************/

function checkNum () {
 var re= new RegExp();
 re = /^\d*\.?\d*$/;
 str=document.forms[0].monthly_income.value;
 if(re.exec(str))
 {
 }else{
  alert("Please enter a dollar amount using only numbers and a decimal point.");
 }
}

// This capitalizes the first letter of each word in the passed input
// element.  It also strips out extraneous spaces.
function capitalizeMe(elem) {
 var a = elem.value.split(' ');
 var s = '';
 for(var i = 0; i < a.length; ++i) {
  if (a[i].length > 0) {
   if (s.length > 0) s += ' ';
   s += a[i].charAt(0).toUpperCase() + a[i].substring(1);
  }
 }
 elem.value = s;
}

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

function validate(f) {
<?php generate_layout_validation('DEM'); ?>
 return true;
}

// This invokes the patient search dialog.
function searchme() {
 var f = document.forms[0];
 var url = '../main/finder/patient_select.php?popup=1';

<?php
$lres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_name, seq");
while ($lrow = sqlFetchArray($lres)) {
  $field_id  = $lrow['field_id'];
  if (strpos($field_id, 'em_') === 0) continue;
  $data_type = $lrow['data_type'];
  $fldname = "form_$field_id";
  switch($data_type) {
    case  1:
    case 11:
    case 12:
    case 13:
    case 14:
      echo
      " if (f.$fldname.selectedIndex > 0) {\n" .
      "  url += '&$field_id=' + escape(f.$fldname.options[f.$fldname.selectedIndex].value);\n" .
      " }\n";
      break;
    case  2:
    case  3:
    case  4:
    case 15:
      echo
      " if (trimlen(f.$fldname.value) > 0) {\n" .
      "  url += '&$field_id=' + escape(f.$fldname.value);\n" .
      " }\n";
      break;
  }
}
?>

 dlgopen(url, '_blank', 700, 500);
}

//-->

</script>
</head>

<body class="body_top">

<form action='new_comprehensive_save.php' method='post' onsubmit='return validate(this)'>

<span class='title'><?php xl('Search or Add Patient','e'); ?></span>

<table width='100%' cellpadding='0' cellspacing='8'>
 <tr>
  <td align='left' valign='top'>

<?php

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo " </table>\n";
    echo "</div>\n";
  }
}

$last_group    = '';
$cell_count    = 0;
$item_count    = 0;
$display_style = 'block';
$group_seq     = 0; // this gives the DIV blocks unique IDs

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = '';

  if (strpos($field_id, 'em_') === 0) {
    $tmp = substr($field_id, 3);
    if (isset($result2[$tmp])) $currvalue = $result2[$tmp];
  }
  else {
    if (isset($result[$field_id])) $currvalue = $result[$field_id];
  }

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    //$group_seq  = substr($this_group, 0, 1);  -- replaced by a simple counter
    $group_seq++;    // ID for DIV tags
    $group_name = substr($this_group, 1);
    if (strlen($last_group) > 0) echo "<br />";
    echo "<span class='bold'><input type='checkbox' name='form_cb_$group_seq' id='form_cb_$group_seq' value='1' " .
      "onclick='return divclick(this,\"div_$group_seq\");'";
    if ($display_style == 'block') echo " checked";
    echo " /><b>" . (($GLOBALS['translate_layout'])? xl($group_name) : $group_name) . "</b></span>\n";
    echo "<div id='div_$group_seq' class='section' style='display:$display_style;'>\n";
    echo " <table border='0' cellpadding='0'>\n";
    $display_style = 'none';
    $last_group = $this_group;
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td colspan='$titlecols'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  echo "<b>";
  if ($frow['title']) echo (($GLOBALS['translate_layout'])? xl($frow['title']) : $frow['title']).":"; else echo "&nbsp;";
  echo "</b>";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_form_field($frow, $currvalue);
}

end_group();
?>

<center><br />
<input type="button" id="search" value="Search" />
&nbsp;&nbsp;
<input type="button" id="create" value="Create New Patient" />

</center>

  </td>
  <td align='right' valign='top' width='1%' nowrap>
   <!-- Image upload stuff was here but got moved. -->
  </td>
 </tr>
</table>

</form>

<!-- include support for the list-add selectbox feature -->
<?php include($GLOBALS['fileroot']."/library/options_listadd.inc"); ?>

</body>

<script language="JavaScript">

// fix inconsistently formatted phone numbers from the database
var f = document.forms[0];
if (f.form_phone_contact) phonekeyup(f.form_phone_contact,mypcc);
if (f.form_phone_home   ) phonekeyup(f.form_phone_home   ,mypcc);
if (f.form_phone_biz    ) phonekeyup(f.form_phone_biz    ,mypcc);
if (f.form_phone_cell   ) phonekeyup(f.form_phone_cell   ,mypcc);

<?php echo $date_init; ?>

// -=- jQuery makes life easier -=-

var matches = 0; // number of patients that match the demographic information being entered
var override = 0; // flag that overrides the duplication warning

$(document).ready(function() {
    // when these fields lose focus, do a look-up to check for duplicates
    // already in the database
    $('#form_fname').blur(function() { DupeCheck(); });
    $('#form_mname').blur(function() { DupeCheck(); });
    $('#form_lname').blur(function() { DupeCheck(); });
    $('#form_pubpid').blur(function() { DupeCheck(); });
    $('#form_DOB').blur(function() { DupeCheck(); });
    $('#form_ss').blur(function() { DupeCheck(); });
    $('#form_sex').blur(function() { DupeCheck(); });

    $('#search').click(function() { searchme(); });
    $('#create').click(function() { submitme(); });

    // function updates the matchcount DIV
    var DupeCheck = function() {
        $.get("<?php echo $GLOBALS['webroot']; ?>/library/ajax/find_patients.php",
                { returntype: "count",
                  fname: $('#form_fname').val(),
                  mname: $('#form_mname').val(),
                  lname: $('#form_lname').val(),
                  pubpid: $('#form_pubpid').val(),
                  DOB: $('#form_DOB').val(),
                  ss: $('#form_ss').val(),
                  sex: $('#form_sex').val()
                },
                function(data, textStatus) {
                    matches = data;
                }
             );
    };

    var submitme = function() {
        var f = document.forms[0];
        if (matches > 0 && override == false) {
            if (! confirm("DUPLICATION WARNING\n===================\nThere are "+matches+" patient(s) in the database that match the demographic information you have entered.\n\nDo you wish to continue adding this new patient?"))
                return false;
        }
        if (validate(f)) {
            top.restoreSession();
            f.submit();
        }
    }
}); // end document.ready

</script>

</html>

