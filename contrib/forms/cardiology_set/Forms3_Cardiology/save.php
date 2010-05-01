<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('_date' => 'textfield','_name' => 'textfield','_chief_complaint' => 'textfield','_wt' => 'textfield','_bp' => 'textfield','_p' => 'textfield','_t' => 'textfield','_r' => 'textfield','_ht' => 'textfield','_location' => 'textfield','_quality' => 'textfield','_severity' => 'textfield','_duration' => 'textfield','_timing' => 'textfield','_context' => 'textfield','_modifying_factors' => 'textfield','_signs_symptoms' => 'textfield','_status_of_chronic_illness' => 'textfield','_systemic_positive' => 'textfield','_systemic_negative' => 'textfield','_ent_positive' => 'textfield','_ent_negative' => 'textfield','_eyes_positive' => 'textfield','_eyes_negative' => 'textfield','_lymph_positive' => 'textfield','_lymph_negative' => 'textfield','_resp_positive' => 'textfield','_resp_negative' => 'textfield','_cv_positive' => 'textfield','_cv_negative' => 'textfield','_gi_positive' => 'textfield','_gi_negative' => 'textfield','_gu_positive' => 'textfield','_gu_negative' => 'textfield','_skin_positive' => 'textfield','_skin_negative' => 'textfield','_ms_positive' => 'textfield','_ms_negative' => 'textfield','_psych_positive' => 'textfield','_psych_negative' => 'textfield','_all_other_ros_negative_' => 'textarea','_past_famiy_social_history' => 'textarea','_ph_no_change_since' => 'textfield','_fh_no_change_since' => 'textfield','_sh_no_change_since' => 'textfield','examination' => 'textarea');
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
$newid = formSubmit("form_Forms3_Cardiology", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Forms3_Cardiology", $newid, "Forms3_Cardiology", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Forms3_Cardiology set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), _date='".$field_names["_date"]."',_name='".$field_names["_name"]."',_chief_complaint='".$field_names["_chief_complaint"]."',_wt='".$field_names["_wt"]."',_bp='".$field_names["_bp"]."',_p='".$field_names["_p"]."',_t='".$field_names["_t"]."',_r='".$field_names["_r"]."',_ht='".$field_names["_ht"]."',_location='".$field_names["_location"]."',_quality='".$field_names["_quality"]."',_severity='".$field_names["_severity"]."',_duration='".$field_names["_duration"]."',_timing='".$field_names["_timing"]."',_context='".$field_names["_context"]."',_modifying_factors='".$field_names["_modifying_factors"]."',_signs_symptoms='".$field_names["_signs_symptoms"]."',_status_of_chronic_illness='".$field_names["_status_of_chronic_illness"]."',_systemic_positive='".$field_names["_systemic_positive"]."',_systemic_negative='".$field_names["_systemic_negative"]."',_ent_positive='".$field_names["_ent_positive"]."',_ent_negative='".$field_names["_ent_negative"]."',_eyes_positive='".$field_names["_eyes_positive"]."',_eyes_negative='".$field_names["_eyes_negative"]."',_lymph_positive='".$field_names["_lymph_positive"]."',_lymph_negative='".$field_names["_lymph_negative"]."',_resp_positive='".$field_names["_resp_positive"]."',_resp_negative='".$field_names["_resp_negative"]."',_cv_positive='".$field_names["_cv_positive"]."',_cv_negative='".$field_names["_cv_negative"]."',_gi_positive='".$field_names["_gi_positive"]."',_gi_negative='".$field_names["_gi_negative"]."',_gu_positive='".$field_names["_gu_positive"]."',_gu_negative='".$field_names["_gu_negative"]."',_skin_positive='".$field_names["_skin_positive"]."',_skin_negative='".$field_names["_skin_negative"]."',_ms_positive='".$field_names["_ms_positive"]."',_ms_negative='".$field_names["_ms_negative"]."',_psych_positive='".$field_names["_psych_positive"]."',_psych_negative='".$field_names["_psych_negative"]."',_all_other_ros_negative_='".$field_names["_all_other_ros_negative_"]."',_past_famiy_social_history='".$field_names["_past_famiy_social_history"]."',_ph_no_change_since='".$field_names["_ph_no_change_since"]."',_fh_no_change_since='".$field_names["_fh_no_change_since"]."',_sh_no_change_since='".$field_names["_sh_no_change_since"]."',examination='".$field_names["examination"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
