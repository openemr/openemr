<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
?>

<html>
<head>
<title><?php xl('HPI Lookup','e'); ?></title>
<link rel="stylesheet" href='<?php echo $GLOBALS['css_header'] ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function setHpi(hpi) {
	// alert("Opener: "+opener);
  if (opener.closed || ! opener.set_hpi) {
   alert('The destination form was closed; I cannot act on your selection.');
  } else {
   opener.set_hpi(hpi);
  }
  window.close();
  return false;
}

</script>
</head>

<?php
$fres=sqlStatement("Select lname, fname, ss FROM patient_data WHERE pid='".$pid."'");
$p_info=array();
unset($p_info);
if($fres) { $p_info=sqlFetchArray($fres); } 
?>

<body class="body_top">
<form method='post' name='theform' action='hpi_choice_popup.php'>
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr>
  <td height="1">
  </td>
 </tr>
 <tr>
    <td><b>HPI Entries Currently on File For:&nbsp;&nbsp;&nbsp;<?php echo $p_info{'fname'},' ',$p_info{'lname'},' (',$pid,')'; ?></b></td>
 </tr>
 <tr>
  <td height="1">
  </td>
</table>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr style="background-color: #ddddff">
  <td><b>Click the Date to Select the HPI Text</b></td>
 </tr>
 <tr>
  <td height="1">
  </td>
 </tr>
</table>

<table width='100%' border='0' cellpadding='5'>
  <tr>
    <td style='width: 15%'>Date</td>
    <td>HPI Text</td>
  </tr>
<?php
  $query="SELECT form_gen_exam.date as date, ge_hpi AS hpi ".
    "FROM forms, form_gen_exam WHERE deleted != 1 AND forms.pid = '".$pid.
    "' AND (form_name='Extended Exam (1)' OR ".
    "form_name='General Examination Form') AND forms.form_id=form_gen_exam.id ".
    "ORDER BY form_gen_exam.date DESC";
	// Standard query for customers with the typical extended exam
  $query="SELECT form_ext_exam1.date as date, ee1_hpi AS hpi ".
    "FROM forms, form_ext_exam1 WHERE deleted != 1 AND forms.pid = '".$pid.
    "' AND form_name='Extended Exam (1)' AND forms.form_id=form_ext_exam1.id ".
    "ORDER BY form_ext_exam1.date DESC";
	// Standard query for customers with the typical extended exam
  $query="SELECT form_ped_comp1.date as date, pc1_hpi AS hpi ".
    "FROM forms, form_ped_comp1 WHERE deleted != 1 AND forms.pid = '".$pid.
    "' AND form_name='Comprehensive Pediatric Exam' AND ".
		"forms.form_id=form_ped_comp1.id ORDER BY form_ped_comp1.date DESC";
	// Query for ABC - 
  $query="SELECT form_psy_exam1.date as date, py1_hpi AS hpi ".
    "FROM forms, form_psy_exam1 WHERE deleted != 1 AND forms.pid = '".$pid.
    "' AND form_name='General Psych Exam' AND forms.form_id=form_psy_exam1.id ".
    "ORDER BY form_psy_exam1.date DESC";
	// Custom query for WHC, with many women's forms
	$form_list=array( array('frmname' => 'form_whc_comp', 'fldname' => 'wc_hpi'),
					// array('frmname' => 'form_whc_ww2', 'fldname' => 'ww_hpi'),
					// array('frmname' => 'form_whc_well_woman', 'fldname' => 'w3_hpi'),
					array('frmname' => 'form_whc_bleeding', 'fldname' => 'w5_hpi'),
					array('frmname' => 'form_whc_lump', 'fldname' => 'w6_hpi'),
					array('frmname' => 'form_whc_discharge', 'fldname' => 'w7_hpi'),
					array('frmname' => 'form_whc_infertile', 'fldname' => 'w8_hpi'),
					array('frmname' => 'form_whc_uti', 'fldname' => 'w10_hpi'));
					// array('frmname' => 'form_whc_newpat', 'fldname' => 'w1_hpi'));

	$query='';
	$cnt=0;
	foreach($form_list as $ref => $frm) {
		if($cnt) $query .= " UNION ALL ";
		$query .= "SELECT ".$frm['fldname']." AS hpi, pid, date from ".$frm['frmname'].
							" WHERE pid=$pid AND ".$frm['fldname']." != ''";
		$cnt++;
	}
	$query .= " ORDER BY date DESC";
	// Query for Psych - 
  $query="SELECT py1_form_dt AS date, py1_hpi AS hpi ".
    "FROM form_psy_exam1 WHERE form_psy_exam1.pid = ? UNION ALL ".
  	"SELECT py2_form_dt AS date, py2_hpi AS hpi ".
    "FROM form_psy_exam2 WHERE form_psy_exam2.pid = ? UNION ALL ".
  	"SELECT ee1_form_dt AS date, ee1_hpi AS hpi ".
    "FROM form_ext_exam1 WHERE form_ext_exam1.pid = ? ".
    "ORDER BY date DESC";
	// Standard query for customers with the typical extended exam
  $query="SELECT form_ext_exam2.date as date, hpi ".
    "FROM forms, form_ext_exam2 WHERE deleted != 1 AND forms.pid=? " .
    "AND formdir = 'ext_exam2' AND forms.form_id=form_ext_exam2.id ".
  	// "UNION ALL SELECT form_ext_exam1.date as date, ee1_hpi AS hpi ".
    // "FROM forms, form_ext_exam2 WHERE deleted != 1 AND forms.pid=? " .
    // "AND formdir = 'ext_exam2' AND forms.form_id=form_ext_exam2.id ".
    "ORDER BY date DESC";
  $forms=sqlStatement($query, array($pid));
  $row_cnt=0;
  while ($row = sqlFetchArray($forms)) {
    $getDate=substr($row{'date'},0,10);
    $getHPI=base64_encode($row{'hpi'});
    $dispHPI=trim(htmlspecialchars($row{'hpi'}, ENT_QUOTES, '', FALSE));
    if(empty($dispHPI)) { continue; }
    $anchor = "<a href='' " .
      "onclick='return setHpi(\"$getHPI\")'>";
    echo " <tr>";
    echo "  <td style='vertical-align: top'>$anchor$getDate</a></td>\n";
    echo "  <td><textarea name='hpi' readonly='readonly' style='white-space: pre-wrap; width: 100%' rows='4'>$dispHPI</textarea></td>\n";
    echo " </tr>";
    $row_cnt++;
  }
  if(!$row_cnt) {
    echo "<tr><td colspan='2' style='text-align: center; color: #FF0000'><b>No HPI Text Found For This Patient</b></td></tr>\n";
  }
?>
	<tr><td>&nbsp;</td></tr>
	<tr>
		<td colspan="2"><div style="float: right; padding-right: 15px;"><a href="javascript: window.close();" class="css_button btn btn-primary"><span>Close</span></a></div></td>
	</tr>
</table>
</center>
</form>
</body>
</html>
