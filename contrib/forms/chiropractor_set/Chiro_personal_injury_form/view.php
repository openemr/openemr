<!-- view.php --> 
 <?php 
 include_once("../../globals.php"); 
 include_once("$srcdir/api.inc"); 
 formHeader("Form: Chiro_personal_injury_form"); 
 $returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
 $obj = formFetch("form_Chiro_personal_injury_form", $_GET["id"]);  //#Use the formFetch function from api.inc to get values for existing form. 
  
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
 DATE_HEADER 
 <form method=post action="<?echo $rootdir?>/forms/Chiro_personal_injury_form/save.php?mode=update&id=<?echo $_GET["id"];?>" name="my_form" onSubmit="return top.restoreSession()"> 
 <h1> Chiro personal injury form </h1> 
 <hr> 
 <input type="submit" name="submit form" value="submit form" /> <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>

  
<table width="100%" cellpadding="0" cellspacing="0"> 
  
         <tr> 
  
            <td class="text"    style="border:solid 1px #000000"    colspan="3" align="center"><h3> <?php xl("Patient History Questionnaire",'e') ?> </h3> 
             </td> 
         </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Patient Name  / (Nombre):</td><td class="text"   ><input type="text" name="_patient_name" value="<?php $result = chkdata_Txt($obj,"_patient_name"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Middle Name</td><td class="text"   ><input type="text" name="_middle_name" value="<?php $result = chkdata_Txt($obj,"_middle_name"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   >     
  
 <table> 
  
 <tr><td class="text" > Last Name</td><td class="text"   ><input type="text" name="_last_name" value="<?php $result = chkdata_Txt($obj,"_last_name"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    colspan="3"> 
  
 <table> 
  
 <tr><td class="text" > Address /(Direction):</td><td class="text"   ><input type="text" name="_address_direction" value="<?php $result = chkdata_Txt($obj,"_address_direction"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    width="33%"> 
  
 <table> 
  
 <tr><td class="text" > City:</td><td class="text"   ><input type="text" name="_city" value="<?php $result = chkdata_Txt($obj,"_city"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"    width="33%"> 
  
 <table> 
  
 <tr><td class="text" > State:</td><td class="text"   ><input type="text" name="_state" value="<?php $result = chkdata_Txt($obj,"_state"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"    width="33%"> 
  
 <table> 
  
 <tr><td class="text" > Zip:</td><td class="text"   ><input type="text" name="_zip" value="<?php $result = chkdata_Txt($obj,"_zip"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Phone # (Telefone) (Home)</td><td class="text"   ><input type="text" name="_phone_number_home" value="<?php $result = chkdata_Txt($obj,"_phone_number_home"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
     <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Work</td><td class="text"   ><input type="text" name="_phone_number_work" value="<?php $result = chkdata_Txt($obj,"_phone_number_work"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   >&nbsp; 
  
      
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Sex : (Sexo) M/F</td><td class="text"   ><label><input type="checkbox" name="_sex[]" value="Male" <?php $result = chkdata_CB($obj,"_sex","Male"); echo $result;?> <?php xl(">Male",'e') ?> </label> 
 <label><input type="checkbox" name="_sex[]" value="Female" <?php $result = chkdata_CB($obj,"_sex","Female"); echo $result;?> <?php xl(">Female",'e') ?> </label></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Date Of Birth (Feeha de Naeimiento)</td><td class="text"   ><input type="text" name="_date_of_birth" value="<?php $result = chkdata_Txt($obj,"_date_of_birth"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > S.S. # (Seguro Social)</td><td class="text"   ><input type="text" name="_social_security" value="<?php $result = chkdata_Txt($obj,"_social_security"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"     colspan="3"> 
  
 <table> 
  
 <tr>
  <td class="text"   > Nature Of Accident(Accidence):</td> 
  <td class="text"   ><label><input type="checkbox" name="_nature_of_accident[]" value="Automobile" <?php $result = chkdata_CB($obj,"_nature_of_accident","Automobile"); echo $result;?> <?php xl(">Automobile",'e') ?> </label> 
 <label><input type="checkbox" name="_nature_of_accident[]" value="slip and fall" <?php $result = chkdata_CB($obj,"_nature_of_accident","slip and fall"); echo $result;?> <?php xl(">slip and fall",'e') ?> </label> 
 <label><input type="checkbox" name="_nature_of_accident[]" value="work related" <?php $result = chkdata_CB($obj,"_nature_of_accident","work related"); echo $result;?> <?php xl(">work related",'e') ?> </label></td></tr> 
  
 </table> 
  
 <table> 
  
 <tr><td class="text" > Other</td><td class="text"   ><input type="text" name="_other" value="<?php $result = chkdata_Txt($obj,"_other"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    colspan="3" > 
  
     <table width="100%" cellpadding="0" cellspacing="0"> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    colspan="2"> 
  
 <table> 
  
 <tr><td class="text" > Date Of Accident :(Feeha de Accidente)</td><td class="text"   ><input type="text" name="_date_of_accident" value="<?php $result = chkdata_Txt($obj,"_date_of_accident"); echo $result;?>"></td></tr> 
  
 </table> 
     </td>     
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    width="50%"> 
  
 <table> 
  
 <tr><td class="text" > Insurance Name:</td><td class="text"   ><input type="text" name="_insurance_name" value="<?php $result = chkdata_Txt($obj,"_insurance_name"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"    width="50%"> 
  
 <table> 
  
 <tr><td class="text" > Phone#</td><td class="text"   ><input type="text" name="_phone_no" value="<?php $result = chkdata_Txt($obj,"_phone_no"); echo $result;?>"></td></tr> 
  
 </table> 
     </td>    
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    colspan="2">      
  
 <table> 
  
 <tr><td class="text" > Address (Direccion):</td><td class="text"   ><input type="text" name="_address_of_insurance_company" value="<?php $result = chkdata_Txt($obj,"_address_of_insurance_company"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Claim #(Numero de Reclamo):</td><td class="text"   ><input type="text" name="_claim_number" value="<?php $result = chkdata_Txt($obj,"_claim_number"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Policy # (Numero decPoliza)</td><td class="text"   ><input type="text" name="_policy_number" value="<?php $result = chkdata_Txt($obj,"_policy_number"); echo $result;?>"></td></tr> 
  
 </table> 
     </td>   
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Attorney Name:(Nombre de Abogado)</td> 
<td class="text"   ><input type="text" name="_attorney_name" value="<?php $result = chkdata_Txt($obj,"_attorney_name"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Attorney’s Phone # (Telefone de Abegado)</td><td class="text"   ><input type="text" name="_attorney_phone_number" value="<?php $result = chkdata_Txt($obj,"_attorney_phone_number"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    colspan="2"> 
  
 <table> 
  
 <tr>
  <td class="text"   > Attorney address / (Direccion):</td> 
  <td class="text"   ><input type="text" name="_attorney_address" value="<?php $result = chkdata_Txt($obj,"_attorney_address"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr>     
  
     </table> 
     </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"       colspan="3" > 
  
     <table width="100%" cellpadding="0" cellspacing="0"> 
  
     <tr> 
  
      
  
    <td class="text"    style="border:solid 1px #000000"    width="50%"> 
  
 <table> 
  
 <tr><td class="text" > Health Insurance(Plan Medico):</td><td class="text"   ><input type="text" name="_health_insurance" value="<?php $result = chkdata_Txt($obj,"_health_insurance"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"    width="50%"> 
  
 <table> 
  
 <tr><td class="text" > Phone#</td><td class="text"   ><input type="text" name="_health_insurance_phone_number" value="<?php $result = chkdata_Txt($obj,"_health_insurance_phone_number"); echo $result;?>"></td></tr> 
  
 </table> 
         </td> 
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"    colspan="3"> 
  
 <table> 
  
 <tr><td class="text" > Address :</td><td class="text"   ><input type="text" name="_address_of_health_insurance" value="<?php $result = chkdata_Txt($obj,"_address_of_health_insurance"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
      
     </tr> 
  
     <tr> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Subscriber ID #</td><td class="text"   ><input type="text" name="_subscriber_id_number" value="<?php $result = chkdata_Txt($obj,"_subscriber_id_number"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
  
    <td class="text"    style="border:solid 1px #000000"   > 
  
 <table> 
  
 <tr><td class="text" > Group #</td><td class="text"   ><input type="text" name="_group_number" value="<?php $result = chkdata_Txt($obj,"_group_number"); echo $result;?>"></td></tr> 
  
 </table> 
     </td> 
     </tr> 
  
      
  
     </table> 
     </td> 
     </tr> 
  
      
  
     </table>

 <table></table><input type="submit" name="submit form" value="submit form" />  <a href='<?php echo $GLOBALS['webroot']?>/interface/patient_file/encounter/<?php echo $returnurl?>' onclick='top.restoreSession()'> <?php xl("[do not save]",'e') ?> </a>

  
 </form> 
 <?php 
 formFooter(); 
 ?> 
