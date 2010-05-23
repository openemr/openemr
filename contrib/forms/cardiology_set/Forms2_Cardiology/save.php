<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('_recommended_subacute_bacterial_endocarditis_prophylaxis' => 'checkbox_group','_amoxicillin_regimen' => 'Erythromycin','_other' => 'textfield','_full_active_participation_with_no_restrictions' => 'checkbox','_full_active_participation_with_moderate_exercise' => 'checkbox','_partial_active_participation_with_light_exercise' => 'checkbox','_limited_active_participation_with_no_exercise' => 'checkbox','_date_of_the_last_reaction1' => 'textfield','_type_of_reaction1' => 'textfield','_medication_trigger2' => 'textfield','_date_of_the_last_reaction2' => 'textfield','_type_of_reaction2' => 'textfield','_medication_trigger3' => 'textfield','_date_of_the_last_reaction3' => 'textfield','_type_of_reaction3' => 'textfield','_medication_strength__sig1' => 'textfield','_special_instructions1' => 'textfield','_medication_strength__sig2' => 'textfield','_special_instructions2' => 'textfield','_medication_strength__sig3' => 'textfield','_special_instructions3' => 'textfield','_medication_strength__sig4' => 'textfield','_special_instructions4' => 'textfield','_medication_strength__sig5' => 'textfield','_special_instructions5' => 'textfield','_non_prescription_medications' => 'checkbox_group','_describe_any_recent_operations_or_serious_illness' => 'textarea','_describe_any_physical_disability_effecting_camp_activity' => 'textarea','_describe_any_pertinent_findings_on_examination' => 'textarea','_does_applicant_have_a_history_of_dysrhythmias' => 'radio_group','_date_of_last_episode' => 'date</td>','_please_describe' => 'textfield</td>','_has_there_been_any_recent_cardiac_concerns_medical_events' => 'textarea</td>','_does_applicant_have_a_pacemaker_or_icd' => 'radio_group','_date_of_insertion' => 'date</td>','_reason_for_implantable_device' => 'textarea</td>','_pacemaker_brand' => 'textfield','_pacemaker_model' => 'textfield','_pacemakerdate_of_last_interrogation' => ':date','_pacemaker_programmed_to' => 'textfield','_pacemaker_mode' => 'textfield','_pacemaker_lower_rate' => 'textfield','_pacemaker_upper_rate' => 'textfield','_icd_brand' => 'textfield','_icd_model' => 'textfield','_icd_date_of_last_interrogation' => 'date','_has_icd_discharged_recently_and_how_often' => 'textfield','_date_of_transplant' => 'textfield','_surgeon' => 'textfield','_name_of_center' => 'textfield','_phone' => 'textfield','_evidence_of_rejection' => 'radio_group','_last_cardiac_biopsy_date' => 'date','_if_evidence_of_rejection_then_type_and_grade' => 'textarea','_height' => 'textfield','_weight' => 'textfield','_heart_rate' => 'textfield','_o2_saturation' => 'textfield','_bp_ra' => 'textfield','_bp_la' => 'textfield','_bp_rl' => 'textfield','_bp_ll' => 'textfield','_pulses_rue' => 'textfield','_pulses_lue' => 'textfield','_pulses_rle' => 'textfield','_pulses_lle' => 'textfield','_cardiovascular' => 'textfield','_precordial_activity' => 'textfield','_murmurs' => 'textfield','_neurological' => 'textfield','_lungs' => 'textfield','_abdomen' => 'textfield','_gi_gu' => 'textfield');
$negatives = array();
//process each field according to it's type
foreach($field_names as $key=>$val)
{
  $pos = '';
  $neg = '';
	if ($val == "checkbox")
	{
		if ($_POST[$key]) {$field_names[$key] = "yes";}
		else {$field_names[$key] = "negative";}
	}
	elseif (($val == "checkbox_group")||($val == "scrolling_list_multiples"))
	{
		if (array_key_exists($key,$negatives)) #a field requests reporting of negatives
		{
                  if ($_POST[$key]) 
                  {
			foreach($_POST[$key] as $var) #check positives against list
			{
				if (array_key_exists($var, $negatives[$key]))
				{	#remove positives from list, leaving negatives
					unset($negatives[$key][$var]);
				}
			}
                  }
			if (is_array($negatives[$key]) && count($negatives[$key])>0) 
			{
				$neg = "Negative for ".implode(', ',$negatives[$key]).'.';
			}
		}
		if (is_array($_POST[$key]) && count($_POST[$key])>0) 
		{
			$pos = implode(', ',$_POST[$key]);
		}
		if($pos) {$pos = 'Positive for '.$pos.'.  ';}
		$field_names[$key] = $pos.$neg;	
	}
	else
	{
		$field_names[$key] = $_POST[$key];
	}
        if ($field_names[$key] != '')
        {
//          $field_names[$key] .= '.';
          $field_names[$key] = preg_replace('/\s*,\s*([^,]+)\./',' and $1.',$field_names[$key]); // replace last comma with 'and' and ending period
        } 
}

//end special processing
if(get_magic_quotes_gpc()) {
  foreach ($field_names as $k => $var) {
    $field_names[$k] = stripslashes($var);
  }
}
foreach ($field_names as $k => $var) {
  #if (strtolower($k) == strtolower($var)) {unset($field_names[$k]);}
  $field_names[$k] = mysql_real_escape_string($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
reset($field_names);
$newid = formSubmit("form_Forms2_Cardiology", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Forms2_Cardiology", $newid, "Forms2_Cardiology", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Forms2_Cardiology set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), _recommended_subacute_bacterial_endocarditis_prophylaxis='".$field_names["_recommended_subacute_bacterial_endocarditis_prophylaxis"]."',_amoxicillin_regimen='".$field_names["_amoxicillin_regimen"]."',_other='".$field_names["_other"]."',_full_active_participation_with_no_restrictions='".$field_names["_full_active_participation_with_no_restrictions"]."',_full_active_participation_with_moderate_exercise='".$field_names["_full_active_participation_with_moderate_exercise"]."',_partial_active_participation_with_light_exercise='".$field_names["_partial_active_participation_with_light_exercise"]."',_limited_active_participation_with_no_exercise='".$field_names["_limited_active_participation_with_no_exercise"]."',_date_of_the_last_reaction1='".$field_names["_date_of_the_last_reaction1"]."',_type_of_reaction1='".$field_names["_type_of_reaction1"]."',_medication_trigger2='".$field_names["_medication_trigger2"]."',_date_of_the_last_reaction2='".$field_names["_date_of_the_last_reaction2"]."',_type_of_reaction2='".$field_names["_type_of_reaction2"]."',_medication_trigger3='".$field_names["_medication_trigger3"]."',_date_of_the_last_reaction3='".$field_names["_date_of_the_last_reaction3"]."',_type_of_reaction3='".$field_names["_type_of_reaction3"]."',_medication_strength__sig1='".$field_names["_medication_strength__sig1"]."',_special_instructions1='".$field_names["_special_instructions1"]."',_medication_strength__sig2='".$field_names["_medication_strength__sig2"]."',_special_instructions2='".$field_names["_special_instructions2"]."',_medication_strength__sig3='".$field_names["_medication_strength__sig3"]."',_special_instructions3='".$field_names["_special_instructions3"]."',_medication_strength__sig4='".$field_names["_medication_strength__sig4"]."',_special_instructions4='".$field_names["_special_instructions4"]."',_medication_strength__sig5='".$field_names["_medication_strength__sig5"]."',_special_instructions5='".$field_names["_special_instructions5"]."',_non_prescription_medications='".$field_names["_non_prescription_medications"]."',_describe_any_recent_operations_or_serious_illness='".$field_names["_describe_any_recent_operations_or_serious_illness"]."',_describe_any_physical_disability_effecting_camp_activity='".$field_names["_describe_any_physical_disability_effecting_camp_activity"]."',_describe_any_pertinent_findings_on_examination='".$field_names["_describe_any_pertinent_findings_on_examination"]."',_does_applicant_have_a_history_of_dysrhythmias='".$field_names["_does_applicant_have_a_history_of_dysrhythmias"]."',_date_of_last_episode='".$field_names["_date_of_last_episode"]."',_please_describe='".$field_names["_please_describe"]."',_has_there_been_any_recent_cardiac_concerns_medical_events='".$field_names["_has_there_been_any_recent_cardiac_concerns_medical_events"]."',_does_applicant_have_a_pacemaker_or_icd='".$field_names["_does_applicant_have_a_pacemaker_or_icd"]."',_date_of_insertion='".$field_names["_date_of_insertion"]."',_reason_for_implantable_device='".$field_names["_reason_for_implantable_device"]."',_pacemaker_brand='".$field_names["_pacemaker_brand"]."',_pacemaker_model='".$field_names["_pacemaker_model"]."',_pacemakerdate_of_last_interrogation='".$field_names["_pacemakerdate_of_last_interrogation"]."',_pacemaker_programmed_to='".$field_names["_pacemaker_programmed_to"]."',_pacemaker_mode='".$field_names["_pacemaker_mode"]."',_pacemaker_lower_rate='".$field_names["_pacemaker_lower_rate"]."',_pacemaker_upper_rate='".$field_names["_pacemaker_upper_rate"]."',_icd_brand='".$field_names["_icd_brand"]."',_icd_model='".$field_names["_icd_model"]."',_icd_date_of_last_interrogation='".$field_names["_icd_date_of_last_interrogation"]."',_has_icd_discharged_recently_and_how_often='".$field_names["_has_icd_discharged_recently_and_how_often"]."',_date_of_transplant='".$field_names["_date_of_transplant"]."',_surgeon='".$field_names["_surgeon"]."',_name_of_center='".$field_names["_name_of_center"]."',_phone='".$field_names["_phone"]."',_evidence_of_rejection='".$field_names["_evidence_of_rejection"]."',_last_cardiac_biopsy_date='".$field_names["_last_cardiac_biopsy_date"]."',_if_evidence_of_rejection_then_type_and_grade='".$field_names["_if_evidence_of_rejection_then_type_and_grade"]."',_height='".$field_names["_height"]."',_weight='".$field_names["_weight"]."',_heart_rate='".$field_names["_heart_rate"]."',_o2_saturation='".$field_names["_o2_saturation"]."',_bp_ra='".$field_names["_bp_ra"]."',_bp_la='".$field_names["_bp_la"]."',_bp_rl='".$field_names["_bp_rl"]."',_bp_ll='".$field_names["_bp_ll"]."',_pulses_rue='".$field_names["_pulses_rue"]."',_pulses_lue='".$field_names["_pulses_lue"]."',_pulses_rle='".$field_names["_pulses_rle"]."',_pulses_lle='".$field_names["_pulses_lle"]."',_cardiovascular='".$field_names["_cardiovascular"]."',_precordial_activity='".$field_names["_precordial_activity"]."',_murmurs='".$field_names["_murmurs"]."',_neurological='".$field_names["_neurological"]."',_lungs='".$field_names["_lungs"]."',_abdomen='".$field_names["_abdomen"]."',_gi_gu='".$field_names["_gi_gu"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
