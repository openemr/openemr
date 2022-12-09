
function toggleConstipationExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_const") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

function togglePainExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_abd") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

function toggleVomitExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_vom") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

function toggleBleedExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_bld") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

function toggleBowelExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_bwl") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

function toggleDietExamNull()
{
  var i;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].name.indexOf("pad_diet_") == 0) {
			if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      	document.forms[0].elements[i].checked = false;
			}
			if(document.forms[0].elements[i].type.indexOf('text') != -1) {
      	document.forms[0].elements[i].value = '';
			}
			if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      	document.forms[0].elements[i].selectedIndex = 0;
			}
    }
  }
}

