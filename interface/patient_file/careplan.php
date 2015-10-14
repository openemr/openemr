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
 // @author Sherwin Gaddis
 
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
 $user = $_SESSION['authUser'];
 
     //Pull list of physician from the users table
		function genProviderSelect($selname, $toptext, $default=0, $disabled=false) {
		  $query = "SELECT id, lname, fname FROM users WHERE " .
			"( authorized = 1 OR info LIKE '%provider%' ) AND username != '' " .
			"AND active = 1 AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
			"ORDER BY lname, fname";
		  $res = sqlStatement($query);
		  echo "   <select name='" . attr($selname) . "'";
		  if ($disabled) echo " disabled";
		  echo ">\n";
		  echo "    <option value=''>" . text($toptext) . "\n";
		  while ($row = sqlFetchArray($res)) {
			$provid = $row['id'];
			echo "    <option value='" . attr($provid) . "'";
			if ($provid == $default) echo " selected";
			echo ">" . text($row['lname'] . ", " . $row['fname']) . "\n";
		  }
		  echo "   </select>\n";
		}
 //========================
 // Populate from old previous to next care plan
 //
 //========================
 
 function getLastResults($encounter, $pid, $date){
     
     //Fetch info from previous encounter
     $query = sqlStatement("SELECT * FROM care_plan WHERE pid = $pid ORDER BY id DESC LIMIT 1");
     $res = sqlFetchArray($query);
     
    $diagnosis1  = $res['diag_1'];
    $active1     = $res['active_1'];
    $riskfactor1 = $res['risk_1'];
    $assessplan1 = $res['assessment_1'];
    $goals1      = $res['goal_1'];
    $provider1   = $res['provider_1'];
    
    $diagnosis2  = $res['diag_2'];
    $active2     = $res['active_2'];
    $riskfactor2 = $res['risk_2'];
    $assessplan2 = $res['assessment_2'];
    $goals2      = $res['goal_2'];
    $provider2   = $res['provider_2'];
    
    $diagnosis3  = $res['diag_3'];
    $active3     = $res['active_3'];
    $riskfactor3 = $res['risk_3'];
    $assessplan3 = $res['assessment_3'];
    $goals3      = $res['goal_3'];
    $provider3   = $res['provider_3'];
	
    $diagnosis4  = $res['diag_4'];
    $active4     = $res['active_4'];
    $riskfactor4 = $res['risk_4'];
    $assessplan4 = $res['assessment_4'];
    $goals4      = $res['goal_4'];
    $provider4   = $res['provider_4'];
	
    $diagnosis5  = $res['diag_5'];
    $active5     = $res['active_5'];
    $riskfactor5 = $res['risk_5'];
    $assessplan5 = $res['assessment_5'];
    $goals5      = $res['goal_5'];
    $provider5   = $res['provider_5'];
            
    $prevention  = $res['prevention'];
    $pmh         = $res['pmh'];
    $psh         = $res['psh'];
    $fhsh        = $res['fhsh'];
    $sh          = $res['sh'];
    $audit       = "";
    
    $sql = "INSERT INTO  care_plan SET " .
                                   " pid = '". $pid .
                           "', encounter = '". $encounter .
                                "', date = '". $date .
                              "', diag_1 = '". $diagnosis1 .
                             "', diag_2 = '" . $diagnosis2 .
                             "', diag_3 = '" . $diagnosis3 .
                             "', diag_4 = '" . $diagnosis4 .
                             "', diag_5 = '" . $diagnosis5 .							 
                           "', active_1 = '" . $active1 . 
                           "', active_2 = '" . $active2 . 
                           "', active_3 = '" . $active3 .
                           "', active_4 = '" . $active4 .
                           "', active_5 = '" . $active5 .
                             "', risk_1 = '" . $riskfactor1 . 
                             "', risk_2 = '" . $riskfactor2 . 
                             "', risk_3 = '" . $riskfactor3 .
                             "', risk_4 = '" . $riskfactor4 .
                             "', risk_5 = '" . $riskfactor5 .
                       "', assessment_1 = '" . $assessplan1 . 
                       "', assessment_2 = '" . $assessplan2 .
                       "', assessment_3 = '" . $assessplan3 .
                       "', assessment_4 = '" . $assessplan4 .
                       "', assessment_5 = '" . $assessplan5 .
                             "', goal_1 = '" . $goals1 . 
                             "', goal_2 = '" . $goals2 .
                             "', goal_3 = '" . $goals3 .
                             "', goal_4 = '" . $goals4 .
                             "', goal_5 = '" . $goals5 .
                         "', provider_1 = '" . $provider1.
                         "', provider_2 = '" . $provider2.
                         "', provider_3 = '" . $provider3.
                         "', provider_4 = '" . $provider4.
                         "', provider_5 = '" . $provider5.
                         "', prevention = '" . $prevention .
                                "', pmh = '" . $pmh .
                                "', psh = '" . $psh .
                               "', fhsh = '" . $fhsh .
                                 "', sh = '" . $sh .
                              "', audit = '" . $audit . "'";
     sqlStatement($sql);
 }
 
 
 //Trigger to copy previous reselts into new encounter
 $sql = "SELECT encounter FROM care_plan WHERE encounter = $encounter ";
 $prior = sqlStatement($sql);
 $prior_res = sqlFetchArray($prior);
 if(empty($prior_res['encounter'])){
     echo "New Care Plan";
     getLastResults($encounter, $pid, $date);
 }
 
 //========================
 // Information gathering
 //========================
     //data to populate form on load
    $query = sqlStatement("SELECT * FROM care_plan WHERE pid = $pid AND encounter = $encounter ");
    $result = sqlFetchArray($query);
    
    //return all patient demographic data
    $getData = getPatientData($pid);
    //var_dump($getData);
    //var_dump($result); //troubleshooting
    
    //displays the patient DOB
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
	 
   //Patient history data
    $his = sqlStatement("SELECT * FROM history_data WHERE pid = $pid ORDER BY id DESC LIMIT 1");
    $his_array = sqlFetchArray($his);
    //var_dump($his_array);
    
    $pe = sqlStatement("SELECT form_id FROM forms WHERE encounter = $encounter AND pid = $pid AND form_name LIKE 'Physical Exam'");
    $pe_id = sqlFetchArray($pe);
    $peid = $pe_id['form_id'];
	
	$ros = sqlStatement("SELECT form_id FROM forms WHERE encounter = $encounter AND pid = $pid AND form_name LIKE 'Review of Systems'");
	$ros_id = sqlFetchArray($ros);
	$rosid = $ros_id['form_id'];
	
	
?>
<!DOCTYPE html>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
	<script type="text/javascript" src="../../library/js/fancybox.2.1.5/lib/jquery-1.10.1.min.js"></script>

	<!-- Add mousewheel plugin (this is optional) -->
	<script type="text/javascript" src="../../library/js/fancybox.2.1.5/lib/jquery.mousewheel-3.0.6.pack.js"></script>

	<!-- Add fancyBox main JS and CSS files -->
	<script type="text/javascript" src="../../library/js/fancybox.2.1.5/source/jquery.fancybox.js?v=2.1.5"></script>
	<link rel="stylesheet" type="text/css" href="../../library/js/fancybox.2.1.5/source/jquery.fancybox.css?v=2.1.5" media="screen" />

<style>
input[type=radio]
{
font-size: xx-small;
}
input[type=text]
{
  width: 85px;
}

button.nav {
display:inline-block;
/*min-width: 50px;*/
width: 140px;
/*padding:5px 10px;*/

}

</style>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

 // Process click on Delete link.
 function deleteme1() {
  dlgopen('../patient_file/careplan_deleter.php?row=1&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter;?>', '_blank', 500, 450);
  return false;
 }
 
  function deleteme2() {
  dlgopen('../patient_file/careplan_deleter.php?row=2&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter;?>', '_blank', 500, 450);
  return false;
 }
 
  function deleteme3() {
  dlgopen('../patient_file/careplan_deleter.php?row=3&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter;?>', '_blank', 500, 450);
  return false;
 }
 
  function deleteme4() {
  dlgopen('../patient_file/careplan_deleter.php?row=4&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter;?>', '_blank', 500, 450);
  return false;
 }
 
  function deleteme5() {
  dlgopen('../patient_file/careplan_deleter.php?row=5&pid=<?php echo $pid; ?>&encounter=<?php echo $encounter;?>', '_blank', 500, 450);
  return false;
 }
 
 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  closeme();
 }

 function closeme() {
    if (parent.$) parent.$.fancybox.close();
    window.close();
	
 }

// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0][current_sel_name];
 var s = f.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.value = s;
}



//This invokes the find-code popup.
function sel_diagnosis(e) {
 current_sel_name = e.name;
 dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("diagnosis","csv"); ?>', '_blank', 500, 400);
}

//This invokes the find-code popup.
function sel_procedure(e) {
 current_sel_name = e.name;
 dlgopen('../patient_file/encounter/find_code_popup.php?codetype=<?php echo collect_codetypes("procedure","csv"); ?>', '_blank', 500, 400);
}

function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        $("#"+div).hide();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        $("#"+div).show();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }
} 
function refresh() {
    location.reload();
	
}

		$(document).ready(function() {
			/*
			 *  Simple image gallery. Uses default settings
			 */

			$('.fancybox').fancybox();

			/*
			 *  Different effects
			 */

			// Change title type, overlay closing speed
			$(".fancybox-effects-a").fancybox({
				helpers: {
					title : {
						type : 'outside'
					},
					overlay : {
						speedOut : 0
					}
				}
			});

			// Disable opening and closing animations, change title type
			$(".fancybox-effects-b").fancybox({
				openEffect  : 'none',
				closeEffect	: 'none',

				helpers : {
					title : {
						type : 'over'
					}
				}
			});

			// Set custom style, close if clicked, change title type and overlay color
			$(".fancybox-effects-c").fancybox({
				wrapCSS    : 'fancybox-custom',
				closeClick : true,

				openEffect : 'none',

				helpers : {
					title : {
						type : 'inside'
					},
					overlay : {
						css : {
							'background' : 'rgba(238,238,238,0.85)'
						}
					}
				}
			});

			// Remove padding, set opening and closing animations, close if clicked and disable overlay
			$(".fancybox-effects-d").fancybox({
				padding: 0,

				openEffect : 'elastic',
				openSpeed  : 150,

				closeEffect : 'elastic',
				closeSpeed  : 150,

				closeClick : true,

				helpers : {
					overlay : null
				}
			});

			/*
			 *  Button helper. Disable animations, hide close button, change title type and content
			 */

			$('.fancybox-buttons').fancybox({
				openEffect  : 'none',
				closeEffect : 'none',

				prevEffect : 'none',
				nextEffect : 'none',

				closeBtn  : false,

				helpers : {
					title : {
						type : 'inside'
					},
					buttons	: {}
				},

				afterLoad : function() {
					this.title = 'Image ' + (this.index + 1) + ' of ' + this.group.length + (this.title ? ' - ' + this.title : '');
				}
				
			});
			
			$(".fancybox").fancybox({
			  type: 'iframe',
			  afterClose: function () {
			      //parent.location.reload(true);
				  window.location.reload()
			  }
            }); 
		});

</script>
</head>

<body class="body_top">
<div style="text-align:center" class="buttons">
     <a href='../patient_file/summary/demographics.php' class='css_button' id='back'><span><?php echo htmlspecialchars( xl('Back'), ENT_NOQUOTES); ?></span></a>
</div>
  <br><br>last edited by:<?php echo " " . $ledit['user']; ?>
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
            <button class="nav"><a class="fancybox fancybox.iframe" href="../patient_file/summary/rx_noframeset.php" onclick='top.restoreSession()'>Prescription</a></button><br>
         
            <button class="nav"><a class="fancybox fancybox.iframe" href="../patient_file/encounter/load_form.php?formname=LBFlabs<?php
             if(!empty($lab_id)){ echo "&id=".$lab_id['form_id'];} ?>"  onclick='top.restoreSession()'> Lab Order Form</a></button><br>
         
         <button class="nav"><a class="fancybox fancybox.iframe" href="../patient_file/summary/stats_full.php?active=all&category=medication" onclick='top.restoreSession()'>Medications</a></button><br>
		 
    <?php if(empty($rosid)){
          echo "<button class='nav'><a class='fancybox fancybox.iframe' href='../patient_file/encounter/load_form.php?formname=ros' onclick='top.restoreSession()'>Review of Systems</a></button>";
		 }else {
		   echo "<button class='nav'><a class='fancybox fancybox.iframe' href='../patient_file/encounter/view_form.php?formname=ros&id=$rosid' onclick='top.restoreSession()'>Review of Systems</a></button>";
		 }
     ?>
   <?php 
        if(empty($peid)){
            echo "<button class='nav'><a class='fancybox fancybox.iframe' href='../patient_file/encounter/load_form.php?formname=LBFPHEX' onclick='top.restoreSession()'>Physical Exam</a></button>";
        }else{
            echo "<button class='nav'><a class='fancybox fancybox.iframe' href='../patient_file/encounter/view_form.php?formname=LBFPHEX&id=".$peid.">' onclick='top.restoreSession()'>Physical Exam</a></button>";
        } 

        
	?>
         
         <button class="nav"><a class="fancybox fancybox.iframe" href="../patient_file/transaction/add_transaction.php" onclick='top.restoreSession()'>Referal</a></button><br>
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
            <!-- <font color="blue">Medications:</font> --> <?php 
               //while($rows=sqlFetchArray($meds)){
                  // echo $rows['title'] . ", ";
               //}
         ?>
        </td>
    </tr>
    </table>
</center>

<p> </p>
<center>
<form action="careplan_save.php" method="post" id="careplan" name="careplan">
    <input type="submit" value="Save" title="Always save before leaving file or your work could be edited by someone other than you." >
    <input type="hidden" name="pid" value="<?php echo $pid; ?>">
    <input type="hidden" name="encounter" value="<?php echo $encounter;  ?>">
    <input type="hidden" name="date" value="<?php echo $date; ?>">
    <input type="hidden" name="audit" value="1">
    <input type="hidden" name="user" value="<?php echo $user; ?>">
    
<table class="TableGrid1" border="1" cellspacing="0" cellpadding="0" style="margin-left:
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
  <td width="89" valign="top" style="width:66.7pt;border:solid windowtext 1.0pt;
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
Medication
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
  <td width="69" valign="top" style="width:51.45pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">

Delete
  </td>
 </tr>
<tr style="mso-yfti-irow:1;height:18.75pt"><!-- First Row -->
  <td width="52" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
    
    <input type="text" name="diagnosis_1" id="codesearch" value="<?php echo $result['diag_1']?>"
         onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly />
    <?php
       $txt = explode(":", $result['diag_1']);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       echo "<font size='2'>".$gtxt['short_desc']."</font>";
    ?>
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
  
  
  <select name="active_1">
      <option value = '1' >Active</option> 
      <option value = '2' <?php if($result['active_1'] == 2){echo "selected";} ?>>Inactive</option>
  </select>
  
  </td>
  <td width="89" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="risk_factor_1">
    <option value=""> </option>
        <option value="compliant" <?php if($result['risk_1'] == 'compliant'){echo "selected";} ?>>Compliant</option>
	<option value="Non-Compliant" <?php if($result['risk_1'] == 'Non-Compliant'){echo "selected";} ?>>Non-Compliant</option> 
	<option value="Other" <?php if($result['risk_1'] == 'Other'){echo "selected";} ?>>Other</option>   
  </select>

  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="assess_plan_1" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      
      <?php 
      echo $result['assessment_1'] . "\n";
      if(!empty($result['assessment_1']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 

  
  </textarea>
  
  </td>
    <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
  <?php  //Get medications
  $diag_med = $result['diag_1'];
   if(!empty($result['diag_1'] ) || $result['diag_1'] == " "){
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid AND `diagnosis` = '$diag_med' "); 
    while($mrows = sqlFetchArray($meds)){
        echo $mrows['title'].", ";
     }
   }else
       {
          echo "<font size=1em>No Med Assigned</font>"; 
       
       }
    ?>
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

  
  <textarea name="goals_1" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      <?php 
        echo $result['goal_1'] . "\n";
      if(!empty($result['goal_1']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 
  </textarea>
  
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php 
  
     genProviderSelect('provider_1', '-- '.xl("Please Select").' --', $default = $result['provider_1']);
			?>  
 
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
      <p id="row1"><p>
	  <input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick="deleteme1()" />
 
  </td>
 </tr>
 
 <tr style="mso-yfti-irow:2;height:18.75pt"><!--Second Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
  <input type="text" name="diagnosis_2" id="codesearch" value="<?php echo $result['diag_2']; ?>"
         onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly />
    <?php
       $txt = explode(":", $result['diag_2']);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       echo "<font size='2'>".$gtxt['short_desc']."</font>";
    ?>
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="active_2">
      <option value = '1' >Active</option> 
      <option value = '2' <?php if($result['active_2'] == 2){echo "selected";} ?>>Inactive</option>
  </select>  
 
  </td>
  <td width="89" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="risk_factor_2">
    <option value=""> </option>
	<option value="compliant" <?php if($result['risk_2'] == 'compliant'){echo "selected";} ?>>Compliant</option>
	<option value="Non-Compliant" <?php if($result['risk_2'] == 'Non-Compliant'){echo "selected";} ?>>Non-Compliant</option>
	<option value="Other" <?php if($result['risk_2'] == 'Other'){echo "selected";} ?>>Other</option>   
  </select>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="assess_plan_2" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      
     <?php 
         echo $result['assessment_2'] . "\n";
      if(!empty($result['assessment_2']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?>

  </textarea>
  </td>
    <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
  <?php  //Get medications
  $diag_med = $result['diag_2'];
   if(isset($result['diag_2']) ){
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid AND `diagnosis` = '$diag_med' "); 
    while($mrows = sqlFetchArray($meds)){
        echo $mrows['title'].", ";
     }
   }else
       {
          echo "<font size=1em>No Med Assigned</font>"; 
       
       }
    ?>
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

  <textarea name="goals_2" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      <?php 
        echo $result['goal_2'] . "\n";
      if(!empty($result['goal_2']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php
        genProviderSelect('provider_2', '-- '.xl("Please Select").' --', $default = $result['provider_2']);   
			?> 

  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">


  <input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme2()' />

  </td>
 </tr>
 <?php 
     //show next diagnosis row automatically if the first two are filled
 if(!empty($result['diag_2']) && !empty($result['diag_1'])){
     
     ?>
  <tr style="mso-yfti-irow:2;height:18.75pt"><!--Third Diagnosis Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
  <input type="text" name="diagnosis_3"  value="<?php echo $result['diag_3'] ?>"
         onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly />
    <?php
       $txt = explode(":", $result['diag_3']);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       echo "<font size='2'>".$gtxt['short_desc']."</font>";
    ?>  
   </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="active_3">
      <option value = '1' >Active</option> 
      <option value = '2' <?php if($result['active_3'] == 2){echo "selected";} ?>>Inactive</option>
  </select>  

  </td>
  <td width="89" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="risk_factor_3">
    <option value=""> </option>
	<option value="compliant" <?php if($result['risk_3'] == 'compliant'){echo "selected";} ?>>Compliant</option>
	<option value="Non-Compliant" <?php if($result['risk_3'] == 'Non-Compliant'){echo "selected";} ?>>Non-Compliant</option>
	<option value="Other" <?php if($result['risk_3'] == 'Other'){echo "selected";} ?>>Other</option>   
  </select>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="assess_plan_3" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
    <?php 
         echo $result['assessment_3'] . "\n";
      if(!empty($result['assessment_3']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?>
  
  </textarea>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">
  <?php  //Get medications
  $diag_med = $result['diag_3'];
   if(!empty($result['diag_3']) || $result['diag_3'] != " "){
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid AND `diagnosis` = '$diag_med' "); 
    while($mrows = sqlFetchArray($meds)){
        echo $mrows['title'].", ";
     }
   }else
       {
          echo "<font size=1em>No Med Assigned</font>"; 
       
       }
    ?>
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

  <textarea name="goals_3" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      <?php 
        echo $result['goal_3'] . "\n";
      if(!empty($result['goal_3']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php
    genProviderSelect('provider_3', '-- '.xl("Please Select").' --', $default = $result['provider_3']);
			?> 

  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
    
	<input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme3()' />
	
  </td>
 </tr>
 
 <?php } ?>
 
 <!-- auto Show row  4 -->
 
  <?php 
     //show next diagnosis box automatically if the first two are filled
 if(!empty($result['diag_2']) && !empty($result['diag_3'])){
     
     ?>
  <tr style="mso-yfti-irow:2;height:18.75pt"><!--Third Diagnosis Row -->
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
  <input type="text" name="diagnosis_4"  value="<?php echo $result['diag_4'] ?>"
         onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly />
    <?php
       $txt = explode(":", $result['diag_4']);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       echo "<font size='2'>".$gtxt['short_desc']."</font>";
    ?>  
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="active_4">
      <option value = '1'>Active</option> 
      <option value = '2' <?php if($result['active_4'] == 2){echo "selected";} ?>>Inactive</option> 
  </select>  

  </td>
  <td width="89" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <select name="risk_factor_4">
    <option value=""> </option>
	<option value="compliant" <?php if($result['risk_4'] == 'compliant'){echo "selected";} ?>>Compliant</option>
	<option value="Non-Compliant" <?php if($result['risk_4'] == 'Non-Compliant'){echo "selected";} ?>>Non-Compliant</option>
	<option value="Other" <?php if($result['risk_4'] == 'Other'){echo "selected";} ?>>Other</option>   
  </select>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="assess_plan_4" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
    <?php 
         echo $result['assessment_4'] . "\n";
      if(!empty($result['assessment_4']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?>
  </textarea>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">

  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
  <?php  //Get medications
  $diag_med = $result['diag_4'];
   if(!empty($result['diag_4']) || $result['diag_4'] != " "){
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid AND `diagnosis` = '$diag_med' "); 
    while($mrows = sqlFetchArray($meds)){
        echo $mrows['title'].", ";
     }
   }else
       {
          echo "<font size=1em>No Med Assigned</font>"; 
       
       }
    ?>
  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="goals_4" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
    <?php 
        echo $result['goal_4'] . "\n";
      if(!empty($result['goal_4']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php
    genProviderSelect('provider_4', '-- '.xl("Please Select").' --', $default = $result['provider_4']);
    ?> 

  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme4()' />
  
  </td>
 </tr>
 
 <?php } ?>
 
 <!-- auto show row 5 -->
 
  <?php 
     //show next diagnosis box automatically if the first two are filled
 if(!empty($result['diag_3']) && !empty($result['diag_4'])){
     
     ?>
  <tr style="mso-yfti-irow:2;height:18.75pt">
  <td width="102" valign="top" style="width:76.7pt;border:solid windowtext 1.0pt;
  border-top:none;mso-border-top-alt:solid windowtext .5pt;mso-border-alt:solid windowtext .5pt;
  height:18.75pt">
  
  <input type="text" name="diagnosis_5"  value="<?php echo $result['diag_5'] ?>"
         onclick='sel_diagnosis(this)' title='<?php echo htmlspecialchars(xl('Click to select or change diagnoses'),ENT_QUOTES); ?>' readonly />
    <?php
       $txt = explode(":", $result['diag_5']);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       echo "<font size='2'>".$gtxt['short_desc']."</font>";
    ?>
  </td>
  <td width="68" valign="top" style="width:50.9pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

    <select name="active_5">
      <option value = '1' >Active</option> 
      <option value = '2' <?php if($result['active_5'] == 2){echo "selected";} ?>>Inactive</option> 
  </select>  

  </td>
  <td width="89" valign="top" style="width:66.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <select name="risk_factor_5">
    <option value=""> </option>
	<option value="compliant" <?php if($result['risk_5'] == 'compliant'){echo "selected";} ?>>Compliant</option>
	<option value="Non-Compliant" <?php if($result['risk_5'] == 'Non-Compliant'){echo "selected";} ?>>Non-Compliant</option> 
	<option value="Other" <?php if($result['risk_5'] == 'Other'){echo "selected";} ?>>Other</option>   
  </select>
  </td>
  <td width="103" valign="top" style="width:77.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="assess_plan_5" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      <?php 
         echo $result['assessment_5'] . "\n";
      if(!empty($result['assessment_5']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?>
  </textarea>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">
  <?php  //Get medications
  $diag_med = $result['diag_5'];
   if(!empty($result['diag_5']) || $result['diag_5'] != ""){
    $meds = sqlStatement("SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = $pid AND `diagnosis` = '$diag_med' "); 
    while($mrows = sqlFetchArray($meds)){
        echo $mrows['title'].", ";
     }
   }else
       {
          echo "<font size=1em>No Med Assigned</font>"; 
       
       }
    ?>
  </td>
  <td width="105" valign="top" style="width:78.95pt;border:solid windowtext 1.0pt;
  border-left:none;mso-border-left-alt:solid windowtext .5pt;mso-border-alt:
  solid windowtext .5pt;height:41.6pt">

  </td>
  <td width="61" valign="top" style="width:45.6pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <textarea name="goals_5" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
      <?php 
        echo $result['goal_5'] . "\n";
      if(!empty($result['goal_5']) && $result['audit'] == 0){
          
         echo $date . "--";
      }
      ?> 
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.7pt;border-top:none;border-left:none;
  border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

  <?php
  
    genProviderSelect('provider_5', '-- '.xl("Please Select").' --', $default = $result['provider_5']);
     
  ?> 

  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:18.75pt">

     <input type='button' value='<?php echo xla('Delete'); ?>' style='color:red' onclick='deleteme5()' />
	 
  </td>
 </tr>
 
 <?php } ?>
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
      
      <button><a href="http://epss.ahrq.gov/ePSS/search.jsp" target="_blank">Access</a></button>
      
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:23.2pt">
 &nbsp;
  <textarea  name='prevention' cols="90" <?php if($result['audit'] == 1){echo "readonly"; } ?> >
      <?php 
      if(!empty($result['prevention'])){
         echo $result['prevention'] . "\n"; 
         echo $date . "--";
      }
      ?> 
  
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:23.2pt">

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
     &nbsp;      
      <button><a class="fancybox fancybox.iframe" href="../patient_file/history/history.php">Edit</a></button>
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
      &nbsp;
  <textarea name='pmh' cols="90" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
<?php
  echo "\n";
  echo " -----\n";
   $his_date = explode( " ", $his_array['date']);
   $newInfo = $his_date[0];
   if(strtotime($date) == strtotime($newInfo)){
      echo " EXAM/TESTS: \n";
       $data = explode("|", $his_array['exams']);
       foreach($data as $v){
               $item = explode(":", $v);
           if($item[1] != 0){
               $name = $item[0];
               $sql = "SELECT title FROM list_options  WHERE list_id LIKE 'exams' AND option_id LIKE '$name' ";
               $lna = sqlStatement($sql);
               $lnar = sqlFetchArray($lna);
           echo $lnar['title'] ." "; if($item[1] == 2){echo "- Abnormal ";}else{echo "- Normal ";} echo "\n";
           
           }
           
       }
      echo "\n Risk Factors: \n";
      $ndata = explode("|", $his_array['usertext11']);
        foreach($ndata as $v){
            //echo $v;
            $sql = "SELECT title FROM list_options WHERE list_id LIKE 'riskfactors' AND option_id LIKE '$v'";
            $rfn = sqlStatement($sql);
            $rfres = sqlFetchArray($rfn);
            echo $rfres['title'] . "\n";
        }  
      
   }else {
       //Previously saved information is displayed if the history has not been updated.
       echo $result['pmh']; 
   }

?>
  
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">

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
     &nbsp;      
	  <button><a class="fancybox fancybox.iframe" href="../patient_file/summary/stats_full.php?active=all&category=surgery">Edit</a></button>
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">
    &nbsp;
    <textarea name='psh' cols="90" readonly<?php ///if($result['audit'] == 1){echo "readonly"; } ?>><?php //echo $result['psh']; ?>
  <?php 
         //Always populate from table
     $s_res = sqlStatement("SELECT title, destination FROM lists WHERE type LIKE 'surgery' and pid = $pid ");
     while($rows =  sqlFetchArray($s_res)){
         echo $rows['title'] . " - " . $rows['destination'];
     }
  ?>  
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:19.75pt">

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
	  <button><a class="fancybox fancybox.iframe" href="../patient_file/history/history.php">Edit</a></button>
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
    &nbsp;
  <textarea name='fhsh' cols="90" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
<?php

  echo "\n";
  echo "-----\n";
   $his_date = explode( " ", $his_array['date']);
   $newInfo = $his_date[0];
   if(strtotime($date) == strtotime($newInfo)){

        if(!empty($his_array['history_mother'])){
         echo   "Mother: " . $his_array['history_mother'] . " " .
                             $his_array['dc_mother'] . "\n";
        }

        if(!empty($his_array['history_father'])){
         echo   "Father: " . $his_array['history_father'] . " " .
                             $his_array['dc_father'] . "\n";
        }

        if(!empty($his_array['history_siblings'])){
         echo   "Siblings: " . $his_array['history_siblings'] . " " .
                             $his_array['dc_siblings'] . "\n";
        }

        if(!empty($his_array['history_offspring'])){
         echo   "Offspring: " . $his_array['history_offspring'] . " " .
                             $his_array['dc_offspring'] . "\n";
        }

        if(!empty($his_array['history_spouse'])){
         echo   "Spouse: " . $his_array['history_spouse'] . " " .
                             $his_array['dc_spouse'] . "\n \n";
        }

        echo "\n";

        if(!empty($his_array['relatives_cancer'])){
         echo "Cancer: " . $his_array['relatives_cancer'] . "\n" ;
       
        }

         if(!empty($his_array['relatives_diabetes'])){
         echo "Diabetes: " . $his_array['relatives_diabetes'] . "\n" ;
       
        }
 
         if(!empty($his_array['relatives_diabetes'])){
         echo "Heart Problems: " . $his_array['relatives_heart_problems'] . "\n" ;
       
        }

        if(!empty($his_array['relatives_epilepsy'])){
         echo "Epilepsy: " . $his_array['relatives_epilepsy'] . "\n" ;
       
        }

        if(!empty($his_array['relatives_suicide'])){
         echo "Suicide: " . $his_array['relatives_suicide'] . "\n" ;
       
        }


        if(!empty($his_array['relatives_tuberculosis'])){
         echo "Tuberculosis: " . $his_array['relatives_tuberculosis'] . "\n" ;
       
        }


        if(!empty($his_array['relatives_high_blood_pressure'])){
         echo "Hypertention: " . $his_array['relatives_high_blood_pressure'] . "\n" ;
       
        }

        if(!empty($his_array['relatives_stoke'])){
         echo "Stroke: " . $his_array['relatives_stroke'] . "\n" ;
       
        }

        if(!empty($his_array['relatives_mental_illness'])){
         echo "Mental Illiness: " . $his_array['relatives_mental_illness'] . "\n" ;
       
        }
          
   }else {
       //Previously saved information is displayed if the history has not been updated.
       echo $result['sh']; 
   }


?>
  
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
   &nbsp;
  </td>
 </tr>
 <!-- section -->
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
	  <button><a class="fancybox fancybox.iframe" href="../patient_file/history/history.php">Edit</a></button>     
  </td>
  <td width="427" colspan="5" valign="top" style="width:320.55pt;border-top:none;
  border-left:none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
    &nbsp;
  <textarea name='sh' cols="90" <?php if($result['audit'] == 1){echo "readonly"; } ?>>
<?php

  echo "\n";
  echo "----- \n";
   $his_date = explode( " ", $his_array['date']);
   $newInfo = $his_date[0];
   if(strtotime($date) == strtotime($newInfo)){
       if($his_array['coffee'] != '|0|'){
       echo "Coffee: " . $his_array['coffee'] . "\n";
       }
       if($his_array['tobacco'] != '|0|'){
       echo "Tobacco: " . $his_array['tobacco'] . "\n";
       }
       if($his_array['alcohol'] != '|0|'){
       echo "Alcohol: " . $his_array['alcohol'] . "\n";
       }
       if(!empty($his_array['sleep_patterns'])){
       echo "Sleep Patterns: " . $his_array['sleep_patterns'] . "\n";
       }
       if($his_array['exercise_patterns'] != '|0|'){
       echo "Exercise: " . $his_array['exercise_patterns'] . "\n";
       }
       if(!empty($his_array['seatbelt_use'])){
       echo "Seatbelt Use: " . $his_array['seatbelt_use'] . "\n";
       }
       if($his_array['counseling'] != '|0|'){
       echo "Counseling: " . $his_array['counseling'] . "\n";
       }
       if($his_array['hazardous_activities'] != '|0|'){
       echo "Seatbelt Use: " . $his_array['hazardous_activities'] . "\n";
       }
       if($his_array['recreational_drugs'] != '|0|'){
       echo "Recreational Drug Use: " . $his_array['recreational_drugs'] . "\n";
       }
       
   }else {
       //Previously saved information is displayed if the history has not been updated.
       echo $result['sh']; 
   }
   

?>
  </textarea>
  </td>
  <td width="69" valign="top" style="width:51.45pt;border-top:none;border-left:
  none;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;
  mso-border-top-alt:solid windowtext .5pt;mso-border-left-alt:solid windowtext .5pt;
  mso-border-alt:solid windowtext .5pt;height:24.95pt">
   &nbsp;
  </td>
 </tr>
</tbody></table>
    <p></p>
    <input type="submit" value="Save" title="Always save before leaving file or your work could be edited by someone other than you." ><input type="submit" value="Edit" title="Click here to edit, your user ID will be recorded" >
</form>
    <button><a href="careplan_print.php">Print Preview</a></button>
</center>
</body>
</html>