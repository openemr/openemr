<?php 

 // Copyright (C) 2010 OpenEMR Support LLC
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists prescriptions and their dispensations according
 // to various input selection criteria.
 //
 // @author Sherwin Gaddis sherwingaddis@gmail.com
 
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");
	require_once("$srcdir/formatting.inc.php");
    require_once("../../custom/code_types.inc.php");

 $encounter = $GLOBALS['encounter'];
  if(empty($encounter)){
      echo "<head><meta http-equiv='refresh' content='5' ></head>";
      echo "Open and Encounter first please..";
      exit;
  }
 $date = date('Y-m-d');
 $pid = $GLOBALS['pid'];
 $user = $GLOBALS['authUser'];
 
 //echo "Curent PID = " . $pid;
    $query = sqlStatement("SELECT * FROM care_plan WHERE pid = $pid AND encounter = $encounter ");
    $result = sqlFetchArray($query);
    
    //return all patient demographic data
    $getData = getPatientData($pid);
    //var_dump($getData);
    //var_dump($result); //troubleshooting
    
    //displays the patient
    $dobYMD =  $getData['DOB'];
    $age = getPatientAge($dobYMD);
    
    //retrieve allergies of the patient
    $allergy = sqlStatement("SELECT type, title FROM lists WHERE pid = $pid and type = 'allergy'");
    //var_dump($allergy_r);
    
    //retrieve pharmacy fone numbers
    $ph_id = $getData['pharmacy_id'];
    
    $pharmacy = sqlStatement("SELECT * FROM phone_numbers WHERE foreign_id = '$ph_id' AND type = 5 ");
    $pharmacy_r = sqlFetchArray($pharmacy);
    //var_export($pharmacy_r);
    
    //var_dump($GLOBALS); 
    //Get who edited the record last
    $last_edit = sqlStatement("SELECT user FROM care_plan_audit WHERE pid = $pid and encounter = $encounter LIMIT 1 ");
    $ledit = sqlFetchArray($last_edit);
    
	//Get patient vitals
    $vitals = sqlStatement("SELECT * FROM form_vitals WHERE pid = $pid ORDER BY id DESC LIMIT 1");
    $v_res = sqlFetchArray($vitals);
    //var_dump($v_res);
    
    //Get lab order if any
    $lab = sqlStatement("SELECT form_id FROM forms WHERE pid = $pid AND form_name = 'Laboratory Orders' ORDER BY id DESC LIMIT 1");
    $lab_id = sqlFetchArray($lab);
    
    //Get medications
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid ");
     //var_export($pmeds);
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">


<style>
input[type=text]
{
  width: 85px;
}
div.vitals
{
position: relative;
left: 305px;
}

#printable { display: none; }

    @media print
    {
    	#non-printable { display: none; }
    	#printable { display: block; }
    }
    
</style>


</head>

<body class="">
<div style="text-align:center" class="buttons" id="non-printable">
    <a href='careplan.php' class='css_button' id='back'><span><?php echo htmlspecialchars( xl('Back'), ENT_NOQUOTES); ?></span></a>
</div>
<div id="printableArea">
<center><h1> Care Plan </h1></center>
<p> </p>
<center>
<table width="800" height="75"  >
    <tr>
         <td width="200">
             Patient: <?php echo $getData['lname'] . " " . $getData['fname']; ?>   <br>
             Address:   <?php echo $getData['street']; ?> <br>
             Phone: <?php echo $getData['phone_home']; ?>     <br>
             Email:  <?php echo $getData['email']; ?>          
         
         </td>
         
         <td width="200">
             Age: <?php echo $age; ?><br>
             DOB: <?php echo $getData['DOB']; ?><br>
             Allergies:  <?php while($rows=  sqlFetchArray($allergy)){ echo $rows['title'] . " ";} ?><br>
             Pharmacy Phone:   Fax:<br> 
             
             
         
         </td>
        <td width="200">
 
	</td>
    </tr>  
</table>
    <table width="800" >
            <tr>
        <td width="700">
               Vitals: <?php if(!empty($encounter)){ echo "BP: ".$v_res['bps']."/"
                                                                .$v_res['bpd']." HR: "
                                                                .$v_res['pulse']." Temp: "
                                                                .$v_res['temperature']." Wt: "
                                                                .$v_res['weight']." BMI: "
                                                                .$v_res['BMI'] ; }else{ echo "No vitals";}?>
        </td>
        
    </tr>
    <tr>
        <td>
             <!-- empty -->
        </td>
    </tr>
    </table>
</center>
<div class="vitals" >
 
</div>
<p> </p>
<center>

    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <input type="hidden" name="encounter" value="<?php echo $encounter;  ?>">
    <input type="hidden" name="date" value="<?php echo $date; ?>">
    <input type="hidden" name="audit" value="1">
    <input type="hidden" name="user" value="<?php echo $user; ?>">
    
<table width="850" class="TableGrid1" border="1" cellspacing="0" cellpadding="0" style="margin-left:
 -.25pt;border-collapse:collapse;border:none;mso-border-alt:solid windowtext .5pt;
 mso-yfti-tbllook:1184;mso-padding-alt:0in 5.4pt 0in 5.4pt">
 <tbody><tr style="mso-yfti-irow:0;mso-yfti-firstrow:yes;height:26.6pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  mso-border-alt:solid windowtext .5pt;height:41.6pt">
  <b>Diagnoses</b>
  </td>
  <td width="68" valign="top" style="width:50.9pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
Active / Inactive<o:p></o:p></span></b></p>
  </td>
  <td width="289" valign="top" style="width:66.7pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
  Risk Factors (compliance, clinical)
  </td>
  <td width="103" valign="top" style="width:77.45pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
Assessment / Plan
  </td>
  <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
Orders/results: Meds, labs, imaging,
  referrals
  </td>
  <td width="61" valign="top" style="width:45.6pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
   Goals
  </td>
  <td width="69" valign="top" style="width:51.7pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
   Provider
  </td>

 </tr>
<tr style="mso-yfti-irow:1;height:18.75pt"><!-- First Row -->
  <td width="52" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
    
    <?php echo $result['diag_1']?>
  

  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
  
  
  
       <?php if($result['active_1'] == 1){echo "Active";}?> 
       <?php if($result['active_1'] == 2){echo "Inactive";}?>
 
  
  </td>
  <td width="189" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

 <?php echo $result['risk_1']; ?> 
  
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['assessment_1']; ?> 
  
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt"><br>
  <?php if(!empty($lab_id)){echo " View Results"; }else{echo " No labs";} ?>
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  
  <?php echo $result['goal_1'] ?> 
  
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

 
     <?php if($result['provider_1'] == 2){echo "Kim Dunn";}?>
	 <?php if($result['provider_1'] == 3){echo "Julie";}?>
  
 
  </td>

 </tr>
 
 <tr style="mso-yfti-irow:2;height:18.75pt"><!--Second Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  

  

  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    
       
       <?php if($result['active_2'] == 1){echo "Active";}?> 
       <?php if($result['active_2'] == 2){echo "Inactive";}?>
    
 
  </td>
  <td width="189" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['risk_2'] ?>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

 <?php echo $result['assessment_2'] ?>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
       &nbsp;
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['goal_2'] ?>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  
     <?php if($result['provider_2'] == 2){echo "Kim Dunn";}?>
	 <option value="3" <?php if($result['provider_2'] == 3){echo "Julie";}?>
  

  </td>

 </tr>


  <tr style="mso-yfti-irow:2;height:18.75pt"><!--Third Diagnosis Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
 <?php echo $result['diag_3'] ?>
  
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    
       
       <?php if($result['active_3'] == 1){echo "Active";}?> 
       <?php if($result['active_3'] == 2){echo "Inactive";}?>
    

  </td>
  <td width="189" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['risk_3'] ?>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['assessment_3'] ?>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
     &nbsp;
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['goal_3'] ?>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  
     <?php if($result['provider_3'] == 2){echo "Kim Dunn";}?>
	 <?php if($result['provider_3'] == 3){echo "Julie";}?>
  

  </td>

 </tr>
 

 
 <!-- auto Show row  4 -->
 

  <tr style="mso-yfti-irow:2;height:18.75pt"><!--Forth Diagnosis Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
 <?php echo $result['diag_4'] ?>
  

  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

   
       
        <?php if($result['active_4'] == 1){echo "Active";}?>
        <?php if($result['active_4'] == 2){echo "Inactive";}?>
  
  </td>
  <td width="189" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['risk_4'] ?>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['assessment_4'] ?>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
     &nbsp;
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['goal_4'] ?>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

 
    <?php if($result['provider_4'] == 2){echo "Kim Dunn";}?>
	 <?php if($result['provider_4'] == 3){echo "Julie";}?>
  </select>

  </td>

 </tr>
 

 
 <!-- auto show row 5 -->

  <tr style="mso-yfti-irow:2;height:18.75pt"><!--Fifth Diagnosis Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
  <?php echo $result['diag_5'] ?>
         
  

  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    
       
       <?php if($result['active_5'] == 1){echo "Active";}?> 
       <?php if($result['active_5'] == 2){echo "Inactive";}?>
   

  </td>
  <td width="189" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['risk_5'] ?>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

     <?php echo $result['assessment_5'] ?>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
     &nbsp; Delete this row
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php echo $result['goal_5'] ?>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  
     <?php if($result['provider_5'] == 2){echo "Kim Dunn";}?>
         <?php if($result['provider_5'] == 3){echo "Julie";}?>
  

  </td>

 </tr>
 
 
 <!-- Bottom three row -->
 <tr style="mso-yfti-irow:3;height:23.2pt"> 
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:23.2pt">
      Prevention
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:23.2pt">
      
     
      
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:23.2pt">
 &nbsp;
  <?php echo $result['prevention']; ?>
  </td>

 </tr>
 <!-- PM History  -->
  <tr style="mso-yfti-irow:4;height:19.75pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:19.75pt">
     PMH
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
      
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
    &nbsp;
  <?php echo $result['pmh']; ?>
  </td>

 </tr>
 <tr style="mso-yfti-irow:4;height:19.75pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:19.75pt">
     PSH
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
      
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
    &nbsp;
  <?php echo $result['psh']; ?>
  </td>

 </tr>
  <!-- FH -->
 <tr style="mso-yfti-irow:5;mso-yfti-lastrow:yes;height:24.95pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:24.95pt">
     FH
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
     &nbsp;
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
    
           <?php echo $result['fhsh']; ?>
  </td>

 </tr>
 <!-- secton -->
 <tr style="mso-yfti-irow:5;mso-yfti-lastrow:yes;height:24.95pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:24.95pt">
     SH
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
     &nbsp;
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
   
  <?php echo $result['sh']; ?>

  </td>

 </tr>
</tbody></table>
</div>
 <div id="non-printable">
    <input type="button" onclick="printDiv('printableArea')" value="Print">
 </div>

</center>

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