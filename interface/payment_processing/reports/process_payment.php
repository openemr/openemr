<?php

/**
 * To 'assign' or 'refund' the collected credit card, check, cash payments
 * NB: NO DATA is written to tables ar_activity and ar_session
 * 
 * gets data of a single row from table cc_ledger1 and displays a form with details of the 
 * payment. Need to select 'encounter' and 'processed by', optionally add a comment
 * then 'assign' or 'refund'. Clicking 'assign' will write the encounter number to table cc_ledger1
 * Clicking 'refund' will take the 'pay_amount' value from table 'cc_ledger1' and append it to 'comment' as string
 * refunded 'pay_amount' on datestamp and update 'refund' with that value. It will also set pay_amount to 0.00 for that row, i.e. full refund only
 * a date-time value is written to 'process_date' in table 'cc_ledger1' That will allow to conditionally display a green tick mark in 
 * the 'assign' column in credit_cash_check_charges_detailed_totals_assign_pp.php 
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 * 
 * @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2016, Sherwin Gaddis, Ranganath Pathak
 * @version 2.0 
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.open-emr.org 
 */

require "../scripts/php/connection_openemr.inc.php";
require_once "../scripts/php/sanitize.php";
require_once("../../globals.php");
	
	// all strings that need to be translated
						
	$lang=array('select_encounter' => htmlspecialchars(xl('Select Encounter'), ENT_NOQUOTES),
				'choose_one' => htmlspecialchars(xl('Choose one'), ENT_NOQUOTES),
				'cannot_do_zero_amount_refund' => htmlspecialchars(xl('Cannot do zero amount refund'), ENT_NOQUOTES),
				'refunded' => htmlspecialchars(xl('refunded'), ENT_NOQUOTES),
				'on' => htmlspecialchars(xl('on'), ENT_NOQUOTES),
				'currency' => htmlspecialchars(xl('$'), ENT_NOQUOTES) // to get rid of dollar sign in pay_amount
				
				);
	
	
   
	$error      = FALSE;
    $result     = FALSE;
	
	// variables to search by
	
	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$pay_method= $_GET['pay_method'];
	 }
	elseif(isset($_POST['row_id'])){
		$id = $_POST['row_id'];
		$pay_method= $_POST['pay_method'];
	 }
	else{
		
		echo "No id returned";
		exit;
	}
	
	
	try{
		//update table cc_ledger1 if form is submitted
		
		if(isset($_POST['assign'])){
			$message = '';
			if(isset($_POST['row_id'])){$id = check_input($_POST['row_id']);}	// ? redundant	
			if(isset($_POST['encounter'])){ $encounter = check_input($_POST['encounter']);}
			if(isset($_POST['comment'])){$comment = check_input($_POST['comment']);}
			if(isset($_POST['processor'])){$processor = check_input($_POST['processor']);} 
			if(isset($_POST ['process_date'])  && $_POST['process_date'] !==''){
				$process_date = check_input($_POST['process_date']);
			}
			else{
				$date1 = date_create();
				$process_date = date_format($date1, 'Y-m-d H:i:s');
			}
			
					
			// start new code - contains conditional for encounter
			if(empty($_POST['encounter'])){ 
								$message2  = "* {$lang['select_encounter']}";
								$background2="background:#FFB6C1;";
			}
					
			else {if(isset($_POST['processor']) && $_POST['processor'] !==''){
							$sql_insert =  'UPDATE cc_ledger1 as CL
											SET CL.processor = :processor,
											CL.process_date = :process_date,
											CL.encounter = :encounter,
											CL.comment = :comment
											WHERE CL.id = :id ';
				
					$stmt =  $conn -> prepare($sql_insert);
					$stmt->bindParam(':processor', $processor, PDO::PARAM_INT);
					$stmt->bindParam(':process_date', $process_date, PDO::PARAM_STR);
					$stmt->bindParam(':encounter', $encounter, PDO::PARAM_INT);
					$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					
					header('Location: credit_cash_check_charges_detailed_totals_assign_pp.php');
					exit();
					
				}
				else{
					$message = "* {$lang['choose_one']}";
					$background="background:#FFB6C1;";
				}
	
			}
			
			// end of new code
			
		}
		
		
		// for refunds - does only full refunds
		
		if(isset($_POST['refund_charge'])){
			$message = '';
			if(isset($_POST['processor']) && $_POST['processor'] !==''){
				if(isset($_POST['pay_amount']) ){$pay_amount = check_input($_POST['pay_amount']);}
				//$pos_curr = strpos($pay_amount, $lang['currency']);
				//$pay_amount1 = substr($pay_amount, ($pos_curr + 1));
				if(isset($_POST['row_id'])){$id = check_input($_POST['row_id']);}	// ? redundant	
				if(isset($_POST['comment'])){$comment = check_input($_POST['comment']);}
				if(isset($_POST['processor'])){$processor = check_input($_POST['processor']);} 
				if(isset($_POST ['process_date'])  && $_POST['process_date'] !==''){
					$process_date = check_input($_POST['process_date']);
				}
				else{
					$date1 = date_create();
					$process_date = date_format($date1, 'Y-m-d H:i:s');
				}
				//if(isset($_POST['pay_amount'])&& substr($_POST['pay_amount'],1) > 0.00){ // updates for non zero +ve numbers  0.00
				if(isset($_POST['pay_amount'])&& $pay_amount > 0.00){ // updates for non zero +ve numbers  0.00
					$refund  = $pay_amount;
					
					$comment .= " {$lang['refunded']} ".$pay_amount . " {$lang['on']} " . date('Y-m-d H:i:s');;
					
					$pay_amount = 0.00; // money fully refunded, amount now set to zero
					
					$sql_insert =  'UPDATE cc_ledger1 as CL
									SET CL.processor = :processor,
									CL.pay_amount = :pay_amount,
									CL.process_date = :process_date,
									CL.refund = :refund,
									CL.comment = :comment
									WHERE CL.id = :id ';
					
					$stmt =  $conn -> prepare($sql_insert);
					$stmt->bindParam(':processor', $processor, PDO::PARAM_INT);
					$stmt->bindParam(':pay_amount', $pay_amount, PDO::PARAM_INT);
					$stmt->bindParam(':process_date', $process_date, PDO::PARAM_STR);
					$stmt->bindParam(':refund', $refund, PDO::PARAM_INT);  
					$stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
					$stmt->execute();
					
					header('Location: credit_cash_check_charges_detailed_totals_assign_pp.php');
					exit();
					
				}
				else{
					$message1 = "* {$lang['cannot_do_zero_amount_refund']}";
					$background1="background:#FFB6C1;";
				}
			}
			else{
				$message = "* {$lang['choose_one']}";
				$background="background:#FFB6C1;";
			}
		}
		
		
		
		if(isset($_POST['row_id'])){
		$id = $_POST['row_id'];
		
		}
		
		
		$sql = "SELECT 	CL.id,
					CL.trans_id,
					CL.pid,
					CL.account_code,
                    CL.currency_code,
					CL.pay_amount,
					DATE_FORMAT(CL.chrg_date, '%Y-%m-%d') as charge_date,
					CL.processor,
					CL.processed,
					CL.process_date,
					CL.encounter,
					CL.comment,
					CL.refund,
					PD.lname AS pt_lname,
					PD.fname AS pt_fname,
					PD.mname AS pt_mname,
					CASE account_code 
						  WHEN 'PP' THEN 'PT PMT' 
						  WHEN 'PCP' THEN 'COPAY' 
						  WHEN 'POA' THEN 'PMT ON ACCT' 
						  WHEN 'PRP' THEN 'PRE PAY'
						  ELSE account_code
						  END AS 'pmt_type'  
			FROM cc_ledger1 as CL
			INNER JOIN patient_data as PD 
					ON PD.pid = CL.pid
			WHERE CL.id = :id  ";
			
		$stmt = $conn->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$result = $stmt->fetch();
		
		$enc_pid = $result['pid'];
		$enc_date = $result['charge_date'];
		
		// to get option values for select box for processor
		$uid = $_SESSION['authUserID'];
		$sql1 = "SELECT U.id,
						U.fname,
						U.specialty
				FROM users as U
                WHERE U.id = :id
                    AND U.active = 1";
		
		
		// $stmt1 = $conn->query($sql1);
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bindParam(':id', $uid, PDO::PARAM_INT);
        $stmt1->execute();
		$result1 = $stmt1->fetchAll();
		$option_value = '';
		
	
		// to get option values for select box for encounter number

		$sql2 = "SELECT FE.date as encounter_date,
						FE.encounter
				FROM form_encounter AS FE
                WHERE FE.pid = :enc_pid
						AND FE.date <= :enc_date
                ORDER BY FE.date DESC";

		
		$stmt2 = $conn->prepare($sql2);
        $stmt2->bindParam(':enc_pid', $enc_pid, PDO::PARAM_INT);
        $stmt2->bindParam(':enc_date', $enc_date, PDO::PARAM_STR);
        $stmt2->execute();
		$result2 = $stmt2->fetchAll();
		$option_encounter = '';
	
	
	} 
	catch(PDOException $e) {
        $error = $e->getMessage();
    }

    $conn = null;
	
	// all strings that need to be translated
						
	$lang=array(
				'cash_payment_processing' => htmlspecialchars(xl('Cash Payment Processing'), ENT_NOQUOTES),
				'check_#' => htmlspecialchars(xl('Check #'), ENT_NOQUOTES),
				'credit_card_payment_processing' => htmlspecialchars(xl('Credit Card Payment Processing'), ENT_NOQUOTES),
				'payment_processing' => htmlspecialchars(xl('Payment Processing'), ENT_NOQUOTES),
				'do_you_wish_to_proceed'=> htmlspecialchars(xl('Do you wish to proceed'), ENT_NOQUOTES)
				);
	
	
	 // conditionals
			  
			  if ($pay_method =='Cash'){
										$form_title = $lang['cash_payment_processing'];
										$class_dark = 'cash-dark';
										$class_light = 'cash-light';
									}
			 elseif ($pay_method =='Check'){
				 
										$trans_id_array = explode( '~', $result['trans_id']);
										$check_no = $trans_id_array[2];
										$form_title = "{$lang['check_#']} $check_no {$lang['payment_processing']}";
										$class_dark = 'check-dark';
										$class_light = 'check-light';
									}
			 elseif ($pay_method =='Credit'){
										$form_title = $lang['credit_card_payment_processing'];
										$class_dark = 'credit-dark';
										$class_light = 'credit-light';
									}	 
			 else {
				 
										$form_title = $lang['payment_processing'];
										$class_dark = 'credit-dark';
										$class_light = 'credit-light';
									}
?>
<!DOCTYPE html>

<html lang="en">
	<head>
	  <meta charset="utf-8">

	  <title>Cash Check Credit Payment Processing</title>
	  <link rel="shortcut icon" href="favicon/favicon_cc_payment.ico" >
	  
	  <link rel="stylesheet" href="../css/report.css">
	  
	
	  
	  
	</head>
	<body>
	<?php
            if($error)
            {
                echo "<div class=\"error\"><strong>Database Error:</strong> $error</div>";
            }
	?>
	
	
		<form name = "form_process" id = "form_process" method = "post" action = "process_payment.php" class = "<?php echo $class_light;?>" onsubmit="return confirm('<?php echo $lang["do_you_wish_to_proceed"].'?';?>');">
			<div>
<?php
	if($result && count($result) > 0){
        $currency_code = strtoupper($result['currency_code']);
		$pay_amount = $result['pay_amount'];
		$charge_date = $result['charge_date'];
		$pid = $result['pid'];
		$account_code = $result['account_code'];
		$pmt_type = $result['pmt_type'];
		$processor = $result['processor'];
		$processed = $result['processed'];
		$process_date = $result['process_date'];
		if($process_date=='0000-00-00 00:00:00'){$process_date_view = '';} else{$process_date_view = $process_date;}
		$encounter = $result['encounter'];
		//if($encounter==0){ $encounter_view = '';} else{$encounter_view = $encounter;}
		$comment = $result['comment'];
		if($pay_amount > 0 && $result['refund'] == 0){
			$refund = $result['pay_amount'];
		}
		elseif ($pay_amount ==0 && $result['refund'] > 0){
			$refund = $result['refund'];
		}
		$pt_lname = $result['pt_lname'];
		$pt_fname = $result['pt_fname'];
		$pt_mname = $result['pt_mname'];
		$name = $pt_fname." ". trim($pt_mname." ".$pt_lname);
		
		
		// all strings that need to be translated
						
	$lang=array('select_encounter' => htmlspecialchars(xl('Select Encounter'), ENT_NOQUOTES),
				'choose_one' => htmlspecialchars(xl('Choose one'), ENT_NOQUOTES),
				'cannot_do_zero_amount_refund' => htmlspecialchars(xl('Cannot do zero amount refund'), ENT_NOQUOTES),
				'name' => htmlspecialchars(xl('Name'), ENT_NOQUOTES),
				'mrn' => htmlspecialchars(xl('MRN'), ENT_NOQUOTES),
				'encounter' => htmlspecialchars(xl('Encounter'), ENT_NOQUOTES),
				'select_encounter' => htmlspecialchars(xl('Select Encounter'), ENT_NOQUOTES),
				'amount' => htmlspecialchars(xl('Amount'), ENT_NOQUOTES),
				'payment_type' => htmlspecialchars(xl('Payment Type'), ENT_NOQUOTES),
				'charge_date' => htmlspecialchars(xl('Charge Date'), ENT_NOQUOTES),
				'process_date' => htmlspecialchars(xl('Process Date'), ENT_NOQUOTES),
				'processed_by' => htmlspecialchars(xl('Processed By'), ENT_NOQUOTES),
				'currency' => htmlspecialchars(xl('$'), ENT_NOQUOTES),
				'comment' => htmlspecialchars(xl('Comment'), ENT_NOQUOTES),
				'max_65_characters' => htmlspecialchars(xl('max 65 characters'), ENT_NOQUOTES),
				'assign' => htmlspecialchars(xl('Assign'), ENT_NOQUOTES),
				'cancel' => htmlspecialchars(xl('Cancel'), ENT_NOQUOTES),
				'refund' => htmlspecialchars(xl('Refund'), ENT_NOQUOTES)
				);
		
					
			// for processor select box
			
			foreach ($result1 as $row){
				//if($processor ==11){$selected11 = 'selected';} elseif($processor ==12){$selected12 ='selected';} elseif($processor ==2156){$selected2156 ='selected';}
				
				$id1 = $row ['id'];
				$fname = $row ['fname'];
				
				if ($processor==$id1){$option_value .=  "<option value = $id1 style= '$background'  selected = 'selected'>$fname</option> \r\n";}
				elseif ($processor!=$id1){ $option_value .=  "<option value = $id1 style= '$background' >$fname</option> \r\n";}
				
				}
			// for the encounter select box	
			foreach ($result2 as $row){
								
				$encounter_list = $row ['encounter'];
				$display_text = substr($row ['encounter_date'], 0, 10)." | " . $row ['encounter'];
				
				if ($encounter==$encounter_list){$option_encounter .=  "<option value = $encounter_list style= '$background'  selected = 'selected'>$display_text</option> \r\n";}
				elseif ($encounter!=$encounter_list){ $option_encounter .=  "<option value = $encounter_list style= '$background' >$display_text</option> \r\n";}
				
				}
					
			$str = <<<EOF
				<h3 class = '$class_dark'>$form_title</h3>
				
				<label>
					<span>{$lang['name']}</span><input id='name' type='text' name='name' value = '$name' disabled />
				</label>
				
				<div style = 'float:left'>
				<label>
					<span>{$lang['mrn']}</span><input id='pid' type='text' name='pid' value = '$pid' style= 'width:160px;' disabled />
				</label>
				</div>
				<div style = 'float:left;'>
				<label>
					<span style= 'margin-left: 55px'>{$lang['encounter']}</span><select id='encounter' type='text' name='encounter' style= 'width:160px; margin-left: 55px; $background2' /> 
												<option value = '' style= '$background2'>{$lang['select_encounter']}</option>
												$option_encounter
											</select>
				</label>
				</div>
				<div style = 'width:150px;float:left;border:none;color:red'><br> &nbsp&nbsp$message2</div>
				<div style='clear: both;'></div>
				
				<div style = 'float:left'>
				<label>
					<span>{$lang['amount']} ({$currency_code})</span><input id='pay_amount' type='text' name='pay_amount' value = '$pay_amount' style= 'width:160px; background-color:#FFFFCC;font-weight:700; color:#0674e1; $background1' readonly='readonly' />
				</label>
				</div>
				<div style = 'float:left'>
				<label>
					<span style= 'margin-left: 55px'>{$lang['payment_type']}</span><input id='account_code' type='text' name='account_code' value = '$pmt_type'  style= 'width:160px; margin-left: 55px;background-color:#FFFFCC;font-weight:700; color:#0674e1' disabled />
				</label>
				</div>
				<div style = 'width:150px;float:left;border:none;color:red'><br> &nbsp&nbsp$message1</div>
				<div style='clear: both;'></div>
				<div style = 'float:left'>
				<label>
					<span>{$lang['charge_date']}</span><input id='charge_date' type='text' name='charge_date' value = '$charge_date' style= 'width:160px;' disabled />
				</label>
				</div>
				<div style = 'float:left'>
				<label>
					<span style= 'margin-left: 55px'>{$lang['process_date']}</span><input id='process_date' value = '$process_date_view' type='text' name='process_date' style= 'width:160px; margin-left: 55px' readonly='readonly' />
				</label>
				</div>
				<div style='clear: both;'></div>
				<div style = 'float:left;border:none'>
				<label>
					<span>{$lang['processed_by']}</span><select id='processor' type='text' name='processor' style= $background /> 
												<option value = '' style= '$background'  ></option>
												$option_value
											</select>
											
				</label>
				</div>
				<div style = 'width:150px;float:left;border:none;color:red'><br> &nbsp&nbsp$message</div>
				<div style='clear: both;'></div>
				<div style = 'float:left'>
				<label>
					<span>{$lang['comment']}</span><span style ='font-size:11px'> ({$lang['max_65_characters']})</span><textarea id='comment' name='comment' maxlength="65">$comment</textarea>
					
				</label>
				</div>
				<div class = 'submit-button' style = 'float:right'>
					<input type='submit' id = 'button-blue' class = '$class_dark' name = 'assign' value='{$lang['assign']}' onclick='return top.restoreSession()' />
					<input type='button' id = 'button-blue' class = '$class_dark' name = 'cancel' value=' {$lang['cancel']}' onclick= "top.restoreSession(); window.location.href='credit_cash_check_charges_detailed_totals_assign_pp.php'" />
					<input type='submit' id = 'button-blue' class = '$class_dark' name = 'refund_charge' value='{$lang['refund']}' style = 'color:black;' onclick='return top.restoreSession()'/>
					<input type = 'hidden' id = 'row_id' name = 'row_id' value = $id />
					<input type = 'hidden' id = 'pay_method' name = 'pay_method' value = $pay_method />
					<input type = 'hidden' id = 'refund' name = 'refund' value = $refund />
					
				</div>
				<div style='clear: both;'></div>
				
			</div>
			<div style='clear: both;'></div>
EOF;
	
	
	
	echo $str;
			
	
	 	
	}	
		
		
?>
		
		</form>
		<script>
			<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
		</script>
	</body>
</html>