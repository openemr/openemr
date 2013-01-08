<?php
// Copyright (C) 2010-2013 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../orders/lab_exchange_tools.php");

// Indicates if we are entering in batch mode.
$form_batch = empty($_GET['batch']) ? 0 : 1;

// Indicates if we are entering in review mode.
$form_review = empty($_GET['review']) ? 0 : 1;

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xl('Not authorized'));

// Check authorization for pending review.
$reviewauth = acl_check('patients', 'sign');
if ($form_review and !$reviewauth and !$thisauth) die(xl('Not authorized'));

// Set pid for pending review.
if ($_GET['set_pid'] && $form_review) {
  require_once("$srcdir/pid.inc");
  require_once("$srcdir/patient.inc");
  setpid($_GET['set_pid']);
  
  $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
  ?>
  <script language='JavaScript'>
    parent.left_nav.setPatient(<?php echo "'" . addslashes($result['fname']) . " " . addslashes($result['lname']) . "',$pid,'" . addslashes($result['pubpid']) . "','', ' " . xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($result['DOB_YMD']) . "'"; ?>);
    parent.left_nav.setRadio(window.name, 'orp');
  </script>
  <?php
}

if (!$form_batch && !$pid && !$form_review) die(xl('There is no current patient'));

function oresRawData($name, $index) {
  $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
  return trim(strip_escape_custom($s));
}

function oresData($name, $index) {
  $s = isset($_POST[$name][$index]) ? $_POST[$name][$index] : '';
  return formDataCore($s, true);
}

function QuotedOrNull($fld) {
  if (empty($fld)) return "NULL";
  return "'$fld'";
}

$current_report_id = 0;

if ($_POST['form_submit'] && !empty($_POST['form_line'])) { 
  foreach ($_POST['form_line'] as $lino => $line_value) {
    list($order_id, $order_seq, $report_id, $result_id) = explode(':', $line_value);

    // Not using xl() here because this is for debugging only.
    if (empty($order_id)) die("Order ID is missing from line $lino.");

    // If report data exists for this line, save it.
    $date_report = oresData("form_date_report", $lino);

    if (!empty($date_report)) {
      $sets =
        "procedure_order_id = '$order_id', " .
        "procedure_order_seq = '$order_seq', " .
        "date_report = '$date_report', " .
        "date_collected = " . QuotedOrNull(oresData("form_date_collected", $lino)) . ", " .
        "specimen_num = '" . oresData("form_specimen_num", $lino) . "', " .
        "report_status = '" . oresData("form_report_status", $lino) . "'";

      // Set the review status to reviewed.
      if ($form_review) 
        $sets .= ", review_status = 'reviewed'";
    
      if ($report_id) { // Report already exists.
        sqlStatement("UPDATE procedure_report SET $sets "  .
          "WHERE procedure_report_id = '$report_id'");
      }
      else { // Add new report.
        $report_id = sqlInsert("INSERT INTO procedure_report SET $sets");
      }
    }

    // If this line had report data entry fields, filled or not, set the
    // "current report ID" which the following result data will link to.
    if (isset($_POST["form_date_report"][$lino])) $current_report_id = $report_id;

    // If there's a report, save corresponding results.
    if ($current_report_id) {
      // Comments and notes will be combined into one comments field.
      $form_comments = oresRawData("form_comments", $lino);
      $form_comments = str_replace("\n"  ,'~' , $form_comments);
      $form_comments = str_replace("\r"  ,''  , $form_comments);
      $form_notes = oresRawData("form_notes", $lino);
      if ($form_notes !== '') {
        $form_comments .= "\n" . $form_notes;
      }
      $sets =
        "procedure_report_id = '$current_report_id', " .
        "result_code = '" . oresData("form_result_code", $lino) . "', " .
        "result_text = '" . oresData("form_result_text", $lino) . "', " .
        "abnormal = '" . oresData("form_result_abnormal", $lino) . "', " .
        "result = '" . oresData("form_result_result", $lino) . "', " .
        "`range` = '" . oresData("form_result_range", $lino) . "', " .
        "units = '" . oresData("form_result_units", $lino) . "', " .
        "facility = '" . oresData("form_facility", $lino) . "', " .
        "comments = '" . add_escape_custom($form_comments) . "', " .
        "result_status = '" . oresData("form_result_status", $lino) . "'";
      if ($result_id) { // result already exists
        sqlStatement("UPDATE procedure_result SET $sets "  .
          "WHERE procedure_result_id = '$result_id'");
      }
      else { // Add new result.
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
 }
 top.restoreSession();
 return true;
}

</script>

</head>

<body class="body_top">
<form method='post' action='orders_results.php?batch=<?php echo $form_batch; ?>&review=<?php echo $form_review; ?>'
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
   <!-- removed by jcw -- check/submit sequece too tedious.  This is a quick fix -->
<!--   <input type='checkbox' name='form_all' value='1' <?php if ($_POST['form_all']) echo " checked"; ?>><?php xl('Include Completed','e') ?>
   &nbsp;-->
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>>
  </td>
 </tr>
</table>

<?php if (!$form_batch || ($form_proc_type > 0 && $form_from_date)) { ?>

<table width='100%' cellpadding='1' cellspacing='2'>

 <tr class='head'>
  <td colspan='2'><?php echo $form_batch ? xl('Patient') : xl('Order'); ?></td>
  <td colspan='4'><?php xl('Report','e'); ?></td>
  <td colspan='7'><?php xl('Results and','e'); ?> <span class='reccolor''>
   <?php  xl('Recommendations','e'); ?></span></td>
 </tr>

 <tr class='head'>
  <td><?php echo $form_batch ? xl('Name') : xl('Date'); ?></td>
  <td><?php echo $form_batch ? xl('ID') : xl('Procedure Name'); ?></td>
  <td><?php xl('Reported','e'); ?></td>
  <td><?php xl('Ext Time Collected','e'); ?></td>
  <td><?php xl('Specimen','e'); ?></td>
  <td><?php xl('Status','e'); ?></td>
  <td><?php xl('Code','e'); ?></td>
  <td><?php xl('Name','e'); ?></td>
  <td><?php xl('Abn','e'); ?></td>
  <td><?php xl('Value','e'); ?></td>
  <td><?php xl('Units', 'e'); ?></td>
  <td><?php xl('Range','e'); ?></td>
  <td><?php xl('?','e'); ?></td>
 </tr>

<?php 
$selects =
  "po.procedure_order_id, po.date_ordered, pc.procedure_order_seq, " .
  "pt1.procedure_type_id AS order_type_id, pc.procedure_name, " .
  "pr.procedure_report_id, pr.date_report, pr.date_collected, pr.specimen_num, " .
  "pr.report_status, pr.review_status";

$joins =
  "JOIN procedure_order_code AS pc ON pc.procedure_order_id = po.procedure_order_id " .
  "LEFT JOIN procedure_type AS pt1 ON pt1.lab_id = po.lab_id AND pt1.procedure_code = pc.procedure_code " .
  "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id AND " .
  "pr.procedure_order_seq = pc.procedure_order_seq";

$orderby =
  "po.date_ordered, po.procedure_order_id, " .
  "pc.procedure_order_seq, pr.procedure_report_id";

// removed by jcw -- check/submit sequece too tedious.  This is a quick fix
//$where = empty($_POST['form_all']) ?
//  "( pr.report_status IS NULL OR pr.report_status = '' OR pr.report_status = 'prelim' )" :
//  "1 = 1";

$where = "1 = 1";

if ($form_batch) {
  $query = "SELECT po.patient_id, " .
  "pd.fname, pd.mname, pd.lname, pd.pubpid, $selects " .
  "FROM procedure_order AS po " .
  "LEFT JOIN patient_data AS pd ON pd.pid = po.patient_id $joins " .
  "WHERE pt.procedure_type_id = '$form_proc_type' AND " .
  "po.date_ordered >= '$form_from_date' AND po.date_ordered <= '$form_to_date' " .
  "AND $where " .
  "ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, $orderby";
}
else {
  $query = "SELECT $selects " .
  "FROM procedure_order AS po " .
  "$joins " .
  "WHERE po.patient_id = '$pid' AND $where " .
  "ORDER BY $orderby";
}

$res = sqlStatement($query);

$lastpoid = -1;
$lastpcid = -1;
$lastprid = -1;
$encount = 0;
$lino = 0;
$extra_html = '';
$lastrcn = '';
$facilities = array();

while ($row = sqlFetchArray($res)) {
  $order_type_id  = empty($row['order_type_id'      ]) ? 0 : ($row['order_type_id' ] + 0);
  $order_id       = empty($row['procedure_order_id' ]) ? 0 : ($row['procedure_order_id' ] + 0);
  $order_seq      = empty($row['procedure_order_seq']) ? 0 : ($row['procedure_order_seq'] + 0);
  $report_id      = empty($row['procedure_report_id']) ? 0 : ($row['procedure_report_id'] + 0);
  $date_report    = empty($row['date_report'     ]) ? '' : $row['date_report'];
  $date_collected = empty($row['date_collected'  ]) ? '' : substr($row['date_collected'], 0, 16);
  $specimen_num   = empty($row['specimen_num'    ]) ? '' : $row['specimen_num'];
  $report_status  = empty($row['report_status'   ]) ? '' : $row['report_status']; 
  $review_status  = empty($row['review_status'   ]) ? 'received' : $row['review_status'];

  // skip report_status = receive to make sure do not show the report before it reviewed and sign off by Physicians
  if ($form_review) {
    if ($review_status == "reviewed") continue;
  }
  else {
    if ($review_status == "received") continue;
  }

  $selects = "pt2.procedure_type, pt2.procedure_code, pt2.units AS pt2_units, " .
    "pt2.range AS pt2_range, pt2.procedure_type_id AS procedure_type_id, " .
    "pt2.name AS name, pt2.description, pt2.seq AS seq, " .
    "ps.procedure_result_id, ps.result_code AS result_code, ps.result_text, ps.abnormal, ps.result, " .
    "ps.range, ps.result_status, ps.facility, ps.comments, ps.units, ps.comments";

  // procedure_type_id for order:
  $pt2cond = "pt2.parent = $order_type_id AND " .
    "(pt2.procedure_type LIKE 'res%' OR pt2.procedure_type LIKE 'rec%')";

  // pr.procedure_report_id or 0 if none:
  $pscond = "ps.procedure_report_id = $report_id";

  $joincond = "ps.result_code = pt2.procedure_code";

  // This union emulates a full outer join. The idea is to pick up all
  // result types defined for this order type, as well as any actual
  // results that do not have a matching result type.
  $query = "(SELECT $selects FROM procedure_type AS pt2 " .
    "LEFT JOIN procedure_result AS ps ON $pscond AND $joincond " .
    "WHERE $pt2cond" .
    ") UNION (" .
    "SELECT $selects FROM procedure_result AS ps " .
    "LEFT JOIN procedure_type AS pt2 ON $pt2cond AND $joincond " .
    "WHERE $pscond) " .
    "ORDER BY seq, name, procedure_type_id, result_code";

  $rres = sqlStatement($query);
  while ($rrow = sqlFetchArray($rres)) {
    $restyp_code      = empty($rrow['procedure_code'  ]) ? '' : $rrow['procedure_code'];
    $restyp_type      = empty($rrow['procedure_type'  ]) ? '' : $rrow['procedure_type'];
    $restyp_name      = empty($rrow['name'            ]) ? '' : $rrow['name'];
    $restyp_units     = empty($rrow['pt2_units'       ]) ? '' : $rrow['pt2_units'];
    $restyp_range     = empty($rrow['pt2_range'       ]) ? '' : $rrow['pt2_range'];

    $result_id        = empty($rrow['procedure_result_id']) ? 0 : ($rrow['procedure_result_id'] + 0);
    $result_code      = empty($rrow['result_code'     ]) ? $restyp_code : $rrow['result_code'];
    $result_text      = empty($rrow['result_text'     ]) ? $restyp_name : $rrow['result_text'];
    $result_abnormal  = empty($rrow['abnormal'        ]) ? '' : $rrow['abnormal'];
    $result_result    = empty($rrow['result'          ]) ? '' : $rrow['result'];
    $result_units     = empty($rrow['units'           ]) ? $restyp_units : $rrow['units'];
    $result_facility  = empty($rrow['facility'        ]) ? '' : $rrow['facility'];
    $result_comments  = empty($rrow['comments'        ]) ? '' : $rrow['comments'];
    $result_range     = empty($rrow['range'           ]) ? $restyp_range : $rrow['range'];
    $result_status    = empty($rrow['result_status'   ]) ? '' : $rrow['result_status'];

    // If there is more than one line of comments, everything after that is "notes".
    $result_notes = '';
    $i = strpos($result_comments, "\n");
    if ($i !== FALSE) {
      $result_notes = trim(substr($result_comments, $i + 1));
      $result_comments = substr($result_comments, 0, $i);
    }
    $result_comments = trim($result_comments);

    if($result_facility <> "" && !in_array($result_facility, $facilities)) {
      $facilities[] = $result_facility;
    }

    if ($lastpoid != $order_id || $lastpcid != $order_seq) {
      ++$encount;
      $lastrcn = '';
    }
    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

    echo " <tr class='detail' bgcolor='$bgcolor'>\n";

    // Generate first 2 columns.
    if ($lastpoid != $order_id || $lastpcid != $order_seq) {
      $lastprid = -1; // force report fields on first line of each procedure
      if ($form_batch) {
        if ($lastpoid != $order_id) {
          $tmp = $row['lname'];
          if ($row['fname'] || $row['mname'])
            $tmp .= ', ' . $row['fname'] . ' ' . $row['mname'];
          echo "  <td>" . text($tmp) . "</td>\n";
          echo "  <td>" . text($row['pubpid']) . "</td>\n";
        }
        else {
          echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
        }
      }
      else {
        if ($lastpoid != $order_id) {
          echo "  <td>" . $row['date_ordered'] . "</td>\n";
        }
        else {
          echo "  <td style='background-color:transparent'>&nbsp;</td>";
        }
        echo "  <td>" . text($row['procedure_name']) . "</td>\n";
      }
    }
    else {
      echo "  <td colspan='2' style='background-color:transparent'>&nbsp;</td>";
    }

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
        " id='form_date_report[$lino]' class='celltextfw' value='" . attr($date_report) . "' " .
        " title='" . xl('Date of this report') . "'" .
        " onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'" .
        " />";
      echo "<span class='bold' id='q_date_report[$lino]' style='cursor:pointer' " .
        "title='" . xl('Click here to choose a date') . "' />?</span>";
      echo "</td>\n";

      echo "  <td nowrap>";
      echo "<input type='text' size='13' name='form_date_collected[$lino]'" .
        " id='form_date_collected[$lino]'" .
        " class='celltextfw' value='" . attr($date_collected) . "' " .
        " title='" . xl('Date and time of sample collection') . "'" .
        " onkeyup='datekeyup(this,mypcc,true)' onblur='dateblur(this,mypcc,true)'" .
        " />";
      echo "<span class='bold' id='q_date_collected[$lino]' style='cursor:pointer' " .
        "title='" . xl('Click here to choose a date and time') . "' />?</span>";
      echo "</td>\n";

      echo "  <td>";
      echo "<input type='text' size='8' name='form_specimen_num[$lino]'" .
        " class='celltext' value='" . attr($specimen_num) . "' " .
        " title='" . xl('Specimen number/identifier') . "'" .
        " />";
      echo "</td>\n";

      echo "  <td>";
      echo generate_select_list("form_report_status[$lino]", 'proc_rep_status',
        $report_status, xl('Report Status'), ' ', 'cellselect');
      echo "</td>\n";
    }
    else {
      echo "  <td colspan='4' style='background-color:transparent'>&nbsp;</td>\n";
    }

    echo "  <td nowrap>";
    echo "<input type='text' size='6' name='form_result_code[$lino]'" .
      " class='celltext' value='" . attr($result_code) . "' />" .
      "</td>\n";

    echo "  <td>" .
      "<input type='text' size='16' name='form_result_text[$lino]'" .
      " class='celltext' value='" . attr($result_text) . "' />";
      "</td>\n";

    echo "  <td>";
    echo generate_select_list("form_result_abnormal[$lino]", 'proc_res_abnormal',
      $result_abnormal, xl('Indicates abnormality'), ' ', 'cellselect');
    echo "</td>\n";

    echo "  <td>";
    if ($result_units == 'bool') {
      echo "&nbsp;--";
    }
    else {
      echo "<input type='text' size='7' name='form_result_result[$lino]'" .
        " class='celltext' value='" . attr($result_result) . "' " .
        " />";
    }
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='text' size='4' name='form_result_units[$lino]'" .
      " class='celltext' value='" . attr($result_units) . "' " .
      " title='" . xl('Units applicable to the result value') . "'" .
      " />";
    echo "</td>\n";

    echo "  <td>";
    echo "<input type='text' size='8' name='form_result_range[$lino]'" .
      " class='celltext' value='" . attr($result_range) . "' " .
      " title='" . xl('Reference range of results') . "'" .
      " />";
    // Include a hidden form field containing all IDs for this line.
    echo "<input type='hidden' name='form_line[$lino]' " .
      "value='$order_id:$order_seq:$report_id:$result_id' />";
    echo "</td>\n";

    echo "  <td class='bold' style='cursor:pointer' " .
      "onclick='extShow($lino, this)' align='center' " .
      "title='" . xl('Click here to view/edit more details') . "'>";
    echo "&nbsp;?&nbsp;";
    echo "</td>\n";

    echo " </tr>\n";

    // Create a floating div for additional attributes of this result.
    $extra_html .= "<div id='ext_$lino' " .
      "style='position:absolute;width:750px;border:1px solid black;" .
      "padding:2px;background-color:#cccccc;visibility:hidden;" .
      "z-index:1000;left:-1000px;top:0px;font-size:9pt;'>\n" .
      "<table width='100%'>\n" .
      "<tr><td class='bold' align='center' colspan='2' style='padding:4pt 0 4pt 0'>" .
      htmlspecialchars($result_text) .
      "</td></tr>\n" .
      "<tr><td class='bold' width='1%' nowrap>" . xlt('Status') . ": </td>" .
      "<td>" . generate_select_list("form_result_status[$lino]", 'proc_res_status',
        $result_status, xl('Result Status'), '') . "</td></tr>\n" .
      "<tr><td class='bold' nowrap>" . xlt('Facility') . ": </td>" .
      "<td><input type='text' size='15' name='form_facility[$lino]'" .
      " value='$result_facility' " .
      " title='" . xla('Supplier facility name') . "'" .
      " style='width:100%' /></td></tr>\n" .
      "<tr><td class='bold' nowrap>" . xlt('Comments') . ": </td>" .
      "<td><textarea rows='3' cols='15' name='form_comments[$lino]'" .
      " title='" . xla('Comments for this result or recommendation') . "'" .
      " style='width:100%' />" . htmlspecialchars($result_comments) .
      "</textarea></td></tr>\n" .
      "<tr><td class='bold' nowrap>" . xlt('Notes') . ": </td>" .
      "<td><textarea rows='4' cols='15' name='form_notes[$lino]'" .
      " title='" . xla('Additional notes for this result or recommendation') . "'" .
      " style='width:100%' />" . htmlspecialchars($result_notes) .
      "</textarea></td></tr>\n" .
      "</table>\n" .
      "<p><center><input type='button' value='" . xla('Close') . "' " .
      "onclick='extShow($lino, false)' /></center></p>\n".
      "</div>";

    $lastpoid = $order_id;
    $lastpcid = $order_seq;
    $lastprid = $report_id;
    ++$lino;
  }
}

if (!empty($facilities)) {
  // display facility information
  $extra_html .= "<table>";
  $extra_html .= "<tr><th>". xl('Performing Laboratory Facility') . "</th></tr>";
  foreach($facilities as $facilityID) {
    foreach(explode(":", $facilityID) as $lab_facility) {
      $facility_array = getFacilityInfo($lab_facility);
      if($facility_array) {
        $extra_html .=
          "<tr><td><hr></td></tr>" .
          "<tr><td>". htmlspecialchars($facility_array['fname']) . " " . htmlspecialchars($facility_array['lname']) . ", " . htmlspecialchars($facility_array['title']). "</td></tr>" .
          "<tr><td>". htmlspecialchars($facility_array['organization']) . "</td></tr>" .
          "<tr><td>". htmlspecialchars($facility_array['street']) . " " .htmlspecialchars($facility_array['city']) . " " . htmlspecialchars($facility_array['state']) . "</td></tr>" .
          "<tr><td>". htmlspecialchars(formatPhone($facility_array['phone'])) . "</td></tr>";
      }
    }
  }
  $extra_html .= "</table>\n";
}
?>

</table>

<?php
if ($form_review) {
 // if user authorized for pending review.
 if ($reviewauth) {
 ?>
  <center><p>
   <input type='submit' name='form_submit' value='<?php xl('Sign Results','e'); ?>' />
  </p></center>
 <?php
 }
 else {
 ?>
  <center><p>
   <input type='button' name='form_submit' value='<?php xl('Sign Results','e'); ?>' onclick="alert('<?php xl('Not authorized','e') ?>');" />
  </p></center>
 <?php
 }
}
else {
?>
 <center><p>
  <input type='submit' name='form_submit' value='<?php xl('Save','e'); ?>' />
 </p></center>
<?php
}
?>

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
