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
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<form method=post action="<?php echo $rootdir;?>/forms/Initial_New_Patient_Physical_Exam/save.php?mode=new" name="Initial_New_Patient_Physical_Exam" onSubmit="return top.restoreSession()">
<table width="100%" border="0" cellspacing="0" cellpadding="0" >
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






<tr><td colspan="5" align="center">
<table cellpadding="0" cellspacing="0" border="0">
<tr><td><label class="text"><?php xl("% Sweeter",'e') ?></label></td><td>&nbsp;<input type="text" name="sweeter"  /></td></tr>
<tr> <td><label class="text"><?php xl("% Bloater",'e') ?></label></td><td>&nbsp;<input type="text" name="bloater"  /></td></tr>
<tr> <td><label class="text"><?php xl("% Grazer",'e') ?></label></td><td>&nbsp;<input type="text" name="grazer"  /></td></tr>
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





<tr><td align="right" width="150"> <b class="text"><?php xl("GENERAL:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="general[]" value="Alert" /> <?php xl("Alert",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="Oriented X3" /> <?php xl("Oriented X3",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="Not in distress" /> <?php xl("Not in distress",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="In distress" /> <?php xl("In distress",'e') ?> </label></td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="general[]" value="Well developed" /> <?php xl("Well developed",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="Well nourished" /> <?php xl("Well nourished",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="Petite" /> <?php xl("Petite",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="general[]" value="Obese" /> <?php xl("Obese",'e') ?> </label></td></tr>


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





<tr><td align="right" width="150"> <b class="text"><?php xl("Head",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="head[]" value="AT NC" /> <?php xl("AT/NC",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="head[]" value="Hirsutism  Facial hairs" /> <?php xl("Hirsutism/Facial hairs",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Eyes",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="eyes[]" value="PERRLA" /> <?php xl("PERRLA",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="eyes[]" value="EOMI" /> <?php xl("EOMI",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="eyes[]" value="Anicteric" /> <?php xl("Anicteric",'e') ?> </label></td><td> </td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="eyes[]" value="Pink" /> <?php xl("Pink",'e') ?> </label> </td><td> <label class="text"><input type="checkbox" name="eyes[]" value="Pale" /> <?php xl("Pale",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="eyes[]" value="Icteric" /> <?php xl("Icteric",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="eyes[]" value="Cataracts" /> <?php xl("Cataracts",'e') ?> </label></td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Ears",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="ears[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="ears[]" value="TM w  good light reflex" /> <?php xl("TM w/ good light reflex",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Nose",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="nose[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="nose[]" value="Patent" /> <?php xl("Patent",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="nose[]" value="No discharge" /> <?php xl("No discharge",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="nose[]" value="Discharge" /> <?php xl("Discharge",'e') ?> </label></td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Throat",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="throat[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="throat[]" value="Erythematous" /> <?php xl("Erythematous",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Oral cavity",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="oral_cavity[]" value="No lesions" /> <?php xl("No lesions",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="oral_cavity[]" value="Lesions" /> <?php xl("Lesions",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="oral_cavity[]" value="Friable gums" /> <?php xl("Friable gums",'e') ?> </label></td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Dentition",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="dentition[]" value="Good" /> <?php xl("Good",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="dentition[]" value="Fair" /> <?php xl("Fair",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="dentition[]" value="Poor" /> <?php xl("Poor",'e') ?> </label></td><td>&nbsp;</td></tr>



  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>


<tr><td align="right" width="150"> <b class="text"><?php xl("NECK:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="neck[]" value="No lymphadenopathy" /> <?php xl("No lymphadenopathy",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="No thyromegally" /> <?php xl("No thyromegally",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="No Bruit" /> <?php xl("No Bruit",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="FROM" /> <?php xl("FROM",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="neck[]" value="Lymphadenopathy" /> <?php xl("Lymphadenopathy",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="Thyromegally" /> <?php xl("Thyromegally",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="Right Bruit" /> <?php xl("Right Bruit",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neck[]" value="Left Bruit" /> <?php xl("Left Bruit",'e') ?> </label></td></tr>



  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>


<tr><td align="right" width="150"> <b class="text"><?php xl("HEART:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="heart[]" value="NSR" /> <?php xl("NSR",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="heart[]" value="S1 S2" /> <?php xl("S1/S2",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="heart[]" value="No murmur" /> <?php xl("No murmur",'e') ?> </label></td><td> </td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="heart[]" value="Irregular rate" /> <?php xl("Irregular rate",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="heart[]" value="Irreg rhythm" /> <?php xl("Irreg rhythm",'e') ?> </label> </td><td><label class="text"><input type="checkbox" name="heart[]" value="Murmur" /> <?php xl("Murmur",'e') ?> </label> </td><td><label class="text"><input type="checkbox" name="heart[]" value="Gallop" /> <?php xl("Gallop",'e') ?> </label></td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("LUNG:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="lung[]" value="Clear to ascultation" /> <?php xl("Clear to ascultation",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="No rales" /> <?php xl("No rales",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="No wheezes" /> <?php xl("No wheezes",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="No rhonchi" /> <?php xl("No rhonchi",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="lung[]" value="Distant" /> <?php xl("Distant",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="Rales" /> <?php xl("Rales",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="Wheezes" /> <?php xl("Wheezes",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="lung[]" value="Rhonchi" /> <?php xl("Rhonchi",'e') ?> </label></td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("CHEST:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="chest[]" value="No palpable tenderness" /> <?php xl("No palpable tenderness",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="chest[]" value="Palpable tenderness" /> <?php xl("Palpable tenderness",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>
  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("BREAST:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="breast[]" value="Did not examine" /> <?php xl("Did not examine",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Male",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="male[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="male[]" value="Gynecomastia" /> <?php xl("Gynecomastia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="male[]" value="Palpable mass" /> <?php xl("Palpable mass",'e') ?> </label></td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Female",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="female[]" value="Normal size" /> <?php xl("Normal size",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Normal exam" /> <?php xl("Normal exam",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Enlarged" /> <?php xl("Enlarged",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Pendulous" /> <?php xl("Pendulous",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="female[]" value="Palpable mass" /> <?php xl("Palpable mass",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Tender" /> <?php xl("Tender",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Erythematous" /> <?php xl("Erythematous",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="female[]" value="Peau d orange" /> <?php xl("Peau d'orange",'e') ?> </label></td></tr>





<tr><td align="right" width="150">&nbsp;</td> <td colspan="4"><input type="text" name="note" size="80"  /></td></tr>



  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>


<tr><td align="right" width="150"> <b class="text"><?php xl("ABDOMEN:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="abdomen[]" value="NABS" /> <?php xl("NABS",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Soft" /> <?php xl("Soft",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Non tender" /> <?php xl("Non-tender",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Non distended" /> <?php xl("Non-distended",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Obese" /> <?php xl("Obese",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Hepatomegaly" /> <?php xl("Hepatomegaly",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Ascites" /> <?php xl("Ascites",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Tender" /> <?php xl("Tender",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Distended" /> <?php xl("Distended",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Guarding" /> <?php xl("Guarding",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="Rebound tenderness" /> <?php xl("Rebound tenderness",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="abdomen[]" value="CVA tenderness" /> <?php xl("CVA tenderness",'e') ?> </label></td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Scar",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="scar[]" value="Upper midline" /> <?php xl("Upper midline",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Lower midline" /> <?php xl("Lower midline",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Rt subcostal" /> <?php xl("Rt subcostal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Lt subcostal" /> <?php xl("Lt subcostal",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Rt inguinal" /> <?php xl("Rt inguinal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Lt inguinal" /> <?php xl("Lt inguinal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Paramedian" /> <?php xl("Paramedian",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Pfanennsteil" /> <?php xl("Pfanennsteil",'e') ?> </label></td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="scar[]" value="Upper Transverse" /> <?php xl("Upper Transverse",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Lower Transverse" /> <?php xl("Lower Transverse",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="Laparoscopic" /> <?php xl("Laparoscopic",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="scar[]" value="McBurney s" /> <?php xl("McBurney's",'e') ?> </label></td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("UMBILIUS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="umbilius[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="umbilius[]" value="Hernia" /> <?php xl("Hernia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="umbilius[]" value="Lymphadenopathy" /> <?php xl("Lymphadenopathy",'e') ?> </label></td><td>&nbsp;</td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("GROINS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="groins[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="groins[]" value="Rt hernia" /> <?php xl("Rt hernia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="groins[]" value="Lt hernia" /> <?php xl("Lt hernia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="groins[]" value="Rash" /> <?php xl("Rash",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="groins[]" value="Lymphadenopathy" /> <?php xl("Lymphadenopathy",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("EXTREMITIES:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="extremities[]" value="Warm" /> <?php xl("Warm",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Dry" /> <?php xl("Dry",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="No edema" /> <?php xl("No edema",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="No calf tenderness" /> <?php xl("No calf tenderness",'e') ?> </label></td></tr><tr><td></td><td>  <label class="text"><input type="checkbox" name="extremities[]" value="Pitting edema" /> <?php xl("Pitting edema",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Stasis dermatitis" /> <?php xl("Stasis dermatitis",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Varicosities" /> <?php xl("Varicosities",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Calf tenderness" /> <?php xl("Calf tenderness",'e') ?> </label></td></tr><tr><td></td><td>  <label class="text"><input type="checkbox" name="extremities[]" value="Acanthosis Nigricans" /> <?php xl("Acanthosis Nigricans",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Spider Angiomas" /> <?php xl("Spider Angiomas",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Palmar Erythema" /> <?php xl("Palmar Erythema",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="extremities[]" value="Hirsutism" /> <?php xl("Hirsutism",'e') ?> </label></td></tr>


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





<tr><td align="right" width="150"> <b class="text"><?php xl("Peripheral pulses",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="peripheral_pulses[]" value="Did not examine" /> <?php xl("Did not examine",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="peripheral_pulses[]" value="2 pulses" /> <?php xl("2+ pulses",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Right",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Radial" /> <?php xl("Radial",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Inguinal" /> <?php xl("Inguinal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="Popliteal" /> <?php xl("Popliteal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="DP" /> <?php xl("DP",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="right_peripheral_pulses[]" value="PT" /> <?php xl("PT",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Left",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Radial" /> <?php xl("Radial",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Inguinal" /> <?php xl("Inguinal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="Popliteal" /> <?php xl("Popliteal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="DP" /> <?php xl("DP",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="left_peripheral_pulses[]" value="PT" /> <?php xl("PT",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("NEUROLOGICAL:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="neurological[]" value="Did not examine" /> <?php xl("Did not examine",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="neurological[]" value="Grossly normal" /> <?php xl("Grossly normal",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Right",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="right_neurological[]" value="Normal strength   DTR s" /> <?php xl("Normal strength & DTR's",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn grip strength" /> <?php xl("Abn grip strength",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn arm strength" /> <?php xl("Abn arm strength",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn leg strength" /> <?php xl("Abn leg strength",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="right_neurological[]" value="Abn DTR s" /> <?php xl("Abn DTR's",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>





<tr><td align="right" width="150"> <b class="text"><?php xl("Left",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="left_neurological[]" value="Normal strength   DTR s" /> <?php xl("Normal strength & DTR's",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn grip strength" /> <?php xl("Abn grip strength",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn arm strength" /> <?php xl("Abn arm strength",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn leg strength" /> <?php xl("Abn leg strength",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="left_neurological[]" value="Abn DTR s" /> <?php xl("Abn DTR's",'e') ?> </label></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>

  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>




<tr><td align="right" width="150"> <b class="text"><?php xl("RECTUM:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="rectum[]" value="Did not examine" /> <?php xl("Did not examine",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="rectum[]" value="Normal" /> <?php xl("Normal",'e') ?> </label></td><td> </td><td> </td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="rectum[]" value="Palpable mass" /> <?php xl("Palpable mass",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="rectum[]" value="Enlarged prostate" /> <?php xl("Enlarged prostate",'e') ?> </label> </td><td><label class="text"><input type="checkbox" name="rectum[]" value="Hemorrhoids" /> <?php xl("Hemorrhoids",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="rectum[]" value="Fissure  Fistula" /> <?php xl("Fissure/Fistula",'e') ?> </label></td></tr>

  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>




<tr><td align="right" width="150"> <b class="text"><?php xl("PELVIC:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="pelvic[]" value="Did not examine" /> <?php xl("Did not examine",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="pelvic[]" value="Nomal" /> <?php xl("Nomal",'e') ?> </label></td><td> </td><td> </td></tr><tr><td></td><td><label class="text"><input type="checkbox" name="pelvic[]" value="CM tenderness" /> <?php xl("CM tenderness",'e') ?> </label> </td><td><label class="text"><input type="checkbox" name="pelvic[]" value="Rt adnexal mass" /> <?php xl("Rt adnexal mass",'e') ?> </label></td><td><label class="text"><input type="checkbox" name="pelvic[]" value="Lt adnexal mass" /> <?php xl("Lt adnexal mass",'e') ?> </label></td><td>&nbsp;</td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150"> <b class="text"><?php xl("ASSESSMENT:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="assessment[]" value="Morbid obesity" /> <?php xl("Morbid obesity",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="DVT" /> <?php xl("DVT",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Inguinal" /> <?php xl("Hernia, Inguinal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Lower Back Pain" /> <?php xl("Lower Back Pain",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Asthma" /> <?php xl("Asthma",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Failed prev wt loss surgery" /> <?php xl("Failed prev wt loss surgery",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Internal" /> <?php xl("Hernia, Internal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Osteoarthritis" /> <?php xl("Osteoarthritis",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="CHF" /> <?php xl("CHF",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Fatty Liver" /> <?php xl("Fatty Liver",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hypercholesterolemia" /> <?php xl("Hypercholesterolemia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Panniculitis" /> <?php xl("Panniculitis",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Coronary Artery Dz" /> <?php xl("Coronary Artery Dz",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Gallbladder Dz" /> <?php xl("Gallbladder Dz",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Umbilical" /> <?php xl("Hernia, Umbilical",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="PVD" /> <?php xl("PVD",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="COPD" /> <?php xl("COPD",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="GERD" /> <?php xl("GERD",'e') ?> </label></td><td>  <label class="text"><input type="checkbox" name="assessment[]" value="Hypertension" /> <?php xl("Hypertension",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Sleep Apnea" /> <?php xl("Sleep Apnea",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Depression" /> <?php xl("Depression",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Hiatal" /> <?php xl("Hernia, Hiatal",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hypertriglyceridemia" /> <?php xl("Hypertriglyceridemia",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Urinary Incontinence" /> <?php xl("Urinary Incontinence",'e') ?> </label></td></tr><tr><td></td><td>  <label class="text"><input type="checkbox" name="assessment[]" value="Diabetes" /> <?php xl("Diabetes",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hernia Incisional" /> <?php xl("Hernia, Incisional",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Hypothyroidism" /> <?php xl("Hypothyroidism",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="assessment[]" value="Venous Stasis Dz" /> <?php xl("Venous Stasis Dz",'e') ?> </label></td></tr>


  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>



<tr><td align="right" width="150">&nbsp;</td> <td colspan="4"><textarea name="note2"  rows="4" cols="60"></textarea></td></tr>
  <tr>
    <td align="left" colspan="5">&nbsp;</td>
  </tr>

</table>


<table>

<tr><td align="right" width="150"> <b class="text"><?php xl("RECOMMENDATIONS:",'e') ?></b> </td> <td><label class="text"><input type="checkbox" name="recommendations[]" value="VBG Vertical Banded Gastroplasty" /> <?php xl("VBG (Vertical Banded Gastroplasty)",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="PRYGBP Proximal Roux en Y Gastric Bypass" /> <?php xl("PRYGBP (Proximal Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="SG Sleeve Gastrectomy" /> <?php xl("SG (Sleeve Gastrectomy)",'e') ?> </label></td><td>  <label class="text"><input type="checkbox" name="recommendations[]" value="MRYGBP Medial Roux en Y Gastric Bypass" /> <?php xl("MRYGBP (Medial Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="ABG Adjustable Banded Gastroplasty" /> <?php xl("ABG (Adjustable Banded Gastroplasty)",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="DRYGBP Distal Roux en Y Gastric Bypass" /> <?php xl("DRYGBP (Distal Roux-en-Y Gastric Bypass)",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Gastric Restrictive Procedure other than VBG  ABG" /> <?php xl("Gastric Restrictive Procedure other than VBG/  ABG",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Duodenal Switch Procedure" /> <?php xl("Duodenal Switch Procedure",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Revision of Gastric Restrictive Procedure" /> <?php xl("Revision of Gastric Restrictive Procedure",'e') ?> </label></td><td>  <label class="text"><input type="checkbox" name="recommendations[]" value="BPD Biliopancreatic Diversion" /> <?php xl("BPD (Biliopancreatic Diversion)",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Lysis of Adhesions" /> <?php xl("Lysis of Adhesions",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Liver Biopsy" /> <?php xl("Liver Biopsy",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Hiatal Hernia Repair w  Fundoplication" /> <?php xl("Hiatal Hernia Repair w/  Fundoplication",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Hiatal Hernia Repair w o Fundoplication" /> <?php xl("Hiatal Hernia Repair w/o Fundoplication",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Vagotomy   Pyloraplasty" /> <?php xl("Vagotomy & Pyloraplasty",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Abdominoplasty" /> <?php xl("Abdominoplasty",'e') ?> </label></td></tr><tr><td></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Appendectomy possible" /> <?php xl("Appendectomy (possible)",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Cholecystectomy possible" /> <?php xl("Cholecystectomy (possible)",'e') ?> </label></td></tr><tr><td></td><td>  <label class="text"><input type="checkbox" name="recommendations[]" value="EGD Esophagogastroduodenoscopy" /> <?php xl("EGD (Esophagogastroduodenoscopy)",'e') ?> </label></td><td> <label class="text"><input type="checkbox" name="recommendations[]" value="Colonoscopy" /> <?php xl("Colonoscopy",'e') ?> </label></td></tr>

</table>

<table>
  <tr>
    <td align="left" colspan="2">&nbsp;</td>
  </tr>

<tr><td align="right" width="150">&nbsp;</td> <td><textarea name="note3"  rows="4" cols="60"></textarea></td></tr>

</table>
<input type="submit" name="submit form" value="submit form" />
</form>
<a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
<?php
formFooter();
?>
