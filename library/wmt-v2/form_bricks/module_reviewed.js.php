<script type="text/javascript">

function SetReview(webroot,pid,chk,module,user,frmdir,enc,dt) {
	if(chk.checked) {
		var mode = 'add'
	} else {
		var mode = 'delete'
	}
	var name_div = chk.id + '_by';

	var output = 'error';
	$.ajax({
		type: "POST",
		url: webroot + "/library/wmt-v2/ajax/rvw_log.ajax.php",
		datatype: "html",
		data: {
			mode: mode,
			pid: pid,
			module: module,
			user: user,
			frmdir: frmdir,
			enc: enc,
			dt: dt
		},
		success: function(result) {
			if(result['error'] || result == '') {
				output = '';
				alert('Could NOT Update Review Status\n'+result['error']);
			} else {
				output = result;
				if(mode == 'delete') output = '';
				if(document.getElementById(name_div) != null) {
					document.getElementById(name_div).innerHTML = output;
				}
			}
		},
		async: true
	});
	return output;
}

</script>
