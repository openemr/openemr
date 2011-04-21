<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Forms3_Cardiology"); 
 $obj = formFetch("form_Forms3_Cardiology", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
 <link rel=stylesheet href="<?echo $css_header;?>" type="text/css"> 
 </head> 
 <body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
 <form method=post action="<?echo $rootdir?>/forms/Forms3_Cardiology/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form" onSubmit="return top.restoreSession()"> 
 <h1> Forms3_Cardiology </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" />
 
 <table width="100%" cellpadding="0" cellspacing="0"> 
 
     <tr> 
 
         <td class='text'  valign="top"> 
 
             <table width="100%" cellpadding="0" cellspacing="0"> 
 
                 <tr> 
 
                     <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > date</td> <td class='text' ><input type="text" name="_date" value="<?php $result = chkdata_Txt($obj,"_date"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'   style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > name</td> <td class='text' ><input type="text" name="_name" value="<?php $result = chkdata_Txt($obj,"_name"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                      
                 </tr> 
 
 		<tr> 
 
 			<td class='text'  colspan='2' style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > chief complaint</td> <td class='text' ><input type="text" name="_chief_complaint" value="<?php $result = chkdata_Txt($obj,"_chief_complaint"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td></tr> 
 
             </table> 
         </td> 
     </tr> 
 
     <tr> 
 
         <td class='text'  valign="top" style="border: 1px #000000 solid; height: 15px;"> 
 
             <table width="100%" cellpadding="0" cellspacing="0">                 
 
                 <tr> 
 
                      
 
                     <td class='text'  colspan="2" style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > wt</td> <td class='text' ><input type="text" name="_wt" value="<?php $result = chkdata_Txt($obj,"_wt"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > bp</td> <td class='text' ><input type="text" name="_bp" value="<?php $result = chkdata_Txt($obj,"_bp"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                      <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > p</td> <td class='text' ><input type="text" name="_p" value="<?php $result = chkdata_Txt($obj,"_p"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > t</td> <td class='text' ><input type="text" name="_t" value="<?php $result = chkdata_Txt($obj,"_t"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > r</td> <td class='text' ><input type="text" name="_r" value="<?php $result = chkdata_Txt($obj,"_r"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > ht</td> <td class='text' ><input type="text" name="_ht" value="<?php $result = chkdata_Txt($obj,"_ht"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr>     
 
                  
 
             </table> 
         </td> 
     </tr> 
 
      
 
      
 
      
 
      
 
     <tr> 
 
          <td class='text'  valign="top" style="border: 1px #000000 solid; height: 15px;"> 
 
             <table width="100%" cellpadding="0" cellspacing="0"> 
 
                 <tr> 
 
                     <td class='text'  colspan="5" align="center" style="border: 1px #000000 solid; height: 15px;"> 
 
                         <h3> 
 
                             HPI 
 
                         </h3> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                      
 
                     <td class='text'   style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > location</td> <td class='text' ><input type="text" name="_location" value="<?php $result = chkdata_Txt($obj,"_location"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > quality</td> <td class='text' ><input type="text" name="_quality" value="<?php $result = chkdata_Txt($obj,"_quality"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                      <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > severity</td> <td class='text' ><input type="text" name="_severity" value="<?php $result = chkdata_Txt($obj,"_severity"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > duration</td> <td class='text' ><input type="text" name="_duration" value="<?php $result = chkdata_Txt($obj,"_duration"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td>	 
 
                     <td class='text'  style="border: 1px #000000 solid;">&nbsp; 
 
                         
                     </td>		 
                 </tr> 
 
 <tr> 
 
 <td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > timing</td> <td class='text' ><input type="text" name="_timing" value="<?php $result = chkdata_Txt($obj,"_timing"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > context</td> <td class='text' ><input type="text" name="_context" value="<?php $result = chkdata_Txt($obj,"_context"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > modifying factors</td> <td class='text' ><input type="text" name="_modifying_factors" value="<?php $result = chkdata_Txt($obj,"_modifying_factors"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > signs symptoms</td> <td class='text' ><input type="text" name="_signs_symptoms" value="<?php $result = chkdata_Txt($obj,"_signs_symptoms"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
 		<td class='text'  style="border: 1px #000000 solid;"> 
  
 <table> 
  
 <tr><td class='text' > status of chronic illness</td> <td class='text' ><input type="text" name="_status_of_chronic_illness" value="<?php $result = chkdata_Txt($obj,"_status_of_chronic_illness"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td></tr> 
 
                  
 
                  
 
             </table> 
         </td> 
     </tr> 
 
      
 
      <tr> 
 
          <td class='text'  valign="top" style="border: 1px #000000 solid; height: 15px;"> 
 
             <table width="100%" cellpadding="0" cellspacing="0"> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border: 1px #000000 solid; height: 15px;">                        
 
                             ROS 
 
                          
                     </td> 
 
 	<td class='text'  align="center" style="border: 1px #000000 solid; height: 15px;"> 
 
                          
 
                             + 
 
                          
                     </td> 
 
 	<td class='text'  align="center" style="border: 1px #000000 solid; height: 15px;"> 
 
                          
 
                             - 
 
                         
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Systemic</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > systemic positive</td> <td class='text' ><input type="text" name="_systemic_positive" value="<?php $result = chkdata_Txt($obj,"_systemic_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > systemic negative</td> <td class='text' ><input type="text" name="_systemic_negative" value="<?php $result = chkdata_Txt($obj,"_systemic_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         ENT</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > ent positive</td> <td class='text' ><input type="text" name="_ent_positive" value="<?php $result = chkdata_Txt($obj,"_ent_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > ent negative</td> <td class='text' ><input type="text" name="_ent_negative" value="<?php $result = chkdata_Txt($obj,"_ent_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Eyes</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > eyes positive</td> <td class='text' ><input type="text" name="_eyes_positive" value="<?php $result = chkdata_Txt($obj,"_eyes_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > eyes negative</td> <td class='text' ><input type="text" name="_eyes_negative" value="<?php $result = chkdata_Txt($obj,"_eyes_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Lymph</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > lymph positive</td> <td class='text' ><input type="text" name="_lymph_positive" value="<?php $result = chkdata_Txt($obj,"_lymph_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > lymph negative</td> <td class='text' ><input type="text" name="_lymph_negative" value="<?php $result = chkdata_Txt($obj,"_lymph_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Resp</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > resp positive</td> <td class='text' ><input type="text" name="_resp_positive" value="<?php $result = chkdata_Txt($obj,"_resp_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > resp negative</td> <td class='text' ><input type="text" name="_resp_negative" value="<?php $result = chkdata_Txt($obj,"_resp_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         CV</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > cv positive</td> <td class='text' ><input type="text" name="_cv_positive" value="<?php $result = chkdata_Txt($obj,"_cv_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > cv negative</td> <td class='text' ><input type="text" name="_cv_negative" value="<?php $result = chkdata_Txt($obj,"_cv_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         GI</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > gi positive</td> <td class='text' ><input type="text" name="_gi_positive" value="<?php $result = chkdata_Txt($obj,"_gi_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > gi negative</td> <td class='text' ><input type="text" name="_gi_negative" value="<?php $result = chkdata_Txt($obj,"_gi_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         GU</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > gu positive</td> <td class='text' ><input type="text" name="_gu_positive" value="<?php $result = chkdata_Txt($obj,"_gu_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > gu negative</td> <td class='text' ><input type="text" name="_gu_negative" value="<?php $result = chkdata_Txt($obj,"_gu_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Skin</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > skin positive</td> <td class='text' ><input type="text" name="_skin_positive" value="<?php $result = chkdata_Txt($obj,"_skin_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > skin negative</td> <td class='text' ><input type="text" name="_skin_negative" value="<?php $result = chkdata_Txt($obj,"_skin_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         MS</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > ms positive</td> <td class='text' ><input type="text" name="_ms_positive" value="<?php $result = chkdata_Txt($obj,"_ms_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > ms negative</td> <td class='text' ><input type="text" name="_ms_negative" value="<?php $result = chkdata_Txt($obj,"_ms_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
                         Psych</td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > psych positive</td> <td class='text' ><input type="text" name="_psych_positive" value="<?php $result = chkdata_Txt($obj,"_psych_positive"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  align="center" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > psych negative</td> <td class='text' ><input type="text" name="_psych_negative" value="<?php $result = chkdata_Txt($obj,"_psych_negative"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                 <tr> 
 
                     <td class='text'   align="left" colspan="3" style="border-right: #000000 1px solid; border-top: #000000 1px solid; 
 
                         border-left: #000000 1px solid; border-bottom: #000000 1px solid; height: 15px"> 
  
 <table> 
  
 <tr><td class='text' > all other ros negative </td> <td class='text' ><textarea name="_all_other_ros_negative_"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_all_other_ros_negative_"); echo $result;?></textarea></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                  
 
                  
 
                  
 
             </table> 
         </td> 
     </tr> 
 
      
 
      
 
       <tr> 
 
          <td class='text'  valign="top" style="border: 1px #000000 solid; height: 15px;"> 
 
             <table width="100%" cellpadding="0" cellspacing="0"> 
 
                 <tr> 
 
                     <td class='text'  colspan="3" align="center" style="border: 1px #000000 solid; height: 15px;">                       
  
 <table> 
  
 <tr><td class='text' > past famiy social history</td> <td class='text' ><textarea name="_past_famiy_social_history"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"_past_famiy_social_history"); echo $result;?></textarea></td></tr> 
  
 </table> 
                     </td> 
                 </tr> 
 
                  
 
                  <tr> 
 
                      
 
                     <td class='text'  style="border: 1px #000000 solid; height: 27px;"> 
  
 <table> 
  
 <tr><td class='text' > ph no change since</td> <td class='text' ><input type="text" name="_ph_no_change_since" value="<?php $result = chkdata_Txt($obj,"_ph_no_change_since"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                     <td class='text'  style="border: 1px #000000 solid; height: 27px;"> 
  
 <table> 
  
 <tr><td class='text' > fh no change since</td> <td class='text' ><input type="text" name="_fh_no_change_since" value="<?php $result = chkdata_Txt($obj,"_fh_no_change_since"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
 
                      <td class='text'  style="border: 1px #000000 solid; height: 27px;"> 
  
 <table> 
  
 <tr><td class='text' > sh no change since</td> <td class='text' ><input type="text" name="_sh_no_change_since" value="<?php $result = chkdata_Txt($obj,"_sh_no_change_since"); echo $result;?>"></td></tr> 
  
 </table> 
                     </td> 
                 </tr>   
 
 <tr><td class='text'  colspan='3'> 
  
 <table> 
  
 <tr><td class='text' > <?php xl("Examination",'e') ?> </td> <td class='text' ><textarea name="examination"  rows="4" cols="40"><?php $result = chkdata_Txt($obj,"examination"); echo $result;?></textarea></td></tr> 
  
 </table> 
 </td></tr>             
 
                  
 
             </table> 
         </td> 
     </tr> 
 
 </table> 
 <table></table><input type="submit" name="submit form" value="submit form" /> 
  
 </form> 
 <?php 
 formFooter(); 
 ?> 
