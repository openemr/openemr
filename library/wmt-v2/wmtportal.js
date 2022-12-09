function AcceptPortalData(source)
{
	var target = source.substr(4);
	// alert("Target: "+target);
	// var type = document.getElementById(target).nodeName.toUpperCase();
	var type = document.getElementById(target).type.toUpperCase();
	// alert("Type: "+type);
	if(type.substr(0,6) == 'SELECT') {
		var title = document.getElementById(source).innerHTML;
		var opt = document.getElementById(target).options;
		var num_entries = opt.length;
		// alert("Searching ("+num_entries+") options for ["+title+"]");
		for (var i=0; i < num_entries; i++) {
			if(opt[i].text == title) {
				document.getElementById(target).options[i].selected = true;
				return true;	
			}
		}
	} else if(type == 'CHECKBOX') {
		var that = "";
		if(arguments.length > 1) var that = arguments[1];
		if(document.getElementById(source).innerHTML.toUpperCase() == 'CHECKED') {
			document.getElementById(target).checked = true;
			if(that) document.getElementById(that).checked = false;
		} else {
			document.getElementById(target).checked = false;
		}
	} else if(type == 'RADIO') {
		var chc = document.getElementById(source).innerHTML;
		var opt = document.getElementsByName(target);
		var num_entries = opt.length;
		for (var i=0; i < num_entries; i++) {
			document.getElementById(opt[i].id).checked = false;
			if(opt[i].value == chc) {
				document.getElementById(opt[i].id).checked = true;
			}
		}
	} else if(type == 'TEXTAREA') {
		var existing = document.getElementById(target).value;
		if(existing != '') {
			existing = existing + "\n\n";
		}
		var additional =  document.getElementById(source).innerHTML;
		if(existing.indexOf(additional) == -1) {
			existing = existing + additional;
			document.getElementById(target).value = existing
		}
	} else {
		document.getElementById(target).value = 
														document.getElementById(source).innerHTML;
	}

}
