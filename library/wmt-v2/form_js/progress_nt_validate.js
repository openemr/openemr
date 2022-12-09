	var f = document.forms[0];
	var flag = f.elements[pre+'prg_nt_progress_flag'].checked;
	var late = f.elements[pre+'prg_nt_late_flag'].checked;
	var start_hour = f.elements[pre+'prg_nt_start_hour'].value;
	var start_min = f.elements[pre+'prg_nt_start_min'].value;
	var end_hour = f.elements[pre+'prg_nt_end_hour'].value;
	var end_min = f.elements[pre+'prg_nt_end_min'].value;
	var counselor = f.elements[pre+'prg_nt_counselor'].value;
	var notes = f.elements[pre+'prg_nt_notes'].value;
	var conv = document.getElementById(pre+'prg_nt_converted');
	if(conv != null) conv = conv.value;

	if (flag || late) {
		if (!counselor || !notes) {
			msg = 'You must specify a counselor and a narrative\n '+
			'before saving a case note or late entry.';
			ok = false;
		}
	}
	else {
		if( notes  && !conv) {
			if ( !start_hour || !start_min || !end_hour || !end_min || !counselor ) {
				msg = 'You must provide complete time data for the progress note before saving.';
				ok = false;
			}
		}
	}
