function SubmitBoneDensity(base,wrap,formID)
{
  document.forms[0].action=base+'&mode=bone&wrap='+wrap;
  if(formID != '' && formID != 0) {
 		document.forms[0].action=base+'&mode=bone&wrap='+wrap+'&id='+formID;
	}
	document.forms[0].submit();
}

function UpdateBoneDensity(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Bone Density Test ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('bd_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid list entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'&mode=updatebone&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'&mode=updatebone&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteBoneDensity(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Bone Density Test ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('bd_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid Bone Density ID...Aborting");
		return false;
	}
	var warn="  Delete This Bone Density?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\n     Are You Sure You Want To Delete This "+
				"Bone Density?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'&mode=delbone&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'&mode=delbone&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
		document.forms[0].submit();
	}
	return false;
}

function UnlinkBoneDensity(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Bone Density Test ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('bd_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid list entry...Aborting");
		return false;
	}
  document.forms[0].action=base+'&mode=unlinkbone&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'&mode=unlinkbone&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
	return false;
}

