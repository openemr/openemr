<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Forms2_Cardiology"); 
 $obj = formFetch("form_Forms2_Cardiology", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
 <style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'> var mypcc = '1'; </script>
 
 <form method=post action="<?php echo $rootdir?>/forms/Forms2_Cardiology/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onSubmit="return top.restoreSession()">
 <h1> Forms2_Cardiology </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" />
 
  <table width="100%"> 
 
             <tr> 
 
                 <td class="text"> 
                     <strong> <?php xl("Recommended Subacute Bacterial Endocarditis Prophylaxis",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> recommended subacute bacterial endocarditis prophylaxis</td> <td><label><input type="checkbox" name="_recommended_subacute_bacterial_endocarditis_prophylaxis[]" value="None" <?php $result = chkdata_CB($obj,"_recommended_subacute_bacterial_endocarditis_prophylaxis","None"); echo $result;?> <?php xl(">None",'e') ?> </label> 
 <label><input type="checkbox" name="_recommended_subacute_bacterial_endocarditis_prophylaxis[]" value="Standard" <?php $result = chkdata_CB($obj,"_recommended_subacute_bacterial_endocarditis_prophylaxis","Standard"); echo $result;?> <?php xl(">Standard",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
  
  
 </table> 
 
                     <br /> 
  
 <table> 
  
 <tr><td> other</td> <td><input type="text" name="_other" value="<?php $result = chkdata_Txt($obj,"_other"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
 
                     <strong> <?php xl("Check the letter below describing the level of exercise tolerance in which the 
                         applicant is able to participate.",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> full active participation with no restrictions</td> <td><label><input type="checkbox" name="_full_active_participation_with_no_restrictions" value="yes" <?php $result = chkdata_CB($obj,"_full_active_participation_with_no_restrictions","yes"); echo $result;?>></label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> full active participation with moderate exercise</td> <td><label><input type="checkbox" name="_full_active_participation_with_moderate_exercise" value="yes" <?php $result = chkdata_CB($obj,"_full_active_participation_with_moderate_exercise","yes"); echo $result;?>></label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> partial active participation with light exercise</td> <td><label><input type="checkbox" name="_partial_active_participation_with_light_exercise" value="yes" <?php $result = chkdata_CB($obj,"_partial_active_participation_with_light_exercise","yes"); echo $result;?>></label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> limited active participation with no exercise</td> <td><label><input type="checkbox" name="_limited_active_participation_with_no_exercise" value="yes" <?php $result = chkdata_CB($obj,"_limited_active_participation_with_no_exercise","yes"); echo $result;?>></label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="3"> 
                     <b> <?php xl("Allergies:",'e') ?> </b></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
 
                     Medication/Trigger 
                 </td> 
 
                 <td class='text'> 
 
                     Date of the last Reaction 
                 </td> 
 
                 <td class='text'> 
 
                     Type of Reaction 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
 
                     Medication Trigger1:textfield 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> date of the last reaction1</td> <td><input type="text" name="_date_of_the_last_reaction1" value="<?php $result = chkdata_Txt($obj,"_date_of_the_last_reaction1"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> type of reaction1</td> <td><input type="text" name="_type_of_reaction1" value="<?php $result = chkdata_Txt($obj,"_type_of_reaction1"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication trigger2</td> <td><input type="text" name="_medication_trigger2" value="<?php $result = chkdata_Txt($obj,"_medication_trigger2"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> date of the last reaction2</td> <td><input type="text" name="_date_of_the_last_reaction2" value="<?php $result = chkdata_Txt($obj,"_date_of_the_last_reaction2"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> type of reaction2</td> <td><input type="text" name="_type_of_reaction2" value="<?php $result = chkdata_Txt($obj,"_type_of_reaction2"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication trigger3</td> <td><input type="text" name="_medication_trigger3" value="<?php $result = chkdata_Txt($obj,"_medication_trigger3"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> date of the last reaction3</td> <td><input type="text" name="_date_of_the_last_reaction3" value="<?php $result = chkdata_Txt($obj,"_date_of_the_last_reaction3"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> type of reaction3</td> <td><input type="text" name="_type_of_reaction3" value="<?php $result = chkdata_Txt($obj,"_type_of_reaction3"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="2"> 
                     <strong> <?php xl("Medications:",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
 
                     Medication / Strength / SIG: 
                 </td> 
 
                 <td class='text'> 
 
                     Special Instructions: 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication strength  sig1</td> <td><input type="text" name="_medication_strength__sig1" value="<?php $result = chkdata_Txt($obj,"_medication_strength__sig1"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> special instructions1</td> <td><input type="text" name="_special_instructions1" value="<?php $result = chkdata_Txt($obj,"_special_instructions1"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication strength  sig2</td> <td><input type="text" name="_medication_strength__sig2" value="<?php $result = chkdata_Txt($obj,"_medication_strength__sig2"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> special instructions2</td> <td><input type="text" name="_special_instructions2" value="<?php $result = chkdata_Txt($obj,"_special_instructions2"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication strength  sig3</td> <td><input type="text" name="_medication_strength__sig3" value="<?php $result = chkdata_Txt($obj,"_medication_strength__sig3"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> special instructions3</td> <td><input type="text" name="_special_instructions3" value="<?php $result = chkdata_Txt($obj,"_special_instructions3"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication strength  sig4</td> <td><input type="text" name="_medication_strength__sig4" value="<?php $result = chkdata_Txt($obj,"_medication_strength__sig4"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> special instructions4</td> <td><input type="text" name="_special_instructions4" value="<?php $result = chkdata_Txt($obj,"_special_instructions4"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> medication strength  sig5</td> <td><input type="text" name="_medication_strength__sig5" value="<?php $result = chkdata_Txt($obj,"_medication_strength__sig5"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> special instructions5</td> <td><input type="text" name="_special_instructions5" value="<?php $result = chkdata_Txt($obj,"_special_instructions5"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table> 
 
             <tr> 
 
                 <td class='text' align="center"> 
 
                     <strong> <?php xl("Non-prescription medications we stock in the camp infirmary are listed below: 
                         Please check those which we SHOULD NOT administer",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> non prescription medications</td> <td><label><input type="checkbox" name="_non_prescription_medications[]" value="Acetaminophen" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Acetaminophen"); echo $result;?> <?php xl(">Acetaminophen",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Advil" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Advil"); echo $result;?> <?php xl(">Advil",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Benadryl" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Benadryl"); echo $result;?> <?php xl(">Benadryl",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Caladryl" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Caladryl"); echo $result;?> <?php xl(">Caladryl",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Chloraseptic Spray" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Chloraseptic Spray"); echo $result;?> <?php xl(">Chloraseptic Spray",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Cough Medicine" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Cough Medicine"); echo $result;?> <?php xl(">Cough Medicine",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Dramamine" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Dramamine"); echo $result;?> <?php xl(">Dramamine",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Kaopectate" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Kaopectate"); echo $result;?> <?php xl(">Kaopectate",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Meclazine" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Meclazine"); echo $result;?> <?php xl(">Meclazine",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Milk of Magnesia" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Milk of Magnesia"); echo $result;?> <?php xl(">Milk of Magnesia",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Pepto Bismol" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Pepto Bismol"); echo $result;?> <?php xl(">Pepto Bismol",'e') ?> </label> 
 <label><input type="checkbox" name="_non_prescription_medications[]" value="Sudafed" <?php $result = chkdata_CB($obj,"_non_prescription_medications","Sudafed"); echo $result;?> <?php xl(">Sudafed",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> describe any recent operations or serious illness</td> <td><textarea name="_describe_any_recent_operations_or_serious_illness"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_describe_any_recent_operations_or_serious_illness"); echo $result;?></textarea></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> describe any physical disability effecting camp activity</td> <td><textarea name="_describe_any_physical_disability_effecting_camp_activity"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_describe_any_physical_disability_effecting_camp_activity"); echo $result;?></textarea></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> describe any pertinent findings on examination</td> <td><textarea name="_describe_any_pertinent_findings_on_examination"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_describe_any_pertinent_findings_on_examination"); echo $result;?></textarea></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="4"> 
                     <strong> <?php xl("Cardiac Rhythm/Device History",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' style="width: 498px"> 
  
 <table> 
  
 <tr><td> does applicant have a history of dysrhythmias</td> <td><label><input type="radio" name="_does_applicant_have_a_history_of_dysrhythmias" value="Yes" <?php $result = chkdata_Radio($obj,"_does_applicant_have_a_history_of_dysrhythmias","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="radio" name="_does_applicant_have_a_history_of_dysrhythmias" value="NO" <?php $result = chkdata_Radio($obj,"_does_applicant_have_a_history_of_dysrhythmias","NO"); echo $result;?> <?php xl(">NO",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
  
 </table> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="24"> 
  
 <table> 
  
  
 </table> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="4"> 
  
 <table> 
  
  
 </table> 
             </tr> 
 
             <tr> 
 
                 <td class='text' style="width: 498px"> 
  
 <table> 
  
 <tr><td> does applicant have a pacemaker or icd</td> <td><label><input type="radio" name="_does_applicant_have_a_pacemaker_or_icd" value="Yes" <?php $result = chkdata_Radio($obj,"_does_applicant_have_a_pacemaker_or_icd","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="radio" name="_does_applicant_have_a_pacemaker_or_icd" value="NO" <?php $result = chkdata_Radio($obj,"_does_applicant_have_a_pacemaker_or_icd","NO"); echo $result;?> <?php xl(">NO",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
  
 </table> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="2"> 
  
 <table> 
  
  
 </table> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="4"> 
                     <strong> <?php xl("Pacemaker",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' style="width: 25%"> 
  
 <table> 
  
 <tr><td> pacemaker brand</td> <td><input type="text" name="_pacemaker_brand" value="<?php $result = chkdata_Txt($obj,"_pacemaker_brand"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' style="width: 25%"> 
  
 <table> 
  
 <tr><td> pacemaker model</td> <td><input type="text" name="_pacemaker_model" value="<?php $result = chkdata_Txt($obj,"_pacemaker_model"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' colspan="2" style="width: 50%"> 
  
 <table> 
  
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pacemaker programmed to</td> <td><input type="text" name="_pacemaker_programmed_to" value="<?php $result = chkdata_Txt($obj,"_pacemaker_programmed_to"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pacemaker mode</td> <td><input type="text" name="_pacemaker_mode" value="<?php $result = chkdata_Txt($obj,"_pacemaker_mode"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pacemaker lower rate</td> <td><input type="text" name="_pacemaker_lower_rate" value="<?php $result = chkdata_Txt($obj,"_pacemaker_lower_rate"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pacemaker upper rate</td> <td><input type="text" name="_pacemaker_upper_rate" value="<?php $result = chkdata_Txt($obj,"_pacemaker_upper_rate"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="6"> 
                     <strong> <?php xl("ICD",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> icd brand</td> <td><input type="text" name="_icd_brand" value="<?php $result = chkdata_Txt($obj,"_icd_brand"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> icd model</td> <td><input type="text" name="_icd_model" value="<?php $result = chkdata_Txt($obj,"_icd_model"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> 
 <span class='text'><?php xl(' icd date of last interrogation (yyyy-mm-dd): ','e') ?></span> 
 </td><td> 
 <input type='text' size='10' name='_icd_date_of_last_interrogation' id='_icd_date_of_last_interrogation' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event'  value="<?php $result = chkdata_Date($obj,"_icd_date_of_last_interrogation"); echo $result;?>"> 
 <img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22' 
 id='img__icd_date_of_last_interrogation' border='0' alt='[?]' style='cursor:pointer' 
 title='Click here to choose a date'> 
 <script> 
 Calendar.setup({inputField:'_icd_date_of_last_interrogation', ifFormat:'%Y-%m-%d', button:'img__icd_date_of_last_interrogation'}); 
 </script> 
 </td></tr> 
  
 </table> 
                     </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="3"> 
  
 <table> 
  
 <tr><td> has icd discharged recently and how often</td> <td><input type="text" name="_has_icd_discharged_recently_and_how_often" value="<?php $result = chkdata_Txt($obj,"_has_icd_discharged_recently_and_how_often"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="2"> 
                     <strong> <?php xl("Cardiac Transplant Only",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> date of transplant</td> <td><input type="text" name="_date_of_transplant" value="<?php $result = chkdata_Txt($obj,"_date_of_transplant"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> surgeon</td> <td><input type="text" name="_surgeon" value="<?php $result = chkdata_Txt($obj,"_surgeon"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> name of center</td> <td><input type="text" name="_name_of_center" value="<?php $result = chkdata_Txt($obj,"_name_of_center"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> phone</td> <td><input type="text" name="_phone" value="<?php $result = chkdata_Txt($obj,"_phone"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> evidence of rejection</td> <td><label><input type="radio" name="_evidence_of_rejection" value="Yes" <?php $result = chkdata_Radio($obj,"_evidence_of_rejection","Yes"); echo $result;?> <?php xl(">Yes",'e') ?> </label> 
 <label><input type="radio" name="_evidence_of_rejection" value="NO" <?php $result = chkdata_Radio($obj,"_evidence_of_rejection","NO"); echo $result;?> <?php xl(">NO",'e') ?> </label></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> 
 <span class='text'><?php xl(' last cardiac biopsy date (yyyy-mm-dd): ','e') ?></span> 
 </td><td> 
 <input type='text' size='10' name='_last_cardiac_biopsy_date' id='_last_cardiac_biopsy_date' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event'  value="<?php $result = chkdata_Date($obj,"_last_cardiac_biopsy_date"); echo $result;?>"> 
 <img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22' 
 id='img__last_cardiac_biopsy_date' border='0' alt='[?]' style='cursor:pointer' 
 title='Click here to choose a date'> 
 <script> 
 Calendar.setup({inputField:'_last_cardiac_biopsy_date', ifFormat:'%Y-%m-%d', button:'img__last_cardiac_biopsy_date'}); 
 </script> 
 </td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="2"> 
  
 <table> 
  
 <tr><td> if evidence of rejection then type and grade</td> <td><textarea name="_if_evidence_of_rejection_then_type_and_grade"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_if_evidence_of_rejection_then_type_and_grade"); echo $result;?></textarea></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 
         <table width="100%"> 
 
             <tr> 
 
                 <td class='text' colspan="5"> 
                     <strong> <?php xl("Physical Exam:",'e') ?> </strong></td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> height</td> <td><input type="text" name="_height" value="<?php $result = chkdata_Txt($obj,"_height"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> weight</td> <td><input type="text" name="_weight" value="<?php $result = chkdata_Txt($obj,"_weight"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> heart rate</td> <td><input type="text" name="_heart_rate" value="<?php $result = chkdata_Txt($obj,"_heart_rate"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' > 
  
 <table> 
  
 <tr><td> o2 saturation</td> <td><input type="text" name="_o2_saturation" value="<?php $result = chkdata_Txt($obj,"_o2_saturation"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="4"> 
                     Blood Pressures:</td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> bp ra</td> <td><input type="text" name="_bp_ra" value="<?php $result = chkdata_Txt($obj,"_bp_ra"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> bp la</td> <td><input type="text" name="_bp_la" value="<?php $result = chkdata_Txt($obj,"_bp_la"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> bp rl</td> <td><input type="text" name="_bp_rl" value="<?php $result = chkdata_Txt($obj,"_bp_rl"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> bp ll</td> <td><input type="text" name="_bp_ll" value="<?php $result = chkdata_Txt($obj,"_bp_ll"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="4"> 
                     Pulses:</td> 
             </tr> 
 
             <tr> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pulses rue</td> <td><input type="text" name="_pulses_rue" value="<?php $result = chkdata_Txt($obj,"_pulses_rue"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pulses lue</td> <td><input type="text" name="_pulses_lue" value="<?php $result = chkdata_Txt($obj,"_pulses_lue"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pulses rle</td> <td><input type="text" name="_pulses_rle" value="<?php $result = chkdata_Txt($obj,"_pulses_rle"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> pulses lle</td> <td><input type="text" name="_pulses_lle" value="<?php $result = chkdata_Txt($obj,"_pulses_lle"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                  
             </tr> 
 
             <tr> 
 
                 <td class='text' > 
  
 <table> 
  
 <tr><td> cardiovascular</td> <td><input type="text" name="_cardiovascular" value="<?php $result = chkdata_Txt($obj,"_cardiovascular"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' colspan="2"> 
  
 <table> 
  
 <tr><td> precordial activity</td> <td><input type="text" name="_precordial_activity" value="<?php $result = chkdata_Txt($obj,"_precordial_activity"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text'> 
  
 <table> 
  
 <tr><td> murmurs</td> <td><input type="text" name="_murmurs" value="<?php $result = chkdata_Txt($obj,"_murmurs"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="2"> 
  
 <table> 
  
 <tr><td> neurological</td> <td><input type="text" name="_neurological" value="<?php $result = chkdata_Txt($obj,"_neurological"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' colspan="2"> 
  
 <table> 
  
 <tr><td> lungs</td> <td><input type="text" name="_lungs" value="<?php $result = chkdata_Txt($obj,"_lungs"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
             <tr> 
 
                 <td class='text' colspan="2" style="height: 21px"> 
  
 <table> 
  
 <tr><td> abdomen</td> <td><input type="text" name="_abdomen" value="<?php $result = chkdata_Txt($obj,"_abdomen"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
 
                 <td class='text' colspan="2" style="height: 21px"> 
  
 <table> 
  
 <tr><td> gi gu</td> <td><input type="text" name="_gi_gu" value="<?php $result = chkdata_Txt($obj,"_gi_gu"); echo $result;?>"></td></tr> 
  
 </table> 
                 </td> 
             </tr> 
 
         </table> 
 <table></table><input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
