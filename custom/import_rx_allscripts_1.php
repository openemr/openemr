<?php
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//

/////////////////////////////////////////////////////////////////////
// Import Excel file generated from Allscripts ePrescribe Provider Report
/////////////////////////////////////////////////////////////////////

/* for $GLOBALS[], ?? */
include_once("../interface/globals.php");
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for display_layout_rows(), ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');

// To Read the Excel input from ePrescribe, phpExcel package is needed.   
// Modify the relative path in the following line based on your local structure for  
// PHPExcel root directory 
require_once ('../interface/PHPExcel/Classes/PHPExcel.php');

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
	die($form_name.': Access Denied.');
}
$data = array();
$recPresc = array();
$colAnchors = array();

// Minimize the display data
// Not used - encoding in different versions of Excel
function display_tokens ($needle, $haystack, $nbr) {
	$tokens = explode($needle, $haystack, $nbr);
	if ((!$tokens) || (count($tokens) < 2)) {
		return $haystack;
	}
	else return implode($needle, $tokens);
}

// array_search with partial matches
function array_searchp($needle, $haystack, $dbg) {
	if(!is_array($haystack)) return false;
	foreach ($haystack as $key=>$item) {
		// if ($dbg) echo $item.' in '.$needle.':'.preg_match("/{$item}/i", $needle).'<BR>';
		if (preg_match("/{$item}/i", $needle)) return $key;
	}
	return false;
}

function input_parse_err ($errDesc, $keyIndex, $colPos, $currValue, $expValue ) {
	global $data;
	echo $errDesc." while extracting record ".(Count($data)+1)."<BR>".
			"Found field ".$keyIndex." in column ".$colPos." instead of ".$expValue." as expected.<BR>".
			"Field value :".$currValue."<BR>";
}

function update_currPresc ($keyIndex, $colPos, $currValue) {
	global $recPresc;
	global $colAnchors;
	global $data;
	$keyWrk = $keyIndex;

	// Set the Anchors during first record fill
	if ((array_key_exists($keyWrk, $colAnchors))) {
		$keyWrk = array_search($colPos, $colAnchors);
		// Check for unexpected new line start without filling in all elements
		if (($keyWrk) && ($keyIndex != $keyWrk) && ($keyWrk < 9)) {
			add_presc();
			$keyIndex = $keyWrk;
		}
	}
	else {
		// Debug to see the anchors being built from 1st record
		// echo "Anchored ".$keyIndex.' as '.$keyWrk."@".$colPos."<BR>";
		$colAnchors[$keyWrk] = $colPos;
	}

	// Debug
	// echo $keyIndex.":".$colPos.":".$currValue.':'.$keyWrk."<BR>";

	if ($keyWrk > 0) {
		$recPresc [$keyWrk] = $currValue;
	}
	if ($keyIndex == 11)	{ // actual $keyIndex (not the local $keyWrk)
		add_presc();
		$keyIndex = 2;
	} else {
		$keyIndex++;
	}
	return $keyIndex;
}

function add_presc() {
	global $data;
	global $recPresc;
	$intCount = 1;

	$data []= array(
  'order_date' => $recPresc[$intCount++],
  'patient_name' => $recPresc[$intCount++],
  'drug_name' => $recPresc[$intCount++],
  'pharmacy_note' => $recPresc[$intCount++], 
  'quantity_desc' => $recPresc[$intCount++],
  'refills_desc' => $recPresc[$intCount++],
  'pharmacy_name' => $recPresc[$intCount++],
  'pharmacy_status' => $recPresc[$intCount++],
  'filled_by_name' => $recPresc[$intCount++],
  'provider_name' => $recPresc[$intCount++],
  'initiator_name'	=> $recPresc[$intCount++],
	);

	// Cleanup the non-repeating parts of the $recPresc
	$intCount = 3;
	while ($intCount <= count($recPresc)) {
		$recPresc[$intCount] = "";
		$intCount++;
	}
}

function sql_condition ($fieldname, $oper, $fieldvalues) {
	return $fieldname.$oper."'".addslashes($fieldvalues[$fieldname])."' ";
}

function insert_batch () {
	global $data;

	$sqlExec = "SELECT IFNULL(MAX(pi_batch_id),0)+1 AS pi_batch_id FROM prescriptions_imports";
	$sqlRows = sqlQuery($sqlExec);
	$thisBatch = $sqlRows['pi_batch_id'];

	foreach ($data as &$presc ) {
		// Check if this presc was staged previously
		$sqlExec = "SELECT id FROM prescriptions_imports WHERE (" .
		sql_condition('order_date','=',$presc).
			 ") AND (". sql_condition('patient_name','=',$presc).
			 ") AND (". sql_condition('drug_name','=',$presc).
			 ") AND (". sql_condition('pharmacy_status','=',$presc).
			 ")LIMIT 1";
		$sqlRows = sqlQuery($sqlExec);
		if ($sqlRows['id']) {
			$presc['dup'] = true;
		} else  {
			$sqlExec = "INSERT INTO prescriptions_imports ".
				"SET pi_batch_id = '".$thisBatch."' ";
			foreach (array_keys($presc) as $presField) {
				$sqlExec .= ",".sql_condition($presField, "=",$presc);
			}
			$sqlRows = sqlQuery($sqlExec);
		}
	}
	// Post insert processing - Currently batch_id is not used to limit updates
	// 0. Skip Prescriptions reported by Pharmacies to be in error or rejected
	$sqlExec = 	'UPDATE prescriptions_imports AS pi '.
				'SET pi.pi_pid = 0, pi_status = "REJECTED" '.
				'WHERE (pi.pi_pid is null) '.
				"AND FIND_IN_SET(pi.pharmacy_status,'Entered in Error,Rejected')<>0 ".
				'AND (pi.pi_batch_id = '.$thisBatch.')';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 1. Skip duplicate names - Allscript does not provide DoB for any further resolution
	$sqlExec = 	'UPDATE prescriptions_imports AS pi '.
				'INNER JOIN (SELECT CONCAT_WS(" ,", p.lname, p.fname) AS presc_name '.
				'FROM patient_data AS p '.
				'GROUP BY p.fname, p.lname '.
				'HAVING COUNT(p.id) > 1) AS dup '.
				'ON pi.patient_name=dup.presc_name '.
				'SET pi.pi_pid = 0, pi_status = "NOT UNIQUE" '.
				'WHERE (pi.pi_pid is null) '.
				'AND (pi.pi_batch_id = '.$thisBatch.')';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 2. Resolve patient names
	$sqlExec =	'UPDATE prescriptions_imports AS pi '.
				'INNER JOIN patient_data AS p '.
				'ON pi.patient_name=CONCAT_WS(" ,", p.lname, p.fname) '.
				'SET pi.pi_pid = p.pid, pi_status = null '.
				'WHERE (pi.pi_pid is null) ';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 3. Resolve provider names
	$sqlExec = 'UPDATE prescriptions_imports AS pi '.
				'INNER JOIN users AS u '.
				'ON (pi.provider_name = CONCAT_WS(",", u.lname, u.fname)) '.
				'SET pi.pi_approver_id = u.id '.
				'WHERE (pi.pi_approver_id is null) '.
				'AND (pi.pi_batch_id = '.$thisBatch.')';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 4. Default provider to the patient record
	$sqlExec = 'UPDATE prescriptions_imports AS pi '.
				'INNER JOIN patient_data AS p '.
				'ON pi.pi_pid=p.pid '.
				'SET pi.pi_approver_id = p.providerid '.
				'WHERE (pi.pi_approver_id is null) '.
				'AND (pi.pi_batch_id = '.$thisBatch.')';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 5. Insert into the prescriptions table
	$sqlExec = "INSERT INTO prescriptions (patient_id,provider_id,filled_by_id,date_added,date_modified,start_date".
				",drug,quantity,refills,note) ".
				"SELECT pi.pi_pid,pi.pi_approver_id,pi.id,STR_TO_DATE(pi.order_date,'%m/%d/%Y'),STR_TO_DATE(pi.order_date,'%m/%d/%Y'),STR_TO_DATE(pi.order_date,'%m/%d/%Y')".
				",pi.drug_name,pi.quantity_desc,pi.refills_desc,pi.pharmacy_note ".
  				"FROM prescriptions_imports pi ".
  				"LEFT OUTER JOIN openemr.prescriptions p ".
       			"ON (pi.id = p.filled_by_id) ". 
  				"WHERE (p.filled_by_id is null) ".
    			"AND (pi.pi_pid > 0) ";
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 6. Insert into the medications table
	$sqlExec = "INSERT INTO lists (date, type, title, pid, begdate) ".
				"SELECT CURRENT_DATE() as date, 'medication' as type, pi.drug_name, pi.pi_pid, ".
				"MAX(str_to_date(pi.order_date, '%m/%d/%Y')) AS pi_begdate ".
				"FROM openemr.prescriptions_imports pi ".
				"LEFT OUTER JOIN openemr.lists li ".
				"ON (pi.pi_pid = li.pid) AND (pi.drug_name = li.title) ".
				"WHERE (pi.pi_pid > 0) ".
				"AND (str_to_date(pi.order_date, '%m/%d/%Y') > IFNULL(li.enddate, 0)) ".
				"AND (NULLIF(li.type, 'medication') IS NULL) ".
				"AND (li.title is NULL) ".
				"GROUP BY pi.pi_pid, pi.drug_name ";
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
	// 7. Mark the skipped records for reporting. They may get processed if pt name is added later.
	$sqlExec = 	'UPDATE prescriptions_imports AS pi '.
				'SET pi_status = "NO MATCH" '.
				'WHERE (pi.pi_pid is null) '.
				'AND (pi.pi_status is null)';
	//	echo time().":".$sqlExec."<BR>";
	$sqlRows = sqlQuery($sqlExec);
	//	echo time().":".count($sqlRows)." Rows returned.<BR>";
}
$strSkipCells = array (
				'CELL_NULL' => null,
				'CELL_EMPTY' => "",
				'CELL_DATE' => 'Date:',
				'CELL_HDR.NR' => 'Patient',
				'CELL_AUTH' => 'Authorize:',
				'CELL_ORIG' => 'Originator:',
				'CELL_SNDR' => 'Sender:',
);

$strSkipTokens = array (
				'CELL_DAY_TOTAL.NR' => 'Total Prescription for ',
				'CELL_HDR_RANGE.NR' => 'DateRange :',
				'CELL_HDR_PROV.NR' => 'Provider:'
);

if ( $_FILES['file']['tmp_name'] ) {
	$strFile = $_FILES['file']['tmp_name'];
	$objReader = PHPExcel_IOFactory::createReaderForFile($strFile);
	// set read only, to not read all excel properties, just data
	$objReader->setReadDataOnly(true);
	$objXLS = $objReader->load($strFile);
	$rowIterator = $objXLS->getSheet(1)->getRowIterator();

	$index = 0;
	$nextRow = 0;
	$newRow = 0;
	$HdrLine = 0;

	/* Debug to see the input being processed by PHPExcel framework
	 foreach($rowIterator as $row){
	 $cellIterator = $row->getCellIterator();
	 $cellIterator->setIterateOnlyExistingCells(true);
	 foreach ($cellIterator as $cell) {
	 echo PHPExcel_Cell::columnIndexFromString($cell->getColumn()). " : " . $cell->getCalculatedValue().'<BR>';
	 }
	 }
	 */

	foreach($rowIterator as $row){
		$cellIterator = $row->getCellIterator();
		// Loop all cells, even if it is not set
		$cellIterator->setIterateOnlyExistingCells(true);
		$newRow = 1;
		foreach ($cellIterator as $cell) {
			$colNbr = PHPExcel_Cell::columnIndexFromString($cell->getColumn());
			$currCell = $cell->getCalculatedValue();
			$skipCell = array_search($currCell, $strSkipCells);
			if ($skipCell == false) $skipCell = array_searchp($currCell, $strSkipTokens, ($index>8));

			// Debug
			// echo $index.":".$currCell.":".$skipCell."<BR>";

			// Use 'Date:' to locate the prescription date
			if ($index == 0) {
				$HdrLine = 0;
				if ($skipCell != 'CELL_DATE') $skipCell = 'SKIP.NR';
			}
			if (($skipCell == 'CELL_DATE') && ( $newRow == 1 )) {
				$newRow = 0;
				$HdrLine = 0;
				$index = 1;		// subsequent cell will contain date
			}
			// First column value of 'Patient' on a new row is the Header row
			if (($index == 2) && ($HdrLine == 0)) {
				if (($skipCell == 'CELL_HDR.NR') && ($newRow == 1)) {
					$HdrLine = 1;
				} else {
					$skipCell = 'SKIP.NR';
				}
			}
			if ($skipCell) {
				$nextRow = ((strripos($skipCell, ".NR") > 0) ? 1:0);
			}
			if (($skipCell == false) && ($index > 0)) {
				switch ($index) {
					case 1 :
						$nextRow = 1;
						break;
					case 2 :
						if (($colAnchors[3] > 0) && ($colNbr >= $colAnchors[3])) {
							$index++;
						} else {
							$nextRow = 1;
						}
						break;
					case 11 :
						$nextRow = 1;
						break;
					default:
						break;
				}
				$index = update_currPresc($index, $colNbr, $currCell);
			}
			if ($nextRow == 1) {
				$nextRow = 0;
				break;
			}
		}
	}
	$objXLS->disconnectWorksheets();
	unset($objXLS);

	if ($HdrLine) insert_batch();
	else echo "Error:Nothing processed.";
}
?>

<html>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<head>
<title>Import Allscripts ePrescribe Provider Report (Detailed)</title>
</head>
<body id="report_results">
<?php html_header_show();?>
<?php if ((count($data) == 0) && ($_FILES['file']['tmp_name'])) { ?>
	<b>Oops - Nothing to update!</b>
	<BR> This import routine was unable to find any valid prescription data
	in the file uploaded by you.
	<BR>
	<BR>
	<b>Possible reasons</b> :
	<BR>
	<ol>
		<li>If you specified a file that was not generated by the Allscripts
			report export, try repeating the import process.<BR>Hint: Was the
			file saved in .xls format?</li>
		<li>If you continue to get this error for valid Allscripts export
			file, it is possible that Allscripts has changed the layout of the
			export file. Your system administrator will need to change system
			settings to fix the problem.</li>
	</ol>
	<b>Patient information in OpenEMR has not been updated.</b>
	<?php } else if ($_POST["Action"] == "Upload") { ?>
	<form enctype="multipart/form-data"
		action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"
		<?php echo ($_FILES['file']['tmp_name'] ? '' : 'style="display:none";');?>>
		<table>
			<tr>
				<td>Please review the <?php echo count($data) ?> extracted records.<BR>
					Then press 'View Exceptions' button to see the list of followup
					actions.</td>
				<td><input type="submit" name="Action" value="View Exceptions" /></td>
			</tr>
		</table>
		<table>
			<tr>
				<th>Date</th>
				<th>Patient</th>
				<th>Medication/SIG</th>
				<th>Qty</th>
				<th>Pharmacy</th>
				<th>Status</th>
				<th>Refs</th>
			</tr>
			<?php foreach( $data as $row ) { ?>
			<tr>
				<td><?php echo( ($row['dup']?'*':'').$row['order_date'] ); ?></td>
				<td><?php echo( $row['patient_name'] ); ?></td>
				<td><?php echo( $row['drug_name'] ); ?></td>
				<td><?php echo( $row['quantity_desc'].' / '.$row['refills_desc']); ?>
				</td>
				<td><?php echo( $row['pharmacy_name'] ); ?>
				</td>
				<td><?php echo( $row['pharmacy_status'] ); ?></td>
				<td>O:<?php echo( $row['initiator_name'] ); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2" />
				<td colspan="4"><?php echo( $row['pharmacy_note'] ); ?></td>
				<td>S:<?php echo( $row['filled_by_name'] ); ?>
				</td>
			</tr>
			<?php } ?>
		</table>
	</form>
	<?php } else if ($_POST["Action"] == "View Exceptions") { ?>
	<form enctype="multipart/form-data"
		action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<table>
			<tr>
				<td>Please review and resolve the following exceptions identified
					during processing of all imports so far.</td>
				<td><input type="submit" name="Action" value="Load Another File" />
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<th>Patient</th>
				<th>Medication</th>
				<th>Date</th>
				<th>Qty</th>
				<th>Status</th>
			</tr>
			<?php 	$sqlExec = 	'SELECT patient_name, STR_TO_DATE(order_date,"%m/%d/%Y") as pi_date, drug_name, CONCAT(quantity_desc," x",refills_desc) as  qty,pi_status '.
				'FROM prescriptions_imports '.
				'WHERE pi_status is not null '.
				'ORDER BY patient_name ASC, pi_date DESC';
			$result = sqlStatement($sqlExec);

			while ($row = sqlFetchArray($result)) { 
				$dispName = ($prevName == $row['patient_name'] ? "style='visibility:hidden; '" : "")
				?>
			<tr>
				<td <?php echo $dispName; ?>>
					<?php echo( $row['patient_name'] ); ?></td>
				<td><?php echo( $row['drug_name'] ); ?></td>
				<td><?php echo( $row['pi_date'] ); ?></td>
				<td><?php echo( $row['qty']); ?></td>
				<td><?php echo( $row['pi_status'] ); ?></td>
			</tr>
			<?php  $prevName = $row['patient_name'];
				} ?>
		</table>
	</form>
	<?php } else { ?>
	<form enctype="multipart/form-data"
		action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" 
		style="display:<?php echo ($_FILES['file']['tmp_name'] ? 'none' : '');?>;">
		<input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
		<table>
			<tr>
				<td>Use 'Browse' button to specify ePrescribe generated Excel
					file:</td>
				<td><input type="file" name="file" /></td>
			</tr>
			<tr>
				<td />
				<td><input type="submit" name="Action" value="Upload" /></td>
			</tr>
		</table>
	</form>
	<?php } ?>
</body>
</html>