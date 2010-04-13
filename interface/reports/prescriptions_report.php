<?php
 // Copyright (C) 2006, 2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists prescriptions and their dispensations according
 // to various input selection criteria.
 // 
 // Fix drug name search to work in a broader sense - tony@mi-squared.com 2010
 
 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
 require_once("../drugs/drugs.inc.php");
 require_once("$srcdir/formatting.inc.php");

 $form_from_date  = fixDate($_POST['form_from_date'], date('Y-01-01'));
 $form_to_date    = fixDate($_POST['form_to_date']  , date('Y-m-d'));
 $form_patient_id = trim($_POST['form_patient_id']);
 $form_drug_name  = trim($_POST['form_drug_name']);
 $form_lot_number = trim($_POST['form_lot_number']);
 $form_facility   = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Prescriptions and Dispensations','e'); ?></title>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for receipt display.
 function show_receipt(payid) {
  // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
  return false;
 }

</script>

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
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
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Prescriptions and Dispensations','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='prescriptions_report.php'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<?php xl('Facility','e'); ?>:
			</td>
			<td>
				<?php
				 // Build a drop-down list of facilities.
				 //
				 $query = "SELECT id, name FROM facility ORDER BY name";
				 $fres = sqlStatement($query);
				 echo "   <select name='form_facility'>\n";
				 echo "    <option value=''>-- " . xl('All Facilities') . " --\n";
				 while ($frow = sqlFetchArray($fres)) {
				  $facid = $frow['id'];
				  echo "    <option value='$facid'";
				  if ($facid == $form_facility) echo " selected";
				  echo ">" . $frow['name'] . "\n";
				 }
				 echo "    <option value='0'";
				 if ($form_facility === '0') echo " selected";
				 echo ">-- " . xl('Unspecified') . " --\n";
				 echo "   </select>\n";
				?>
			</td>
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
		<tr>
			<td class='label'>
			   <?php xl('Patient ID','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_patient_id' size='10' maxlength='20' value='<?php echo $form_patient_id ?>'
				title=<?php xl('Optional numeric patient ID','e','\'','\''); ?> />
			</td>
			<td class='label'>
			   <?php xl('Drug','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_drug_name' size='10' maxlength='250' value='<?php echo $form_drug_name ?>'
				title=<?php xl('Optional drug name, use % as a wildcard','e','\'','\''); ?> />
			</td>
			<td class='label'>
			   <?php xl('Lot','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_lot_number' size='10' maxlength='20' value='<?php echo $form_lot_number ?>'
				title=<?php xl('Optional lot number, use % as a wildcard','e','\'','\''); ?> />
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
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
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
</div> <!-- end of parameters -->

<?php
 if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table>
 <thead>
  <th> <?php xl('Patient','e'); ?> </th>
  <th> <?php xl('ID','e'); ?> </th>
  <th> <?php xl('RX','e'); ?> </th>
  <th> <?php xl('Drug Name','e'); ?> </th>
  <th> <?php xl('NDC','e'); ?> </th>
  <th> <?php xl('Units','e'); ?> </th>
  <th> <?php xl('Refills','e'); ?> </th>
  <th> <?php xl('Instructed','e'); ?> </th>
  <th> <?php xl('Reactions','e'); ?> </th>
  <th> <?php xl('Dispensed','e'); ?> </th>
  <th> <?php xl('Qty','e'); ?> </th>
  <th> <?php xl('Manufacturer','e'); ?> </th>
  <th> <?php xl('Lot','e'); ?> </th>
 </thead>
 <tbody>
<?php
 if ($_POST['form_refresh']) {
  $where = "r.date_modified >= '$form_from_date' AND " .
   "r.date_modified <= '$form_to_date'";
  //if ($form_patient_id) $where .= " AND r.patient_id = '$form_patient_id'";
  if ($form_patient_id) $where .= " AND p.pubpid = '$form_patient_id'";
  if ($form_drug_name ) $where .= " AND (d.name LIKE '$form_drug_name' OR r.drug LIKE '$form_drug_name')";
  if ($form_lot_number) $where .= " AND i.lot_number LIKE '$form_lot_number'";

  $query = "SELECT r.id, r.patient_id, " .
   "r.date_modified, r.dosage, r.route, r.interval, r.refills, r.drug, " .
   "d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions, " .
   "s.sale_id, s.sale_date, s.quantity, " .
   "i.manufacturer, i.lot_number, i.expiration, " .
   "p.pubpid, ".
   "p.fname, p.lname, p.mname, u.facility_id " .
   "FROM prescriptions AS r " .
   "LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id " .
   "LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id " .
   "LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id " .
   "LEFT OUTER JOIN users AS u ON u.id = r.provider_id " .
   "WHERE $where " .
   //"ORDER BY p.lname, p.fname, r.patient_id, r.id, s.sale_id";
   "ORDER BY p.lname, p.fname, p.pubpid, r.id, s.sale_id";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  $last_patient_id      = 0;
  $last_prescription_id = 0;
  while ($row = sqlFetchArray($res)) {
   // If a facility is specified, ignore rows that do not match.
   if ($form_facility !== '') {
     if ($form_facility) {
       if ($row['facility_id'] != $form_facility) continue;
     }
     else {
       if (!empty($row['facility_id'])) continue;
     }
   }
   $patient_name    = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
   //$patient_id      = $row['patient_id'];
   $patient_id      = $row['pubpid'];
   $prescription_id = $row['id'];
   $drug_name       = empty($row['name']) ? $row['drug'] : $row['name'];
   $ndc_number      = $row['ndc_number'];
   $drug_units      = $row['size'] . ' ' .
	               generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']);
   $refills         = $row['refills'];
   $reactions       = $row['reactions'];
   $instructed      = $row['dosage'] . ' ' .
	               generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']) .
	               ' ' .
                       generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['interval']);
   //if ($row['patient_id'] == $last_patient_id) {
   if (strcmp($row['pubpid'], $last_patient_id) == 0) {
    $patient_name = '&nbsp;';
    $patient_id   = '&nbsp;';
    if ($row['id'] == $last_prescription_id) {
     $prescription_id = '&nbsp;';
     $drug_name       = '&nbsp;';
     $ndc_number      = '&nbsp;';
     $drug_units      = '&nbsp;';
     $refills         = '&nbsp;';
     $reactions       = '&nbsp;';
     $instructed      = '&nbsp;';
    }
   }
?>
 <tr>
  <td>
   <?php echo $patient_name ?>
  </td>
  <td>
   <?php echo $patient_id ?>
  </td>
  <td>
   <?php echo $prescription_id ?>
  </td>
  <td>
   <?php echo $drug_name ?>
  </td>
  <td>
   <?php echo $ndc_number ?>
  </td>
  <td>
   <?php echo $drug_units ?>
  </td>
  <td>
   <?php echo $refills ?>
  </td>
  <td>
   <?php echo $instructed ?>
  </td>
  <td>
   <?php echo $reactions ?>
  </td>
  <td>
   <a href='../drugs/dispense_drug.php?sale_id=<?php echo $row['sale_id'] ?>'
    style='color:#0000ff' target='_blank'>
    <?php echo oeFormatShortDate($row['sale_date']) ?>
   </a>
  </td>
  <td>
   <?php echo $row['quantity'] ?>
  </td>
  <td>
   <?php echo $row['manufacturer'] ?>
  </td>
  <td>
   <?php echo $row['lot_number'] ?>
  </td>
 </tr>
<?php
   $last_prescription_id = $row['id'];
   //$last_patient_id = $row['patient_id'];
   $last_patient_id = $row['pubpid'];
  } // end while
 } // end if
?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>
</form>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
