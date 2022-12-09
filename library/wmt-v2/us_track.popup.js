function SubmitUltrasound(base,wrap,formID)
{
  document.forms[0].action=base+'&mode=ultra&wrap='+wrap;
  if(formID != '' && formID != 0) {
 		document.forms[0].action=base+'&mode=ultra&wrap='+wrap+'&id='+formID;
	}
	document.forms[0].submit();
}

function UpdateUltrasound(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Ultrasound ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('us_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid list entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'&mode=updateultra&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'&mode=updateultra&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteUltrasound(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Ultrasound ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('us_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Ultrasound...Aborting");
		return false;
	}
	var warn="  Delete This Ultrasound?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\n       Are You Sure You Want To Delete This "+
				"Ultrasound?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'&mode=delultra&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'&mode=delultra&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
		document.forms[0].submit();
	}
	return false;
}

function UnlinkUltrasound(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Ultrasound ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('us_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid list entry...Aborting");
		return false;
	}

 	document.forms[0].action=base+'&mode=unlinkultra&wrap='+wrap+'&itemID='+itemID;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'&mode=unlinkultra&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
	return false;
}

function PopUltrasounds(pid) {
	var test= top.frames.length;
	alert("Test: "+test);
	dlgopen('../../../controller.php?document&view&patient_id='+pid+'&doc_id=', '_self', 800, 800);
}
