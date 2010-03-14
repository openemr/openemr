<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");

// Indicates if we are entering in batch mode.
$form_batch = empty($_GET['batch']) ? 0 : 1;

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xl('Not authorized'));

if (!$form_batch && !$pid) die(xl('There is no current patient'));

function oresData($name, $index) {
  $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
  return formDataCore($s, true);
}

function QuotedOrNull($fld) {
  if (empty($fld)) return "NULL";
  return "'$fld'";
}

$current_report_id = 0;

if ($_POST['form_submit']) {
  foreach ($_POST['form_line'] as $lino => $line_value) {
    list($order_id, $restyp_id, $report_id, $result_id) = explode(':', $line_value);

    // Not using xl() here because these errors are for debugging only.
    if (empty($order_id)) die("Order ID is missing from line $lino.");
    if (empty($restyp_id)) die("Result type ID is missing from line $lino.");

    // If report data exists for this line, save it.
    $date_report = oresData("form_date_report", $lino);

    if (!empty($date_report)) {
      $sets =
        "procedure_order_id = '$order_id', " .
        "date_report = '$date_report', " .
        "date_collected = " . QuotedOrNull(oresData("form_date_collected", $lino)) . ", " .
        "specimen_num = '" . oresData("form_specimen_num", $lino) . "', " .
        "report_status = '" . oresData("form_report_status", $lino) . "'";
      if ($report_id) { // report already exists
        sqlStatement("UPDATE procedure_report SET $sets "  .
          "WHERE procedure_report_id = '$report_id'");
      }
      else { // add new report
        $report_id = sqlInsert("INSERT INTO procedure_report SET $sets");
      }
    }

    // If this line had report data entry fields, filled or not, set the
    // "current report ID" which the following result data will link to.
    if (isset($_POST["form_date_report"][$lino])) $current_report_id = $report_id;

    // If there's a report, save corresponding results.
    if ($current_report_id) {
      $sets =
        "procedure_report_id = '$current_report_id', " .
        "procedure_type_id = '$restyp_id', " .
        "abnormal = '" . oresData("form_result_abnormal", $lino) . "', " .
        "result = '" . oresData("form_result_result", $lino) . "', " .
        "range = '" . oresData("form_result_range", $lino) . "', " .
        "facility = '" . oresData("form_facility", $lino) . "', " .
        "comments = '" . oresData("form_comments", $lino) . "', " .
        "result_status = '" . oresData("form_result_status", $lino) . "'";
      if ($result_id) { // result already exists
        sqlStatement("UPDATE procedure_result SET $sets "  .
          "WHERE procedure_result_id = '$result_id'");
      }
      else { // add new result
        $result_id = sqlInsert("INSERT INTO procedure_result SET $sets");
      }
    }

  } // end foreach
}
?>
<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>
<title><?php  xl('Procedure Results','e'); ?></title>

<style>

tr.head   { font-size:10pt; background-color:#cccccc; text-align:center; }
tr.detail { font-size:10pt; }
a, a:visited, a:hover { color:#0000cc; }

.celltext {
 font-size:10pt;
 font-weight:normal;
 border-style:solid;
 border-top-width:0px;
 border-bottom-width:0px;
 border-left-width:0px;
 border-right-width:0px;
 border-color: #aaaaaa;
 background-color:transparent;
 width:100%;
 color:#0000cc;
}

.celltextfw {
 font-size:10pt;
 font-weight:normal;
 border-style:solid;
 border-top-width:0px;
 border-bottom-width:0px;
 border-left-width:0px;
 border-right-width:0px;
 border-color: #aaaaaa;
 background-color:transparent;
 color:#0000cc;
}

.cellselect {
 font-size:10pt;
 background-color:transparent;
 color:#0000cc;
}

.reccolor {
 color:#008800;
}

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>

<script language="JavaScript">

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

// This invokes the find-procedure-type popup.
var ptvarname;
function sel_proc_type(varname) {
 var f = document.forms[0];
 if (typeof varname == 'undefined') varname = 'form_proc_type';
 ptvarname = varname;
 dlgopen('types.php?popup=1&order=' + f[ptvarname].value, '_blank', 800, 500);
}

// This is for callback by the find-procedure-type popup.
// Sets both the selected type ID and its descriptive name.
function set_proc_type(typeid, typename) {
 var f = document.forms[0];
 f[ptvarname].value = typeid;
 f[ptvarname + '_desc'].value = typename;
}

// Helper functions.
function extGetX(elem) {
 var x = 0;
 while(elem != null) {
  x += elem.offsetLeft;
  elem = elem.offsetParent;
 }
 return x;
}
function extGetY(elem) {
 var y = 0;
 while(elem != null) {
  y += elem.offsetTop;
  elem = elem.offsetParent;
 }
 return y;
}

// Show or hide the "extras" div for a result.
var extdiv = null;
function extShow(lino, show) {
 var thisdiv = document.getElementById("ext_" + lino);
 if (extdiv) {
  extdiv.style.visibility = 'hidden';
  extdiv.style.left = '-1000px';
  extdiv.style.top = '0px';
 }
 if (show && thisdiv != extdiv) {
  extdiv = thisdiv;
  var dw = window.innerWidth ? window.innerWidth - 20 : document.body.clientWidth;
  x = dw - extdiv.offsetWidth;
  if (x < 0) x = 0;
  var y = extGetY(show) + show.offsetHeight;
  extdiv.style.left = x;
  extdiv.style.top  = y;
  extdiv.style.visibility = 'visible';
 }
 else {
  extdiv = null;
 }
}

// Helper function for validate.
function prDateRequired(rlino) {
 var f = document.forms[0];
 if (f['form_date_report['+rlino+']'].value.length < 10) {
  alert('<?php xl('Missing report date','e') ?>');
  if (f['form_date_report['+rlino+']'].focus)
   f['form_date_report['+rlino+']'].focus();
  return false;
 }
 return true;
}

// Validation at submit time.
function validate(f) {
 var rlino = 0;
 for (var lino = 0; f['form_line['+lino+']']; ++lino) {
  if (f['form_date_report['+lino+']']) {
   rlino = lino;
   if (f['form_report_status['+rlino+']'].selectedIndex > 0) {
    if (!prDateRequired(rlino)) return false;
   }
  }
  var abnstat = f['form_result_abnormal['+lino+']'].selectedIndex > 0;
  if (abnstat && !prDateRequired(rlino)) return false;
  /*******************************************************************
  var resstat = f['form_result_status['+lino+']'].selectedIndex > 0;
  if (resstat != abnstat) {
   alert('<?php xl('Result status or abnormality is missing','e') ?>');
   if (f['form_result_abnormal['+lino+']'].focus)
    f['form_result_abnormal['+lino+']'].focus();
   return false;
  }
  *******************************************************************/
 }
 top.restoreSession();
 return true;
}

</script>

</head>

<body class="body_top">
<form method='post' action='orders_results.php?batch=<?php echo $form_batch; ?>'
 onsubmit='return validate(this)'>

<table>
 <tr>
  <td class='text'>
<?php
if ($form_batch) {
  $form_from_date = formData('form_from_date','P',true);
  $form_to_date   = formData('form_to_date','P',true);
  if (empty($form_to_date)) $form_to_date = $form_from_date;
  $form_proc_type = formData('form_proc_type') + 0;
  if (!$form_proc_type) $form_proc_type = -1;
  $form_proc_type_desc = '';
  if ($form_proc_type > 0) {
    $ptrow = sqlQuery("SELECT name FROM procedure_type WHERE " .
      "procedure_type_id = '$form_proc_type'");
    $form_proc_type_desc = $ptrow['name'];
  }
?>
   <?php xl('Procedure','e'); ?>:
   <input type='text' size='30' name='form_proc_type_desc'
    value='<?php echo addslashes($form_proc_type_desc) ?>'
    onclick='sel_proc_type()' onfocus='this.blur()'
    title='<?php xl('Click to select the desired procedure','e'); ?>'
    style='cursor:pointer;cursor:hand' readonly />
   <input type='hidden' name='form_proc_type' value='<?php echo $form_proc_type ?>' />

   &nbsp;<?php xl('From','e'); ?>:
   <input type='text' size='10' name='form_from_date' id='form_from_date'
    value='<?php echo $form_from_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />

   &nbsp;<?php xl('To','e'); ?>:
   <input type='text' size='10' name='form_to_date' id='form_to_date'
    value='<?php echo $form_to_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />

   &nbsp;
<?php
} // end header for batch option
?>
   <input type='checkbox' name='form_all' value='1'<?php if ($_POST['form_all']) echo " checked"; ?>><?php xl('Include Completed','e') ?>
   &nbsp;
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>>
  </td>
 </tr>
</table>

<?php if (!$form_batch || ($form_proc_type > 0 && $form_from_date)) { ?>

<table width='100%' cellpadding='1' cellspacing='2'>

 <tr class='head'>
  <td colspan='2'><?php echo $form_batch ? xl('Patient') : xl('Order'); ?></td>
  <td colspan='4'><?php xl('Report','e'); ?></td>
  <td colspan='4'><?php xl('Results and','e'); ?> <span class='reccolor''>
   <?php  xl('Recommendations','e'); ?></span></td>
 </tr>

 <tr class='head'>
  <td><?php echo $form_batch ? xl('Name') : xl('Date'); ?></td>
  <td><?php echo $form_batch ? xl('ID') : xl('Name'); ?></td>
  <td><?php xl('Reported','e'); ?></td>
  <td><?php xl('Ext Time Collected','e'); ?></td>
  <td><?php xl('Specimen','e'); ?></td>
  <td><?php xl('Status','e'); ?></td>
  <td><?php xl('Name (click for more)','e'); ?></td>
  <td><?php xl('Abn','e'); ?></td>
  <td><?php xl('Value','e'); ?></td>
  <td><?php xl('Range','e'); ?></td>
 </tr>

<?php 
$selects =
  "po.procedure_order_id, po.date_ordered, " .
  "po.procedure_type_id AS order_type_id, pt1.name AS procedure_name, " .
  "ptrc.name AS result_category_name, " .
  "pt2.procedure_type AS result_type, " .
  "pt2.procedure_type_id AS result_type_id, pt2.name AS result_name, " .
  "pt2.units AS result_def_units, pt2.range AS result_def_range, " .
  "pt2.description AS result_description, lo.title AS units_name, " .
  "pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, pr.report_status, " .
  "ps.procedure_result_id, ps.abnormal, ps.result, ps.range, ps.result_status, " .
  "ps.facility, ps.comments";

// This join syntax means that results must all be at the same "level".
// Either there is one result the same as the order, or all results are
// direct children of the order, or all results are grandchildren of the
// order.  No other arrangements are allowed.
//
$joins =
  "LEFT JOIN procedure_type AS pt1 ON pt1.procedure_type_id = po.procedure_type_id " .
  // ptrc is an optional result category just under the order type
  "LEFT JOIN procedure_type AS ptrc ON ptrc.parent = po.procedure_type_id " .
  "AND ptrc.procedure_type LIKE 'grp%' " .
  // pt2 is a result or recommendation type the same as or just under the order type
  "LEFT JOIN procedure_type AS pt2 ON " .
  "( ( ptrc.procedure_type_id IS NULL AND ( pt2.parent = po.procedure_type_id " .
  "OR pt2.procedure_type_id = po.procedure_type_id ) ) OR " .
  "( ptrc.procedure_type_id IS NOT NULL AND pt2.parent = ptrc.procedure_type_id ) " .
  ") AND ( pt2.procedure_type LIKE 'res%' OR pt2.procedure_type LIKE 'rec%' ) " .
  //
  "LEFT JOIN list_options AS lo ON list_id = 'proc_unit' AND option_id = pt2.units " .
  "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
  "LEFT JOIN procedure_result AS ps ON ps.procedure_report_id = pr.procedure_report_id " .
  "AND ps.procedure_type_id = pt2.procedure_type_id";

$orderby =
  "po.date_ordered, po.procedure_order_id, pr.procedure_report_id, " .
  "ptrc.seq, ptrc.name, ptrc.procedure_type_id, " .
  "pt2.seq, pt2.name, pt2.procedure_type_id";

$where = empty($_POST['form_all']) ?
  "( pr.report_status IS NULL OR pr.report_status = '' OR pr.report_status = 'prelim' )" :
  "1 = 1";

if ($form_batch) {
  $res = sqlStatement("SELECT po.patient_id, " .
  "pd.fname, pd.mname, pd.lname, pd.pubpid, $selects " .
  "FROM procedure_order AS po " .
  "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id $joins " .
  "WHERE po.procedure_type_id = '$form_proc_type' AND " .
  "po.date_ordered >= '$form_from_date' AND po.date_ordered <= '$form_to_date' " .
  "AND $where " .
  "ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, $orderby");
}
else {
  $res = sqlStatement("SELECT $selects " .
  "FROM procedure_order AS po $joins " .
  "WHERE po.patient_id = '$pid' AND $where " .
  "ORDER BY $orderby");
}

$lastpoid = -1;
$lastprid = -1;
$encount = 0;
$lino = 0;
$extra_html = '';

while ($row = sqlFetchArray($res)) {
  $order_id  = empty($row['procedure_order_id' ]) ? 0 : ($row['procedure_order_id' ] + 0);
  $restyp_id = empty($row['result_type_id'])      ? 0 : ($row['result_type_id'     ] + 0);
  $report_id = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
  $result_id = empty($row['procedure_result_id']) ? 0 : ($row['procedure_result_id'] + 0);

  $result_name = '';
  if (!empty($row['result_category_name'])) $result_name = $row['result_category_name'] . ' / ';
  if (!empty($row['result_name'])) $result_name .= $row['result_name'];

  $date_report      = empty($row['date_report'     ]) ? '' : $row['date_report'];
  $date_collected   = empty($row['date_collected'  ]) ? '' : substr($row['date_collected'], 0, 16);
  $specimen_num     = empty($row['specimen_num'    ]) ? '' : $row['specimen_num'];
  $report_status    = empty($row['report_status'   ]) ? '' : $row['report_status']; 
  $result_abnormal  = empty($row['abnormal'        ]) ? '' : $row['abnormal'];
  $result_result    = empty($row['result'          ]) ? '' : $row['result'];
  $facility         = empty($row['facility'        ]) ? '' : $row['facility'];
  $comments         = empty($row['comments'        ]) ? '' : $row['comments'];
  $result_range     = empty($row['range'           ]) ? $row['result_def_range'] : $row['range'];
  $result_status    = empty($row['result_status'   ]) ? '' : $row['result_status'];
  $result_def_units = empty($row['result_def_units']) ? '' : $row['result_def_units'];
  $units_name       = empty($row['units_name'      ]) ? xl('Units not defined') : $row['units_name'];

  if ($lastpoid != $order_id) ++$encount;
  $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

  echo " <tr class='detail' bgcolor='$bgcolor'>\n";

  // If this starts a new order, display its date and procedure name,
  // otherwise empty space.
  //
  if ($lastpoid != $order_id) {
    if ($form_batch) {
      $tmp = $row['lname'];
      if ($row['fname'] || $row['mname'])
        $tmp .= ', ' . $row['fname'] . ' ' . $row['mname'];
      echo "  <td>" . htmlentities($tmp) . "</td>\n";
      echo "  <td>" . htmlentities($row['pubpid']) . "</td>\n";
    }
    else {
      echo "  <td>" . $row['date_ordered'] . "</td>\n";
      echo "  <td>" . htmlentities($row['procedure_name']) . "</td>\n";
    }
    $lastprid = -1; // force report fields on first line of each order
  } else {
    echo "  <td colspan='2' style='background-color:#94d6e7'>&nbsp;";
  }
  // Include a hidden form field containing all IDs for this line.
  echo "<input type='hidden' name='form_line[$lino]' value='$order_id:$restyp_id:$report_id:$result_id' />";
  echo "</td>\n";

  // If this starts a new report or a new order, generate the report form
  // fields.  In the case of a new order with no report yet, the fields will
  // have their blank/default values, and form_line (above) will indicate a
  // report ID of 0.
  //
  // TBD: Also generate default report fields and another set of results if
  // the previous report is marked "Preliminary".
  //
  if ($report_id != $lastprid) {
    echo "  <td nowrap>";
    echo "<input type='text' size='8' name='form_date_report[$lino]'" .
      " id='form_date_report[$lino]' class='celltextfw' value='$date_report' " .
      " title='" . xl('Date of this report') . "'" .
      " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'" .
      " />";
    echo "<span class='bold' id='q_date_report[$lino]' style='cursor:pointer' " .
      "title='" . xl('Click here to choose a date') . "' />?</span>";
    echo "</td>\n";

    echo "  <td nowrap>";
    echo "<input type='text' size='13' name='form_date_collected[$lino]'" .
      " id='form_date_collected[$lino]'" .
      " class='celltextfw' value='$date_collected' " .
      " title='" . xl('Date and time of sample collection') . "'" .
      " onkeyup='datekeyup(this,mypcc,true)' onblur='dateblur(this,mypcc,true)'" .
      " />";
    echo "<span class='bold' id='q_date_collected[$lino]' style='cursor:pointer' " .
      "title='" . xl('Click here to choose a date and time') . "' />?</span>";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='text' size='8' name='form_specimen_num[$lino]'" .
      " class='celltext' value='$specimen_num' " .
      " title='" . xl('Specimen number/identifier') . "'" .
      " />";
    echo "</td>\n";

    echo "  <td>";
    echo generate_select_list("form_report_status[$lino]", 'proc_rep_status',
      $report_status, xl('Report Status'), ' ', 'cellselect');
    echo "</td>\n";
  }
  else {
    echo "  <td colspan='4' style='background-color:#94d6e7'>&nbsp;</td>\n";
  }

  echo "  <td title='" . addslashes($row['result_description']) . "'";
  if ($row['result_type'] == 'rec') echo " class='reccolor'";
  echo " style='cursor:pointer' onclick='extShow($lino, this)'>" .
    htmlentities($result_name) . "</td>\n";

  echo "  <td>";
  echo generate_select_list("form_result_abnormal[$lino]", 'proc_res_abnormal',
    $result_abnormal, xl('Indicates abnormality'), ' ', 'cellselect');
  echo "</td>\n";

  echo "  <td>";
  if ($result_def_units == 'bool') {
    // echo generate_select_list("form_result_result[$lino]", 'proc_res_bool',
    //   $result_result, $units_name, ' ', 'cellselect');
    echo "&nbsp;--";
  }
  else {
    echo "<input type='text' size='4' name='form_result_result[$lino]'" .
      " class='celltext' value='$result_result' " .
      " title='" . addslashes($units_name) . "'" .
      " />";
  }
  echo "</td>\n";

  echo "  <td>";
  echo "<input type='text' size='8' name='form_result_range[$lino]'" .
    " class='celltext' value='$result_range' " .
    " title='" . xl('Reference range of results') . "'" .
    " />";
  echo "</td>\n";

  echo " </tr>\n";

  // Create a floating div for additional attributes of this result.
  $extra_html .= "<div id='ext_$lino' " .
    "style='position:absolute;width:500px;border:1px solid black;" .
    "padding:2px;background-color:#cccccc;visibility:hidden;" .
    "z-index:1000;left:-1000px;top:0px;font-size:9pt;'>\n" .
    "<table width='100%'>\n" .
    "<tr><td class='bold' align='center' colspan='2' style='padding:4pt 0 4pt 0'>" .
    // xl('Additional Attributes') .
    htmlspecialchars($result_name) .
    "</td></tr>\n" .
    "<tr><td class='bold' width='1%' nowrap>" . xl('Status') . ": </td>" .
    "<td>" . generate_select_list("form_result_status[$lino]", 'proc_res_status',
      $result_status, xl('Result Status'), '') . "</td></tr>\n" .
    "<tr><td class='bold' nowrap>" . xl('Facility') . ": </td>" .
    "<td><input type='text' size='15' name='form_facility[$lino]'" .
    " value='$facility' " .
    " title='" . xl('Supplier facility name') . "'" .
    " style='width:100%' /></td></tr>\n" .
    "<tr><td class='bold' nowrap>" . xl('Comments') . ": </td>" .
    "<td><textarea rows='3' cols='15' name='form_comments[$lino]'" .
    " title='" . xl('Comments for this result or recommendation') . "'" .
    " style='width:100%' />" . htmlspecialchars($comments) .
    "</textarea></td></tr>\n" .
    "</table>\n" .
    "<p><center><input type='button' value='" . xl('Close') . "' " .
    "onclick='extShow($lino, false)' /></center></p>\n" .
    "</div>\n";

  $lastpoid = $order_id;
  $lastprid = $report_id;
  ++$lino;
}
?>
</table>

<center><p>
 <input type='submit' name='form_submit' value='<?php xl('Save','e'); ?>' />
</p></center>

<?php } ?>

<?php echo $extra_html; ?>

<script language='JavaScript'>

<?php if ($form_batch) { ?>
// Initialize calendar widgets for "from" and "to" dates.
Calendar.setup({inputField:'form_from_date', ifFormat:'%Y-%m-%d',
 button:'img_from_date'});
Calendar.setup({inputField:'form_to_date', ifFormat:'%Y-%m-%d',
 button:'img_to_date'});
<?php } ?>

// Initialize calendar widgets for report dates and collection dates.
var f = document.forms[0];
for (var lino = 0; f['form_line['+lino+']']; ++lino) {
 if (f['form_date_report['+lino+']']) {
  Calendar.setup({inputField:'form_date_report['+lino+']', ifFormat:'%Y-%m-%d',
   button:'q_date_report['+lino+']'});
  Calendar.setup({inputField:'form_date_collected['+lino+']', ifFormat:'%Y-%m-%d %H:%M',
   button:'q_date_collected['+lino+']', showsTime:true});
 }
}

</script>

</form>
</body>
</html>
