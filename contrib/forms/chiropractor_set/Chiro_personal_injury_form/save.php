<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('_patient_name' => 'textfield','_middle_name' => 'textfield','_last_name' => 'textfield','_address_direction' => 'textfield','_city' => 'textfield','_state' => 'textfield','_zip' => 'textfield','_phone_number_home' => 'textfield','_phone_number_work' => 'textfield','_sex' => 'checkbox_group','_date_of_birth' => 'textfield','_social_security' => 'textfield','_nature_of_accident' => 'checkbox_group','_other' => 'textfield','_date_of_accident' => 'textfield','_insurance_name' => 'textfield','_phone_no' => 'textfield','_address_of_insurance_company' => 'textfield','_claim_number' => 'textfield','_policy_number' => 'textfield','_attorney_name' => 'textfield','_attorney_phone_number' => 'textfield','_attorney_address' => 'textfield','_health_insurance' => 'textfield','_health_insurance_phone_number' => 'textfield','_address_of_health_insurance' => 'textfield','_subscriber_id_number' => 'textfield','_group_number' => 'textfield');
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
$newid = formSubmit("form_Chiro_personal_injury_form", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Chiro_personal_injury_form", $newid, "Chiro_personal_injury_form", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Chiro_personal_injury_form set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), _patient_name='".$field_names["_patient_name"]."',_middle_name='".$field_names["_middle_name"]."',_last_name='".$field_names["_last_name"]."',_address_direction='".$field_names["_address_direction"]."',_city='".$field_names["_city"]."',_state='".$field_names["_state"]."',_zip='".$field_names["_zip"]."',_phone_number_home='".$field_names["_phone_number_home"]."',_phone_number_work='".$field_names["_phone_number_work"]."',_sex='".$field_names["_sex"]."',_date_of_birth='".$field_names["_date_of_birth"]."',_social_security='".$field_names["_social_security"]."',_nature_of_accident='".$field_names["_nature_of_accident"]."',_other='".$field_names["_other"]."',_date_of_accident='".$field_names["_date_of_accident"]."',_insurance_name='".$field_names["_insurance_name"]."',_phone_no='".$field_names["_phone_no"]."',_address_of_insurance_company='".$field_names["_address_of_insurance_company"]."',_claim_number='".$field_names["_claim_number"]."',_policy_number='".$field_names["_policy_number"]."',_attorney_name='".$field_names["_attorney_name"]."',_attorney_phone_number='".$field_names["_attorney_phone_number"]."',_attorney_address='".$field_names["_attorney_address"]."',_health_insurance='".$field_names["_health_insurance"]."',_health_insurance_phone_number='".$field_names["_health_insurance_phone_number"]."',_address_of_health_insurance='".$field_names["_address_of_health_insurance"]."',_subscriber_id_number='".$field_names["_subscriber_id_number"]."',_group_number='".$field_names["_group_number"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
