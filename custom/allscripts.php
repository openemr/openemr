<?php
// Copyright (C) 2012 Accretics Corp. <md.support@accretics.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Provides import of electronic prescriptions into OpemEMR.
// This functionality will not be possible without work of people at phpexcel.codeplex.com

$piSTATS = array(
'REC_MATCH_INIT' => '0',
'REC_MATCH_ERR' => '1',
'REC_MATCH_OK' => '2',
'Sent to Pharmacy' => '3',
'Pending Approval' => '4',
'Active' => '7',
'Completed' => '8',
'Discontinued' => '8',
'REC_LOCKED' => '8',
'EIE' => '9',
'Entered in Error' => '9',
'Rejected' => '9',
'REC_IGNORE' => '9',
);

 //SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//
//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

include_once("../interface/globals.php");
include_once("$srcdir/acl.inc");

require_once("$srcdir/api.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/amc.php");

// To Read the Excel input from ePrescribe, phpExcel package is needed.
// Modify the relative path in the following line based on your local structure for
// PHPExcel root directory. Original code assumes this file in 'custom' directory.
require_once($GLOBALS['fileroot'].'/interface/PHPExcel/Classes/PHPExcel.php');

/* Check the access control lists to ensure permissions to this page */
$thisauth = acl_check('patients', 'med');
if (!$thisauth) {
    die($form_name.': Access Denied.');
}

$errFile = 0;

/////////////////////////////////////////////////////////////////////
// Import Excel file format 1.
// First row layout must be in the order specified by $xlCols.
/////////////////////////////////////////////////////////////////////

function process_xlfile ($strFile) {
    global $piSTATS, $errFile;

    $xlCols = array(
'Provider Name' => '',
'Provider Message' => '',
'Created' => 'order_date',
'Rx Date' => 'filled_date',
'Patient Name' => 'patient_name',
'Medication' => 'drug_name',
'SIG' => 'pharmacy_note',
'Quantity' => 'quantity_desc',
'Refill Quantity' => 'refills_desc',
'Rx Count' => 'presc_count',
'Rx Action' => 'pharmacy_name',
'Rx Status' => 'pharmacy_status',
'Site Name' => '',
'Orginator' => 'initiator_name',
'Authorize' => 'provider_name',
'Sender By' => 'filled_by_name',
'Printed By' => ''
    );

    /** Identify the type of $inputFileName **/
    $inputFileType = PHPExcel_IOFactory::identify($strFile);

    /** Create a new Reader of the type that has been identified **/
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);

    // set read only, to not read all excel properties, just data
    $objReader->setReadDataOnly(true);
    $objReader->setLoadSheetsOnly(0);

    /** Load $inputFileName to a PHPExcel Object **/
    echo $strfile;
    $objXLS = $objReader->load($strFile);
    $rowIterator = $objXLS->getActiveSheet()->getRowIterator();
    $HdrLine = 0;

    foreach ($rowIterator as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);
        reset($xlCols);										// alternative to PHPExcel_Cell::columnIndexFromString($cell->getColumn())-1
        switch ($row->getRowIndex ()) {
            case 1:
                if (count($xlCols) != PHPExcel_Cell::columnIndexFromString($objXLS->getActiveSheet()->getHighestColumn()) ) {
                    echo "File does not have " . count($xlCols) . " columns as expected.<BR>";
                    $errFile = 1;
                    break;										// Stop row iteration
                }											// End check for valid number of columns
                foreach ($cellIterator as $cell) {
                    if (strcasecmp(key($xlCols), $cell->getCalculatedValue()) == 0) {
                        next($xlCols);
                    } else {
                        echo $cell->getCalculatedValue() . " unexpected.<BR>";
                        $errFile = 2;
                        break;										// Stop row iteration
                    }
                }
                // Settings for a valid input file
                $HdrLine = 1;
                $thisBatch = max_value ('pi_batch_id', 'prescriptions_imports') + 1;
                break;
            default:
                $sqlExec = 'INSERT INTO prescriptions_imports SET pi_status = ?, pi_batch_id = ?';
                $piValues = array($piSTATS['REC_MATCH_INIT'], $thisBatch);
                foreach ($cellIterator as $cell) {
                    if (current($xlCols) != '') {
                        $sqlExec .= ','.current($xlCols).'= ?';
                        array_push($piValues, addslashes($cell->getCalculatedValue()));
                    }
                    next ($xlCols);
                }
                sqlInsert($sqlExec, $piValues);
                break;
        } // Processed all cells in a row
    } // Processed a row

    $objXLS->disconnectWorksheets();
    unset($objXLS);
    return $thisBatch;
}

function process_newbatch ($thisBatch) {
    global $piSTATS;
    // Delete records with blank keys
    $sqlExec =	'DELETE FROM prescriptions_imports '.
        'WHERE (pi_batch_id = ? ) AND '.
        '( TRIM(patient_name)="" '.
        'OR TRIM(drug_name)="" '.
        'OR TRIM(order_date)="" )';
    $sqlRows = sqlQuery($sqlExec, array($thisBatch));
    // Identify possible duplicates - they can be reprocessed manually to get accepted
    // $sqlExec = 'UPDATE prescriptions_imports AS pi '.
    // 'INNER JOIN prescriptions_imports AS hi '.
    // ' ON pi.order_date=hi.order_date '.
    // 'AND pi.patient_name=hi.patient_name '.
    // 'AND pi.drug_name=hi.drug_name '.
    // 'SET pi.pi_status='.$piSTATS['REC_MATCH_ERR'].', pi.pi_error = "DUPLICATE" '.
    // 'WHERE (pi.pi_batch_id = ?) '.
    // ' AND (hi.pi_batch_id <> ?) ';
    // $sqlRows = sqlQuery($sqlExec, array($thisBatch, $thisBatch));
    //
    // Perform updates that are available for reprocessing as well
    rximport ();
}

function max_value ($colName, $tabName) {
    $sqlExec = "SELECT IFNULL(MAX(". $colName ."),0) AS colMax FROM ". $tabName;
    $sqlRows = sqlQuery($sqlExec);
    return $sqlRows['colMax'];
}

function sql_condition ($fieldname, $oper, $fieldvalues) {
    return $fieldname.$oper."'".addslashes($fieldvalues[$fieldname])."' ";
}

function rximportOne ($piRow) {
    global $piSTATS;

    // 0. If an active prescription exists, update the dates
    $sqlExec = 'SELECT id FROM prescriptions '.
        'WHERE (patient_id = ? ) AND (drug = ? ) AND (dosage = ?) AND (quantity = ?) '.
        'AND ((active = 1) OR (date_added = ?))';
    $sqlRows = sqlStatement($sqlExec, array($piRow['pi_pid'], $piRow['drug'], $piRow['dosage'], $piRow['quantity_desc'], $piRow['rxDate']));
    if (sqlNumRows($sqlRows) == 1) {
        $sqlRow = sqlFetchArray($sqlRows);
        sqlStatement('UPDATE prescriptions SET date_modified = ?, filled_date = ?, active = ? WHERE id = ?',
        array($piRow['rxDate'], $piRow['rxDate'], (($piRow['pharmacy_status']=='Active') ? 1 : -1), $sqlRow['id']));
	} else {
        // 1. Mark all older prescriptions as inactive - assume chronological loads
        $sqlRows = sqlQuery ("UPDATE prescriptions SET active = -1 ".
            "WHERE active<>-1 ".
            "AND patient_id = ? ".
            "AND drug = ? "
            ,array($piRow['pi_pid'], $piRow['drug']));

        // 2. Insert into the prescriptions table
        $sqlExec = "INSERT INTO prescriptions (patient_id,provider_id,filled_by_id,date_added,date_modified,start_date".
            ",drug,dosage,quantity,refills,note,active,erx_source) ".
            "SELECT pi.pi_pid,pi.pi_approver_id,pi.id".
            ",STR_TO_DATE(pi.order_date,'%m/%d/%Y'),STR_TO_DATE(pi.order_date,'%m/%d/%Y'),STR_TO_DATE(pi.order_date,'%m/%d/%Y')".
            ",SUBSTRING_INDEX(pi.drug_name, ',', 1) AS drug,SUBSTRING_INDEX(pi.drug_name, ',', -1) AS dosage,pi.quantity_desc,pi.refills_desc,pi.pharmacy_note, ".
            "IF(pi.pharmacy_status='Active',1,-1) AS active, 1 AS erx_source ".
            "FROM prescriptions_imports AS pi WHERE pi.id = ?";
        $sqlRows = sqlInsert($sqlExec, array($piRow['id']));
	}
}

function rximport () {
    global $piSTATS;

    // // 0. New logic should update the original record and ignoring duplicate.
    // // OR activate the following code to delete duplicates
    // $sqlExec = 'DELETE FROM prescriptions_imports '.
    // 'WHERE (pi_error = "DUPLICATE") '.
    // 'AND (pi_status = ?)';
    // $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_INIT']));

    // 0. Skip Prescriptions reported by Pharmacies to be in error or rejected
    $matchKeys = array_keys($piSTATS, $piSTATS['REC_IGNORE']);
    foreach ($matchKeys as $strWrk) {
        $sqlExec = 'UPDATE prescriptions_imports SET pi_status = ? , pi_error = "REJECTED" '.
            'WHERE (pharmacy_status = ? ) '.
            ' AND (pi_status = ? )'.
            '';
        $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_IGNORE'], $strWrk, $piSTATS['REC_MATCH_INIT']));
	}

    // 1. Skip duplicate names - Allscript does not provide DoB for any further resolution
    $sqlExec = 'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN ('.
        'SELECT TRIM(CONCAT_WS(" ",CONCAT_WS(" ,", p.lname, p.fname),p.mname)) AS patient_name '.
        'FROM patient_data AS p '.
        'GROUP BY p.fname, p.lname, p.mname '.
        'HAVING (COUNT(p.id) > 1) '.
        ') AS dup '.
        'ON pi.patient_name=dup.patient_name '.
        'SET pi.pi_status = ?, pi_error = "NOT UNIQUE" '.
        'WHERE (pi.pi_status = ?)'.
        '';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_ERR'], $piSTATS['REC_MATCH_INIT']));

    // 2. Resolve patient names and assign pid
    $sqlExec =	'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN patient_data AS p '.
        'ON pi.patient_name=TRIM(CONCAT_WS(" ",CONCAT_WS(" ,", p.lname, p.fname),p.mname)) '.
        'SET pi.pi_pid = p.pid, pi_error = null, pi.pi_status = ? '.
        'WHERE (pi.pi_status = ?) '.
        '';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_OK'], $piSTATS['REC_MATCH_INIT']));

    // 1a. Repeat duplicate names check - This time w/o mname
    $sqlExec = 'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN ('.
        'SELECT TRIM(CONCAT_WS(" ,", p.lname, p.fname)) AS patient_name '.
        'FROM patient_data AS p '.
        'GROUP BY p.fname, p.lname '.
        'HAVING (COUNT(p.id) > 1) '.
        ') AS dup '.
        'ON pi.patient_name=dup.patient_name '.
        'SET pi.pi_status = ?, pi_error = "NOT UNIQUE" '.
        'WHERE (pi.pi_status = ?)'.
        '';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_ERR'], $piSTATS['REC_MATCH_INIT']));

    // 2a. Resolve patient names and assign pid - This time w/o mname
    $sqlExec =	'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN patient_data AS p '.
        'ON pi.patient_name=TRIM(CONCAT_WS(" ,", p.lname, p.fname)) '.
        'SET pi.pi_pid = p.pid, pi_error = null, pi.pi_status = ? '.
        'WHERE (pi.pi_status = ?) '.
        '';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_OK'], $piSTATS['REC_MATCH_INIT']));

    // 3. Resolve provider names
    $sqlExec = 'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN users AS u '.
        'ON (pi.provider_name = CONCAT_WS(",", u.lname, u.fname)) '.
        'SET pi.pi_approver_id = u.id '.
        'WHERE (pi.pi_approver_id is null) '.
        ' AND (u.username is not null) '.
        // 'AND (pi.pi_status = '.$piSTATS['REC_MATCH_OK'].') '.
        '';
    $sqlRows = sqlQuery($sqlExec);

    // 4. Default provider to the patient record
    $sqlExec = 'UPDATE prescriptions_imports AS pi '.
        'INNER JOIN patient_data AS p '.
        'ON pi.pi_pid=p.pid '.
        'SET pi.pi_approver_id = p.providerid '.
        'WHERE (pi.pi_approver_id is null) '.
        'AND (pi.pi_status = ?) '.
        '';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_OK']));

    // 5. Process each record in staging table with status REC_MATCH_OK
    $idStart = max_value ('id', 'prescriptions');
    $piRows = sqlStatement('SELECT *, STR_TO_DATE(pi.order_date,"%m/%d/%Y") rxDate, '.
        'SUBSTRING_INDEX(pi.drug_name, ",", 1) AS drug, SUBSTRING_INDEX(pi.drug_name, ",", -1) AS dosage '.
        'FROM prescriptions_imports AS pi '.
             'WHERE (pi.pi_status = ?) '.
        'ORDER BY pi_pid, drug, rxDate ASC',
        array($piSTATS['REC_MATCH_OK']));
    while ($piRow = sqlFetchArray($piRows)) {
        rximportOne($piRow);
	}
    $idEnd = max_value ('id', 'prescriptions');
    if ($idEnd > $idStart) {
        $sqlExec = "SELECT p.id, p.patient_id, p.date_added, p.filled_by_id, pi.pharmacy_status " .
            "FROM prescriptions p INNER JOIN prescriptions_imports AS pi ".
            "ON pi.id = p.filled_by_id " .
            "WHERE (p.id > ?) ".
            " AND (p.id <= ?)" ;
        $result = sqlStatement($sqlExec, array($idStart, $idEnd));
        if (sqlNumRows($result) > 0) {
            while ($row = sqlFetchArray($result)) {
                processAmcCall('e_prescribe_amc', true, 'add', $row['patient_id'], 'prescriptions', $row['id'], $row['date_added']);
                sqlQuery ('UPDATE prescriptions_imports SET prescriptions_id = ?, pi_status= ? WHERE id = ? ',
                array($row['id'], $piSTATS[$row['pharmacy_status']], $row['filled_by_id']));
			}
		}
	}

    // 6. Insert into the medications table
    //$sqlExec = "INSERT INTO lists (date, type, title, pid, begdate) ".
    // "SELECT CURRENT_DATE() as date, 'medication' as type, pi.drug_name, pi.pi_pid, ".
    // "MAX(str_to_date(pi.order_date, '%m/%d/%Y')) AS pi_begdate ".
    // "FROM openemr.prescriptions_imports pi ".
    // "LEFT OUTER JOIN openemr.lists li ".
    // "ON (pi.pi_pid = li.pid) AND (li.title like CONCAT('%', pi.drug_name,' %')) AND (li.type = 'medication') ".
    // "WHERE (pi.pi_pid > 0) ".
    // "AND (LENGTH(TRIM(IFNULL(pi.drug_name,''))) > 0) ".
    // "AND (str_to_date(pi.order_date, '%m/%d/%Y') > IFNULL(li.enddate, 0)) ".
    // "AND (li.title is NULL) ".
    // "GROUP BY pi.pi_pid, pi.drug_name ";
    //$sqlRows = sqlInsert($sqlExec);

    // 7. Mark the skipped records for reporting. They may get processed if pt name is added later.
    $sqlExec = 'UPDATE prescriptions_imports AS pi '.
        'SET pi.pi_status = ?, pi_error = "NO MATCH" '.
        'WHERE (pi.pi_status = ?)';
    $sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_MATCH_ERR'], $piSTATS['REC_MATCH_INIT']));
}

if ($_POST["Action"] == 'Ignore All') {
	// Walk through the pi_id values to set pid
	$sqlExec = 'UPDATE prescriptions_imports '.
		'SET pi_status = ?'.
		'WHERE pi_status = ?';
	$sqlRows = sqlQuery($sqlExec, array($piSTATS['REC_IGNORE'], $piSTATS['REC_MATCH_ERR']));
} else
	if ($_POST["Action"] == 'Reprocess') {
		// Walk through the pi_id values to set pid
		foreach ($_POST["pi"] as $pi_id => $pi_pid) {
			if ($pi_pid) {
				$sqlExec = "SELECT pid FROM patient_data WHERE pid=".$pi_pid;
				$sqlRows = sqlQuery($sqlExec);
				if ($sqlRows['pid'] == $pi_pid) {
					$sqlExec = 'UPDATE prescriptions_imports AS pi '.
						'SET pi_pid = '.$pi_pid.', pi_error="", pi.pi_status = '.$piSTATS['REC_MATCH_OK'].' '.
						'WHERE pi.id = ?';
                }
			} else {
                $sqlExec = 'UPDATE prescriptions_imports AS pi '.
                    'SET pi_error="", pi.pi_status = "'.$piSTATS['REC_MATCH_INIT'].'" '.
                    'WHERE pi.id = ?';
			}
            $sqlRows = sqlQuery($sqlExec, array($pi_id));
		}
        rximport ();
	} else
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $thisBatch = process_xlfile ($_FILES['file']['tmp_name']);
            if ($thisBatch) {
                process_newbatch($thisBatch);
            } else {
                echo "Error:Nothing processed.";
            }
        }
?>


<html>
<head>
    <?php html_header_show();?>
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css" />
    <title>Import Prescriptions (Input Format 1)</title>
</head>
<body id="report_results">
    <?php if ($errFile > 0) { ?>
    <br />
    <b>Oops - Nothing to update!</b>
    <br />This import routine was unable to find valid prescription data in the file uploaded by you.
    <br />
    <br />
    <b>Possible reasons</b>:
    <br />
    <ol>
        <li>
            You specified a file that was not generated in required format.
Try repeating the import process with a valid file.
            <br />Hint: Was the
file saved in .xls format?
        </li>
        <li>
            If you continue to get this error for valid export file,
it is possible that the provider has changed the layout of the
export file. Your system administrator will need to change system
settings to fix the problem.
        </li>
    </ol>
    <b>Patient information in OpenEMR has not been updated.</b>
    <?php } else if ($_POST["Action"] == "Upload") { ?>
    <form enctype="multipart/form-data"
        action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"
        <?php echo ($_FILES['file']['tmp_name'] ? '' : 'style="display:none";'); ?>>
        <table>
            <tr>
                <td>
                    Please review the extracted records from last uploaded file (Batch <?php echo $thisBatch; ?>).
                    <br />
                    Then press 'View Exceptions' button to see the list of followup actions.
                </td>
                <td>
                    <input type="submit" name="Action" value="View Exceptions" />
                </td>
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
            <?php
              $sqlExec = 'SELECT pi_error, patient_name, STR_TO_DATE(order_date,"%m/%d/%Y") as pi_date, drug_name '.
',CONCAT(quantity_desc," x",refills_desc) as qty, pharmacy_name, pharmacy_status, initiator_name '.
',pharmacy_note, filled_by_name '.
'FROM prescriptions_imports '.
'WHERE (pi_batch_id = ?) '.
'ORDER BY id ASC ';
              $result = sqlStatement($sqlExec, array($thisBatch) );
              if (sqlNumRows($result) > 0) {
                  while ($row = sqlFetchArray($result)) {
            ?>
            <tr>
                <td>
                    <?php echo( (strcasecmp($row['pi_error'],"DUPLICATE")?'':'*').$row['pi_date'] ); ?>
                </td>
                <td>
                    <?php echo( $row['patient_name'] ); ?>
                </td>
                <td>
                    <?php echo( $row['drug_name'] ); ?>
                </td>
                <td>
                    <?php echo( $row['qty']); ?>
                </td>
                <td>
                    <?php echo( $row['pharmacy_name'] ); ?>
                </td>
                <td>
                    <?php echo( $row['pharmacy_status'] ); ?>
                </td>
                <td>
                    O:<?php echo( $row['initiator_name'] ); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2"></td>
                <td colspan="4">
                    <?php echo( $row['pharmacy_note'] ); ?>
                </td>
                <td>
                    S:<?php echo( $row['filled_by_name'] ); ?>
                </td>
            </tr>
            <?php }
              } ?>
        </table>
    </form>
    <?php } else {
              $sqlExec = "SELECT MAX(STR_TO_DATE(order_date,'%m/%d/%Y')) as last_order FROM prescriptions_imports";
              $result = sqlStatement($sqlExec);
              $row = sqlFetchArray($result);
              $last_order = $row['last_order'];
    ?>
    <form enctype="multipart/form-data"
        action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"
        style="display:<?php echo ($_FILES['file']['tmp_name'] ? 'none' : '');?>;">
        <input type="hidden" name="MAX_FILE_SIZE" value="3072000" />
        <table>
            <tr>
                <td>
                    Use 'Browse' button to specify ePrescribe generated Excel file:
                    <br />
                    <b>
                        Records were last imported up to <?php echo $last_order; ?>.
                    </b>
                </td>
                <td style='text-align:right'>
                    <input type="file" name="file" />
                </td>
                <td style='text-align:right'>
                    <input type="submit" name="Action" value="Upload" />
                </td>
            </tr>
        </table>
        <?php
              $sqlExec = 'SELECT pi.id, patient_name, STR_TO_DATE(order_date,"%m/%d/%Y") as pi_date, drug_name, CONCAT(quantity_desc," x",refills_desc) as qty,pi_error '.
'FROM prescriptions_imports pi '.
'WHERE (pi_status = ?) '.
'ORDER BY patient_name ASC, pi_date DESC ';
$result = sqlStatement($sqlExec, array($piSTATS['REC_MATCH_ERR']));
if (sqlNumRows($result) > 0) {
        ?>
        <table>
            <tr>
                <td>
                    <b>
                        Please review the following <?php echo sqlNumRows($result); ?> exceptions and reconcile the differences in patient names.
                    </b>
                </td>
                <td style='text-align:right'>
                    <input type="submit" name="Action" value="Ignore All" />
            </tr>
            <tr>
                <td>Enter pt number(s) and press 'Reprocess' button for manual reconciliation.</td>
                <td style='text-align:right'>
                    <input type="submit" name="Action" value="Reprocess" />
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <th>Patient Name</th>
                <th>Medication</th>
                <th>Date</th>
                <th>Qty</th>
                <th>Status</th>
                <th>EMR Pt. Id.</th>
            </tr>
            <?php
while ($row = sqlFetchArray($result)) {
$dispName = ($prevName == $row['patient_name'] ? "style='visibility:hidden; '" : "")
            ?>
            <tr>
                <td <?php echo $dispName; ?>>
                    <?php echo( $row['patient_name'] ); ?>
                </td>
                <td>
                    <?php echo( $row['drug_name'] ); ?>
                </td>
                <td>
                    <?php echo( $row['pi_date'] ); ?>
                </td>
                <td>
                    <?php echo( $row['qty']); ?>
                </td>
                <td>
                    <?php echo( $row['pi_error'] ); ?>
                </td>
                <td>
                    <input type="text" size="5" name="pi[<?php echo( $row['id'] ); ?>]" />
                </td>
            </tr>
            <?php $prevName = $row['patient_name'];
} ?>
        </table>
    </form>
    <?php
}
}
    ?>
</body>
</html>