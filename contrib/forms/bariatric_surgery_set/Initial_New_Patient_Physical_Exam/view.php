 <?php 
//================================================
//Form Created by
//Z&H Healthcare Solutions, LLC.
//www.zhservices.com
//sam@zhholdings.com
//Initial New Patient Physical Exam
//================================================
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 $obj = formFetch("form_Initial_New_Patient_Physical_Exam", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
 function chkdata_Txt(&$obj, $var) { 
         return htmlspecialchars($obj{"$var"},ENT_QUOTES); 
 } 
 function chkdata_Date(&$obj, $var) { 
         return htmlspecialchars($obj{"$var"},ENT_QUOTES); 
 } 
 function chkdata_CB(&$obj, $nam, $var) { 
 	if (preg_match("/Negative.*$var/",$obj{$nam})) {return;} else {return "checked";} 
 } 
 function chkdata_Radio(&$obj, $nam, $var) { 
 	if (strpos($obj{$nam},$var) !== false) {return "checked";} 
 } 
  function chkdata_PopOrScroll(&$obj, $nam, $var) { 
 	if (preg_match("/Negative.*$var/",$obj{$nam})) {return;} else {return "selected";} 
 } 
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
 ?> 
<html><head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
 <form method=post action="<?php echo $rootdir?>/forms/Initial_New_Patient_Physical_Exam/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onSubmit="return top.restoreSession()">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td align="center" colspan="5"><h3> <?php xl("015 Initial New Patient Physical Exam - ",'e') ?> </h3></td>
  </tr>

  <tr>
    <td align="right"><b class="text"> <?php xl("EATING HABITS:",'e') ?> </b></td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
  </tr>

 
  
 <tr><td align="center"  colspan="5">
 <table border="0" cellspacing="0" cellpadding="0">
 <tr><td><label class="text"><?php xl("% Sweeter",'e') ?></label> </td> <td>&nbsp;<input type="text" name="sweeter" value="<?php $result = chkdata_Txt($obj,"sweeter"); echo $result;?>"></td></tr> 
 
 <tr><td> <label class="text"><?php xl("% Bloater",'e') ?></label> </td> <td>&nbsp;<input type="text" name="bloater" value="<?php $result = chkdata_Txt($obj,"bloater"); echo $result;?>"></td></tr> 
 
 <tr><td> <label class="text"><?php xl("% Grazer",'e') ?></label> </td> <td>&nbsp;<input type="text" name="grazer" value="<?php $result = chkdata_Txt($obj,"grazer"); echo $result;?>"></td></tr> 
</table>
</td></tr>  
   <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>
 
  <tr>
    <td align="left" colspan="5"><b class="text"> <?php xl("PHYSICAL EXAMINATION:",'e') ?> </b></td>
  </tr>
  
   <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 <tr><td align="right" width="150"> <b class="text"><?php xl("GENERAL:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="general[]" value="Alert" <?php $result = chkdata_CB($obj,"general","Alert"); echo $result;?>> <?php xl("Alert",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="Oriented X3" <?php $result = chkdata_CB($obj,"general","Oriented X3"); echo $result;?>> <?php xl("Oriented X3",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="Not in distress" <?php $result = chkdata_CB($obj,"general","Not in distress"); echo $result;?>> <?php xl("Not in distress",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="In distress" <?php $result = chkdata_CB($obj,"general","In distress"); echo $result;?>> <?php xl("In distress",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="general[]" value="Well developed" <?php $result = chkdata_CB($obj,"general","Well developed"); echo $result;?>> <?php xl("Well developed",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="Well nourished" <?php $result = chkdata_CB($obj,"general","Well nourished"); echo $result;?>> <?php xl("Well nourished",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="Petite" <?php $result = chkdata_CB($obj,"general","Petite"); echo $result;?>> <?php xl("Petite",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="general[]" value="Obese" <?php $result = chkdata_CB($obj,"general","Obese"); echo $result;?>> <?php xl("Obese",'e') ?> </label></td></tr> 
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
  <tr>
    <td align="right"><b class="text"> <?php xl("HEENT:",'e') ?> </b></td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
  </tr>
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Head",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="head[]" value="AT NC" <?php $result = chkdata_CB($obj,"head","AT NC"); echo $result;?>> <?php xl("AT/NC",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="head[]" value="Hirsutism  Facial hairs" <?php $result = chkdata_CB($obj,"head","Hirsutism  Facial hairs"); echo $result;?>> <?php xl("Hirsutism/Facial hairs",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Eyes",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="eyes[]" value="PERRLA" <?php $result = chkdata_CB($obj,"eyes","PERRLA"); echo $result;?>> <?php xl("PERRLA",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="eyes[]" value="EOMI" <?php $result = chkdata_CB($obj,"eyes","EOMI"); echo $result;?>> <?php xl("EOMI",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="eyes[]" value="Anicteric" <?php $result = chkdata_CB($obj,"eyes","Anicteric"); echo $result;?>> <?php xl("Anicteric",'e') ?> </label></td><td> 
 </td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="eyes[]" value="Pink" <?php $result = chkdata_CB($obj,"eyes","Pink"); echo $result;?>> <?php xl("Pink",'e') ?> </label> 
 </td><td> <label class="text"><input type="checkbox" name="eyes[]" value="Pale" <?php $result = chkdata_CB($obj,"eyes","Pale"); echo $result;?>> <?php xl("Pale",'e') ?> </label>
 </td><td><label class="text"><input type="checkbox" name="eyes[]" value="Icteric" <?php $result = chkdata_CB($obj,"eyes","Icteric"); echo $result;?>> <?php xl("Icteric",'e') ?> </label> 
 </td><td><label class="text"><input type="checkbox" name="eyes[]" value="Cataracts" <?php $result = chkdata_CB($obj,"eyes","Cataracts"); echo $result;?>> <?php xl("Cataracts",'e') ?> </label></td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Ears",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="ears[]" value="Normal" <?php $result = chkdata_CB($obj,"ears","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="ears[]" value="TM w  good light reflex" <?php $result = chkdata_CB($obj,"ears","TM w  good light reflex"); echo $result;?>> <?php xl("TM w/  good light reflex",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Nose",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="nose[]" value="Normal" <?php $result = chkdata_CB($obj,"nose","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="nose[]" value="Patent" <?php $result = chkdata_CB($obj,"nose","Patent"); echo $result;?>> <?php xl("Patent",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="nose[]" value="No discharge" <?php $result = chkdata_CB($obj,"nose","No discharge"); echo $result;?>> <?php xl("No discharge",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="nose[]" value="Discharge" <?php $result = chkdata_CB($obj,"nose","Discharge"); echo $result;?>> <?php xl("Discharge",'e') ?> </label></td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Throat",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="throat[]" value="Normal" <?php $result = chkdata_CB($obj,"throat","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="throat[]" value="Erythematous" <?php $result = chkdata_CB($obj,"throat","Erythematous"); echo $result;?>> <?php xl("Erythematous",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Oral cavity",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="oral_cavity[]" value="No lesions" <?php $result = chkdata_CB($obj,"oral_cavity","No lesions"); echo $result;?>> <?php xl("No lesions",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="oral_cavity[]" value="Lesions" <?php $result = chkdata_CB($obj,"oral_cavity","Lesions"); echo $result;?>> <?php xl("Lesions",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="oral_cavity[]" value="Friable gums" <?php $result = chkdata_CB($obj,"oral_cavity","Friable gums"); echo $result;?>> <?php xl("Friable gums",'e') ?> </label></td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Dentition",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="dentition[]" value="Good" <?php $result = chkdata_CB($obj,"dentition","Good"); echo $result;?>> <?php xl("Good",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="dentition[]" value="Fair" <?php $result = chkdata_CB($obj,"dentition","Fair"); echo $result;?>> <?php xl("Fair",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="dentition[]" value="Poor" <?php $result = chkdata_CB($obj,"dentition","Poor"); echo $result;?>> <?php xl("Poor",'e') ?> </label></td><td>&nbsp;</td></tr> 
  
  
  
   <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 <tr><td align="right" width="150"> <b class="text"><?php xl("NECK:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="neck[]" value="No lymphadenopathy" <?php $result = chkdata_CB($obj,"neck","No lymphadenopathy"); echo $result;?>> <?php xl("No lymphadenopathy",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="No thyromegally" <?php $result = chkdata_CB($obj,"neck","No thyromegally"); echo $result;?>> <?php xl("No thyromegally",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="No Bruit" <?php $result = chkdata_CB($obj,"neck","No Bruit"); echo $result;?>> <?php xl("No Bruit",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="FROM" <?php $result = chkdata_CB($obj,"neck","FROM"); echo $result;?>> <?php xl("FROM",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="neck[]" value="Lymphadenopathy" <?php $result = chkdata_CB($obj,"neck","Lymphadenopathy"); echo $result;?>> <?php xl("Lymphadenopathy",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="Thyromegally" <?php $result = chkdata_CB($obj,"neck","Thyromegally"); echo $result;?>> <?php xl("Thyromegally",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="Right Bruit" <?php $result = chkdata_CB($obj,"neck","Right Bruit"); echo $result;?>> <?php xl("Right Bruit",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neck[]" value="Left Bruit" <?php $result = chkdata_CB($obj,"neck","Left Bruit"); echo $result;?>> <?php xl("Left Bruit",'e') ?> </label></td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("HEART:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="heart[]" value="NSR" <?php $result = chkdata_CB($obj,"heart","NSR"); echo $result;?>> <?php xl("NSR",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="heart[]" value="S1 S2" <?php $result = chkdata_CB($obj,"heart","S1 S2"); echo $result;?>> <?php xl("S1/S2",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="heart[]" value="No murmur" <?php $result = chkdata_CB($obj,"heart","No murmur"); echo $result;?>> <?php xl("No murmur",'e') ?> </label></td><td> 
 </td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="heart[]" value="Irregular rate" <?php $result = chkdata_CB($obj,"heart","Irregular rate"); echo $result;?>> <?php xl("Irregular rate",'e') ?> </label>
 </td><td> <label class="text"><input type="checkbox" name="heart[]" value="Irreg rhythm" <?php $result = chkdata_CB($obj,"heart","Irreg rhythm"); echo $result;?>> <?php xl("Irreg rhythm",'e') ?> </label>
 </td><td> <label class="text"><input type="checkbox" name="heart[]" value="Murmur" <?php $result = chkdata_CB($obj,"heart","Murmur"); echo $result;?>> <?php xl("Murmur",'e') ?> </label>
 </td><td><label class="text"><input type="checkbox" name="heart[]" value="Gallop" <?php $result = chkdata_CB($obj,"heart","Gallop"); echo $result;?>> <?php xl("Gallop",'e') ?> </label></td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("LUNG:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="lung[]" value="Clear to ascultation" <?php $result = chkdata_CB($obj,"lung","Clear to ascultation"); echo $result;?>> <?php xl("Clear to ascultation",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="No rales" <?php $result = chkdata_CB($obj,"lung","No rales"); echo $result;?>> <?php xl("No rales",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="No wheezes" <?php $result = chkdata_CB($obj,"lung","No wheezes"); echo $result;?>> <?php xl("No wheezes",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="No rhonchi" <?php $result = chkdata_CB($obj,"lung","No rhonchi"); echo $result;?>> <?php xl("No rhonchi",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="lung[]" value="Distant" <?php $result = chkdata_CB($obj,"lung","Distant"); echo $result;?>> <?php xl("Distant",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="Rales" <?php $result = chkdata_CB($obj,"lung","Rales"); echo $result;?>> <?php xl("Rales",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="Wheezes" <?php $result = chkdata_CB($obj,"lung","Wheezes"); echo $result;?>> <?php xl("Wheezes",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="lung[]" value="Rhonchi" <?php $result = chkdata_CB($obj,"lung","Rhonchi"); echo $result;?>> <?php xl("Rhonchi",'e') ?> </label></td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("CHEST:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="chest[]" value="No palpable tenderness" <?php $result = chkdata_CB($obj,"chest","No palpable tenderness"); echo $result;?>> <?php xl("No palpable tenderness",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="chest[]" value="Palpable tenderness" <?php $result = chkdata_CB($obj,"chest","Palpable tenderness"); echo $result;?>> <?php xl("Palpable tenderness",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("BREAST:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="breast[]" value="Did not examine" <?php $result = chkdata_CB($obj,"breast","Did not examine"); echo $result;?>> <?php xl("Did not examine",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Male",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="male[]" value="Normal" <?php $result = chkdata_CB($obj,"male","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="male[]" value="Gynecomastia" <?php $result = chkdata_CB($obj,"male","Gynecomastia"); echo $result;?>> <?php xl("Gynecomastia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="male[]" value="Palpable mass" <?php $result = chkdata_CB($obj,"male","Palpable mass"); echo $result;?>> <?php xl("Palpable mass",'e') ?> </label></td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Female",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="female[]" value="Normal size" <?php $result = chkdata_CB($obj,"female","Normal size"); echo $result;?>> <?php xl("Normal size",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Normal exam" <?php $result = chkdata_CB($obj,"female","Normal exam"); echo $result;?>> <?php xl("Normal exam",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Enlarged" <?php $result = chkdata_CB($obj,"female","Enlarged"); echo $result;?>> <?php xl("Enlarged",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Pendulous" <?php $result = chkdata_CB($obj,"female","Pendulous"); echo $result;?>> <?php xl("Pendulous",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="female[]" value="Palpable mass" <?php $result = chkdata_CB($obj,"female","Palpable mass"); echo $result;?>> <?php xl("Palpable mass",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Tender" <?php $result = chkdata_CB($obj,"female","Tender"); echo $result;?>> <?php xl("Tender",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Erythematous" <?php $result = chkdata_CB($obj,"female","Erythematous"); echo $result;?>> <?php xl("Erythematous",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="female[]" value="Peau d orange" <?php $result = chkdata_CB($obj,"female","Peau d orange"); echo $result;?>> <?php xl("Peau d'orange",'e') ?> </label></td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"></td> <td colspan="4"><input size="80" type="text" name="note" value="<?php $result = chkdata_Txt($obj,"note"); echo $result;?>"></td></tr> 
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("ABDOMEN:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="abdomen[]" value="NABS" <?php $result = chkdata_CB($obj,"abdomen","NABS"); echo $result;?>> <?php xl("NABS",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Soft" <?php $result = chkdata_CB($obj,"abdomen","Soft"); echo $result;?>> <?php xl("Soft",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Non tender" <?php $result = chkdata_CB($obj,"abdomen","Non tender"); echo $result;?>> <?php xl("Non-tender",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Non distended" <?php $result = chkdata_CB($obj,"abdomen","Non distended"); echo $result;?>> <?php xl("Non-distended",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="abdomen[]" value="Obese" <?php $result = chkdata_CB($obj,"abdomen","Obese"); echo $result;?>> <?php xl("Obese",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Hepatomegaly" <?php $result = chkdata_CB($obj,"abdomen","Hepatomegaly"); echo $result;?>> <?php xl("Hepatomegaly",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Ascites" <?php $result = chkdata_CB($obj,"abdomen","Ascites"); echo $result;?>> <?php xl("Ascites",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Tender" <?php $result = chkdata_CB($obj,"abdomen","Tender"); echo $result;?>> <?php xl("Tender",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Distended" <?php $result = chkdata_CB($obj,"abdomen","Distended"); echo $result;?>> <?php xl("Distended",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Guarding" <?php $result = chkdata_CB($obj,"abdomen","Guarding"); echo $result;?>> <?php xl("Guarding",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="Rebound tenderness" <?php $result = chkdata_CB($obj,"abdomen","Rebound tenderness"); echo $result;?>> <?php xl("Rebound tenderness",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="abdomen[]" value="CVA tenderness" <?php $result = chkdata_CB($obj,"abdomen","CVA tenderness"); echo $result;?>> <?php xl("CVA tenderness",'e') ?> </label></td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Scar",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="scar[]" value="Upper midline" <?php $result = chkdata_CB($obj,"scar","Upper midline"); echo $result;?>> <?php xl("Upper midline",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Lower midline" <?php $result = chkdata_CB($obj,"scar","Lower midline"); echo $result;?>> <?php xl("Lower midline",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Rt subcostal" <?php $result = chkdata_CB($obj,"scar","Rt subcostal"); echo $result;?>> <?php xl("Rt subcostal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Lt subcostal" <?php $result = chkdata_CB($obj,"scar","Lt subcostal"); echo $result;?>> <?php xl("Lt subcostal",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="scar[]" value="Rt inguinal" <?php $result = chkdata_CB($obj,"scar","Rt inguinal"); echo $result;?>> <?php xl("Rt inguinal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Lt inguinal" <?php $result = chkdata_CB($obj,"scar","Lt inguinal"); echo $result;?>> <?php xl("Lt inguinal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Paramedian" <?php $result = chkdata_CB($obj,"scar","Paramedian"); echo $result;?>> <?php xl("Paramedian",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Pfanennsteil" <?php $result = chkdata_CB($obj,"scar","Pfanennsteil"); echo $result;?>> <?php xl("Pfanennsteil",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="scar[]" value="Upper Transverse" <?php $result = chkdata_CB($obj,"scar","Upper Transverse"); echo $result;?>> <?php xl("Upper Transverse",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Lower Transverse" <?php $result = chkdata_CB($obj,"scar","Lower Transverse"); echo $result;?>> <?php xl("Lower Transverse",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="Laparoscopic" <?php $result = chkdata_CB($obj,"scar","Laparoscopic"); echo $result;?>> <?php xl("Laparoscopic",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="scar[]" value="McBurney s" <?php $result = chkdata_CB($obj,"scar","McBurney s"); echo $result;?>> <?php xl("McBurney's",'e') ?> </label></td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("UMBILIUS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="umbilius[]" value="Normal" <?php $result = chkdata_CB($obj,"umbilius","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="umbilius[]" value="Hernia" <?php $result = chkdata_CB($obj,"umbilius","Hernia"); echo $result;?>> <?php xl("Hernia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="umbilius[]" value="Lymphadenopathy" <?php $result = chkdata_CB($obj,"umbilius","Lymphadenopathy"); echo $result;?>> <?php xl("Lymphadenopathy",'e') ?> </label></td><td>&nbsp;</td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("GROINS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="groins[]" value="Normal" <?php $result = chkdata_CB($obj,"groins","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="groins[]" value="Rt hernia" <?php $result = chkdata_CB($obj,"groins","Rt hernia"); echo $result;?>> <?php xl("Rt hernia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="groins[]" value="Lt hernia" <?php $result = chkdata_CB($obj,"groins","Lt hernia"); echo $result;?>> <?php xl("Lt hernia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="groins[]" value="Rash" <?php $result = chkdata_CB($obj,"groins","Rash"); echo $result;?>> <?php xl("Rash",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="groins[]" value="Lymphadenopathy" <?php $result = chkdata_CB($obj,"groins","Lymphadenopathy"); echo $result;?>> <?php xl("Lymphadenopathy",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("EXTREMITIES:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="extremities[]" value="Warm" <?php $result = chkdata_CB($obj,"extremities","Warm"); echo $result;?>> <?php xl("Warm",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Dry" <?php $result = chkdata_CB($obj,"extremities","Dry"); echo $result;?>> <?php xl("Dry",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="No edema" <?php $result = chkdata_CB($obj,"extremities","No edema"); echo $result;?>> <?php xl("No edema",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="No calf tenderness" <?php $result = chkdata_CB($obj,"extremities","No calf tenderness"); echo $result;?>> <?php xl("No calf tenderness",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="extremities[]" value="Pitting edema" <?php $result = chkdata_CB($obj,"extremities","Pitting edema"); echo $result;?>> <?php xl("Pitting edema",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Stasis dermatitis" <?php $result = chkdata_CB($obj,"extremities","Stasis dermatitis"); echo $result;?>> <?php xl("Stasis dermatitis",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Varicosities" <?php $result = chkdata_CB($obj,"extremities","Varicosities"); echo $result;?>> <?php xl("Varicosities",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Calf tenderness" <?php $result = chkdata_CB($obj,"extremities","Calf tenderness"); echo $result;?>> <?php xl("Calf tenderness",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="extremities[]" value="Acanthosis Nigricans" <?php $result = chkdata_CB($obj,"extremities","Acanthosis Nigricans"); echo $result;?>> <?php xl("Acanthosis Nigricans",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Spider Angiomas" <?php $result = chkdata_CB($obj,"extremities","Spider Angiomas"); echo $result;?>> <?php xl("Spider Angiomas",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Palmar Erythema" <?php $result = chkdata_CB($obj,"extremities","Palmar Erythema"); echo $result;?>> <?php xl("Palmar Erythema",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="extremities[]" value="Hirsutism" <?php $result = chkdata_CB($obj,"extremities","Hirsutism"); echo $result;?>> <?php xl("Hirsutism",'e') ?> </label></td></tr> 
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
  <tr>
    <td align="right"><b class="text"> <?php xl("VASCULAR:",'e') ?> </b></td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
    <td align="left">&nbsp;</td>
  </tr>
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Peripheral pulses",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="peripheral_pulses[]" value="Did not examine" <?php $result = chkdata_CB($obj,"peripheral_pulses","Did not examine"); echo $result;?>> <?php xl("Did not examine",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="peripheral_pulses[]" value="2 pulses" <?php $result = chkdata_CB($obj,"peripheral_pulses","2 pulses"); echo $result;?>> <?php xl("2+ pulses",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Right",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Radial" <?php $result = chkdata_CB($obj,"right_peripheral_pulses","Radial"); echo $result;?>> <?php xl("Radial",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Inguinal" <?php $result = chkdata_CB($obj,"right_peripheral_pulses","Inguinal"); echo $result;?>> <?php xl("Inguinal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Popliteal" <?php $result = chkdata_CB($obj,"right_peripheral_pulses","Popliteal"); echo $result;?>> <?php xl("Popliteal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="DP" <?php $result = chkdata_CB($obj,"right_peripheral_pulses","DP"); echo $result;?>> <?php xl("DP",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="PT" <?php $result = chkdata_CB($obj,"right_peripheral_pulses","PT"); echo $result;?>> <?php xl("PT",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Left",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Radial" <?php $result = chkdata_CB($obj,"left_peripheral_pulses","Radial"); echo $result;?>> <?php xl("Radial",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Inguinal" <?php $result = chkdata_CB($obj,"left_peripheral_pulses","Inguinal"); echo $result;?>> <?php xl("Inguinal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Popliteal" <?php $result = chkdata_CB($obj,"left_peripheral_pulses","Popliteal"); echo $result;?>> <?php xl("Popliteal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="DP" <?php $result = chkdata_CB($obj,"left_peripheral_pulses","DP"); echo $result;?>> <?php xl("DP",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="PT" <?php $result = chkdata_CB($obj,"left_peripheral_pulses","PT"); echo $result;?>> <?php xl("PT",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
   <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 <tr><td align="right" width="150"> <b class="text"><?php xl("NEUROLOGICAL:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="neurological[]" value="Did not examine" <?php $result = chkdata_CB($obj,"neurological","Did not examine"); echo $result;?>> <?php xl("Did not examine",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="neurological[]" value="Grossly normal" <?php $result = chkdata_CB($obj,"neurological","Grossly normal"); echo $result;?>> <?php xl("Grossly normal",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Right",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="right_neurological[]" value="Normal strength   DTR s" <?php $result = chkdata_CB($obj,"right_neurological","Normal strength   DTR s"); echo $result;?>> <?php xl("Normal strength & DTR's",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn grip strength" <?php $result = chkdata_CB($obj,"right_neurological","Abn grip strength"); echo $result;?>> <?php xl("Abn grip strength",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn arm strength" <?php $result = chkdata_CB($obj,"right_neurological","Abn arm strength"); echo $result;?>> <?php xl("Abn arm strength",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn leg strength" <?php $result = chkdata_CB($obj,"right_neurological","Abn leg strength"); echo $result;?>> <?php xl("Abn leg strength",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn DTR s" <?php $result = chkdata_CB($obj,"right_neurological","Abn DTR s"); echo $result;?>> <?php xl("Abn DTR's",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("Left",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="left_neurological[]" value="Normal strength   DTR s" <?php $result = chkdata_CB($obj,"left_neurological","Normal strength   DTR s"); echo $result;?>> <?php xl("Normal strength & DTR's",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn grip strength" <?php $result = chkdata_CB($obj,"left_neurological","Abn grip strength"); echo $result;?>> <?php xl("Abn grip strength",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn arm strength" <?php $result = chkdata_CB($obj,"left_neurological","Abn arm strength"); echo $result;?>> <?php xl("Abn arm strength",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn leg strength" <?php $result = chkdata_CB($obj,"left_neurological","Abn leg strength"); echo $result;?>> <?php xl("Abn leg strength",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn DTR s" <?php $result = chkdata_CB($obj,"left_neurological","Abn DTR s"); echo $result;?>> <?php xl("Abn DTR's",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("RECTUM:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="rectum[]" value="Did not examine" <?php $result = chkdata_CB($obj,"rectum","Did not examine"); echo $result;?>> <?php xl("Did not examine",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="rectum[]" value="Normal" <?php $result = chkdata_CB($obj,"rectum","Normal"); echo $result;?>> <?php xl("Normal",'e') ?> </label></td><td> 
 </td><td> 
 </td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="rectum[]" value="Palpable mass" <?php $result = chkdata_CB($obj,"rectum","Palpable mass"); echo $result;?>> <?php xl("Palpable mass",'e') ?> </label>
 </td><td><label class="text"><input type="checkbox" name="rectum[]" value="Enlarged prostate" <?php $result = chkdata_CB($obj,"rectum","Enlarged prostate"); echo $result;?>> <?php xl("Enlarged prostate",'e') ?> </label> 
 </td><td><label class="text"><input type="checkbox" name="rectum[]" value="Hemorrhoids" <?php $result = chkdata_CB($obj,"rectum","Hemorrhoids"); echo $result;?>> <?php xl("Hemorrhoids",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="rectum[]" value="Fissure  Fistula" <?php $result = chkdata_CB($obj,"rectum","Fissure  Fistula"); echo $result;?>> <?php xl("Fissure/Fistula",'e') ?> </label></td></tr> 
  
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("PELVIC:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="pelvic[]" value="Did not examine" <?php $result = chkdata_CB($obj,"pelvic","Did not examine"); echo $result;?>> <?php xl("Did not examine",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="pelvic[]" value="Nomal" <?php $result = chkdata_CB($obj,"pelvic","Nomal"); echo $result;?>> <?php xl("Nomal",'e') ?> </label></td><td> 
 </td><td> 
</td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="pelvic[]" value="CM tenderness" <?php $result = chkdata_CB($obj,"pelvic","CM tenderness"); echo $result;?>> <?php xl("CM tenderness",'e') ?> </label>
 </td><td> <label class="text"><input type="checkbox" name="pelvic[]" value="Rt adnexal mass" <?php $result = chkdata_CB($obj,"pelvic","Rt adnexal mass"); echo $result;?>> <?php xl("Rt adnexal mass",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="pelvic[]" value="Lt adnexal mass" <?php $result = chkdata_CB($obj,"pelvic","Lt adnexal mass"); echo $result;?>> <?php xl("Lt adnexal mass",'e') ?> </label></td><td>&nbsp;</td></tr> 
  
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("ASSESSMENT:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="assessment[]" value="Morbid obesity" <?php $result = chkdata_CB($obj,"assessment","Morbid obesity"); echo $result;?>> <?php xl("Morbid obesity",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="DVT" <?php $result = chkdata_CB($obj,"assessment","DVT"); echo $result;?>> <?php xl("DVT",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Inguinal" <?php $result = chkdata_CB($obj,"assessment","Hernia Inguinal"); echo $result;?>> <?php xl("Hernia, Inguinal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Lower Back Pain" <?php $result = chkdata_CB($obj,"assessment","Lower Back Pain"); echo $result;?>> <?php xl("Lower Back Pain",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Asthma" <?php $result = chkdata_CB($obj,"assessment","Asthma"); echo $result;?>> <?php xl("Asthma",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Failed prev wt loss surgery" <?php $result = chkdata_CB($obj,"assessment","Failed prev wt loss surgery"); echo $result;?>> <?php xl("Failed prev wt loss surgery",'e') ?> </label></td><td>
 <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Internal" <?php $result = chkdata_CB($obj,"assessment","Hernia Internal"); echo $result;?>> <?php xl("Hernia, Internal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Osteoarthritis" <?php $result = chkdata_CB($obj,"assessment","Osteoarthritis"); echo $result;?>> <?php xl("Osteoarthritis",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="CHF" <?php $result = chkdata_CB($obj,"assessment","CHF"); echo $result;?>> <?php xl("CHF",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Fatty Liver" <?php $result = chkdata_CB($obj,"assessment","Fatty Liver"); echo $result;?>> <?php xl("Fatty Liver",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hypercholesterolemia" <?php $result = chkdata_CB($obj,"assessment","Hypercholesterolemia"); echo $result;?>> <?php xl("Hypercholesterolemia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Panniculitis" <?php $result = chkdata_CB($obj,"assessment","Panniculitis"); echo $result;?>> <?php xl("Panniculitis",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="assessment[]" value="Coronary Artery Dz" <?php $result = chkdata_CB($obj,"assessment","Coronary Artery Dz"); echo $result;?>> <?php xl("Coronary Artery Dz",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Gallbladder Dz" <?php $result = chkdata_CB($obj,"assessment","Gallbladder Dz"); echo $result;?>> <?php xl("Gallbladder Dz",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Umbilical" <?php $result = chkdata_CB($obj,"assessment","Hernia Umbilical"); echo $result;?>> <?php xl("Hernia, Umbilical",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="PVD" <?php $result = chkdata_CB($obj,"assessment","PVD"); echo $result;?>> <?php xl("PVD",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="COPD" <?php $result = chkdata_CB($obj,"assessment","COPD"); echo $result;?>> <?php xl("COPD",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="GERD" <?php $result = chkdata_CB($obj,"assessment","GERD"); echo $result;?>> <?php xl("GERD",'e') ?> </label></td><td>
 <label class="text"><input type="checkbox" name="assessment[]" value="Hypertension" <?php $result = chkdata_CB($obj,"assessment","Hypertension"); echo $result;?>> <?php xl("Hypertension",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Sleep Apnea" <?php $result = chkdata_CB($obj,"assessment","Sleep Apnea"); echo $result;?>> <?php xl("Sleep Apnea",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Depression" <?php $result = chkdata_CB($obj,"assessment","Depression"); echo $result;?>> <?php xl("Depression",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Hiatal" <?php $result = chkdata_CB($obj,"assessment","Hernia Hiatal"); echo $result;?>> <?php xl("Hernia, Hiatal",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hypertriglyceridemia" <?php $result = chkdata_CB($obj,"assessment","Hypertriglyceridemia"); echo $result;?>> <?php xl("Hypertriglyceridemia",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Urinary Incontinence" <?php $result = chkdata_CB($obj,"assessment","Urinary Incontinence"); echo $result;?>> <?php xl("Urinary Incontinence",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="assessment[]" value="Diabetes" <?php $result = chkdata_CB($obj,"assessment","Diabetes"); echo $result;?>> <?php xl("Diabetes",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Incisional" <?php $result = chkdata_CB($obj,"assessment","Hernia Incisional"); echo $result;?>> <?php xl("Hernia, Incisional",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Hypothyroidism" <?php $result = chkdata_CB($obj,"assessment","Hypothyroidism"); echo $result;?>> <?php xl("Hypothyroidism",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="assessment[]" value="Venous Stasis Dz" <?php $result = chkdata_CB($obj,"assessment","Venous Stasis Dz"); echo $result;?>> <?php xl("Venous Stasis Dz",'e') ?> </label></td></tr> 
  
  
  
   <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

  
 <tr><td align="right" width="150"></td> <td colspan="4"><textarea name="note2"  rows="4" cols="60"><?php $result = chkdata_Txt($obj,"note2"); echo $result;?></textarea></td></tr> 
    <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

 </table> 
  
 <table> 
  
 <tr><td align="right" width="150"> <b class="text"><?php xl("RECOMMENDATIONS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="recommendations[]" value="VBG Vertical Banded Gastroplasty" <?php $result = chkdata_CB($obj,"recommendations","VBG Vertical Banded Gastroplasty"); echo $result;?>> <?php xl("VBG (Vertical Banded Gastroplasty)",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="PRYGBP Proximal Roux en Y Gastric Bypass" <?php $result = chkdata_CB($obj,"recommendations","PRYGBP Proximal Roux en Y Gastric Bypass"); echo $result;?>> <?php xl("PRYGBP (Proximal Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="SG Sleeve Gastrectomy" <?php $result = chkdata_CB($obj,"recommendations","SG Sleeve Gastrectomy"); echo $result;?>> <?php xl("SG (Sleeve Gastrectomy)",'e') ?> </label></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="MRYGBP Medial Roux en Y Gastric Bypass" <?php $result = chkdata_CB($obj,"recommendations","MRYGBP Medial Roux en Y Gastric Bypass"); echo $result;?>> <?php xl("MRYGBP (Medial Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="ABG Adjustable Banded Gastroplasty" <?php $result = chkdata_CB($obj,"recommendations","ABG Adjustable Banded Gastroplasty"); echo $result;?>> <?php xl("ABG (Adjustable Banded Gastroplasty)",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="DRYGBP Distal Roux en Y Gastric Bypass" <?php $result = chkdata_CB($obj,"recommendations","DRYGBP Distal Roux en Y Gastric Bypass"); echo $result;?>> <?php xl("DRYGBP (Distal Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="Gastric Restrictive Procedure other than VBG  ABG" <?php $result = chkdata_CB($obj,"recommendations","Gastric Restrictive Procedure other than VBG  ABG"); echo $result;?>> <?php xl("Gastric Restrictive Procedure other than VBG/ ABG",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Duodenal Switch Procedure" <?php $result = chkdata_CB($obj,"recommendations","Duodenal Switch Procedure"); echo $result;?>> <?php xl("Duodenal Switch Procedure",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Revision of Gastric Restrictive Procedure" <?php $result = chkdata_CB($obj,"recommendations","Revision of Gastric Restrictive Procedure"); echo $result;?>> <?php xl("Revision of Gastric Restrictive Procedure",'e') ?> </label></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="BPD Biliopancreatic Diversion" <?php $result = chkdata_CB($obj,"recommendations","BPD Biliopancreatic Diversion"); echo $result;?>> <?php xl("BPD (Biliopancreatic Diversion)",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Lysis of Adhesions" <?php $result = chkdata_CB($obj,"recommendations","Lysis of Adhesions"); echo $result;?>> <?php xl("Lysis of Adhesions",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Liver Biopsy" <?php $result = chkdata_CB($obj,"recommendations","Liver Biopsy"); echo $result;?>> <?php xl("Liver Biopsy",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="Hiatal Hernia Repair w  Fundoplication" <?php $result = chkdata_CB($obj,"recommendations","Hiatal Hernia Repair w  Fundoplication"); echo $result;?>> <?php xl("Hiatal Hernia Repair w/  Fundoplication",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Hiatal Hernia Repair w o Fundoplication" <?php $result = chkdata_CB($obj,"recommendations","Hiatal Hernia Repair w o Fundoplication"); echo $result;?>> <?php xl("Hiatal Hernia Repair w/o Fundoplication",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Vagotomy   Pyloraplasty" <?php $result = chkdata_CB($obj,"recommendations","Vagotomy   Pyloraplasty"); echo $result;?>> <?php xl("Vagotomy & Pyloraplasty",'e') ?> </label></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="Abdominoplasty" <?php $result = chkdata_CB($obj,"recommendations","Abdominoplasty"); echo $result;?>> <?php xl("Abdominoplasty",'e') ?> </label></td></tr><tr><td></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Appendectomy possible" <?php $result = chkdata_CB($obj,"recommendations","Appendectomy possible"); echo $result;?>> <?php xl("Appendectomy (possible)",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Cholecystectomy possible" <?php $result = chkdata_CB($obj,"recommendations","Cholecystectomy possible"); echo $result;?>> <?php xl("Cholecystectomy (possible)",'e') ?> </label></td></tr><tr><td></td><td>
 <label class="text"><input type="checkbox" name="recommendations[]" value="EGD Esophagogastroduodenoscopy" <?php $result = chkdata_CB($obj,"recommendations","EGD Esophagogastroduodenoscopy"); echo $result;?>> <?php xl("EGD (Esophagogastroduodenoscopy)",'e') ?> </label></td><td> 
 <label class="text"><input type="checkbox" name="recommendations[]" value="Colonoscopy" <?php $result = chkdata_CB($obj,"recommendations","Colonoscopy"); echo $result;?>> <?php xl("Colonoscopy",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
    <tr>
    <td align="left" colspan="2">&nbsp;</td>
  </tr>

 <tr><td align="right" width="150"></td> <td><textarea name="note3"  rows="4" cols="60"><?php $result = chkdata_Txt($obj,"note3"); echo $result;?></textarea></td></tr> 
  
 </table> 
<input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
 <?php 
 formFooter(); 
 ?> 
