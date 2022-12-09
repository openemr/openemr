function setTimeStamp(chk, chk_dt)
{
	if(chk.checked == true) TimeStamp(chk_dt);
}

function disableForm()
{
  var f = document.forms[0];
  var i;
  var l = f.elements.length;
	
  for (i=0; i<l; i++) {
		if(f.elements[i].type.indexOf('check') != -1) 
			f.elements[i].readonly = true;
		if(f.elements[i].type.indexOf('text') != -1) 
			f.elements[i].readonly = true;
		if(f.elements[i].type.indexOf('select') != -1) 
			f.elements[i].readonly = true;
   }
}

$(document).ready(function() {
	var certified = document.getElementById('referral').value;
	if(certified) disableForm();
});
