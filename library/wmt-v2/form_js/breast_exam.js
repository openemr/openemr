function toggleBreastExamNull()
{
  var f = document.forms[0];
  var i;
  var l = f.elements.length;
  for (i=0; i<l; i++) {
    if(f.elements[i].name.indexOf("bre_") == 0) {
			if(f.elements[i].type.indexOf('check') != -1) 
				f.elements[i].checked = false;
			if(f.elements[i].type.indexOf('text') != -1) 
				f.elements[i].value = '';
			if(f.elements[i].type.indexOf('select') != -1) 
				f.elements[i].selectedIndex= 0;
    }
  }
}

function toggleBreastExamNormal()
{
  document.forms[0].elements['bre_br_axil'].selectedIndex='2';
  document.forms[0].elements['bre_br_mass'].selectedIndex='2';
  document.forms[0].elements['bre_nr_ev'].selectedIndex='1';
  document.forms[0].elements['bre_nr_in'].selectedIndex='2';
  document.forms[0].elements['bre_nr_mass'].selectedIndex='2';
  document.forms[0].elements['bre_nr_dis'].selectedIndex='2';
  document.forms[0].elements['bre_nr_ret'].selectedIndex='2';
  document.forms[0].elements['bre_bl_axil'].selectedIndex='2';
  document.forms[0].elements['bre_bl_mass'].selectedIndex='2';
  document.forms[0].elements['bre_nl_ev'].selectedIndex='1';
  document.forms[0].elements['bre_nl_in'].selectedIndex='2';
  document.forms[0].elements['bre_nl_mass'].selectedIndex='2';
  document.forms[0].elements['bre_nl_dis'].selectedIndex='2';
  document.forms[0].elements['bre_nl_ret'].selectedIndex='2';
}

