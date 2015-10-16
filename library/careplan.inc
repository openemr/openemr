<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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
 
 
//====================
// Retrieve the name of the code selected
//====================
 
   function codeNameTxt($code){
     
        $txt = explode(":", $code);
       $sql = "SELECT short_desc FROM `icd9_dx_code` WHERE `formatted_dx_code` LIKE '$txt[1]'";
       $stxt = sqlStatement($sql);
       $gtxt = sqlFetchArray($stxt);
       $display = $gtxt['short_desc'];
       
       return $display;
 }
 
 /*
  * ==========================
  * pull the medications based on the icd9 codes used
  * ==========================
  */
 
 