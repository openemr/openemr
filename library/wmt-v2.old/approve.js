
function ApprovalTimeStamp(thisDate)
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

function ValidateApprovalPIN(source, check, disp, approve)
{
  var pin= document.getElementById(source).value;
  if(pin == '') {
    alert("No PIN Entered");
    return false;
  }
  var pin_check= document.getElementById(check).value;
  if(pin == pin_check) {
    alert("Form Approved");
    document.getElementById(disp).value = 'Approved - Now Click [Save Data] to Commit';
    document.getElementById(approve).value = 'a';
    return true;
  } else {
    alert("Incorrect PIN");
    document.getElementById(source).value = '';
    return false;
  }
}

function VerifyApprovalPIN(source, check, approve)
{
  var pin= document.getElementById(source).value;
  if(pin == '') {
    top.restoreSession();
    document.approval_form.submit();
    return true;
  }
  var pin_check= document.getElementById(check).value;
  if(pin == pin_check) {
    document.getElementById(approve).value = 'a';
    top.restoreSession();
    document.approval_form.submit();
    return true;
  } else {
    alert("Incorrect PIN");
    document.getElementById(source).value = '';
    document.getElementById(approve).value = '';
    return false;
  }
}

function UpdateApprovalSelect(allowed)
{
	// If the user can not approve just return
	if(!allowed) return true;
	// Get the value of the bill/unbilled select box
	var bill = document.getElementById('form_priority');
	var val = bill.options[bill.selectedIndex].value
	val = val.toLowerCase();
	// Check if Approve is a choice currently in the status select box
	var select = document.getElementById('form_complete');
	var len = select.options.length;
	var test = 0;
	var opt = "";
	for (var i=0; i < len; i++) {
		opt = select.options[i].value;
		opt = opt.toLowerCase();
		if(opt == 'a') test = i + 1;
	}
	// In this case we need to add it
	if(val == 'b' && !test) {
		select.options[len] = new Option('Approved', 'a');
	// Or in this case we remove it
	} else if(test) {
		select.options[(test - 1)] = null;
	}
}

function ExitForm()
{
  top.restoreSession();
}
