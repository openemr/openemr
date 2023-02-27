<?php
// Copyright (C) 2015 Rich Genandt <rich@williamsmedtech.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows new patient visits with no follow up

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later
$bgcolor  = '#FFFFDD';

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'doctor'  => 'lower(u.lname), lower(u.fname)',
  'patient' => 'lower(p.lname), lower(p.fname)',
  'pubpid'  => 'lower(p.pubpid), e.date',
  'default'  => 'lower(dr.lname), lower(dr.fname)'
);

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  $desc = str_replace(array("\r", "\t", "\n"), '', $desc);
  return $desc;
}

function show_doc_total($lastdocname, $doc_encounters) {
  global $form_csvexport;
  if ($lastdocname) {
		if($form_csvexport) {
			echo '"'.$lastdocname.'",';
			echo '"'.$doc_encounters.'"';
			echo "\r";
		} else {
    	echo " <tr>\n";
    	echo "  <td class='detail'>$lastdocname</td>\n";
    	echo "  <td class='detail' align='right'>$doc_encounters</td>\n";
    	echo " </tr>\n";
		}
  }
}

$six_months_ago = mktime(0,0,0,date('m')-6,date('d'),date('Y'));
$form_from_date = date('Y-m-d', $six_months_ago);
$form_to_date   = fixDate(date('Y-m-d'),date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider  = '';
$form_facility  = '';
$form_details   = false;
$form_csvexport = false;
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_details'])) $form_details = true;
if(isset($_POST['form_csvexport'])) $form_csvexport = $_POST['form_csvexport'];

if(!isset($_POST['form_orderby'])) $_POST['form_orderby'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

$query = "SELECT " .
  "e.encounter, e.date, e.provider_id, " .
  "p.fname, p.mname, p.lname, p.pid, p.pubpid, p.providerID, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname, " .
  "dr.lname AS drlname, dr.fname AS drfname, dr.mname AS drmname " .
  "FROM ( form_encounter AS e, forms AS f ) " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pid " .
  "LEFT JOIN users AS u ON u.id = e.provider_id " .
  "LEFT JOIN users AS dr ON dr.id = p.providerID " .
  "WHERE f.encounter = e.encounter AND f.formdir = 'newpatient' ".
  "AND f.deleted = 0 AND e.provider_id != p.providerID ";
if ($form_to_date) {
  $query .= "AND e.date >= '$form_from_date 00:00:00' AND e.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND e.date >= '$form_from_date 00:00:00' AND e.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND e.provider_id = '$form_provider' ";
}
if ($form_facility) {
  $query .= "AND e.facility_id = '$form_facility' ";
}
$query .= "GROUP BY e.provider_id, p.pid ORDER BY $orderby";

$res = false;
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
	$res = sqlStatement($query);
}

if($form_csvexport != '' && $form_csvexport !== false) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=no_follow_up_rpt.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"NP Last",';
		echo '"NP First",';
		echo '"NP Middle",';
    echo '"Patient Last",';
    echo '"Patient First",';
    echo '"Patient Middle",';
		echo '"ID",';
		echo '"Dr Last",';
		echo '"Dr First",';
		echo '"Dr Middle"';
  } else {
		echo '"Nurse Practitioner",';
    echo '"Encounters"';
  }
	echo "\r";
	// End of Export
} else {
	// Start of HTML output
?>
<html>
<head>
<title><?php xl('Patients Seen By NP','e'); ?></title>
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
<span class='title'><?php xl('Report','e'); ?> - <?php xl('Patients Seen By NP','e'); ?></span>
<div id="report_parameters_daterange">
<?php
echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ";
echo date("d F Y", strtotime($form_to_date));
?>
</div>

<form method='post' name='theform' id='theform' action='patients_by_np.php'>
<div id="report_parameters">
<table>
 <tr>
  <td width='850px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'> <?php xl('From','e'); ?>: </td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'> <?php xl('To','e'); ?>: </td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="label" style="text-align: left;">
				<input name="form_details" id="form_details" type="checkbox" value="1" <?php echo $form_details ? 'checked="checked"' : ''; ?> /><label for="form_details">&nbsp;&nbsp;Show Details</label>
			</td>
			<td class='label'> <?php xl('Encounter Provider','e'); ?>: </td>
			<td>
				<?php

				 $query = "SELECT id, lname, fname FROM users WHERE active=1 AND ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				 $ures = sqlStatement($query);

				 echo "   <select name='form_provider'>\n";
				 echo "    <option value=''>-- " . xl('All') . " --\n";

				 while ($urow = sqlFetchArray($ures)) {
				  $provid = $urow['id'];
				  echo "    <option value='$provid'";
				  if ($provid == $_POST['form_provider']) echo " selected";
				  echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
				 }

				 echo "   </select>\n";

				?>
			</td>
		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
					<span><?php echo xl('Submit'); ?></span></a>

					<?php if ($_POST['form_refresh'] || $_POST['form_csvexport'] || $_POST['form_orderby']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span><?php xl('Print','e'); ?></span></a>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
						<span><?php echo xl('CSV Export'); ?></span></a>
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
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
?>
<div id="report_results">
<table>

 <thead>
<?php if ($form_details) { ?>
  <th>
   <a href="nojs.php" onclick="return dosort('doctor')"
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Encounter Provider','e'); ?> </a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('default')"
   <?php if ($form_orderby == "default") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient Provider','e'); ?> </a>
  </th>
<?php } else { ?>
  <th><?php  xl('Encounter Provider','e'); ?></td>
  <th><?php  xl('Encounters','e'); ?></td>
<?php } ?>
 </thead>
 <tbody>
<?php
} // End of the refresh/orderby condition
} // end not CSV export - HTML output
?>
<?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
	$row_cnt = 0;
  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
		$row_cnt++;

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }

    $errmsg  = "";
    if ($form_details) {
			if($form_csvexport) { 
  			echo '"' . display_desc($row['ulname']). '",';
  			echo '"' . display_desc($row['ufname']). '",';
  			echo '"' . display_desc($row['umname']). '",';
  			echo '"' . display_desc($row['lname']) . '",';
				echo '"' . display_desc($row['fname']) . '",';
				echo '"' . display_desc($row['mname']) . '",';
  			echo '"' . $row['pubpid'] . '",';
  			echo '"' . display_desc($row['drlname']). '",';
  			echo '"' . display_desc($row['drfname']). '",';
  			echo '"' . display_desc($row['drmname']). '';
				echo "\r";
			} else {
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td> <?php echo ($docname == $lastdocname) ? "" : $docname ?>&nbsp; </td>
  <td>
   <?php echo $row['lname'].', '.$row['fname'].' '.$row['mname']; ?>&nbsp;</td>
  <td> <?php echo $row['pubpid']; ?>&nbsp; </td>
  <td>
   <?php echo $row['drlname'].', '.$row['drfname'].' '.$row['drmname']; ?>&nbsp;
  </td>
 </tr>
<?php $bgcolor = ($bgcolor == '#FFFFDD') ? '#FFDDDD' : '#FFFFDD'; ?>
<?php } // End HTML line detail ?>

<?php
    } else {
      if ($docname != $lastdocname) {
        show_doc_total($lastdocname, $doc_encounters);
        $doc_encounters = 0;
      }
      ++$doc_encounters;
    }
    $lastdocname = $docname;
  }

  if (!$form_details) show_doc_total($lastdocname, $doc_encounters);
}
} // End of overall conditional
?>
<?php if(!$form_csvexport) { ?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php if(!$row_cnt) { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type="hidden" name="form_csvexport" id="form_csvexport" value="" />

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>

</script>

</html>
<?php } ?>
