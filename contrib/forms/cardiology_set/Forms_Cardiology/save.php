<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('_first_name' => 'textfield','_middle_name' => 'textfield','_last_name' => 'textfield','_nick_name' => 'textfield','_street_address_number' => 'textfield','_street_name' => 'textfield','_street_name_apt' => 'textfield','_street_name_space' => 'textfield','_po_box_address_number' => 'textfield','_po_box_street' => 'textfield','_po_box_apt' => 'textfield','_po_box_space' => 'textfield','_city' => 'textfield','_state' => 'textfield','_zip_code' => 'textfield','_social_security' => 'textfield','_home_phone' => 'textfield','_email_address' => 'textfield','_cell_phone' => 'textfield','_date_of_birth' => 'date','_age' => 'textfield','_sex' => 'checkbox_group','_marital_status' => 'checkbox_group','_occupation' => 'textfield','_employer_name' => 'textfield','_employer_street_address' => 'textfield','_employer_city' => 'textfield','_employer_state' => 'textfield','_employer_zip_code' => 'textfield','_business_phone' => 'textfield','_extension' => 'textfield','_drivers_license' => 'textfield','_drivers_license_state' => 'textfield','_spg_refers_to_spouse_parents_guarantors' => 'textfield','_spg_first_name' => 'textfield','_spg_middle_name' => 'textfield','_spg_last_name' => 'textfield','_spg_occupation' => 'textfield','_spg_address_if_different_than_above' => 'textfield','_spg_city' => 'textfield','_spg_state' => 'textfield','_spg_zip_code' => 'textfield','_spg_home_phone' => 'textfield','_spg_employer_street_address' => 'textfield','_spg_employer_city' => 'textfield','_spg_employer_state' => 'textfield','_spg_employer_zip_code' => 'textfield','_spg_employer_business_phone' => 'textfield','_spg_employer_extension' => 'textfield','_concerning_insurance_deatils' => 'checkbox_group','_date_of_injury' => 'date','_primary_insurance_co_here' => 'textfield','_primary_insurance_group_number' => 'textfield','_primary_insurance_id_number' => 'textfield','_primary_insurance_insured_name' => 'textfield','_primary_insurance_insured_date_of_birth' => 'date','_primary_insurance_insured_address' => 'textfield','_secondary_insurance_co_name' => 'textfield','_secondary_insurance_group_number' => 'textfield','_secondary_insurance_id_number' => 'textfield','_secondary_insurance_insureds_name' => 'textfield','_secondary_insurance_insureds_date_of_birth' => 'date','_secondary_insurance_insureds_col_address' => 'textfield','_person_to_notify_in_case_of_emergency_not_leaving_with_you' => 'textfield','_relationship' => 'textfield','_person_address' => 'textfield','_person_street' => 'textfield','_person_apt' => 'textfield','_person_space' => 'textfield','_person_city' => 'textfield','_person_state' => 'textfield','_person_zip_code' => 'textfield','_person_home_phone' => 'textfield','heart_problems_or_symptoms' => 'checkbox_group','have_you_ever_had' => 'checkbox_group','check_if_you_have' => 'checkbox_group','close_family_member_with' => 'checkbox_group','if_a_woman_have_you' => 'checkbox_group','menopause_passed_on_what_age' => 'textfield','have_you_take_estrogen_replacement' => 'checkbox','please_tell_us_anything_else_about_heart' => 'textarea','medicine_detail1' => 'textarea','medicine_detail2' => 'textarea','medicine_detail3' => 'textarea','medicine_detail4' => 'textarea','medicine_detail5' => 'textarea','medicine_detail6' => 'textarea','medicine_detail7' => 'textarea','medicine_detail8' => 'textarea','are_you_allergic_to_any_medications' => 'checkbox_group','lis_medicine_to_which_you_are_allergic' => 'textfield','what_kind_of_reaction_did_you_have' => 'textfield','constitutional' => 'checkbox_group','heent' => 'checkbox_group','respiratory' => 'checkbox_group','digestive' => 'checkbox_group','urinary' => 'checkbox_group','musculoskeletal' => 'checkbox_group','dermatological' => 'checkbox_group','men' => 'checkbox_group','women' => 'checkbox_group','female_reproductive' => 'checkbox_group','neurological' => 'checkbox_group','psychiatric' => 'checkbox_group','endocrinology' => 'checkbox_group','hematological' => 'checkbox_group','have_you_had_any_operations' => 'textarea','are_you_being_treated_now_or_have_been_treated_for_any_illness' => 'textarea','marital_status' => 'checkbox_group','do_you_smoke' => 'checkbox_group','occupation' => 'textfield','how_many_packs_per_day' => 'textfield','leisure_activities' => 'textfield','for_how_many_years' => 'textfield','educational_level' => 'textfield','how_much_alcohol_do_you_drink' => 'textfield','do_you_use_any_drugs' => 'textfield','heart_problems' => 'checkbox_group','high_blood_pressure' => 'checkbox_group','diabetes' => 'checkbox_group','_cancer' => 'checkbox_group','year' => 'textfield','hospital' => 'textfield','reason' => 'textfield');
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
$newid = formSubmit("form_Forms_Cardiology", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Forms_Cardiology", $newid, "Forms_Cardiology", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Forms_Cardiology set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), _first_name='".$field_names["_first_name"]."',_middle_name='".$field_names["_middle_name"]."',_last_name='".$field_names["_last_name"]."',_nick_name='".$field_names["_nick_name"]."',_street_address_number='".$field_names["_street_address_number"]."',_street_name='".$field_names["_street_name"]."',_street_name_apt='".$field_names["_street_name_apt"]."',_street_name_space='".$field_names["_street_name_space"]."',_po_box_address_number='".$field_names["_po_box_address_number"]."',_po_box_street='".$field_names["_po_box_street"]."',_po_box_apt='".$field_names["_po_box_apt"]."',_po_box_space='".$field_names["_po_box_space"]."',_city='".$field_names["_city"]."',_state='".$field_names["_state"]."',_zip_code='".$field_names["_zip_code"]."',_social_security='".$field_names["_social_security"]."',_home_phone='".$field_names["_home_phone"]."',_email_address='".$field_names["_email_address"]."',_cell_phone='".$field_names["_cell_phone"]."',_date_of_birth='".$field_names["_date_of_birth"]."',_age='".$field_names["_age"]."',_sex='".$field_names["_sex"]."',_marital_status='".$field_names["_marital_status"]."',_occupation='".$field_names["_occupation"]."',_employer_name='".$field_names["_employer_name"]."',_employer_street_address='".$field_names["_employer_street_address"]."',_employer_city='".$field_names["_employer_city"]."',_employer_state='".$field_names["_employer_state"]."',_employer_zip_code='".$field_names["_employer_zip_code"]."',_business_phone='".$field_names["_business_phone"]."',_extension='".$field_names["_extension"]."',_drivers_license='".$field_names["_drivers_license"]."',_drivers_license_state='".$field_names["_drivers_license_state"]."',_spg_refers_to_spouse_parents_guarantors='".$field_names["_spg_refers_to_spouse_parents_guarantors"]."',_spg_first_name='".$field_names["_spg_first_name"]."',_spg_middle_name='".$field_names["_spg_middle_name"]."',_spg_last_name='".$field_names["_spg_last_name"]."',_spg_occupation='".$field_names["_spg_occupation"]."',_spg_address_if_different_than_above='".$field_names["_spg_address_if_different_than_above"]."',_spg_city='".$field_names["_spg_city"]."',_spg_state='".$field_names["_spg_state"]."',_spg_zip_code='".$field_names["_spg_zip_code"]."',_spg_home_phone='".$field_names["_spg_home_phone"]."',_spg_employer_street_address='".$field_names["_spg_employer_street_address"]."',_spg_employer_city='".$field_names["_spg_employer_city"]."',_spg_employer_state='".$field_names["_spg_employer_state"]."',_spg_employer_zip_code='".$field_names["_spg_employer_zip_code"]."',_spg_employer_business_phone='".$field_names["_spg_employer_business_phone"]."',_spg_employer_extension='".$field_names["_spg_employer_extension"]."',_concerning_insurance_deatils='".$field_names["_concerning_insurance_deatils"]."',_date_of_injury='".$field_names["_date_of_injury"]."',_primary_insurance_co_here='".$field_names["_primary_insurance_co_here"]."',_primary_insurance_group_number='".$field_names["_primary_insurance_group_number"]."',_primary_insurance_id_number='".$field_names["_primary_insurance_id_number"]."',_primary_insurance_insured_name='".$field_names["_primary_insurance_insured_name"]."',_primary_insurance_insured_date_of_birth='".$field_names["_primary_insurance_insured_date_of_birth"]."',_primary_insurance_insured_address='".$field_names["_primary_insurance_insured_address"]."',_secondary_insurance_co_name='".$field_names["_secondary_insurance_co_name"]."',_secondary_insurance_group_number='".$field_names["_secondary_insurance_group_number"]."',_secondary_insurance_id_number='".$field_names["_secondary_insurance_id_number"]."',_secondary_insurance_insureds_name='".$field_names["_secondary_insurance_insureds_name"]."',_secondary_insurance_insureds_date_of_birth='".$field_names["_secondary_insurance_insureds_date_of_birth"]."',_secondary_insurance_insureds_col_address='".$field_names["_secondary_insurance_insureds_col_address"]."',_person_to_notify_in_case_of_emergency_not_leaving_with_you='".$field_names["_person_to_notify_in_case_of_emergency_not_leaving_with_you"]."',_relationship='".$field_names["_relationship"]."',_person_address='".$field_names["_person_address"]."',_person_street='".$field_names["_person_street"]."',_person_apt='".$field_names["_person_apt"]."',_person_space='".$field_names["_person_space"]."',_person_city='".$field_names["_person_city"]."',_person_state='".$field_names["_person_state"]."',_person_zip_code='".$field_names["_person_zip_code"]."',_person_home_phone='".$field_names["_person_home_phone"]."',heart_problems_or_symptoms='".$field_names["heart_problems_or_symptoms"]."',have_you_ever_had='".$field_names["have_you_ever_had"]."',check_if_you_have='".$field_names["check_if_you_have"]."',close_family_member_with='".$field_names["close_family_member_with"]."',if_a_woman_have_you='".$field_names["if_a_woman_have_you"]."',menopause_passed_on_what_age='".$field_names["menopause_passed_on_what_age"]."',have_you_take_estrogen_replacement='".$field_names["have_you_take_estrogen_replacement"]."',please_tell_us_anything_else_about_heart='".$field_names["please_tell_us_anything_else_about_heart"]."',medicine_detail1='".$field_names["medicine_detail1"]."',medicine_detail2='".$field_names["medicine_detail2"]."',medicine_detail3='".$field_names["medicine_detail3"]."',medicine_detail4='".$field_names["medicine_detail4"]."',medicine_detail5='".$field_names["medicine_detail5"]."',medicine_detail6='".$field_names["medicine_detail6"]."',medicine_detail7='".$field_names["medicine_detail7"]."',medicine_detail8='".$field_names["medicine_detail8"]."',are_you_allergic_to_any_medications='".$field_names["are_you_allergic_to_any_medications"]."',lis_medicine_to_which_you_are_allergic='".$field_names["lis_medicine_to_which_you_are_allergic"]."',what_kind_of_reaction_did_you_have='".$field_names["what_kind_of_reaction_did_you_have"]."',constitutional='".$field_names["constitutional"]."',heent='".$field_names["heent"]."',respiratory='".$field_names["respiratory"]."',digestive='".$field_names["digestive"]."',urinary='".$field_names["urinary"]."',musculoskeletal='".$field_names["musculoskeletal"]."',dermatological='".$field_names["dermatological"]."',men='".$field_names["men"]."',women='".$field_names["women"]."',female_reproductive='".$field_names["female_reproductive"]."',neurological='".$field_names["neurological"]."',psychiatric='".$field_names["psychiatric"]."',endocrinology='".$field_names["endocrinology"]."',hematological='".$field_names["hematological"]."',have_you_had_any_operations='".$field_names["have_you_had_any_operations"]."',are_you_being_treated_now_or_have_been_treated_for_any_illness='".$field_names["are_you_being_treated_now_or_have_been_treated_for_any_illness"]."',marital_status='".$field_names["marital_status"]."',do_you_smoke='".$field_names["do_you_smoke"]."',occupation='".$field_names["occupation"]."',how_many_packs_per_day='".$field_names["how_many_packs_per_day"]."',leisure_activities='".$field_names["leisure_activities"]."',for_how_many_years='".$field_names["for_how_many_years"]."',educational_level='".$field_names["educational_level"]."',how_much_alcohol_do_you_drink='".$field_names["how_much_alcohol_do_you_drink"]."',do_you_use_any_drugs='".$field_names["do_you_use_any_drugs"]."',heart_problems='".$field_names["heart_problems"]."',high_blood_pressure='".$field_names["high_blood_pressure"]."',diabetes='".$field_names["diabetes"]."',_cancer='".$field_names["_cancer"]."',year='".$field_names["year"]."',hospital='".$field_names["hospital"]."',reason='".$field_names["reason"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
