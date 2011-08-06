<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

//process form variables here
//create an array of all of the existing field names
$field_names = array('general_condit' => 'checkbox_group','muscle_joint' => 'checkbox_group','pain_numb_locat' => 'checkbox_group','gastrointest' => 'textfield','genitouro' => 'checkbox_group','respiratory' => 'checkbox_group','head_neck' => 'checkbox_group','cardiovascular' => 'checkbox_group','women_health' => 'checkbox_group','alcoholism' => 'checkbox','anemia' => 'checkbox','arthritis' => 'checkbox','cancer' => 'checkbox','herpes' => 'checkbox','hypoglycemia' => 'checkbox','diabetes' => 'checkbox','eczema' => 'checkbox','goiter' => 'checkbox','ulcers' => 'checkbox','gout' => 'checkbox','heart_disease' => 'checkbox','rheumatic_fever' => 'checkbox','miscarriage' => 'checkbox','polio' => 'checkbox','pneumonia' => 'checkbox','atherosclerosis' => 'checkbox','multiple_sclerosis' => 'checkbox','stroke' => 'checkbox','tuberculosis' => 'checkbox','epilepsy' => 'checkbox','measles' => 'checkbox','chicken_pox' => 'checkbox','venereal_disease' => 'checkfield','organ_gland' => 'checkbox_group','family_history' => 'checkbox_group','regular_meals' => 'checkbox_group');
$negatives = array();
//process each field according to it's type
foreach($field_names as $key=>$val)
{
  $pos = '';
  $neg = '';
	if ($val == "checkbox")
	{
		if ($_POST[$key]) { $field_names[$key] = "yes"; }
		else { $field_names[$key] = "negative"; }
	}
	elseif (($val == "checkbox_group")||($val == "scrolling_list_multiples"))
	{
		if (is_array($_POST[$key]) && count($_POST[$key])>0) 
		{
			foreach($_POST[$key] as $var){ // concats list of hist forms
				$flagsum = 0;
				if (is_array($var) && count($var) > 0){
					foreach ($var as $sub_int => $sub_var){
						$flagsum += pow(2,$sub_int);
						$str_conditionname = $sub_var;
					}
				}
				else {$flagsum = 1; $str_conditionname = $var;}
				if ($pos == '') {$pos = $flagsum.$str_conditionname;}
				else {$pos = $pos.",".$flagsum.$str_conditionname;}
			}
			
		}
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
			if (is_array($negatives[$key])>0) 
			{
				$neg_len = count($negatives[$key]);
				for ($i = 0; $i < $neg_len; $i++){$neg = $neg ."0".$negatives[$key][$i];}
			}
		}
		$field_names[$key] = $pos.$neg;	
	}
	else
	{
		$field_names[$key] = $_POST[$key];
	}
        if ($field_names[$key] != '')
        {
			// $field_names[$key] .= '.';
			// $field_names[$key] = preg_replace('/\s*,\s*([^,]+)\./',' and $1.',$field_names[$key]); // replace last comma with 'and' and ending period
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
	$newid = formSubmit("form_chiro_history", $field_names, $_GET["id"], $userauthorized);
	addForm($encounter, "chiro_history", $newid, "chiro_history", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_chiro_history set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), approximate_onset_date='".$field_names["approximate_onset_date"]."',general_condit='".$field_names["general_condit"]."', muscle_joint='".$field_names["muscle_joint"]."', pain_numb_locat='".$field_names["pain_numb_locat"]."', gastrointest='".$field_names["gastrointest"]."',genitouro='".$field_names["genitouro"]."',respiratory='".$field_names["respiratory"]."',head_neck='".$field_names["head_neck"]."',cardiovascular='".$field_names["cardiovascular"]."',women_health='".$field_names["women_health"]."',alcoholism='".$field_names["alcoholism"]."',anemia='".$field_names["anemia"]."',arthritis='".$field_names["arthritis"]."',cancer='".$field_names["cancer"]."',herpes='".$field_names["herpes"]."',hypoglycemia='".$field_names["hypoglycemia"]."',diabetes='".$field_names["diabetes"]."',eczema='".$field_names["eczema"]."',goiter='".$field_names["goiter"]."',ulcers='".$field_names["ulcers"]."',gout='".$field_names["gout"]."',heart_disease='".$field_names["heart_disease"]."',rheumatic_fever='".$field_names["rheumatic_fever"]."',miscarriage='".$field_names["miscarriage"]."',polio='".$field_names["polio"]."',pneumonia='".$field_names["pneumonia"]."',atherosclerosis='".$field_names["atherosclerosis"]."',multiple_sclerosis='".$field_names["multiple_sclerosis"]."',stroke='".$field_names["stroke"]."',tuberculosis='".$field_names["tuberculosis"]."',epilepsy='".$field_names["epilepsy"]."',measles='".$field_names["measles"]."',chicken_pox='".$field_names["chicken_pox"]."',venereal_disease='".$field_names["venereal_disease"]."',organ_gland='".$field_names["organ_gland"]."',family_history='".$field_names["family_history"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
