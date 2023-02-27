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
// include_once("$srcdir/wmt/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later
$last_year= mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month= mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) $_POST['form_form_date'] = '' ;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = '' ;
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_year));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = '';
$form_details = false;
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];

$query = "SELECT " .
	"p.DOB, p.lname, p.fname, p.pid, p.pubpid, p.sex, p.providerID, ".
	"l.diagnosis, l.begdate, l.enddate, l.activity ".
  "FROM patient_data AS p " .
	"RIGHT JOIN lists AS l USING (pid) ".
	"WHERE l.activity > 0 AND l.type = 'medical_problem' AND l.diagnosis ".
	"LIKE '%ICD10:I10%' ORDER BY providerID, pubpid";

$lres=array();
if(isset($_REQUEST['form_refresh'])) { 
	$lres = sqlStatement($query);
	$cnt = sqlNumRows($lres);
}
?>
<html>
<head>
<title><?php xl('BP For ICD10/DOB Target','e'); ?></title>
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

<script type="text/javascript">
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function refreshme() {
  document.forms[0].submit();
}
</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Summary Target BP','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='vital_diag_rpt.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='800px'><div style='float:left'>
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
					<span><?php xl('Submit','e'); ?></span></a>

            <?php if(isset($_REQUEST['form_refresh']) ) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span><?php xl('Print','e'); ?></span></a>
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
if (isset($_REQUEST['form_refresh'])) {
?>
<div id="report_results">
<table>

<thead>
<?php if ($form_details) { ?>
  <th><?php xl('Provider','e'); ?></th>
  <th><?php xl('Date','e'); ?></th>
  <th><?php xl('Patient','e'); ?></th>
  <th><?php xl('ID','e'); ?></th>
  <th><?php xl('Encounter','e'); ?></th>
  <th><?php xl('BP','e'); ?></th>
<?php } else { ?>
  <th colspan='3'><?php xl('Provider','e'); ?></th>
  <th colspan='3'><?php xl('Patients','e'); ?></th>
<?php } ?>
</thead>

<?php
$rpt_tot_cnt = 0;
$rpt_control_cnt = 0;
$doc_tot_cnt = array();
$doc_control_cnt = array();
$prev_provider = '';
$dr_names = array();
$fres = sqlStatement('SELECT id, lname, fname, mname FROM users WHERE 1');
while($res = sqlFetchArray($fres)) {
	$dr_names[$res{'id'}] = $res{'lname'}.', '.$res{'fname'};
}

// DOING A SECOND QUERY HERE BECAUSE WE NEED SO MUCH STUFF :(
$query = "SELECT forms.encounter, forms.form_id, fe.provider_id AS visit_dr, ".
	"SUBSTRING(fe.date,1,10) AS dos, fv.bps, fv.bpd ".
	"FROM forms LEFT JOIN form_encounter AS fe using (encounter) ".
	"LEFT JOIN form_vitals AS fv ON (fv.id = form_id) ".
	"WHERE forms.formdir = 'vitals' ";
if($form_to_date) {
	$query .= "AND fe.date >= '$form_from_date 00:00:00 ' AND fe.date <= '$form_to_date 23:59:59' ";
} else {
	$query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
}
$query .= "AND forms.deleted = 0 AND forms.pid = ? ".
	"ORDER BY fe.date DESC LIMIT 1";

if($lres) {
  while($row = sqlFetchArray($lres)) {
		// echo "<tr><td colspan='6'>";
		// print_r($row);
		// echo "</td></tr>\n";
		$age = getPatientAge($row{'DOB'});
		if($age >= 18 && $age <= 85) {
			// GET THE VITALS IF THE AGE IS CORRECT
			$vrow = sqlQuery($query, array($row{'pid'}));
		 	// echo "<tr><td colspan='6'>Vital Row:";
		 	// print_r($vrow);
		 	// echo "</td></tr>\n";
			if($vrow{'pbs'} < 140 && $vrow{'bpd'} < 90) {
				if($form_details) {
					echo "<tr>";
					echo "<td>",htmlspecialchars($dr_names[$row{'providerID'}],ENT_QUOTES,'',FALSE);
					echo "<td>",$vrow{'dos'},"</td>\n";
					echo "<td>",htmlspecialchars($row{'lname'},ENT_QUOTES,'',FALSE),", ";
					echo htmlspecialchars($row{'fname'},ENT_QUOTES,'',FALSE),"</td>\n";
					echo "<td>",$row{'pubpid'},"</td>\n";
					echo "<td>",$vrow{'bps'}," / ",$vrow{'bpd'},"</td>\n";
					echo "</td></tr>\n";
				}
				$doc_control_cnt[$row{'providerID'}]++;
				$rpt_control_cnt++;
			} else {
			}
			$doc_tot_cnt[$row{'providerID'}]++;
			$rpt_tot_cnt++;
		} 
	}	
	foreach($doc_tot_cnt as $dr => $tot) {
		echo "<tr><td colspan='3'>Total Patients For ",$dr_names[$dr]," :</td>\n";
		echo "<td colspan='2'>&nbsp;&nbsp;</td>\n";
		echo "<td style='text-align: right;'>",$doc_tot_cnt[$dr],"</td>\n";
		echo "<tr><td colspan='3'>Total Controlled Patients For ",$dr_names[$dr]," :</td>\n";
		echo "<td colspan='2'>&nbsp;&nbsp;</td>\n";
		echo "<td style='text-align: right;'>",$doc_control_cnt[$dr],"</td>\n";
		if($form_details) {
			echo "<tr><td colspan='6'>&nbsp;</td></tr>\n";
		}
	}
}
if($rpt_tot_cnt) {
	echo "<tr><td colspan='3'>Total Patients For Report : </td>\n";
	echo "<td colspan='2'>&nbsp;&nbsp;</td>\n";
	echo "<td style='text-align: right;'>$rpt_tot_cnt</td>\n";
	echo "<tr><td colspan='3'>Total Controlled Patients For Report : </td>\n";
	echo "<td colspan='2'>&nbsp;&nbsp;</td>\n";
	echo "<td style='text-align: right;'>$rpt_control_cnt</td>\n";
}
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

<script type='text/javascript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
