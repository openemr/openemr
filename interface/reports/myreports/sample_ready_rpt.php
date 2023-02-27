<?php
// Copyright (C) 2013-2016 Williams Medical Technologies
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows semen samples that are in the lab for analysis

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), form_encounter.date',
  'pubpid'  => 'lower(patient_data.pubpid), form_encounter.date',
  'time'    => 'coll_date',
  'acc'     => 'acc',
  'type'    => 'forms.formdir'
);
$pop_used= checkSettingMode('wmt::form_popup');

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$yesterday = mktime(0,0,0,date('m'),date('d')-2,date('Y'));
$form_from_date= date('Y-m-d', $yesterday);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider = '';
$form_disposition = '';
$analysis_needed = true;
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_disposition'])) $form_disposition= $_POST['form_disposition'];
if(!isset($_POST['analysis_needed'])) $analysis_needed = false;
$form_details   = "1";

$orderby = $ORDERHASH['acc'];
$form_orderby = 'acc';

$query = 
  "SELECT forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"form_encounter.provider_id, form_encounter.supervisor_id, ".
	"form_semen_collect.coll_date, form_semen_collect.acc, ".
	"form_semen_collect.id, ".
	"form_semen_analysis.id AS aid, ".
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid ".
	"FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
	"LEFT JOIN form_semen_collect ON forms.form_id = form_semen_collect.id ".
	"LEFT JOIN form_semen_analysis ON ".
	"form_semen_collect.acc = form_semen_analysis.acc ".
  "WHERE ".
  "forms.deleted != '1' AND form_semen_collect.id != '' AND ".
	"forms.formdir = 'semen_collect' ";
if ($form_to_date) {
  $query .= "AND coll_date >= '$form_from_date' AND coll_date <= '$form_to_date' ";
} else {
  $query .= "AND coll_date >= '$form_from_date' AND coll_date <= '$form_from_date' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}

	$query .= " GROUP BY acc UNION ALL SELECT ".
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"form_encounter.provider_id, form_encounter.supervisor_id, ".
	"form_semen_intake.coll_date, form_semen_intake.acc, ".
	"form_semen_intake.id, ".
	"form_semen_analysis.id AS aid, ".
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid ".
	"FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
	"LEFT JOIN form_semen_intake ON forms.form_id = form_semen_intake.id ".
	"LEFT JOIN form_semen_analysis ON ".
	"form_semen_intake.acc = form_semen_analysis.acc ".
  "WHERE ".
  "forms.deleted != '1' AND form_semen_intake.id != '' AND ".
	"forms.formdir = 'semen_intake' ";
if ($form_to_date) {
  $query .= "AND coll_date >= '$form_from_date' AND coll_date <= '$form_to_date' ";
} else {
  $query .= "AND coll_date >= '$form_from_date' AND coll_date <= '$form_from_date' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}

$query .= " ORDER BY $orderby";
// echo "Query:  $query<br>\n";

$res=array();
if(isset($_GET['mode'])) {
	$res = sqlStatement($query);
}
$item=0;

?>
<html>
<head>
<title><?php xl('Approve Forms','e'); ?></title>
<link rel=stylesheet href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

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

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

function AnalysisPop(pid, id, enc)
{
	var warn_msg = '';
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(id == '' || id == 0) warn_msg = 'Form ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/semen_collect/view.php?mode=update&type=analysis&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 900, 900, 1);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Sample Disposition Report','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='sample_ready_rpt.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td colspan="3"><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
								"UPPER(specialty) LIKE '%SUPERVISOR%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider'>\n";
              echo "    <option value=''";
							if($form_provider == '') { echo " selected"; }
							echo ">-- " . xl('All') . " --</option>\n";
							// THIS SHOULD NOT EVEN BE POSSIBLE
              // echo "    <option value='0'";
							// if($form_provider == '0') { echo " selected"; }
							// echo ">-- " . xl('None Assigned') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
             ?></td>
					<td class="label" style="text-align: left;"><?php xl('Disposition','e'); ?>: </td>
					<td colspan="3"><select name="form_disposition" id="form_disposition">
						<?php ListSel($form_disposition, 'Disposition_Category', '- ALL -'); ?>
					</select></td>
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
					<td colspan="4" class="label"><input name="analysis_needed" id="analysis_needed" type="checkbox" value="1" <?php echo (($analysis_needed)?'checked':''); ?> /><label for="analysis_needed">&nbsp;&nbsp;Only Report Samples With No Analysis On File</label></td>
				</tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:10px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php xl('Submit','e'); ?></span></a>
          </div>
        </td>
      </tr>
			<tr>
				<td>
          <div style='margin-left:10px'>
            <?php if (isset($_POST['form_refresh'])) { ?>
            <a href='#' class='css_button' onclick='window.print()'><span><?php xl('Print','e'); ?></span></a>
            <?php } else { echo "&nbsp;"; } ?>
					</div>
				</td>
			</tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
if (isset($_POST['form_refresh'])) {
?>
<div id="report_results">
<table>

 <thead>
	<?php if ($form_details) { ?>
  <th>
		<?php xl('Date','e'); ?>
  </th>
  <th>
		<?php xl('Patient','e'); ?>
  </th>
  <th>
		<?php xl('ID','e'); ?>
  </th>
  <th>
   <?php  xl('Type','e'); ?>
  </th>
  <th>
   <?php  xl('Analysis Status','e'); ?>
  </th>
  <th>
   <?php  xl('Accession','e'); ?>
  </th>
  <th>
   <?php  xl('Disposition','e'); ?>
  </th>
 </thead>
	<?php } ?>
 <tbody>
<?php
	if ($res) {
  	$lastdocname = "";
  	$doc_encounters = 0;
  	while ($row = sqlFetchArray($res)) {

			// echo "<tr><td>&nbsp;</td></tr>\n";
			// print_r($row);
			// echo "<br>\n";
    	$errmsg  = "";
			$status = 'No Analysis On File';
			if($row{'aid'}) { $status = 'Analysis On File'; }
			if($analysis_needed) {
				if($row{'aid'}) { continue; }
			}
			$type = 'unknown';
			if($row{'formdir'} == 'semen_collect') { $type = 'Collection'; }	
			if($row{'formdir'} == 'semen_intake') { $type = 'Intake'; }	
			$sql = "SELECT * FROM form_semen_disp WHERE pid=? AND acc=? ORDER BY ".
				"disp_date DESC LIMIT 1";
			$fres = sqlStatementNoLog($sql, array($row{'pid'}, $row{'acc'}));
			$frow = sqlFetchArray($fres);
			$disposition = 'No Entries On File';
			if($frow{'id'}) {
				if($form_disposition && ($form_disposition != $frow{'disp_category'})) { continue; }
				$disposition = ListLook($frow{'disp_category'},'Disposition_Category');
			}
			$item++;
	?>
 <tr>
  <td>
   <?php echo oeFormatShortDate(substr($row{'coll_date'}, 0, 10)); ?>&nbsp;
  </td>
  <td>
   <?php echo $row{'lname'}.', '.$row{'fname'}.' '.$row{'mname'}; ?>&nbsp;
  </td>
  <td>
   <?php echo $row{'pubpid'}; ?>&nbsp;
  </td>
  <td>
		<?php echo $type; ?>&nbsp;
  </td>
  <td>
		<?php echo $status; ?>&nbsp;
  </td>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="AnalysisPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>');">
	<?php } ?>
   <?php echo $row{'acc'}; ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
		<?php } ?>
  </td>
  <td>
		<?php echo $disposition; ?>&nbsp;
  </td>
 </tr>
<?php
  	}
	}
?>

</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" tabindex="-1" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_refresh" id="form_refresh" tabindex="-1" value=""/>
<input name="item_total" id="item_total" type="hidden" tabindex="-1" value="<?php echo $item; ?>" />

</form>
</body>

<script type="text/javascript" src="../../../library/wmt/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
