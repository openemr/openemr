<?php
/** **************************************************************************
 *	QUEST/MIGRATE.PHP
 *
 *	Copyright (c)2015 - Williams Medical Technology, Inc.
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @subpackage quest
 *  @version 2.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 * 
 *************************************************************************** */
error_reporting ( E_ALL & ~ E_NOTICE & ~ E_WARNING & ~ E_STRICT & ~ E_DEPRECATED );
ini_set ( 'error_reporting', E_ALL & ~ E_NOTICE & ~ E_WARNING & ~ E_STRICT & ~ E_DEPRECATED );
ini_set ( 'display_errors', 1 );

$ignoreAuth = true; // signon not required!!
                  
// ENVIRONMENT SETUP
if (defined ( 'STDIN' )) {
	parse_str ( implode ( '&', array_slice ( $argv, 1 ) ), $_GET );
}

$_GET ['site'] = 'default';
$_SESSION ['site_id'] = 'default';

$here = dirname ( dirname ( dirname ( __FILE__ ) ) );
require_once ($here . "/globals.php");
require_once ("{$GLOBALS['srcdir']}/lists.inc");
require_once ("{$GLOBALS['srcdir']}/wmt/wmt.class.php");

// GET LAB DATA
$lab_data = sqlQuery ( "SELECT * FROM procedure_providers WHERE type = 'quest' " );
if (! $lab_data ['ppid'])
	die ( "No Quest Laboratory Provider found ?" );
$lab_id = $lab_data ['ppid'];
$lab_name = $lab_data ['name'];
$lab_account = $lab_data ['send_fac_id'];

// CLEAR TABLES OF PREVIOUS RUN
sqlStatementNoLog ( "DELETE FROM `forms` WHERE formdir = 'quest'" );
sqlStatementNoLog ( "DELETE FROM `form_quest`" );
sqlStatementNoLog ( "DELETE FROM `procedure_order_code` WHERE procedure_order_id IN (SELECT procedure_order_id FROM `procedure_order` WHERE lab_id = ?)", array (
		$lab_id 
) );
sqlStatementNoLog ( "DELETE FROM `procedure_order` WHERE lab_id = ?", array (
		$lab_id 
) );
sqlStatementNoLog ( "DELETE FROM `procedure_result` WHERE procedure_report_id IN (SELECT procedure_report_id FROM `procedure_report` WHERE lab_id = ?)", array (
		$lab_id 
) );
sqlStatementNoLog ( "DELETE FROM `procedure_report` WHERE lab_id = ?", array (
		$lab_id 
) );

// GET THE FORM RECORDS AND IDS
$query = "SELECT * FROM ";
$query .= "( SELECT forms.id AS forms_id, forms.date AS forms_date, forms.encounter AS forms_encounter, forms.form_name AS forms_name, forms.pid AS forms_pid, forms.user AS forms_user, forms.groupname AS forms_groupname, forms.authorized AS forms_authorized, ";
$query .= "fqo.id AS ord_id, fqr.id AS res_id, fqo.order0_number AS number FROM form_quest_order fqo ";
$query .= "LEFT JOIN forms ON forms.form_id = fqo.id AND forms.formdir = 'quest_order' AND forms.deleted = 0 ";
$query .= "LEFT JOIN form_quest_result fqr ON fqo.order0_number = fqr.request_order ";
$query .= "UNION ";
$query .= "SELECT forms.id AS forms_id, forms.date AS forms_date, forms.encounter AS forms_encounter, forms.form_name AS forms_name, forms.pid AS forms_pid, forms.user AS forms_user, forms.groupname AS forms_groupname, forms.authorized AS forms_authorized, ";
$query .= "fqo.id AS ord_id, fqr.id AS res_id, fqr.request_order AS number FROM form_quest_result fqr ";
$query .= "LEFT JOIN forms ON forms.form_id = fqr.id AND forms.formdir = 'quest_result' AND forms.deleted = 0 ";
$query .= "LEFT JOIN form_quest_order fqo ON fqo.order0_number = fqr.request_order) XXX ";
// SPECIAL FOR UIMDA RESTART !!!!
// ***************************** $query .= "WHERE number > 'AG924865625' ";
$query .= "GROUP BY number ORDER BY number, forms_id ";
$forms = sqlStatement ( $query );

// PROCESS EACH LAB ORDER
$last_number = '';
$count = 0;
while ( $old = sqlFetchArray ( $forms ) ) {
	// DEBUG --- while ( ($old = sqlFetchArray($forms)) && $count++ < 10) {
	$cleaned = preg_replace ( "/[^0-9]/", "", $old ['number'] );
	$order_number = $cleaned;
	if (! $old ['forms_pid'] || ! $cleaned)
		continue; // bad patient or no order number
	$dupchk = sqlQuery ( "SELECT procedure_order_id AS id FROM procedure_order WHERE procedure_order_id = ?", array (
			$order_number 
	) );
	if ($dupchk ['id']) {
		echo "\nWARNING DUPLICATE: $last_number ";
		// DO NOT USE DUPLICATE NUMBER ... CREATE NEW !!!!
		$order_number = $GLOBALS ['adodb'] ['db']->GenID ( 'order_seq' );
		$dupchk = sqlQuery ( "SELECT procedure_order_id AS id FROM procedure_order WHERE procedure_order_id = ?", array (
				$order_number 
		) );
		while ( $dupchk ['id'] ) {
			$order_number = $GLOBALS ['adodb'] ['db']->GenID ( 'order_seq' );
			$dupchk = sqlQuery ( "SELECT procedure_order_id AS id FROM procedure_order WHERE procedure_order_id = ?", array (
					$order_number 
			) );
		}
		echo "REPLACED BY $order_number\n";
	}
	
	$date = '';
	$order = '';
	$result = '';
	$diagnoses = '';
	$doctor = '';
	$number = '';
	$provider = '';
	$facility = '';
	$status = 'i';
	$pid = $old ['forms_pid'];
	$last_number = $cleaned;
	
	$record = '';
	$params = array ();
	
	if ($old ['ord_id']) {
		$order = sqlQueryNoLog ( "SELECT * FROM form_quest_order WHERE id = ?", array (
				$old ['ord_id'] 
		) );
		if ($order ['id']) { // skip if order not found
			$odate = $order ['order0_datetime'];
			$status = 's'; // default submitted
			if ($order ['status'] == 'i' || $order ['status'] == 'g')
				$status = $order ['status'];
			
			// BUILD DIAGNOSES ARRAY
			for($i = 0; $i < 10; $i ++) {
				if ($order ['dx' . $i . '_code']) {
					if (! empty ( $diagnoses ))
						$diagnoses .= "|";
					$type = (strpos ( $order ['dx' . $i . '_code'], 'ICD9:' ) === false) ? 'ICD10:' : 'ICD9:';
					$code = (str_replace ( $type, '', $order ['dx' . $i . '_code'] ));
					$diagnoses .= $type . $code . "^" . $order ['dx' . $i . '_text'];
				}
			}
			
			// GET PHYSICIAN
			if ($order ['request_provider']) {
				$provider = $order ['request_provider'];
				$doctor = sqlQuery ( "SELECT * FROM users WHERE id = ? ", array (
						$order ['request_provider'] 
				) );
			}
			
			$record .= "date = ?, ";
			$params [] = $odate;
			$record .= "activity = ?, ";
			$params [] = $order ['activity'];
			$record .= "ins_primary = ?, ";
			$params [] = $order ['ins_primary_id'];
			$record .= "ins_secondary = ?, ";
			$params [] = $order ['ins_secondary_id'];
			$record .= "diagnoses = ?, ";
			$params [] = $diagnoses;
			$record .= "order_psc = ?, ";
			$params [] = $order ['order0_psc'];
			$record .= "request_notes = ?, ";
			$params [] = $order ['request_notes'];
			$record .= "request_handling = ?, ";
			$params [] = $order ['request_handling'];
			$record .= "request_billing = ?, ";
			$params [] = $order ['request_billing'];
			$record .= "facility_id = ?, ";
			$params [] = $order ['facility_id'];
			$record .= "order_number = ?, ";
			$params [] = $order_number;
			$record .= "order_req_id = ?, ";
			$params [] = $order ['order0_req_id'];
			$record .= "order_abn_id = ?, ";
			$params [] = $order ['order0_abn_id'];
			
			$record .= "request_account = ?, ";
			$params [] = $order ['request_account'];
			// $record .= "copy_pat = ?, "; $params[] = $order['copy_pat'];
			// $record .= "copy_acct = ?, "; $params[] = $order['copy_acct'];
			// $record .= "copy_acctname = ?, "; $params[] = $order['copy_acctname'];
			// $record .= "copy_fax = ?, "; $params[] = $order['copy_fax'];
			// $record .= "copy_faxname = ?, "; $params[] = $order['copy_faxname'];
			$record .= "order_abn_signed = ?, ";
			$params [] = $order ['order_abn_signed'];
			$record .= "work_flag = ?, ";
			$params [] = $order ['work_flag'];
			$record .= "work_insurance = ?, ";
			$params [] = $order ['work_insurance'];
			$record .= "work_date = ?, ";
			$params [] = ($order ['work_flag']) ? $order ['work_date'] : '';
			$record .= "work_employer = ?, ";
			$params [] = $order ['work_employer'];
			$record .= "work_case = ?, ";
			$params [] = $order ['work_case'];
		} // end if order
	}
	
	if ($old ['res_id']) {
		$result = sqlQueryNoLog ( "SELECT * FROM form_quest_result WHERE id = ?", array (
				$old ['res_id'] 
		) );
		if ($result ['id']) { // skip if result not found
			if (! $odate)
				$odate = $result ['specimen_datetime'];
			$status = 'z'; // assume final if results found
			if ($result ['status'] == 'x' || $result ['status'] == 'v' || $result ['status'] == 'n')
				$status = $result ['status'];
			
			// CREATE ORDER IF REQUIRED
			if (! $record) {
				// GET PHYSICIAN
				if ($result ['request_provider']) {
					$doctor = sqlQuery ( "SELECT * FROM users WHERE id = ? ", array (
							$result ['request_provider'] 
					) );
					if ($doctor ['npi'])
						$provider = $doctor ['id'];
				}
				if (! $provider && $result ['request_npi']) {
					$doctor = sqlQuery ( "SELECT * FROM users WHERE npi = ? ", array (
							$result ['request_npi'] 
					) );
					if ($doctor)
						$provider = $doctor ['id'];
				}
				
				// GET INSURANCE
				$ins_primary = '';
				$ins_secondary = '';
				if ($result ['specimen_datetime'] > 0) {
					$ins_primary = wmtInsurance::getPidInsDate ( $pid, $result ['specimen_datetime'], 'primary' );
					$ins_secondary = wmtInsurance::getPidInsDate ( $pid, $result ['specimen_datetime'], 'secondary' );
				}
				
				$record .= "date = ?, ";
				$params [] = $odate;
				$record .= "activity = ?, ";
				$params [] = $result ['activity'];
				$record .= "ins_primary = ?, ";
				$params [] = $ins_primary ['id'];
				$record .= "ins_secondary = ?, ";
				$params [] = $ins_secondary ['id'];
				$record .= "facility_id = ?, ";
				$params [] = $result ['request_facility'];
				$record .= "order_number = ?, ";
				$params [] = $order_number;
			}
			
			// ADD RESULT INFORMATION
			$record .= "lab_number = ?, ";
			$params [] = $result ['lab_number'];
			$record .= "result_datetime = ?, ";
			$params [] = $result ['result_datetime'];
			$record .= "result_abnormal = ?, ";
			$params [] = $result ['result_abnormal'];
			$record .= "reviewed_datetime = ?, ";
			$params [] = $result ['reviewed_datetime'];
			$record .= "reviewed_id = ?, ";
			$params [] = $result ['reviewed_id'];
			$record .= "notified_datetime = ?, ";
			$params [] = $result ['notified_datetime'];
			$record .= "notified_person = ?, ";
			$params [] = $result ['notified_person'];
			$record .= "notified_id = ?, ";
			$params [] = $result ['notified_id'];
			$record .= "review_notes = ?, ";
			$params [] = $result ['result_notes'];
			$record .= "result_doc_id = ?, ";
			$params [] = $result ['document_id'];
			
			if ($doctor ['npi']) { // use system information
				$record .= "doc_npi = ?, ";
				$params [] = $doctor ['npi'];
				$record .= "doc_lname = ?, ";
				$params [] = $doctor ['lname'];
				$record .= "doc_fname = ?, ";
				$params [] = $doctor ['fname'];
				$record .= "doc_mname = ?, ";
				$params [] = $doctor ['mname'];
			} else { // no doctor found so use results
				$record .= "doc_npi = ?, ";
				$params [] = $result ['request_npi'];
			}
		}
	}
	
	if (! $record)
		die ( "SOMTHING IS WRONG: no records found for forms record " . $old ['forms_id'] . " !!" );
	
	$record .= "pid = ?, ";
	$params [] = $old ['forms_pid'];
	$record .= "user = ?, ";
	$params [] = $old ['forms_user'];
	$record .= "groupname = ?, ";
	$params [] = ($old ['groupname']) ? $old ['groupname'] : 'OBGYN Care LLC';
	$record .= "authorized = ?, ";
	$params [] = ($old ['authorized']) ? $old ['authorized'] : '0';
	$record .= "lab_id = ?, ";
	$params [] = $lab_id;
	
	// GET PATIENT
	if ($old ['forms_pid']) {
		$pid = $old ['forms_pid'];
		$patient = sqlQuery ( "SELECT * FROM patient_data WHERE pid = ?", array (
				$pid 
		) );
	}
	
	$record .= "pat_pubpid = ?, ";
	$params [] = ($result ['request_pubpid']) ? $result ['request_pubpid'] : $patient ['pubpid'];
	$record .= "pat_fname = ?, ";
	$params [] = ($result ['request_pat_first']) ? $result ['request_pat_first'] : $patient ['fname'];
	$record .= "pat_mname = ?, ";
	$params [] = ($result ['request_pat_middle']) ? $result ['request_pat_middle'] : $patient ['mname'];
	$record .= "pat_lname = ?, ";
	$params [] = ($result ['request_pat_last']) ? $result ['request_pat_last'] : $patient ['lname'];
	$record .= "pat_DOB = ?, ";
	$params [] = ($result ['request_DOB']) ? $result ['request_DOB'] : $patient ['DOB'];
	$record .= "pat_ss = ?, ";
	$params [] = $patient ['ss'];
	$record .= "pat_race = ?, ";
	$params [] = $patient ['race'];
	
	$record .= "status = ?, ";
	$params [] = $status;
	$record .= "priority = ? ";
	$params [] = 'n';
	
	// CREATE THE FORMS RECORD
	$title = "Quest Laboratories - " . $order_number . " (" . $old ['number'] . "-" . $old ['forms_pid'] . ")";
	echo "\n" . $title;
	
	$form_id = sqlInsert ( "INSERT INTO form_quest SET " . $record, $params );
	
	$params = array ();
	$record = "INSERT INTO forms SET ";
	$record .= "date = ?, ";
	$params [] = $old ['forms_date'];
	$record .= "encounter = ?, ";
	$params [] = $old ['forms_encounter'];
	$record .= "form_name = ?, ";
	$params [] = $title;
	$record .= "form_id = ?, ";
	$params [] = $form_id;
	$record .= "pid = ?, ";
	$params [] = $old ['forms_pid'];
	$record .= "user = ?, ";
	$params [] = $old ['forms_user'];
	$record .= "groupname = ?, ";
	$params [] = 'OBGYN Care LLC';
	$record .= "authorized = ?, ";
	$params [] = $old ['forms_authorized'];
	$record .= "deleted = ?, ";
	$params [] = 0;
	$record .= "formdir = ? ";
	$params [] = 'quest';
	
	sqlInsert ( $record, $params );
	
	// TRANSLATE STATUS
	$flag = 'Complete';
	if ($status) {
		if ($status == 'i')
			$flag = 'Incomplete';
		if ($status == 's')
			$flag = 'Pending';
		if ($status == 'g')
			$flag = 'Processed';
	}
	
	// CREATE THE PROCEDURE ORDER RECORD
	$params = array ();
	$record = "REPLACE INTO procedure_order SET ";
	$record .= "patient_id = ?, ";
	$params [] = $old ['forms_pid'];
	$record .= "date_ordered = ?, ";
	$params [] = $odate;
	$record .= "date_pending = ?, ";
	$params [] = $order ['order0_pending'];
	$record .= "specimen_fasting = ?, ";
	$params [] = $order ['order0_fasting'];
	$record .= "specimen_duration = ?, ";
	$params [] = $order ['order0_duration'];
	$record .= "specimen_transport = ?, ";
	$params [] = $order ['order0_type'];
	$record .= "date_collected = ?, ";
	$params [] = ($result ['specimen_datetime']) ? $result ['specimen_datetime'] : $odate;
	$record .= "date_transmitted = ?, ";
	$params [] = ($result ['received_datetime']) ? $result ['received_datetime'] : $order ['request_processed'];
	$record .= "clinical_hx = ?, ";
	$params [] = $order ['order0_notes'];
	$record .= "provider_id = ?, ";
	$params [] = $provider;
	$record .= "procedure_order_id = ?, ";
	$params [] = $order_number;
	$record .= "control_id = ?, ";
	$params [] = $result ['lab_number'];
	$record .= "order_status = ?, ";
	$params [] = $flag;
	$record .= "encounter_id = ?, ";
	$params [] = $old ['forms_encounter'];
	$record .= "lab_id = ?, ";
	$params [] = $lab_id;
	$record .= "activity = ?, ";
	$params [] = 1;
	$record .= "order_priority = ? ";
	$params [] = 'normal';
	
	sqlInsert ( $record, $params );
	
	// GET THE ITEM RECORDS (may have orphans with no orders / orders with no results)
	$items = array ();
	
	$query = "SELECT * FROM form_quest_order_item ";
	$query .= "WHERE parent_id = ? AND pid = ? ";
	$query .= "ORDER BY id";
	$ord_result = sqlStatement ( $query, array (
			$old ['ord_id'],
			$old ['forms_pid'] 
	) );
	// STORES test_code, test_text, test_profile
	while ( $ord_item = sqlFetchArray ( $ord_result ) )
		$items [$ord_item ['test_code']] = $ord_item; // store order items by code
	
	/*
	 * OLD SYSTEMS THE PID IN THE RESULT = ORPHAN NOT ACTUAL PATIENT
	 * $query = "SELECT * FROM form_quest_result_item ";
	 * $query .= "WHERE parent_id = ? AND pid = ? ";
	 * $query .= "ORDER BY id";
	 * $res_result = sqlStatement($query, array($old['res_id'],$old['forms_pid']));
	 */
	$query = "SELECT * FROM form_quest_result_item ";
	$query .= "WHERE parent_id = ? ";
	$query .= "ORDER BY id";
	$res_result = sqlStatement ( $query, array (
			$old ['res_id'] 
	) );
	// STORES test_code, test_text,
	while ( $res_item = sqlFetchArray ( $res_result ) ) {
		$ord_item = $items [$res_item ['test_code']]; // needed to retrieve profile/zseg if present
		$res_item ['test_profile'] = $ord_item ['test_profile']; // store profile from order (may be null)
		$res_item ['test_zseg'] = $ord_item ['test_zseg']; // store zseg from order (may be null)
		$items [$res_item ['test_code']] = $res_item; // store result items by code (replace order if present)
	}
	
	// PROCESS EACH RESULT DETAIL RECORD
	$seq = 0;
	$report_id = '';
	
	foreach ( $items as $item ) {
		
		// TRANSLATE ORDER ITEM TYPE
		$type = ($item ['test_profile']) ? 'pro' : 'ord';
		
		echo "\n  ordered item: " . ++ $seq;
		
		// CREATE THE quest 2.0 ORDER ITEM RECORD
		$params = array ();
		$record = "REPLACE INTO procedure_order_code SET ";
		$record .= "diagnoses = ?, ";
		$params [] = $diagnoses;
		$record .= "procedure_order_id = ?, ";
		$params [] = $order_number;
		$record .= "procedure_code = ?, ";
		$params [] = $item ['test_code'];
		$record .= "procedure_name = ?, ";
		$params [] = $item ['test_text'];
		$record .= "procedure_type = ?, ";
		$params [] = $type;
		$record .= "procedure_order_seq = ?, ";
		$params [] = $seq;
		$record .= "procedure_source = ?, ";
		$params [] = 1;
		$record .= "do_not_send = ? ";
		$params [] = 0;
		
		sqlInsert ( $record, $params );
		
		// CREATE AOE RESPONSES FOR THIS TEST
		$aoe = array ();
		$aseq = 1;
		for($i = 0; $i < 20; $i ++) {
			if ($item ['aoe' . $i . '_code']) { // aoe answer
				$params = array ();
				$record = "REPLACE INTO procedure_answers SET ";
				$record .= "procedure_order_id = ?, ";
				$params [] = $order_number;
				$record .= "procedure_order_seq = ?, ";
				$params [] = $seq;
				$record .= "procedure_code = ?, ";
				$params [] = $item ['test_code'];
				$record .= "answer_seq = ?, ";
				$params [] = $aseq ++;
				$record .= "question_code = ?, ";
				$params [] = $item ['aoe' . $i . '_code'];
				$record .= "answer = ? ";
				$params [] = $item ['aoe' . $i . '_text'];
				
				sqlInsert ( $record, $params );
			}
		}
		
		// CHECK FOR RESULTS FOR THIS TEST
		if ($result && $item ['observation_status']) {
			
			// TRANSLATE REPORT STATUS
			$reviewed = 'Pending';
			if ($result ['result_datetime'])
				$reviewed = 'Received';
			if ($result ['reviewed_id'])
				$reviewed = 'Reviewed';
			
			// TRANSLATE LAB STATUS
			$status = ($result ['lab_status'] == 'CM') ? 'Complete' : 'Received';
			
			// CREATE THE quest 2.0 REPORT RECORDS (ONE PER ORDER ITEM)
			$params = array ();
			$record = "REPLACE INTO procedure_report SET ";
			$record .= "date_collected = ?, ";
			$params [] = $result ['specimen_datetime'];
			$record .= "procedure_order_id = ?, ";
			$params [] = $order_number;
			$record .= "review_status = ?, ";
			$params [] = $reviewed;
			$record .= "report_notes = ?, ";
			$params [] = $result ['result_notes'];
			$record .= "specimen_num = ?, ";
			$params [] = $result ['lab_number'];
			$record .= "procedure_order_seq = ?, ";
			$params [] = $seq;
			$record .= "date_report = ?, ";
			$params [] = $result ['result_datetime'];
			$record .= "report_status = ?, ";
			$params [] = $status;
			$record .= "lab_id = ? ";
			$params [] = $lab_id;
			
			$report_id = sqlInsert ( $record, $params );
			
			// GET THE RESULT ITEM RECORDS
			$query = "SELECT * FROM form_quest_result_item ";
			$query .= "WHERE parent_id = ? AND test_code = ? AND pid = ? ";
			$query .= "ORDER BY test_code, sequence";
			$det_result = sqlStatement ( $query, array (
					$item ['parent_id'],
					$item ['test_code'],
					$item ['pid'] 
			) );
			
			$rseq = 1;
			while ( $detail = sqlFetchArray ( $det_result ) ) {
				if ($rseq == 1)
					echo "\n  detail items: ";
				echo $rseq . " ";
				
				// TRANSLATE RESULT STATUS
				$status = 'Final';
				if ($detail ['observation_status'] == 'X')
					$status = 'Cancelled';
				if ($detail ['observation_status'] == 'C')
					$status = 'Corrected';
				if ($detail ['observation_status'] == 'P')
					$status = 'Preliminary';
				if ($detail ['observation_status'] == 'I')
					$status = 'Pending';
				
				// CREATE THE quest 2.0 RESULT DETAIL RECORDS
				$params = array ();
				$record = "REPLACE INTO procedure_result SET ";
				$record .= "`facility` = ?, ";
				$params [] = ($detail ['producer_id']) ? $detail ['producer_id'] : 'DEFAULT';
				$record .= "`result_code` = ?, ";
				$params [] = ($detail ['observation_loinc']) ? $detail ['observation_loinc'] : 'X' . $detail ['test_code'] . '-' . $rseq;
				$record .= "`result_text` = ?, ";
				$params [] = $detail ['observation_label'];
				$record .= "`result_status` = ?, ";
				$params [] = $status;
				$record .= "`date` = ?, ";
				$params [] = ($detail ['observation_datetime']) ? $detail ['observation_datetime'] : $result ['result_datetime'];
				$record .= "`result_data_type` = ?, ";
				$params [] = $detail ['observation_type'];
				$record .= "`result` = ?, ";
				$params [] = $detail ['observation_value'];
				$record .= "`units` = ?, ";
				$params [] = $detail ['observation_units'];
				$record .= "`range` = ?, ";
				$params [] = $detail ['observation_range'];
				$record .= "`abnormal` = ?, ";
				$params [] = ($detail ['observation_abnormal'] == 'N') ? '' : $detail ['observation_abnormal'];
				$record .= "`comments` = ?, ";
				$params [] = $detail ['observation_notes'];
				$record .= "`procedure_report_id` = ? ";
				$params [] = $report_id;
				
				sqlInsert ( $record, $params );
				$rseq ++;
			}
		}
	}
}

echo "\n\nDONE...";
?>
