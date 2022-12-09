// This is for callback by the find-code popup.
function set_cpt(codetype, code, codedesc, fee, codefield, descfield, feefield)
{
 var f = document.forms[0];
 var s = f.elements[codefield].value;
 var numargs = arguments.length;
 var isSpan = false;
 var tst = document.getElementById(descfield).type;
 if(tst == undefined) isSpan = true;
 if (code != '') {
  f.elements[codefield].value = code;
	if(f.elements[codefield].type.indexOf('check') != -1) {
		f.elements[codefield].checked = true;
  }
	if(isSpan) {
		while(document.getElementById(descfield).firstChild) {
			document.getElementById(descfield).removeChild(document.getElementById(descfield).firstChild);
		}
  	document.getElementById(descfield).appendChild( document.createTextNode(codedesc) );
	} else {
  	f.elements[descfield].value = codedesc;
	}
  if(feefield != '') f.elements[feefield].value = fee;
	if(numargs > 7) {
  	if(arguments[7] != '') f.elements[arguments[7]].value = codetype;
	}
	if(numargs > 8) {
  	if(arguments[8] != '') f.elements[arguments[8]].value = code;
	}
	return true;
 } else {
  f.elements[codefield].value = '';
	if(f.elements[codefield].type.indexOf('check') != -1) {
		f.elements[codefield].checked = false;
	}
	if(isSpan) {
		while(document.getElementById(descfield).firstChild) {
			document.getElementById(descfield).removeChild(document.getElementById(descfield).firstChild);
		}
	} else {
  	f.elements[descfield].value = '';
	}
  if(feefield != '') f.elements[feefield].value = '';
	if(numargs > 7) {
		if(arguments[7] != '') f.elements[arguments[7]].value = '';
	}
	if(numargs > 8) {
		if(arguments[8] != '') f.elements[arguments[8]].value = '';
	}
	return false;
 }
}

// This invokes the find-code popup.
function get_cpt(cptField)
{
 var numargs = arguments.length;
 var srch = document.forms[0].elements[cptField].value;
 var type = 'CPT4';
 var target = '../../../custom/cpt_code_popup.php?&thiscpt='+cptField;
 if(srch != '') target += '&bn_search=1&search_term='+srch;
 if(numargs > 1) {
	if(arguments[1] != '') target += '&thisdesc='+arguments[1];
 }
 if(numargs > 2) {
	if(arguments[2] != '') target += '&thisfee='+arguments[2];
 }
 if(numargs > 3) {
	if(arguments[3] != '') target += '&codetype='+arguments[3];
 }
 if(numargs > 4) {
	if(arguments[4] != '') target += '&addlcode='+arguments[4];
 }
 if(numargs > 5) {
	if(arguments[5] != '') target += '&thistype='+arguments[5];
 }
 wmtOpen(target, '_blank', 700, 800);
}
