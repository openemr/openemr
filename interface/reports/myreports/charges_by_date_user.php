<?php
// Copyright (C) 2019-2022 Williams Medical Technologies (WMT)
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
require_once($GLOBALS['srcdir']."/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;
use OpenEMR\Common\Acl\AclMain;

if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_date_sort'])) $_POST['form_date_sort'] = 'post';
set_time_limit(0);
$rpt_lines = 0;

function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

function userTotals() {
	global $form_user, $user_id, $user_desc, $user_qty, $user_total;
  global $prev_sort, $prev_sort_desc, $prev_user, $prev_user_desc;
	global $sec_sort, $sec_desc, $form_csvexport;
	global $prim_sort, $prim_desc, $prim_sort_left, $prim_desc_left;

	if($user_id != $prev_user && $prev_user) {
   	if(!$form_csvexport) { ?>
 			<tr bgcolor="#ddffff">
  			<td class="detail" colspan="3">
					<?php echo xl('Total For') . ': ',display_desc($prev_user_desc); ?></td>
				<td class="detail" colspan="5">&nbsp;</td>
  			<td align="right"><?php echo $user_qty; ?></td>
  			<td align="right"><?php bucks($user_total); ?></td>
			</tr>
			<?php if($user_id && $user_id != '^end^') { ?>
			<tr><td colspan="10">&nbsp;</td></tr>
			<tr>
  			<td class="detail" colspan="4">
					<?php echo display_desc($user_desc); ?>&nbsp;</td>
				<td class="detail" colspan="6">&nbsp;</td>
			</tr>	
			<?php	
			}
		}
   	$user_total     = $user_qty = 0;
  	$prev_user      = $user_id;
  	$prev_user_desc = $user_desc;
	}
	if(!$prev_user) {
		if(!$form_csvexport) {
	?>
		<tr>
  		<td class="detail" colspan="4">
				<?php echo display_desc($user_desc); ?>&nbsp;</td>
			<td class="detail" colspan="6">&nbsp;</td>
		</tr>	
	<?php
		}
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function primSortTotals() {
  global $prim_sort, $prim_desc, $prim_total, $prim_qty;
  global $prev_sort, $prev_sort_desc, $prim_sort_left, $prim_desc_left;
	global $sec_sort, $sec_desc, $form_details, $form_csvexport, $dtl_lines;


  if ($prim_sort != $prev_sort && $prev_sort) {
    // Print primary sort total.
    if ($form_csvexport) {
			// If we are printing details we don't total for spreadsheets
      if(!$form_details) {
        echo '"' . display_desc($prev_sort) . '",';
        echo '"' . display_desc($prev_desc)  . '",';
        echo '"' . $prim_qty. '",';
        echo '"'; bucks($prim_total); echo '"' . "\n";
			}
    } else { 
			if(!$form_details) { ?>
 				<tr bgcolor="#ddffff">
  				<td class="detail"><?php echo display_desc($prev_sort); ?>&nbsp;</td>
  				<td class="detail"><?php echo display_desc($prev_sort_desc); ?>&nbsp;</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_total); ?></td>
 				</tr>
			<?php } else { ?>
 				<tr bgcolor="#ddffff">
  				<td class="detail" colspan="4">
							<?php echo 'Total For: ',display_desc($prev_sort),'&nbsp;-&nbsp;',
								display_desc($prev_sort_desc); ?></td>
					<td class="detail" colspan="4">&nbsp;</td>
  				<td align="right"><?php echo $prim_qty; ?></td>
  				<td align="right"><?php bucks($prim_total); ?></td>
				</tr>
				<?php if($prim_sort && $prim_sort != '^end^') { ?>
				<!-- tr> <td class="detail" colspan="8">&nbsp;</td></tr -->	
			<?php
				}
      } // End not csv export
			// echo "Finished the total Line<br>\n";
    }
    $prim_total     = $prim_qty = 0;
    $prev_sort      = $prim_sort;
    $prev_sort_desc = $prim_desc;
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
  } else if(!$prev_sort) {
		$prim_sort_left = $prim_sort;
		$prim_desc_left = $prim_desc;
	}
}

function thisLineItem($transdate, $enc, $qty, $amount, $provider, $dos) {
  global $prim_sort, $prim_desc, $prim_total, $prim_qty;
	global $sec_sort, $sec_desc, $grand_total, $grand_qty;
	global $form_user, $user_id, $user_desc, $user_qty, $user_total;
  global $prev_sort, $prev_sort_desc, $prev_user, $prev_user_desc;
	global $prim_sort_left, $prim_desc_left, $dtl_lines;
	global $form_csvexport, $form_details, $form_order, $bgcolor;

  $rowamount = sprintf('%01.2f', $amount);

	primSortTotals();
	userTotals();

	$bgcolor = ($bgcolor == "FFDDDD") ? "FFFFDD" : "FFDDDD";

  if($form_details) {
    if($form_csvexport) {
      echo '"' . display_desc($prim_sort) . '","' .
      display_desc($prim_desc) . '","' . display_desc($sec_sort) . '","' .
      display_desc($sec_desc) . '","' . display_desc($enc) . '","' .
			display_desc($provider) . '","' . oeFormatShortDate($dos) . '","' . 
      oeFormatShortDate($transdate) . '","' . display_desc($qty) . '","'; 
      bucks($rowamount);
			echo '"' . "\n";
    } else {
			$patient_id = ($form_order == 'CPT') ? $sec_sort : $prim_sort;
?>

 <tr bgcolor="<?php echo $bgcolor; ?>">
  <td class="detail">
		<?php echo display_desc($prim_sort_left); $prim_sort_left = "&nbsp;"; ?>
	&nbsp;</td>
  <td class="detail">
		<?php echo display_desc($prim_desc_left); $prim_desc_left = "&nbsp;"?>
	&nbsp;</td>
  <td class="detail"><?php echo display_desc($sec_sort); ?>&nbsp;</td>
  <td class="detail"><?php echo display_desc($sec_desc); ?>&nbsp;</td>
  <td class="detail">
   <!-- a href='../../patient_file/encounter/patient_encounter.php?pid=<?php // echo $patient_id; ?>&set_encounter=<?php // echo $enc; ?>' -->
   <?php echo $enc; ?>&nbsp;</td>
	<td><?php echo htmlspecialchars($provider, ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate($dos); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate($transdate); ?>&nbsp;</td>
  <td align="right"><?php echo $qty; ?></td>
  <td align="right"><?php bucks($rowamount); ?></td>
 </tr>
<?php

    } // End not csv export
  } // end details
  $prim_total     += $amount;
  $user_total     += $amount;
  $grand_total    += $amount;
  $prim_qty       += $qty;
  $user_qty       += $qty;
  $grand_qty      += $qty;
	$prev_user      =  $user_id;
	$prev_user_desc =  $user_desc;
	$prev_sort      =  $prim_sort;
	$prev_sort_desc =  $prim_desc;
} // end line print function

if (! AclMain::aclCheckCore('acct', 'rep')) die(xl("Unauthorized access."));

$default_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(!isset($_POST['form_from_date'])) {
	$_POST['form_from_date'] = $default_date;
} else $POST['form_from_date'] = DateToYYYYMMDD($_POST['form_from_date']);
if(!isset($_POST['form_to_date'])) {
	$_POST['form_to_date'] = $default_date;
} else $_POST['form_to_date'] = DateToYYYYMMDD($_POST['form_to_date']);
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_user      = isset($_POST['form_user']) ? $_POST['form_user'] : '';
$form_provider  = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_details   = isset($_POST['form_details']) ? $_POST['form_details'] : '1';
$form_order     = isset($_POST['form_order']) ? $_POST['form_order'] : 'PAT';
$form_csvexport = $_POST['form_csvexport'];
$form_date_sort = $_POST['form_date_sort'];
$form_details = 1;

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=chgs_by_user_date.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if ($form_details) {
		echo ($form_order == 'CPT') ? '"CPT",' : '"PID",';
		echo ($form_order == 'CPT') ? '"Description",' : '"Patient Name",';
		echo ($form_order == 'CPT') ? '"PID",' : '"CPT",';
		echo ($form_order == 'CPT') ? '"Patient Name",' : '"Description",';
    echo '"Encounter",';
    echo '"Provider",';
    echo '"Service Dt",';
    echo '"Post Date",';
    echo '"Qty",';
    echo '"Amount"' . "\n";
  } else {
		echo ($form_order == 'CPT') ? '"CPT",' : '"PID",';
		echo ($form_order == 'CPT') ? '"Description",' : '"Patient Name",';
    echo '"Qty",';
    echo '"Total"' . "\n";
  }
	// End of Export
} else {
?>
<html>
<head>
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
    #report_results {
       margin-top: 30px;
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

<title><?php echo xl('Charges by Date and Operator') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Charges by Date and Operator'); ?></span>

<form method='post' action='charges_by_date_user.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='1000px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'><?php echo xl('Facility'); ?>:</td>
			<td>
			<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
			</td>
			<td class='label'><?php echo xl('From'); ?>:</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
			   <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
			<td class='label'><?php echo xl('To'); ?>:</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Enter as <?php echo $date_title_fmt; ?>'>
			   <img src='<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
			<td class="label"><input name="form_date_sort" id="date_sort_post" type="radio" value="post" <?php echo $form_date_sort == 'post' ? 'checked="checked"' : ''; ?> /><label for="date_sort_post"><?php echo xl('Dt Posted'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="form_date_sort" id="date_sort_serv" type="radio" value="serv" <?php echo $form_date_sort == 'serv' ? 'checked="checked"' : ''; ?> /><label for="date_sort_serv"><?php echo xl('Service Dt'); ?></label></td>
		</tr>
		<tr>
			<td class='label'>
				<?php echo xl('Operator'); ?>:
			</td>
      <td style='width: 18%;'><?php
        // Build a drop-down list of providers.
        $query = "SELECT id, username, lname, fname FROM users " .
					"WHERE username != '' AND active='1' ORDER BY lname, fname";
        $ures = sqlStatement($query);

        echo "   <select name='form_user' id='form_user'>\n";
        echo "    <option value=''";
				if($form_user == '') { echo " selected"; }
				echo ">-- " . xl('All') . " --</option>\n";
        while ($urow = sqlFetchArray($ures)) {
          $provid = $urow['id'];
          echo "    <option value='$provid'";
          if ($provid == $form_user) echo " selected";
          echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
        }
        echo "   </select>\n";
        ?></td>
			<td class="label"><?php echo xl('Order By'); ?>:</td>
			<td><select name="form_order" id="form_order">
				<option value="PAT" <?php echo ($form_order == "PAT") ? 'selected' : ''; ?> ><?php echo xl('Patient'); ?></option>
				<option value="CPT" <?php echo ($form_order == "CPT") ? 'selected' : ''; ?> ><?php echo xl('Procedure'); ?></option>
			</select></td>
      <td class='label'><?php xl('Provider','e'); ?>: </td>
      <td colspan="3"><?php
      // Build a drop-down list of providers.
      $query = "SELECT id, username, lname, fname FROM users " .
			 "WHERE authorized=1 AND username!='' AND active='1' ".
			 "AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
			 "UPPER(specialty) LIKE '%SUPERVISOR%') ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($form_provider == '') echo 'selected="selected"';
			echo ">-- " . xl('All') . " --</option>\n";
      echo "    <option value='none'";
			if($form_provider == 'none') echo 'selected="selected"';
			echo ">-- " . xl('No Provider Assigned') . " --</option>\n";
      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $form_provider) echo " selected";
        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
     }
     echo "   </select>\n";
     ?></td>
			<!--td>
			   <input type='checkbox' name='form_details'<?php // if ($form_details) echo ' checked'; ?>>
			   <?php // xl('Details','e'); ?>
			</td -->
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
	<div id="report_results">
	<table >
 	<thead>
  	<th> <?php echo ($form_order == 'CPT') ? xl('CPT') : xl('PID'); ?> </th>
  	<th>
   	<?php echo ($form_order == 'CPT') ? xl('Description') : xl('Patient Name'); ?>
  	</th>
  	<th> <?php echo ($form_order == 'CPT') ? xl('PID') : xl('CPT'); ?> </th>
  	<th>
   	<?php echo ($form_order == 'CPT') ? xl('Patient Name') : xl('Description'); ?>
  	</th>
  	<th> <?php echo xl('Encounter'); ?> </th>
  	<th> <?php echo xl('Provider'); ?> </th>
  	<th> <?php echo xl('Service Dt'); ?> </th>
  	<th> <?php echo xl('Post Dt'); ?> </th>
  	<th align="right"> <?php echo xl('Qty'); ?> </th>
  	<th align="right"> <?php echo xl('Amount'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date . ' 00:00:00';
  $to_date   = $form_to_date . ' 23:59:59';

  $prim_sort = $prim_desc = $user_id = $user_desc = '';
	$sec_sort = $sec_desc = $bgcolor = '';
	$prim_total = $prim_qty = $user_total = $user_qty = 0;
	$dtl_lines = $rpt_lines = $grand_total = $grand_qty = 0;
	$prev_sort = $prev_sort_desc =  $prev_user = $prev_user_desc = '';
	$binds = array();

  $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, " .
		"b.units, b.date AS post_dt, b.code_text, b.user, b.provider_id, " .
		"fe.date AS serv_dt, fe.facility_id, fe.invoice_refno, " .
		"fe.provider_id AS pr_id, " .
		"pat.lname AS plast, pat.fname AS pfirst, pat.mname AS pmi, " .
		"op.lname AS ulast, op.fname AS ufirst, op.mname AS umi " .
		// "dr.lname AS drlast, dr.fname AS drfirst, dr.mname AS drmi, " .
		// "prov.lname AS provlast, prov.fname AS provfirst, prov.mname AS provmi " .
		// "lo.title " .
    "FROM billing AS b " .
    "JOIN code_types AS ct ON ct.ct_key = b.code_type " .
    "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter " .
    "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier " .
		"LEFT JOIN patient_data AS pat ON b.pid = pat.pid " .
		"LEFT JOIN users as op ON b.user = op.id " .
		// "LEFT JOIN users AS dr ON b.provider_id = dr.id " .
		// "LEFT JOIN users AS prov ON fe.provider_id = prov.id " .
     // "LEFT JOIN list_options AS lo ON lo.list_id = 'superbill' AND lo.option_id = c.superbill " .
    "WHERE b.code_type != 'COPAY' AND b.activity = 1 AND b.fee != 0 AND ";
	if($form_date_sort == 'serv') {
    $query .= "fe.date >= ? AND fe.date <= ?";
	} else {
    $query .= "b.date >= ? AND b.date <= ?";
	}
	$binds[] = $from_date;
	$binds[] = $to_date;
  if($form_facility) {
		$query .= " AND fe.facility_id = ?"; 
		$binds[] = $form_facility;
	}
  if ($form_user) {
		$query .= " AND b.user = ?"; 
		$binds[] = $form_user;
	}
  if ($form_provider == 'none') {
		$query .= " AND ((b.provider_id = 0 OR b.provider_id IS NULL) AND " .
		"(fe.provider_id = 0 OR fe.provider_id IS NULL)) "; 
  } else if ($form_provider) {
		$query .= " AND ( b.provider_id = ? OR " .
		"(b.provider_id = 0 AND fe.provider_id = ?) )";
		$binds[] = $form_provider;
		$binds[] = $form_provider;
	}
	if($form_order == 'CPT') {
		$query .= " ORDER BY b.user, b.code, b.pid";
	} else {
		$query .= " ORDER BY b.user, b.pid, ";
		if($form_date_sort == 'serv') {
			$query .= "fe.date";
		} else {
			$query .= "b.date";
		}
	}
  //$query .= " ORDER BY lo.title, b.code, fe.date, fe.id";

  $res = sqlStatement($query, $binds);
  while ($row = sqlFetchArray($res)) {
		$user_id = $row['user'];
		$user_desc = $row['ulast'].', '.$row['ufirst'];
		$prim_sort = ($form_order == 'CPT') ? $row['code'] : $row['pid'];
		$prim_desc = ($form_order == 'CPT') ? $row['code_text'] : $row['plast'].','.$row['pfirst'];
		$sec_sort = ($form_order == 'CPT') ? $row['pid'] : $row['code'];
		$sec_desc = ($form_order == 'CPT') ? $row['plast'].','.$row['pfirst'] : $row['code_text'];
		$rendering = $row{'provider_id'} ? $row{'provider_id'} : $row{'pr_id'};
		$drname = 'Not Specified';
		if($rendering) {
			$dr = sqlQuery('SELECT * FROM users WHERE id = ?',array($rendering));
			$drname = $dr{'lname'} . ', ' . $dr{'fname'};
		}
    thisLineItem(substr($row['post_dt'], 0, 10), $row['encounter'], 
			$row['units'], $row['fee'], $drname, 
			substr($row['serv_dt'], 0, 10));
		$dtl_lines++;
		$rpt_lines++;
  }

	$prim_sort = '^end^';
	$user_id = '^end^';
	primSortTotals();
	userTotals();

	if(!$form_user && !$form_csvexport) {
	?>
 	<tr bgcolor="#ddffff">
 	 <td class="detail" colspan="8"> <?php echo xl('Grand Total'); ?> </td>
 	 <td align="right"> <?php echo $grand_qty; ?> </td>
 	 <td align="right"> <?php bucks($grand_total); ?> </td>
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

<!-- stuff for the popup calendar -->
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", dfFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
