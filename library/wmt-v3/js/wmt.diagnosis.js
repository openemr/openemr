
function ToggleDiagWindowMode(base,wrap,formID,mode)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true';
	} else {
		base += '&continue=true';
	}
  document.forms[0].action = base+'&mode=window&disp='+mode+'&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_diag(codetype, code, selector, codedesc, codefield)
{
	var f = document.forms[0];
	var s = f.elements[codefield].value;
	var numargs = arguments.length;
	var isSpan = false;
	if(numargs >= 6) {
 		var target = document.getElementById(arguments[5]).type;
 		if(target == undefined) isSpan = true;
 		target = document.getElementById(arguments[5]);
	}
	if (code) {
		f.elements[codefield].value = code;
		if(numargs >= 6) {
			if(arguments[5] != '') {
				if(isSpan) {
					while(target.firstChild) {
						target.removeChild(target.firstChild);
					}
					target.appendChild(document.createTextNode(codedesc));
				} else {
					f.elements[arguments[5]].value = codedesc;
				}
			}
		}
		if(numargs >= 7) {
			if(arguments[6] != '') SetDatetoToday(arguments[6]);
		}
		if(numargs >= 8) {
			if(arguments[7] != '') f.elements[arguments[7]].focus();
			if(arguments[7] != '') f.elements[arguments[7]].value = code+' - '+codedesc;
		}
		if(numargs >= 9) {
			if(arguments[8] != '') f.elements[arguments[8]].value = codetype;
		}
		if(numargs >= 10) {
			if(arguments[9] != '') f.elements[arguments[9]].checked = true;
			if(arguments[9] != '') f.elements[arguments[9]].value= code;
		}
		return true;
	} else {
 		f.elements[codefield].value = '';
		if(numargs >= 6) {
			if(arguments[5] != '') {
				if(isSpan) {
					while(target.firstChild) {
						target.removeChild(target.firstChild);
					}
				} else {
					f.elements[arguments[5]].value = '';
				}
			}
		}
		if(numargs >= 7) {
			if(arguments[6] != '') f.elements[arguments[6]].value = '';
		}
		if(numargs >= 9) {
			if(arguments[8] != '') f.elements[arguments[8]].value = '';
		}
		if(numargs >= 10) {
			if(arguments[9] != '') f.elements[arguments[9]].checked = false;
			if(arguments[9] != '') f.elements[arguments[9]].value = '';
		}
		return false;
	}
}

// This invokes the find-code popup.
function get_diagnosis(diagField)
{
	var numargs = arguments.length;
	var srch = document.forms[0].elements[diagField].value;
	var code_type = '';
	var type_check = '';
	// To pass the code type with an existing field
	if(numargs >= 5) {
		type_check = arguments[4].substr(0,3);
		if(type_check.toLowerCase() == 'icd') {
			code_type = arguments[4];
		} else {	
			code_type = document.forms[0].elements[arguments[4]].value;
		}
	}
	var target = '../../../custom/diag_code_popup.php?thisdiag='+diagField;
	if(code_type != '') target += '&codetype='+code_type;
	if(srch != '') target += '&bn_search=1&search_term='+srch;
	if(numargs >= 2) target += '&thisdesc='+arguments[1];
	if(numargs >= 3) target += '&thisdate='+arguments[2];
	if(numargs >= 4) target += '&nextfocus='+arguments[3];
	if(numargs >= 5) {
		if(type_check.toLowerCase() == 'icd') {
			code_type = arguments[4];
		} else {	
			target += '&thistype='+arguments[4];
		}
	}
	if(numargs >= 6) {
		if(type_check.toLowerCase() == 'icd') {
			target += '&thischeck='+arguments[5];
		}
	}
	wmtOpen(target, '_blank', 700, 800);
}

function AddDiagnosis(base,wrap,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true';
	} else {
		base += '&continue=true';
	}
  document.forms[0].action = base+'&mode=diag&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

function UpdateDiagnosis(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true&mode=updatediag';
	} else {
		base += '&continue=true&mode=updatediag';
	}
	if(!ValidateItem(itemID, 'dg_id_', 'Diagnosis')) return false;
	document.forms[0].action = base+'&wrap='+wrap+'&itemID='+itemID;
 	if(formID != '' && formID != 0 && formID != null) {
		document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteDiagnosis(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true&mode=deldiag';
	} else {
		base += '&continue=true&mode=deldiag';
	}
	if(!ValidateItem(itemID, 'dg_id_', 'Diagnosis')) return false;
	if(confirm("      Delete This Diagnosis?\n\nThis Action Can Not Be Reversed!")) {

  	document.forms[0].action = base+'&wrap='+wrap+'&itemID='+itemID;
 		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action += '&id='+formID;
		}
		document.forms[0].submit();
	}
}

function UnlinkAllDiagnoses(base,wrap,maxItems,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true';
	} else {
		base += '&continue=true';
	}
 	document.forms[0].action = base+'&mode=unlinkalldiags&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

function UnlinkDiagnosis(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true&mode=unlinkdiag';
	} else {
		base += '&continue=true&mode=unlinkdiag';
	}
	if(!ValidateItem(itemID, 'dg_id_', 'Diagnosis')) return false;
 	document.forms[0].action=base+'&wrap='+wrap+'&itemID='+itemID;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

function LinkDiagnosis(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(base.indexOf('?') == -1) {
		base += '?continue=true';
	} else {
		base += '&continue=true';
	}
	if(!ValidateItem(itemID, 'dg_id_', 'Diagnosis')) return false;
 	document.forms[0].action = base+'&mode=linkdiag&wrap='+wrap+'&itemID='+itemID;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action += '&id='+formID;
	}
	document.forms[0].submit();
}

// This the 'Plan Favorites' popup window.
function GetPlan(plan_field, code_field, type_field)
{
	var code = document.getElementById(code_field).value;
	var type = document.getElementById(type_field).value;
	var link_base = '../../..';
	if(arguments.length > 3) link_base = arguments[3];
	if(!code || code == '') {
		alert("Please choose a diagnosis code before searching for a plan");
		return false;
	}
	if(!type || type == '') {
		alert("Fatal Error - No Code Type Was Provided");
		return false;
	}
	wmtOpen(link_base+'/interface/forms/favorites/new.php?choose=yes&ctype='+type+'&code='+code+'&target='+plan_field, '_blank', 800, 600);
}

// This is for callback by the Favorite look-up  popup.
function set_plan(plan_field, plan) {
 var decodedPlan = window.atob(plan);
 if (decodedPlan) {
   document.forms[0].elements[plan_field].value = decodedPlan;
 }
}
