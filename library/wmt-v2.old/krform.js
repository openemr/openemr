function setWeeksFrom(oldDate, newDate, targetColumn)
{
	var date1 = document.getElementById(oldDate).value;
	if(date1 == 0 || date1 == '') return false;	
	date1 = new Date(date1);
	if(date1 == 'Invalid Date') {
		alert("Surgery Date is NOT a Valid Date, Use 'YYYY-MM-DD' for Auto Calculating");
		return false;
	}
	var date2 = document.getElementById(newDate).value;
	if(date2 == 0 || date2 == '') return false;	
	date2 = new Date(date2);
	if(date2 == 'Invalid Date') {
		alert("Follow Up Date is NOT a Valid Date, Use 'YYYY-MM-DD' for Auto Calculating");
		return false;
	}
	var one_week = 1000 * 60 * 60 * 24 * 7;
	var seconds1 = date1.getTime();
	var seconds2 = date2.getTime();
	if(seconds1 > seconds2) {
		alert("The Follow Up Date is Prior to the Surgery Date");
		document.getElementById('kf_vis_num_'+targetColumn).value= '';
		return false;	
	}
  var mySeconds= Math.abs(seconds2 - seconds1);
	var myWeeks = Math.round(mySeconds / one_week);
	document.getElementById('kf_vis_num_'+targetColumn).value= myWeeks;
	return true;
}

function setKneeVisitNum(thisField,thisColumn)
{
	var my_val = document.getElementById(thisField).value;
	document.getElementById('tmp_label_lax_'+thisColumn).value = my_val;
	document.getElementById('tmp_label_align_'+thisColumn).value = my_val;
	document.getElementById('tmp_label_comp_'+thisColumn).value = my_val;
	document.getElementById('tmp_label_pat_'+thisColumn).value = my_val;
	document.getElementById('tmp_label_xray_'+thisColumn).value = my_val;
	document.getElementById('tmp_label_screw_'+thisColumn).value = my_val;
}

function clearKneeFU(thisID)
{
	var i;
	var l = document.knee_replace.elements.length;
	for(i=0; i<l; i++) {
		if(document.knee_replace.elements[i].name.indexOf('_'+thisID) == -1) continue;
		if(document.knee_replace.elements[i].type.indexOf('select') != -1) {
			document.knee_replace.elements[i].selectedIndex = 0;
		} else if(document.knee_replace.elements[i].type.indexOf('check') != -1) {
			document.knee_replace.elements[i].checked = false;
		} else {
			document.knee_replace.elements[i].value = '';
		}
	}
}

function UnlinkKneeFollowUp(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid Follow Up ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('kf_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid list entry...Aborting");
		return false;
	}
	var list=document.getElementById('kf_id_'+itemID).value;
	clearKneeFU(itemID);
 	document.forms[0].action=base+'&mode=unlinkkfu&wrap='+wrap+'&itemID='+itemID+'&listID='+list;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'&mode=unlinkkfu&wrap='+wrap+'&itemID='+itemID+'&id='+formID+'&listID='+list;
	}
	document.forms[0].submit();
}
