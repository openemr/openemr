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
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/wmtstandard.inc");
require_once("$srcdir/wmt-v2/wmt.msg.inc");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

$ORDER_LBF_FROM_ID = 'LBF_imagingorder';


function getLayoutFormFields($form_id) {
  $result = sqlStatement("SELECT lo.field_id, lo.title, lo.list_id, lgp.* from layout_group_properties lgp left join layout_options lo on lo.form_id = lgp.grp_form_id WHERE lgp.grp_form_id = ? and lgp.grp_group_id = '' ", array($form_id));

  $items = array();
  while ($row = sqlFetchArray($result)) {
    $items[] = $row;
  }

  return $items;
}

function getFormFieldByTitle($fields = array(), $field_id = '') {
  $field = array();

  if(!empty($fields) && !empty($field_id)) {
    foreach ($fields as $fk => $fItem) {
      if(isset($fItem['title']) && $fItem['title'] == $field_id) {
        $field = $fItem;
      }
    }
  }

  return $field;
}

//Fetch order lbf form fields
$lbfFormFields = getLayoutFormFields($ORDER_LBF_FROM_ID);
$img_facility_field = getFormFieldByTitle($lbfFormFields, 'Imaging Facility');
$sched_date_field = getFormFieldByTitle($lbfFormFields, 'Schedule Date');

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function MultiUserSelect($thisField) {
    $rlist= sqlStatement("SELECT * FROM users WHERE authorized=1 AND " .
           "active=1 ORDER BY lname");
    echo "<option value=''";
    if(!$thisField) echo " selected='selected'";
    echo ">&nbsp;</option>";
    while ($rrow= sqlFetchArray($rlist)) {
      echo "<option value='" . $rrow['username'] . "'";
      //if(in_array($rrow['username'], $thisField)) echo " selected='selected'";
      echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
      echo "</option>";
    }
  }

$date_mode = checkSettingMode('wmt::rto_report_date_mode');
//if(!$date_mode) $date_mode = 'rto_target_date';
if(!$date_mode) $date_mode = 'rto_date';


$alertmsg = ''; // not used yet but maybe later
if(! AclMain::aclCheckCore('acct', 'rep')) die(xl("Unauthorized access."));

$ORDERHASH = array(
  'user'    => 'lower(rto_resp_user), rto_target_date',
  'patient' => 'lower(lname), lower(fname), rto_target_date',
  'pubpid'  => 'lower(pubpid), rto_target_date',
  'due'     => 'rto_target_date',
  'create'  => 'rto_date',
  'ordered' => 'lower(rto_ordered_by), rto_target_date'
);

$bgcolor = '';

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$next_month = mktime(0,0,0,date('m')+1,date('d'),date('Y'));
$form_from_date = fixDate(date('Y-m-d'), date('Y-m-d'));
$form_to_date = date('Y-m-d', $next_month);

$form_from_lbf_sched_date = '';
$form_to_lbf_sched_date = '';

if(isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}


if(isset($_POST['form_from_lbf_sched_date']) && !empty($_POST['form_from_lbf_sched_date'])) {
  $_POST['form_from_lbf_sched_date'] = DateToYYYYMMDD($_POST['form_from_lbf_sched_date']);
  $form_from_lbf_sched_date = fixDate($_POST['form_from_lbf_sched_date'], date('Y-m-d'));
}
if(isset($_POST['form_to_lbf_sched_date']) && !empty($_POST['form_to_lbf_sched_date'])) {
  $_POST['form_to_lbf_sched_date'] = DateToYYYYMMDD($_POST['form_to_lbf_sched_date']);
  $form_to_lbf_sched_date = fixDate($_POST['form_to_lbf_sched_date'], date('Y-m-d'));
}

$form_user = '';
//$form_status = 'p';
$form_status = array('p');
$form_action= '';
$form_ordered_by = '';
$form_show_comments = '';
$form_csvexport = '';
$form_date_mode = $date_mode;

$form_lbf_img_facility = '';

if(!isset($_POST['form_no_date'])) {
	$form_no_date = false;
  //$form_no_date = (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) ? false : true;
} else $form_no_date = true;
if(!isset($_POST['form_show_comments'])) {
	$form_show_comments  = (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) ? false : true;
} else $form_show_comments = true;
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['form_status'])) $form_status = $_POST['form_status'];
if(isset($_POST['form_action'])) $form_action= $_POST['form_action'];
if(isset($_POST['form_csvexport'])) $form_csvexport = $_POST['form_csvexport'];
if(isset($_POST['form_ordered_by'])) $form_ordered_by= $_POST['form_ordered_by'];
if(isset($_POST['form_date_mode'])) $form_date_mode = $_POST['form_date_mode'];

if(isset($_POST['form_lbf_img_facility'])) $form_lbf_img_facility = $_POST['form_lbf_img_facility'];

$form_orderby= 'due';
if(isset($_REQUEST['form_orderby'])) {
	$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'due';
}
if($date_mode == 'rto_date') {
	$form_orderby= 'create';
	if(isset($_REQUEST['form_orderby'])) {
		$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ? $_REQUEST['form_orderby'] : 'create';
	}
}
$orderby = $ORDERHASH[$form_orderby];

$middle_query = "LEFT JOIN form_order_layout AS fol on fol.rto_id = rto.id ";
//Filter Img Facility
if(isset($img_facility_field['field_id']) || isset($sched_date_field['field_id'])) {
  if((isset($form_lbf_img_facility) && !in_array("", $form_lbf_img_facility)) || !empty($form_from_lbf_sched_date) || !empty($form_to_lbf_sched_date)) {
    //$middle_query .= "LEFT JOIN form_order_layout AS fol on fol.rto_id = rto.id ";
  }
}

//Filter Img Facility
if(isset($img_facility_field['field_id'])) {
  if(isset($form_lbf_img_facility) && !in_array("", $form_lbf_img_facility)) {
    $middle_query .= "LEFT JOIN lbf_data ld on ld.form_id = fol.form_id and ld.field_id = '".$img_facility_field['field_id']."' ";
  }
}

//Filter Schedule Date
if(isset($sched_date_field['field_id'])) {
  if(!empty($form_from_lbf_sched_date) || !empty($form_to_lbf_sched_date)) {
    $middle_query .= "LEFT JOIN lbf_data ld1 on ld1.form_id = fol.form_id and ld1.field_id = '".$sched_date_field['field_id']."' "; 
  }
}

$binds = array();
$query = "SELECT " .
  "rto.rto_ordered_by AS requester, rto.pid, rto.date, rto.id, rto_status, ".
	"rto_resp_user, rto_target_date, rto_action, rto_notes, rto_date, " .
	"lo.title as status_title,  " .
	// "oe.pc_insurance, " .
	// "ic.name AS insurance_name, " .
  "p.fname, p.mname, p.lname, p.pubpid, p.street, p.city, p.state, " .
	"p.postal_code, p.email, p.phone_cell, SUBSTRING(fe.date,1,10) AS dolv " . 
  "FROM form_rto AS rto " .
	"LEFT JOIN form_encounter AS fe ON fe.id = (SELECT id FROM form_encounter " .
	"WHERE form_encounter.pid = rto.pid ORDER BY date DESC LIMIT 1) " .
  "LEFT JOIN patient_data AS p ON (rto.pid = p.pid) " .
  $middle_query . 
  "RIGHT JOIN list_options AS lo ON (rto_status = lo.option_id AND " .
  "lo.list_id = 'RTO_Status' AND lo.activity = 1) " .
	// "LEFT JOIN appointment_encounter AS ae USING (encounter) " .
	// "LEFT JOIN openemr_postcalendar_events AS oe ON (ae.eid = oe.pc_eid) " .
	// "LEFT JOIN insurance_companies AS ic ON (oe.pc_insurance = ic.id) " .
  "WHERE rto.pid != '' AND fol.formdir = '".$ORDER_LBF_FROM_ID."' ";
if ($form_to_date) {
  $query .= "AND (($form_date_mode >= ? AND $form_date_mode <= ?) ";
	$binds[] = $form_from_date;
	$binds[] = $form_to_date;
} else {
  $query .= "AND (($form_date_mode >= ? AND $form_date_mode <= ?) ";
	$binds[] = $form_from_date;
	$binds[] = $form_from_date;
}
if($form_no_date) {
	$query .= "OR ($form_date_mode IS NULL OR $form_date_mode = '') ";
}
$query .= ') ';
if ($form_ordered_by && $form_ordered_by != '~all~' && !in_array("", $form_ordered_by)) {
  $query .= "AND rto_ordered_by IN ('".implode("','",$form_ordered_by)."') ";
	//$binds[] = $form_ordered_by;
}
if ($form_user && $form_user != '~all~') {
  $query .= "AND rto_resp_user = ? ";
	$binds[] = $form_user;
}

if ($form_status && !in_array("", $form_status)) {
  $query .= "AND rto_status IN ('".implode("','",$form_status)."') ";
	//$binds[] = $form_status;
}
if ($form_action && !in_array("", $form_action)) {
  $query .= "AND rto_action IN ('".implode("','",$form_action)."') ";
	//$binds[] = $form_action;
}

//Filter Img Facility
if(isset($img_facility_field['field_id'])) {
  if(!empty($form_lbf_img_facility) && !in_array("", $form_lbf_img_facility)) {
    $query .= "AND ld.field_value IN ('".implode("','",$form_lbf_img_facility)."') ";
  }
}

//Filter Img Schedule Date
if(isset($sched_date_field['field_id'])) {
  if(!empty($form_from_lbf_sched_date) && !empty($form_to_lbf_sched_date)) {
    $query .= "AND (ld1.field_value >= ? AND ld1.field_value <= ?) ";
    $binds[] = $form_from_lbf_sched_date;
    $binds[] = $form_to_lbf_sched_date;
  }
}

$query .= "ORDER BY $orderby";

$res='';
if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) {
	$res = sqlStatement($query, $binds);
}

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=rto_report.csv");
  header("Content-Description: File Transfer");
	echo '"' . xl('Responsible') . '",';
  echo '"' . xl('Create Date') . '",';
  echo '"' . xl('Due Date') . '",';
  echo '"' . xl('Patient Last') . '",';
  echo '"' . xl('Patient First') . '",';
  echo '"' . xl('Pid') . '",';
  echo '"' . xl('Street') . '",';
  echo '"' . xl('City') . '",';
  echo '"' . xl('State') . '",';
  echo '"' . xl('ZIP') . '",';
  echo '"' . xl('Email') . '",';
  echo '"' . xl('Cell Phone') . '",';
  // echo '"' . xl('Insurance') . '",';
  echo '"' . xl('Status') . '",';
  echo '"' . xl('Order Type') . '",';
  echo '"' . xl('Ordered By') . '",';
  echo '"' . xl('Last Visit') . '"' . "\n";
} else {
?>
<html>
<head>
<title><?php echo xl('Imaging Orders Report'); ?></title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui', 'jquery-ui-base', 'oemr_ad']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style type="text/css">

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
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>

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

<script type="text/javascript">
  $(document).ready(function(){
    $(".rto_note_container").each(function() {
      var contentHeight = $(this).find('.content.summeryContainer').children().outerHeight();
      if(contentHeight > 62) {
        $(this).find('.actBtn').show();
      } 

      var contentHeight1 = $(this).find('.content.summeryNoteContainer').children().outerHeight();
      if(contentHeight1 > 47) {
        $(this).find('.actBtn').show();
      }
    });

    jQuery('[data-toggle="tooltip"]').tooltip({
        content: function(){
          var element = $( this );
          //return element.attr('title')
          return element.html();
        },
        track: true
    });
  });
</script>

<style type="text/css">
  .summeryContainer table {
    width: auto!important;
    border: 0px !important;
  }

  .summeryContainer table tr:nth-child(odd), .summeryContainer table tr:nth-child(odd),
  .summeryContainer table tr:nth-child(even), .summeryContainer table tr:nth-child(even) {
    background-color: transparent !important;
  }

  .summeryContainer table td, .summeryContainer table td {
    padding: 2px 5px !important;
  }

  .summeryContainer {
    height: 100%;
    font-size: 12px;
    display: inline-block;
  }
  .summeryContainer table tr {
    /*display: none;*/
  }
  .summeryContainer table tr:nth-child(1), 
  .summeryContainer table tr:nth-child(2),
  .summeryContainer table tr:nth-child(3),
  .summeryContainer table tr:nth-child(4) {
    /*display: table-row !important;*/
  }

  .ui-tooltip-content table {
    font-size: 12px;
  }

  .ui-tooltip-content > div {
    font-size: 12px;
  }
</style>

<style type="text/css">
  .rto_note_container .read-more-state {
    display: none;
  }

  .rto_note_container .content.summeryNoteContainer {
    max-height: 45px;
      overflow: hidden;
      height: auto;
      position: relative;
      width: auto;
      margin-bottom: 8px;
  }

  .rto_note_container .content.summeryContainer {
    max-height: 60px;
      overflow: hidden;
      height: auto;
      position: relative;
      width: auto;
      margin-bottom: 8px;
  }

  .rto_note_container .actBtn {
    display: none;
  }

  .rto_note_container .actBtn .readmore {
    display: block;
  }

  .rto_note_container .actBtn .lessmore {
    display: none;
  }

  .read-more-state:checked ~ .content {
    opacity: 1;
    max-height: 999em !important;
  }

  .read-more-state:checked ~ .actBtn .lessmore {
    display: block;
  }

  .read-more-state:checked ~ .actBtn .readmore {
    display: none;
  }

  @media print {
    .rto_note_container .content.summeryNoteContainer {
      max-height: 100%;
    }

    .rto_note_container .content.summeryContainer {
      max-height: 100%;
    }
  }
</style>

</head>
<body class="body_top">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Imaging Orders'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='imaging_order_report.php'>
<div id="report_parameters">
<table>
 <tr>
  <td width='1300px'>
    <div style='float:left'>
      <table class='text'>
        <tr>
          <td class='label'><?php echo xl('Ordered By'); ?>: </td>
          <td><select name='form_ordered_by[]' multiple class="form-control form-control-sm">
						<?php 
						UserSelect($form_ordered_by, false, '', array(), '-- ALL --', true);
						?>
          </select></td>
          <!-- <td class='label'><?php //echo xl('Order Type'); ?>: </td>
          <td><select name='form_action[]' multiple>
						<?php //MultiListSel($form_action, 'RTO_Action', '-- ALL --'); ?>
          </select></td> -->
          <td class='label'><?php echo xl('Status'); ?>: </td>
          <td><select name='form_status[]' multiple class="form-control form-control-sm">
						<?php MultiListSel($form_status, 'RTO_Status', '-- ALL --'); ?>
          </select></td>

          <?php if(!empty($img_facility_field) && isset($img_facility_field['list_id']) && !empty($img_facility_field['list_id'])) { ?>
          <td class='label'><?php echo xl('Imaging Facility'); ?>: </td>
          <td>
            <select name='form_lbf_img_facility[]' multiple class="form-control form-control-sm">
            <?php MultiListSel($form_lbf_img_facility, $img_facility_field['list_id'], '-- ALL --'); ?>
            </select>
          </td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <?php } ?>

         </tr>
         <tr>
          <td class='label'><?php echo xl('Responsible'); ?>: </td>
          <td><select name='form_user' class="form-control form-control-sm">
						<?php 
						MsgUserGroupSelect($form_user, true, false, true);
						?>
          </select></td>
           <td class='label'><?php echo xl('From'); ?>: </td>
           <td style="width: 190px;">
             <input type='text' name='form_from_date' class="wmtInput" id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter As <?php echo $date_title_fmt; ?>' style='width: 150px;'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>
           <td class='label'><?php echo xl('To'); ?>: </td>
           <td style="width: 190px;">
             <input type='text' name='form_to_date' id="form_to_date" class="wmtInput" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter As <?php echo $date_title_fmt; ?>' style='width: 150px;'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'></td>

            <?php if(!empty($sched_date_field)) { ?>
            <td class='label'><?php echo xl('From Schedule Date'); ?>: </td>
            <td style="width: 190px;">
               <input type='text' name='form_from_lbf_sched_date' class="wmtInput" id="form_from_lbf_sched_date" size='10' value='<?php echo oeFormatShortDate($form_from_lbf_sched_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter As <?php echo $date_title_fmt; ?>' style='width: 150px;'>
               <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_lbf_sched_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'>
            </td>
            <td class='label'><?php echo xl('To Schedule Date'); ?>: </td>
            <td style="width: 190px;">
             <input type='text' name='form_to_lbf_sched_date' class="wmtInput" id="form_to_lbf_sched_date" size='10' value='<?php echo oeFormatShortDate($form_to_lbf_sched_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter As <?php echo $date_title_fmt; ?>' style='width: 150px;'>
             <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_lbf_sched_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xl('Click here to choose a date'); ?>'>
            </td>
            <?php } ?>
         </tr>
				 <tr style="height:1px;">
					 <td colspan="2" style="display: none;">
           	 <input type='checkbox' name='form_no_date' id="form_no_date" value='1' <?php echo $form_no_date ? "checked='checked' " : ""; ?> />&nbsp;
					 	<label for="form_no_date" class="label"><?php echo xl('Include tasks with no target date'); ?>?</label></td>
						<td colspan="2" style="display: none;">Use Date&nbsp;<input name="form_date_mode" id="rto_date" type="radio" <?php echo $form_date_mode == 'rto_date' ? 'checked' : ''; ?> value="rto_date" /><label for="rto_date">&nbsp;Created</label>&nbsp;&nbsp;or&nbsp;
						<input name="form_date_mode" id="rto_target_date" type="radio" <?php echo $form_date_mode == 'rto_target_date' ? 'checked' : ''; ?> value="rto_target_date" /><label for="rto_target_date">&nbsp;Due</label></td>
					 <td colspan="2" style="display: none;">
           	 <input type='checkbox' name='form_show_comments' id="form_show_comments" value='1' <?php echo $form_show_comments ? "checked='checked' " : ""; ?> />&nbsp;
					 	<label for="form_show_comments" class="label"><?php echo xl('Show Comments'); ?>?</label></td>
				 </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td><div style='margin-left:15px'>
          <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
					<span><?php echo xl('Submit'); ?></span></a>

          <?php if (isset($_POST['form_refresh']) || isset($_POST['form_orderby']) ) { ?>
          <a href='#' class='css_button' onclick='window.print()'>
					<span><?php echo xl('Print'); ?></span></a>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
					<span><?php echo xl('CSV Export'); ?></span></a>
          <?php } ?>
          </div></td>
      </tr>
     </table>
  </td>
 </tr>
</table>
</div><!-- END REPORT PARAMETERS -->

<?php if (isset($_POST['form_refresh'])) { ?>
	<div id="report_results" class="table-responsive">
	<table class='text table table-sm'>
  <?php if(1 !== 1) { ?>
  <thead class="thead-light">
  	<th><a href="nojs.php" onclick="return dosort('user')"
   	<?php if ($form_orderby == "user") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Responsible User'); ?></a></th>
  	<th><a href="nojs.php" onclick="return dosort('create')"
   	<?php if ($form_orderby == "create") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Create Date'); ?></a></th>
  	<th><a href="nojs.php" onclick="return dosort('due')"
   	<?php if ($form_orderby == "due") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Due Date'); ?></a></th>
  	<th><a href="nojs.php" onclick="return dosort('patient')"
   	<?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Patient'); ?></a></th>
  	<th><a href="nojs.php" onclick="return dosort('pubpid')"
   	<?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php echo xl('ID'); ?></a></th>
  	<th><?php echo xl('Status'); ?></th>
  	<th><?php echo xl('Order Type'); ?></th>
  	<th><a href="nojs.php" onclick="return dosort('ordered')"
   	<?php if ($form_orderby == "order") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Ordered By'); ?></a></th>
  	<th><?php echo xl('Last Visit'); ?></th>
 	</thead>
  <?php } ?>
  <thead class="thead-light">
    <th><a href="nojs.php" onclick="return dosort('ordered')"
    <?php if ($form_orderby == "order") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Provider'); ?></a></th>
    <th><a href="nojs.php" onclick="return dosort('user')"
    <?php if ($form_orderby == "user") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Responsible'); ?></a></th>
    <th><a href="nojs.php" onclick="return dosort('create')"
    <?php if ($form_orderby == "create") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Created'); ?></a></th>
    <th><a href="nojs.php" onclick="return dosort('patient')"
    <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php echo xl('Patient'); ?></a></th>
    <th><?php echo xl('Type'); ?></th>
    <th><?php echo xl('Status'); ?></th>
  </thead>
 	<tbody>
	<?php
	} // END OF REFRESH INCLUDE
} // END OF NOT CSV EXPORT
if ($res) {
 	$lastusername = '';
 	$doc_encounters = 0;
	$sql = 'SELECT * FROM form_encounter WHERE pid=? ORDER BY date DESC LIMIT 1';
 	while ($row = sqlFetchArray($res)) {
   	$username = 'Not Assigned';
		$requested_by = 'Not Specified';
		if(!empty($row['rto_resp_user'])) 
			$username = MsgUserGroupDisplay($row['rto_resp_user']);
		if($row['requester'] != '') 
			$requested_by = MsgUserGroupDisplay($row['requester']);
		$action = ListLook($row['rto_action'],'RTO_Action');
		$pubpid = $row['pid'];
		$id = $row['id'];
   	$errmsg  = "";
		$dolv = oeFormatShortDate($row{'dolv'});

    $patientData = getPatientData($row['pid'], "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
    $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));
    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);
    $patientPubpid = $patientData['pubpid'];

    $clickFun = "handleGoToOrder('".$id."', '".$pubpid."', '".$patientPubpid."', '".$patientName."', '".$patientDOB."')";

		if(!$dolv) $dolv = '--';
		$bgcolor = ($bgcolor == "D6EAF8") ? "FBFCFC" : "D6EAF8";
		if($form_csvexport) {
			echo '"' . display_desc($username) . '","' .
			oeFormatShortDate($row{'rto_ate'}) . '","' .
			oeFormatShortDate($row{'rto_target_date'}) . '","' .
			display_desc($row{'lname'}) . '","' .
			display_desc($row{'fname'}) . '","' .
			display_desc($row{'pubpid'}) . '","' .
			display_desc($row{'street'}) . '","' .
			display_desc($row{'city'}) . '","' .
			display_desc($row{'state'}) . '","' .
			display_desc($row{'postal_code'}) . '","' .
			display_desc($row{'email'}) . '","' .
			display_desc($row{'phone_cell'}) . '","' .
			// display_desc($row{'insurance_name'}) . '","' .
			display_desc(ListLook($row{'rto_status'},'RTO_Status')) . '","' .
			display_desc($action) . '","' .
			display_desc($requested_by) . '","' . $dolv . '"' . "\n";
		} else {
	?>
  <?php if(1 !== 1) { ?>
 	<tr bgcolor='<?php echo $bgcolor ?>'>
  	<td><?php echo $username; ?>&nbsp;</td>
  	<td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
   	<?php echo oeFormatShortDate(substr($row['rto_date'], 0, 10)) ?>&nbsp;</a></td>
  	<td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
   	<?php echo oeFormatShortDate(substr($row['rto_target_date'], 0, 10)) ?>&nbsp;</a></td>
  	<td><a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');"><?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;</a></td>
  	<td><?php echo $row['pubpid']; ?>&nbsp;</td>
  	<td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
   	<?php echo ListLook($row['rto_status'],'RTO_Status'); ?>&nbsp;</a></td>
  	<td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
   	<?php echo $action; ?>&nbsp;</a></td>
		<td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
			<?php echo $requested_by; ?></a></td>
		<td><?php echo $dolv; ?></td>
 	</tr>
  <?php } ?>
  <tr bgcolor='<?php echo $bgcolor ?>'>
    <td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
      <?php echo $requested_by; ?></a></td>
    <td><?php echo $username; ?>&nbsp;</td>
    <td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
    <?php echo oeFormatShortDate(substr($row['rto_date'], 0, 10)) ?>&nbsp;</a></td>
    <td><a href="javascript:goParentPid('<?php echo $row{'pid'}; ?>');"><?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;</a></td>
    <td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
    <?php echo $action; ?>&nbsp;</a></td>
    <td><a href="javascript:void(0);" onclick="<?php echo $clickFun; ?>">
    <?php echo ListLook($row['rto_status'],'RTO_Status'); ?>&nbsp;</a></td>
  </tr>
	<?php 
    if($form_show_comments) { 
	?>
     	<tr>
      	<td>&nbsp;</td>
      	<td colspan="8"><div><?php 
          getImagingOrdersSummary($row{'id'}, $row{'pid'}, $row);
        ?></div></td>
     	</tr>		
	<?php
			}
		} // END OF NOT CSV EXPORT
   	$lastusername = $username;
 	} // END OF QUERY READ LOOP
} // END OF 'res' BOOLEAN CHECK

if(!$form_csvexport) {
?>
	</tbody>
	</table></div>  <!-- END OF RESULTS -->
	<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.'); ?>
	</div>

	<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
	<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
	<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
	</form>
	</body>

	<script type='text/javascript'>
	<?php include($GLOBALS['srcdir'].'/wmt-v2/report_tools.inc.js'); ?>
 	Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 	Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});

  <?php if(!empty($sched_date_field)) { ?>
  Calendar.setup({inputField:"form_from_lbf_sched_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_lbf_sched_date"});
  Calendar.setup({inputField:"form_to_lbf_sched_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_lbf_sched_date"});
  <?php } ?>
	
	<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>
	
	</script>
	</html>
<?php } // END NOT CSV EXPORT ?>
