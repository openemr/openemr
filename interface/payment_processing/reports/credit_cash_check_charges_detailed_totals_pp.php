<?php

/**
 * To display credit card, check, cash collected with option to 'assign' or 'refund' payments
 * 
 * gets data from table cc_ledger1 and displays a 90-day report of all cash, check and credit card 
 * payments. Will display either 'Live' or 'Test' credit card data based on live/test key 
 * in Administration > Globals > CC Gateway > PK_KEY. Clicking on the assign arrow will link to 
 * process_payment.php that lets you 'assign' or 'refund' this payment against an  'encounter'
 * NB: This payment has to be MANUALLY ASSIGNED in opnEMR to be refelected in tables ar_session and ar_activity
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
	require_once("../../globals.php");
	
   
	$error      = FALSE;
    $result     = FALSE;
	
	// variables to search by
	
	if(isset($_POST['date_from'])){
		$date_from = $_POST['date_from'];
	 }
	// else { $date_from =  date('Y-m').'-01';}
	else{
		
		$date_from = date('Y-m-d', strtotime('-90 days'));
	}
	 
	if(isset($_POST['date_to'])){
		$date_to = $_POST['date_to'];
	 }
	else { $date_to = date('Y-m-d' );}
	
		
	 if(isset($_POST['date_type'])){
		$date_type = $_POST['date_type'];
	 }
	else {$date_type = 'CL.chrg_date';}
	
	//to display either 'Live' or 'Test' Credit card payments based on 
	//live/test key in Administration > Globals > CC Gateway > PK_KEY
	
	$pk_key_stripe = $GLOBALS['pk_key_stripe'];
  
	if (strpos($pk_key_stripe,'live') !== false) { 
	   $live_test = "'L'";
	}		  
	elseif(strpos($pk_key_stripe,'test') !== false){
		$live_test = "'T'";
	}
	// to manually set variables
	//$date_from = date('Y-m-d' );
	//$date_from = '2015-09-09';
	//$date_to = '2015-09-28';
	//$md = '%';
	//$date_type = 'AR.post_time';
	
	
	$date_from1 = new DateTime($date_from);
	$date_from1=  $date_from1->format('m/d/Y');
	
	
	$date_to1 = new DateTime($date_to);
	$date_to1 =  $date_to1->format('m/d/Y');
	
	
	
	if($date_type =='CL.chrg_date'){
		$title_date = " Charge Date";
	}
	
	
	
	$title_short = "Cash Check Credit Charges ";
	$title = "Details of All " .$title_short. " by $title_date -  " . $date_from1 . " to ". $date_to1;
	
	
	
    try {
       		
		$sql_init = "SELECT 	CL.id,
		CL.chrg_date,
		DATE_FORMAT(CL.chrg_date, '%Y-%m-%d') as charge_date,
		CL.process_date,
		PD.fname,
		PD.lname,
		PD.mname,
		CL.pid,
		U.fname as u_fname,
		U.lname as u_lname,
		CL.	account_code,
        CL.currency_code,
		CL.pay_amount,
		CL.comment,
		CASE transaction_type 
		  WHEN $live_test THEN 'Credit' 
		  WHEN 'S' THEN 'Cash' 
		  WHEN 'Q' THEN 'Check' 
		  ELSE transaction_type
		  END AS 'pay_method',
		CASE account_code 
		  WHEN 'PP' THEN 'PT PMT' 
		  WHEN 'PCP' THEN 'COPAY' 
		  WHEN 'POA' THEN 'PMT ON ACCT' 
		  WHEN 'PRP' THEN 'PRE PAY'
		  ELSE account_code
		  END AS 'pmt_type' 

		FROM cc_ledger1 AS CL

		INNER JOIN patient_data AS PD
				ON PD.pid = CL.PID
		INNER JOIN users AS U
				ON U.id = CL.post_user

		WHERE DATE_FORMAT($date_type, '%Y-%m-%d') >= :date_from
			  AND  DATE_FORMAT($date_type, '%Y-%m-%d') <  adddate(:date_to,1)
			  AND CL.transaction_type IN($live_test, 'S', 'Q')
		ORDER BY DATE_FORMAT(CL.chrg_date, '%Y-%m-%d') DESC,  pay_method, CL.post_user   ";
		
		 $sql = "SELECT count(*) as rows 
				FROM ($sql_init) as t";
		
		
        
		//$total = $conn->query($sql)
		$total = $conn->prepare($sql);
		$total->execute(array(':date_from' => $date_from , ':date_to' => $date_to));
		$res = $total->fetch(PDO::FETCH_OBJ);
		
		//echo " Total records is : " . $total->rows. "<br>" ;
		//echo " Total records : " . $res->rows. "<br>" ;
        $perpage = 100;
        //$posts = $total->rows;
		$posts =$res->rows;
        $pages = ceil($posts / $perpage);
		//echo " Total pages : " . $pages. "<br>" ;
        //# default
        $get_pages = isset($_GET['page']) ? $_GET['page'] : 1;

        $data = array(

            'options' => array(
                'default'   => 1,
                'min_range' => 1,
                'max_range' => $pages
                )
        );

        $number = trim($get_pages);
        $number = filter_var($number, FILTER_VALIDATE_INT, $data);
        $range = $perpage * ($number - 1);

        $prev = $number - 1;
        $next = $number + 1;
        
       
        $stmt = $conn->prepare($sql_init . "LIMIT :limit, :perpage");
		$stmt->bindParam(':date_from', $date_from, PDO::PARAM_STR);
		$stmt->bindParam(':date_to', $date_to, PDO::PARAM_STR);
		$stmt->bindParam(':perpage', $perpage, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $range, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchAll();
		
		$count = 0;
		$prev_charge_date =''; 
		$grand_total_pay_amount = 0;
		

    } catch(PDOException $e) {
        $error = $e->getMessage();
    }

    $conn = null;


?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="shortcut icon" href="../favicon/favicon_bills.ico" >
		<title><?php echo $title_short ?></title>
		<link rel="stylesheet" href="../css/report.css">

    </head>
    <body>

         <div id="wrap">

            <?php
            if($error)
            {
                echo "<div class=\"error\"><strong>Database Error:</strong> $error</div>";
            }
            ?>

            <?php
			
			// all strings that need to be translated
					
			$lang=array('id' => htmlspecialchars(xl('ID'), ENT_NOQUOTES),
						'charge_date' => htmlspecialchars(xl('Charge Date'), ENT_NOQUOTES),
						'name' => htmlspecialchars(xl('Name'), ENT_NOQUOTES),
						'mrn' => htmlspecialchars(xl('MRN'), ENT_NOQUOTES),
						'charged_by' => htmlspecialchars(xl('Charged By'), ENT_NOQUOTES),
						'pmt_method' => htmlspecialchars(xl('Pmt Method'), ENT_NOQUOTES),
						'pmt_type' => htmlspecialchars(xl('Pmt Type'), ENT_NOQUOTES),
						'comment' => htmlspecialchars(xl('Comment'), ENT_NOQUOTES),
                        'currency_code' => htmlspecialchars(xl('Currency Code'), ENT_NOQUOTES),
						'assign' => htmlspecialchars(xl('Assign'), ENT_NOQUOTES),
						'amount' => htmlspecialchars(xl('Amount'), ENT_NOQUOTES),
						'cash' => htmlspecialchars(xl('Cash'), ENT_NOQUOTES),
						'check' => htmlspecialchars(xl('Check'), ENT_NOQUOTES),
						'credit' => htmlspecialchars(xl('Credit'), ENT_NOQUOTES),
						'assigned_on' => htmlspecialchars(xl('Assigned on'), ENT_NOQUOTES),
						'unassigned_for' => htmlspecialchars(xl('Unassigned for'), ENT_NOQUOTES),
						'days' => htmlspecialchars(xl('days'), ENT_NOQUOTES),
						'warning' => htmlspecialchars(xl('WARNING'), ENT_NOQUOTES),
						'please_assign_charged' => htmlspecialchars(xl('Please assign, charged'), ENT_NOQUOTES),
						'total_for' => htmlspecialchars(xl('Total for'), ENT_NOQUOTES),
						'total_pages' => htmlspecialchars(xl('Total pages'), ENT_NOQUOTES),
						'prev' => htmlspecialchars(xl('prev'), ENT_NOQUOTES),
						'next' => htmlspecialchars(xl('next'), ENT_NOQUOTES),
						'no_results_found' => htmlspecialchars(xl('No results found'), ENT_NOQUOTES),
						'total_collections' => htmlspecialchars(xl('Total Collections'), ENT_NOQUOTES),
						'unassigned' => htmlspecialchars(xl('Unassigned'), ENT_NOQUOTES),
						'assigned' => htmlspecialchars(xl('Assigned'), ENT_NOQUOTES),
						'total' => htmlspecialchars(xl('Total'), ENT_NOQUOTES),
						'curr' => htmlspecialchars(xl('$'), ENT_NOQUOTES),
						'collections_for' => htmlspecialchars(xl('collections for'), ENT_NOQUOTES)
						
						);

                if($result && count($result) > 0) //<th style = 'style='display:none'> and "<td style='display:none'>$pay_method2" added to help in the modal total calculations
                {
                    echo "
                    <div class='posts'>
                        <h3>$title</h3>
                        <table id = 'credit-cash-check'>
						 
                    <thead>
                        <th>{$lang['id']}
                        <th>{$lang['charge_date']}
						<th>{$lang['name']}
						<th>{$lang['mrn']}
						<th>{$lang['charged_by']}
						<th>{$lang['pmt_method']}
						<th>{$lang['pmt_type']}
						<th>{$lang['comment']}
						
                        <th style = 'text-align:center'>{$lang['currency_code']}
						<th style = 'text-align:right'>{$lang['amount']}
						<th style='display:none'>
						                      
                        
                    <tbody>
            ";
                    foreach($result as $key => $row)
                    { 
						$name = $row[fname]." ". trim($row[mname]." ".$row[lname]);
						$id = trim($row[id]);
						$charge_date = $row[charge_date];
						$process_date = $row[process_date];
						$process_date_short = substr($process_date, 0, 10);
						$pid = $row[pid];
						$charged_by = substr($row[u_fname],0,1).' '.substr($row[u_lname],0,3);
						$pmt_type = $row[pmt_type];
						$pay_method = $row[pay_method];
						$comment = $row[comment];
                        $currency_code = strtoupper($row[currency_code]);
						$amount = $row[pay_amount];
						$date1 = date_create($charge_date);
						$date2 = date_create(date('Y-m-d'));
						$date_diff_days = date_diff($date1, $date2) -> format("%a");
						$pay_method2 = trim($row[pay_method]);
						
						
											
						//conditional formatting of cells, makes blank cells pink
						
						$cursor = "cursor: cell" ;
						//on click with appopriate arguements for payment collector and payment method columns
						$on_click = "onClick=\"collectorTotal(this.parentNode.cells[1].firstChild.data, this.parentNode.cells[4].firstChild.data)\"";
						//$on_click1 = "onClick=\"calculateTotal(this.parentNode.cells[1].firstChild.data, this.parentNode.cells[10].firstChild.nodeValue)\"";
						
						if($name==''){$td_name = "<td style = 'background-color:pink'>$name"; }
						else{$td_name = "<td >$name";}
						
						if($id==''){$td_id = "<td style = 'background-color:pink'>$id"; }
						else{$td_id = "<td >$id";}
						
						if($charge_date==''){$td_charge_date = "<td style = 'background-color:pink'>$charge_date"; }
						else{$td_charge_date = "<td >$charge_date";}
						
						if($row[pid] ==''){$td_pid = "<td style = 'background-color:pink'>$row[pid]";}
						else{$td_pid = "<td >$row[pid]";}
						//else{$td_pid = "<td >$row[pid]";}
						
						if($charged_by==''){$td_charged_by = "<td style = 'background-color:pink'>$charged_by"; }
						else{$td_charged_by = "<td  class = 'prominent' style = 'cursor:url(../favicon/favicon_bills.ico), cell;' $on_click>$charged_by";}
						
						if($pmt_type==''){$td_pmt_type = "<td style = 'background-color:pink'>$pmt_type"; }
						else{$td_pmt_type = "<td >$pmt_type";}
						//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
						// to accomodate internationilization allows innerHTMl of hidden field to conditionally determine image
						 if ($pay_method2 == 'Cash'){$img = 'assign-cash.png';}
						 elseif ($pay_method2 == 'Check'){$img = 'assign-check.png';}
						 elseif ($pay_method == 'Credit'){$img = 'assign-credit.png';}
						 else {$img = 'assign.png';}
						//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~	
						
						
						if($pay_method== 'Cash'){$styled_pay_method = "<span style = 'color:purple' >{$lang['cash']}</span>";}
						elseif($pay_method== 'Check'){$styled_pay_method = "<span style = 'color:#EE7600' >{$lang['check']}</span>";}
						elseif($pay_method== 'Credit'){$styled_pay_method = "<span style = 'color:green' >{$lang['credit']}</span>";}
						
						if($pay_method==''){$td_pay_method = "<td style = 'background-color:pink'>$styled_pay_method"; }
						else{$td_pay_method = "<td class = 'prominent'>$styled_pay_method";}
						 
						 
						 
						$td_comment = "<td >$comment";
						
						if ($process_date !== '0000-00-00 00:00:00'){
							
							//$td_assign = "<td style='text-align:center;'><a href = 'process_payment.php?id=$id&pay_method=$pay_method' title = '{$lang['assigned_on']} $process_date_short' onclick='top.restoreSession()'><img id='assign-image'src = '../img/tick.png'></a>";
						}
						else{
							if($date_diff_days > 3 && $date_diff_days<= 7 ){
								$td_assign = "<td style = 'text-align:center;background-color:#FBFB8F' ><a href = 'process_payment.php?id=$id&pay_method=$pay_method' title = '{$lang['unassigned_for']} $date_diff_days {$lang['days']}' onclick='top.restoreSession()'><img id='assign-image' src = '../img/$img'></a>";
							}
							elseif ($date_diff_days > 7 && $date_diff_days<= 14 ){
								$td_assign = "<td style = 'text-align:center;background-color:pink' ><a href = 'process_payment.php?id=$id&pay_method=$pay_method' title = '{$lang['unassigned_for']} $date_diff_days {$lang['days']}' onclick='top.restoreSession()'><img id='assign-image' src = '../img/$img'></a>";
							}
							elseif ($date_diff_days > 14 ){
								$td_assign = "<td style = 'text-align:center;background-color:red' title = '{$lang['warning']}: {$lang['unassigned_for']} $date_diff_days days' ><a href = 'process_payment.php?id=$id&pay_method=$pay_method' onclick='top.restoreSession()'><img id='assign-image' src = '../img/$img'></a>";
							}
							else{
								$td_assign = "<td style='text-align:center;' ><a href = 'process_payment.php?id=$id&pay_method=$pay_method' title = '{$lang['please_assign_charged']} $date_diff_days days ago' onclick='top.restoreSession()'><img id='assign-image' src = '../img/$img'></a>";
							}
							
						 }
						if($currency_code==''){$td_currency_code = "<td style = 'background-color:pink'>$currency_code"; }
						else{$td_currency_code = "<td style = 'text-align:center' >$currency_code";}
						
						
						
						if($amount==''){$td_amount = "<td style = 'background-color:pink; text-align:right'>$amount"; }
						else{$td_amount = "<td style='text-align:right' >$amount";}
						
						$count++;
						$total_pay_amount = $row[pay_amount];
										
						if($count != 1  && $charge_date != $prev_charge_date)   {
							echo "
								<tr class = 'totals' style  = 'background-color: yellow;'>
									<td colspan ='9' style='text-align:right'>{$lang['total_for']} $prev_charge_date : </td>
									<td style='text-align:right'> $grand_total_pay_amount </td>
								</tr>
							";
								 $grand_total_pay_amount = 0; //Setting total to 0 once it is displayed
								
								 
							
						}
						
						$td_hid_pay_method = "<td style='display:none'>$pay_method2";
						
						$grand_total_pay_amount +=  $total_pay_amount;
						$grand_total_pay_amount = number_format($grand_total_pay_amount,2,".","");
						$prev_charge_date = $charge_date ;
									
                        echo "
                        <tr>
                            $td_id
							$td_charge_date
							$td_name
							$td_pid
							$td_charged_by
							$td_pay_method
							$td_pmt_type
							$td_comment
							
                            $td_currency_code
							$td_amount
							$td_hid_pay_method
							
                            
                            
                        ";
                    } 
				if($grand_total_pay_amount  != 0){
					echo "
									<tr class = 'totals' style  = 'background-color: yellow;'>
										<td colspan ='9' style='text-align:right'>{$lang['total_for']} $charge_date : </td>
										<td style='text-align:right'> $grand_total_pay_amount </td>
																	
									</tr>
							";
				}
					
                    echo '
                    </table>
                </div>
                ';
                }

            ?>

            <div class="navigation">
            <?php

                 if($result && count($result) > 0)
                {
                    echo "<h4>{$lang['total_pages']} ($pages)</h4>";

                    # first page
                    if($number <= 1)
                        echo "<span>&laquo; {$lang['prev']}</span> | <a href=\"?page=$next\">{$lang['next']} &raquo;</a>";

                    # last page
                    elseif($number >= $pages)
                        echo "<a href=\"?page=$prev\">&laquo; {$lang['prev']}</a> | <span>{$lang['next']} &raquo;</span>";

                    # in range
                    else
                        echo "<a href=\"?page=$prev\">&laquo; {$lang['prev']}</a> | <a href=\"?page=$next\">{$lang['next']} &raquo;</a>";
                }

                else
                {
                    echo "<p>{$lang['no_results_found']}.</p>";
                }


            ?>
            </div>

        </div>
		
		<!-- modal-div~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
				<div id = "modalWrapper" class="modal-wrapper"> 
					<div class="modal-overlay">     
						&nbsp; 

					</div>   
						<div class="modal-vertical"> 
							<div id = "popupModal" class="popup-modal"> 
								<div id = "modalHeader" class = "modal-header" ></div>
								<div id = "modalBody" class = "modal-body">
									<div id= "line1" > 
										<div id="line1-left" class="line-left"></div>
										<div id="line1-right" class="line-right"></div>
										<div style="clear: both;"></div>
									</div>
									<div id= "line2" > 
										<div id="line2-left" class="line-left"></div>
										<div id="line2-right" class="line-right"></div>
										<div style="clear: both;"></div>
									</div>
									<div id= "line3" class = "" > 
										<div id="line3-left" class="line-left"></div>
										<div id="line3-right" class="line-right"></div>
										<div style="clear: both;"></div>
									</div>
									<div id= "line4" class = "line-total" > 
										<div id="line4-left" class="line-left"></div>
										<div id="line4-right" class="line-right highlight"></div>
										<div style="clear: both;"></div>
									</div>
								
								</div>
								
								<div id = "modalClose" >
									<a id="closeModal"><img src="../img/modal_close.png" onClick = "closeModal();"/></a>
								</div>
							</div>
						</div> 
						
				</div> 
		
		<!-- end-modal-div~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
		
		<script>
		
		
		function calculateTotal(payDate, payMethod){
			// To calculate the total of a particular payment method
			var payDate = payDate.trim();
			var payMethod = payMethod.trim();
			
			
			
			var ccc = document.getElementById('credit-cash-check');
			var cellTotal = 0;
			var assignedTotal = 0;
			var unassignedTotal = 0;
			
			for(var i=0;i<ccc.rows.length;i++) {
				var trs = ccc.getElementsByTagName("tr")[i];
				
				if(trs.cells.length > 2 ){
					
					var cellValDate= trs.cells[1].firstChild.data;
					//var cellValMethod=trs.cells[5].firstChild.innerHTML;
					//var cellValAmount= parseFloat(trs.cells[9].firstChild.data);
									
					if(trs.cells.length == 11 ){
						var cellValAmount= parseFloat(trs.cells[9].firstChild.data);
						var cellValMethod=trs.cells[10].firstChild.nodeValue;// changed on 1/27/2016 to accomodate for foreign languages thae chenge value of Pmt Method Cell
					}
					else if(trs.cells.length > 11 ){
						var cellValAmount= parseFloat(trs.cells[10].firstChild.data);
						var cellValMethod=trs.cells[11].firstChild.nodeValue;// changed on 1/27/2016 to accomodate for foreign languages thae chenge value of Pmt Method Cell
						
					}
					
					
					cellValDate = cellValDate.trim();
					cellValMethod = cellValMethod.trim();
					
					
					if (!isNaN(cellValAmount) && cellValMethod == payMethod &&  cellValDate == payDate){
						cellTotal += cellValAmount;
						var cellImgSrc = trs.cells[8].childNodes[0].firstChild.src; // to look for tick.png which means it is assigned
						if(cellImgSrc.indexOf('tick.png') > -1 ){
							assignedTotal += cellValAmount;
						}
						else {
							unassignedTotal += cellValAmount;
						}
						
					}
				}
			} 
	
					
			//conditional selection of modal color 
			var modalBackground = payMethod.toLowerCase()+"-light";
			var modalHeaderBackground = payMethod.toLowerCase()+"-dark";
			
			// defining the various elements that need to be worked on 
			var displayModal = document.getElementById("modalWrapper");
			var popupModal = document.getElementById("popupModal");
			var modalHeader = document.getElementById("modalHeader");
			var modalBody = document.getElementById("modalBody");
			
			var modalBodyLine1Left = document.getElementById("line1-left");
			var modalBodyLine1Right = document.getElementById("line1-right");
			
			var modalBodyLine2Left = document.getElementById("line2-left");
			var modalBodyLine2Right = document.getElementById("line2-right");
			
			var modalBodyLine3Left = document.getElementById("line3-left");
			var modalBodyLine3Right = document.getElementById("line3-right");
			
			var modalBodyLine4Left = document.getElementById("line4-left");
			var modalBodyLine4Right = document.getElementById("line4-right");
		
			
			
			
			// styling the color of the modal
			popupModal.className = "popup-modal " + modalBackground;
			modalHeader.className = "modal-header " + modalHeaderBackground;
			modalBodyLine1Right.className = "line-right warning";
			modalBodyLine2Right.className = "line-right good";
			modalBodyLine3Right.className = "line-right";
			modalBodyLine4Right.className = "line-right highlight";
			
			// content of the modal	header
			
			if (payMethod == 'Cash'){payMethodHead = <?php echo "'".$lang['cash']."'";?>;}
			else if(payMethod == 'Check'){payMethodHead = <?php echo "'".$lang['check']."'";?>;}
			else if (payMethod == 'Credit'){payMethodHead = <?php echo "'".$lang['credit']."'";?>;}
			
			modalHeader.innerHTML = payMethodHead + " - " + "<?php echo $lang['total_collections']; ?>" + " - " + payDate ;
			
			//modalBody.innerHTML = bodyContent;
			
			modalBodyLine1Left.innerHTML = " <?php echo $lang['unassigned']; ?> " + payMethodHead + " <?php echo $lang['total']; ?>:";
			modalBodyLine1Right.innerHTML = "<?php echo $lang['curr']; ?>"+ unassignedTotal.toFixed(2);
			
			modalBodyLine2Left.innerHTML = "<?php echo $lang['assigned']; ?> " + payMethodHead + " <?php echo $lang['total']; ?>:";
			modalBodyLine2Right.innerHTML = "<?php echo $lang['curr']; ?>"+ assignedTotal.toFixed(2);
			
			modalBodyLine3Left.innerHTML = "";
			modalBodyLine3Right.innerHTML = "";
			
			modalBodyLine4Left.innerHTML = "<?php echo  $lang['total']; ?>  " + payMethodHead + " <?php echo $lang['collections_for']; ?> <span style='font-weight:bold'>" + payDate +": </span> ";
			modalBodyLine4Right.innerHTML = "<?php echo $lang['curr']; ?>"+ cellTotal.toFixed(2);
									
			
			//diplaying the modal
			displayModal.style.display = "block";
			
		}
		


		</script>
		<script type="text/javascript" src="../scripts/js/closeModal.js"></script>
		
		<script type="text/javascript" >
			<?php  
				require ("../scripts/php/collectorTotal.php");
				require($GLOBALS['srcdir'] . "/restoreSession.php"); 
			?>
		</script>

    </body>
</html>
