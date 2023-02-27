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
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
$ORDERHASH = array(
 'user'  => 'lower(ulast), lower(ufirst), final_edd',
 'patient' => 'lower(lname), lower(fname), final_edd',
 'pubpid'  => 'lower(pubpid), final_edd',
 'date'    => 'final_edd',
);

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$next_month = mktime(0,0,0,date('m')+1,date('d'),date('Y'));
$next_year = mktime(0,0,0,date('m'),date('d'),date('Y')+1);
$form_from_date = fixDate(date('Y-m-d'), date('Y-m-d'));
$form_to_date = date('Y-m-d', $next_year);
$show_empty = 0;
if(isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_user = '';
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['show_empty'])) $show_empty = $_POST['show_empty'];
$form_provider = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];

$form_orderby= 'date';
if(isset($_REQUEST['form_orderby']))
	$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'date';
$orderby = $ORDERHASH[$form_orderby];
$use_acog = sqlQuery("SHOW TABLES LIKE 'form_acog_antepartum_C'");
$use_ob_complete = sqlQuery("SHOW TABLES LIKE 'form_ob_complete'");
$use_acog = FALSE;

$binds = array();
if($use_acog && $use_ob_complete) {
	$query = "SELECT forms.deleted, forms.pid, forms.form_id, forms.formdir, " .
		"form_acog_antepartum_C.ac_edd_final AS final_edd, ".
		"form_acog_antepartum_C.id AS fid, patient_data.fname, ".
		"patient_data.mname, patient_data.lname, patient_data.pubpid, " .
		"form_encounter.encounter, form_encounter.provider_id, " .
		"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle " .
  	"FROM forms LEFT JOIN form_acog_antepartum_C " .
  	"ON forms.form_id = form_acog_antepartum_C.id " .
		"LEFT JOIN form_encounter USING (encounter) " .
  	"LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
		"LEFT JOIN users AS u on patient_data.providerID = u.id " .
  	"WHERE forms.deleted != 1 AND forms.formdir = 'acog_antepartum_C' ";
	if(!$show_empty) $query .= "AND (form_acog_antepartum_C.ac_edd_final != ''".
			" AND form_acog_antepartum_C.ac_edd_final IS NOT NULL) ";
	if ($form_user) {
  	$query .= "AND form_acog_antepartum_C.user = ? ";
		$binds[] = $form_user;
	}
	if($form_provider !== '') {
		$query .= "AND patient_data.providerID = ? ";
		$binds[] = $form_provider;
	}
	$query .= "UNION ALL SELECT forms.deleted, forms.pid, forms.form_id, " .
		"forms.formdir, form_ob_complete.upd_edd AS final_edd, ".
		"form_ob_complete.id AS fid, patient_data.fname, ".
		"patient_data.mname, patient_data.lname, patient_data.pubpid, " .
		"form_encounter.encounter, form_encounter.provider_id, " .
		"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle " .
  	"FROM forms LEFT JOIN form_ob_complete " .
  	"ON forms.form_id = form_ob_complete.id " .
		"LEFT JOIN form_encounter USING (encounter) " .
  	"LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
		"LEFT JOIN users AS u on patient_data.providerID = u.id " .
  	"WHERE forms.deleted != 1 AND forms.formdir = 'ob_complete' ";
	if(!$show_empty) $query .= "AND (form_ob_complete.upd_edd != ''".
			" AND form_ob_complete.upd_edd IS NOT NULL) ";
	if ($form_user) {
  	$query .= "AND form_ob_complete.user = ? ";
		$binds[] = $form_user;
	}
	if($form_provider !== '') {
		$query .= "AND patient_data.providerID = ? ";
		$binds[] = $form_provider;
	}
} else {
	// JUST ONE OB FORM OR THE OTHER
  $query = "SELECT p.fname, p.mname, p.lname, p.pubpid, p.pid, ";
	if($use_acog) {
		$query .= "acog.ac_edd_final AS final_edd, acog.id AS fid, ";
	} else {
		$query .= "ob.upd_edd AS final_edd, ob.id AS fid, ";
	}
	$query .= "fe.encounter, fe.provider_id, " . 
		"pp.pp_date_of_pregnancy AS deliver_dt, " .
		"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle " .
  	"FROM patient_data AS p ";
	if($use_acog) {
		$query .= "LEFT JOIN form_acog_antepartum_C AS acog " .
  		"ON acog.id = (SELECT id FROM form_acog_antepartum_C " .
			"AS ac LEFT JOIN forms ON (ac.id = forms.form_id AND " .
			"forms.formdir = 'form_acog_antepartum_C') WHERE ".
			"forms.deleted = 0 AND ac.pid = p.pid ";
		if(!$show_empty) $query .= "AND (ac.ac_edd_final != ''".
			" AND ac.ac_edd_final IS NOT NULL) ";
		$query .= "ORDER BY ac_edd_final DESC LIMIT 1) ";
	}
	if($use_ob_complete) {
		$query .= "LEFT JOIN form_ob_complete AS ob " .
  		"ON ob.id = (SELECT obc.id FROM form_ob_complete AS obc LEFT JOIN " .
			"forms ON (obc.id = forms.form_id AND forms.formdir = 'ob_complete') ".
			"WHERE forms.deleted = 0 AND obc.pid = p.pid ";
		if(!$show_empty) $query .= "AND (obc.upd_edd != ''".
			" AND obc.upd_edd IS NOT NULL) ";
		$query .= "ORDER BY upd_edd DESC LIMIT 1) ";
	}
	$query .= "LEFT JOIN forms as f ON (f.form_id = ";
	if($use_ob_complete) $query .= "ob.id ";
	if($use_acog) $query .= "acog.id ";

	$query .= "AND f.pid = p.pid AND f.formdir = ?) ";
	if($use_ob_complete) $binds[] = 'ob_complete';
	if($use_acog) $binds[] = 'acog_antepartum_C';
	$query .= "LEFT JOIN form_encounter AS fe ON (f.encounter = fe.encounter) " .
		"LEFT JOIN users AS u ON p.providerID = u.id " .
		"LEFT JOIN form_whc_pp AS pp ON pp.id = (SELECT pp.id FROM form_whc_pp " .
		"WHERE pp.pid = p.pid ORDER BY SUBSTRING(pp_date_of_pregnancy,1,7) " .
		"DESC LIMIT 1) WHERE 1 ";
	if ($form_user) {
  	$query .= "AND acog.user = ? ";
		$binds[] = $form_user;
	}
	if($form_provider !== '') {
		$query .= "AND p.providerID = ? ";
		$binds[] = $form_provider;
	}
}
// $query .= "GROUP BY pid ";
if ($form_to_date) {
 	$query .= "HAVING ((final_edd >= ? AND final_edd <= ?) ";
	$binds[] = $form_from_date;
	$binds[] = $form_to_date;
} else {
 	$query .= "HAVING ((final_edd >= ? AND final_edd <= ?) ";
	$binds[] = $form_from_date;
	$binds[] = $form_from_date;
}
if ($show_empty) {
 	$query .= "OR (final_edd = '' OR final_edd IS NULL)) ";
} else {
	$query .= ") ";
}
$query .= "ORDER BY $orderby";

$res='';
if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) {
	$res = sqlStatement($query, $binds);
}
?>
<html>
<head>
<title><?php echo xl('OB Patients'); ?></title>
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

<script type="text/javascript">
<?php include($GLOBALS['srcdir'].'/wmt-v2/report_tools.inc.js'); ?>

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

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('OB Patients by Due Date'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='edd_report.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='800px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
           <td class='label'><?php echo xl('Due From'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>
           <td class='label'><?php echo xl('To'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>
          <td class='label'><?php echo xl('Provider'); ?>: </td>
          <td><?php
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

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
             ?></td>
						<td>
							<input type="checkbox" name="show_empty" id="show_empty" value="1" <?php echo $show_empty == 1 ? ' checked ' : ''; ?> /></td>
						<td class='label'><?php echo xl('Show Empty Dates'); ?></td>
         </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'><span><?php echo xl('Submit'); ?></span></a>

            <?php if(isset($_POST['form_refresh']) || isset($_POST['form_orderby']) ) { ?>
            <a href='#' class='css_button' onclick='window.print()'><span><?php echo xl('Print'); ?></span></a>
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
 if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) {
?>
<div id="report_results">
<table>

 <thead>
	<!--
  <th>
   <a href="nojs.php" onclick="return dosort('user')"
   <?php if ($form_orderby == "user") echo " style=\"color:#00cc00\"" ?>><?php  echo xl('Responsible User'); ?> </a>
  </th>
	-->
  <th>
   <a href="nojs.php" onclick="return dosort('date')"
   <?php if ($form_orderby == "date") echo " style=\"color:#00cc00\"" ?>><?php  echo xl('Estimated Due Date'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Patient'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php echo xl('ID'); ?></a>
  </th>
	<th><?php echo xl('Provider'); ?></th>
 </thead>
 <tbody>
<?php
$bgcolor='#DDDDFF';
if ($res) {
  $lastusername = '';
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {

		$pubpid = $row['pid'];
		$id = $row['fid'];
    $errmsg = "";
		$username = $row['ulast'].', '.$row['ufirst'];
		if(strlen($row{'deliver_dt'}) == 7) $row{'deliver_dt'} .= '-01';
		if(strlen($row{'deliver_dt'}) == 10) {
			if(substr($row{'deliver_dt'},-1) == '0') 
					$row{'deliver_dt'} = substr($row{'deliver_dt'},0,0) . "1";
		}
		echo "Found: ";
		print_r($row);
		echo "<br>\n";
		if($row{'deliver_dt'} > $row{'final_edd'}) continue;
		echo "Past the Delivery Date Check<br>\n";
		if($row{'deliver_dt'} && $row{'deliver_dt'} != '0000-00-00') {
			// $last_delivery_dt = new DateTime($row{'deliver_dt'});
			// $final_edd = new DateTime($row{'final_edd'});
			$last_delivery_dt = strtotime($row{'deliver_dt'});
			$final_edd = strtotime($row{'final_edd'});
			echo "Delivered: [$last_delivery_dt]<br>\n";
			echo "Final EDD: [$final_edd]<br>\n";
			$diff = ($final_edd - $last_delivery_dt) / 86400;
			// $diff = $final_edd->diff($last_delivery_dt)->format("%a");
			echo "Day difference ($diff)<br>\n";
			if($diff < 280) continue;
		}
?>
 <tr bgcolor="<?php echo $bgcolor; ?>">
  <td>
   <?php echo oeFormatShortDate(substr($row['final_edd'], 0, 10)) ?>&nbsp;</a>
  </td>
  <td>
   <!-- a href='javascript:;' onclick="opener.top.frames['RTop'].location='../../patient_file/summary/demographics.php?set_pid=<?php echo $row['pid']; ?>'; opener.top.restoreSession(); " -->
	<a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');"><?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;</a>
  </td>
  <td>
   <?php echo $row['pubpid']; ?>&nbsp;
  </td>
	<td>
		<?php echo $row['ulast'],', ',$row['ufirst']; ?>
	</td>
<?php
    $lastusername = $username;
		if($bgcolor == '#DDDDFF') {
			$bgcolor='#FFFFDD';
		} else {
			$bgcolor='#DDDDFF';
		}
  }

}
?>
</tbody>
</table>
</div>
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type='text/javascript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>

</script>

</html>
