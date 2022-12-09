

function toggleADHDNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("adhd_") == 0) {
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

