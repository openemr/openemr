<?php
//================================================
//Form Created by
//Z&H Healthcare Solutions, LLC.
//www.zhservices.com
//sam@zhholdings.com
//Initial New Patient Physical Exam
//================================================
//------------This file inserts your field data into the MySQL database
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
require_once("$srcdir/formdata.inc.php");

//process form variables here
//create an array of all of the existing field names
$field_names = array('sweeter' => 'textfield','bloater' => 'textfield','grazer' => 'textfield','general' => 'checkbox_group','head' => 'checkbox_group','eyes' => 'checkbox_group','ears' => 'checkbox_group','nose' => 'checkbox_group','throat' => 'checkbox_group','oral_cavity' => 'checkbox_group','dentition' => 'checkbox_group','neck' => 'checkbox_group','heart' => 'checkbox_group','lung' => 'checkbox_group','chest' => 'checkbox_group','breast' => 'checkbox_group','male' => 'checkbox_group','female' => 'checkbox_group','note' => 'textfield','abdomen' => 'checkbox_group','scar' => 'checkbox_group','umbilius' => 'checkbox_group','groins' => 'checkbox_group','extremities' => 'checkbox_group','peripheral_pulses' => 'checkbox_group','right_peripheral_pulses' => 'checkbox_group','left_peripheral_pulses' => 'checkbox_group','neurological' => 'checkbox_group','right_neurological' => 'checkbox_group','left_neurological' => 'checkbox_group','rectum' => 'checkbox_group','pelvic' => 'checkbox_group','assessment' => 'checkbox_group','note2' => 'textarea','recommendations' => 'checkbox_group','note3' => 'textarea');
$negatives = array('general' => array('Alert' => 'Alert','Oriented X3' => 'Oriented X3','Not in distress' => 'Not in distress','In distress' => 'In distress','Well developed' => 'Well developed','Well nourished' => 'Well nourished','Petite' => 'Petite','Obese' => 'Obese'),'head' => array('AT NC' => 'AT NC','Hirsutism  Facial hairs' => 'Hirsutism  Facial hairs'),'eyes' => array('PERRLA' => 'PERRLA','EOMI' => 'EOMI','Anicteric' => 'Anicteric','Pink' => 'Pink','Pale' => 'Pale','Icteric' => 'Icteric','Cataracts' => 'Cataracts'),'ears' => array('Normal' => 'Normal','TM w  good light reflex' => 'TM w  good light reflex'),'nose' => array('Normal' => 'Normal','Patent' => 'Patent','No discharge' => 'No discharge','Discharge' => 'Discharge'),'throat' => array('Normal' => 'Normal','Erythematous' => 'Erythematous'),'oral_cavity' => array('No lesions' => 'No lesions','Lesions' => 'Lesions','Friable gums' => 'Friable gums'),'dentition' => array('Good' => 'Good','Fair' => 'Fair','Poor' => 'Poor'),'neck' => array('No lymphadenopathy' => 'No lymphadenopathy','No thyromegally' => 'No thyromegally','No Bruit' => 'No Bruit','FROM' => 'FROM','Lymphadenopathy' => 'Lymphadenopathy','Thyromegally' => 'Thyromegally','Right Bruit' => 'Right Bruit','Left Bruit' => 'Left Bruit'),'heart' => array('NSR' => 'NSR','S1 S2' => 'S1 S2','No murmur' => 'No murmur','Irregular rate' => 'Irregular rate','Irreg rhythm' => 'Irreg rhythm','Murmur' => 'Murmur','Gallop' => 'Gallop'),'lung' => array('Clear to ascultation' => 'Clear to ascultation','No rales' => 'No rales','No wheezes' => 'No wheezes','No rhonchi' => 'No rhonchi','Distant' => 'Distant','Rales' => 'Rales','Wheezes' => 'Wheezes','Rhonchi' => 'Rhonchi'),'chest' => array('No palpable tenderness' => 'No palpable tenderness','Palpable tenderness' => 'Palpable tenderness'),'breast' => array('Did not examine' => 'Did not examine'),'male' => array('Normal' => 'Normal','Gynecomastia' => 'Gynecomastia','Palpable mass' => 'Palpable mass'),'female' => array('Normal size' => 'Normal size','Normal exam' => 'Normal exam','Enlarged' => 'Enlarged','Pendulous' => 'Pendulous','Palpable mass' => 'Palpable mass','Tender' => 'Tender','Erythematous' => 'Erythematous','Peau d orange' => 'Peau d orange'),'abdomen' => array('NABS' => 'NABS','Soft' => 'Soft','Non tender' => 'Non tender','Non distended' => 'Non distended','Obese' => 'Obese','Hepatomegaly' => 'Hepatomegaly','Ascites' => 'Ascites','Tender' => 'Tender','Distended' => 'Distended','Guarding' => 'Guarding','Rebound tenderness' => 'Rebound tenderness','CVA tenderness' => 'CVA tenderness'),'scar' => array('Upper midline' => 'Upper midline','Lower midline' => 'Lower midline','Rt subcostal' => 'Rt subcostal','Lt subcostal' => 'Lt subcostal','Rt inguinal' => 'Rt inguinal','Lt inguinal' => 'Lt inguinal','Paramedian' => 'Paramedian','Pfanennsteil' => 'Pfanennsteil','Upper Transverse' => 'Upper Transverse','Lower Transverse' => 'Lower Transverse','Laparoscopic' => 'Laparoscopic','McBurney s' => 'McBurney s'),'umbilius' => array('Normal' => 'Normal','Hernia' => 'Hernia','Lymphadenopathy' => 'Lymphadenopathy'),'groins' => array('Normal' => 'Normal','Rt hernia' => 'Rt hernia','Lt hernia' => 'Lt hernia','Rash' => 'Rash','Lymphadenopathy' => 'Lymphadenopathy'),'extremities' => array('Warm' => 'Warm','Dry' => 'Dry','No edema' => 'No edema','No calf tenderness' => 'No calf tenderness','Pitting edema' => 'Pitting edema','Stasis dermatitis' => 'Stasis dermatitis','Varicosities' => 'Varicosities','Calf tenderness' => 'Calf tenderness','Acanthosis Nigricans' => 'Acanthosis Nigricans','Spider Angiomas' => 'Spider Angiomas','Palmar Erythema' => 'Palmar Erythema','Hirsutism' => 'Hirsutism'),'peripheral_pulses' => array('Did not examine' => 'Did not examine','2 pulses' => '2 pulses'),'right_peripheral_pulses' => array('Radial' => 'Radial','Inguinal' => 'Inguinal','Popliteal' => 'Popliteal','DP' => 'DP','PT' => 'PT'),'left_peripheral_pulses' => array('Radial' => 'Radial','Inguinal' => 'Inguinal','Popliteal' => 'Popliteal','DP' => 'DP','PT' => 'PT'),'neurological' => array('Did not examine' => 'Did not examine','Grossly normal' => 'Grossly normal'),'right_neurological' => array('Normal strength   DTR s' => 'Normal strength   DTR s','Abn grip strength' => 'Abn grip strength','Abn arm strength' => 'Abn arm strength','Abn leg strength' => 'Abn leg strength','Abn DTR s' => 'Abn DTR s'),'left_neurological' => array('Normal strength   DTR s' => 'Normal strength   DTR s','Abn grip strength' => 'Abn grip strength','Abn arm strength' => 'Abn arm strength','Abn leg strength' => 'Abn leg strength','Abn DTR s' => 'Abn DTR s'),'rectum' => array('Did not examine' => 'Did not examine','Normal' => 'Normal','Palpable mass' => 'Palpable mass','Enlarged prostate' => 'Enlarged prostate','Hemorrhoids' => 'Hemorrhoids','Fissure  Fistula' => 'Fissure  Fistula'),'pelvic' => array('Did not examine' => 'Did not examine','Nomal' => 'Nomal','CM tenderness' => 'CM tenderness','Rt adnexal mass' => 'Rt adnexal mass','Lt adnexal mass' => 'Lt adnexal mass'),'assessment' => array('Morbid obesity' => 'Morbid obesity','DVT' => 'DVT','Hernia Inguinal' => 'Hernia Inguinal','Lower Back Pain' => 'Lower Back Pain','Asthma' => 'Asthma','Failed prev wt loss surgery' => 'Failed prev wt loss surgery','Hernia Internal' => 'Hernia Internal','Osteoarthritis' => 'Osteoarthritis','CHF' => 'CHF','Fatty Liver' => 'Fatty Liver','Hypercholesterolemia' => 'Hypercholesterolemia','Panniculitis' => 'Panniculitis','Coronary Artery Dz' => 'Coronary Artery Dz','Gallbladder Dz' => 'Gallbladder Dz','Hernia Umbilical' => 'Hernia Umbilical','PVD' => 'PVD','COPD' => 'COPD','GERD' => 'GERD','Hypertension' => 'Hypertension','Sleep Apnea' => 'Sleep Apnea','Depression' => 'Depression','Hernia Hiatal' => 'Hernia Hiatal','Hypertriglyceridemia' => 'Hypertriglyceridemia','Urinary Incontinence' => 'Urinary Incontinence','Diabetes' => 'Diabetes','Hernia Incisional' => 'Hernia Incisional','Hypothyroidism' => 'Hypothyroidism','Venous Stasis Dz' => 'Venous Stasis Dz'),'recommendations' => array('VBG Vertical Banded Gastroplasty' => 'VBG Vertical Banded Gastroplasty','PRYGBP Proximal Roux en Y Gastric Bypass' => 'PRYGBP Proximal Roux en Y Gastric Bypass','SG Sleeve Gastrectomy' => 'SG Sleeve Gastrectomy','MRYGBP Medial Roux en Y Gastric Bypass' => 'MRYGBP Medial Roux en Y Gastric Bypass','ABG Adjustable Banded Gastroplasty' => 'ABG Adjustable Banded Gastroplasty','DRYGBP Distal Roux en Y Gastric Bypass' => 'DRYGBP Distal Roux en Y Gastric Bypass','Gastric Restrictive Procedure other than VBG  ABG' => 'Gastric Restrictive Procedure other than VBG  ABG','Duodenal Switch Procedure' => 'Duodenal Switch Procedure','Revision of Gastric Restrictive Procedure' => 'Revision of Gastric Restrictive Procedure','BPD Biliopancreatic Diversion' => 'BPD Biliopancreatic Diversion','Lysis of Adhesions' => 'Lysis of Adhesions','Liver Biopsy' => 'Liver Biopsy','Hiatal Hernia Repair w  Fundoplication' => 'Hiatal Hernia Repair w  Fundoplication','Hiatal Hernia Repair w o Fundoplication' => 'Hiatal Hernia Repair w o Fundoplication','Vagotomy   Pyloraplasty' => 'Vagotomy   Pyloraplasty','Abdominoplasty' => 'Abdominoplasty','Appendectomy possible' => 'Appendectomy possible','Cholecystectomy possible' => 'Cholecystectomy possible','EGD Esophagogastroduodenoscopy' => 'EGD Esophagogastroduodenoscopy','Colonoscopy' => 'Colonoscopy'));
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
$newid = formSubmit("form_Initial_New_Patient_Physical_Exam", $field_names, $_GET["id"], $userauthorized);
addForm($encounter, "Initial New Patient Physical Exam", $newid, "Initial_New_Patient_Physical_Exam", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_Initial_New_Patient_Physical_Exam set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), sweeter='".$field_names["sweeter"]."',bloater='".$field_names["bloater"]."',grazer='".$field_names["grazer"]."',general='".$field_names["general"]."',head='".$field_names["head"]."',eyes='".$field_names["eyes"]."',ears='".$field_names["ears"]."',nose='".$field_names["nose"]."',throat='".$field_names["throat"]."',oral_cavity='".$field_names["oral_cavity"]."',dentition='".$field_names["dentition"]."',neck='".$field_names["neck"]."',heart='".$field_names["heart"]."',lung='".$field_names["lung"]."',chest='".$field_names["chest"]."',breast='".$field_names["breast"]."',male='".$field_names["male"]."',female='".$field_names["female"]."',note='".$field_names["note"]."',abdomen='".$field_names["abdomen"]."',scar='".$field_names["scar"]."',umbilius='".$field_names["umbilius"]."',groins='".$field_names["groins"]."',extremities='".$field_names["extremities"]."',peripheral_pulses='".$field_names["peripheral_pulses"]."',right_peripheral_pulses='".$field_names["right_peripheral_pulses"]."',left_peripheral_pulses='".$field_names["left_peripheral_pulses"]."',neurological='".$field_names["neurological"]."',right_neurological='".$field_names["right_neurological"]."',left_neurological='".$field_names["left_neurological"]."',rectum='".$field_names["rectum"]."',pelvic='".$field_names["pelvic"]."',assessment='".$field_names["assessment"]."',note2='".$field_names["note2"]."',recommendations='".$field_names["recommendations"]."',note3='".$field_names["note3"]."' where id=$id");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
?>
