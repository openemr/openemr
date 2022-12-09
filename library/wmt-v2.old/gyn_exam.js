

function toggleGynExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("gyn_") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex= 0;
			}
    }
  }
}

function toggleGynExamNormal()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("gyn_") == 0) {
			// First clear all the entries
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
				// Then recheck it if it's the normal one
    		if(document.forms[0].elements[i].name.indexOf("_wnl") != -1) {
      		document.forms[0].elements[i].checked = true;
				}
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex= 0;
			}
    }
  }
}

function afterAtrophic()
{
	if(document.forms[0].elements['gyn_vag_atro_type'].selectedIndex != 0) {
		document.forms[0].elements['gyn_vag_atro'].checked = true;
		document.forms[0].elements['gyn_vag_wnl'].checked = false;
	}
}

function afterCystocele()
{
	if(document.forms[0].elements['gyn_vag_cys_type'].selectedIndex != 0) {
		document.forms[0].elements['gyn_vag_cys'].checked=true;
		document.forms[0].elements['gyn_vag_wnl'].checked=false;
	}
}

function afterRectocele()
{
	if(document.forms[0].elements['gyn_vag_rec_type'].selectedIndex != 0) {
		document.forms[0].elements['gyn_vag_rec'].checked=true;
		document.forms[0].elements['gyn_vag_wnl'].checked=false;
	}
}

function afterVagina()
{
	if(document.forms[0].elements['gyn_vag_wnl'].checked == true) {
		document.forms[0].elements['gyn_vag_atro_type'].selectedIndex = 0;
		document.forms[0].elements['gyn_vag_cys_type'].selectedIndex = 0;
		document.forms[0].elements['gyn_vag_rec_type'].selectedIndex = 0;
	}
	
}

