<?php
/********************************************************************************\
 * Copyright (C) ViCarePlus, Visolve (vicareplus_engg@visolve.com)              *
 *                                                                              *
 * This program is free software; you can redistribute it and/or                *
 * modify it under the terms of the GNU General Public License                  *
 * as published by the Free Software Foundation; either version 2               *
 * of the License, or (at your option) any later version.                       *
 *                                                                              *
 * This program is distributed in the hope that it will be useful,              *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                *
 * GNU General Public License for more details.                                 *
 *                                                                              *
 * You should have received a copy of the GNU General Public License            *
 * along with this program; if not, write to the Free Software                  *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.  *
 \********************************************************************************/
require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/translation.inc.php");
?>

<html>
<head>
<title><?php xl('De Identification','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<style type="text/css">
.style1 {
	text-align: center;
}</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">
//get value from popup window
function set_related(s,type) {
 var list;
 if(type == "diagnosis")
	list = "diagnosis_list";
 else if(type == "drugs")
   list = "drug_list";
 else if(type == "immunizations")
   list = "immunization_list";
   
 var p=s.split("#");
 var elSel = document.getElementById(list);
 var i,k;
 for (i=0;p[i]!=null;i++)
 {
  for(k=0;k<elSel.length;k++)
  {
   if((elSel.options[k].value)==p[i])
    p[i]= " ";
  }	
  if(p[i]!= " ")
  {
  var elOptNew = document.createElement('option');
  elOptNew.text = p[i];
  elOptNew.value = p[i];
  try {
    elSel.add(elOptNew, null); // standards compliant; doesn't work in IE
  }
  catch(ex) {
    elSel.add(elOptNew); // IE only
  }
  }
 }  
 show_value(type);
}

function get_values(type)
{
 if(type == "diagnosis")
	dlgopen('find_code_popup.php', '_blank', 500, 400);
 else if(type == "drugs")
   dlgopen('find_drug_popup.php', '_blank', 500, 400);
 else if(type == "immunizations")
   dlgopen('find_immunization_popup.php', '_blank', 500, 400);
}

//remove item selected from list 
function remove_selected(type)
{
  var list;
  if(type == "diagnosis")
	list = "diagnosis_list";
 else if(type == "drugs")
   list = "drug_list";
 else if(type == "immunizations")
   list = "immunization_list";   
  var elSel = document.getElementById(list);
  var i;
  for (i = elSel.length - 1; i>=0; i--) {
    if (elSel.options[i].selected) 
	{
      elSel.remove(i);
	}
  }
  show_value(type);
}

function show_value(type)
{
 var list,text,radio;
  if(type == "diagnosis")
	{ radio = "diagnosis"; list = "diagnosis_list"; text="diagnosis_text"; }
 else if(type == "drugs")
  { radio = "drugs";  list = "drug_list"; text="drug_text"; }
 else if(type == "immunizations")
  { radio = "immunizations"; list = "immunization_list"; text="immunization_text"; } 
 if(document.getElementById(radio) == "all")
 {
	document.getElementById(text).value="all";
 }
 else
{ 
 var str;
 var elSel = document.getElementById(list);
  var i;
  for (i = elSel.length - 1; i>=0; i--)
  {  
	  if(!str)
	   str = elSel.options[i].value;
	  else 
      str = str +"#"+elSel.options[i].value;
	  
    }  
 document.getElementById(text).value=str;
 }
} 
 
//disable - enable other checkbox when all checkbox is clicked
function disable_other_chkbox()
{
 var value = document.forms[0].all.checked; 
 if(value == 1) 
 {
 document.forms[0].history_data.disabled = true;
 document.forms[0].prescriptions.disabled = true;
 document.forms[0].lists.disabled = true;
 document.forms[0].immunization.disabled = true;
 document.forms[0].transactions.disabled = true;
 document.forms[0].billing_data.disabled = true;
 document.forms[0].insurance_data.disabled = true;
 }
 else
 {
 document.forms[0].history_data.disabled = false;
 document.forms[0].prescriptions.disabled = false;
 document.forms[0].lists.disabled = false;
 document.forms[0].immunization.disabled = false;
 document.forms[0].transactions.disabled = false;
 document.forms[0].billing_data.disabled = false;
 document.forms[0].insurance_data.disabled = false;
 }
}

//disable list,add button,remove button when all option is selected
function disable_controls(type)
{
 var list,button1,button2;
 if(type == "diagnosis")
 { 	button1 = "add_diagnosis"; button2 = "remove_diagnosis"; list = "diagnosis_list"; text="diagnosis_text";  }
 else if(type == "drugs")
  { button1 = "add_drug";  button2 = "remove_drug"; list = "drug_list";  text="drug_text";}
 else if(type == "immunizations")
  { button1 = "add_immunization"; button2 = "remove_immunization"; list = "immunization_list"; text="immunization_text"; }
  document.getElementById(button1).disabled = true;
  document.getElementById(button2).disabled = true;
  document.getElementById(list).disabled = true;
  document.getElementById(text).value = "all";
}

function enable_controls(type)
{
 var list,button1,button2;
 if(type == "diagnosis")
 { 	button1 = "add_diagnosis"; button2 = "remove_diagnosis"; list = "diagnosis_list";  }
 else if(type == "drugs")
  { button1 = "add_drug";  button2 = "remove_drug"; list = "drug_list";  }
 else if(type == "immunizations")
  { button1 = "add_immunization"; button2 = "remove_immunization"; list = "immunization_list";
 }
  document.getElementById(button1).disabled = false;
  document.getElementById(button2).disabled = false;
  document.getElementById(list).disabled = false;
  show_value(type);
}


function form_validate()
{
 if(document.forms[0].begin_date.value >= document.forms[0].end_date.value)
 { 
  alert("<?php echo xl('End date should be greater than Begin date');?>");
  return false;
 }

 if(document.forms[0].all.checked == false &&
 document.forms[0].history_data.checked == false &&
 document.forms[0].prescriptions.checked == false &&
 document.forms[0].immunization.checked == false && 
 document.forms[0].lists.checked == false &&
 document.forms[0].transactions.checked == false && 
 document.forms[0].billing_data.checked == false &&
 document.forms[0].insurance_data.checked == false)
 { 
  alert("<?php echo xl('Select Data Required for De Identification');?>");
  return false;
 }
 
 if(document.forms[0].diagnosis_text.value == "undefined" || document.forms[0].diagnosis_text.value == "")
 { 
  alert("<?php echo xl('Select Diagnosis for De Identification request');?>");
  return false;
 }
 if(document.forms[0].drug_text.value == "undefined" || document.forms[0].drug_text.value == "")
 { 
  alert("<?php echo xl('Select Drugs for De Identification request');?>");
  return false;
 }
 if(document.forms[0].immunization_text.value == "undefined" || document.forms[0].immunization_text.value == "")
 { 
  alert("<?php echo xl('Select Immunizations for De Identification request');?>");
  return false;
 }
 alert("<?php echo xl('De Identification process is started and running in background'); echo '\n'; echo xl('Please visit the screen after some time');?>");
 top.restoreSession();
 return true;
}

function download_file()
{
 alert("<?php echo xl('De-identification files will be saved in'); echo ' `'.$GLOBALS['temporary_files_dir'].'` '; echo xl('location of the openemr machine and may contain sensitive data, so it is recommended to manually delete the files after its use');?>");
 document.de_identification.submit();
}

</script>
</head>
<body class="body_top">
<form name="de_identification" id="de_identification" action="de_identification_screen2.php" method="post" onsubmit="return form_validate();"> 
<strong><?php xl('De Identification','e'); ?></strong>
<?php
 $row = sqlQuery("SHOW TABLES LIKE 'de_identification_status'");
 if (empty($row))
 {
   ?>
   <table>  <tr> 	<td>&nbsp;</td> <td>&nbsp;</td> </tr>
	      <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
 </table>
 <table class="de_identification_status_message" align="center" >
	<tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
        <?php echo xl('Please upgrade OpenEMR Database to include De Identification procedures, function, tables'); ?>
	</br></br><a  target="Blank" href="../../contrib/util/de_identification_upgrade.php"><?php echo xl('Click here');?></a>
	<?php echo xl('to run'); 
    	echo " de_identification_upgrade.php</br>";?><br>
		</td>
		<td>&nbsp;</td>
	</tr>    
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
  <?php  
  }
 else {
 $query = "select status from de_identification_status";
 $res = sqlStatement($query);
 if ($row = sqlFetchArray($res))
 {
   $deIdentificationStatus = addslashes($row['status']);
 /* $deIdentificationStatus:
 *  0 - There is no De Identification in progress. (start new De Identification process)
 *  1 - A De Identification process is currently in progress.
 *  2 - The De Identification process completed and xls file is ready to download
 *  3 - The De Identification process completed with error
 */    
 }
 if($deIdentificationStatus == 1) 
 {
  //1 - A De Identification process is currently in progress.
 ?>
 <table>  <tr> 	<td>&nbsp;</td> <td>&nbsp;</td> </tr>
	      <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
 </table>
 <table class="de_identification_status_message" align="center" >
	<tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
        <?php echo xl('De Identification Process is ongoing');
	echo "</br></br>";
	echo xl('Please visit De Identification screen after some time'); 
    	echo "</br>";	?>      <br>
		</td>
		<td>&nbsp;</td>
	</tr>    
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	</table>
 <?php
 }
 else if($deIdentificationStatus == 2)
 {
  //2 - The De Identification process completed and xls file is ready to download
  $query = "SELECT count(*) as count FROM de_identified_data ";
     $res = sqlStatement($query);
     if ($row = sqlFetchArray($res)) 
     {
      $no_of_items = addslashes($row['count']);
	  }
      if($no_of_items <= 1)
      {
	 //start new search - no patient record fount
	 $query = "update de_identification_status set status = 0"; 
 $res = sqlStatement($query);
	 ?>
	 <table>  <tr> 	<td>&nbsp;</td> <td>&nbsp;</td> </tr>
	      <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
 </table>
 <table class="de_identification_status_message" align="center" >
	    <tr valign="top">

		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
       <?php echo xl('No Patient record found for given Selection criteria');
	echo "</br></br>";
	echo xl('Please start new De Identification process'); 
    echo "</br>";	?> </br>      
		</td>
		<td>&nbsp;</td>
	</tr>    
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>

	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	</table>
	
	<table align="center">
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
      </table>	  
	 <?php
      }
     else {
 ?>
 <table>  <tr> 	<td>&nbsp;</td> <td>&nbsp;</td> </tr>
	      <tr>  <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
 </table>
 <table class="de_identification_status_message" align="center" >
	    <tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
        <?php echo xl('De Identification Process is completed');
	echo "</br></br>";
	echo xl('Please Click download button to download the De Identified data'); 
    echo "</br>";	?>      <br>
		</td>
		<td>&nbsp;</td>
	</tr>    
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	</table>
	<table align="center">
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
   <tr>
   <td colspan="2" class="style1">
		<input type="button" name="Download" value=<?php echo xl("Download");?> onclick="download_file()" ></td>
   </tr>
   </table>
 <?php
 	}
 }
 else if($deIdentificationStatus == 3)
 {
  //3 - The De Identification process completed with error
  ?>
   <table>  <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	        <tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr> 
   </table>
 <table class="de_identification_status_message" align="center" >
	    <tr valign="top">
		<td>&nbsp;</td>
		<td rowspan="3">
		<br>
        <?php echo xl('Some error has occured during De Identification Process');
	echo "</br></br>";
	echo xl('De Identified data may not be complete'); 
    echo "</br></br>";	    
   ?><span class="text"><?php echo xl('Please view De Identification error log table for more details'); 
    echo "</br>";	?></span> 	<br>
		</td>
		<td>&nbsp;</td>
	</tr>    
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
	</table>
	<table align="center">
	<tr> <td>&nbsp;</td> <td>&nbsp;</td> </tr>
   <tr>
   <td colspan="2" class="style1">
		<input type="button" name="Download" value=<?php echo xl("Download Anyway");?>  onclick="download_file()"></td>
   </tr>
   </table>		
 </tr>
 </table>

  <?php
  } 
  if($deIdentificationStatus == 0 )
  {
  //0 - There is no De Identification in progress. (start new De Identification process)
 ?>
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;">
</div>
<table style="width: 74%" border=0>
	<tr rowspan=2> 
		<td>&nbsp;</td>
		<td><span class="text"><?php xl('Begin Date','e'); ?></span>
		<input type="text" size="10" name="begin_date" id="begin_date" value="<?php echo $viewmode ? substr($result['date'], 0, 10) : date('Y-m-d'); ?>" title="<?php xl('yyyy-mm-dd Date of service','e'); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" />
		<img src="../pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_begin_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php xl('Click here to choose a date','e'); ?>">&nbsp;
		</td>
		<td><span class="text"><?php xl('End Date','e'); ?></span>
		<input type="text" size="10" name="end_date" id="end_date" value="<?php echo $viewmode ? substr($result['date'], 0, 10) : date('Y-m-d'); ?>" title="<?php xl('yyyy-mm-dd Date of service','e'); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" />
		<img src="../pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_end_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php xl('Click here to choose a date','e'); ?>">
		</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>		
		</tr>
		<tr> 
		<td>&nbsp;</td> </tr>		
		<tr><td>&nbsp;</td>
		<td colspan=2 class="de_identification_input_controls_box"><input type="checkbox" name="unstructured" id="unstructured" value=<?php echo xl("unstructured");?>><span class="text"><?php xl('Include Unstructured data','e'); ?></span></td>
		<td>&nbsp;</td></tr>
		<tr>
		<td>&nbsp;</td>		
		<td colspan="2">
		<table class="de_identification_input_controls_box">
			<tr>
				<td><span class="text"><?php xl('Select data to be included in De Identified data','e'); ?></span> 				<br />
				<input type="checkbox" name="all" id ="all" value='all' onclick="disable_other_chkbox()"><span class="text"><?php xl('All','e'); ?> </span><br />
				<input type="checkbox" name="history_data" id="history_data" value='history_data'><span class="text"><?php xl('History Data','e'); ?></span> <br />
				<input type="checkbox" name="immunization" id="immunization" value="immunizations"><span class="text"><?php xl('Immunizations','e'); ?></span>
				<br />
				<input type="checkbox" name="prescriptions" id="prescriptions" value="prescriptions"><span class="text"><?php xl('Prescriptions','e'); ?></span>
				
&nbsp;</td>		<br />
				<td><br>
				<input type="checkbox" name="lists" id="lists" value="lists"><span class="text"><?php xl('Issues','e'); ?> </span><br />
				<input type="checkbox" name="transactions" id="transactions" value="transactions"><span class="text"><?php xl('Transactions','e'); ?></span>
				<br />
				<input type="checkbox" name="insurance_data" id="insurance_data" value="insurance_data"><span class="text"><?php xl('Insurance Data','e'); ?> </span><br />
				<input type="checkbox" name="billing_data" id="billing_data" value="billing_data"><span class="text"><?php xl('Billing Data','e'); ?></span> <br />
				
&nbsp;</td>
			</tr>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2"><br>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="3">
		<table style="width: 100%">
			<tr valign="top">
				<!--diagnosis--><td style="width:50%;" class="style1"><span class="text"><?php xl('Enter Diagnosis','e'); ?></span>
				<input type="radio" id="diagnosis" name="diagnosis" value="all" onclick="disable_controls('diagnosis');" /><span class="text"> <?php xl('All','e'); ?></span>
				<input type="radio" id="diagnosis" name="diagnosis" value="select_diagnosis" onclick="enable_controls('diagnosis');"    /> 
				<span class="text"><?php xl('Select Diagnosis','e'); ?></span>
				<select id="diagnosis_list" name="diagnosis_list" size="10" style="width: 60%">
				</select>
				
				</td>
				<td style="width:50%;" class="style1">
				<!--drugs--><span class="text"><?php xl('Enter Drugs','e'); ?></span>
				<input type="radio" id="drugs" name="drugs" value="all"); onclick="disable_controls('drugs')"/><span class="text"> <?php xl('All','e'); ?></span>
				<input type="radio" id="drugs" name="drugs" value="select_drug" onclick="enable_controls('drugs')" /> 
				<span class="text"><?php xl('Select Drugs','e'); ?> <br></span>
				<select id="drug_list" name="drug_list" size="10" style="width: 60%">
				</select>
				
				</td>
			</tr>
			<tr> <td class="style1"> 
				<input type="button" name="add_diagnosis" id = "add_diagnosis" value=<?php echo xl("Add Diagnosis"); ?> onclick="get_values('diagnosis')">
				<input type="button" name="remove_diagnosis" id="remove_diagnosis"value=<?php echo xl("Remove"); ?> onclick="remove_selected('diagnosis')">&nbsp; </td> <td class="style1"> 
				<input type="button" name="add_drug" id="add_drug" value=<?php echo xl("Add Drug"); ?> onclick="get_values('drugs')">
				<input type="button" name="remove_drug" id="remove_drug" value=<?php echo xl("Remove"); ?> onclick="remove_selected('drugs')"> 
			</td> </tr>
		</table>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2" class="style1">
		<!--immunizations--><br>
		<span class="text"><?php xl('Enter Immunizations','e'); ?></span>
		<input type="radio" id="immunizations" name="immunizations" value="all" onclick="disable_controls('immunizations')"/><span class="text"> <?php xl('All','e'); ?></span>
		<input type="radio" id="immunizations" name="immunizations" value="select_immunization" onclick="enable_controls('immunizations')" /> 
		<span class="text"><?php xl('Select Immunizations','e'); ?></span> <br>
		<select id="immunization_list" name="immunization_list" size="10" width="300" style="width: 30%">
		</select> <br>
		<input type="button" name="add_immunization" id="add_immunization" value=<?php echo xl("Add Immunization"); ?> onclick="get_values('immunizations')">
		<input type="button" name="remove_immunization" id="remove_immunization" value=<?php echo xl("Remove"); ?> onclick="remove_selected('immunizations')">
		<br>
&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="2" class="style1">
		<input type="submit" name="Submit" value=<?php echo xl("Submit"); ?> ></td>
		<td>&nbsp;</td>
	</tr>
	
	<input type="hidden" name="diagnosis_text" id="diagnosis_text"><br>
		<input type="hidden" name="drug_text" id="drug_text"><br>
		<input type="hidden" name="immunization_text" id="immunization_text">
</table>
<script language='JavaScript'>
/* required for popup calendar */
Calendar.setup({inputField:"begin_date", ifFormat:"%Y-%m-%d", button:"img_begin_date"});
Calendar.setup({inputField:"end_date", ifFormat:"%Y-%m-%d", button:"img_end_date"});
</script>		
	<?php	
	}   
       }
      
   ?>
</form>
</body>
</html>
