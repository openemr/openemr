<?php
//------------Forms generated from formsWiz
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
function assessment_form_report($pid, $encounter, $cols, $id) {
 $cols = 2; // force always 1 column
 $count = 0;
 $data = formFetch("form_assessment_form", $id);
 
 $finalOutPut = '';
 $in_clinic_test_value = [];
 if ($data) {
  $finalOutPut .= "<table class='show_print' style='word-wrap: break-word;word-break: break-all;border-spacing: 0px;border-top:1px solid #303030; border-left:1px solid #303030;border-right:1px solid #303030;width:1000px;'><tr>";
  $vitalTable = '<table style="word-wrap: break-word;border-spacing: 0px;border-top:1px solid #303030; border-left:1px solid #303030; border-right:1px solid #303030;width:100%;font-size:12px;"><tr><th>Weight(lb)</th><th>Height(In)</th><th>Temp(*F)</th><th>BP</th><th>Pulse</th><th>RR</th><th>BMI</th><th>Sat(%)</th><th>On02</th></tr>';
  $in_clinic_test_head = Array( "Urinalysis Test", "Nitrites", "Protein", "Blood", "Ketones", "Glucose", "Specific Gravity", "pH", "Leukocyte", "Urobilirubin", "Bilirubin", "Flu Test", "Pregnancy Test", "Strep Test", "Finger Stick", "Comments");  
   
  $glucose = Array("", "<=130 mg/d", ">=130 mg/d");
  $bilirubin = Array("", "NEGATIVE", "POSITIVE");
  $ketones = Array("", "None", "Present");
  $specific_gravity = Array("", "1.005", "1.010", "1.015", "1.020", "1.025");
  $blood = Array("", "<=1 RBCs", "<=2 RBCs", "<=3 RBCs");
  $protein = Array("", "<=50 mg/d", "<=100 mg/d", "<=150 mg/d");
  
  foreach($data as $key => $value) {
	  $oldKey = $key;
	$vitals = ['vital_weight', 'vital_height', 'vital_temp', 'vital_bp1', 'vital_bp2', 'vital_pulse', 'vital_rr', 'vital_bmi', 'vital_sat', 'vital_on02'];
   if (($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || empty($value) || $value == "" || $value == "0000-00-00 00:00:00" || $key == "family_history" || $key == "social_history" || $key == "social_history_pks_day" || $key == "social_history_yrs_smkd" || $key == "vital_ht" || $key == "vital_wt" || $key == "vital_tp") && !in_array($key, $vitals)) {
    continue;
   }
   if ($value == "on") {
    $value = "yes";
   }
   $key=ucwords(str_replace("_"," ",$key));
    
	if($oldKey == 'in_clinic_tests'){
		$in_clinic_test_value = json_decode(urldecode($value));
		continue;
	}
	
	if($oldKey == 'laborders'){
		$laborder = json_decode(urldecode($value));
		foreach($laborder as $ictKey => $ictValue) {
			$value = '';
			 foreach($ictValue as $v){
			 	$ordsql = sqlQuery("SELECT list_name FROM order_for_test WHERE id='$v'");
			 	if(!empty($ordsql['list_name'])){
			 		$value .= $ordsql['list_name']."\n";
			 	}
			 }
			 break;
		}
	}
	
	if($oldKey == "social_history"){
		$Social_History_sql = sqlFetchArray(sqlStatement("select * from list_options WHERE list_id='Social_History' AND option_id='".$value."'"));
		if(isset($Social_History_sql) && isset($Social_History_sql['title'])) $value = $Social_History_sql['title'];		
		$finalOutPut .= "<td style='width:17%;text-align:right;vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=bold>" . xlt($key) . ": </span></td>";
		$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . nl2br(text($value)) . "</span></td>";
	}elseif($oldKey == "social_history_pks_day" || $oldKey == "social_history_yrs_smkd"){
		$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text><span class=bold>" . xlt($key) . ": </span>" . nl2br(text($value)) . "</span></td>";
		if($oldKey == "social_history_yrs_smkd"){
			$count = 0;
			$finalOutPut .= "</tr><tr>\n";
		}
		continue;
	}elseif(in_array($oldKey, $vitals)){
		if($oldKey == 'vital_weight'){
			$finalOutPut .= "<td colspan='4' style='vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:0px;'>";
			$finalOutPut .= $vitalTable."<tr>";
		}
		if($oldKey == 'vital_bp1'){
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . nl2br(text($value)) . " / ";
		}elseif($oldKey == 'vital_bp2'){
			$finalOutPut .= nl2br(text($value)) . "</span></td>";
		}elseif($oldKey == 'vital_weight'){
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . $data['vital_wt'] . "</span></td>";
		}elseif($oldKey == 'vital_height'){
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . $data['vital_ht'] . "</span></td>";
		}elseif($oldKey == 'vital_temp'){
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . $data['vital_tp'] . "</span></td>";
		}else{			
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . nl2br(text($value)) . "</span></td>";
		}
		if($oldKey == 'vital_on02'){
			
			$finalOutPut .= '</tr></table></td>';			
			$count = 0;
			$finalOutPut .= "</tr><tr>\n";
		}
		continue;
	}else{
		$finalOutPut .= "<td style='width:17%;text-align:right;vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=bold>" . xlt($key) . ": </span></td>";
		$finalOutPut .= "<td style='width:33%;vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class=text>" . nl2br(text($value)) . "</span></td>";
	}
   
   $count++;
   if ($count == $cols) {
    $count = 0;
    $finalOutPut .= "</tr><tr>\n";
   }
  }
 }
 
 if($count == 1) $finalOutPut .= "<td style='width:17%;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'></td><td style='width:17%;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:6px;'></td>";
 $finalOutPut .= "</tr>";
 
 
		$ictCcount = 0; $ictCols = 4;
		$finalOutPut .= "<tr><td colspan='4' style='vertical-align:top;border-bottom:1px solid #303030; border-right:1px solid #DDD;padding:0px;'>";
		$finalOutPut .= '<table style="word-wrap: break-word;border-spacing: 0px;border-top:1px solid #303030; border-left:1px solid #303030; border-right:1px solid #303030;width:1000px;font-size:12px;"><tr>';	
		$clinic_testdecode = json_decode($value);
		foreach($in_clinic_test_value as $ictKey => $ictValue) {
			
			/*if($ictKey == 9) $ictValue = $protein[$ictValue] ?  $protein[$ictValue] : '';
			if($ictKey == 11) $ictValue = $blood[$ictValue] ?  $blood[$ictValue] : '';
			if($ictKey == 12) $ictValue = $specific_gravity[$ictValue] ?  $specific_gravity[$ictValue] : '';
			if($ictKey == 13) $ictValue = $ketones[$ictValue] ?  $ketones[$ictValue] : '';
			if($ictKey == 14) $ictValue = $bilirubin[$ictValue] ?  $bilirubin[$ictValue] : '';
			if($ictKey == 15) $ictValue = $glucose[$ictValue] ?  $glucose[$ictValue] : '';*/
			
			$finalOutPut .= "<td style='vertical-align:top;border-bottom:1px solid #303030;border-top:1px solid #303030; border-right:1px solid #DDD;padding:6px;'><span class='bold'>$in_clinic_test_head[$ictKey]: </span><span class='text'>$ictValue</span></td>";
			$ictCcount++;
			if ($ictCcount == $ictCols) {
				$ictCcount = 0;
				$finalOutPut .= "</tr><tr>";
			}
		}
		$finalOutPut .= "</tr></table>";
		$finalOutPut .= "</td></tr>";
 
 $finalOutPut .= "</table>";
 print $finalOutPut;
}
?>
