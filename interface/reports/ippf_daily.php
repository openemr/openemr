<?php
// This module creates the Barbados Daily Record.

include_once("../globals.php");
include_once("../../library/patient.inc");
include_once("../../library/acl.inc");

// Might want something different here.
//
if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date     = fixDate($_POST['form_from_date']);
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_output   = isset($_POST['form_output']) ? 0 + $_POST['form_output'] : 1;

$report_title = xl('Clinic Daily Record');

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

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
else { // not export
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

<form name='theform' method='post'
 action='ippf_daily.php?t=<?php echo $report_type ?>'>

<table border='0' cellspacing='5' cellpadding='1'>
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
 echo "   <select name='form_facility'>\n";
 echo "    <option value=''>-- All Facilities --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "    <option value='$facid'";
  if ($facid == $_POST['form_facility']) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "   </select>\n";
?>
  </td>
  <td colspan='2' class='detail' nowrap>
   <?php xl('Date','e'); ?>
   <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Report date yyyy-mm-dd' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
  </td>
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

  $lores = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'contrameth' ORDER BY title");
  while ($lorow = sqlFetchArray($lores)) {
    $areport[$lorow['option_id']] = array($lorow['title'],
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
  }
  $areport['zzz'] = array('Unknown', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

  // This gets us all MA codes, with encounter and patient
  // info attached and grouped by patient and encounter.
  $query = "SELECT " .
    "fe.pid, fe.encounter, fe.date AS encdate, fe.pc_catid, " .
    "pd.regdate, b.code_type, b.code " .
    "FROM form_encounter AS fe " .
    "JOIN patient_data AS pd ON pd.pid = fe.pid " .
    "LEFT JOIN billing AS b ON " .
    "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
    "AND b.code_type = 'MA' " .
    "WHERE fe.date >= '$from_date 00:00:00' AND " .
    "fe.date <= '$from_date 23:59:59' ";

  if ($form_facility) {
    $query .= "AND fe.facility_id = '$form_facility' ";
  }
  $query .= "ORDER BY fe.pid, fe.encounter, b.code";
  $res = sqlStatement($query);

  $last_pid = '0';
  $last_contra_pid = '0';
  $last_encounter = '0';
  $method = '';

  while ($row = sqlFetchArray($res)) {
    if ($row['code_type'] === 'MA') {

      // Logic for individual patients.
      //
      if ($row['pid'] != $last_pid) { // new patient
        $last_pid = $row['pid'];

        $crow = sqlQuery("SELECT lc.new_method " .
          "FROM lists AS l, lists_ippf_con AS lc WHERE " .
          "l.pid = '$last_pid' AND l.begdate <= '$from_date' AND " .
          "( l.enddate IS NULL OR l.enddate > '$from_date' ) AND " .
          "l.activity = 1 AND l.type = 'contraceptive' AND lc.id = l.id " .
          "ORDER BY l.begdate DESC LIMIT 1");
        $amethods = explode('|', empty($crow) ? 'zzz' : $crow['new_method']);

        // TBD: We probably want to select the method with highest CYP here,
        // but for now we'll settle for the first one that appears.
        $method = $amethods[0];

        if (empty($areport[$method])) {
          // This should not happen.
          $areport[$method] = array("Unlisted method '$method'",
            0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        }

        // Count total clients.
        ++$areport[$method][3];

        // Count as new or old client.
        if ($row['regdate'] == $from_date) {
          ++$areport[$method][1];
        } else {
          ++$areport[$method][2];
        }

        // Maybe count as old Client First Visit this year.
        $regyear = substr($row['regdate'], 0, 4);
        $thisyear = substr($from_date, 0, 4);
        if ($regyear && $regyear < $thisyear) {
          $trow = sqlQuery("SELECT count(*) AS count FROM form_encounter " .
            "WHERE date >= '$thisyear-01-01 00:00:00' AND " .
            "date < '" . $row['encdate'] . " 00:00:00'");
          if (empty($trow['count'])) ++$areport[$method][5];
        }
      } // end new patient

      // Logic for visits.
      //
      if ($row['encounter'] != $last_encounter) { // new visit
        $last_encounter = $row['encounter'];

        // Count unique clients coming for supply or re-supply.
        if ($row['pc_catid'] == '10' && $last_pid != $last_contra_pid) {
          $last_contra_pid = $last_pid;
          ++$areport[$method][4];
        }
      }

      // Logic for specific services.
      //
      $code = 0 + $row['code'];
      if ($code == 255004) ++$areport[$method][6];  // pap smear
      if ($code == 256101) ++$areport[$method][7];  // preg test
      if ($code == 375008) ++$areport[$method][8];  // dr's check
      if ($code == 375014) ++$areport[$method][9];  // dr's visit (was 375013)
      if ($code == 375011) ++$areport[$method][10]; // advice     (was 009903)
      if ($code == 019916) ++$areport[$method][11]; // couns by method
      if ($code == 039916) ++$areport[$method][12]; // infert couns
      if ($code == 019911) ++$areport[$method][13]; // std/aids couns
    }
  } // end while

  if ($form_output != 3) {
    echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
  } // end not csv export

  // Generate headings.
  genStartRow("bgcolor='#dddddd'");
  genHeadCell(xl('Method'         ));
  genHeadCell(xl('New Clients'    ), true);
  genHeadCell(xl('Old Clients'    ), true);
  genHeadCell(xl('Total Clients'  ), true);
  genHeadCell(xl('Contra Clients' ), true);
  genHeadCell(xl('O.A.F.V.'       ), true);
  genHeadCell(xl('Pap Smear'      ), true);
  genHeadCell(xl('Preg Test'      ), true);
  genHeadCell(xl('Dr Check'       ), true);
  genHeadCell(xl('Dr Visit'       ), true);
  genHeadCell(xl('Advice'         ), true);
  genHeadCell(xl('Couns by Method'), true);
  genHeadCell(xl('Infert Couns'   ), true);
  genHeadCell(xl('STD/AIDS Couns' ), true);
  genEndRow();

  $encount = 0;

  foreach ($areport as $key => $varr) {
    $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
    genStartRow("bgcolor='$bgcolor'");
    genAnyCell($varr[0], false, 'detail');
    // Generate data and accumulate totals for this row.
    for ($cnum = 0; $cnum < 13; ++$cnum) {
      genNumCell($varr[$cnum + 1], $cnum);
    }
    genEndRow();
  } // end foreach

  if ($form_output != 3) {
    // Generate the line of totals.
    genStartRow("bgcolor='#dddddd'");
    genHeadCell(xl('Totals'));
    for ($cnum = 0; $cnum < 13; ++$cnum) {
      genHeadCell($atotals[$cnum], true);
    }
    genEndRow();
    // End of table.
    echo "</table>\n";
  }

} // end if submit

if ($form_output != 3) {
?>
</form>
</center>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
<?php if ($form_output == 2) { ?>
 window.print();
<?php } ?>
</script>

</body>
</html>
<?php
} // end not export
?>
