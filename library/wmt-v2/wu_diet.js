
function CheckEndTime(start, end, target)
{
	var startTime= document.getElementById(start).value;
	var endTime= document.getElementById(end).value;
	var totalTime= document.getElementById(target).value;
	// If the time was already set, just return
	if(totalTime != '') return true;
	if(endTime == '') {
		endTime= GetShortTimeStamp();
		document.getElementById(end).value= endTime;
	}
	var endMinute= parseInt(endTime.slice(-2));
	var endHour= parseInt(endTime.slice(0,2));
	var startMinute= parseInt(startTime.slice(-2));
	var startHour= parseInt(startTime.slice(0,2));
	// alert(startHour+'  '+startMinute+'  -  '+endHour+'  '+endMinute);
	if(isNaN(endHour) || isNaN(endMinute) || isNaN(startHour) || isNaN(startMinute)) return true;
	var endTotal= parseInt((endHour * 60) + endMinute);
	var startTotal= parseInt((startHour * 60) + startMinute);
	// alert(endTotal+'  ::  '+startTotal);
	var len= parseInt(endTotal - startTotal);
	if(isNaN(len)) return true;
	document.getElementById(target).value= len;
	return true;
}

