function toggleChange(chk) {
	if(chk.checked == false) {
		document.getElementById('prefer1').style.display = 'none';
		document.getElementById('prefer2').style.display = 'none';
		document.getElementById('queued').value = '';
		document.getElementById('sent').value = '';
		document.getElementById('queued_by').value = '';
		document.getElementById('tmp_send_now').value = '';
	} else {
		document.getElementById('prefer1').style.display = 'block';
		document.getElementById('prefer2').style.display = 'block';
		if(document.getElementById('sent').value == '' ||
			document.getElementById('sent').value == '0000-00-00 00:00:00') {
				document.getElementById('tmp_send_now').value = '1';
		} else {
			var msg = 'A Referral Was Sent For This Encounter Already -\n';
			msg += '\nAre You Sure You Want To Re-Submit?';
			if(confirm(msg)) document.getElementById('tmp_send_now').value = '1';
		}
	}
}

function confirmHL7Send(chk, root, sent, pid, base, wrap, fid, user) {
	var msg = 'Generate the HL7 referral to Optum?';
	var processed = 0;
	if(sent) msg += '\n\nThis patient was already referred on ' + sent;
	if(chk.checked == false) {
		msg = 'Remove the Queued Referral (if possible)?\n\n';
		msg += 'This can be done if the message has not been sent\n';
		processed = 1;
	}
	if(!confirm(msg)) {
		return false;
	}
	if(!processed) {
		TimeStamp('queued');
		document.getElementById('queued_by').value = user;
		document.getElementById('queue_label').innerHTML = 'Referral Queued On: '+
				document.getElementById('queued').value;
	}
	
	var output = 'error';
	$.ajax({
		type: "POST",
		url: root+"/library/wmt-v2/ajax/hl7_queue.ajax.php",
		datatype: "html",
		data: {
			hl7_msg_group: 'ADT',
			hl7_msg_type: 'A28',
			oemr_table: 'patient_data',
			oemr_ref_id: pid,
			target: 'optum',
			log_table: 'form_optum_screen',
			log_field: 'sent',
			processed: processed
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem queueing the HL7 message - details:\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: false
	});
	if(processed) {
		document.getElementById('queued').value = '';
		document.getElementById('queued_by').value = 0;
		document.getElementById('queue_label').innerHTML = '';
	}
	// AutoSave(base, wrap, '', fid);
	return output;
}
