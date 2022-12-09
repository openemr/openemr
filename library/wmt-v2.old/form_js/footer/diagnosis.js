

function SetPatEd(chk,link_type,link_id,code_fld,type_fld,method,language) {
	if(chk.checked) {
		var mode = 'add'
	} else {
		var mode = 'remove'
	}
	var code = '';
	var type = '';
	if(code_fld) {
		if(document.getElementById(code_fld) == null) return false;
		code = document.getElementById(code_fld).value;
	}
	if(type_fld) {
		if(document.getElementById(type_fld) == null) return false;
		type = document.getElementById(type_fld).value;
	}
	var code += ':' + type;
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/amc_ed_log.ajax.php",
		datatype: "html",
		data: {
			mode: mode,
			link_type: link_type,
			link_id: link_id,
			ed_code: code,
			method: method,
			language: language 
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem retrieving Lot # details\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: false
	});
	return output;
}
