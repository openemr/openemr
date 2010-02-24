<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This module creates statistical reports related to lab tests and
// other procedure orders.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/acl.inc");
require_once("../../custom/code_types.inc.php");

// Might want something different here.
//
if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date     = fixDate($_POST['form_from_date']);
$to_date       = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_by       = $_POST['form_by'];     // this is a scalar
$form_show     = $_POST['form_show'];   // this is an array
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_sexes    = isset($_POST['form_sexes']) ? $_POST['form_sexes'] : '3';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

if (empty($form_by))    $form_by = '4';
if (empty($form_show))  $form_show = array('1');

// One of these is chosen as the left column, or Y-axis, of the report.
//
$report_title = xl('Procedure Statistics Report');
$arr_by = array(
  4  => xl('Specific Result'),
  5  => xl('Followups Indicated'),
);

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

$arr_show   = array(
  // '.total' => array('title' => 'Total Positives'),
  '.tneg'  => array('title' => 'Total Negatives'),
  '.age'   => array('title' => 'Age Category'),
); // info about selectable columns

$arr_titles = array(); // will contain column headers

// Query layout_options table to generate the $arr_show table.
// Table key is the field ID.
$lres = sqlStatement("SELECT field_id, title, data_type, list_id, description " .
  "FROM layout_options WHERE " .
  "form_id = 'DEM' AND uor > 0 AND field_id NOT LIKE 'em%' " .
  "ORDER BY group_name, seq, title");
while ($lrow = sqlFetchArray($lres)) {
  $fid = $lrow['field_id'];
  if ($fid == 'fname' || $fid == 'mname' || $fid == 'lname') continue;
  $arr_show[$fid] = $lrow;
  $arr_titles[$fid] = array();
}

// Compute age in years given a DOB and "as of" date.
//
function getAge($dob, $asof='') {
  if (empty($asof)) $asof = date('Y-m-d');
  $a1 = explode('-', substr($dob , 0, 10));
  $a2 = explode('-', substr($asof, 0, 10));
  $age = $a2[0] - $a1[0];
  if ($a2[1] < $a1[1] || ($a2[1] == $a1[1] && $a2[2] < $a1[2])) --$age;
  // echo "<!-- $dob $asof $age -->\n"; // debugging
  return $age;
}

$cellcount = 0;

function genStartRow($att) {
  global $cellcount, $form_output;
  if ($form_output != 3) echo " <tr $att>\n";
  $cellcount = 0;
}

function genEndRow() {
  global $form_output;
  if ($form_output == 3) {
    echo "\n";
  }
  else {
    echo " </tr>\n";
  }
}

function getListTitle($list, $option) {
  $row = sqlQuery("SELECT title FROM list_options WHERE " .
    "list_id = '$list' AND option_id = '$option'");
  if (empty($row['title'])) return $option;
  return $row['title'];
}

// Usually this generates one cell, but allows for two or more.
//
function genAnyCell($data, $right=false, $class='') {
  global $cellcount, $form_output;
  if (!is_array($data)) {
    $data = array(0 => $data);
  }
  foreach ($data as $datum) {
    if ($form_output == 3) {
      if ($cellcount) echo ',';
      echo '"' . $datum . '"';
    }
    else {
      echo "  <td";
      if ($class) echo " class='$class'";
      if ($right) echo " align='right'";
      echo ">$datum</td>\n";
    }
    ++$cellcount;
  }
}

function genHeadCell($data, $right=false) {
  genAnyCell($data, $right, 'dehead');
}

// Create an HTML table cell containing a numeric value, and track totals.
//
function genNumCell($num, $cnum) {
  global $atotals, $form_output;
  $atotals[$cnum] += $num;
  if (empty($num) && $form_output != 3) $num = '&nbsp;';
  genAnyCell($num, true, 'detail');
}

// Helper function called after the reporting key is determined for a row.
//
function loadColumnData($key, $row) {
  global $areport, $arr_titles, $from_date, $to_date, $arr_show;

  // If no result, do nothing.
  if (empty($row['abnormal'])) return;

  // If first instance of this key, initialize its arrays.
  if (empty($areport[$key])) {
    $areport[$key] = array();
    $areport[$key]['.prp'] = 0;       // previous pid
    $areport[$key]['.wom'] = 0;       // number of positive results for women
    $areport[$key]['.men'] = 0;       // number of positive results for men
    $areport[$key]['.neg'] = 0;       // number of negative results
    $areport[$key]['.age'] = array(0,0,0,0,0,0,0,0,0); // age array
    foreach ($arr_show as $askey => $dummy) {
      if (substr($askey, 0, 1) == '.') continue;
      $areport[$key][$askey] = array();
    }
  }

  // Flag this patient as having been encountered for this report row.
  $areport[$key]['.prp'] = $row['pid'];

  // Collect abnormal results only, except for a column of total negatives.
  if ($row['abnormal'] == 'no') {
    ++$areport[$key]['.neg'];
    return;
  }

  // Increment the correct sex category.
  if (strcasecmp($row['sex'], 'Male') == 0)
    ++$areport[$key]['.men'];
  else
    ++$areport[$key]['.wom'];

  // Increment the correct age category.
  $age = getAge(fixDate($row['DOB']), $row['date_ordered']);
  $i = min(intval(($age - 5) / 5), 8);
  if ($age < 11) $i = 0;
  ++$areport[$key]['.age'][$i];

  // For each patient attribute to report, this increments the array item
  // whose key is the attribute's value.  This works well for list-based
  // attributes.  A key of "Unspecified" is used where the attribute has
  // no assigned value.
  foreach ($arr_show as $askey => $dummy) {
    if (substr($askey, 0, 1) == '.') continue;
    $status = empty($row[$askey]) ? 'Unspecified' : $row[$askey];
    $areport[$key][$askey][$status] += 1;
    $arr_titles[$askey][$status] += 1;
  }
}

// This is called for each row returned from the query.
//
function process_result_code($row) {
  global $areport, $arr_titles, $form_by;

  // Specific Results.  One row for each result name.
  //
  if ($form_by === '4') {
    $key = $row['order_name'] . ' / ' . $row['result_name'];
    loadColumnData($key, $row);
  }

  // Recommended followup services.
  //
  else if ($form_by === '5') {
    if (!empty($row['related_code'])) {
      $relcodes = explode(';', $row['related_code']);
      foreach ($relcodes as $codestring) {
        if ($codestring === '') continue;
        // list($codetype, $code) = explode(':', $codestring);
        // if ($codetype !== 'IPPF') continue;
        $key = $codestring;
        loadColumnData($key, $row);
      }
    }
  }

} // end function process_result_code()

  // If we are doing the CSV export then generate the needed HTTP headers.
  // Otherwise generate HTML.
  //
  if ($form_output == 3) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=service_statistics_report.csv");
    header("Content-Description: File Transfer");
  }
  else {
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo $report_title; ?></title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php echo $report_title; ?></h2>

<form name='theform' method='post' action='procedure_stats.php'>

<table border='0' cellspacing='5' cellpadding='1'>

 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Rows','e'); ?>:
  </td>
  <td valign='top' class='detail'>
   <select name='form_by' title='Left column of report'>
<?php
  foreach ($arr_by as $key => $value) {
    echo "    <option value='$key'";
    if ($key == $form_by) echo " selected";
    echo ">" . $value . "</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Filters','e'); ?>:
  </td>
  <td rowspan='2' colspan='2' class='detail'
   style='border-style:solid;border-width:1px;border-color:#cccccc'>
   <table>
    <tr>
     <td valign='top' class='detail' nowrap>
      <?php xl('Sex','e'); ?>:
     </td>
     <td class='detail' valign='top'>
      <select name='form_sexes' title='<?php xl('To filter by sex','e'); ?>'>
<?php
  foreach (array(3 => xl('Men and Women'), 1 => xl('Women Only'), 2 => xl('Men Only')) as $key => $value) {
    echo "       <option value='$key'";
    if ($key == $form_sexes) echo " selected";
    echo ">$value</option>\n";
  }
?>
      </select>
     </td>
    </tr>
    <tr>
     <td valign='top' class='detail' nowrap>
      <?php xl('Facility','e'); ?>:
     </td>
     <td valign='top' class='detail'>
<?php
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility ORDER BY name";
 $fres = sqlStatement($query);
 echo "      <select name='form_facility'>\n";
 echo "       <option value=''>-- All Facilities --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "       <option value='$facid'";
  if ($facid == $_POST['form_facility']) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "      </select>\n";
?>
     </td>
    </tr>
    <tr>
     <td colspan='2' class='detail' nowrap>
      <?php xl('From','e'); ?>
      <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
      <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
       id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
       title='<?php xl('Click here to choose a date','e'); ?>'>
      <?php xl('To','e'); ?>
      <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $to_date ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
      <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
       id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
       title='<?php xl('Click here to choose a date','e'); ?>'>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('Columns','e'); ?>:
  </td>
  <td valign='top' class='detail'>
   <select name='form_show[]' size='4' multiple
    title='<?php xl('Hold down Ctrl to select multiple items','e'); ?>'>
<?php
  foreach ($arr_show as $key => $value) {
    $title = $value['title'];
    if (empty($title) || $key == 'title') $title = $value['description'];
    echo "    <option value='$key'";
    if (is_array($form_show) && in_array($key, $form_show)) echo " selected";
    echo ">$title</option>\n";
  }
?>
   </select>
  </td>
 </tr>
 <tr>
  <td valign='top' class='dehead' nowrap>
   <?php xl('To','e'); ?>:
  </td>
  <td colspan='3' valign='top' class='detail' nowrap>
<?php
foreach (array(1 => 'Screen', 2 => 'Printer', 3 => 'Export File') as $key => $value) {
  echo "   <input type='radio' name='form_output' value='$key'";
  if ($key == $form_output) echo ' checked';
  echo " />$value &nbsp;";
}
?>
  </td>
  <td align='right' valign='top' class='detail' nowrap>
   <input type='submit' name='form_submit' value='<?php xl('Submit','e'); ?>'
    title='<?php xl('Click to generate the report','e'); ?>' />
  </td>
 </tr>
 <tr>
  <td colspan='5' height="1">
  </td>
 </tr>
</table>
<?php
  } // end not export

  if ($_POST['form_submit']) {
    $pd_fields = '';
    foreach ($arr_show as $askey => $asval) {
      if (substr($askey, 0, 1) == '.') continue;
      if ($askey == 'regdate' || $askey == 'sex' || $askey == 'DOB' ||
        $askey == 'lname' || $askey == 'fname' || $askey == 'mname' ||
        $askey == 'contrastart' || $askey == 'referral_source') continue;
      $pd_fields .= ', pd.' . $askey;
    }

    $sexcond = '';
    if ($form_sexes == '1') $sexcond = "AND pd.sex NOT LIKE 'Male' ";
    else if ($form_sexes == '2') $sexcond = "AND pd.sex LIKE 'Male' ";

    // This gets us all results, with encounter and patient
    // info attached and grouped by patient and encounter.
    $query = "SELECT " .
      "po.patient_id, po.encounter_id, po.date_ordered, " .
      "po.provider_id, pd.regdate, " .
      "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, " .
      "pd.contrastart, pd.referral_source$pd_fields, " .
      "ps.abnormal, ps.procedure_type_id AS result_type, " .
      "pto.name AS order_name, ptr.name AS result_name, ptr.related_code " .
      "FROM procedure_order AS po " .
      "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
      "JOIN patient_data AS pd ON pd.pid = po.patient_id $sexcond" .
      "JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
      "AND pr.date_report IS NOT NULL " .
      "JOIN procedure_result AS ps ON ps.procedure_report_id = pr.procedure_report_id " .
      "AND ps.result_status = 'final' " .
      "JOIN procedure_type AS pto ON pto.procedure_type_id = po.procedure_type_id " .
      "JOIN procedure_type AS ptr ON ptr.procedure_type_id = ps.procedure_type_id " .
      "AND ptr.procedure_type NOT LIKE 'rec' " .
      "WHERE po.date_ordered IS NOT NULL AND po.date_ordered >= '$from_date' " .
      "AND po.date_ordered <= '$to_date' ";
    if ($form_facility) {
      $query .= "AND fe.facility_id = '$form_facility' ";
    }
    $query .= "ORDER BY fe.pid, fe.encounter, ps.procedure_type_id"; // needed?

    $res = sqlStatement($query);

    while ($row = sqlFetchArray($res)) {
      process_result_code($row);
    }

    // Sort everything by key for reporting.
    ksort($areport);
    foreach ($arr_titles as $atkey => $dummy) ksort($arr_titles[$atkey]);

    if ($form_output != 3) {
      echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

    genStartRow("bgcolor='#dddddd'");

    // genHeadCell($arr_by[$form_by]);
    // If the key is an MA or IPPF code, then add a column for its description.
    if ($form_by === '5')
    {
      genHeadCell(array($arr_by[$form_by], xl('Description')));
    } else {
      genHeadCell($arr_by[$form_by]);
    }

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
      // if ($value == '.total') { // Total Positives
      //   genHeadCell(xl('Total'));
      // }
      if ($value == '.tneg') { // Total Negatives
        genHeadCell(xl('Negatives'));
      }
      else if ($value == '.age') { // Age
        genHeadCell(xl('0-10' ), true);
        genHeadCell(xl('11-14'), true);
        genHeadCell(xl('15-19'), true);
        genHeadCell(xl('20-24'), true);
        genHeadCell(xl('25-29'), true);
        genHeadCell(xl('30-34'), true);
        genHeadCell(xl('35-39'), true);
        genHeadCell(xl('40-44'), true);
        genHeadCell(xl('45+'  ), true);
      }
      else if ($arr_show[$value]['list_id']) {
        foreach ($arr_titles[$value] as $key => $dummy) {
          genHeadCell(getListTitle($arr_show[$value]['list_id'],$key), true);
        }
      }
      else if (!empty($arr_titles[$value])) {
        foreach ($arr_titles[$value] as $key => $dummy) {
          genHeadCell($key, true);
        }
      }
    }

    if ($form_output != 3) {
      genHeadCell(xl('Positives'), true);
    }

    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
      $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

      $dispkey = $key;

      // If the key is an MA or IPPF code, then get its description.
      if ($form_by === '5')
      {
        list($codetype, $code) = explode(':', $key);
        $type = $code_types[$codetype]['id'];
        $dispkey = array($key, '');
        $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
          "code_type = '$type' AND code = '$code' ORDER BY id LIMIT 1");
        if (!empty($crow['code_text'])) $dispkey[1] = $crow['code_text'];
      }

      genStartRow("bgcolor='$bgcolor'");

      genAnyCell($dispkey, false, 'detail');

      // This is the column index for accumulating column totals.
      $cnum = 0;
      $totalsvcs = $areport[$key]['.wom'] + $areport[$key]['.men'];

      // Generate data for this row.
      foreach ($form_show as $value) {
        // if ($value == '.total') { // Total Positives
        //   genNumCell($totalsvcs, $cnum++);
        // }
        if ($value == '.tneg') { // Total Negatives
          genNumCell($areport[$key]['.neg'], $cnum++);
        }
        else if ($value == '.age') { // Age
          for ($i = 0; $i < 9; ++$i) {
            genNumCell($areport[$key]['.age'][$i], $cnum++);
          }
        }
        else if (!empty($arr_titles[$value])) {
          foreach ($arr_titles[$value] as $title => $dummy) {
            genNumCell($areport[$key][$value][$title], $cnum++);
          }
        }
      }

      // Write the Total column data.
      if ($form_output != 3) {
        $atotals[$cnum] += $totalsvcs;
        genAnyCell($totalsvcs, true, 'dehead');
      }

      genEndRow();
    } // end foreach

    if ($form_output != 3) {
      // Generate the line of totals.
      genStartRow("bgcolor='#dddddd'");

      // genHeadCell(xl('Totals'));
      // If the key is an MA or IPPF code, then add a column for its description.
      if ($form_by === '5')
      {
        genHeadCell(array(xl('Totals'), ''));
      } else {
        genHeadCell(xl('Totals'));
      }

      for ($cnum = 0; $cnum < count($atotals); ++$cnum) {
        genHeadCell($atotals[$cnum], true);
      }
      genEndRow();
      // End of table.
      echo "</table>\n";
    }

  } // end of if refresh or export

  if ($form_output != 3) {
?>
</form>
</center>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php if ($form_output == 2) { ?>
 window.print();
<?php } ?>
</script>

</body>
</html>
<?php
  } // end not export
?>
