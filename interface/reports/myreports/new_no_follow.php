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
  'doctor'  => 'lower(u.lname), lower(u.fname), fe.date',
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date, lower(u.lname), lower(u.fname)',
);

function bucks($amount) {
  if ($amount) printf("%.2f", $amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  $desc = str_replace(array("\r", "\t", "\n"), '', $desc);
  return $desc;
}

function show_doc_total($lastdocname, $doc_encounters) {
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
$follow_up_date = fixDate(date('Y-m-d'),date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
if(isset($_POST['follow_up_date'])) {
	$follow_up_date = fixDate($_POST['follow_up_date'], date('Y-m-d'));
}
$form_provider   = '';
$form_facility   = '';
$form_details    = true;
$form_csvexport  = false;
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_details'])) $form_details = true;
if(isset($_POST['form_csvexport'])) $form_csvexport = $_POST['form_csvexport'];

// if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = false;
if(!isset($_POST['form_orderby'])) $_POST['form_orderby'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

$query = "SELECT " .
  "fe.encounter, fe.date, fe.reason, fe.pc_catid, fe.referral_source, " .
  "p.fname, p.mname, p.lname, p.pid, p.pubpid, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
  "FROM ( form_encounter AS fe, forms AS f ) " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
  "LEFT JOIN users AS u ON u.id = fe.provider_id " .
  "WHERE f.encounter = fe.encounter AND f.formdir = 'newpatient' ";
if ($form_to_date) {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND fe.provider_id = '$form_provider' ";
}
if ($form_facility) {
  $query .= "AND fe.facility_id = '$form_facility' ";
}
$query .= "AND fe.date = (SELECT MIN(fe2.date) FROM form_encounter " .
	"AS fe2 WHERE fe2.pid = fe.pid) ";
$query .= "ORDER BY $orderby";

$res = sqlStatement($query);
if($form_csvexport != '' && $form_csvexport !== false) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=no_follow_up_rpt.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"Dr Last",';
		echo '"Dr First",';
		echo '"Dr Middle",';
		echo '"Date",';
    echo '"Patient Last",';
    echo '"Patient First",';
    echo '"Patient Middle",';
		echo '"ID",';
    echo '"Encounter Reason",';
    echo '"Enc ID",';
    echo '"Form(s)",';
    echo '"Coding"' . "\r";
  } else {
		echo '"Provider",';
    echo '"Encounters"' . "\r";
  }
	// End of Export
} else {
	// Start of HTML output
?>
<html>
<head>
<title><?php xl('Follow Up Report','e'); ?></title>
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
<span class='title'><?php xl('Report','e'); ?> - <?php xl('Follow Up','e'); ?></span>
<div id="report_parameters_daterange">
<?php
echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ";
echo date("d F Y", strtotime($form_to_date));
echo "<br>Follow Up Visit On or Before:&nbsp; ";
echo date("d F Y", strtotime($follow_up_date));
?>
</div>

<form method='post' name='theform' id='theform' action='new_no_follow.php'>
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
			<td class='label'> <?php xl('Follow Up By','e'); ?>: </td>
			<td>
			   <input type='text' name='follow_up_date' id="follow_up_date" size='10' value='<?php echo $follow_up_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_follow_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'> <?php xl('Provider','e'); ?>: </td>
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
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> </a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?></a>
  </th>
  <th> <?php  xl('Encounter Reason','e'); ?> </th>
  <th> <?php  xl('Enc ID','e'); ?> </th>
  <th> <?php  xl('Form(s)','e'); ?> </th>
  <th> <?php  xl('Coding','e'); ?> </th>
<?php } else { ?>
  <th><?php  xl('Provider','e'); ?></td>
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
	$fu = "SELECT fe.pid, " .
  	"fe.encounter, fe.date, fe.reason, fe.pc_catid, fe.referral_source, " .
  	"u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
  	"FROM form_encounter AS fe " .
  	"LEFT JOIN users AS u ON u.id = fe.provider_id " .
  	"WHERE fe.pid = ? AND fe.date > ? AND ".
		"fe.date <= '$follow_up_date 23:59:59'";
	$ww = "SELECT * FROM forms WHERE  encounter = ? AND pid = ? AND formdir = ?";
  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
		$row_cnt++;

		// Is there a follow up visit of any type?
		$fuv = sqlQuery($fu, array($patient_id, $row['date']));
		if(!isset($fuv{'pid'})) $fuv{'pid'} = '';
		if(!isset($fuv{'date'})) $fuv{'date'} = '';
		if($fuv{'pid'} && $fuv{'date'}) continue;

		// Is there a comp form on this visit?  If so, throw it out.
		$wwv = sqlQuery($ww, array($row['encounter'], $patient_id, 'whc_comp'));
		if(!isset($wwv{'pid'})) $wwv{'pid'} = '';
		if(!isset($wwv{'formdir'})) $wwv{'formdir'} = '';
		if($wwv{'pid'} && $wwv{'formdir'}) continue;

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }

    $errmsg  = "";
    if ($form_details) {
      // Fetch all other forms for this encounter.
      $encnames = '';      
      $encarr = getFormByEncounter($patient_id, $row['encounter'],
        "formdir, user, form_name, form_id");
      if($encarr!='') {
	      foreach ($encarr as $enc) {
	        if ($enc['formdir'] == 'newpatient') continue;
	        if ($encnames) {
						if($form_csvexport) {
							if($encnames) $encnames .= '~';
						} else {
							if($encnames) $encnames .= '<br />';
						}
					}
	        $encnames .= $enc['form_name'];
	      }
      }     
      // Fetch Primary Insurance from demographics
      $insid = getInsuranceDataByDate($patient_id,oeFormatShortDate(substr($row['date'], 0, 10)),'primary');

      // Fetch coding and compute billing status.
      $coded = "";
      $billed_count = 0;
      $unbilled_count = 0;
      if ($billres = getBillingByEncounter($row['pid'], $row['encounter'],
        "code_type, code, code_text, billed"))
      {
        foreach ($billres as $billrow) {
          // $title = addslashes($billrow['code_text']);
          if ($billrow['code_type'] != 'COPAY' && $billrow['code_type'] != 'TAX') {
            if($form_csvexport) {
              if($coded) $coded .= '|';
            } else {
              if($coded) $coded .= ', ';
            }
            $coded .= $billrow['code'];
            if ($billrow['billed']) ++$billed_count; else ++$unbilled_count;
          }
        }
      }
      // Figure out insurance payer on billed claim
      $pres = sqlStatement("SELECT payer_id FROM claims " . 
              "WHERE patient_id = '{$row['pid']}' AND encounter_id = '{$row['encounter']}'" .
              " ORDER BY version DESC LIMIT 1");        
      $prow = sqlFetchArray($pres);
      if ($prow['payer_id']) {
          $insid['provider'] = $prow['payer_id'];
      }  
              
      // Get insurance name of either the primary for un billed or the payor for billed
      $insname = getInsuranceProvider($insid['provider']);
      
      // Figure product sales into billing status.
      $sres = sqlStatement("SELECT billed FROM drug_sales " .
        "WHERE pid = '{$row['pid']}' AND encounter = '{$row['encounter']}'");
      while ($srow = sqlFetchArray($sres)) {
        if ($srow['billed']) ++$billed_count; else ++$unbilled_count;
      }

      // Compute billing status.
      if ($billed_count && $unbilled_count) $status = xl('Mixed' );
      else if ($billed_count              ) $status = xl('Closed');
      else if ($unbilled_count            ) $status = xl('Open'  );
      else                                  $status = xl('Empty' );
			$reason = $row['reason'];
			if(strlen($reason) > 40) {
  			$reason = substr($reason,0,40).'...';
			}
?>
<?php if($form_csvexport) { 
  echo '"' . display_desc($row['ulname']). '",';
  echo '"' . display_desc($row['ufname']). '",';
  echo '"' . display_desc($row['umname']). '",';
  echo '"'.oeFormatShortDate(substr($row['date'], 0, 10)).'",';
  echo '"' . display_desc($row['lname']) . '",';
	echo '"' . display_desc($row['fname']) . '",';
	echo '"' . display_desc($row['mname']) . '",';
  echo '"' . $row['pubpid'] . '",';
  echo '"' . display_desc($row['reason']) . '",';
  echo '"' . display_desc($row['encounter']) . '",';
  echo '"' . display_desc($encnames) . '",';
  echo '"' . display_desc($coded) . '"' . "\r";
} else {
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td> <?php echo ($docname == $lastdocname) ? "" : $docname ?>&nbsp; </td>
  <td> <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp; </td>
  <td>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;
  </td>
  <td> <?php echo $row['pubpid']; ?>&nbsp; </td>
  <td> <?php echo substr($row['reason'],0,40); ?>&nbsp; </td>
  <td> <?php echo $row['encounter']; ?>&nbsp; </td>
  <td> <?php echo $encnames; ?>&nbsp; </td>
  <td> <?php echo $coded; ?> </td>
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
 Calendar.setup({inputField:"follow_up_date", ifFormat:"%Y-%m-%d", button:"img_follow_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
<?php } ?>
