function toggleFamilyExtraNo()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('mcw_fh_') != -1) {
  			document.forms[0].elements[i].selectedIndex = '2';
			}
		}
	}
}

function get_family_defaults() {
	var thisPerson=document.getElementById('fh_who').value;
	var thisField='fh_def_'+thisPerson+'[]';
	var traits=document.forms[0].elements[thisField];
	// alert("Number of Traits: "+traits.length);
	// for (var cnt=0; cnt < traits.length; cnt++) alert("This Trait: "+traits[cnt].value);
	document.forms[0].elements['fh_dead'].value=traits[1].value;
	document.forms[0].elements['fh_age'].value=traits[2].value;
	document.forms[0].elements['fh_age_dead'].value=traits[3].value;
}
