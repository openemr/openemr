<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
require_once("$srcdir/formdata.inc.php");

//process form variables here
//create an array of all of the existing field names
$field_names = array('lmp' => 'date','edc' => 'date','weeks' => 'textfield','days' => 'textfield','supplements' => 'textarea','treatments' => 'textarea','height_of_fundus' => 'textfield','hemoglobin' => 'textfield','platelets' => 'textfield','urinalysis' => 'textarea','urine_culture' => 'textarea','abo_and_rh' => 'textfield','coombs_test' => 'textfield','syphillis_rpr' => 'textfield','hepatitis_b' => 'textfield','hiv' => 'textfield','group_b_streptococcus_culture' => 'textfield','glucose_challenge_test' => 'textfield','others' => 'textarea','fetal_heart' => 'radio_group','ultrasound_notes' => 'textarea','problem_list' => 'textarea','other_notes' => 'textarea');
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
foreach ($field_names as $k => $var) {
  #if (strtolower($k) == strtolower($var)) {unset($field_names[$k]);}
  $field_names[$k] = formDataCore($var);
echo "$var\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
reset($field_names);
$newid = formSubmit("form_Obstetrics_Form", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Obstetrics_Form", $newid, "Obstetrics_Form", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Obstetrics_Form set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), lmp='".$field_names["lmp"]."',edc='".$field_names["edc"]."',weeks='".$field_names["weeks"]."',days='".$field_names["days"]."',supplements='".$field_names["supplements"]."',treatments='".$field_names["treatments"]."',height_of_fundus='".$field_names["height_of_fundus"]."',hemoglobin='".$field_names["hemoglobin"]."',platelets='".$field_names["platelets"]."',urinalysis='".$field_names["urinalysis"]."',urine_culture='".$field_names["urine_culture"]."',abo_and_rh='".$field_names["abo_and_rh"]."',coombs_test='".$field_names["coombs_test"]."',syphillis_rpr='".$field_names["syphillis_rpr"]."',hepatitis_b='".$field_names["hepatitis_b"]."',hiv='".$field_names["hiv"]."',group_b_streptococcus_culture='".$field_names["group_b_streptococcus_culture"]."',glucose_challenge_test='".$field_names["glucose_challenge_test"]."',others='".$field_names["others"]."',fetal_heart='".$field_names["fetal_heart"]."',ultrasound_notes='".$field_names["ultrasound_notes"]."',problem_list='".$field_names["problem_list"]."',other_notes='".$field_names["other_notes"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
