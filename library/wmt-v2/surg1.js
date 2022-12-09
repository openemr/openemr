
function SetStatusLabel(thisLabel, thisCheck)
{
	if(document.getElementById(thisCheck).checked == true) {
		document.getElementById(thisLabel).innerHTML = 'COMPLETE';
		document.getElementById(thisLabel).style.color = 'black';
	} else {
		document.getElementById(thisLabel).innerHTML = '* PENDING *';
		document.getElementById(thisLabel).style.color = 'red';
	}
}

function SetNotificationLabel(thisLabel, thisCheck)
{
	if(document.getElementById(thisCheck).checked == true) {
		document.getElementById(thisLabel).innerHTML = 'NOTIFIED';
		document.getElementById(thisLabel).style.color = 'black';
	} else {
		document.getElementById(thisLabel).innerHTML = '* NOTIFICATION PENDING *';
		document.getElementById(thisLabel).style.color = 'red';
	}
}

function SetStatusDivByCheck(thisDiv, withCheck, woCheck)
{
	var wc = document.getElementById(withCheck);
	if(wc == null) {
		wc = false;
	} else {
		if(document.getElementById(withCheck).checked == true) wc = true; 
	}
	var wo = document.getElementById(woCheck);
	if(wo == null) {
		wo = false;
	} else {
		if(document.getElementById(woCheck).checked == true) wo = true; 
	}
	if(wc == true || wo == true) {
		document.getElementById(thisDiv).style.display = 'block';
		// alert("But in the true section");
	} else {
		document.getElementById(thisDiv).style.display = 'none';
	}
}

function SetStatusDivBySelect(thisDiv, refSelect)
{
	if(document.getElementById(refSelect).value) { 
		document.getElementById(thisDiv).style.display = 'block';
		// alert("But in the true section");
	} else {
		document.getElementById(thisDiv).style.display = 'none';
	}
}

function ApptDateCheck(appt, surg)
{
	var apptDt = document.getElementById(appt).value;
	var surgDt = document.getElementById(surg).value;
	// alert("Appointment: ("+apptDt+")   Surgery: ["+surgDt+"]");
	if(!surgDt || !apptDt) return true;
	if(apptDt > surgDt) {
		alert("\t\t\t\t** Warning **\n"+
			"Scheduled appointment date "+apptDt+" is AFTER\n"+
			"     the scheduled surgery date of "+surgDt);
		return true;
	}
}

function Surg1ReferralPending(dataField, doneField, refType)
{
	// alert("Checking "+dataField+"   And   "+doneField);
	var ref = document.getElementById(dataField);
	// alert("The Data Field: "+ref);
	if(ref == null) return false;
	ref = document.getElementById(dataField).value;
	// alert("The Data Field Was Valid - Contents: "+ref);
	if(ref) {
		var rcv = document.getElementById(doneField);
		// alert("My Done Field is "+rcv);
		if(rcv != null) rcv = document.getElementById(doneField).checked;
		// alert("My Done Value is "+rcv);
		if(!rcv) {
			alert(refType+" Is Still Pending, Can Not Mark as Complete!");
			return(true)
		}
	}
	return false;
}

function CheckAllSurg1Referrals()
{
	var stat = document.getElementById('form_complete').value;
	if(stat != 'c' && stat != 'a') return true;
	if(Surg1ReferralPending('sc1_psy_ref_dr','sc1_psy_done','Neuropsychology Referral')) return false;
	if(Surg1ReferralPending('sc1_neu_ref_dr','sc1_neu_done','Neurology Referral')) return false;
	if(Surg1ReferralPending('sc1_pain_ref_dr','sc1_pain_done','Pain Clinic Referral')) return false;
	if(Surg1ReferralPending('sc1_rad_mri_lum','sc1_rad_mri_lum_done','MRI Lumbar')) return false;
	if(Surg1ReferralPending('sc1_rad_mri_thor','sc1_rad_mri_thor_done','MRI Thoracic')) return false;
	if(Surg1ReferralPending('sc1_rad_mri_cerv','sc1_rad_mri_cerv_done','MRI Cervical')) return false;
	if(Surg1ReferralPending('sc1_rad_mri_brain','sc1_rad_mri_brain_done','MRI Brain')) return false;
	if(Surg1ReferralPending('sc1_rad_ct_lum','sc1_rad_ct_lum_done','CT Lumbar')) return false;
	if(Surg1ReferralPending('sc1_rad_ct_thor','sc1_rad_ct_thor_done','CT Thoracic')) return false;
	if(Surg1ReferralPending('sc1_rad_ct_cerv','sc1_rad_ct_cerv_done','CT Cervical')) return false;
	if(Surg1ReferralPending('sc1_rad_ct_brain','sc1_rad_ct_brain_done','CT Brain')) return false;
	return true;
}

function AutoCheckMessage()
{
	if(document.forms[0].elements['tmp_notify_name'].selectedIndex == 0) {
		document.forms[0].elements['tmp_notify_other'].checked = false;
	}	else {
		document.forms[0].elements['tmp_notify_other'].checked = true;
	}
}
