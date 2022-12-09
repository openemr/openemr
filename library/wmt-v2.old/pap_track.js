
function SubmitPap(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=pap&wrap='+wrap;
  if(formID != '' && formID != 0) {
 		document.forms[0].action=base+'?mode=pap&wrap='+wrap+'&id='+formID;
	}
	document.forms[0].submit();
}

function UpdatePap(base,wrap,itemID,formID)
{
	SetScrollTop();
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Pap ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('pt_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Pap Tracking entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'?mode=updatepap&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'?mode=updatepap&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function DeletePap(base,wrap,itemID,formID)
{
	SetScrollTop();
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Pap Tracking ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('pt_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Pap Tracking entry...Aborting");
		return false;
	}
	var warn="  Delete This Pap Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\n       Are You Sure You Want To Delete This Pap "+
				"Entry?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
 		document.forms[0].action=base+'?mode=delpap&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0) {
 		document.forms[0].action=base+'?mode=delpap&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
		document.forms[0].submit();
	}
}

function UnlinkPap(base,wrap,itemID,formID)
{
	SetScrollTop();
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Pap ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('pt_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Pap Tracking entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'?mode=unlinkpap&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'?mode=unlinkpap&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

