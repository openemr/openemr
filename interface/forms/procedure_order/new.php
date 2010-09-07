<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");

// Defaults for new orders.
$row = array(
  'provider_id' => $_SESSION['authUserID'],
  'date_ordered' => date('Y-m-d'),
  'date_collected' => date('Y-m-d H:i'),
);

if (! $encounter) { // comes from globals.php
 die("Internal error: we do not seem to be in an encounter!");
}

function cbvalue($cbname) {
 return $_POST[$cbname] ? '1' : '0';
}

function cbinput($name, $colname) {
 global $row;
 $ret  = "<input type='checkbox' name='$name' value='1'";
 if ($row[$colname]) $ret .= " checked";
 $ret .= " />";
 return $ret;
}

function cbcell($name, $desc, $colname) {
 return "<td width='25%' nowrap>" . cbinput($name, $colname) . "$desc</td>\n";
}

function QuotedOrNull($fld) {
  if (empty($fld)) return "NULL";
  return "'$fld'";
}

$formid = formData('id', 'G') + 0;

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {

  $sets =
    "procedure_type_id = " . (formData('form_proc_type') + 0)           . ", " .
    "date_ordered = " . QuotedOrNull(formData('form_date_ordered'))     . ", " .
    "provider_id = " . (formData('form_provider_id') + 0)               . ", " .
    "date_collected = " . QuotedOrNull(formData('form_date_collected')) . ", " .
    "order_priority = '" . formData('form_order_priority')              . "', " .
    "order_status = '" . formData('form_order_status')                  . "', " .
    "patient_instructions = '" . formData('form_patient_instructions')  . "', " .
    "patient_id = '" . $pid                                             . "', " .
    "encounter_id = '" . $encounter                                     . "'";

  // If updating an existing form...
  //
  if ($formid) {
    $query = "UPDATE procedure_order SET $sets "  .
      "WHERE procedure_order_id = '$formid'";
    sqlStatement($query);
  }

  // If adding a new form...
  //
  else {
    $query = "INSERT INTO procedure_order SET $sets";
    $newid = sqlInsert($query);
    addForm($encounter, "Procedure Order", $newid, "procedure_order", $pid, $userauthorized);
  }

  formHeader("Redirecting....");
  formJump();
  formFooter();
  exit;
}

if ($formid) {
  $row = sqlQuery ("SELECT * FROM procedure_order WHERE " .
    "procedure_order_id = '$formid' AND activity = '1'") ;
}

$enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date FROM " .
  "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
  "p.pid = '$pid' AND f.pid = '$pid' AND f.encounter = '$encounter' AND " .
  "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
  "fe.id = f.form_id LIMIT 1");
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css" />

<style>

td {
 font-size:10pt;
}

.inputtext {
 padding-left:2px;
 padding-right:2px;
}

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<script language='JavaScript'>

// This invokes the find-procedure-type popup.
var ptvarname;
function sel_proc_type(varname) {
 var f = document.forms[0];
 if (typeof varname == 'undefined') varname = 'form_proc_type';
 ptvarname = varname;
 dlgopen('../../orders/types.php?popup=1&order=' + f[ptvarname].value, '_blank', 800, 500);
}

// This is for callback by the find-procedure-type popup.
// Sets both the selected type ID and its descriptive name.
function set_proc_type(typeid, typename) {
 var f = document.forms[0];
 f[ptvarname].value = typeid;
 f[ptvarname + '_desc'].value = typename;
}

</script>

</head>

<body class="body_top">

<form method="post" action="<?php echo $rootdir ?>/forms/procedure_order/new.php?id=<?php echo $formid ?>" onsubmit="return top.restoreSession()">

<p class='title' style='margin-top:8px;margin-bottom:8px;text-align:center'>
<?php
  echo xl('Procedure Order for') . ' ';
  echo $enrow['fname'] . ' ' . $enrow['mname'] . ' ' . $enrow['lname'];
  echo ' ' . xl('on') . ' ' . oeFormatShortDate(substr($enrow['date'], 0, 10));
?>
</p>

<center>

<p>
<table border='1' width='95%'>

<?php
$ptid = -1; // -1 means no order is selected yet
$ptrow = array('name' => '');
if (!empty($row['procedure_type_id'])) {
  $ptid = $row['procedure_type_id'];
  $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " .
    "procedure_type_id = '$ptid'");
}
?>
 <tr>
  <td width='1%' nowrap><b><?php xl('Order Type','e'); ?>:</b></td>
  <td>
   <input type='text' size='50' name='form_proc_type_desc'
    value='<?php echo addslashes($ptrow['name']) ?>'
    onclick='sel_proc_type()' onfocus='this.blur()'
    title='<?php xl('Click to select the desired procedure','e'); ?>'
    style='width:100%;cursor:pointer;cursor:hand' readonly />
   <input type='hidden' name='form_proc_type' value='<?php echo $ptid ?>' />
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Ordering Provider','e'); ?>:</b></td>
  <td>
<?php
generate_form_field(array('data_type'=>10,'field_id'=>'provider_id'),
  $row['provider_id']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Date Ordered','e'); ?>:</b></td>
  <td>
<?php
    echo "<input type='text' size='10' name='form_date_ordered' id='form_date_ordered'" .
      " value='" . $row['date_ordered'] . "'" .
      " title='" . xl('Date of this order') . "'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'" .
      " />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_date_ordered' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . xl('Click here to choose a date') . "' />";
?>
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Internal Time Collected','e'); ?>:</b></td>
  <td>
<?php
    echo "<input type='text' size='16' name='form_date_collected' id='form_date_collected'" .
      " value='" . $row['date_collected'] . "'" .
      " title='" . xl('Date and time that the sample was collected') . "'" .
      // " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'" .
      " />" .
      "<img src='$rootdir/pic/show_calendar.gif' align='absbottom' width='24' height='22'" .
      " id='img_date_collected' border='0' alt='[?]' style='cursor:pointer'" .
      " title='" . xl('Click here to choose a date and time') . "' />";
?>
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Priority','e'); ?>:</b></td>
  <td>
<?php
generate_form_field(array('data_type'=>1,'field_id'=>'order_priority',
  'list_id'=>'ord_priority'), $row['order_priority']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Status','e'); ?>:</b></td>
  <td>
<?php
generate_form_field(array('data_type'=>1,'field_id'=>'order_status',
  'list_id'=>'ord_status'), $row['order_status']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' nowrap><b><?php xl('Patient Instructions','e'); ?>:</b></td>
  <td>
   <textarea rows='3' cols='40' name='form_patient_instructions' style='width:100%'
    wrap='virtual' class='inputtext' /><?php echo $row['patient_instructions'] ?></textarea>
  </td>
 </tr>

</table>

<p>
<input type='submit' name='bn_save' value='<?php xl('Save','e'); ?>' />
&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
</p>

</center>

<script language='JavaScript'>
Calendar.setup({inputField:'form_date_ordered', ifFormat:'%Y-%m-%d',
 button:'img_date_ordered'});
Calendar.setup({inputField:'form_date_collected', ifFormat:'%Y-%m-%d %H:%M',
 button:'img_date_collected', showsTime:true});
</script>

</form>
</body>
</html>

