<?php
/**
* Encounter form for entering procedure orders.
*
* Copyright (C) 2010-2013 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../../orders/qoe.inc.php");
require_once("../../orders/gen_hl7_order.inc.php");
require_once("../../../custom/code_types.inc.php");

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

// If Save or Transmit was clicked, save the info.
//
if ($_POST['bn_save'] || $_POST['bn_xmit']) {
  $ppid = formData('form_lab_id') + 0;

  $sets =
    "date_ordered = " . QuotedOrNull(formData('form_date_ordered'))     . ", " .
    "provider_id = " . (formData('form_provider_id') + 0)               . ", " .
    "lab_id = " . $ppid                                                 . ", " .
    "date_collected = " . QuotedOrNull(formData('form_date_collected')) . ", " .
    "order_priority = '" . formData('form_order_priority')              . "', " .
    "order_status = '" . formData('form_order_status')                  . "', " .
    "clinical_hx = '" . formData('form_clinical_hx')                    . "', " .
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
    $formid = sqlInsert($query);
    addForm($encounter, "Procedure Order", $formid, "procedure_order", $pid, $userauthorized);
  }

  // Remove any existing procedures and their answers for this order and
  // replace them from the form.

  sqlStatement("DELETE FROM procedure_answers WHERE procedure_order_id = ?",
    array($formid));
  sqlStatement("DELETE FROM procedure_order_code WHERE procedure_order_id = ?",
    array($formid));

  for ($i = 0; isset($_POST['form_proc_type'][$i]); ++$i) {
    $ptid = $_POST['form_proc_type'][$i] + 0;
    if ($ptid <= 0) continue;

    $prefix = "ans$i" . "_";

    $poseq = sqlInsert("INSERT INTO procedure_order_code SET ".
      "procedure_order_id = ?, " .
      "diagnoses = ?, " .
      "procedure_code = (SELECT procedure_code FROM procedure_type WHERE procedure_type_id = ?), " .
      "procedure_name = (SELECT name FROM procedure_type WHERE procedure_type_id = ?)",
      array($formid, strip_escape_custom($_POST['form_proc_type_diag'][$i]), $ptid, $ptid));

    $qres = sqlStatement("SELECT " .
      "q.procedure_code, q.question_code, q.options, q.fldtype " .
      "FROM procedure_type AS t " .
      "JOIN procedure_questions AS q ON q.lab_id = t.lab_id " .
      "AND q.procedure_code = t.procedure_code AND q.activity = 1 " .
      "WHERE t.procedure_type_id = ? " .
      "ORDER BY q.seq, q.question_text", array($ptid));

    while ($qrow = sqlFetchArray($qres)) {
      $options = trim($qrow['options']);
      $qcode = trim($qrow['question_code']);
      $fldtype = $qrow['fldtype'];
      $data = '';
      if ($fldtype == 'G') {
        if ($_POST["G1_$prefix$qcode"]) {
          $data = $_POST["G1_$prefix$qcode"] * 7 + $_POST["G2_$prefix$qcode"];
        }
      }
      else {
        $data = $_POST["$prefix$qcode"];
      }
      if (!isset($data) || $data === '') continue;
      if (!is_array($data)) $data = array($data);
      foreach ($data as $datum) {
        // Note this will auto-assign the seq value.
        sqlStatement("INSERT INTO procedure_answers SET ".
          "procedure_order_id = ?, " .
          "procedure_order_seq = ?, " .
          "question_code = ?, " .
          "answer = ?",
          array($formid, $poseq, $qcode, strip_escape_custom($datum)));
      }
    }
  }

  $alertmsg = '';
  if ($_POST['bn_xmit']) {
    $hl7 = '';
    $alertmsg = gen_hl7_order($formid, $hl7);
    if (empty($alertmsg)) {
      $alertmsg = send_hl7_order($ppid, $hl7);
    }
    if (empty($alertmsg)) {
      sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE " .
        "procedure_order_id = ?", array($formid));
    }
  }

  formHeader("Redirecting....");
  if ($alertmsg) {
    echo "\n<script language='Javascript'>alert('";
    echo addslashes(xl('Transmit failed') . ': ' . $alertmsg);
    echo "')</script>\n";
  }
  formJump();
  formFooter();
  exit;
}

if ($formid) {
  $row = sqlQuery ("SELECT * FROM procedure_order WHERE " .
    "procedure_order_id = ?",
    array($formid)) ;
}

$enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date FROM " .
  "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
  "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
  "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
  "fe.id = f.form_id LIMIT 1",
  array($pid, $encounter));
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
// formseq = 0-relative index in the form.
var gbl_formseq;
function sel_proc_type(formseq) {
 var f = document.forms[0];
 // if (!f.form_lab_id.value) {
 //  alert('<?php echo xls('Please select a procedure provider'); ?>');
 //  return;
 // }
 gbl_formseq = formseq;
 var ptvarname = 'form_proc_type[' + formseq + ']';
 /********************************************************************
 dlgopen('../../orders/types.php?popup=1' +
  '&labid=' + f.form_lab_id.value +
  '&order=' + f[ptvarname].value +
  '&formid=<?php echo $formid; ?>' +
  '&formseq=' + formseq,
  '_blank', 800, 500);
 ********************************************************************/
 // This replaces the above for an easier/faster order picker tool.
 dlgopen('../../orders/find_order_popup.php' +
  '?labid=' + f.form_lab_id.value +
  '&order=' + f[ptvarname].value +
  '&formid=<?php echo $formid; ?>' +
  '&formseq=' + formseq,
  '_blank', 800, 500);
}

// This is for callback by the find-procedure-type popup.
// Sets both the selected type ID and its descriptive name.
function set_proc_type(typeid, typename) {
 var f = document.forms[0];
 var ptvarname = 'form_proc_type[' + gbl_formseq + ']';
 var ptdescname = 'form_proc_type_desc[' + gbl_formseq + ']';
 f[ptvarname].value = typeid;
 f[ptdescname].value = typename;
}

// This is also for callback by the find-procedure-type popup.
// Sets the contents of the table containing the form fields for questions.
function set_proc_html(s, js) {
 document.getElementById('qoetable[' + gbl_formseq + ']').innerHTML = s;
 eval(js);
}

// New lab selected so clear all procedures and questions from the form.
function lab_id_changed() {
 var f = document.forms[0];
 for (var i = 0; true; ++i) {
  var ix = '[' + i + ']';
  if (!f['form_proc_type' + ix]) break;
  f['form_proc_type' + ix].value = '-1';
  f['form_proc_type_desc' + ix].value = '';
  document.getElementById('qoetable' + ix).innerHTML = '';
 }
}

// Add a line for entry of another procedure.
function addProcLine() {
 var f = document.forms[0];
 var table = document.getElementById('proctable');
 // Compute i = next procedure index.
 var i = 0;
 for (; f['form_proc_type[' + i + ']']; ++i);
 var row = table.insertRow(table.rows.length);
 var cell = row.insertCell(0);
 cell.vAlign = 'top';
 cell.innerHTML = "<b><?php echo xls('Procedure'); ?> " + (i + 1) + ":</b>";
 var cell = row.insertCell(1);
 cell.vAlign = 'top';
 cell.innerHTML =
  "<input type='text' size='50' name='form_proc_type_desc[" + i + "]'" +
  " onclick='sel_proc_type(" + i + ")'" +
  " onfocus='this.blur()'" +
  " title='<?php echo xla('Click to select the desired procedure'); ?>'" +
  "  style='width:100%;cursor:pointer;cursor:hand' readonly />" +
  " <input type='hidden' name='form_proc_type[" + i + "]' value='-1' />" +
  "<br /><b><?php echo xla('Diagnoses'); ?>: </b>" +
  "<input type='text' size='50' name='form_proc_type_diag[" + i + "]'" +
  " onclick='sel_related(this.name)'" +
  " title='<?php echo xla('Click to add a diagnosis'); ?>'" +
  " onfocus='this.blur()'" +
  " style='cursor:pointer;cursor:hand' readonly />" +
  " <div style='width:95%;' id='qoetable[" + i + "]'></div>";
 sel_proc_type(i);
 return false;
}

// The name of the form field for find-code popup results.
var rcvarname;

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f[rcvarname].value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f[rcvarname].value = s;
}

// This invokes the find-code popup.
function sel_related(varname) {
 rcvarname = varname;
 // codetype is just to make things easier and avoid mistakes.
 // Might be nice to have a lab parameter for acceptable code types.
 // Also note the controlling script here runs from interface/patient_file/encounter/.
 dlgopen('find_code_popup.php?codetype=<?php echo attr(collect_codetypes("diagnosis","csv")) ?>', '_blank', 500, 400);
}

var transmitting = false;

// Issue a Cancel/OK warning if a previously transmitted order is being transmitted again.
function validate(f) {
<?php if (!empty($row['date_transmitted'])) { ?>
 if (transmitting) {
  if (!confirm('<?php echo xls('This order was already transmitted on') . ' ' .
    addslashes($row['date_transmitted']) . '. ' .
    xls('Are you sure you want to transmit it again?'); ?>')) {
    return false;
  }
 }
<?php } ?>
 top.restoreSession();
 return true;
}

</script>

</head>

<body class="body_top">

<form method="post" action="<?php echo $rootdir ?>/forms/procedure_order/new.php?id=<?php echo $formid ?>"
 onsubmit="return validate(this)">

<p class='title' style='margin-top:8px;margin-bottom:8px;text-align:center'>
<?php
  echo xl('Procedure Order for') . ' ';
  echo $enrow['fname'] . ' ' . $enrow['mname'] . ' ' . $enrow['lname'];
  echo ' ' . xl('on') . ' ' . oeFormatShortDate(substr($enrow['date'], 0, 10));
?>
</p>

<center>

<p>
<table border='1' width='95%' id='proctable'>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Ordering Provider','e'); ?>:</b></td>
  <td valign='top'>
<?php
generate_form_field(array('data_type'=>10,'field_id'=>'provider_id'),
  $row['provider_id']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Sending To','e'); ?>:</b></td>
  <td valign='top'>
   <select name='form_lab_id' onchange='lab_id_changed()'>
 <?php
  $ppres = sqlStatement("SELECT ppid, name FROM procedure_providers " .
    "ORDER BY name, ppid");
  while ($pprow = sqlFetchArray($ppres)) {
    echo "<option value='" . attr($pprow['ppid']) . "'";
    if ($pprow['ppid'] == $row['lab_id']) echo " selected";
    echo ">" . text($pprow['name']) . "</option>";
  }
?>
   </select>
  </td>
 </tr>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Order Date','e'); ?>:</b></td>
  <td valign='top'>
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
  <td width='1%' valign='top' nowrap><b><?php xl('Internal Time Collected','e'); ?>:</b></td>
  <td valign='top'>
<?php
    echo "<input type='text' size='16' name='form_date_collected' id='form_date_collected'" .
      " value='" . substr($row['date_collected'], 0, 16) . "'" .
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
  <td width='1%' valign='top' nowrap><b><?php xl('Priority','e'); ?>:</b></td>
  <td valign='top'>
<?php
generate_form_field(array('data_type'=>1,'field_id'=>'order_priority',
  'list_id'=>'ord_priority'), $row['order_priority']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Status','e'); ?>:</b></td>
  <td valign='top'>
<?php
generate_form_field(array('data_type'=>1,'field_id'=>'order_status',
  'list_id'=>'ord_status'), $row['order_status']);
?>
  </td>
 </tr>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Clinical History','e'); ?>:</b></td>
  <td valign='top'>
   <input type='text' maxlength='255' name='form_clinical_hx' style='width:100%'
    class='inputtext' value='<?php echo attr($row['clinical_hx']); ?>' />
  </td>
 </tr>

 <tr>
  <td width='1%' valign='top' nowrap><b><?php xl('Patient Instructions','e'); ?>:</b></td>
  <td valign='top'>
   <textarea rows='3' cols='40' name='form_patient_instructions' style='width:100%'
    wrap='virtual' class='inputtext' /><?php echo $row['patient_instructions'] ?></textarea>
  </td>
 </tr>

<?php

  // This section merits some explanation. :)
  //
  // If any procedures have already been saved for this form, then a top-level table row is
  // created for each of them, and includes the relevant questions and any existing answers.
  // Otherwise a single empty table row is created for entering the first or only procedure.
  //
  // If a new procedure is selected or changed, the questions for it are (re)generated from
  // the dialog window from which the procedure is selected, via JavaScript.  The sel_proc_type
  // function and the types.php script that it invokes collaborate to support this feature.
  //
  // The generate_qoe_html function in qoe.inc.php contains logic to generate the HTML for
  // the questions, and can be invoked either from this script or from types.php.
  //
  // The $i counter that you see below is to resolve the need for unique names for form fields
  // that may occur for each of the multiple procedure requests within the same order.
  // procedure_order_seq serves a similar need for uniqueness at the database level.

  $oparr = array();
  if ($formid) {
    $opres = sqlStatement("SELECT " .
      "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, " .
      "pc.diagnoses, pt.procedure_type_id " .
      "FROM procedure_order_code AS pc " .
      "LEFT JOIN procedure_type AS pt ON pt.lab_id = ? AND " .
      "pt.procedure_code = pc.procedure_code " .
      "WHERE pc.procedure_order_id = ? " .
      "ORDER BY pc.procedure_order_seq",
      array($row['lab_id'], $formid));
    while ($oprow = sqlFetchArray($opres)) {
      $oparr[] = $oprow;
    }
  }
  if (empty($oparr)) $oparr[] = array('procedure_name' => '');

  $i = 0;
  foreach ($oparr as $oprow) {
    $ptid = -1; // -1 means no procedure is selected yet
    if (!empty($oprow['procedure_type_id'])) {
      $ptid = $oprow['procedure_type_id'];
    }
?>
 <tr>
  <td width='1%' valign='top'><b><?php echo xl('Procedure') . ' ' . ($i + 1); ?>:</b></td>
  <td valign='top'>
   <input type='text' size='50' name='form_proc_type_desc[<?php echo $i; ?>]'
    value='<?php echo attr($oprow['procedure_name']) ?>'
    onclick="sel_proc_type(<?php echo $i; ?>)"
    onfocus='this.blur()'
    title='<?php xla('Click to select the desired procedure','e'); ?>'
    style='width:100%;cursor:pointer;cursor:hand' readonly />
   <input type='hidden' name='form_proc_type[<?php echo $i; ?>]' value='<?php echo $ptid ?>' />
   <br /><b><?php echo xlt('Diagnoses'); ?>:</b>
   <input type='text' size='50' name='form_proc_type_diag[<?php echo $i; ?>]'
    value='<?php echo attr($oprow['diagnoses']) ?>' onclick='sel_related(this.name)'
    title='<?php echo xla('Click to add a diagnosis'); ?>'
    onfocus='this.blur()'
    style='cursor:pointer;cursor:hand' readonly />
   <!-- MSIE innerHTML property for a TABLE element is read-only, so using a DIV here. -->
   <div style='width:95%;' id='qoetable[<?php echo $i; ?>]'>
<?php
$qoe_init_javascript = '';
echo generate_qoe_html($ptid, $formid, $oprow['procedure_order_seq'], $i);
if ($qoe_init_javascript)
  echo "<script language='JavaScript'>$qoe_init_javascript</script>";
?>
   </div>
  </td>
 </tr>
<?php
    ++$i;
  }
?>

</table>

<p>
<input type='button' value='<?php echo xla('Add Procedure'); ?>' onclick="addProcLine()" />
&nbsp;
<input type='submit' name='bn_save' value='<?php echo xla('Save'); ?>' onclick='transmitting = false;' />
&nbsp;
<input type='submit' name='bn_xmit' value='<?php echo xla('Save and Transmit'); ?>' onclick='transmitting = true;' />
&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'" />
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

