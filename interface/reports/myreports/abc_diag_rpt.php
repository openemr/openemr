<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/patient.inc");
include_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later
$last_year= mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month= mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) { $_POST['form_form_date'] = '' ; }
if(!isset($_POST['form_to_date'])) { $_POST['form_to_date'] = '' ; }
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_year));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];

// First define the query for NQF 0105
$query = "SELECT " .
	"lists.id, lists.type, lists.diagnosis, lists.pid, ".
	"form_encounter.date, form_encounter.pid, form_encounter.provider_id, ". 
	"patient_data.DOB, patient_data.lname, patient_data.fname ".
  "FROM lists ".
	"LEFT JOIN issue_encounter ON (lists.id = issue_encounter.list_id) ".
	"LEFT JOIN form_encounter ON (issue_encounter.encounter = form_encounter.encounter) ".
  "LEFT JOIN patient_data ON (lists.pid = patient_data.pid) " .
	"WHERE lists.type='medical_problem' AND ".
	"(diagnosis='ICD9:311' OR diagnosis LIKE 'ICD9:296.2%') ";
if ($form_to_date) {
  $query .= "AND lists.date >= '$form_from_date 00:00:00 ' AND lists.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND lists.date >= '$form_from_date 00:00:00' AND lists.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
$query .= "GROUP BY lists.pid, lists.diagnosis ";
$query .= "ORDER BY lists.pid";
$query1 = $query;
//echo "Query: ",$query1,"<br>\n";

// define the query for NQF 0004
$query = "SELECT " .
	"lists.id, lists.type, lists.diagnosis, lists.pid, ".
	"form_encounter.date, form_encounter.pid, form_encounter.provider_id, ". 
	"patient_data.DOB, patient_data.lname, patient_data.fname ".
  "FROM lists ".
	"LEFT JOIN issue_encounter ON (lists.id = issue_encounter.list_id) ".
	"LEFT JOIN form_encounter ON (issue_encounter.encounter = form_encounter.encounter) ".
  "LEFT JOIN patient_data ON (lists.pid = patient_data.pid) " .
	"WHERE lists.type='medical_problem' AND ".
	"(diagnosis='ICD9:303.90' OR diagnosis='ICD9:305.00' OR diagnosis='ICD9:291.9' OR diagnosis='ICD9:304.3' OR diagnosis='ICD9:305.2' OR diagnosis='ICD9:292.9' OR diagnosis='ICD9:304.8') ";
if ($form_to_date) {
  $query .= "AND lists.date >= '$form_from_date 00:00:00 ' AND lists.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND lists.date >= '$form_from_date 00:00:00' AND lists.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
$query .= "GROUP BY lists.pid, lists.diagnosis ";
$query .= "ORDER BY lists.pid";
$query2 = $query;
// echo "Query: ",$query2,"<br>\n";
$nfq1=array();
$nfq2=array();
if(isset($_GET['mode'])) { 
	$nfq1= sqlStatement($query1);
	$nfq2= sqlStatement($query2);
	$cnt0105 = sqlNumRows($nfq1);
	$cnt0004 = sqlNumRows($nfq2);
}
?>
<html>
<head>
<title><?php xl('NQF Quick List','e'); ?></title>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function refreshme() {
  document.forms[0].submit();
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('NQF Quick Summary','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='abc_diag_rpt.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td width='800px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users WHERE " .
								"authorized=1 AND active='1' AND username!='' AND " .
								"(specialty LIKE '%Provid%' OR specialty LIKE '%Super%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider'>\n";
              echo "    <option value=''>-- " . xl('All') . " --\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
              }
              echo "   </select>\n";
              ?></td>
         </tr>
         <tr>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

            <?php if(isset($_GET['mode']) ) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
            <?php } ?>
          </div>
        </td>
      </tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_GET['mode'])) {
?>
<div id="report_results">
<table>

<?php
// Build an array of result totals here
// if ($nfq1) {
  // while ($row = sqlFetchArray($nfq1)) {
		// echo "<tr><td>";
		// print_r($row);
		// echo "</td></tr>\n";
		// $check=GetLastBMI($row['pid'], $row['DOB']);
		// echo "<tr>\n";
		// echo "<td>",$row{'pid'},"</td>\n";
		// echo "<td>",$row{'lname'},"</td>\n";
		// echo "<td>",$row{'fname'},"</td>\n";
		// echo "<td>",$row{'diagnosis'},"</td>\n";
		// echo "<td>",$row{'encounter'},"</td>\n";
	// }	
// }
echo "<tr>\n";
echo "<td class='bold'>NFQ 0004 Distinct Entries</td>\n";
echo "<td class='text'>$cnt0004</td>\n";
echo "<td class='text'>Definition: ICD9 303.90, 305.00, 291.9, 304.3, 305.2, 292.9, 304.8</td>\n";
echo "<tr>\n";
echo "<td class='bold'>NFQ 0105 Distinct Entries</td>\n";
echo "<td class='text'>$cnt0105</td>\n";
echo "<td class='text'>Definition: ICD9 311 and 296.2x</td>\n";

?>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
