<?php
/**
 *
 * Copyright (C) 2012-2013 Sherwin Gaddis <sherwingaddis@gmail.com> Open Med Practice
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    http://www.open-emr.org
 **/
  
 //SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/htmlspecialchars.inc.php");
require_once("$srcdir/lab.inc");

//require_once("barcode.php");


formHeader("Form:Lab Requisition");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
$obj = $formid ? formFetch("form_requisition", $formid) : array();


global $pid, $encounter;


$oid = fetchProcedureId($pid, $encounter);


if(empty($oid)){
	print "<center>No Order found, please enter procedure order first</center>";
	exit;
	die;
	

}
	$patient_id = $pid;
	$pdata = getPatientData($pid);
	$facility = getFacility();
	$ins = getAllinsurances($pid);
	$provider = getProviders();	
	
	$order = getProceduresInfo($oid);
	//var_dump($order);
	$prov_id = $order['provider_id'];
	$npi = getNPI($prov_id);
	$bar = rand(41689913, 9999999999);
	
	
	?>

<!DOCTYPE html>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<style>
table, th, td {
     border: 1px solid black;
	 border-collapse: collapse;
 }
 
 .req {
	 margin: auto;
	 width: 90%;
	 padding: 10px;
 }
 
 .reqHeader {
	 margin: auto;
	 width: 90%;
	 padding: 10px;	 
 }
 
 .cinfo {
	 float: left;
	 
 }
 
 .pdata {
	
	 position: relative;
	 right: -205px;
	 z-index: -5;
	 
 }
 
 #printable { display: none; }

    @media print
    {
    	#non-printable { display: none; }
    	#printable { display: block; }
    }
 
   .notes {
	  position: relative;
	 float: left;
	 width: 255px;
	 height: 125px;
   }
 
  .dx {
	  position: relative;
	  float: right;
	  border-style: solid;
	  border-width: 1px;
	  width: 130px;
	  height: 125px;
  }
  

 
  .plist {
	  position: relative;
	  float: left;
	
  }
  
  .pFill {
	  float: left;
	  
  }
  .barcode{
	  position: relative;
	  right: -380px;
  }
</style>
</head>

<body>
<div class="barcode">
<br>
<br>
    <img  src="../../forms/requisition/barcode.php?text=<?php echo $oid; ?>" alt="barcode" /></br>
&#160;&#160;&#160;&#160;&#160;	<?php echo $oid; ?>
</div>
<div class="reqHeader" id="printableArea">
<p><font size="4"><b>Req #:</b> <?php echo $oid; ?>  &#160;&#160;&#160;&#160;&#160;&#160;<b>Client #:</b> LU301</font></p>
   <div class="cinfo">
   <font size="4">
       <?php echo $facility['name'] ."<br>". $facility['street'] . "<br>" .
	              $facility['city'].",".$facility['state'].",".$facility['postal_code'] . "<br>" .
				  $facility['phone']; ?>
				  </font>
   </div>
   <div class="pdata">
         <p><font size="4">
  ACL Laboratories</br>
  5400 Pearl Street | Rosemont, IL 60018</br>
  O:847.349.7553 | F: 847.349.7380</br>
         </font></p>

   </div>
</div>
<div class="req" id="printableArea">
	<table style="width:800px" border="1">
	   <tr style="height:125px;"> 
		   <td style="vertical-align:top; width:400px;" >
		   <div class="plist">
			   Collection Date/Time:           </br>
			   Lab Reference ID:          </br>
			   Fasting:                   </br>
			   Hours:		              </br>
			 </div>
			<div class="pFill">
              <?php echo $order['date_collected'];?> </br>
			  
            </div>			
		   </td>
		   <td style="vertical-align:top">
		    <div class="plist">
			   <b>Patient ID: </b>  </br>
			   <b>DOB: </b> </br>
			   <b>Sex: </b>    </br>
			   <b>Patient Name: </b>  </br>
			   <b>Address: </b>   </br>
			   <b>City,St,Zip: </b>   </br>
			   <b>Phone: </b></br>
			</div>
			<div class="pFill">
			     <?php echo " ".$pid; ?></br>
				 <?php echo " ".$pdata['DOB']; ?> </br>  
				 <?php echo " ".$pdata['sex']; ?></br>
				<?php echo $pdata['fname'] ." ". $pdata['lname']; ?> </br>
				<?php echo $pdata['street'] ; ?></br>
				<?php echo $pdata['city'].",".$pdata['state'].",".$pdata['postal_code']; ?></br>
				<?php echo   $pdata['phone_home']; ?></br>
			</div>   
		   </td>
	   </tr>
	   
	   <tr style="height:125px">
		   <td style="vertical-align:top; width:400px;">
			  <font size="4"><strong>Ordering Physician:</strong></font></br>
			  <div class="plist">
			   Name:        </br>
			   NPI:         </br>
			   UPIN:        </br>
			   </div>
			 <div class="pFill">
			   <?php echo $provider[1]; ?></br>
			   <?php echo $npi[0]; ?></br>
			   <?php echo $npi[1]; ?></br>
			   
			   </div>
		   </td>
		   <td style="vertical-align:top">
			 <font size="4"><strong>Responsible Party:</strong></font></br>
			  <div class="plist">
   			   Name:             </br>
			   Address:          </br>
			   City,St,Zip:      </br>
			   Relationship:     </br>
			   </div>
			   <div class="pFill">
			   <?php echo "/"; ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo "/"; ?></br>
			   
			   </div>
		   </td>   
	   
	   
	   </tr>
		  <tr style="height:125px"> 
		   <td style="vertical-align:top; width:400px;">
			  <font size="4"><strong>Primary Insurance:</strong></font></br>
			  <div class="plist">
			   Bill Type:</br>
			   Payor/Carrier Code:</br>
			   Insurance Name:</br>
			   Insurance Address:</br>
			   City,St,Zip:</br>
			   Subscriber/Policy#:</br>
			   Group #:</br>
			   Physician's UPIN:</br>
			   Employer:</br>
			   Relationship:</br>
			  </div>
			<div class="pFill">
			   <?php if(empty($ins[0]['name'])){echo "Client Bill";}else{echo "Insurance";} ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo $ins[0]['name']; ?></br>
			   <?php echo $ins[0]['line1']; ?></br>
			   <?php echo $ins[0]['city'] .", ". $ins[0]['state']." ".$ins[0]['zip']; ?></br>
			   <?php echo $ins[0]['policy_number']; ?></br>
			   <?php echo $ins[0]['group_number']; ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo $ins[0]['subscriber_employer']; ?></br>
			   <?php echo $ins[0]['subscriber_relationship']; ?></br>
			   
			   
			   </div> 
		   </td>
		   <td style="vertical-align:top">
			  <font size="4"><strong>Secondary Insurance:</strong></font></br>
			  <div class="plist">
			   Bill Type:</br>
			   Payor/Carrier Code:</br>
			   Insurance Name:</br>
			   Insurance Address:</br>
			   City,St,Zip:</br>
			   Subscriber/Policy#:</br>
			   Group #:</br>
			   Physician's UPIN:</br>
			   Employer:</br>
			   Relationship:</br>
			   </div>
			 <div class="pFill">
			   <?php if(empty($ins[1]['name'])){echo " ";}else{echo "Insurance";}; ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo $ins[1]['name']; ?></br>
			   <?php echo $ins[1]['line1']; ?></br>
			   <?php echo $ins[1]['city'] .", ". $ins[1]['state']." ".$ins[1]['zip']; ?></br>
			   <?php echo $ins[1]['policy_number']; ?></br>
			   <?php echo $ins[1]['group_number']; ?></br>
			   <?php echo "/"; ?></br>
			   <?php echo $ins[1]['subscriber_employer']; ?></br>
			   <?php echo $ins[1]['subscriber_relationship']; ?></br>
			   
			   </div>
		   </td>
	   </tr>
	   
	   <tr style="height:125px">
		   <td style="vertical-align:top; width:400px;">
			 <font size="4"><strong>Test Ordered:</strong></font></br>
			  <?php echo $order['procedure_name']; ?> 
		   </td>
		   <td style="vertical-align:top">
		    <div class="notes">
			 <font size="4"><strong>Order Notes:</strong></font></br>
			   <?php echo $order['clinical_hx']; ?>
			 </div>
           <div class="dx">
		     <font size="4"><strong>Dx Codes:</strong></font></br>
			 <?php echo $order['diagnoses']; ?>
		   </div>
           </td>  
	   </tr>

	</table>
	<table style="width:800px" border="1"> 
	   <tr style="height:125px">
		  <td style="vertical-align:top">
			   <font size="4"><strong>AOE Q&A: </strong></font></br>
			   
		  </td>
	   </tr>
	</table>
	<br>
	</br>
	&#160;&#160;&#160;&#160;&#160; End of Requisition #:  <?php echo $oid; ?>
</div>
<div class="reqHeader" id="non-printable">
<input type="button" onclick="printDiv('printableArea')" value="Print">
</div>

<script>
function printDiv(divname) {
    var printContents = document.getElementById(divname).innerHTML;
    var originalContents = document.body.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
}
</script>
</body>
</html>

