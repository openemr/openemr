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
//This section handles ajax functions for insurance,patient and for encounters.
//===============================================================================
?>
<script type="text/javascript">
$(document).ready(function(){	
  $("#type_code").keyup(function(e){
	  if (e.which == 9 || e.which == 13)
		 {//tab key,enter key.Prevent ajax activity.
		  return false;
		 }
		else
		 {//Both insurance or patient can come.The drop down value in 'type_name' decides which one to process.
		   ajaxFunction('non');
		   return;
		 }
  });	
  $("#patient_code").keyup(function(e){
	  if (e.which == 9 || e.which == 13)
		 {//tab key,enter key.Prevent ajax activity.
		  return false;
		 }
		else
		 {
		   ajaxFunction('patient');
		   return;
		 }
  });	
  function ajaxFunction(Source) {
  if(Source=='encounter')
   {
	  document.getElementById('ajax_mode').value='encounter';
   }
  else if(Source=='patient')
   {
	  if(document.getElementById('patient_code').value.length<3)
	   return false;
	  document.getElementById('ajax_mode').value='set_patient';
   }
   //For the below two cases, same text box is used for both insurance and patient.
  else if(document.getElementById('type_name') && document.getElementById('type_name').options[document.getElementById('type_name').selectedIndex].value=='patient')
   {//Patient
	  if(document.getElementById('type_code').value.length<3)
	   return false;
	  document.getElementById('ajax_mode').value='set_patient';
   }
  else
   {//Insurance
	  if(document.getElementById('type_code').value.length<3)
	   return false;
	  document.getElementById('ajax_mode').value='set';
   }
//Send ajax request
   $.ajax({
    type: "POST",
    url: "../../library/ajax/payment_ajax.php",
    dataType: "html",
    data: {
     ajax_mode: document.getElementById('ajax_mode').value,
     patient_code: Source=='patient' ? document.getElementById('patient_code').value : '',
    insurance_text_ajax: document.getElementById('type_code') ? document.getElementById('type_code').value : '',
	encounter_patient_code:Source=='encounter' ? document.getElementById('hidden_patient_code').value : ''
   },
    success: function(thedata){//alert(thedata)
	document.getElementById('ajax_mode').value='';
	  if(Source=='encounter')
	   {
		 $("#ajax_div_encounter_error").empty();
		 $("#ajax_div_encounter").empty();
		 $("#ajax_div_encounter").html(thedata);
		 $("#ajax_div_encounter").show();
	   }
	  else if(Source=='patient')
	   {
		 $("#ajax_div_patient_error").empty();
		 $("#ajax_div_patient").empty();
		 $("#ajax_div_insurance_error").empty();
		 $("#ajax_div_insurance").empty();
		 $("#ajax_div_patient").html(thedata);
		 $("#ajax_div_patient").show();
	   }
	  else
	   {//Patient or Insurance
		 $("#ajax_div_patient_error").empty();
		 $("#ajax_div_patient").empty();
		 $("#ajax_div_insurance_error").empty();
		 $("#ajax_div_insurance").empty();
		 $("#ajax_div_insurance").html(thedata);
		 $("#ajax_div_insurance").show();
	   }
	if(document.getElementById('anchor_insurance_code_1'))
		document.getElementById('anchor_insurance_code_1').focus();
	if(document.getElementById('tr_insurance_1'))
		document.getElementById('tr_insurance_1').bgColor='#94D6E7'//selected color
    },
    error:function(){
    }	
   });
   return;		
  }
 });
//==============================================================================================================================================
//Following functions are needed for other tasks related to ajax.
//Html retured from the ajax above, contains list of either insurance,patient or encounter.
//On click or 'enter key' press over any one item the listing vanishes and the clicked one gets listed in the parent page's text box.
//List of functions starts
//===========================================================
function PutTheValuesClick(Code,Name)
 {//Used while -->CLICK<-- over list in the insurance/patient portion.
  document.getElementById('type_code').value=Name;
  document.getElementById('hidden_ajax_close_value').value=Name;
  document.getElementById('description').value=Name;
  document.getElementById('hidden_type_code').value=Code;
  document.getElementById('div_insurance_or_patient').innerHTML=Code;
  document.getElementById('ajax_div_insurance').style.display='none';
  document.getElementById('type_code').focus();
 }
function PutTheValuesClickDistribute(Code,Name)
 {//Used while -->CLICK<-- over list in the patient portion before the start of distribution of amount.
  document.getElementById('patient_code').value=Name;
  document.getElementById('hidden_ajax_patient_close_value').value=Name;
  document.getElementById('hidden_patient_code').value=Code;
  document.getElementById('patient_name').innerHTML=Code;
  document.getElementById('ajax_div_patient').style.display='none';
  document.getElementById('patient_name').focus();
	document.getElementById('mode').value='search';
	top.restoreSession();
	document.forms[0].submit();
 }
function PutTheValuesClickEncounter(Code,Name)
 {//Used while -->CLICK<-- over list in the encounter portion.
  document.getElementById('encounter_no').value=Code;
  document.getElementById('hidden_ajax_encounter_close_value').value=Code;
  document.getElementById('hidden_encounter_no').value=Code;
  document.getElementById('encounter_date').innerHTML=Name;
  document.getElementById('ajax_div_encounter').style.display='none';
  document.getElementById('encounter_date').focus();
	document.getElementById('mode').value='search_encounter';
	top.restoreSession();
	document.forms[0].submit();
 }
function PlaceValues(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the insurance/patient portion.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClick(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('type_code').focus();
	 }
 }
function PlaceValuesDistribute(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the patient portion before the start of distribution of amount.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClickDistribute(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written  
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('patient_code').focus();
	 }
 }
function PlaceValuesEncounter(evt,Code,Name)
 {//Used while -->KEY PRESS<-- over list in the encounter portion.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 13)//enter key
	 {//Vanish the list and populate the parent text box
	  PutTheValuesClickEncounter(Code,Name);
	  PreventIt(evt)  //For browser chorome.It gets submitted,to prevent it the PreventIt(evt) is written
	 }
	else if(!((charCode == 38) || (charCode == 40)))
	 {//if non arrow keys, focus on the parent text box(ie he again types and wants ajax to activate)
	  document.getElementById('encounter_no').focus();
	 }
 }
function ProcessKeyForColoring(evt,Location)
 {//Shows the navigation in the listing by change of colors and focus.Happens when down or up arrow is pressed.
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode == 38)//Up key press
	 {
		Location--;
		if(document.getElementById('tr_insurance_' + (Location)))
		 {
			//restore color in below row
			if((Location+1)%2==1)
			 {
			 document.getElementById('tr_insurance_' + (Location+1)).bgColor='#ddddff';
			 }
			else
			 {
			 document.getElementById('tr_insurance_' + (Location+1)).bgColor='#ffdddd';
			 }
			document.getElementById('tr_insurance_' + (Location)).bgColor='#94D6E7';
			document.getElementById('anchor_insurance_code_' + (Location)).focus();
		 }
	 }
	else if (charCode == 40)//Down key press
	 {
		Location++;
		if(document.getElementById('tr_insurance_' + (Location)))
		 {
			//restore color in above row
			 if((Location-1)%2==1)
			 {
			 document.getElementById('tr_insurance_' + (Location-1)).bgColor='#ddddff';
			 }
			else
			 {
			 document.getElementById('tr_insurance_' + (Location-1)).bgColor='#ffdddd';
			 }
			document.getElementById('tr_insurance_' + (Location)).bgColor='#94D6E7';
			document.getElementById('anchor_insurance_code_' + (Location)).focus();
		 }
	 }
 }
function HideTheAjaxDivs()
 {//Starts working when clicking on the body.Hides the ajax and restores the codes back, as he may have changed it in the text box.
  if(document.getElementById('ajax_div_insurance'))
   {
	  if(document.getElementById('ajax_div_insurance').style.display!='none')
	   {
		  document.getElementById('type_code').value=document.getElementById('hidden_ajax_close_value').value;
		 $("#ajax_div_patient_error").empty();
		 $("#ajax_div_patient").empty();
		 $("#ajax_div_insurance_error").empty();
		 $("#ajax_div_insurance").empty();
		 $("#ajax_div_insurance").hide();
	   }
   }
  if(document.getElementById('ajax_div_patient'))
   {
	  if(document.getElementById('ajax_div_patient').style.display!='none')
	   {
		  document.getElementById('patient_code').value=document.getElementById('hidden_ajax_patient_close_value').value;
		 $("#ajax_div_patient_error").empty();
		 $("#ajax_div_patient").empty();
		 $("#ajax_div_insurance_error").empty();
		 $("#ajax_div_insurance").empty();
		 $("#ajax_div_patient").hide();
	   }
   }
  if(document.getElementById('ajax_div_encounter'))
   {
	  if(document.getElementById('ajax_div_encounter').style.display!='none')
	   {
		  document.getElementById('encounter_no').value=document.getElementById('hidden_ajax_encounter_close_value').value;
		 $("#ajax_div_encounter_error").empty();
		 $("#ajax_div_encounter").empty();
		 $("#ajax_div_encounter").hide();
	   }
   }
 }
//===========================================================
//List of functions ends
//==============================================================================================================================================
</script>