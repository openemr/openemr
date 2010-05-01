<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('_date' => 'textfield','_social_security_number' => 'textfield','_drivers_license_number' => 'textfield','_name' => 'textfield','_address' => 'textfield','_city' => 'textfield','_state' => 'textfield','_zip' => 'textfield','_home_phone' => 'textfield','_cell_phone' => 'textfield','_birth_date' => 'textfield','_age' => 'textfield','_sex' => 'checkbox_group','_business_or_employer' => 'textfield','_type_of_work' => 'textfield','_business_address_and_phone_number' => 'textfield','_check_one' => 'checkbox_group','_number_of_children' => 'textfield','_name_and_number_of_emergency_contact' => 'textfield','_spouse_name' => 'textfield','_occupation' => 'textfield','_employer' => 'textfield','_who_is_responsible_for_your_bill' => 'checkbox_group','_other' => 'textfield','_purpose_of_this_appointment' => 'textfield','_other_doctors_seen_for_this_condition' => 'textfield','_when_did_this_condition_begin' => 'textfield','_check' => 'checkbox_group','_medication_you_now_take' => 'checkbox_group','_others' => 'textfield','_major_surgery_or_operations' => 'checkbox_group','_otherone' => 'textfield','_major_accidents_or_falls' => 'textfield','_hospitalization_if_other_than_above' => 'textfield','_previous_chiropractic_care' => 'checkbox_group','_doctors_name' => 'textfield','_appox_date_of_last_visit' => 'textfield','_coughing_or_sneezing' => 'popup_menu','_climbing' => 'popup_menu','_getting_in_and_out_of_a_car' => 'popup_menu','_kneeling' => 'popup_menu','_bending_forward_to_brush_teeth' => 'popup_menu','_balancing' => 'popup_menu','_turing_over_in_bed' => 'popup_menu','_dressing_self' => 'popup_menu','_walking_short_distance' => 'popup_menu','_sleeping' => 'popup_menu','_standing_more_than_one_hour' => 'popup_menu','_stooping' => 'popup_menu','_sitting_at_table' => 'popup_menu','_gripping' => 'popup_menu','_lying_on_back' => 'popup_menu','_pushing' => 'popup_menu','_lying_flat_on_stomach' => 'popup_menu','_pulling' => 'popup_menu','_lying_on_side_with_knees_bent' => 'popup_menu','_reaching' => 'popup_menu','_bending_over_forward' => 'popup_menu','_sexual_activity' => 'popup_menu','_checking_symptoms_of_nervous_systems' => 'checkbox_group','_how_often_do_you_have_headaches' => 'textfield','_symptoms_are_better_in' => 'checkbox_group','_symptoms_are_worse_in' => 'checkbox_group','_symptoms_do_not_change_with_time_of_day' => 'checkbox','_are_you_pregnant' => 'checkbox_group','_date_of_onset_of_last_menstrual_cycle' => 'textfield','_give_date_of_last_xray' => 'textfield','_what_body_part_were_they_taken_of' => 'textfield','_cancer' => 'checkbox_group','_diabetes' => 'checkbox_group','_heart_problems' => 'checkbox_group','_back_or_neck_problems' => 'checkbox_group','_have_you_retained_an_attorney' => 'checkbox_group','_attorney_name' => 'textfield','_attorney_address' => 'textfield','_attorney_phone' => 'textfield','_number_of_people_in_vechicle_and_their_name' => 'textfield','_were_the_policy_notified' => 'checkbox_group','_what_direction_were_you_headed' => 'checkbox_group','_what_direction_was_other_vechicle' => 'checkbox_group','_name_of_street_or_town' => 'textfield','_were_you_struck_from' => 'checkbox_group','_in_your_own_words_please_describe_accident' => 'textarea','_please_complaints_and_symptoms' => 'textarea','_did_you_lose_any_time_from_work' => 'checkbox_group','_date_when_you_lose_from_work' => 'textfield','_type_of_employment' => 'textfield','_where_were_you_taken_immediately_following_accident' => 'textfield','_if_taken_to_the_hospital_did_you' => 'checkbox_group','_have_you_ever_been_involved_in_an_accident_before' => 'checkbox_group');
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
$newid = formSubmit("form_Chirpractic_physical_therapy_form", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Chirpractic_physical_therapy_form", $newid, "Chirpractic_physical_therapy_form", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Chirpractic_physical_therapy_form set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), _date='".$field_names["_date"]."',_social_security_number='".$field_names["_social_security_number"]."',_drivers_license_number='".$field_names["_drivers_license_number"]."',_name='".$field_names["_name"]."',_address='".$field_names["_address"]."',_city='".$field_names["_city"]."',_state='".$field_names["_state"]."',_zip='".$field_names["_zip"]."',_home_phone='".$field_names["_home_phone"]."',_cell_phone='".$field_names["_cell_phone"]."',_birth_date='".$field_names["_birth_date"]."',_age='".$field_names["_age"]."',_sex='".$field_names["_sex"]."',_business_or_employer='".$field_names["_business_or_employer"]."',_type_of_work='".$field_names["_type_of_work"]."',_business_address_and_phone_number='".$field_names["_business_address_and_phone_number"]."',_check_one='".$field_names["_check_one"]."',_number_of_children='".$field_names["_number_of_children"]."',_name_and_number_of_emergency_contact='".$field_names["_name_and_number_of_emergency_contact"]."',_spouse_name='".$field_names["_spouse_name"]."',_occupation='".$field_names["_occupation"]."',_employer='".$field_names["_employer"]."',_who_is_responsible_for_your_bill='".$field_names["_who_is_responsible_for_your_bill"]."',_other='".$field_names["_other"]."',_purpose_of_this_appointment='".$field_names["_purpose_of_this_appointment"]."',_other_doctors_seen_for_this_condition='".$field_names["_other_doctors_seen_for_this_condition"]."',_when_did_this_condition_begin='".$field_names["_when_did_this_condition_begin"]."',_check='".$field_names["_check"]."',_medication_you_now_take='".$field_names["_medication_you_now_take"]."',_others='".$field_names["_others"]."',_major_surgery_or_operations='".$field_names["_major_surgery_or_operations"]."',_otherone='".$field_names["_otherone"]."',_major_accidents_or_falls='".$field_names["_major_accidents_or_falls"]."',_hospitalization_if_other_than_above='".$field_names["_hospitalization_if_other_than_above"]."',_previous_chiropractic_care='".$field_names["_previous_chiropractic_care"]."',_doctors_name='".$field_names["_doctors_name"]."',_appox_date_of_last_visit='".$field_names["_appox_date_of_last_visit"]."',_coughing_or_sneezing='".$field_names["_coughing_or_sneezing"]."',_climbing='".$field_names["_climbing"]."',_getting_in_and_out_of_a_car='".$field_names["_getting_in_and_out_of_a_car"]."',_kneeling='".$field_names["_kneeling"]."',_bending_forward_to_brush_teeth='".$field_names["_bending_forward_to_brush_teeth"]."',_balancing='".$field_names["_balancing"]."',_turing_over_in_bed='".$field_names["_turing_over_in_bed"]."',_dressing_self='".$field_names["_dressing_self"]."',_walking_short_distance='".$field_names["_walking_short_distance"]."',_sleeping='".$field_names["_sleeping"]."',_standing_more_than_one_hour='".$field_names["_standing_more_than_one_hour"]."',_stooping='".$field_names["_stooping"]."',_sitting_at_table='".$field_names["_sitting_at_table"]."',_gripping='".$field_names["_gripping"]."',_lying_on_back='".$field_names["_lying_on_back"]."',_pushing='".$field_names["_pushing"]."',_lying_flat_on_stomach='".$field_names["_lying_flat_on_stomach"]."',_pulling='".$field_names["_pulling"]."',_lying_on_side_with_knees_bent='".$field_names["_lying_on_side_with_knees_bent"]."',_reaching='".$field_names["_reaching"]."',_bending_over_forward='".$field_names["_bending_over_forward"]."',_sexual_activity='".$field_names["_sexual_activity"]."',_checking_symptoms_of_nervous_systems='".$field_names["_checking_symptoms_of_nervous_systems"]."',_how_often_do_you_have_headaches='".$field_names["_how_often_do_you_have_headaches"]."',_symptoms_are_better_in='".$field_names["_symptoms_are_better_in"]."',_symptoms_are_worse_in='".$field_names["_symptoms_are_worse_in"]."',_symptoms_do_not_change_with_time_of_day='".$field_names["_symptoms_do_not_change_with_time_of_day"]."',_are_you_pregnant='".$field_names["_are_you_pregnant"]."',_date_of_onset_of_last_menstrual_cycle='".$field_names["_date_of_onset_of_last_menstrual_cycle"]."',_give_date_of_last_xray='".$field_names["_give_date_of_last_xray"]."',_what_body_part_were_they_taken_of='".$field_names["_what_body_part_were_they_taken_of"]."',_cancer='".$field_names["_cancer"]."',_diabetes='".$field_names["_diabetes"]."',_heart_problems='".$field_names["_heart_problems"]."',_back_or_neck_problems='".$field_names["_back_or_neck_problems"]."',_have_you_retained_an_attorney='".$field_names["_have_you_retained_an_attorney"]."',_attorney_name='".$field_names["_attorney_name"]."',_attorney_address='".$field_names["_attorney_address"]."',_attorney_phone='".$field_names["_attorney_phone"]."',_number_of_people_in_vechicle_and_their_name='".$field_names["_number_of_people_in_vechicle_and_their_name"]."',_were_the_policy_notified='".$field_names["_were_the_policy_notified"]."',_what_direction_were_you_headed='".$field_names["_what_direction_were_you_headed"]."',_what_direction_was_other_vechicle='".$field_names["_what_direction_was_other_vechicle"]."',_name_of_street_or_town='".$field_names["_name_of_street_or_town"]."',_were_you_struck_from='".$field_names["_were_you_struck_from"]."',_in_your_own_words_please_describe_accident='".$field_names["_in_your_own_words_please_describe_accident"]."',_please_complaints_and_symptoms='".$field_names["_please_complaints_and_symptoms"]."',_did_you_lose_any_time_from_work='".$field_names["_did_you_lose_any_time_from_work"]."',_date_when_you_lose_from_work='".$field_names["_date_when_you_lose_from_work"]."',_type_of_employment='".$field_names["_type_of_employment"]."',_where_were_you_taken_immediately_following_accident='".$field_names["_where_were_you_taken_immediately_following_accident"]."',_if_taken_to_the_hospital_did_you='".$field_names["_if_taken_to_the_hospital_did_you"]."',_have_you_ever_been_involved_in_an_accident_before='".$field_names["_have_you_ever_been_involved_in_an_accident_before"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
