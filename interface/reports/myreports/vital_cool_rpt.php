<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/wmt-v2/list_tools.inc");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';

function RuleSel($this_rule) {
	global $rule_options;
  echo '<option value=""> - Not Used - </option>';
  foreach($rule_options as $rule => $def) {
    echo '<option value="' . $rule . '"';
    if(in_array($this_rule, $_POST['form_rule'])) {
			echo ' selected="selected"';
		}
    echo ">" . htmlspecialchars($def[0], ENT_QUOTES);
    echo "</option>";
  }
}

function ListSel($thisField, $thisList, $empty_label = '') {
  $rlist= sqlStatement("SELECT * FROM list_options WHERE list_id=? AND ".
		"seq >= 0 ORDER BY seq, title",array($thisList));
	if($empty_label) {
  	echo "<option value=''";
  	echo ">$empty_label&nbsp;</option>";
	}
  while ($rrow= sqlFetchArray($rlist)) {
    echo "<option value='" . $rrow{'option_id'} . "'";
    if($thisField == $rrow{'option_id'}) {
			echo " selected='selected'";
		} else if(empty($thisField)) {
			if($rrow{'is_default'} == 1) echo " selected='selected'";
		}
    echo ">" . htmlspecialchars($rrow{'title'}, ENT_NOQUOTES);
    echo "</option>";
  }
}

function ListLook($thisData, $thisList) {
  if($thisData == '') return ''; 
  $rret=sqlQuery("SELECT * FROM list_options WHERE list_id=? ".
        "AND option_id=?", array($thisList, $thisData));
	if($rret{'title'}) {
    $dispValue= $rret{'title'};
  } else {
    $dispValue= '* Not Found *';
  }
  return $dispValue;
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$default_date = date('Y-m-d', $last_year);
if(!isset($_POST['form_from_date'])) $_POST['form_from_date'] = $default_date;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = date('Y-m-d');
if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_details'])) $_POST['form_details'] = false;
if(!isset($_POST['form_diags'])) $_POST['form_diags'] = '';
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_provider_type = 
 	isset($_POST['form_provider_type']) ? $_POST['form_provideri_type'] : 'pat';
$form_csvexport = $_POST['form_csvexport'];
$form_details = $_POST['form_details'];

$exclude_fields = LoadList('form_vital_analysis_exclude');
$rule_options = array();
$rule_options['patient_age'] = array('Age', '&gt;=|&lt;=');
$rule_options['patient_data-sex'] = array('Sex', '==|!=');
$fields = sqlListFields('form_vitals');
$fields = array_slice($fields,7);
foreach($fields as $fld) {
	if(in_array($fld, $exclude_fields)) continue;
	$title = ucfirst(str_replace('_', ' ', $fld)) . ' (From Vitals)';
	$type = '&gt;=|&lt;=';
	if($fld == 'flu' || $fld == 'h_pylori' || $fld == 'mono' || $field == 'strep_a') $type = '==|!=';

	$rule_options['form_vitals-'.$fld] = array($title, $type);
}
$rule_max = 10;
if(isset($GLOBALS['wmt::vital_rpt_max_filters')) $rule_max = $GLOBALS['wmt::vital_rpt_max_filters'];

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=metric_analysis.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo '"PID",';
		echo '"Patient Name",';
		echo '"DOB",';
		echo '"Phone",';
		echo '"Provider",';
    echo '"Date",';
		echo '"Description"';
		echo "\n";
  } else {
		echo '"Provider",';
		echo '"Total Matched",';
		echo '"Total Patients"';
		echo "\n";
  }
	// End of Export
} else {
?>
<html>
<head>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
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
    #report_results {
       margin-top: 30px;
    }
}

@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}
</style>

<title><?php echo xl('Clinical Data by Date and Value') ?></title>
</head>

<body class="body_top">
<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Clinical Data Query'); ?></span>

<form method='post' action='vital_cool_rpt.php' id='theform'>
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='900px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td>
				<table>
					<tr>
					<?php 
					$rule_cnt = 0;
					while($rule_cnt < $rule_max) {
						list($comp1, $comp2) = explode('|',$rule_options[$rule][1]);
					?>
						<td class='label'><?php echo xl($rule_options[$rule][0]); ?>:</td>
      			<td><select name="form_rule_<?php echo $rule_cnt; ?>" id="form_rule_<?php echo $rule_cnt; ?>">
							<?php RuleSel($rule); ?>
      			</select></td>
						<td class='label'><?php echo xl('Values'); ?>&nbsp;<?php echo $comp1; ?></td>
						<td><input name="form_min_val_<?php echo $rule_cnt; ?>" id="form_min_val_<?php echo $rule_cnt; ?>" type="text" style="width: 80px;" value="<?php echo $_POST['form_min_val]'; ?>" title="Enter the lowest value you wish to include in the report" /></td>
						<td class='label'><?php echo xl('Values'); ?> &lt;= </td>
						<td><input name="form_max_val" id="form_max_val" type="text" style="width: 80px;" value="<?php echo $_POST['form_max_val']; ?>" title="Enter the highest value you wish to include in the report" /></td>
					</tr>
				</table>
			</td>
			<td>
				<table>
					<tr>
						<td class='label'><?php echo xl('From'); ?>:</td>
						<td>
			   			<input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   			<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php echo xl('Click here to choose a date'); ?>'>
						</td>
						<td class='label'><?php echo xl('To'); ?>:</td>
						<td>
			   			<input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
							onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   			<img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
							id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
							title='<?php echo xl('Click here to choose a date'); ?>'>
						</td>
					</tr>
				</table>
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

					<?php if ($_POST['form_refresh'] || $_POST['form_csvexport']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span><?php echo xl('Print'); ?></span></a>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
						<span><?php echo xl('CSV Export'); ?></span></a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div> <!-- end of parameters -->

<?php
	if($_POST['form_refresh']) {
?>
	<div id="report_results" style="width: 100%;">
	<table style="width: 100%;">
 	<thead>
  <th> <?php echo xl('PID'); ?> </th>
  <th> <?php echo xl('Patient Name'); ?> </th>
  <th> <?php echo xl('DOB'); ?> </th>
  <th> <?php echo xl('Phone'); ?> </th>
  <th> <?php echo xl('Provider'); ?> </th>
  <th> <?php echo xl('Date'); ?> </th>
  <th> <?php echo xl('Description'); ?> </th>
  <th> <?php echo xl('Value'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date . ' 00:00:00';
  $to_date   = $form_to_date . ' 23:59:59';
	$binds = array($from_date, $to_date);
	$sql = 'SELECT rd.date, rd.pid, item, complete, result, '.
		'p.lname, p.fname, p.mname, DOB, phone_home, pubpid, '.
		'u.lname AS drlname, u.fname AS drfname, u.mname AS drmname '.
		'FROM rule_patient_data AS rd LEFT JOIN patient_data AS p USING (pid) '.
		'LEFT JOIN users AS u ON (p.providerID = u.id) '.
		'WHERE (rd.date >= ? AND rd.date <= ?) ';
	if($_POST['form_rule'] != '') {
		$sql .= 'AND item = ? ';
		$binds[] = $_POST['form_rule'];
	}
	if($_POST['form_min_val'] != '') {
		$sql .= 'AND result >= ? ';
		$binds[] = $_POST['form_min_val'];
	}
	if($_POST['form_max_val'] != '') {
		$sql .= 'AND result <= ? ';
		$binds[] = $_POST['form_max_val'];
	}
	$sql .= 'GROUP BY pubpid ORDER BY pubpid ASC';

	$fres = sqlStatement($sql, $binds);
	
  $bgcolor = '';
	$dtl_line = $rpt_line = 0;

	while($dtl = sqlFetchArray($fres)) {
		if($form_details) {
			if($form_csvexport) {
      	echo '"'.display_desc($dtl{'pubpid'}).'","';
      	echo display_desc($dtl{'fname'} . ' ' . $dtl{'lname'}) . '","';
      	echo oeFormatShortDate($dtl{'DOB'}) . '","';
      	echo display_desc($dtl{'phone_home'}) . '","';
      	echo display_desc($dtl{'drfname'} . ' ' . $dtl{'drlname'}) . '","';
      	echo oeFormatShortDate(substr($dtl{'date'},0,10)) . '","';
      	echo display_desc(ListLook($dtl{'item'}, 'rule_action')) . '","';
      	echo display_desc($dtl{'result'}) . '","';
				echo '"' . "\n";
			} else {
				$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";
			?>
				<tr bgcolor="<?php echo $bgcolor; ?>">
  				<td class="detail"><?php echo display_desc($dtl{'pubpid'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($dtl{'fname'} . ' ' . $dtl{'lname'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo oeFormatShortDate($dtl{'DOB'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($dtl['phone_home']); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($dtl{'drfname'} . ' ' . $dtl{'drlname'}); ?>&nbsp;</td>
  				<td class="detail"><?php echo oeFormatShortDate(substr($dtl{'date'},0,10)); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc(ListLook($dtl{'item'}, 'rule_action')); ?>&nbsp;</td>
      		<td class="detail"><?php echo display_desc($dtl{'result'}); ?>&nbsp;</td>
 				</tr>
			<?php
			}
		}
		$rpt_line++;
	}

	if(!$form_csvexport && $rpt_line) {
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="7"><?php echo xl('Total Number of Patients'); ?> </td>
 	 <td align="right"><?php echo $rpt_line; ?></td>
 	</tr>

<?php
	}
}

if(!$_POST['form_csvexport']) {
?>

</table>
</div> <!-- report results -->
<?php if(!$rpt_lines) { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

</form>
</body>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
