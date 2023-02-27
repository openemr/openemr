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
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/surg1.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'patient' => 'lower(p.lname), lower(p.fname)',
  'pubpid'  => 'lower(p.pubpid)',
	'surgery' => 'form_surg1.sc1_surg_date',
	'doctor'  => 'lower(ulast), lower(ufirst)'
);
$pop_forms= getFormsByType(array('pop_form'));
$pop_used= checkSettingMode('wmt::form_popup');

$last_month = mktime(0,0,0,date('m'),date('d')-2,date('Y'));
$form_from_date= date('Y-m-d', $last_month);
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider = '';
$form_supervisor= '';
$form_facility = '';
$form_name = '';
$form_status= 'i';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_supervisor'])) $form_supervisor = $_POST['form_supervisor'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_status'])) $form_status= $_POST['form_status'];
$form_details   = "1";

$orderby = $ORDERHASH['surgery'];
$form_orderby='surgery';

$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
	"form_surg1.*, ".
	"u.lname AS ulast, u.fname AS ufirst, u.mname AS umiddle, ".
  "p.fname, p.mname, p.lname, p.pubpid, p.pid ".
	"FROM forms " .
	"LEFT JOIN form_encounter USING (encounter) ".
	"LEFT JOIN form_surg1 ON (forms.form_id = form_surg1.id) ".
  "LEFT JOIN patient_data AS p ON forms.pid = p.pid " .
  "LEFT JOIN users AS u ON form_encounter.provider_id = u.id " .
  "WHERE forms.deleted != '1' AND forms.formdir = 'surg1' ";
if ($form_to_date) {
  $query .= "AND ((form_surg1.sc1_surg_date >= '$form_from_date' AND ".
			"form_surg1.sc1_surg_date <= '$form_to_date') ";
} else {
  $query .= "AND ((form_surg1.sc1_surg_date >= '$form_from_date' AND ".
			"form_surg1.sc1_surg_date <= '$form_from_date') ";
}
$query .= "OR form_surg1.sc1_surg_date = '' OR form_surg1.sc1_surg_date = 0) ";
if ($form_facility) {
  $query .= "AND form_encounter.facility_id = '$form_facility' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
if ($form_supervisor !== '') {
  $query .= "AND form_encounter.supervisor_id = '$form_supervisor' ";
}
if ($form_status) {
  $query .= "AND form_surg1.form_complete = '$form_status' ";
}
$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode']) || isset($_GET['approve'])) {
	$res = sqlStatement($query);
}
$item=0;

?>
<html>
<head>
<title><?php xl('Surgical Cases','e'); ?></title>
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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Surgical Cases','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; ".xl('to','e')." &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='surgical_cases.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td><?php xl('Facility','e'); ?>: </td>
          <td>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
					<td class="bold" colspan="4"><?php xl('For Surgeries Scheduled','e'); ?></td>
         </tr>
         <tr>
          <td><?php xl('Provider','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (specialty LIKE '%Provider%' OR ".
								"specialty LIKE '%Supervisor%') ".
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
           	<td><?php xl('From','e'); ?>: </td>
           	<td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
        </tr>
				<tr>
          <td><?php xl('Supervisor','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND specialty LIKE '%Supervisor%' ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_supervisor'>\n";
              echo "    <option value=''";
							if($form_supervisor == '') { echo " selected"; }
							echo ">-- " . xl('All') . " --</option>\n";
              echo "    <option value='0'";
							if($form_supervisor== '0') { echo " selected"; }
							echo ">-- " . xl('None Assigned') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_supervisor) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
             ?></td>
          	<td><?php xl('Status','e'); ?>: </td>
          	<td><?php
               	// Build a drop-down list of form statuses.
              	$query = "SELECT option_id, title FROM list_options WHERE ".
                	"list_id = 'Form_Status' ORDER BY seq";
              	$ures = sqlStatement($query);
	
              	echo "   <select name='form_status'>\n";
              	echo "    <option value=''>-- " . xl('All') . " --</option>\n";
	
              	while ($urow = sqlFetchArray($ures)) {
                	$statid = $urow{'option_id'};
                	echo "    <option value='$statid'";
                	if ($statid == $form_status) echo " selected";
                	echo ">" . $urow{'title'} . "</option>\n";
              	}
              	echo "   </select>\n";
              ?></td>
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
		<?php xl('Provider','e'); ?>
  </th>
  <th>
		<?php xl('Surgery Date','e'); ?>
  </th>
  <th>
		<?php xl('Surgery Type','e'); ?>
  </th>
  <th>
		<?php xl('Patient','e'); ?>
  </th>
  <th>
		<?php xl('ID','e'); ?>
  </th>
  <th>
   <?php  xl('Status','e'); ?>
  </th>
<?php } else { ?>
  <th><?php  xl('Provider','e'); ?></td>
  <th><?php  xl('Encounters','e'); ?></td>
<?php } ?>
 </thead>
 <tbody>
<?php
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $errmsg  = "";
		$item++;
		$pop_link = $GLOBALS['webroot'].'/interface/forms/surg1/view.php?'.
			'mode=update&pid='.$row{'pid'}.'&id='.$row{'form_id'}.'&enc='.
			$row{'encounter'};
		// Build a hover title based on what is outstanding
		$title = '';
		if($row{'sc1_psy_ref_dr'}) {
			if(!isset($row{'sc1_psy_done'})) $row{'sc1_psy_done'} == 0;
			if($title) $title .= "\n";
			if(!$row{'sc1_psy_done'}) { 
				$title .= xl('Neuropsychology Referral Pending','r').' - ';
				if($row{'sc1_psy_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_psy_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('Neuropsychology Referral Complete','r');
			}
		}
		if($row{'sc1_neu_ref_dr'}) {
			if(!isset($row{'sc1_neu_done'})) $row{'sc1_neu_done'} == 0;
			if($title) $title .= "\n";
			if(!$row{'sc1_neu_done'}) { 
				$title .= xl('Neurology Referral Pending','r').' - ';
				if($row{'sc1_neu_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_neu_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('Neurology Referral Complete','r');
			}
		}
		if($row{'sc1_pain_ref_dr'}) {
			if(!isset($row{'sc1_pain_done'})) $row{'sc1_pain_done'} == 0;
			if($title) $title .= "\n";
			if(!$row{'sc1_pain_done'}) { 
				$title .= xl('Pain Clinic Referral Pending','r').' - ';
				if($row{'sc1_pain_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_pain_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('Pain Clinic Referral Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_mri_lum'})) $row{'sc1_rad_mri_lum'} == 0;
		if($row{'sc1_rad_mri_lum'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_mri_lum_done'}) { 
				$title .= xl('MRI Lumbar Pending','r').' - ';
				if($row{'sc1_rad_mri_lum_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_mri_lum_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('MRI Lumbar Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_mri_thor'})) $row{'sc1_rad_mri_thor'} == 0;
		if($row{'sc1_rad_mri_thor'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_mri_thor_done'}) { 
				$title .= xl('MRI Thoracic Pending','r').' - ';
				if($row{'sc1_rad_mri_thor_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_mri_thor_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('MRI Thoracic Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_mri_cerv'})) $row{'sc1_rad_mri_cerv'} == 0;
		if($row{'sc1_rad_mri_cerv'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_mri_cerv_done'}) { 
				$title .= xl('MRI Cervical Pending','r').' - ';
				if($row{'sc1_rad_mri_cerv_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_mri_cerv_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('MRI Cervical Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_mri_brain'})) $row{'sc1_rad_mri_brain'} == 0;
		if($row{'sc1_rad_mri_brain'}) { 
			if($title) $title .= "\n";
			if($row{'sc1_rad_mri_brain_done'}) { 
				$title .= xl('MRI Brain Pending','r').' - ';
				if($row{'sc1_rad_mri_brain_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_mri_brain_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('MRI Brain Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_ct_lum'})) $row{'sc1_rad_ct_lum'} == 0;
		if($row{'sc1_rad_ct_lum'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_ct_lum_done'}) { 
				$title .= xl('CT Lumbar Pending','r').' - ';
				if($row{'sc1_rad_ct_lum_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_ct_lum_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('CT Lumbar Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_ct_thor'})) $row{'sc1_rad_ct_thor'} == 0;
		if($row{'sc1_rad_ct_thor'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_ct_thor_done'}) { 
				$title .= xl('CT Thoracic Pending','r').' - ';
				if($row{'sc1_rad_ct_thor_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_ct_thor_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('CT Thoracic Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_ct_cerv'})) $row{'sc1_rad_ct_cerv'} == 0;
		if($row{'sc1_rad_ct_cerv'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_ct_cerv_done'}) { 
				$title .= xl('CT Cervical Pending','r').' - ';
				if($row{'sc1_rad_ct_cerv_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_ct_cerv_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('CT Cervical Complete','r');
			}
		}
		if(!isset($row{'sc1_rad_ct_brain'})) $row{'sc1_rad_ct_brain'} == 0;
		if($row{'sc1_rad_ct_brain'}) { 
			if($title) $title .= "\n";
			if(!$row{'sc1_rad_ct_brain_done'}) { 
				$title .= xl('CT Brain Pending','r').' - ';
				if($row{'sc1_rad_ct_brain_appt_dt'}) {
					$title .= xl('Appointment Scheduled','r').': '.$row{'sc1_rad_ct_brain_appt_dt'};
				} else {
					$title .= xl('Appointment NOT Scheduled','r');
				}
			} else {
				$title .= xl('CT Brain Complete','r');
			}
		}
		$title_date = substr($row{'sc1_surg_date'}, 0, 10);
		if(!$title_date) $title_date = 'None Specified';
?>
 <tr>
  <td>
   <?php echo $row{'ulast'}.', '.$row{'ufirst'}; ?>&nbsp;
  </td>
  <td title="<?php echo $title; ?>">
	 <a href='javascript:;' onclick="wmtOpen('<?php echo $pop_link; ?>','_blank',900,900);">
   <?php echo $title_date; ?>&nbsp;
	 </a>
  </td>
  <td title="<?php echo $title; ?>">
	 <a href='javascript:;' onclick="wmtOpen('<?php echo $pop_link; ?>','_blank',900,900);">
   <?php echo CptGroupLook($row{'sc1_surg_type'},'4'); ?>&nbsp;
	 </a>
  </td>
  <td>
   <?php echo $row{'lname'}.', '.$row{'fname'}.' '.$row{'mname'}; ?>&nbsp;
  </td>
  <td>
   <?php echo $row{'pubpid'}; ?>&nbsp;
  </td>
  <td>
   <?php echo ListLook($row{'form_complete'},'Form_Status'); ?>&nbsp;
  </td>
 </tr>
<?php
    $lastdocname = $row['ulast'].', '.$row['ufirst'];
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
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
