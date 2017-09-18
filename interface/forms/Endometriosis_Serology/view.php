<!-- view.php --> 
<!--
This form was genreated using formscript.pl

The javascript at the end allows the asessment of Endometriosis from the levels of various interleukins and cytokines.
This is based on several publications:

Serum anti-inflammatory cytokines for the evaluation of inflammatory status in endometriosis. doi:  10.4103/1735-1995.166215
Pro-inflammatory cytokines for evaluation of inflammatory status in endometriosis doi:  10.5114/ceji.2015.50840

-->
 <script type="text/javascript" src="interleukins.js"></script>

 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Endometriosis_Serology"); 
 $obj = formFetch("form_Endometriosis_Serology", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
 DATE_HEADER 
 <form method=post action="<?php echo $rootdir?>/forms/Endometriosis_Serology/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form" onsubmit="return top.restoreSession()"> 
 <h1> Endometriosis Serology </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" /><br> 
 <br> 
 <h3>Lab Analysis</h3> 
 <br> 
 <h4>Serology</h4> 
  
 <table> 
  
 <tr><td>Serum il 1beta</td> <td><input type="text" name="serum_il_1beta" id="ilb" onchange="calculateEndo();" value="<?php $result = chkdata_Txt($obj,"serum_il_1beta"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Serum il 6</td> <td><input type="text" name="serum_il_6" id="il6" onchange="calculateEndo();" value="<?php $result = chkdata_Txt($obj,"serum_il_6"); echo $result;?>"></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td>Serum tnf alpha</td> <td><input type="text" name="serum_tnf_alpha" id="tnf" onchange="calculateEndo();" value="<?php $result = chkdata_Txt($obj,"serum_tnf_alpha"); echo $result;?>"></td></tr> 
  
 </table> 
 <br> 
 <h4>Risk of Endometriosis</h4> 
  
 <table> 
  
 <tr><td>Probability of endometriosis</td> <td><input type="text" name="probability_of_endometriosis" id="ast" value="<?php $result = chkdata_Txt($obj,"probability_of_endometriosis"); echo $result;?>"></td></tr> 
  
 </table> 
 <table></table><input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
