<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Gestational_Age"); 
 $obj = formFetch("form_Gestational_Age", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
 
 <form method=post action="<?php echo $rootdir?>/forms/Gestational_Age/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onsubmit="return top.restoreSession()"> 
 <h1> Gestational_Age </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" /><br> 
 <br> 
 <h3>Dates</h3> 
  
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
  
 <tr><td>Weeks</td> <td><input type="text" name="weeks" value="<?php $result = chkdata_Txt($obj,"weeks"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Days</td> <td><input type="text" name="days" value="<?php $result = chkdata_Txt($obj,"days"); echo $result;?>"></td></tr> 
  
 </table> 
 <table></table><input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
