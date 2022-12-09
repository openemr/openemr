
function togglePQRSToNull(section, client_id)
{
  var i;
	var n;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('checkbox') != -1) {
      if(document.forms[0].elements[i].name.indexOf('tmp_pqrs_') != -1) {
        document.forms[0].elements[i].checked = false;
      }
    }
  }
}

