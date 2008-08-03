<?php
// This module creates statistical reports related to family planning
// and sexual and reproductive health.

include_once("../globals.php");
include_once("../../library/patient.inc");
include_once("../../library/acl.inc");

// Might want something different here.
//
if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$report_type = empty($_GET['t']) ? 'i' : $_GET['t'];

$from_date     = fixDate($_POST['form_from_date']);
$to_date       = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_by       = $_POST['form_by'];     // this is a scalar
$form_show     = $_POST['form_show'];   // this is an array
$form_sexes    = $_POST['form_sexes'];  // this is a scalar
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';

if (empty($form_by))    $form_by = '1';
if (empty($form_show))  $form_show = array('1');
if (empty($form_sexes)) $form_sexes = '3';

// One of these is chosen as the left column, or Y-axis, of the report.
//
if ($report_type == 'm') {
  $arr_by = array(
    101 => xl('MA Category'),
    102 => xl('Specific Service'),
    17  => xl('Patient'),
    9   => xl('Referrals'),
  );
}
else {
  $arr_by = array(
    1  => xl('General Service Category'),
    // 2  => xl('Gynecology/Obstretrics'),
    // 3  => xl('Urology'),
    4  => xl('Specific Service'),
    5  => xl('Abortion Method'),
    6  => xl('Contraceptive Method'),
    7  => xl('Con. Method Following Abortion'),
    8  => xl('Post-Abortion Care by Source'),
    9  => xl('Referrals'),
    // 10 => xl('Abortion Referral Followups'), // duplicates #8?
    11 => xl('Complications of Abortion'),
  );
}

// A reported value is either scalar, or an array listed horizontally.  If
// multiple items are chosen then each starts in the next available column.
//
$arr_show = array(
  1 => xl('Total Services'),
  2 => xl('Age Category'),
  3 => xl('Sex'),
  4 => xl('Religion'),
  5 => xl('Nationality'),
  6 => xl('Marital Status'),
  7 => xl('State/Parish'),
  8 => xl('Occupation'),
);

// These are ICD9 codes that indicate complications of abortion.
// Emailed to Rod by AM on 2008-03-19.
//   A = Incomplete abortion or retention of ovular products
//   B = Excessive bleeding/hemorrhage
//   C = Trauma to vagina, cervix, or uterus
//   D = Shock
//   E = Infection
//   F = Continuing pregnancy
//   G = Ectopic pregnancy
//   H = Other complications
// They really want to use ICD10 instead, but the decision was to do
// that later.
$arr_dx_attrs = array(
  '632'    => 'A',
  '634'    => 'E',
  '634.01' => 'EA',
  '634.02' => 'E',
  '634.1'  => 'B',
  '634.11' => 'BA',
  '634.12' => 'B',
  '634.2'  => 'C',
  '634.21' => 'CA',
  '634.22' => 'C',
  '634.3'  => 'D',
  '634.31' => 'DA',
  '634.32' => 'D',
  '634.4'  => 'H',
  '634.41' => 'HA',
  '634.42' => 'H',
  '634.5'  => 'D',
  '634.51' => 'DA',
  '634.52' => 'D',
  '634.6'  => 'D',
  '634.61' => 'DA',
  '634.62' => 'D',
  '634.7'  => 'H',
  '634.71' => 'HA',
  '634.72' => 'H',
  '634.8'  => 'H',
  '634.81' => 'HA',
  '634.82' => 'H',
  '634.91' => 'A',
  '635'    => 'E',
  '635.01' => 'EA',
  '635.02' => 'E',
  '635.1'  => 'B',
  '635.11' => 'BA',
  '635.12' => 'B',
  '635.2'  => 'C',
  '635.21' => 'CA',
  '635.22' => 'C',
  '635.3'  => 'D',
  '635.31' => 'DA',
  '635.32' => 'D',
  '635.4'  => 'H',
  '635.41' => 'HA',
  '635.42' => 'H',
  '635.5'  => 'D',
  '635.51' => 'DA',
  '635.52' => 'D',
  '635.6'  => 'D',
  '635.61' => 'DA',
  '635.62' => 'D',
  '635.7'  => 'H',
  '635.71' => 'HA',
  '635.72' => 'H',
  '635.8'  => 'H',
  '635.81' => 'HA',
  '635.82' => 'H',
  '635.91' => 'A',
  '636'    => 'E',
  '636.01' => 'EA',
  '636.02' => 'E',
  '636.1'  => 'B',
  '636.11' => 'BA',
  '636.12' => 'B',
  '636.2'  => 'C',
  '636.21' => 'CA',
  '636.22' => 'C',
  '636.3'  => 'D',
  '636.31' => 'DA',
  '636.32' => 'D',
  '636.4'  => 'H',
  '636.41' => 'HA',
  '636.42' => 'H',
  '636.5'  => 'D',
  '636.51' => 'DA',
  '636.52' => 'D',
  '636.6'  => 'D',
  '636.61' => 'DA',
  '636.62' => 'D',
  '636.7'  => 'H',
  '636.71' => 'HA',
  '636.72' => 'H',
  '636.8'  => 'H',
  '636.81' => 'HA',
  '636.82' => 'H',
  '636.91' => 'A',
  '637'    => 'E',
  '637.01' => 'AE',
  '637.02' => 'E',
  '637.1'  => 'B',
  '637.11' => 'AB',
  '637.12' => 'B',
  '637.2'  => 'C',
  '637.21' => 'AC',
  '637.22' => 'C',
  '637.3'  => 'D',
  '637.31' => 'AD',
  '637.32' => 'D',
  '637.4'  => 'H',
  '637.41' => 'AH',
  '637.42' => 'H',
  '637.5'  => 'D',
  '637.51' => 'AD',
  '637.52' => 'D',
  '637.6'  => 'D',
  '637.61' => 'AD',
  '637.62' => 'D',
  '637.7'  => 'H',
  '637.71' => 'AH',
  '637.72' => 'H',
  '637.8'  => 'H',
  '637.81' => 'AH',
  '637.82' => 'H',
  '637.9'  => 'H',
  '637.91' => 'A',
  '638'    => 'EF',
  '638.1'  => 'BF',
  '638.2'  => 'CF',
  '638.3'  => 'DF',
  '638.4'  => 'HF',
  '638.5'  => 'DF',
  '638.6'  => 'DF',
  '638.7'  => 'HF',
  '638.8'  => 'HF',
  '638.9'  => 'F',
  '639'    => 'Eg', // lower case means "possibly" ... how to handle that?
  '639.1'  => 'Bg',
  '639.2'  => 'Cg',
  '639.3'  => 'Dg',
  '639.4'  => 'Hg',
  '639.5'  => 'Dg',
  '639.6'  => 'Dg',
  '639.8'  => 'Hg',
  '639.9'  => 'Hg',
  '640'    => 'A',
  '640.01' => 'A',
  '640.03' => 'A',
);

// This will become the array of reportable values.
$areport = array();

// This accumulates the bottom line totals.
$atotals = array();

// Arrays of titles for some column headings.
$arr_titles = array(
  'rel' => array(),
  'nat' => array(),
  'mar' => array(),
  'sta' => array(),
  'occ' => array(),
  // 'met' => array(),
  // 'toc' => array(),
);

// This tracks the descriptive name of the last abortion method
// encountered for the current patient.
$last_abortion_method = '';

// This is so we know when the above-mentioned current patient changes.
$last_pid = '';

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
  global $cellcount;
  if (! $_POST['form_csvexport']) echo " <tr $att>\n";
  $cellcount = 0;
}

function genEndRow() {
  if ($_POST['form_csvexport']) {
    echo "\n";
  }
  else {
    echo " </tr>\n";
  }
}

function genAnyCell($data, $right=false, $class='') {
  global $cellcount;
  if ($_POST['form_csvexport']) {
    if ($cellcount) echo ',';
    echo '"' . $data . '"';
  }
  else {
    echo "  <td";
    if ($class) echo " class='$class'";
    if ($right) echo " align='right'";
    echo ">$data</td>\n";
  }
  ++$cellcount;
}

function genHeadCell($data, $right=false) {
  genAnyCell($data, $right, 'dehead');
}

// Create an HTML table cell containing a numeric value, and track totals.
//
function genNumCell($num, $cnum) {
  global $atotals;
  $atotals[$cnum] += $num;
  if (empty($num) && !$_POST['form_csvexport']) $num = '&nbsp;';
  genAnyCell($num, true, 'detail');
}

// Translate an IPPF code to the corresponding descriptive name of its
// contraceptive method, or to an empty string if none applies.
//
function getContraceptiveMethod($code) {
  $key = '';
  if (preg_match('/^111101/', $code)) {
    $key = xl('Pills');
  }
  else if (preg_match('/^11111[1-9]/', $code)) {
    $key = xl('Injectables');
  }
  else if (preg_match('/^11112[1-9]/', $code)) {
    $key = xl('Implants');
  }
  else if (preg_match('/^111132/', $code)) {
    $key = xl('Patch');
  }
  else if (preg_match('/^111133/', $code)) {
    $key = xl('Vaginal Ring');
  }
  else if (preg_match('/^112141/', $code)) {
    $key = xl('Male Condoms');
  }
  else if (preg_match('/^112142/', $code)) {
    $key = xl('Female Condoms');
  }
  else if (preg_match('/^11215[1-9]/', $code)) {
    $key = xl('Diaphragms/Caps');
  }
  else if (preg_match('/^11216[1-9]/', $code)) {
    $key = xl('Spermicides');
  }
  else if (preg_match('/^11317[1-9]/', $code)) {
    $key = xl('IUD');
  }
  else if (preg_match('/^145212/', $code)) {
    $key = xl('Emergency Contraception');
  }
  else if (preg_match('/^121181.13/', $code)) {
    $key = xl('Female VSC');
  }
  else if (preg_match('/^122182.13/', $code)) {
    $key = xl('Male VSC');
  }
  else if (preg_match('/^131191.10/', $code)) {
    $key = xl('Awareness-Based');
  }
  return $key;
}

// Translate an IPPF code to the corresponding descriptive name of its
// abortion method, or to an empty string if none applies.
//
function getAbortionMethod($code) {
  $key = '';
  if (preg_match('/^25222[34]/', $code)) {
    if (preg_match('/^2522231/', $code)) {
      $key = xl('D&C');
    }
    else if (preg_match('/^2522232/', $code)) {
      $key = xl('D&E');
    }
    else if (preg_match('/^2522233/', $code)) {
      $key = xl('MVA');
    }
    else if (preg_match('/^252224/', $code)) {
      $key = xl('Medical');
    }
    else {
      $key = xl('Other Surgical');
    }
  }
  return $key;
}

// Helper function called after the reporting key is determined for a row.
//
function loadColumnData($key, $row) {
  global $areport, $arr_titles;

  // If first instance of this key, initialize its arrays.
  if (empty($areport[$key])) {
    $areport[$key] = array();
    $areport[$key]['wom'] = 0;       // number of services for women
    $areport[$key]['men'] = 0;       // number of services for men
    $areport[$key]['age'] = array(0,0,0,0,0,0,0,0,0); // age array
    $areport[$key]['rel'] = array(); // religion array
    $areport[$key]['nat'] = array(); // nationality array
    $areport[$key]['mar'] = array(); // marital status array
    $areport[$key]['sta'] = array(); // state/parish array
    $areport[$key]['occ'] = array(); // occupation array
  }

  // Increment the correct sex category.
  if (strcasecmp($row['sex'], 'Male') == 0)
    ++$areport[$key]['men'];
  else
    ++$areport[$key]['wom'];

  // Increment the correct age category.
  $age = getAge(fixDate($row['DOB']), $row['encdate']);
  $i = min(intval(($age - 5) / 5), 8);
  if ($age < 11) $i = 0;
  ++$areport[$key]['age'][$i];

  // Increment the correct religion category.
  $religion = empty($row['userlist5']) ? 'Unspecified' : $row['userlist5'];
  $areport[$key]['rel'][$religion] += 1;
  $arr_titles['rel'][$religion] += 1;

  // Increment the correct nationality category.
  $nationality = empty($row['country_code']) ? 'Unspecified' : $row['country_code'];
  $areport[$key]['nat'][$nationality] += 1;
  $arr_titles['nat'][$nationality] += 1;

  // Increment the correct marital status category.
  $status = empty($row['status']) ? 'Unspecified' : $row['status'];
  $areport[$key]['mar'][$status] += 1;
  $arr_titles['mar'][$status] += 1;

  // Increment the correct state/parish category.
  $status = empty($row['state']) ? 'Unspecified' : $row['state'];
  $areport[$key]['sta'][$status] += 1;
  $arr_titles['sta'][$status] += 1;

  // Increment the correct occupation category.
  $status = empty($row['occupation']) ? 'Unspecified' : $row['occupation'];
  $areport[$key]['occ'][$status] += 1;
  $arr_titles['occ'][$status] += 1;
}

// This is called for each IPPF service code that is selected.
//
function process_ippf_code($row, $code) {
  global $areport, $arr_titles, $form_by, $last_abortion_method, $last_pid;

  // This tracks the last abortion method for the current patient.
  // The reason for this is that when we encounter a diagnosis code
  // for complication of abortion, we will know what the corresponding
  // abortion method was.
  if ($row['pid'] != $last_pid) {
    $last_abortion_method = '';
    $last_pid = $row['pid'];
  }
  $abortion_method = getAbortionMethod($code);
  if ($abortion_method) $last_abortion_method = $abortion_method;

  $key = 'Unspecified';

  // General Service Category.
  //
  if ($form_by === '1') {
    if (preg_match('/^1/', $code)) {
      $key = xl('SRH - Family Planning');
    }
    else if (preg_match('/^2/', $code)) {
      $key = xl('SRH Non Family Planning');
    }
    else if (preg_match('/^3/', $code)) {
      $key = xl('Non-SRH Medical');
    }
    else if (preg_match('/^4/', $code)) {
      $key = xl('Non-SRH Non-Medical');
    }
    else {
      $key = xl('Invalid Service Codes');
    }
  }

  // Specific Services. One row for each IPPF code.
  //
  else if ($form_by === '4') {
    $key = $code;
  }

  // Abortion Method.
  //
  else if ($form_by === '5') {
    if (!$abortion_method) return;
    $key = $abortion_method;
  }

  // Contraceptive Method.
  //
  else if ($form_by === '6') {
    $key = getContraceptiveMethod($code);
    if (empty($key)) return;
  }

  // Contraceptive method for new contraceptive adoption following abortion.
  //
  else if ($form_by === '7') {
    // Determine if this is a post-abortion care visit.
    if ($row['pc_catdesc'] !== 'fal' &&
        $row['pc_catdesc'] !== 'far' &&
        $row['pc_catdesc'] !== 'faw') return;
    // If yes, generate a separate row for each contraceptive method.
    $key = getContraceptiveMethod($code);
    if (empty($key)) return;
  }

  // Post-Abortion Care by Source.
  // Requirements just call for counting sessions, but this way the columns
  // can be anything - age category, religion, whatever.
  //
  else if ($form_by === '8') {
    // We generate a row for each type of post-abortion visit.
    // Skip all other visit categories.
    if      ($row['pc_catdesc'] === 'fal') $key = xl('For abortions at this clinic');
    else if ($row['pc_catdesc'] === 'far') $key = xl('For abortions referred out');
    else if ($row['pc_catdesc'] === 'faw') $key = xl('For outside abortions');
    else return;
  }

  // Patient Name.
  //
  else if ($form_by === '17') {
    $key = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
  }

  else {
    return;
  }

  // OK we now have the reporting key for this issue.

  loadColumnData($key, $row);
}

// This is called for each MA service code that is selected.
//
function process_ma_code($row) {
  global $form_by;

  $key = 'Unspecified';

  // One row for each service category.
  //
  if ($form_by === '101') {
    if (!empty($row['title'])) $key = xl($row['title']);
  }

  // Specific Services. One row for each MA code.
  //
  else if ($form_by === '102') {
    $key = $row['code'];
  }

  else {
    return;
  }

  loadColumnData($key, $row);
}

// This is called for each icd9 code that is selected.
//
function process_icd_code($row) {
  global $form_by, $last_abortion_method, $arr_dx_attrs;
  // echo "<!-- ICD9 = '" . $row['code'] . "' -->\n"; // debugging

  // One row for each service category.
  //
  if ($form_by === '11' && !empty($arr_dx_attrs[$row['code']])) {
    $attrs = $arr_dx_attrs[$row['code']];
    for ($i = 0; $i < strlen($attrs); ++$i) {
      $c = strtoupper(substr($attrs, $i, 1));
      if ($c == 'A') $ctype = 'Incomplete abortion or retention of ovular products';
      else if ($c == 'B') $ctype = 'Excessive bleeding/hemorrhage';
      else if ($c == 'C') $ctype = 'Trauma to vagina, cervix, or uterus';
      else if ($c == 'D') $ctype = 'Shock';
      else if ($c == 'E') $ctype = 'Infection';
      else if ($c == 'F') $ctype = 'Continuing pregnancy';
      else if ($c == 'G') $ctype = 'Ectopic pregnancy';
      else if ($c == 'H') $ctype = 'Other complications';
      else $ctype = '???';
      $key = $last_abortion_method ? $last_abortion_method : 'Unknown';
      $key .= ': ' . $ctype;
      loadColumnData($key, $row);
    }
  }
}

// This is called for each selected referral.
// Row keys are the first specified MA code, if any.
//
function process_referral($row) {
  $key = 'Unspecified';
  if (!empty($row['refer_related_code'])) {
    $relcodes = explode(';', $row['refer_related_code']);
    foreach ($relcodes as $codestring) {
      if ($codestring === '') continue;
      list($codetype, $code) = explode(':', $codestring);
      if ($codetype !== 'MA') continue;
      $key = $code;
      break;
    }
  }
  loadColumnData($key, $row);
}

  // If we are doing the CSV export then generate the needed HTTP headers.
  // Otherwise generate HTML.
  //
  if ($_POST['form_csvexport']) {
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
<?php html_header_show();?>
<title><?php xl('Service Statistics Report','e'); ?></title>
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
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><? xl('Service Statistics Report','e'); ?></h2>

<form name='theform' method='post' action='ippf_statistics.php?t=<?php echo $report_type ?>'>

<table border='0' cellspacing='0' cellpadding='2'>

 <tr>
  <td valign='top' nowrap>
   For each
  </td>
  <td valign='top'>
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
  <td align='center' valign='top' rowspan='3' nowrap
   ><input type='submit' name='form_refresh' value='<?php xl('Show','e'); ?>' title='<?php xl('Click to generate the report','e'); ?>'
   /><br /><?php xl('or','e'); ?><br
   /><input type='submit' name='form_csvexport' value='<?php xl('Export','e'); ?>' title='<?php xl('Click to downlaod in CSV format','e'); ?>'
   /></td>
  <td valign='top' rowspan='3'>
   <select name='form_show[]' size='4' multiple
    title='<?php xl('Hold down Ctrl to select multiple items','e'); ?>'>
<?php
  foreach ($arr_show as $key => $value) {
    echo "    <option value='$key'";
    if (is_array($form_show) && in_array($key, $form_show)) echo " selected";
    echo ">" . $value . "</option>\n";
  }
?>
   </select>
  </td>
  <td valign='top' rowspan='3' nowrap>
   &nbsp;
   for:
  </td>
  <td valign='top' rowspan='3'>
   <select name='form_sexes' title='<?php xl('To filter by sex','e'); ?>'>
<?php
  foreach (array(3 => xl('Men and Women'), 1 => xl('Women Only'), 2 => xl('Men Only')) as $key => $value) {
    echo "    <option value='$key'";
    if ($key == $form_sexes) echo " selected";
    echo ">$value</option>\n";
  }
?>
   </select>
   <br />
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
   <br />
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>
 <tr>
  <td valign='top' nowrap>
   from
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
  </td>
 </tr>
 <tr>
  <td valign='top' nowrap>
   to
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php
  } // end not export

  if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    $sexcond = '';
    if ($form_sexes == '1') $sexcond = "AND pd.sex NOT LIKE 'Male' ";
    else if ($form_sexes == '2') $sexcond = "AND pd.sex LIKE 'Male' ";

    if ($form_by === '9') {
      $query = "SELECT " .
        "t.refer_related_code, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, pd.userlist5, " .
        "pd.country_code, pd.status, pd.state, pd.occupation " .
        "FROM transactions AS t " .
        "JOIN patient_data AS pd ON pd.pid = t.pid $sexcond" .
        "WHERE t.title = 'Referral' AND t.refer_date >= '$from_date' AND " .
        "t.refer_date <= '$to_date' " .
        "ORDER BY t.pid, t.id";
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        process_referral($row);
      }
    }
    else {
      // This gets us all MA and ICD9 codes, with encounter and patient
      // info attached and grouped by patient and encounter.
      $query = "SELECT " .
        "fe.pid, fe.date AS encdate, opc.pc_catdesc, " .
        "pd.sex, pd.DOB, pd.lname, pd.fname, pd.mname, pd.userlist5, " .
        "pd.country_code, pd.status, pd.state, pd.occupation, " .
        "b.code_type, b.code, " .
        "c.related_code, lo.title " .
        "FROM form_encounter AS fe " .
        "JOIN patient_data AS pd ON pd.pid = fe.pid $sexcond" .
        "LEFT OUTER JOIN openemr_postcalendar_categories AS opc ON " .
        "opc.pc_catid = fe.pc_catid " .
        "LEFT OUTER JOIN billing AS b ON " .
        "b.pid = fe.pid AND b.encounter = fe.encounter AND b.activity = 1 " .
        "AND ( b.code_type = 'MA' OR b.code_type = 'ICD9' ) " .
        "LEFT OUTER JOIN codes AS c ON b.code_type = 'MA' AND c.code_type = '12' AND " .
        "c.code = b.code AND c.modifier = b.modifier " .
        "LEFT OUTER JOIN list_options AS lo ON " .
        "lo.list_id = 'superbill' AND lo.option_id = c.superbill " .
        "WHERE fe.date >= '$from_date 00:00:00' AND " .
        "fe.date <= '$to_date 23:59:59' ";
      if ($form_facility) {
        $query .= "AND fe.facility_id = '$form_facility' ";
      }
      $query .= "ORDER BY fe.pid, fe.encounter, b.code_type DESC, b.code";
      // Note: sorting to get MA codes before ICD9 codes.
      $res = sqlStatement($query);
      while ($row = sqlFetchArray($res)) {
        if ($row['code_type'] === 'MA') {
          process_ma_code($row);
          if (!empty($row['related_code'])) {
            $relcodes = explode(';', $row['related_code']);
            foreach ($relcodes as $codestring) {
              if ($codestring === '') continue;
              list($codetype, $code) = explode(':', $codestring);
              if ($codetype !== 'IPPF') continue;
              process_ippf_code($row, $code);
            }
          }
        }
        else {
          process_icd_code($row);
        }
      }
    }

    // Sort everything by key for reporting.
    ksort($areport);
    ksort($arr_titles['rel']);
    ksort($arr_titles['nat']);
    ksort($arr_titles['mar']);
    ksort($arr_titles['sta']);
    ksort($arr_titles['occ']);

    if (! $_POST['form_csvexport']) {
      echo "<table border='0' cellpadding='1' cellspacing='2' width='98%'>\n";
    } // end not csv export

    genStartRow("bgcolor='#dddddd'");
    genHeadCell($arr_by[$form_by]);

    // Generate headings for values to be shown.
    foreach ($form_show as $value) {
      if ($value == '1') { // Total Services
        genHeadCell(xl('Services'));
      }
      else if ($value == '2') { // Age
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
      else if ($value == '3') { // Sex
        genHeadCell(xl('Women'), true);
        genHeadCell(xl('Men'  ), true);
      }
      else if ($value == '4') { // Religion
        foreach ($arr_titles['rel'] as $key => $value) {
          genHeadCell($key, true);
        }
      }
      else if ($value == '5') { // Nationality
        foreach ($arr_titles['nat'] as $key => $value) {
          genHeadCell($key, true);
        }
      }
      else if ($value == '6') { // Marital Status
        foreach ($arr_titles['mar'] as $key => $value) {
          genHeadCell($key, true);
        }
      }
      else if ($value == '7') { // State/Parish
        foreach ($arr_titles['sta'] as $key => $value) {
          genHeadCell($key, true);
        }
      }
      else if ($value == '8') { // Occupation
        foreach ($arr_titles['occ'] as $key => $value) {
          genHeadCell($key, true);
        }
      }
    }

    if (! $_POST['form_csvexport']) {
      genHeadCell(xl('Total'), true);
    }

    genEndRow();

    $encount = 0;

    foreach ($areport as $key => $varr) {
      $bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

      $dispkey = $key;
      if ($form_by === '4') {
        // Append IPPF service descriptions to their codes.
        $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
          "code_type = '11' AND code = '$key' ORDER BY id LIMIT 1");
        if (!empty($crow['code_text'])) $dispkey .= ' ' . $crow['code_text'];
      }
      else if ($form_by === '102' || $form_by === '9') {
        // Append MA service descriptions to their codes.
        $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
          "code_type = '12' AND code = '$key' ORDER BY id LIMIT 1");
        if (!empty($crow['code_text'])) $dispkey .= ' ' . $crow['code_text'];
      }

      genStartRow("bgcolor='$bgcolor'");

      genAnyCell($dispkey, false, 'detail');

      // This is the column index for accumulating column totals.
      $cnum = 0;
      $totalsvcs = $areport[$key]['wom'] + $areport[$key]['men'];

      // Generate data for this row.
      foreach ($form_show as $value) {
        if ($value == '1') { // Total Services
          genNumCell($totalsvcs, $cnum++);
        }
        else if ($value == '2') { // Age
          for ($i = 0; $i < 9; ++$i) {
            genNumCell($areport[$key]['age'][$i], $cnum++);
          }
        }
        else if ($value == '3') { // Sex
          genNumCell($areport[$key]['wom'], $cnum++);
          genNumCell($areport[$key]['men'], $cnum++);
        }
        else if ($value == '4') { // Religion
          foreach ($arr_titles['rel'] as $title => $nothing) {
            genNumCell($areport[$key]['rel'][$title], $cnum++);
          }
        }
        else if ($value == '5') { // Nationality
          foreach ($arr_titles['nat'] as $title => $nothing) {
            genNumCell($areport[$key]['nat'][$title], $cnum++);
          }
        }
        else if ($value == '6') { // Marital Status
          foreach ($arr_titles['mar'] as $title => $nothing) {
            genNumCell($areport[$key]['mar'][$title], $cnum++);
          }
        }
        else if ($value == '7') { // State/Parish
          foreach ($arr_titles['sta'] as $title => $nothing) {
            genNumCell($areport[$key]['sta'][$title], $cnum++);
          }
        }
        else if ($value == '8') { // Occupation
          foreach ($arr_titles['occ'] as $title => $nothing) {
            genNumCell($areport[$key]['occ'][$title], $cnum++);
          }
        }
      }

      // Write the Total column data.
      if (! $_POST['form_csvexport']) {
        $atotals[$cnum] += $totalsvcs;
        genAnyCell($totalsvcs, true, 'dehead');
      }

      genEndRow();
    } // end foreach

    if (! $_POST['form_csvexport']) {
      // Generate the line of totals.
      genStartRow("bgcolor='#dddddd'");
      genHeadCell("Totals");
      for ($cnum = 0; $cnum < count($atotals); ++$cnum) {
        genHeadCell($atotals[$cnum], true);
      }
      genEndRow();
      // End of table.
      echo "</table>\n";
    }

  } // end of if refresh or export

  if (! $_POST['form_csvexport']) {
?>
</form>
</center>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
<?php
  } // end not export
?>
