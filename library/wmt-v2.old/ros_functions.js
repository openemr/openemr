
function toggleROSTypeToNull(obj, section, client_id)
{
	var segments = obj.name.split("_");
	if(obj.checked == true) {
		if(segments.length > 2) {
			if(segments[2] == 'hpi') {
				document.forms[0].elements[segments[0]+'_'+segments[1]+'_none'].checked = false;
			}
			if(segments[2] == 'none') {
				document.forms[0].elements[segments[0]+'_'+segments[1]+'_hpi'].checked = false;
			}
		}
	}
	var skip = false;
	var a = arguments.length;
	if(a >= 4) skip = arguments[3];
	if(skip) return true;
  var i;
  var n;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('rs_'+section) == 0) {
        document.forms[0].elements[i].selectedIndex = '0';
      	n = document.forms[0].elements[i].name + '_nt';
        document.forms[0].elements[n].value= '';
      }
    }
  }
}

function toggleROStoNull()
{
  var i;
	var n;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf('rs_') == 0) {
        document.forms[0].elements[i].selectedIndex = '0';
      	n = document.forms[0].elements[i].name + '_nt';
        document.forms[0].elements[n].value= '';
      }
    }
    if(document.forms[0].elements[i].name.indexOf('ros_') == 0) {
    	if(document.forms[0].elements[i].type.indexOf('check') != -1) {
				document.forms[0].elements[i].checked = false;
			} else if(document.forms[0].elements[i].type.indexOf('check') != -1) {
			} else {
				document.forms[0].elements[i].value = '';
			}
		}
  }
}

function toggleROStoNo()
{
  var fld_prefix = 'rs_';
	if(arguments.length > 0) fld_prefix = arguments[0];
  var i;
	var n;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') != -1) {
      if(document.forms[0].elements[i].name.indexOf(fld_prefix) == 0) {
        document.forms[0].elements[i].selectedIndex = '2';
      	// n = document.forms[0].elements[i].name + '_nt';
        // document.forms[0].elements[n].value= '';
      }
    }
  }
}

function toggleNoProblem()
{
	if(!confirm("\t\t\t\t* WARNING *\n\nThis will clear any ROS entries in the current form.\n\nClick 'Cancel' if you have data you want to keep.")) return false;
	toggleROStoNull();
  var i;
	var n;
  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('checkbox') != -1) {
    	if(document.forms[0].elements[i].name.indexOf('_none')  != -1) {
      	document.forms[0].elements[i].checked = true;
    	}
		}
  }
}

function toggleROSChecksOff(sel, section)
{
	if(sel.value != "" && sel.value != null) {
		document.forms[0].elements['ros_' + section + '_hpi'].checked=false;
		document.forms[0].elements['ros_' + section + '_none'].checked=false;
	}
}

function setChecks(prefix, suffix, val)
{
	var s = suffix.length;
	var date_name = '';
  var i;
	var n;
  var l = document.forms[0].elements.length;
	var set_date = false
	if(arguments.length > 3) set_date = arguments[3];
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('check') != -1) {
      if(document.forms[0].elements[i].name.indexOf(prefix) == 0) {
				n = document.forms[0].elements[i].name.length;
				if(document.forms[0].elements[i].name.substring(n - s) == suffix) {
        	document.forms[0].elements[i].checked = val;
					if(set_date) {
						date_name= document.forms[0].elements[i].name + set_date;
						if(val == false) {
							document.getElementById(date_name).value = '';
						} else { 
							SetDatetoToday(date_name);
						}
					}
				}
      }
    }
  }
}

