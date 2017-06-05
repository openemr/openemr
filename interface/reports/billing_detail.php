<?php
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// Created for CrisisPrep
// @author  Ken Chapple <ken@mi-squared.com>
// @link    http://www.mi-squared.com

require_once("../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

$columns = array(
        'DOS' => 'date_of_service',
        'Code Type' => 'code_type',
        'Code' => 'code',
        'PID' => 'pid',
        'Patient Name' => 'patient_name',
        'Patient DOB' => 'patient_DOB',
        'Provider Name' => 'provider_name',
        'Encounter' => 'encounter',
        'Code Text' => 'code_text',
        'Billed' => 'billed',
        'Payer ID' => 'payer_id',
        'Bill Process' => 'bill_process',
        'Bill Date' => 'bill_date',
        'Process Date' => 'process_date',
        'Process File' => 'process_file',
        'Price Level' => 'pricelevel',
        'Modifier' => 'modifier',
        'Units' => 'units',
        'Fee' => 'fee',
        'Justify' => 'justify',
        'Target' => 'target',
        'X12 Partner' => 'x12_partner_id',
        'NDC Info' => 'ndc_info',
        'Primary Ins Name' => 'primary_insurance_name',
        'Primary Ins #' => 'primary_insurance_number',
        'Second Ins Name' => 'secondary_insurance_name',
        'Second Ins #' => 'secondary_insurance_number',
        'Tertiary Ins Name' => 'tertiary_insurance_name',
        'Tertiary Ins #' => 'tertiary_insurance_number');

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'date'  => 'date_of_service',
);

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = $_POST['form_provider'];
$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'date';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
$query = "SELECT DATE_FORMAT(B.date,'%Y-%m-%d') AS date_of_service, B.code_type, PD.pricelevel,
    B.code, B.pid, CONCAT( PD.fname, ' ', PD.lname ) AS patient_name, PD.DOB AS patient_DOB, 
    B.provider_id, CONCAT( U.fname, ' ', U.lname ) AS provider_name, 
    B.groupname, B.encounter, B.code_text, B.billed, B.activity, B.payer_id, B.bill_process, 
    B.bill_date, B.process_date, B.process_file, B.modifier, B.units, B.fee, B.justify, B.target, 
    B.x12_partner_id, B.ndc_info, B.notecodes,
    ( SELECT C.name FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'primary'  ORDER BY I.date DESC LIMIT 1) AS primary_insurance_name,
    ( SELECT I.policy_number FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'primary'  ORDER BY I.date DESC LIMIT 1) AS primary_insurance_number,
    ( SELECT C.name FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'secondary'  ORDER BY I.date DESC LIMIT 1) AS secondary_insurance_name,
    ( SELECT I.policy_number FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'secondary'  ORDER BY I.date DESC LIMIT 1) AS secondary_insurance_number,
    ( SELECT C.name FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'tertiary'  ORDER BY I.date DESC LIMIT 1) AS tertiary_insurance_name,
    ( SELECT I.policy_number FROM insurance_data I LEFT JOIN insurance_companies C ON I.provider = C.id WHERE I.pid = B.pid AND I.type = 'tertiary'  ORDER BY I.date DESC LIMIT 1) AS tertiary_insurance_number
    FROM billing B 
    LEFT JOIN patient_data PD ON B.pid = PD.pid
    LEFT JOIN users U ON B.provider_id = U.id 
    WHERE B.activity = '1' ";

if ( $form_to_date ) {
  $query .= "AND B.date >= '$form_from_date 00:00:00' AND B.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND B.date >= '$form_from_date 00:00:00' ";
}
if ( $form_provider ) {
  $query .= "AND U.id = '$form_provider' ";
}
$query .= "ORDER BY $orderby";

$res = sqlStatement( $query );


//To export as csv - Start
if ( isset( $_GET['exportcsv'] ) ) {
	$export_filename="billing_detail".date("Ymd",time());
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$export_filename.'.csv');
	$output = fopen('php://output', 'w');
	fputcsv( $output, array_keys( $columns ) );
	while ( $export_row = sqlFetchArray( $res ) ) {
		$fields = array();
		foreach ( $columns as $label => $key ) {
		    $fields[] = $export_row[$key];
		}
		fputcsv( $output, $fields );
	}
	exit;
}

?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Billing Details','e'); ?></title>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
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

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

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
 
 function export_csv()
 {
	document.forms[0].action='billing_detail.php?exportcsv=1';
	document.forms[0].submit();
	
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Billing','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='billing_detail.php'>

<div id="report_parameters">
<table>
 <tr>
  <td width='550px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
			   <?php xl('Provider','e'); ?>:
			</td>
			<td>
				<?php

				 // Build a drop-down list of providers.
				 //

				 $query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 AND active = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

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
		<tr>
			<td class='label'>
			   <?php xl('From','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'>
			   <?php xl('To','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
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
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").attr("action","billing_detail.php"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					<a href='#' class='css_button' onclick='export_csv()'>
						<span>
							<?php xl('Export as CSV','e'); ?>
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

<?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
<div id="report_results">
<table>

 <thead>
    <?php foreach ( $columns as $label => $key ) { ?>
	<th><?php xl( $label, 'e' ); ?></th>
	<?php } ?>
 </thead>
 <tbody>
<?php while ( $row = sqlFetchArray( $res ) ) { ?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
    <?php foreach ( $columns as $label => $key ) { ?>
	<td><?php echo $row[$key];?></td>
	<?php } ?>
 </tr>
<?php } ?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
