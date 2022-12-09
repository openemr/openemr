function alertMsgStatus(sel, user) {
	var msg = sel.options[sel.selectedIndex].text;
	var tmp = document.getElementById('u_alert_'+sel.value);
	if(tmp != null) {
		var warn = document.forms[0].elements['u_alert_'+sel.value].value.toLowerCase();
		if(warn == 'no alert') return true;
		tmp = document.forms[0].elements['u_title_'+sel.value].value;
		if(tmp == '' || tmp == null) {
			tmp = "Has NOT Set A Messaging Status";
		} else {
			msg = msg + "\n" + tmp;
		}
		tmp = document.forms[0].elements['u_until_'+sel.value].value;
		if(tmp == '' || tmp == null) {
			// msg = msg + "No Return Time Set";
		} else {
			msg = msg + " Until " + tmp;
		}
		tmp = document.forms[0].elements['u_msg_'+sel.value].value;
		if(tmp != '' && tmp != null) msg = msg + "\n\n" + tmp;
	} else {
		return true;
	}
	alert(msg);
}

function loadMsgStatus(user, display) {
	var msg = display;
	var tmp = document.getElementById('u_alert_'+user);
	if(tmp != null) {
		var warn = document.forms[0].elements['u_alert_'+user].value.toLowerCase();
		if(warn == 'no alert') return true;
		tmp = document.forms[0].elements['u_title_'+user].value;
		if(tmp == '' || tmp == null) {
			tmp = "Has NOT Set A Messaging Status";
		} else {
			msg = msg + " Is " + tmp;
		}
		tmp = document.forms[0].elements['u_until_'+user].value;
		if(tmp == '' || tmp == null) {
			// msg = msg + "No Return Time Set";
		} else {
			msg = msg + " Until " + tmp;
		}
		tmp = document.forms[0].elements['u_msg_'+user].value;
		if(tmp != '' && tmp != null) msg = msg + "\n\n" + tmp;
	} else {
		return true;
	}
	alert(msg);
}

