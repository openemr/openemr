<?php 
	require_once("../../globals.php");
	require_once("$srcdir/options.inc.php");
	
	$patient_id =  $_SESSION['pid']; 
	$icd_codes =  $_GET['icd_codes']; 
	$icd_codes_array = explode(',', $icd_codes);
	$selected_orders =  $_GET['selected_orders'];
	$selected_orders_array = explode(',', $selected_orders);
	$ordering_provider =  $_GET['ordering_provider'];
	$order_date = $_GET['order_date'];
	
	$ordering_provider_name = '';
	$usql = sqlStatement("SELECT id, fname, lname FROM users WHERE active=1 AND id='".$ordering_provider."'"); 
	while($ures = sqlFetchArray($usql)){
		$ordering_provider_name = $ures['fname']." ".$ures['lname'];
	}
	
	$patient_data = Array();
	$psql = sqlStatement("SELECT p.fname,p.lname,p.street,p.city,p.state,p.postal_code,p.phone_contact,id.provider,id.plan_name, id.policy_number FROM patient_data p join insurance_data id on p.pid = id.pid WHERE p.pid='".$patient_id."' and id.type='primary' ORDER BY id.id DESC LIMIT 1"); 
	while($pres = sqlFetchArray($psql)){
		$patient_data = $pres;
	}
		
?>
<html>
	<head></head>
	<body>
		<table style="font-size:13px;width:850px;margin:0px auto;">
			<tr>
				<td colspan="2" style="text-align:center;font-size:16px;"><b>ORDER REQUISITION FORM</b></td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;font-size:14px;padding-bottom:20px;"><?php echo $order_date; ?></td>
			</tr>
			<tr>
				<td style="width:50%">
					<h3 style="background:#DDD;padding:5px 10px;margin-bottom:10px;font-size:15px;">PATIENT NAME & ADDRESS</h3>
					<p style="min-height:100px;font-size:14px;line-height:22px;">
						<?php echo $patient_data['fname'].', '.$patient_data['lname'].' ('.$patient_data['DOB'].')<br/>'; ?>
						<?php echo $patient_data['street'].', '.$patient_data['city'].'<br/>'; ?>
						<?php echo $patient_data['state'].' - '.$patient_data['postal_code'].'<br/>'; ?>
						<?php echo $patient_data['phone_contact'].'<br/>'; ?>
					</p>
				</td>
				<td style="width:50%">
					<h3 style="background:#DDD;padding:5px 10px;margin-bottom:10px;font-size:15px;">INSURANCE INFORMATION</h3>
					<p style="min-height:100px;font-size:14px;line-height:22px;">
					<?php
					$ins = sqlQuery("SELECT * FROM insurance_companies WHERE id='".$patient_data['provider']."'");
					if(!empty($ins['name'])){
						echo $ins['name']."</br>";
						echo "Plan Name: ". $patient_data['plan_name']."</br>";
						echo "Policy Number: ".$patient_data['policy_number']."</br>";
					}
					?>
					</p>
				</td>
			</tr>
			<tr>
				<td>
					<h3 style="background:#DDD;padding:5px 10px;margin-bottom:10px;font-size:15px;">DIAGNOSES (ICD)</h3>
					<p style="min-height:100px;font-size:14px;line-height:22px;">
						<?php 
							foreach($icd_codes_array AS $key=>$val){
								echo $val.'<br/>';
							}
						?>
					</p>
				</td>
				<td>
					<h3 style="background:#DDD;padding:5px 10px;margin-bottom:10px;font-size:15px;">ORDERING PROVIDER</h3>
					<p style="min-height:100px;font-size:14px;line-height:22px;">
						Electronically signed by: <br/>
						<?php echo $ordering_provider_name; ?>
					</p>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<h3 style="background:#DDD;padding:5px 10px;margin-bottom:10px;font-size:15px;">REQUESTED STUDIES</h3>
					<p style="min-height:100px;font-size:14px;line-height:22px;">
						<?php 
							foreach($selected_orders_array AS $key=>$val){
								echo $val.'<br/>';
							}
						?>
					</p>
				</td>
			</tr>
		</table>
		<script>
			window.print();
		</script>
	</body>
</html>