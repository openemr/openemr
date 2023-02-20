<?php
/** *************************************************************************************
 *	QUEST/PROCESS.PHP
 *
 *	Copyright (c)2014 - Williams Medical Technology, Inc.
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
 **************************************************************************************** */
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/lists.inc");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");
require_once("{$GLOBALS['srcdir']}/wmt/quest/QuestOrderClient.php");
//require_once("{$GLOBALS['srcdir']}/wmt/quest/QuestModelHL7v2.php");

$document_url = $GLOBALS['web_root']."/controller.php?document&retrieve&patient_id=".$pid."&amp;document_id=";
$reload_url = $rootdir.'/patient_file/encounter/view_form.php?formname=order&id=';

function getCreds($id) {
	if (!$id) return;
	
	$query = "SELECT * FROM users WHERE id = '".$id."' LIMIT 1";
	$user = sqlQuery($query);
	return $user['npi']."^".$user['lname']."^".$user['fname']."^".$user['mname']."^^^^^NPI";
}

function getLabelers($thisField) {
	$rlist= sqlStatement("SELECT * FROM list_options WHERE list_id = 'Quest_Label_Printers' ORDER BY seq, title");
	
	$active = '';
	$default = '';
	$labelers = array();
	while ($rrow= sqlFetchArray($rlist)) {
		if ($thisField == $rrow['option_id']) $active = $rrow['option_id'];
		if ($rrow['is_default']) $default = $rrow['option_id'];
		$labelers[] = $rrow; 
	}

	if (!$active) $active = $default;
	
	echo "<option value=''";
	if (!$active) echo " selected='selected'";
	echo ">&nbsp;</option>\n";
	foreach ($labelers AS $rrow) {
		echo "<option value='" . $rrow['option_id'] . "'";
		if ($active == $rrow['option_id']) echo " selected='selected'";
		echo ">" . $rrow['title'];
		echo "</option>\n";
	}
}

// set processing date/time
$order_data->date_transmitted = date('Y-m-d H:i:s');

// get all AOE questions and answers
$query = "SELECT * FROM procedure_order_code pc ";
$query .= "LEFT JOIN procedure_questions pq ON pq.lab_id = ? AND pc.procedure_code = pq.procedure_code ";
$query .= "LEFT JOIN procedure_answers pa ON pa.question_code = pq.question_code AND pa.procedure_order_id = pc.procedure_order_id AND pa.procedure_order_seq = pc.procedure_order_seq ";
$query .= "WHERE pc.procedure_order_id = ? ";
$query .= "ORDER BY pa.procedure_order_id, pa.procedure_order_seq, pa.answer_seq";
$values[] = $order_data->lab_id;
$values[] = $order_item->procedure_order_id;
$results = sqlStatement($query,$values);

$aoe_list = null;
while ($data = sqlFetchArray($results)) {
	if ($data['answer']) $aoe_list[] = $data;
}

// validate aoe responses (loop)
$aoe_errors = "";
if (is_array($aoe_list)) {
	foreach ($aoe_list as $aoe_data) {
		if ($aoe_data['required'] && !$aoe_data['answer']) {
			$aoe_errors .= "\nQuestion [".$aoe_data['question_text']."] for test [".$aoe_data['procedure_code']."] requires a valid response.";
		}
	}
}

$reload_url = $rootdir.'/patient_file/encounter/view_form.php?formname=quest&id=';
?>

	<form method='post' action="" id="order_process" name="order_process" > 
		<table class="bgcolor2" style="width:100%;height:100%">
			<tr>
				<td colspan="2">
					<h2 style="padding-bottom:0;margin-bottom:0">Order Processing</h2>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="padding-bottom:20px">
<?php 
if ($aoe_errors) { // oh well .. have to terminate process with errors
	echo "The following errors must be corrected before submitting:";
	echo "<pre>\n";
	echo $aoe_errors;
?>
				</td>
			</tr><tr>
				<td style="text-align:right">
					<input type="button" class="wmtButton" onclick="doReturn('<?php echo $order_data->id ?>')" value="close" />
				</td>
			</tr>
		</table>
	</form>
<?php
	exit; 
}

echo "<pre>\n";

try { // catch any processing errors
	// get a handle to processor
	$client = new QuestOrderClient($lab_id);

	// create request message
	$client->buildRequest($order_data);

	// determine third-party payment information
	$ins_primary_type = 0; // default self
	if ($order_data->work_flag) { // workers comp claim
		$order_data->request_billing = 'T';

		// build workers comp insurance record
		$ins_data = new wmtInsurance($work_insurance);
		$ins_data->plan_name = "Workers Comp"; // IN1.08
		$ins_data->group_number = ""; // IN1.08
		$ins_data->work_flag = "Y"; // IN1.31
		$ins_data->policy_number = $order_data->work_case; // IN1.36

		// create hl7 segment
		$client->addInsurance(1, $ins_data);
	}
	else { // normal insurance
		
		// SFA PROPERLY ORDER INSURANCE
		if ($GLOBALS['wmt::lab_ins_pick']) { // special processing for sfa
			if ( !in_array($order_data->request_billing, array('C','T','P','')) ) {
				// assume its an insurance id
				$ins_primary = $order_data->request_billing; // use selected
				$order_data->ins_primary = $ins_primary;
				$ins_secondary = null;  // no secondary
				$order_data->ins_secondary = $ins_secondary;
				$order_data->request_billing = 'T'; // make third-party
			}
		}
		

		// PROCARE CASE ORDERING
		if ($GLOBALS['wmt::case_ins_pick']) {
			if ($order_data->request_billing == 'T') {
				$case_data = sqlQueryNoLog("SELECT `enc_case` FROM `case_appointment_link` WHERE `encounter` = ?",array($encounter));
				if ($case_data) $ins_list = wmtInsurance::getPidInsCase($pid,$case_data['enc_case']);
				$order_data->ins_primary = $ins_list[0]->id;
				$order_data->ins_secondary = $ins_list[1]->id;
				$order_data->ins_tertiary = $ins_list[2]->id;
			}
		}


		$ins_primary = false;
		$ins_primary_type = 0; // default self
		if ($order_data->ins_primary) {
			$ins_primary = new wmtInsurance($order_data->ins_primary);
			$ins_primary_type = $ins_primary->plan_type; // save for ABN check
		}

		$ins_secondary = false;
		if ($order_data->ins_secondary) {
			$ins_secondary = new wmtInsurance($order_data->ins_secondary);
		}

		// create insurance records
		if ( $order_data->request_billing != 'C' && !$ins_primary )
			$order_data->request_billing = 'P'; // if not client bill and no insurance must be patient bill

		if ($order_data->request_billing == 'T' && $ins_primary ) { // only add insurance for third-party bill with insurance
			$client->addInsurance(1, $order_data->request_billing, $ins_primary);
			if ($ins_secondary)
				$client->addInsurance(2, $order_data->request_billing, $ins_secondary);
		}
		else {
			$client->addInsurance(1, $order_data->request_billing, false);
		}
	}
	
	// add guarantor (use insured if available, patient otherwise)
	$client->addGuarantor($order_data->pid, $ins_primary);

	// create orders (loop)
	$seq = 1;
	$test_list = array(); // for requisition
	foreach ($item_list as $item_data) {
		$client->addOrder($seq++, $order_data, $item_data, $aoe_list);
		$test_list[] = array('code'=>$item_data->procedure_code,'name'=>$item_data->procedure_name);
	}
	
	$abn_needed = false;
	if ($ins_primary_type == 2 && !$order_data->work_flag) { // medicare but not workers comp
		$doc_list = $client->getOrderDocuments($order_data->pid,'ABN');
		if (count($doc_list)) {
			$order_data->order_abn_id = $doc_list[0]->get_id();
			$order_data->update();	
			
			if (!$order_data->order_abn_signed) {
				echo "\n\nThis order requires a Medicare 'Advance Beneficiary Notice of Noncoverage'";
				echo "\nPlease print the ABN document and obtain the patient's signature.";
				echo "\nThen resubmit this order with the ABN SIGNED checkbox marked.\n\n\n";	
				$abn_needed = true;		
			}
		}
	}
	
	if (!$order_data->order_abn_id || $order_data->order_abn_signed) { // only submit if ABN not necessary or signed
		// generate requisition
		$doc_list = $client->getOrderDocuments($order_data->pid,'REQ');

		if (count($doc_list)) { // got a document so suceess
			$order_data->status = 's'; // processed
			$order_data->order_req_id = $doc_list[0]->get_id();
			$order_data->order_status = 'processed';
			$order_data->update();	
		}
		else {
			die("FATAL ERROR: failed to generate requisition document!!");
		}
		

		// SFA Automatic lab draw billing!!
		if ($GLOBALS['wmt::auto_draw_bill'] && $order_data->specimen_draw == 'int') {
			// include the FeeSheet class
			require_once($GLOBALS['srcdir']."/FeeSheet.class.php");
		
			// create a new billing object (PID and ENC required)
			$fs = new FeeSheet($pid, $encounter);
		
			// build billing fee item
			$fs->addServiceLineItem(array(
					'codetype'  => 'CPT4',
					'code'  => '36415',  // code item number
					'auth'  => '1',
					'units'  => '1', // as appropriate
					'justify'  => $drg_string,  // ICD10|123.45:ICD10|9876 (not required)
					'provider_id' => $provider_id  // if missing uses enc provider
			));
		
			// create dx entries if present
			if (count($drg_array) > 0) {
				foreach ($drg_array AS $dx_code => $dx_text) {
					// insert diagnosis code
					$fs->addServiceLineItem(array(
							'codetype'  => 'ICD10',
							'code'  => $dx_code,  // as listed in the ICD10 table
							'auth'  => '1',
							'provider_id' => $provider_id  // if missing uses enc provider
					));
				}
			}
		
			// save billing after all items added (service items & product items generated above)
			$fs->save($fs->serviceitems, $fs->productitems);
		}
		// End SFA billing
		
	}
}
catch (Exception $e) {
	die ("FATAL ERROR: ".$e->getMessage());
}
?>
					</pre>
				</td>
			</tr>
<?php 
if (count($doc_list)) { // no documents order failed
?>
			<tr>
				<td class="wmtLabel" colspan="2" style="padding-bottom:10px;padding-left:8px">
					Label Printer: 
					<select class="nolock" id="labeler" name="labeler" style="margin-right:10px">
						<?php getLabelers($_SERVER['REMOTE_ADDR'])?>
					</select>
					Quantity:
					<select class="nolock" name="count" style="margin-right:10px">
						<option value="1"> 1 </option>
						<option value="2"> 2 </option>
						<option value="3"> 3 </option>
						<option value="4"> 4 </option>
						<option value="5"> 5 </option>
					</select>

					<input class="nolock" type="button" tabindex="-1" onclick="printLabels(1)" value="Print Labels" />
				</td>
			</tr>
<?php 
} // end of failed test
?>				
			<tr>
				<td>
<?php if ($order_data->order_abn_id) { ?>
					<input type="button" class="wmtButton" onclick="location.href='<?php echo $document_url . $order_data->order_abn_id ?>';return false" value="ABN print" />
<?php } ?>				
<?php if ($order_data->order_req_id) { ?>
					<input type="button" class="wmtButton" onclick="location.href='<?php echo $document_url . $order_data->order_req_id ?>';return false" value="REQ print" />
<?php } ?>
				</td>
				<td style="text-align:right">
<?php if (!$abn_needed) { ?>
					<input type="button" class="wmtButton" onclick="doClose()" value="close" />
<?php } ?>
					<input type="button" class="wmtButton" onclick="doReturn(<?php echo $order_data->id ?>)" value="return" />
				</td>
			</tr>
		</table>
	</form>
