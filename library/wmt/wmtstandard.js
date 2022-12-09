function ScrollHere(target)
{
	var scroll_target = document.getElementById(target);
	scroll_target.scrollIntoView();
	return true;
}

function togglePanel(divid,imgid1,imgid2,barid)
{
  var numargs=arguments.length;
  if(document.getElementById(divid).style.display == 'none') {
    document.getElementById(divid).style.display = 'block';
    document.getElementById(imgid1).src = '../../../library/wmt/fill-090.png';
    document.getElementById(imgid2).src = '../../../library/wmt/fill-090.png';
    document.getElementById(barid).style.borderBottom = 'solid 1px black';
    document.getElementById(barid).style.borderRadius = '5px 5px 0px 0px';
		if(numargs >= 7) {
    	document.getElementById(arguments[6]).src = '../../../library/wmt/fill-090.png';
		}
		if(numargs >= 8) {
    	document.getElementById(arguments[7]).src = '../../../library/wmt/fill-090.png';
		}
		if(numargs >= 9) {
    	document.getElementById(arguments[8]).style.borderTop= 'solid 1px black';
		}
  } else {
    document.getElementById(divid).style.display = 'none';
    document.getElementById(imgid1).src = '../../../library/wmt/fill-270.png';
    document.getElementById(imgid2).src = '../../../library/wmt/fill-270.png';
    document.getElementById(barid).style.borderRadius = '5px';
		if(numargs >= 7) {
    	document.getElementById(arguments[6]).src = '../../../library/wmt/fill-270.png';
		}
		if(numargs >= 8) {
    	document.getElementById(arguments[7]).src = '../../../library/wmt/fill-270.png';
		}
		if(numargs >= 9) {
    	document.getElementById(arguments[8]).style.borderTop= 'solid 1px black';
		}
    if(barid == 'GEAllergyCollapseBar') {
      document.getElementById(barid).style.borderBottom = 'solid 1px black';
    }
    if(barid == 'GEMedsCollapseBar') {
      document.getElementById(barid).style.borderBottom = 'solid 1px black';
    }
    if(barid == 'DBAllergyCollapseBar') {
      document.getElementById(barid).style.borderBottom = 'solid 1px black';
    }
    if(barid == 'DBMedsCollapseBar') {
      document.getElementById(barid).style.borderBottom = 'solid 1px black';
    }
  }
  // This sets the bottom border of the bar for special boxes
  if(numargs >= 5) {
    if(arguments[4] == 'line') {
      document.getElementById(barid).style.borderBottom = 'solid 1px black';
    }
  }
  if(numargs >= 6) {
		var mode_id=arguments[5];
		// alert("Mode Element: "+mode_id);
    document.getElementById(mode_id).value = 
														document.getElementById(divid).style.display;
		// alert("Element Value: "+document.getElementById(mode_id).value);
  }
}

function ToggleDivDisplay(thisDiv, thisCheck)
{
  if(document.getElementById(thisCheck).checked == true) {
    document.getElementById(thisDiv).style.display = 'block';
		if(numargs > 2) {
			document.getElementById(arguments[2]).value = 'block';
		}
  } else {
    document.getElementById(thisDiv).style.display = 'none';
		if(numargs > 2) {
			document.getElementById(arguments[2]).value = 'none';
		}
	}
}

function TogglePair(ChkBox, UnBox)
{
  if(document.getElementById(ChkBox).checked == true) {
    document.getElementById(UnBox).checked = false;
  }
}

function ToggleTrio(ChkBox, UnBox, UnBox2)
{
  if(document.getElementById(ChkBox).checked == true) {
    document.getElementById(UnBox).checked = false;
    document.getElementById(UnBox2).checked = false;
  }
}

function VerifyYesChecks(YesBox, NoBox)
{
  if(document.getElementById(YesBox).checked == true) {
    document.getElementById(NoBox).checked = false;
  }
}

function VerifyNoChecks(YesBox, NoBox)
{
  if(document.getElementById(NoBox).checked == true) {
    document.getElementById(YesBox).checked = false;
  }
}

function VerifyYesFirstCheck()
{
  var numargs=arguments.length;
  // The First Item is the Yes Box
  if(document.getElementById(arguments[0]).checked == false) return(1);
  for (var i = 1; i < numargs; i++) {
     document.getElementById(arguments[i]).checked = false;
  }
}

function UpdateBMI(height, weight, bmi, bmi_status)
{
  var tmp_bmi = '';
  var tmp_bmi_status = '';

  var ht = document.getElementById(height).value;
  ht = Math.round(ht * 100) / 100;
  ht = ht.toFixed(2);
  document.getElementById(height).value = ht;

  var wt = document.getElementById(weight).value;
  wt = Math.round(wt * 100) / 100;
  wt = wt.toFixed(2);
  document.getElementById(weight).value = wt;

  if((wt <= 0) || (ht <= 0)) {
    document.getElementById(bmi).value = '';
    document.getElementById(bmi_status).value = '';
    return false;
  }

  tmp_bmi = ((wt/ht/ht) * 703);
  tmp_bmi = Math.round(tmp_bmi * 10) / 10;
  tmp_bmi = tmp_bmi.toFixed(1);
  if(tmp_bmi > 42) {
    tmp_bmi_status = 'Obesity III';
  } else if (tmp_bmi > 34) {
    tmp_bmi_status = 'Obesity II';
  } else if (tmp_bmi > 30) {
    tmp_bmi_status = 'Obesity I';
  } else if (tmp_bmi > 27) {
    tmp_bmi_status = 'Overweight';
  } else if (tmp_bmi > 25) {
    tmp_bmi_status = 'Normal BL';
  } else if (tmp_bmi > 18.5) {
    tmp_bmi_status = 'Normal';
  } else if (tmp_bmi) {
    tmp_bmi_status = 'Underweight';
  }
	// The new values
  if(tmp_bmi > 40) {
    tmp_bmi_status = 'Obesity III';
  } else if (tmp_bmi > 35) {
    tmp_bmi_status = 'Obesity II';
  } else if (tmp_bmi > 30) {
    tmp_bmi_status = 'Obesity I';
  } else if (tmp_bmi > 25) {
    tmp_bmi_status = 'Overweight';
  } else if (tmp_bmi > 18.5) {
    tmp_bmi_status = 'Normal';
  } else if (tmp_bmi > 17) {
    tmp_bmi_status = 'Mild Thinness';
  } else if (tmp_bmi > 16) {
    tmp_bmi_status = 'Moderate Thinness';
  } else if (tmp_bmi) {
    tmp_bmi_status = 'Severe Thinness';
  }

  if( tmp_bmi ) {
    document.getElementById(bmi).value = tmp_bmi;
    document.getElementById(bmi_status).value = tmp_bmi_status;
  }
}

function NoDecimal(thisField)
{
  var tmp = document.getElementById(thisField).value;
  tmp = Math.round(tmp);
  if(tmp == '0') tmp='';
  if(tmp == 'NaN') tmp='';
  document.getElementById(thisField).value = tmp;
}

function OneDecimal(thisField)
{
  var tmp = document.getElementById(thisField).value;
  tmp = Math.round(tmp * 10) / 10;
  tmp = tmp.toFixed(1);
  if(tmp == '0.0') tmp='';
  if(tmp == 'NaN') tmp='';
  document.getElementById(thisField).value = tmp;
}

function TwoDecimal(thisField)
{
  var tmp = document.getElementById(thisField).value;
	if(isNaN(tmp)) return false;
  tmp = Math.round(tmp * 100) / 100;
  tmp = tmp.toFixed(2);
	if(arguments.length < 2) {
  	if(tmp == '0.00') tmp='';
	}
  if(tmp == 'NaN') tmp='';
  document.getElementById(thisField).value = tmp;
}

function CalcPatAge(thisDate, thisAge)
{
  var dob = document.getElementById(thisDate).value;
  var age = document.getElementById(thisAge).value;
	var force_change = false;
	if(arguments.length > 2) {
		force_change = arguments[2];
	}
  if(age > 0 && !force_change) {
    return true;
  }
  dob = new Date(dob);  
  if(dob == 'Invalid Date') {
    alert("Not a Valid Date - use 'YYYY-MM-DD'");
    return false;
  }
  var Cdate = new Date;
  var age = Math.floor((( Cdate - dob) /1000 /(60*60*24)) / 365.25 );
  document.getElementById(thisAge).value = age;
  return true;
}

function TimeStamp(thisDate)
{
  var currentTime=new Date();
  var myStamp= currentTime.getFullYear();
  var myMonth= "00" + (currentTime.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + currentTime.getDate();
  myDays= myDays.slice(-2);
  var myHours= "00" + currentTime.getHours();
  myHours= myHours.slice(-2);
  var myMinutes= "00" + currentTime.getMinutes();
  myMinutes= myMinutes.slice(-2);
  var mySeconds= "00" + currentTime.getSeconds();
  mySeconds= mySeconds.slice(-2);
  myStamp= myStamp + "-" + myMonth + "-" + myDays + " " + myHours + ":" +
           myMinutes + ":" + mySeconds;

  document.getElementById(thisDate).value = myStamp;
}

function SetDatetoToday(thisDate)
{
  var currentTime=new Date();
  var myStamp= currentTime.getFullYear();
  var myMonth= "00" + (currentTime.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + currentTime.getDate();
  myDays= myDays.slice(-2);
  myStamp= myStamp + "-" + myMonth + "-" + myDays;

  document.getElementById(thisDate).value = myStamp;
}

function ClearThisField(thisField)
{
  if(thisField) {
    document.getElementById(thisField).value= '';
  }
}

function ShortTimeStamp(thisDate)
{
  var numargs=arguments.length;
  var currentTime=new Date();
  var myHours= "00" + currentTime.getHours();
  myHours= myHours.slice(-2);
  var myMinutes= "00" + currentTime.getMinutes();
  myMinutes= myMinutes.slice(-2);
  var myStamp=  myHours + ":" + myMinutes;

  document.getElementById(thisDate).value = myStamp;
	if(numargs >= 2) {
		// alert("In the Change: "+arguments[1]);
   	document.getElementById(arguments[1]).value = myStamp;
	}
}

function GetShortTimeStamp()
{
  var currentTime=new Date();
  var myHours= "00" + currentTime.getHours();
  myHours= myHours.slice(-2);
  var myMinutes= "00" + currentTime.getMinutes();
  myMinutes= myMinutes.slice(-2);
  var myStamp=  myHours + ":" + myMinutes;
	return myStamp;
}

function CalcShortDiff(start, end, target)
{
	var startTime= document.getElementById(start).value;
	var endTime= document.getElementById(end).value;
	var endMinute= parseInt(endTime.slice(-2));
	var endHour= parseInt(endTime.slice(0,2));
	var startMinute= parseInt(startTime.slice(-2));
	var startHour= parseInt(startTime.slice(0,2));
	// alert(startHour+'  '+startMinute+'  -  '+endHour+'  '+endMinute);
	if(isNaN(endHour) || isNaN(endMinute) || isNaN(startHour) || isNaN(startMinute)) return false;
	var endTotal= parseInt((endHour * 60) + endMinute);
	var startTotal= parseInt((startHour * 60) + startMinute);
	// alert(endTotal+'  ::  '+startTotal);
	var len= parseInt(endTotal - startTotal);
	if(isNaN(len)) return false;
	document.getElementById(target).value= len;
}

//
// These are the functions that support all the inner-block 'Windows'
//


// To re-display the form at the same position in forms that support it
function SetScrollTop()
{
	var notification = document.getElementById('save-notification');
	if(notification != null) {
		notification.style.display = '';
	}
	var scroll = document.getElementById('tmp_scroll_top');
	// alert("scroll element: "+scroll);
	if(scroll != null) {
		// alert("Setting the scroll value: "+document.documentElement.scrollTop);
		scroll.value = document.documentElement.scrollTop;
		// alert("Library Value: "+scroll.value);
	}
}

// Common function to validate the item before we update / delete / unlink it
function ValidateItem(item, prefix, msg)
{
	var skip_id_test = false;
	var suppress_alert = false;
	if(msg != '') msg += ' ';
	if(arguments.length > 3) skip_id_test = arguments[3];
	if(arguments.length > 4) suppress_alert = arguments[4];
  if(item == '' || item == 0 || isNaN(item)) {
		if(!suppress_alert) alert("No valid "+msg+" was received...Aborting");
		return false;
	}
	if(skip_id_test) return true;
	// alert("Testing ["+prefix+"] ("+item+")");
	var test = document.getElementById(prefix+item);
	// alert("Test Reulst ["+test+"]");
	if(test == '' || test == null) {
		if(!suppress_alert) alert("Item ID <"+item+"> does NOT exist...Aborting");
		return false;
	}
	return true;
}

function SubmitLinkBuilder(base,wrap)
{
	SetScrollTop();
	var numargs = arguments.length;
	var itemID = '';
	var formID = '';
	var mode = '';
	var prefix = '';
  var desc = '';
  var num_links = 0;
	if(numargs > 2) itemID = arguments[2];
	if(numargs > 3) formID = arguments[3];
	if(numargs > 4) mode = arguments[4];
	if(numargs > 5) prefix = arguments[5];
	if(numargs > 6) desc = arguments[6];
	if(numargs > 7) num_links = arguments[7];
	if(itemID && itemID != null && itemID != 0 && prefix != '') {
		if(!ValidateItem(itemID, prefix, desc)) return false;
	}
	base += '?continue=true&mode=' + mode + '&wrap=' + wrap;
	if(itemID && itemID != null) base += '&itemID=' + itemID;
	if(formID && formID != null) base += '&id=' + formID;

	if((mode.substr(0,3) == 'del') && num_links > 0) {
		warn = " ** WARNING ** This Entry Is Attached to ["+num_links+
			"] Encounter(s)\n\nAre You Sure You Want To Delete This "+desc+
			"Entry?\n\n                This Action Can NOT Be Reversed!";
		if(!confirm(warn)) return false;
	}

	document.forms[0].action = base;
	document.forms[0].submit();
	return true;
}

function SubmitSurgery(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=surg&wrap='+wrap;
	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode=surg&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateSurgery(base,wrap,itemID,formID) {
	SetScrollTop();
	if(!ValidateItem(itemID, 'ps_id_', 'Surgery')) return false;
 	document.forms[0].action=base+'?mode=updatesurg&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatesurg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkSurgery(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'ps_id_', 'Surgery')) return false;
 	document.forms[0].action=base+'?mode=unlinksurg&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinksurg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteSurgery(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'ps_id_', 'Surgery')) return false;
	var warn=" Delete This Past Surgery Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Past "+
				"Surgery?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delsurg&wrap='+wrap+'&itemID='+itemID;
		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delsurg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function SubmitHospitalization(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=hosp&wrap='+wrap;
	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode=hosp&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateHospitalization(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'hosp_id_', 'Admission')) return false;
 	document.forms[0].action=base+'?mode=updatehosp&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatehosp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteHospitalization(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'hosp_id_', 'Admission')) return false;
	var warn="Delete This Prior Admission?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Prior "+
				"Admission?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delhosp&wrap='+wrap+'&itemID='+itemID;
		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delhosp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function UnlinkHospitalization(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'hosp_id_', 'Admission')) return false;
 	document.forms[0].action=base+'?mode=unlinkhosp&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkhosp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitMedicalHistory(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=pmh&wrap='+wrap;
  if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=pmh&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateMedicalHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pmh_id_', 'Medical History')) return false;
 	document.forms[0].action=base+'?mode=updatepmh&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=updatepmh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteMedicalHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pmh_id_', 'Medical History')) return false;
	var warn="Delete This Medical History Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Medical "+
				"History Entry?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delpmh&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delpmh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function UnlinkMedicalHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pmh_id_', 'Medical History')) return false;
 	document.forms[0].action=base+'?mode=unlinkpmh&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkpmh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitSupplier(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=sp&wrap='+wrap;
  if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=sp&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateSupplier(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'sp_id_', 'Providers / Suppliers')) return false;
 	document.forms[0].action=base+'?mode=updatesp&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=updatesp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteSupplier(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'sp_id_', 'Providers / Suppliers')) return false;
	var warn="Delete This Provider / Supplier Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Provider / "+
				"Supplier Entry?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delsp&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delsp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function UnlinkSupplier(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'sp_id_', 'Provider / Supplier')) return false;
 	document.forms[0].action=base+'?mode=unlinksp&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinksp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitImageHistory(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=img&wrap='+wrap;
  if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=img&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateImageHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'img_id_', 'Image History')) return false;
 	document.forms[0].action=base+'?mode=updateimg&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=updateimg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkImageHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'img_id_', 'Image History')) return false;
 	document.forms[0].action=base+'?mode=unlinkimg&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkimg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteImageHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'img_id_', 'Image History')) return false;
	var warn="Delete This Image History Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Image "+
				"History Entry?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delimg&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delimg&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
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

function SubmitFamilyHistory(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=fh&wrap='+wrap;
  if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode=fh&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateFamilyHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'fh_id_', 'Family History')) return false;
	document.forms[0].action=base+'?mode=updatefh&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
		document.forms[0].action=base+'?mode=updatefh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeleteFamilyHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'fh_id_', 'Family History')) return false;
	var warn="Delete This Family History Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Family History "+
				"Entry?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {

  	document.forms[0].action=base+'?mode=delfh&wrap='+wrap+'&itemID='+itemID;
  	if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delfh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function UnlinkFamilyHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'fh_id_', 'Family History')) return false;
 	document.forms[0].action=base+'?mode=unlinkfh&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkfh&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function ToggleWindowDisplayMode(base,wrap,formID,mode,wmode)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode='+mode+'&disp='+wmode+'&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode='+mode+'&disp='+wmode+'&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdatePrescription(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_id_', 'Prescription')) return false;
 	document.forms[0].action=base+'?mode=updatemed&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatemed&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function LinkPrescription(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_id_', 'Prescription')) return false;
 	document.forms[0].action=base+'?mode=linkmed&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=linkmed&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkPrescription(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_id_', 'Prescription')) return false;
 	document.forms[0].action=base+'?mode=unlinkmed&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkmed&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkAllPrescriptions(base,wrap,maxItems,formID)
{
	SetScrollTop();
 	document.forms[0].action=base+'?mode=unlinkallmeds&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=unlinkallmeds&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdatePrescriptionHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_hist_id_', 'Medication History')) return false;
 	document.forms[0].action=base+'?mode=updatemedhist&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatemedhist&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function LinkPrescriptionHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_hist_id_', 'Prescription')) return false;
 	document.forms[0].action=base+'?mode=linkmedhist&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=linkmedhist&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkPrescriptionHistory(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_hist_id_', 'Medication History')) return false;
 	document.forms[0].action=base+'?mode=unlinkmedhist&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkmedhist&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkAllPrescriptionHistory(base,wrap,maxItems,formID)
{
	SetScrollTop();
 	document.forms[0].action=base+'?mode=unlinkallmedhist&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=unlinkallmedhist&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateMedication(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_id_', 'Medication')) return false;
 	document.forms[0].action=base+'?mode=updatemed&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatemed&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkMedication(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'med_id_', 'Medication')) return false;
 	document.forms[0].action=base+'?mode=unlinkmed&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkmed&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkAllMedications(base,wrap,maxItems,formID)
{
	SetScrollTop();
 	document.forms[0].action=base+'?mode=unlinkallmeds&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=unlinkallmeds&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitAllergy(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=all&wrap='+wrap;
	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode=all&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdateAllergy(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'all_id_', 'Allergy')) return false;
 	document.forms[0].action=base+'?mode=updateall&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updateall&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkAllergy(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'all_id_', 'Allergy')) return false;
 	document.forms[0].action=base+'?mode=unlinkall&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkall&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkAllAllergies(base,wrap,maxItems,formID)
{
	SetScrollTop();
 	document.forms[0].action=base+'?mode=unlinkallall&wrap='+wrap;
 	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=unlinkallall&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitFavorite(base,wrap,itemID,formID,code_field,plan_field)
{
	SetScrollTop();
	var plan = document.forms[0].elements[plan_field].value;
	if(!plan || plan == '') {
		alert("Why would you want to save an empty plan?");
		return false;
	}
	var pcode = document.forms[0].elements[code_field].value;
	if(!pcode || pcode == '') {
		alert("There is no code to attach this plan to - NOT Saving");
		return false;
	}
	alert("Plan Saved as a Favorite");
  document.forms[0].action=base+'?mode=fav&wrap='+wrap;
	if(itemID != '' && itemID != 0) {
  	document.forms[0].action=base+'?mode=fav&wrap='+wrap+'&itemID='+itemID;
	}
  if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=document.forms[0].action+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function SubmitPastPregnancy(base,wrap,formID)
{
	SetScrollTop();
  document.forms[0].action=base+'?mode=pp&wrap='+wrap;
	if(formID != '' && formID != 0 && formID != null) {
  	document.forms[0].action=base+'?mode=pp&wrap='+wrap+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UpdatePastPregnancy(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pp_id_', 'Past Pregnancy')) return false;
 	document.forms[0].action=base+'?mode=updatepp&wrap='+wrap+'&itemID='+itemID;
	if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updatepp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkPastPregnancy(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pp_id_', 'Past Pregnancy')) return false;
 	document.forms[0].action=base+'?mode=unlinkpp&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkpp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function DeletePastPregnancy(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'pp_id_', 'Past Pregnancy')) return false;
	var warn=" Delete This Obstetrical History Entry?\n\nThis Action CAN NOT Be Reversed!";
	var numargs= arguments.length;
	if(numargs > 4) {
		var num_links = arguments[4];
		if(num_links > 0) {
			warn="  ** WARNING ** This Entry Is Attached to ["+num_links+
				"] Encounter(s)\n\nAre You Sure You Want To Delete This Obstetrical "+
				"History?\n\n                This Action Can NOT Be Reversed!";
		}
	}
	if(confirm(warn)) {
  	document.forms[0].action=base+'?mode=delpp&wrap='+wrap+'&itemID='+itemID;
		if(formID != '' && formID != 0 && formID != null) {
  		document.forms[0].action=base+'?mode=delpp&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
		}
  document.forms[0].action=document.forms[0].action+'&continue=true';
		document.forms[0].submit();
	}
	return false;
}

function UpdateImmunization(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'imm_id_', 'Immunization')) return false;
 	document.forms[0].action=base+'?mode=updateimm&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 		document.forms[0].action=base+'?mode=updateimm&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function UnlinkImmunization(base,wrap,itemID,formID)
{
	SetScrollTop();
	if(!ValidateItem(itemID, 'imm_id_', 'Immunization')) return false;
 	document.forms[0].action=base+'?mode=unlinkimm&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0 && formID != null) {
 	document.forms[0].action=base+'?mode=unlinkimm&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
  document.forms[0].action=document.forms[0].action+'&continue=true';
	document.forms[0].submit();
}

function AdjustFocus(here)
{
	document.forms[0].elements[here].focus();
}

function ExitCheckPopup(here)
{
	var url=document.forms[0].elements[here].value;
	alert("Url: "+url);
	if(confirm("Exit Without Saving...Are You Sure?")) {
		top.restoreSession();
		window.location=url;
		return true;
	} else {
		return false;
	}
}

function VerifyApproveForm(here)
{
	var flag= true
  if(arguments.length > 1) {
		flag=arguments[1];
	}
	if(flag == 0) return(true);
	var mode=document.forms[0].elements[here].value;
	if(mode == 'a') {
		if(!confirm("Approving the form will render it completely un-editable and is not reversible.\n\nAre you sure you are ready to do this?")) {
			document.forms[0].elements[here].value='c';
			document.forms[0].elements[here].selectedIndex=2;
		}
	}
	return true;
}

function setEmptyDate(testDate)
{
	if(document.getElementById(testDate).value == '') {
		SetDatetoToday(testDate);
	}
}

function setEmptyTo(thisField, thisVal)
{
	if(document.getElementById(thisField).value == '') {
		document.getElementById(thisField).value = thisVal;
	}
}

function setEmpty(obj, thisVal)
{
	if(obj.value == '') obj.value = thisVal;
}

function SyncContent(thisField, matchField)
{
	var my_val = document.getElementById(thisField).value;
	var cur_val = document.getElementById(matchField).value;
	document.getElementById(matchField).value = my_val;
}

function addListItem(itemField,itemType)
{
 wmtOpen('../../../custom/add_list_entry_popup.php?thisItem='+itemField+'&thisList='+itemType, '_blank', 500, 300);
}

function refreshVisitSummary()
{
	if(null == opener) {
		ploc= top.location;
		return false;
	}
	var ploc=opener.location;
  var res=String(ploc).match(/patient_file\/encounter\/forms.php/);
	if(null != res) {
		opener.location.reload();
	}
}

// Saving and print the form 
function submit_print_form(base, wrap, field, formID)
{
	var mode = 'print';
	if(arguments.length > 4) mode = arguments[4];
	SetScrollTop();
	var myAction = base+'?mode=save&wrap='+wrap+'&continue='+mode;
	if(field != '') myAction += '&focusfield='+field;
	if(formID != '' && formID != 0 && formID != null) myAction += '&id='+formID;
	document.forms[0].action = myAction;
	document.forms[0].submit();
}

function nullOrNumber(thisField)
{
	if(document.forms[0].element[thisField].value = '') return true;
	if(isNaN(document.forms[0].element[thisField].value = '')) {
		document.forms[0].element[thisField].value = '';
	}
	return true;
}

function SetFutureDate(thisDate, tNum, tFrame, target)
{
	var fromDate = document.getElementById(thisDate).value;
	if(fromDate == 0 || fromDate == '') return false;	
	if(tNum == 0 || tNum == '' || isNaN(tNum)) {
		alert("Function 'FutureDate' Called with Invalid Numeric Time Frame");
		return false;	
	}
	fromDate = new Date(fromDate);
	if(fromDate == 'Invalid Date') {
		alert("Not a Valid Date, Use 'YYYY-MM-DD' for Auto Calculations");
		return false;
	}
	if(tFrame != 'd' && tFrame != 'w' && tFrame != 'm' && tFrame != 'y') {
		alert("Function 'FutureDate' Called with Valid Time Interval");
		return false;
	}
	if(tFrame == 'w') tNum = parseInt(tNum * 7);
	if(tFrame == 'm') tNum = parseInt(tNum * 30);
	if(tFrame == 'y') tNum = parseInt(tNum * 365);
	var seconds = fromDate.getTime();
	seconds = seconds + (86400000 * tNum);
	var orderDate= new Date();
	orderDate.setTime(seconds);
  var myYear= orderDate.getFullYear();
  var myMonth= "00" + (orderDate.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + orderDate.getDate();
  myDays= myDays.slice(-2);
	myYear= myYear + "-" + myMonth + "-" + myDays;
	document.getElementById(target).value= myYear;
}

