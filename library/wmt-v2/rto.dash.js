
function SubmitRTO(base,wrap,formID)
{
  document.forms[0].action=base+'?mode=rto&wrap='+wrap;
  if(formID != '' && formID != 0) {
 		document.forms[0].action=base+'?mode=rto&wrap='+wrap+'&id='+formID;
	}
	document.forms[0].submit();
}

function ShowAllRTO(base,wrap,formID)
{
  document.forms[0].action=base+'?mode=rtodisp&all=all';
	document.forms[0].submit();
}

function ShowPendingRTO(base,wrap,formID)
{
  document.forms[0].action=base+'?mode=rtodisp';
	document.forms[0].submit();
}

function UpdateRTO(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid RTO ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('rto_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid RTO entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'?mode=updaterto&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'?mode=updaterto&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function RemindRTO(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid RTO ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('rto_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid RTO entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'?mode=remindrto&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'?mode=remindrto&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function DeleteRTO(base,wrap,itemID,formID)
{
  if(itemID == '' || itemID == 0 || isNaN(itemID)) {
		alert("No Valid RTO ID Was Found...Aborting!!");
		return false;
	}
	var test=document.getElementById('rto_id_'+itemID);
	if(test == '' || test == null) {
		alert("ID <"+itemID+"> is not a valid RTO entry...Aborting");
		return false;
	}
 	document.forms[0].action=base+'?mode=delrto&wrap='+wrap+'&itemID='+itemID;
  if(formID != '' && formID != 0) {
 	document.forms[0].action=base+'?mode=delrto&wrap='+wrap+'&itemID='+itemID+'&id='+formID;
	}
	document.forms[0].submit();
}

function SetRTOStatus(thisStatus)
{
	if(document.forms[0].elements[thisStatus].selectedIndex == 0) {
		document.forms[0].elements[thisStatus].selectedIndex = 1;
	}
}

function FutureDate(thisDate, tNum, tFrame, target)
{
	var fromDate = document.getElementById(thisDate).value;
	var numSel = document.getElementById(tNum);
	var timeNum= parseInt(numSel.options[numSel.selectedIndex].value);
	var frameSel= document.getElementById(tFrame);
	var timeFrame= frameSel.options[frameSel.selectedIndex].value;
	// alert("Num: "+timeNum+"   Frame: "+timeFrame+"  Date: "+fromDate);
	// alert("Num: "+timeNum+"  is our number value" );
	if(fromDate == 0 || fromDate == '') return false;	
	if(timeNum == 0 || timeNum == '' || isNaN(timeNum)) return false;	
	if(timeFrame == 0 || timeFrame == '') return false;	
	fromDate = new Date(fromDate);
	if(fromDate == 'Invalid Date') {
		alert("Not a Valid Date, Use 'YYYY-MM-DD' to Calculate EDD");
		return false;
	}
	if(isNaN(timeNum)) {
		alert("A Valid Number and/or Interval Must Be Provided");
		return false;
	}
	if(timeFrame != 'd' && timeFrame != 'w' && timeFrame != 'm' && timeFrame != 'y') {
		// alert("A Valid Interval Must Be Provided");
		return false;
	}
	if(timeFrame == 'w') timeNum = parseInt(timeNum * 7);
	if(timeFrame == 'm') timeNum = parseInt(timeNum * 30);
	if(timeFrame == 'y') timeNum = parseInt(timeNum * 365);
	var seconds = fromDate.getTime();
	seconds = seconds + (86400000 * timeNum);
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
