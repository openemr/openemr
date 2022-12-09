
function SetCollectionDisplay()
{
	var val = document.getElementById('coll_meth').value;
	if(val == 'm' || val == 'w') { 
		document.getElementById('spill_label').innerHTML = 'Was there any spill?';
		document.getElementById('coll_spill').value = '';
		document.getElementById('spill_label').style.visibility = 'visible';
		document.getElementById('spill_choice').style.visibility = 'visible';
	} else if(val == 'i') { 
		document.getElementById('coll_spill').value = '';
		document.getElementById('spill_label').innerHTML = 'Was a condom used?';
		document.getElementById('spill_label').style.visibility = 'visible';
		document.getElementById('spill_choice').style.visibility = 'visible';
	} else {
		document.getElementById('spill_label').style.visibility = 'hidden';
		document.getElementById('spill_choice').style.visibility = 'hidden';
		document.getElementById('coll_spill').value = '';
		document.getElementById('spill_label').innerHTML = '';
	}
}

function SetNoteDisplay(select,note,text)
{
	var answer = document.getElementById(select).value.substring(0,1);
	var test = answer.toLowerCase();
	if(test == 'y') { 
		document.getElementById(note).style.display = 'block';
		document.getElementById(text).style.width = '97%';
		document.getElementById(text).rows = 4;
	} else {
		document.getElementById(note).style.display = 'none';
		document.getElementById(text).rows = 1;
		document.getElementById(text).value = '';
	}
}

function DisplayOptionalDiv()
{
	// var answer = document.getElementById('disp_category').value.substring(0,4);
	var answer = document.getElementById('disp_category').value;
	var test = answer.toLowerCase();
	if(test == 'cryo') { 
		document.getElementById('cryo_tank_display').style.display = 'block';
	} else {
		document.getElementById('cryo_tank_display').style.display = 'none';
		document.getElementById('disp_cryo_tank').value = '';
		document.getElementById('disp_cryo_bin').value = '';
		document.getElementById('disp_cryo_loc').value = '';
		document.getElementById('disp_cryo_vials').value = '';
		document.getElementById('disp_cryo_vial_amt').value = '';
		document.getElementById('disp_cryo_media').value = '';
		document.getElementById('disp_cryo_media_lot').value = '';
	}
	if(test == 'iui') { 
		document.getElementById('iui_pat_display').style.display = 'block';
	} else {
		document.getElementById('iui_pat_display').style.display = 'none';
		document.getElementById('disp_pat_id').value = '';
		document.getElementById('disp_pat_name').value = '';
	}
}

function CalcSpermOne()
{
  var test1 = Number(document.getElementById('anl_cnt1').value);
  var test2 = Number(document.getElementById('anl_cnt2').value);
	if(isNaN(test1) || isNaN(test2)) {
  	document.getElementById('anl_form').value = '';
  	document.getElementById('anl_tsc').value = '';
  	document.getElementById('anl_tms').value = '';
		return;
	}
	if(test1 == 0 || test2 == 0) {
  	document.getElementById('anl_form').value = '';
  	document.getElementById('anl_tsc').value = '';
  	document.getElementById('anl_tms').value = '';
		return;
	}
  var avg = Math.round((test1 + test2) / 2);
  // count = tmp.toFixed(1);
  if(avg == '0') avg = '';
  document.getElementById('anl_form').value = avg;
  var vol = Number(document.getElementById('anl_volume').value);
	var tsc = Number(0);
	if(isNaN(test1) || !vol) {
  	document.getElementById('anl_tsc').value = '';
	} else {
		var tsc = Math.round(avg * vol);
  	document.getElementById('anl_tsc').value = tsc;
	}
}

function CalcTMS()
{
  var mot = Number(document.getElementById('anl_mot').value);
  var tsc = Number(document.getElementById('anl_tsc').value);
	if(isNaN(tsc) || !mot) {
  	document.getElementById('anl_tms').value = '';
	} else {
  	var tms = Math.round(tsc * (mot / 100));
  	document.getElementById('anl_tms').value = tms;
	}
}

function LabelAgglutination()
{
	var answer = document.getElementById('anl_agg').value.substring(0,1);
	if(answer >= 2) {
		document.getElementById('agg_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('agg_label').innerHTML = '&nbsp;';
	}
}

function LabelProgression()
{
	var answer = document.getElementById('anl_prog').value.substring(0,1);
	if(answer == 1) {
		document.getElementById('prog_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('prog_label').innerHTML = '&nbsp;';
	}
}

function LabelWashProgression()
{
	var answer = document.getElementById('anl_wash_prog').value.substring(0,1);
	if(answer == 1) {
		document.getElementById('wash_prog_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('wash_prog_label').innerHTML = '&nbsp;';
	}
}

function LabelThawProgression()
{
	var answer = document.getElementById('anl_thaw_prog').value.substring(0,1);
	if(answer == 1) {
		document.getElementById('thaw_prog_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('thaw_prog_label').innerHTML = '&nbsp;';
	}
}

function LabelColor()
{
	var answer = document.getElementById('anl_color').value.substring(0,1);
	if(answer == 'r' || answer == 'b') {
		document.getElementById('color_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('color_label').innerHTML = '&nbsp;';
	}
}

function LabelViscosity()
{
	var answer = document.getElementById('anl_visc').value.substring(0,1);
	if(answer >= 3) {
		document.getElementById('visc_label').innerHTML = '* ABNORMAL *';
	} else {
		document.getElementById('visc_label').innerHTML = '&nbsp;';
	}
}

function DeleteDisposition(base,wrap,itemID,formID)
{
	SetScrollTop();
	base = base+'&continue=true';
	if(!ValidateItem(itemID, 'disp_id_', 'Disposition')) return false;
	var warn="Delete This Specimen Dispostion Entry?\n\nThis Action CAN NOT Be Reversed!";
	if(confirm(warn)) {
  	document.forms[0].action=base+'&mode=deldisp&wrap='+wrap+'&itemID='+itemID;
		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'&mode=deldisp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
		document.forms[0].submit();
	}
	return false;
}

function SubmitDisposition(base,wrap,formID)
{
	SetScrollTop();
	base = base+'&continue=true';
  document.forms[0].action=base+'&mode=adddisp&wrap='+wrap;
	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'&mode=adddisp&wrap='+wrap+'&id='+formID;
	}
	document.forms[0].submit();
}

