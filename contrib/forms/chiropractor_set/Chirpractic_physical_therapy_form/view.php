<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Chirpractic_physical_therapy_form"); 
 $returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
 $obj = formFetch("form_Chirpractic_physical_therapy_form", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
 function chkdata_Txt(&$obj, $var) { 
         return htmlentities($obj{"$var"}); 
 } 
 function chkdata_Date(&$obj, $var) { 
         return htmlentities($obj{"$var"}); 
 } 
 function chkdata_CB(&$obj, $nam, $var) { 
 	if (preg_match("/$var/",$obj{$nam})) {return "checked";} else {return "";} 
 } 
 function chkdata_Radio(&$obj, $nam, $var) { 
 	if (strpos($obj{$nam},$var) !== false) {return "checked";} 
 } 
  function chkdata_PopOrScroll(&$obj, $nam, $var) { 
  		if (preg_match("/$var/",$obj{$nam})) {return "selected";} else {return "";} 
 	 
 } 
  
 ?> 
 <html><head> 
 <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
 </head> 
 <body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
 <form method=post action="<?php echo $rootdir?>/forms/Chirpractic_physical_therapy_form/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onSubmit="return top.restoreSession()">
 <h1> Chiropractic physical therapy form </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" />  <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
  
<table cellspacing="0" cellpadding="0" style="width: 100%"> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top"> 
  
                     <h3> 
  
                         CONFIDENTIAL PATIENT CASE HISTORY</h3> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Date:</td> <td class="text" ><input type="text" name="_date" value="<?php $result = chkdata_Txt($obj,"_date"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Social Security #:</td> <td class="text" ><input type="text" name="_social_security_number" value="<?php $result = chkdata_Txt($obj,"_social_security_number"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Drivers License #:</td> <td class="text" ><input type="text" name="_drivers_license_number" value="<?php $result = chkdata_Txt($obj,"_drivers_license_number"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Name:</td> <td class="text" ><input type="text" name="_name" value="<?php $result = chkdata_Txt($obj,"_name"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Address:</td> <td class="text" ><input type="text" name="_address" value="<?php $result = chkdata_Txt($obj,"_address"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > City:</td> <td class="text" ><input type="text" name="_city" value="<?php $result = chkdata_Txt($obj,"_city"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > State:</td> <td class="text" ><input type="text" name="_state" value="<?php $result = chkdata_Txt($obj,"_state"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Zip:</td> <td class="text" ><input type="text" name="_zip" value="<?php $result = chkdata_Txt($obj,"_zip"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Home Phone:</td> <td class="text" ><input type="text" name="_home_phone" value="<?php $result = chkdata_Txt($obj,"_home_phone"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Cell Phone:</td> <td class="text" ><input type="text" name="_cell_phone" value="<?php $result = chkdata_Txt($obj,"_cell_phone"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Birth Date:</td> <td class="text" ><input type="text" name="_birth_date" value="<?php $result = chkdata_Txt($obj,"_birth_date"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Age:</td> <td class="text" ><input type="text" name="_age" value="<?php $result = chkdata_Txt($obj,"_age"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Sex:</td> <td class="text" ><label><input type="checkbox" name="_sex[]" value="Male" <?php $result = chkdata_CB($obj,"_sex","Male"); echo $result;?> <?php xl(">Male",'e') ?> </label> 
 <label><input type="checkbox" name="_sex[]" value="Female" <?php $result = chkdata_CB($obj,"_sex","Female"); echo $result;?> <?php xl(">Female",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Business Or Employer:</td> <td class="text" ><input type="text" name="_business_or_employer" value="<?php $result = chkdata_Txt($obj,"_business_or_employer"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Type Of Work:</td> <td class="text" ><input type="text" name="_type_of_work" value="<?php $result = chkdata_Txt($obj,"_type_of_work"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Business Address And Phone Number:</td> <td class="text" ><input type="text" name="_business_address_and_phone_number" value="<?php $result = chkdata_Txt($obj,"_business_address_and_phone_number"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > check one</td> <td class="text" ><label><input type="checkbox" name="_check_one[]" value="Married" <?php $result = chkdata_CB($obj,"_check_one","Married"); echo $result;?> <?php xl(">Married",'e') ?> </label> 
 <label><input type="checkbox" name="_check_one[]" value="Single" <?php $result = chkdata_CB($obj,"_check_one","Single"); echo $result;?> <?php xl(">Single",'e') ?> </label> 
 <label><input type="checkbox" name="_check_one[]" value="Widowed" <?php $result = chkdata_CB($obj,"_check_one","Widowed"); echo $result;?> <?php xl(">Widowed",'e') ?> </label> 
 <label><input type="checkbox" name="_check_one[]" value="Divorced" <?php $result = chkdata_CB($obj,"_check_one","Divorced"); echo $result;?> <?php xl(">Divorced",'e') ?> </label> 
 <label><input type="checkbox" name="_check_one[]" value="Separated" <?php $result = chkdata_CB($obj,"_check_one","Separated"); echo $result;?> <?php xl(">Separated",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > # Of Children:</td> <td class="text" ><input type="text" name="_number_of_children" value="<?php $result = chkdata_Txt($obj,"_number_of_children"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Name And # Of Emergency Contact:</td> <td class="text" ><input type="text" name="_name_and_number_of_emergency_contact" value="<?php $result = chkdata_Txt($obj,"_name_and_number_of_emergency_contact"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="2" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Spouse Name:</td> <td class="text" ><input type="text" name="_spouse_name" value="<?php $result = chkdata_Txt($obj,"_spouse_name"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Occupation:</td> <td class="text" ><input type="text" name="_occupation" value="<?php $result = chkdata_Txt($obj,"_occupation"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Employer:</td> <td class="text" ><input type="text" name="_employer" value="<?php $result = chkdata_Txt($obj,"_employer"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Who Is Responsible For Your Bill:</td> <td class="text" ><label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Self" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Self"); echo $result;?> <?php xl(">Self",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Spouse" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Spouse"); echo $result;?> <?php xl(">Spouse",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Workmans Comp" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Workmans Comp"); echo $result;?> <?php xl(">Workmans Comp",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Medicaid" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Medicaid"); echo $result;?> <?php xl(">Medicaid",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Medicare" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Medicare"); echo $result;?> <?php xl(">Medicare",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Auto Insurance" <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Auto Insurance"); echo $result;?> <?php xl(">Auto Insurance",'e') ?> </label> 
 <label><input type="checkbox" name="_who_is_responsible_for_your_bill[]" value="Personal health insurance " <?php $result = chkdata_CB($obj,"_who_is_responsible_for_your_bill","Personal health insurance "); echo $result;?> <?php xl(">Personal health insurance ",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td class="text" > Other</td> <td class="text" ><input type="text" name="_other" value="<?php $result = chkdata_Txt($obj,"_other"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top"> 
  
                     <h3> 
  
                         CURRENT HEALTH CONDITION</h3> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Purpose Of This Appointment:</td> <td class="text" ><input type="text" name="_purpose_of_this_appointment" value="<?php $result = chkdata_Txt($obj,"_purpose_of_this_appointment"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Other Doctors Seen For This Condition:</td> <td class="text" ><input type="text" name="_other_doctors_seen_for_this_condition" value="<?php $result = chkdata_Txt($obj,"_other_doctors_seen_for_this_condition"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > When Did This Condition Begin:</td> <td class="text" ><input type="text" name="_when_did_this_condition_begin" value="<?php $result = chkdata_Txt($obj,"_when_did_this_condition_begin"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > check</td> <td class="text" ><label><input type="checkbox" name="_check[]" value="Gradual Onset" <?php $result = chkdata_CB($obj,"_check","Gradual Onset"); echo $result;?> <?php xl(">Gradual Onset",'e') ?> </label> 
 <label><input type="checkbox" name="_check[]" value="Job Related" <?php $result = chkdata_CB($obj,"_check","Job Related"); echo $result;?> <?php xl(">Job Related",'e') ?> </label> 
 <label><input type="checkbox" name="_check[]" value="Auto Related" <?php $result = chkdata_CB($obj,"_check","Auto Related"); echo $result;?> <?php xl(">Auto Related",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Medication You Now Take:</td> <td class="text" ><label><input type="checkbox" name="_medication_you_now_take[]" value="Nerve Pills" <?php $result = chkdata_CB($obj,"_medication_you_now_take","Nerve Pills"); echo $result;?> <?php xl(">Nerve Pills",'e') ?> </label> 
 <label><input type="checkbox" name="_medication_you_now_take[]" value="Pain Killers or Muscle relaxers" <?php $result = chkdata_CB($obj,"_medication_you_now_take","Pain Killers or Muscle relaxers"); echo $result;?> <?php xl(">Pain Killers or Muscle relaxers",'e') ?> </label> 
 <label><input type="checkbox" name="_medication_you_now_take[]" value="Insulin" <?php $result = chkdata_CB($obj,"_medication_you_now_take","Insulin"); echo $result;?> <?php xl(">Insulin",'e') ?> </label> 
 <label><input type="checkbox" name="_medication_you_now_take[]" value="Blood pressure medicine " <?php $result = chkdata_CB($obj,"_medication_you_now_take","Blood pressure medicine "); echo $result;?> <?php xl(">Blood pressure medicine ",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td class="text" > others</td> <td class="text" ><input type="text" name="_others" value="<?php $result = chkdata_Txt($obj,"_others"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="center" colspan="4" valign="top"> 
  
                     <h3> 
  
                         PAST HEALTH HISTORY</h3> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Major Surgery Or Operations:</td> <td class="text" ><label><input type="checkbox" name="_major_surgery_or_operations[]" value="Appendectomy" <?php $result = chkdata_CB($obj,"_major_surgery_or_operations","Appendectomy"); echo $result;?> <?php xl(">Appendectomy",'e') ?> </label> 
 <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Tonsillectomy" <?php $result = chkdata_CB($obj,"_major_surgery_or_operations","Tonsillectomy"); echo $result;?> <?php xl(">Tonsillectomy",'e') ?> </label> 
 <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Gall Bladder" <?php $result = chkdata_CB($obj,"_major_surgery_or_operations","Gall Bladder"); echo $result;?> <?php xl(">Gall Bladder",'e') ?> </label> 
 <label><input type="checkbox" name="_major_surgery_or_operations[]" value="Hernia" <?php $result = chkdata_CB($obj,"_major_surgery_or_operations","Hernia"); echo $result;?> <?php xl(">Hernia",'e') ?> </label> 
 <label><input type="checkbox" name="_major_surgery_or_operations[]" value="BrokenBone" <?php $result = chkdata_CB($obj,"_major_surgery_or_operations","BrokenBone"); echo $result;?> <?php xl(">BrokenBone",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td class="text" > otherone</td> <td class="text" ><input type="text" name="_otherone" value="<?php $result = chkdata_Txt($obj,"_otherone"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Major Accidents Or Falls:</td> <td class="text" ><input type="text" name="_major_accidents_or_falls" value="<?php $result = chkdata_Txt($obj,"_major_accidents_or_falls"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Hospitalization If Other Than Above:</td> <td class="text" ><input type="text" name="_hospitalization_if_other_than_above" value="<?php $result = chkdata_Txt($obj,"_hospitalization_if_other_than_above"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" align="left" colspan="4" valign="top"> 
  
 <table> 
  
 <tr><td class="text" > Previous Chiropractic Care:</td> <td class="text" ><label><input type="checkbox" name="_previous_chiropractic_care[]" value="None " <?php $result = chkdata_CB($obj,"_previous_chiropractic_care","None "); echo $result;?> <?php xl(">None ",'e') ?> </label> Doctors Name: <input type="text" name="_doctors_name" value="<?php $result = chkdata_Txt($obj,"_doctors_name"); echo $result;?>"> Appox Date Of Last Visit:<input type="text" name="_appox_date_of_last_visit" value="<?php $result = chkdata_Txt($obj,"_appox_date_of_last_visit"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
  
   </table> 
  
         <table cellspacing="0" cellpadding="0" width="100%"> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2" align="center"> 
  
                     <h3> 
  
                         Indicate ability to perform the following activities:</h3>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Coughing Or Sneezing</td> <td class="text" ><select name="_coughing_or_sneezing" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_coughing_or_sneezing"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Climbing</td> <td class="text" ><select name="_climbing" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_climbing"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Getting In And Out Of A Car</td> <td class="text" ><select name="_getting_in_and_out_of_a_car" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_getting_in_and_out_of_a_car"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Kneeling</td> <td class="text" ><select name="_kneeling" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_kneeling"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" width="33%"> 
  
 <table> 
  
 <tr><td class="text" > Bending Forward To Brush Teeth</td> <td class="text" ><select name="_bending_forward_to_brush_teeth" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_bending_forward_to_brush_teeth"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" width="33%"> 
  
 <table> 
  
 <tr><td class="text" > Balancing</td> <td class="text" ><select name="_balancing" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_balancing"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Turing Over In Bed</Td> <Td><select name="_turing_over_in_bed" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_turing_over_in_bed"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Dressing Self</td> <td class="text" ><select name="_dressing_self" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_dressing_self"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Walking Short Distance</td> <td class="text" ><select name="_walking_short_distance" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_walking_short_distance"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Sleeping</td> <td class="text" ><select name="_sleeping" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_sleeping"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Standing More Than One Hour</td> <td class="text" ><select name="_standing_more_than_one_hour" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_standing_more_than_one_hour"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Stooping</td> <td class="text" ><select name="_stooping" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_stooping"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Sitting At Table</td> <td class="text" ><select name="_sitting_at_table" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_sitting_at_table"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Gripping</td> <td class="text" ><select name="_gripping" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_gripping"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 

 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Lying On Back</td> <td class="text" ><select name="_lying_on_back" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_lying_on_back"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Pushing</td> <td class="text" ><select name="_pushing" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_pushing"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Lying Flat On Stomach</Td> <td class="text" ><select name="_lying_flat_on_stomach" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_lying_flat_on_stomach"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Pulling</td> <td class="text" ><select name="_pulling" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_pulling"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Lying On Side With Knees Bent</td> <td class="text" ><select name="_lying_on_side_with_knees_bent" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_lying_on_side_with_knees_bent"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Reaching</td> <td class="text" ><select name="_reaching" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_reaching"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Bending Over Forward</td> <td class="text" ><select name="_bending_over_forward" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_bending_over_forward"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Sexual Activity</td> <td class="text" ><select name="_sexual_activity" > 
 <option value=" " <?php $result = chkdata_PopOrScroll($obj,"_sexual_activity"," "); echo $result;?> <?php xl("> ",'e') ?> </option> 
 <option value="U-unable"> <?php xl("U-unable",'e') ?> </option> 
 <option value="P-painful"> <?php xl("P-painful",'e') ?> </option> 
 <option value="D-Diificult"> <?php xl("D-Diificult",'e') ?> </option> 
 <option value="L-Limited"> <?php xl("L-Limited",'e') ?> </option> 
 <option value="N-Normal"> <?php xl("N-Normal",'e') ?> </option> 
 </select></td></tr> 
 </table>                 </td> 
             </tr>
             <tr>
               <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2">Checking Symptoms of Nervous Systems</td>
               
             </tr>
             <tr>
               <td class="text"  style="border: solid 1px #000000" class="text"><label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="Blurring Vision" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","Blurring Vision"); echo $result;?> <?php xl(">Blurring Vision",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="buzzing or ringing in ears" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","buzzing or ringing in ears"); echo $result;?> <?php xl(">Buzzing Or Ringing In Ears",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="confusion" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","confusion"); echo $result;?> <?php xl(">Confusion",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="convulsions" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","convulsions"); echo $result;?> <?php xl(">Convulsions",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="depression or crying spells" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","depression or crying spells"); echo $result;?> <?php xl(">Depression Or Crying Spells",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="dizziness" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","dizziness"); echo $result;?> <?php xl(">Dizziness",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="fainting" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","fainting"); echo $result;?> <?php xl(">Fainting",'e') ?> </label> </td>
               <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemsvalign="top"> <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="paralysis" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","paralysis"); echo $result;?> <?php xl(">Paralysis",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="loss of sleep" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","loss of sleep"); echo $result;?> <?php xl(">Loss Of Sleep",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="low resistance" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","low resistance"); echo $result;?> <?php xl(">Low Resistance",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="muscle jerking" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","muscle jerking"); echo $result;?> <?php xl(">Muscle Jerking",'e') ?> </label> 
 <label><input type="checkbox" name="_checking_symptoms_of_nervous_systems[]" value="headaches" <?php $result = chkdata_CB($obj,"_checking_symptoms_of_nervous_systems","headaches"); echo $result;?> <?php xl(">Headaches",'e') ?> </label>
 
 How Often Do You Have Headaches <input type="text" name="_how_often_do_you_have_headaches" value="<?php $result = chkdata_Txt($obj,"_how_often_do_you_have_headaches"); echo $result;?>"></td>
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  

  
                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Symptoms Are Better In</td> <td class="text" ><label><input type="checkbox" name="_symptoms_are_better_in[]" value="AM" <?php $result = chkdata_CB($obj,"_symptoms_are_better_in","AM"); echo $result;?> <?php xl(">AM",'e') ?> </label> 
 <label><input type="checkbox" name="_symptoms_are_better_in[]" value="Midday" <?php $result = chkdata_CB($obj,"_symptoms_are_better_in","Midday"); echo $result;?> <?php xl(">Midday",'e') ?> </label> 
 <label><input type="checkbox" name="_symptoms_are_better_in[]" value="PM" <?php $result = chkdata_CB($obj,"_symptoms_are_better_in","PM"); echo $result;?> <?php xl(">PM",'e') ?> </label></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Symptoms Are Worse In</td> <td class="text" ><label><input type="checkbox" name="_symptoms_are_worse_in[]" value="AM" <?php $result = chkdata_CB($obj,"_symptoms_are_worse_in","AM"); echo $result;?> <?php xl(">AM",'e') ?> </label> 
 <label><input type="checkbox" name="_symptoms_are_worse_in[]" value="Midday" <?php $result = chkdata_CB($obj,"_symptoms_are_worse_in","Midday"); echo $result;?> <?php xl(">Midday",'e') ?> </label> 
 <label><input type="checkbox" name="_symptoms_are_worse_in[]" value="PM" <?php $result = chkdata_CB($obj,"_symptoms_are_worse_in","PM"); echo $result;?> <?php xl(">PM",'e') ?> </label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Symptoms Do Not Change With Time Of Day</td> <td class="text" ><label><input type="checkbox" name="_symptoms_do_not_change_with_time_of_day" value="yes" <?php $result = chkdata_CB($obj,"_symptoms_do_not_change_with_time_of_day","yes"); echo $result;?>></label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
                     For woman only                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Are You Pregnant</td> <td class="text" ><label><input type="checkbox" name="_are_you_pregnant[]" value="Yes" <?php $result = chkdata_CB($obj,"_are_you_pregnant","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="checkbox" name="_are_you_pregnant[]" value="No" <?php $result = chkdata_CB($obj,"_are_you_pregnant","No"); echo $result;?> <?php xl(">No",'e') ?> </label></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Date Of Onset Of Last Menstrual Cycle</td> <td class="text" ><input type="text" name="_date_of_onset_of_last_menstrual_cycle" value="<?php $result = chkdata_Txt($obj,"_date_of_onset_of_last_menstrual_cycle"); echo $result;?>"></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" class="text"> 
  
 <table> 
  
 <tr><td class="text" > Give Date Of Last Xray</td> <td class="text" ><input type="text" name="_give_date_of_last_xray" value="<?php $result = chkdata_Txt($obj,"_give_date_of_last_xray"); echo $result;?>"></td></tr> 
 </table>                 </td> 
  
                 <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > What Body Part Were They Taken Of</td> <td class="text" ><input type="text" name="_what_body_part_were_they_taken_of" value="<?php $result = chkdata_Txt($obj,"_what_body_part_were_they_taken_of"); echo $result;?>"></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
                 Family History:</td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Cancer</td> <td class="text" ><label><input type="checkbox" name="_cancer[]" value="Mother" <?php $result = chkdata_CB($obj,"_cancer","Mother"); echo $result;?> <?php xl(">Mother",'e') ?> </label> 
 <label><input type="checkbox" name="_cancer[]" value="Father" <?php $result = chkdata_CB($obj,"_cancer","Father"); echo $result;?> <?php xl(">Father",'e') ?> </label> 
 <label><input type="checkbox" name="_cancer[]" value="Brother" <?php $result = chkdata_CB($obj,"_cancer","Brother"); echo $result;?> <?php xl(">Brother",'e') ?> </label> 
 <label><input type="checkbox" name="_cancer[]" value="Sister" <?php $result = chkdata_CB($obj,"_cancer","Sister"); echo $result;?> <?php xl(">Sister",'e') ?> </label> 
 <label><input type="checkbox" name="_cancer[]" value="None" <?php $result = chkdata_CB($obj,"_cancer","None"); echo $result;?> <?php xl(">None",'e') ?> </label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Diabetes</td> <td class="text" ><label><input type="checkbox" name="_diabetes[]" value="Mother" <?php $result = chkdata_CB($obj,"_diabetes","Mother"); echo $result;?> <?php xl(">Mother",'e') ?> </label> 
 <label><input type="checkbox" name="_diabetes[]" value="Father" <?php $result = chkdata_CB($obj,"_diabetes","Father"); echo $result;?> <?php xl(">Father",'e') ?> </label> 
 <label><input type="checkbox" name="_diabetes[]" value="Brother" <?php $result = chkdata_CB($obj,"_diabetes","Brother"); echo $result;?> <?php xl(">Brother",'e') ?> </label> 
 <label><input type="checkbox" name="_diabetes[]" value="Sister" <?php $result = chkdata_CB($obj,"_diabetes","Sister"); echo $result;?> <?php xl(">Sister",'e') ?> </label> 
 <label><input type="checkbox" name="_diabetes[]" value="None" <?php $result = chkdata_CB($obj,"_diabetes","None"); echo $result;?> <?php xl(">None",'e') ?> </label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Heart Problems</td> <td class="text" ><label><input type="checkbox" name="_heart_problems[]" value="Mother" <?php $result = chkdata_CB($obj,"_heart_problems","Mother"); echo $result;?> <?php xl(">Mother",'e') ?> </label> 
 <label><input type="checkbox" name="_heart_problems[]" value="Father" <?php $result = chkdata_CB($obj,"_heart_problems","Father"); echo $result;?> <?php xl(">Father",'e') ?> </label> 
 <label><input type="checkbox" name="_heart_problems[]" value="Brother" <?php $result = chkdata_CB($obj,"_heart_problems","Brother"); echo $result;?> <?php xl(">Brother",'e') ?> </label> 
 <label><input type="checkbox" name="_heart_problems[]" value="Sister" <?php $result = chkdata_CB($obj,"_heart_problems","Sister"); echo $result;?> <?php xl(">Sister",'e') ?> </label> 
 <label><input type="checkbox" name="_heart_problems[]" value="None" <?php $result = chkdata_CB($obj,"_heart_problems","None"); echo $result;?> <?php xl(">None",'e') ?> </label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Back Or Neck Problems</td> <td class="text" ><label><input type="checkbox" name="_back_or_neck_problems[]" value="Mother" <?php $result = chkdata_CB($obj,"_back_or_neck_problems","Mother"); echo $result;?> <?php xl(">Mother",'e') ?> </label> 
 <label><input type="checkbox" name="_back_or_neck_problems[]" value="Father" <?php $result = chkdata_CB($obj,"_back_or_neck_problems","Father"); echo $result;?> <?php xl(">Father",'e') ?> </label> 
 <label><input type="checkbox" name="_back_or_neck_problems[]" value="Brother" <?php $result = chkdata_CB($obj,"_back_or_neck_problems","Brother"); echo $result;?> <?php xl(">Brother",'e') ?> </label> 
 <label><input type="checkbox" name="_back_or_neck_problems[]" value="Sister" <?php $result = chkdata_CB($obj,"_back_or_neck_problems","Sister"); echo $result;?> <?php xl(">Sister",'e') ?> </label> 
 <label><input type="checkbox" name="_back_or_neck_problems[]" value="None" <?php $result = chkdata_CB($obj,"_back_or_neck_problems","None"); echo $result;?> <?php xl(">None",'e') ?> </label></td></tr> 
 </table>                 </td> 
             </tr> 
  
             <tr> 
  
                 <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
                     <table cellspacing="0" cellpadding="0" width="100%"> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" colspan="2" align="center"> 
  
                                 <h3> 
  
                                     Accident Information</h3>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" width="50%"> 
  
 <table> 
  
 <tr><td class="text" > Have You Retained An Attorney</td> <td class="text" ><label><input type="checkbox" name="_have_you_retained_an_attorney[]" value="Yes" <?php $result = chkdata_CB($obj,"_have_you_retained_an_attorney","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="checkbox" name="_have_you_retained_an_attorney[]" value="no" <?php $result = chkdata_CB($obj,"_have_you_retained_an_attorney","no"); echo $result;?> <?php xl(">no",'e') ?> </label></td></tr> 
 </table>                             </td> 
  
                             <td class="text"  style="border: solid 1px #000000" width="50%"> <?php xl("&nbsp; 
                                 ",'e') ?> </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000"> 
                                <?php xl("If yes",'e') ?></td> 
  
                             <td class="text"  style="border: solid 1px #000000"> <?php xl("&nbsp; 
                                 ",'e') ?> </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Name:</td> <td class="text" ><input type="text" name="_attorney_name" value="<?php $result = chkdata_Txt($obj,"_attorney_name"); echo $result;?>"></td></tr> 
 </table>                             </td> 
  
                             <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Address:</td> <td class="text" ><input type="text" name="_attorney_address" value="<?php $result = chkdata_Txt($obj,"_attorney_address"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000"> 
  
 <table> 
  
 <tr><td class="text" > Phone:</td> <td class="text" ><input type="text" name="_attorney_phone" value="<?php $result = chkdata_Txt($obj,"_attorney_phone"); echo $result;?>"></td></tr> 
 </table>                             </td> 
  
                             <td class="text"  style="border: solid 1px #000000">&nbsp; 
                            </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Number Of People In Vechicle And Their Name</td> <td class="text" ><input type="text" name="_number_of_people_in_vechicle_and_their_name" value="<?php $result = chkdata_Txt($obj,"_number_of_people_in_vechicle_and_their_name"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > were the policy notified</td> <td class="text" ><label><input type="checkbox" name="_were_the_policy_notified[]" value="Yes" <?php $result = chkdata_CB($obj,"_were_the_policy_notified","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="checkbox" name="_were_the_policy_notified[]" value="no" <?php $result = chkdata_CB($obj,"_were_the_policy_notified","no"); echo $result;?> <?php xl(">no",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > What Direction Were You Headed</td> <td class="text" ><label><input type="checkbox" name="_what_direction_were_you_headed[]" value="North" <?php $result = chkdata_CB($obj,"_what_direction_were_you_headed","North"); echo $result;?> <?php xl(">North",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="East" <?php $result = chkdata_CB($obj,"_what_direction_were_you_headed","East"); echo $result;?> <?php xl(">East",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="South" <?php $result = chkdata_CB($obj,"_what_direction_were_you_headed","South"); echo $result;?> <?php xl(">South",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_were_you_headed[]" value="West" <?php $result = chkdata_CB($obj,"_what_direction_were_you_headed","West"); echo $result;?> <?php xl(">West",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > What Direction Was Other Vechicle</td> <td class="text" ><label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="North" <?php $result = chkdata_CB($obj,"_what_direction_was_other_vechicle","North"); echo $result;?> <?php xl(">North",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="East" <?php $result = chkdata_CB($obj,"_what_direction_was_other_vechicle","East"); echo $result;?> <?php xl(">East",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="South" <?php $result = chkdata_CB($obj,"_what_direction_was_other_vechicle","South"); echo $result;?> <?php xl(">South",'e') ?> </label> 
 <label><input type="checkbox" name="_what_direction_was_other_vechicle[]" value="Wst" <?php $result = chkdata_CB($obj,"_what_direction_was_other_vechicle","Wst"); echo $result;?> <?php xl(">Wst",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Name Of Street Or Town</td> <td class="text" ><input type="text" name="_name_of_street_or_town" value="<?php $result = chkdata_Txt($obj,"_name_of_street_or_town"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr>
   <td class="text" > Were You Struck From</td> <td class="text" ><label><input type="checkbox" name="_were_you_struck_from[]" value="behind" <?php $result = chkdata_CB($obj,"_were_you_struck_from","behind"); echo $result;?> <?php xl(">behind",'e') ?> </label> 
 <label><input type="checkbox" name="_were_you_struck_from[]" value="front" <?php $result = chkdata_CB($obj,"_were_you_struck_from","front"); echo $result;?> <?php xl(">front",'e') ?> </label> 
 <label><input type="checkbox" name="_were_you_struck_from[]" value="left side" <?php $result = chkdata_CB($obj,"_were_you_struck_from","left side"); echo $result;?> <?php xl(">left side",'e') ?> </label> 
 <label><input type="checkbox" name="_were_you_struck_from[]" value="right side" <?php $result = chkdata_CB($obj,"_were_you_struck_from","right side"); echo $result;?> <?php xl(">right side",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > In Your Own Words Please Describe Accident</td> <td class="text" ><textarea name="_in_your_own_words_please_describe_accident"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_in_your_own_words_please_describe_accident"); echo $result;?></textarea></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Please Complaints And Symptoms</td> <td class="text" ><textarea name="_please_complaints_and_symptoms"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_please_complaints_and_symptoms"); echo $result;?></textarea></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Did You Lose Any Time From Work</td> <td class="text" ><label><input type="checkbox" name="_did_you_lose_any_time_from_work[]" value="Yes" <?php $result = chkdata_CB($obj,"_did_you_lose_any_time_from_work","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="checkbox" name="_did_you_lose_any_time_from_work[]" value="No" <?php $result = chkdata_CB($obj,"_did_you_lose_any_time_from_work","No"); echo $result;?> <?php xl(">No",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Date When You Lose From Work</td> <td class="text" ><input type="text" name="_date_when_you_lose_from_work" value="<?php $result = chkdata_Txt($obj,"_date_when_you_lose_from_work"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Type Of Employment</td> <td class="text" ><input type="text" name="_type_of_employment" value="<?php $result = chkdata_Txt($obj,"_type_of_employment"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Where Were You Taken Immediately Following Accident</td> <td class="text" ><input type="text" name="_where_were_you_taken_immediately_following_accident" value="<?php $result = chkdata_Txt($obj,"_where_were_you_taken_immediately_following_accident"); echo $result;?>"></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > If Taken To The Hospital Did You</td> <td class="text" ><label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Go by ambulance" <?php $result = chkdata_CB($obj,"_if_taken_to_the_hospital_did_you","Go by ambulance"); echo $result;?> <?php xl(">Go by ambulance",'e') ?> </label> 
 <label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Drove self" <?php $result = chkdata_CB($obj,"_if_taken_to_the_hospital_did_you","Drove self"); echo $result;?> <?php xl(">Drove self",'e') ?> </label> 
 <label><input type="checkbox" name="_if_taken_to_the_hospital_did_you[]" value="Taken by someone else" <?php $result = chkdata_CB($obj,"_if_taken_to_the_hospital_did_you","Taken by someone else"); echo $result;?> <?php xl(">Taken by someone else",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                         <tr> 
  
                             <td class="text"  style="border: solid 1px #000000" _checking_symptoms_of_nervous_systemscolspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Have You Ever Been Involved In An Accident Before</td> <td class="text" ><label><input type="checkbox" name="_have_you_ever_been_involved_in_an_accident_before[]" value="yes" <?php $result = chkdata_CB($obj,"_have_you_ever_been_involved_in_an_accident_before","yes"); echo $result;?> <?php xl(">yes",'e') ?> </label> 
 <label><input type="checkbox" name="_have_you_ever_been_involved_in_an_accident_before[]" value="no" <?php $result = chkdata_CB($obj,"_have_you_ever_been_involved_in_an_accident_before","no"); echo $result;?> <?php xl(">no",'e') ?> </label></td></tr> 
 </table>                             </td> 
                         </tr> 
  
                     </table>                 </td> 
             </tr> 
  
         </table>

 <table></table><input type="submit" name="submit form" value="submit form" />  <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
