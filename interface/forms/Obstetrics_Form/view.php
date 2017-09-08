<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Obstetrics_Form"); 
 $obj = formFetch("form_Obstetrics_Form", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
  
 ?> 
 <html><head> 
 <link rel=stylesheet href="<?php echo $css_header;?>" type="text/css"> 
 </head> 
 <body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0> 
 <style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'> var mypcc = '1'; </script>
 
 <form method=post action="<?php echo $rootdir?>/forms/Obstetrics_Form/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onsubmit="return top.restoreSession()"> 
 <h1> Obstetrics Form </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" /><br> 
 <br> 
 <h3>LMP</h3> 
  
 <table> 
  
 <tr><td> 
 <span class='text'><?php xl('Lmp (yyyy-mm-dd): ','e') ?></span> 
 </td><td> 
 <input type='text' size='10' name='lmp' id='lmp' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event'  value="<?php $result = chkdata_Date($obj,"lmp"); echo $result;?>"> 
 <img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22' 
 id='img_lmp' border='0' alt='[?]' style='cursor:pointer' 
 title='Click here to choose a date'> 
 <script> 
 Calendar.setup({inputField:'lmp', ifFormat:'%Y-%m-%d', button:'img_lmp'}); 
 </script> 
 </td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td> 
 <span class='text'><?php xl('Edc (yyyy-mm-dd): ','e') ?></span> 
 </td><td> 
 <input type='text' size='10' name='edc' id='edc' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd last date of this event'  value="<?php $result = chkdata_Date($obj,"edc"); echo $result;?>"> 
 <img src='../../../interface/pic/show_calendar.gif' align='absbottom' width='24' height='22' 
 id='img_edc' border='0' alt='[?]' style='cursor:pointer' 
 title='Click here to choose a date'> 
 <script> 
 Calendar.setup({inputField:'edc', ifFormat:'%Y-%m-%d', button:'img_edc'}); 
 </script> 
 </td></tr> 
  
 </table> 
 <br> 
 <h3>Gestational Age</h3> 
  
 <table> 
  
 <tr><td>Weeks</td> <td><input type="text" name="weeks" size="3" value="<?php $result = chkdata_Txt($obj,"weeks"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Days</td> <td><input type="text" name="days" size="3" value="<?php $result = chkdata_Txt($obj,"days"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h3>Patient History</h3> 
  
 <table> 
  
 <tr><td>Supplements</td> <td><textarea name="supplements"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"supplements"); echo $result;?></textarea></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Treatments</td> <td><textarea name="treatments"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"treatments"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <br> 
 <h3>Physical Exam</h3> 
  
 <table> 
  
 <tr><td>Height of fundus</td> <td><input type="text" name="height_of_fundus" size="3" value="<?php $result = chkdata_Txt($obj,"height_of_fundus"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h3>Lab Results</h3> 
 <br> 
 <h4>CBC</h4> 
  
 <table> 
  
 <tr><td>Hemoglobin</td> <td><input type="text" name="hemoglobin" size="5" value="<?php $result = chkdata_Txt($obj,"hemoglobin"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Platelets</td> <td><input type="text" name="platelets" size="5" value="<?php $result = chkdata_Txt($obj,"platelets"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h4>Urinalysis</h4> 
  
 <table> 
  
 <tr><td>Urinalysis</td> <td><textarea name="urinalysis"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"urinalysis"); echo $result;?></textarea></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Urine culture</td> <td><textarea name="urine_culture"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"urine_culture"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <br> 
 <h4>Blood Group</h4> 
  
 <table> 
  
 <tr><td>Abo and rh</td> <td><input type="text" name="abo_and_rh" size="3" value="<?php $result = chkdata_Txt($obj,"abo_and_rh"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Coombs test</td> <td><input type="text" name="coombs_test" value="<?php $result = chkdata_Txt($obj,"coombs_test"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h4>Serology</h4> 
  
 <table> 
  
 <tr><td>Syphillis RPR</td> <td><input type="text" name="syphillis_rpr" value="<?php $result = chkdata_Txt($obj,"syphillis_rpr"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Hepatitis B</td> <td><input type="text" name="hepatitis_b" value="<?php $result = chkdata_Txt($obj,"hepatitis_b"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>HIV</td> <td><input type="text" name="hiv" value="<?php $result = chkdata_Txt($obj,"hiv"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h4>Cultures</h4> 
  
 <table> 
  
 <tr><td>Group B streptococcus culture</td> <td><input type="text" name="group_b_streptococcus_culture" value="<?php $result = chkdata_Txt($obj,"group_b_streptococcus_culture"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h4>Other tests</h4> 
  
 <table> 
  
 <tr><td>Glucose challenge test</td> <td><input type="text" name="glucose_challenge_test" value="<?php $result = chkdata_Txt($obj,"glucose_challenge_test"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Others</td> <td><textarea name="others"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"others"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <br> 
 <h3>Ultrasound</h3> 
  
 <table> 
  
 <tr><td>Fetal heart</td> <td><label><input type="radio" name="fetal_heart" value="Documented" <?php $result = chkdata_Radio($obj,"fetal_heart","Documented"); echo $result;?>>Documented</label> 
 <label><input type="radio" name="fetal_heart" value="Not Seen" <?php $result = chkdata_Radio($obj,"fetal_heart","Not Seen"); echo $result;?>>Not Seen</label></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Ultrasound notes</td> <td><textarea name="ultrasound_notes"  rows="4" cols="100"><?php $result = chkdata_Txt($obj,"ultrasound_notes"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <br> 
 <h3>Current Problems</h3> 
  
 <table> 
  
 <tr><td>Problem list</td> <td><textarea name="problem_list"  rows="4" cols="100"><?php $result = chkdata_Txt($obj,"problem_list"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <br> 
 <h3>Notes</h3> 
  
 <table> 
  
 <tr><td>Other notes</td> <td><textarea name="other_notes"  rows="4" cols="100"><?php $result = chkdata_Txt($obj,"other_notes"); echo $result;?></textarea></td></tr> 
  
 </table> 
 <table></table><input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
