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
 
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");

echo "save care plan";

if(!empty($_POST)){
    //transfer and filter post array   
    $pid         = filter_input(INPUT_POST, 'pid');
    $encounter   = filter_input(INPUT_POST, 'encounter');
    $date        = filter_input(INPUT_POST, 'date');
    $audit       = filter_input(INPUT_POST, 'audit');
    $user        = filter_input(INPUT_POST, 'user');
    
    $diagnosis1  = filter_input(INPUT_POST, 'diagnosis_1');
    $active1     = filter_input(INPUT_POST, 'active_1');
    $riskfactor1 = filter_input(INPUT_POST, 'risk_factor_1');
    $assessplan1 = filter_input(INPUT_POST, 'assess_plan_1');
    $goals1      = filter_input(INPUT_POST, 'goals_1');
    $provider1   = filter_input(INPUT_POST, 'provider_1');
    
    $diagnosis2  = filter_input(INPUT_POST, 'diagnosis_2');
    $active2     = filter_input(INPUT_POST, 'active_2');
    $riskfactor2 = filter_input(INPUT_POST, 'risk_factor_2');
    $assessplan2 = filter_input(INPUT_POST, 'assess_plan_2');
    $goals2      = filter_input(INPUT_POST, 'goals_2');
    $provider2   = filter_input(INPUT_POST, 'provider_2');
    
    $diagnosis3  = filter_input(INPUT_POST, 'diagnosis_3');
    $active3     = filter_input(INPUT_POST, 'active_3');
    $riskfactor3 = filter_input(INPUT_POST, 'risk_factor_3');
    $assessplan3 = filter_input(INPUT_POST, 'assess_plan_3');
    $goals3      = filter_input(INPUT_POST, 'goals_3');
    $provider3   = filter_input(INPUT_POST, 'provider_3');
	
    $diagnosis4  = filter_input(INPUT_POST, 'diagnosis_4');
    $active4     = filter_input(INPUT_POST, 'active_4');
    $riskfactor4 = filter_input(INPUT_POST, 'risk_factor_4');
    $assessplan4 = filter_input(INPUT_POST, 'assess_plan_4');
    $goals4      = filter_input(INPUT_POST, 'goals_4');
    $provider4   = filter_input(INPUT_POST, 'provider_4');
	
    $diagnosis5  = filter_input(INPUT_POST, 'diagnosis_5');
    $active5     = filter_input(INPUT_POST, 'active_5');
    $riskfactor5 = filter_input(INPUT_POST, 'risk_factor_5');
    $assessplan5 = filter_input(INPUT_POST, 'assess_plan_5');
    $goals5      = filter_input(INPUT_POST, 'goals_5');
    $provider5   = filter_input(INPUT_POST, 'provider_5');
            
    $prevention  = filter_input(INPUT_POST, 'prevention');
    $pmh         = filter_input(INPUT_POST, 'pmh');
    $psh         = filter_input(INPUT_POST, 'psh');
    $fhsh        = filter_input(INPUT_POST, 'fhsh');
    $sh          = filter_input(INPUT_POST, 'sh');
	
	//var_dump($_POST);
	//exit;
}

// ====================================
//check the audit bit to see which way it is set.
// ====================================


    //see if there is an entry for patient and decide to update or insert
    
    $tq = sqlStatement("SELECT pid FROM care_plan WHERE pid = $pid AND encounter = $encounter" );
    $result =  sqlFetchArray($tq);
    echo " the result is -> " . $result['pid']; //
    
    if($result['pid'] != $pid){
                    sqlStatement("INSERT INTO  care_plan SET " .
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
                              "', audit = '" . $audit . "'" );
                    
    }else{
        
      $abit = sqlStatement("SELECT audit FROM care_plan WHERE pid = '$pid' AND encounter = '$encounter' ");
      $res = sqlFetchArray($abit);
      
      if($res['audit'] == 1){ 
          //reset the audit bit to zero
             $audit = 0; 
             //record who edited the record. 
             sqlStatement("INSERT INTO care_plan_audit SET " .
                                " user = '" . $user .
                             "',  date = '" . $date .
                             "',  pid  = '" . $pid .
                         "', encounter = '" . $encounter .
                     
                                   "' " );
             
      }
      
          sqlStatement("UPDATE care_plan SET " .
                                             
                                            " diag_1 = '" . $diagnosis1 . 
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
                                      "', provider_1 = '" . $provider1 .
                                      "', provider_2 = '" . $provider2 .
                                      "', provider_3 = '" . $provider3 .
                                      "', provider_4 = '" . $provider4 .
                                      "', provider_5 = '" . $provider5 .
                                      "', prevention = '" . $prevention .
                                             "', pmh = '" . $pmh .
                                             "', psh = '" . $psh .
                                            "', fhsh = '" . $fhsh .
                                              "', sh = '" . $sh .
                                           "', audit = '" . $audit .
                                    "' WHERE pid = '"     . $pid . "' AND encounter = '"  . $encounter . "' " );
          echo "<br> updated data";
    }

    

    
    
   header('Location: ' . $_SERVER['HTTP_REFERER']);


