<?php
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
require_once("$srcdir/formdata.inc.php");

//process form variables here
//create an array of all of the existing field names
$field_names = array('serum_il_1beta' => 'textfield','serum_il_6' => 'textfield','serum_tnf_alpha' => 'textfield','probability_of_endometriosis' => 'textfield');
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
$newid = formSubmit("form_Endometriosis_Serology", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Endometriosis_Serology", $newid, "Endometriosis_Serology", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Endometriosis_Serology set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), serum_il_1beta='".$field_names["serum_il_1beta"]."',serum_il_6='".$field_names["serum_il_6"]."',serum_tnf_alpha='".$field_names["serum_tnf_alpha"]."',probability_of_endometriosis='".$field_names["probability_of_endometriosis"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
