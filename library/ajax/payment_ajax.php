<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2005-2010 Z&H Healthcare Solutions, LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Paul Simon K <paul@zhservices.com> 
//
// +------------------------------------------------------------------------------+
//===============================================================================
//This section handles ajax for insurance,patient and for encounters.
//===============================================================================
require_once("../../interface/globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formatting.inc.php");
//=================================
if (isset($_REQUEST["ajax_mode"]))
 {
  AjaxDropDownCode();
 }
//=================================
function AjaxDropDownCode()
 {
  if ($_REQUEST["ajax_mode"] == "set")//insurance
   {
	$CountIndex=1;
	$StringForAjax="<div id='AjaxContainerInsurance'><table width='552' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='50'>".htmlspecialchars( xl('Code'), ENT_QUOTES)."</td>
		<td width='300'>".htmlspecialchars( xl('Name'), ENT_QUOTES)."</td>
	    <td width='200'>".htmlspecialchars( xl('Address'), ENT_QUOTES)."</td>
	  </tr>".
	//ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	//PlaceValues(event,'&nbsp;','')==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClick('&nbsp;','')==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	  "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);PlaceValues(event,'&nbsp;','')\"   onclick=\"PutTheValuesClick('&nbsp;','')\">
			<td colspan='3' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>";
	$insurance_text_ajax=formData('insurance_text_ajax','',true);
	$res = sqlStatement("SELECT insurance_companies.id,name,city,state,country FROM insurance_companies
			left join addresses on insurance_companies.id=addresses.foreign_id  where name like '$insurance_text_ajax%' or  insurance_companies.id like '$insurance_text_ajax%' ORDER BY name");
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Id=$row['id'];
		$Name=$row['name'];
		$City=$row['city'];
		$State=$row['state'];
		$Country=$row['country'];
		$Address=$City.', '.$State.', '.$Country;
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		onkeydown=\"ProcessKeyForColoring(event,$CountIndex);PlaceValues(event,'".htmlspecialchars($Id,ENT_QUOTES)."','".htmlspecialchars($Name,ENT_QUOTES)."')\"
			   onclick=\"PutTheValuesClick('".htmlspecialchars($Id,ENT_QUOTES)."','".htmlspecialchars($Name,ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#'>".htmlspecialchars($Id)."</a></td>
			<td><a href='#'>".htmlspecialchars($Name)."</a></td>
		    <td><a href='#'>".htmlspecialchars($Address)."</a></td>
</tr>";
	 }
	$StringForAjax.="</table></div>";
	echo strlen($_REQUEST['insurance_text_ajax']).'~`~`'.$StringForAjax;
	die;
   }
//===============================================================================
  if ($_REQUEST["ajax_mode"] == "set_patient")//patient.
   {//From 2 areas this ajax is called.So 2 pairs of functions are used.
	//PlaceValues==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClick==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PlaceValuesDistribute==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClickDistribute==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	if(isset($_REQUEST['patient_code']) && $_REQUEST['patient_code']!='')
	 {
		$patient_code=formData('patient_code','',true);
		if(isset($_REQUEST['submit_or_simple_type']) && $_REQUEST['submit_or_simple_type']=='Simple')
		 {
			$StringToAppend="PutTheValuesClickPatient";
			$StringToAppend2="PlaceValuesPatient";
		 }
		else
		 {
			$StringToAppend="PutTheValuesClickDistribute";
			$StringToAppend2="PlaceValuesDistribute";
		 }
		$patient_code_complete=$_REQUEST['patient_code'];//we need the spaces here
	 }
	elseif(isset($_REQUEST['insurance_text_ajax']) && $_REQUEST['insurance_text_ajax']!='')
	 {
		$patient_code=formData('insurance_text_ajax','',true);
		$StringToAppend="PutTheValuesClick";
		$StringToAppend2="PlaceValues";
		$patient_code_complete=$_REQUEST['insurance_text_ajax'];//we need the spaces here
	 }
	$CountIndex=1;
	$StringForAjax="<div id='AjaxContainerPatient'><table width='452' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='50'>".htmlspecialchars( xl('Code'), ENT_QUOTES)."</td>
		<td width='100'>".htmlspecialchars( xl('Last Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars( xl('First Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars( xl('Middle Name'), ENT_QUOTES)."</td>
	    <td width='100'>".htmlspecialchars( xl('Date of Birth'), ENT_QUOTES)."</td>
	  </tr>".
	//ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	  "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='5' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>

	  ";
	$res = sqlStatement("SELECT pid as id,fname,lname,mname,DOB FROM patient_data
			 where  fname like '$patient_code%' or lname like '$patient_code%' or mname like '$patient_code%' or 
			 CONCAT(lname,' ',fname,' ',mname) like '$patient_code%' or pid like '$patient_code%' ORDER BY lname");
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Id=$row['id'];
		$fname=$row['fname'];
		$lname=$row['lname'];
		$mname=$row['mname'];
		$Name=$lname.' '.$fname.' '.$mname;
		$DOB=oeFormatShortDate($row['DOB']);
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'".htmlspecialchars($Id,ENT_QUOTES)."','".htmlspecialchars($Name,ENT_QUOTES)."')\"   onclick=\"$StringToAppend('".htmlspecialchars($Id,ENT_QUOTES)."','".htmlspecialchars($Name,ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Id)."</a></td>
			<td><a href='#'>".htmlspecialchars($lname)."</a></td>
		    <td><a href='#'>".htmlspecialchars($fname)."</a></td>
            <td><a href='#'>".htmlspecialchars($mname)."</a></td>
            <td><a href='#'>".htmlspecialchars($DOB)."</a></td>
  </tr>";
	 }
	$StringForAjax.="</table></div>";
	echo strlen($patient_code_complete).'~`~`'.$StringForAjax;
	die;
   }
//===============================================================================
  if ($_REQUEST["ajax_mode"] == "encounter")//encounter
   {
	//PlaceValuesEncounter==>Used while -->KEY PRESS<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	//PutTheValuesClickEncounter==>Used while -->CLICK<-- over list.List vanishes and the clicked one gets listed in the parent page's text box.
	if(isset($_REQUEST['encounter_patient_code']))
	 {
		$patient_code=formData('encounter_patient_code','',true);
		$StringToAppend="PutTheValuesClickEncounter";
		$StringToAppend2="PlaceValuesEncounter";
	 }
	$CountIndex=1;
	$StringForAjax="<div id='AjaxContainerEncounter'><table width='202' border='1' cellspacing='0' cellpadding='0'>
	  <tr class='text' bgcolor='#dddddd'>
		<td width='100'>".htmlspecialchars( xl('Encounter'), ENT_QUOTES)."</td>
		<td width='100'>".htmlspecialchars( xl('Date'), ENT_QUOTES)."</td>
	  </tr>".
	//ProcessKeyForColoring(event,$CountIndex)==>Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	  "<tr class='text' height='20'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
	  onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'&nbsp;','')\"   onclick=\"$StringToAppend('&nbsp;','')\">
			<td colspan='2' align='center'><a id='anchor_insurance_code_$CountIndex' href='#'></a></td>
	  </tr>

	  ";
	$res = sqlStatement("SELECT date,encounter FROM form_encounter
			 where pid ='$patient_code' ORDER BY encounter");
	while ($row = sqlFetchArray($res))
	 {
		if($CountIndex%2==1)
		 {
			$bgcolor='#ddddff';
		 }
		else
		 {
			$bgcolor='#ffdddd';
		 }
		$CountIndex++;
		$Date=$row['date'];
		$Date=split(' ',$Date);
		$Date=oeFormatShortDate($Date[0]);
		$Encounter=$row['encounter'];
		$StringForAjax.="<tr class='text'  bgcolor='$bgcolor' id=\"tr_insurance_$CountIndex\"
		 onkeydown=\"ProcessKeyForColoring(event,$CountIndex);$StringToAppend2(event,'".htmlspecialchars($Encounter,ENT_QUOTES)."','".htmlspecialchars($Date,ENT_QUOTES)."')\"   onclick=\"$StringToAppend('".htmlspecialchars($Encounter,ENT_QUOTES)."','".htmlspecialchars($Date,ENT_QUOTES)."')\">
			<td><a id='anchor_insurance_code_$CountIndex' href='#' >".htmlspecialchars($Encounter)."</a></td>
			<td><a href='#'>".htmlspecialchars($Date)."</a></td>
  </tr>";
	 }
	$StringForAjax.="</table></div>";
	echo $StringForAjax;
	die;
   }
 }
?>