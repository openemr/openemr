function ajaxGracefulStop(msg) {
	var div = document.getElementById('save-notification');
	if(div != null) {
		div.style.display = 'none';
		div.innerHTML = 'Processing...';
	}
	if(msg) alert(msg);
}

function ajaxIssueLink(itemCnt,itemPrefix,encID,patientID,action,listType)
{
	var itemDesc = 'issue';
	if(arguments.length > 6) itemDesc = arguments[6];
	var base = '../../..';
	if(arguments.length > 7) base = arguments[7];
	if(!ValidateItem(itemCnt, itemPrefix+'id_', itemDesc)) return false;
 	if(!ValidateItem(encID, '', 'Encounter ID', true)) return false;
 	if(!ValidateItem(patientID, '', 'Patient ID', true)) return false;
	var itemID = document.getElementById(itemPrefix+'id_'+itemCnt).value;
	var output = 'error';
	$.ajax({
		type: "POST",
		url: base + '/library/wmt-v2/lists.ajax.php',
		datatype: "html",
		data: {
			action: action,
			type: listType,
			pid: patientID,
			id: itemID,
			enc: encID
		},
		success: function(result) {
			if(result['error']) {
				alert('Could NOT '+action+' that '+itemDesc+', details:\n'+result['error']);
			} else {
				output = result;
			}
		},
		async: false
	});

	if(output == 'error')  return false;

	if(action == 'link' || action == 'unlink') {
		var linkBtn = '';
		if(ValidateItem(itemCnt,itemPrefix+'link_btn_','',false,true)) 
				linkBtn = document.getElementById(itemPrefix+'link_btn_'+itemCnt); 
		var spanTxt = '';
		if(ValidateItem(itemCnt,itemPrefix+'link_span_','',false,true)) 
				spanTxt = document.getElementById(itemPrefix+'link_span_'+itemCnt);
		if(spanTxt) {
			while(spanTxt.firstChild) {
				spanTxt.removeChild(spanTxt.firstChild);
			}
			if(action == 'link') spanTxt.appendChild(document.createTextNode("Un-Link"));
			if(action == 'unlink') spanTxt.appendChild(document.createTextNode("Link"));
		}
		if(linkBtn) {
			if(action == 'link') linkBtn.onclick = function() { ajaxIssueLink(itemCnt,itemPrefix,encID,patientID,"unlink",listType)};
			if(action == 'unlink') linkBtn.onclick = function() { ajaxIssueLink(itemCnt,itemPrefix,encID,patientID,"link",listType)};
		}
	}
}

function ajaxIssueLinkAll(itemPrefix,encID,patientID,action,listType,maxItem)
{
	var itemDesc = 'issue';
	if(arguments.length > 6) itemDesc = arguments[6];
	var base = '../../..';
	if(arguments.length > 7) base = arguments[7];
	// LOOP THROUGH ALL THE ITEMS OF THIS TYPE CURRENTLY ON THIS FORM
	var max = document.getElementById(maxItem).value;
	var i;
	for(i=1; i<=max; i++) {
		var item = document.getElementById(itemPrefix+'id_'+i);
		if(item == null) continue;
		ajaxIssueLink(i,itemPrefix,encID,patientID,action,listType);
	}
}

function ajaxIssueAction(encID,patientID,frm,frmId,listType,itemId,listAction)
{
	var div = document.getElementById('save-notification');
	if(div != null) div.style.display = 'block';

	var itemDesc = 'Issues';
	if(arguments.length > 7) itemDesc = arguments[7];
	var listDiv = '';
	if(arguments.length > 8) listDiv = arguments[8];
	var prefix = '';
	if(arguments.length > 9) prefix = arguments[9];
	var wrap = 'update';
	if(arguments.length > 10) wrap = arguments[10];
	var wmode = 'encounter';
	if(arguments.length > 11) wmode = arguments[11];
	var base = '../../..';
	var actionDesc = 'Update';
 	if(!ValidateItem(patientID, '', 'Patient ID', true)) return false;
	var output = 'error';
	var postData = {};
	var msg = '';
	// FOR PROCEDURES MODULE, MUST PASS 'TYPE:CODE:MODIFIER' TO THE AJAX
	if(listType.toLowerCase() == 'procedures') {
		var level = document.getElementById('tmp_price_level');
		var dr = document.getElementById('tmp_visit_dr');
		if(level != null) level = level.value;
		if(dr != null) dr = dr.value;
		if(itemId) {
			var item = document.getElementById('proc_bill_id_'+itemId);
			if(item == null) {
				ajaxGracefulStop('No Iteration for '+itemId+' could be found');
				return false;
			}
			item = document.getElementById('proc_bill_id_'+itemId).value;
			var ctype = document.getElementById('proc_type_'+itemId);
			var code = document.getElementById('proc_code_'+itemId);
			var cmod = document.getElementById('proc_modifier_'+itemId);
			var cunits = document.getElementById('proc_units_'+itemId);
			var cmod = document.getElementById('proc_modifier_'+itemId);
			var cdesc = document.getElementById('proc_title_'+itemId);
			var cplan = document.getElementById('proc_plan_'+itemId);
			var on_fee= document.getElementById('proc_on_fee_'+itemId);
		} else {
			var ctype = document.getElementById('proc_type');
			var code = document.getElementById('proc_code');
			var cmod = document.getElementById('proc_modifier');
			var cunits = document.getElementById('proc_units');
			var cmod = document.getElementById('proc_modifier');
			var cdesc = document.getElementById('proc_title');
			var cplan = document.getElementById('proc_plan');
			var on_fee= document.getElementById('proc_on_fee');
		}
		if(ctype != null) ctype = ctype.value;
		if(code != null) code = code.value;
		if(cmod != null) cmod = cmod.value;
		if(cunits != null) cunits = cunits.value;
		if(cmod != null) cmod = cmod.value;
		if(cdesc != null) cdesc = cdesc.value;
		if(cplan != null) cplan = cplan.value;
		if(on_fee != null) on_fee = on_fee.value;
		if((!ctype || ctype == null) || (!code || code == null)) {
			msg = 'Incomplete Information...Unable to Process Request\nCalled with Type ('+type+') and Code ['+code+']\n\tPlease Contact Support';
			ajaxGracefulSopt(msg);
			return false;
		}

		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: listAction,
			code: code, ctype: ctype, cmod: cmod, cunits: cunits, on_fee: on_fee, 
			cdesc: cdesc, cplan: cplan, type: 'procedures', suppress: 'yes',
			item: item, level: level, dr: dr
		};
	}

	$.ajax({
		type: "POST",
		url: base + '/library/wmt-v2/lists.ajax.php',
		datatype: "html",
		data: postData,
		success: function(result) {
			if(result['error']) {
				msg = 'Could NOT update ['+code+'], details:\n'+result['error'];
			} else {
				output = result;
			}
		},
		async: false
	});
	
	ajaxGracefulStop(msg);
	if(output == 'error') return false;
	if(listDiv == '') return output;
	if(document.getElementById(listDiv) != null) {
		document.getElementById(listDiv).innerHTML = output;
	}
}

function ajaxIssueRefresh(encID,patientID,frm,frmId,listType,listAction)
{
	var div = document.getElementById('save-notification');
	if(div != null) div.style.display = 'block';

	var itemDesc = 'Issues';
	if(arguments.length > 6) itemDesc = arguments[6];
	var listDiv = '';
	if(arguments.length > 7) listDiv = arguments[7];
	var prefix = '';
	if(arguments.length > 8) prefix = arguments[8];
	var wrap = 'update';
	if(arguments.length > 9) wrap = arguments[9];
	var wmode = 'encounter';
	if(arguments.length > 10) wmode = arguments[10];
	var base = '../../..';
	var actionDesc = 'Load';
 	if(!ValidateItem(patientID, '', 'Patient ID', true)) return false;
	var output = 'error';
	var msg = '';

	// FOR DIAGNOSIS/PLAN SECTION ALL CHANGES MUST BE SAVED BEFORE WE DO ANYTHING
	if(listType.toLowerCase() == 'medical_problem') {
		if(!saveAllDiags(encID,patientID,frm,frmId)) {
			msg = 'Unable to Update Existing Plans, Please Contact Support';
			ajaxGracefulStop(msg);
			return false;
		}
	}
	// FOR PROCEDURES/PLAN SECTION ALL CHANGES MUST BE SAVED BEFORE WE DO ANYTHING
	if(listType.toLowerCase() == 'procedures') {
		if(!saveAllProcedures(encID,patientID,frm,frmId)) {
			msg = 'Unable to Update Existing Plans, Please Contact Support';
			ajaxGracefulStop(msg);
			return false;
		}
	}
	// FOR DATED DOCUMENTS MUST SAVE ALL NOTES IN CASE OF CHANGES
	if(listType.toLowerCase() == 'dated_document') {
		if(!saveAllDatedDocuments(patientID,frm,frmId)) {
			msg = 'Unable to Update Existing Plans, Please Contact Support';
			ajaxGracefulStop(msg);
			return false;
		}
	}

	// HERE WE CREATE THE POST ARRAYS FOR VARIOUS ACTIONS
	var postData = {
		enc: encID, pid: patientID, frmdir: frm, id: frmId, type: listType,
		action: listAction, title: itemDesc, div: listDiv, prefix: prefix,
		wrap: wrap, wmode: wmode
	};

	$.ajax({
		type: "POST",
		url: base + '/library/wmt-v2/lists.ajax.php',
		datatype: "html",
		data: postData,
		success: function(result) {
			if(result['error']) {
				msg = 'Could NOT '+actionDesc+' '+itemDesc+', details:\n'+result['error'];
			} else {
				output = result;
			}
		},
		async: false
	});

	ajaxGracefulStop(msg);
	if(output == 'error') return false;

	if(listDiv == '') return output;
	if(document.getElementById(listDiv) != null) {
		document.getElementById(listDiv).innerHTML = output;
	}
	if(listType == 'procedures') {
		justifyAll();
	}
}

function ajaxSubmitPlan(type_field, code_field, plan_field)
{
	var div = document.getElementById('save-notification');
	if(div != null)  div.style.display = 'block';
	var code = document.getElementById(code_field).value;
	var type = document.getElementById(type_field).value;
	var plan = document.getElementById(plan_field).value;
	var base = '../../..';
	var msg = '';
	if(!code) msg = 'A Code Must Be Provided';
	if(!type) {
		if(msg) msg += ', ';
		msg += 'A Code Type Must Be Provided';
	}
	if(!plan) {
		if(msg) msg += ', And ';
		msg += 'Why Save An Empty Plan?';
	}
	if(msg) {
		ajaxGracefulStop(msg);
		return false;
	}
	var output = 'error';

	$.ajax({
		type: "POST",
		url: base + '/library/wmt-v2/plans.ajax.php',
		datatype: "html",
		data: {
			type: type,
			code: code,
			plan: plan
		},
		success: function(result) {
			if(result['error']) {
				msg = 'Could NOT save plan, details:\n'+result['error'];
			} else {
				output = result;
			}
		},
		async: false
	});

	ajaxGracefulStop(msg);
	if(output == 'error')  return false;
}

function saveAllDiags(encID,patientID,frm,frmId)
{
	var div = document.getElementById('save-notification');
	if(div != null) {
		div.innerHTML = 'Updating Existing Plans....';
		div.style.display = 'block';
	}
	var base = '../../..';
	var output = 'error';
	var msg = '';
	
	// LOOP THROUGH ALL THE DIAGS CURRENTLY ON THIS FORM
	var max = document.getElementById('tmp_diag_cnt').value;
	if(!max || max == 0) output = 'success';
	var i;
	for(i=1; i<=max; i++) {
		var item = document.getElementById('dg_id_'+i);
		if(item == null) continue;
		item = document.getElementById('dg_id_'+i).value;
		// alert("Saving iteration ["+i+"]   Item ID: ("+item+")");
		var seq = document.getElementById('dg_seq_'+i);
		if(seq != null) seq = document.getElementById('dg_seq_'+i).value;
		var code = document.getElementById('dg_code_'+i).value;
		var ctype = document.getElementById('dg_type_'+i).value;
		var sdt = document.getElementById('dg_begdt_'+i).value;
		var edt = document.getElementById('dg_enddt_'+i).value;
		var cdesc = document.getElementById('dg_title_'+i).value;
		var cplan = document.getElementById('dg_plan_'+i).value;
		var remain = document.getElementById('dg_remain_'+i).value;
		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: 'update',
			cseq: seq, code: code, ctype: ctype, start_dt: sdt, end_dt: edt, 
			cdesc: cdesc, cplan: cplan, type: 'medical_problem', suppress: 'yes',
			item: item
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT update ['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
		if(output == 'error') break;
	}
	if(output == 'error') {
		ajaxGracefulStop(msg);
		return false;
	}

	// NOW IF THE NEW DIAG HAS INFORMATION, SAVE IT
	var code = document.getElementById('dg_code').value;
	if(code) {
		var seq = document.getElementById('dg_seq');
		if(seq != null) seq = document.getElementById('dg_seq').value;
		var ctype = document.getElementById('dg_type').value;
		var sdt = document.getElementById('dg_begdt').value;
		var edt = document.getElementById('dg_enddt').value;
		var cdesc = document.getElementById('dg_title').value;
		var cplan = document.getElementById('dg_code_plan').value;
		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: 'add',
			cseq: seq, code: code, ctype: ctype, start_dt: sdt, end_dt: edt, 
			cdesc: cdesc, cplan: cplan, type: 'medical_problem', suppress: 'yes'
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT add['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
	} else output = 'success';

	ajaxGracefulStop(msg);
	if(output == 'error')  return false;
	return true;
}

function saveAllProcedures(encID,patientID,frm,frmId)
{
	var div = document.getElementById('save-notification');
	if(div != null) {
		div.innerHTML = 'Updating Existing Plans....';
		div.style.display = 'block';
	}
	var base = '../../..';
	var output = 'error';
	var msg = '';
	
	// LOOP THROUGH ALL THE PROCEDURES CURRENTLY ON THIS FORM
	var max = document.getElementById('tmp_proc_cnt').value;
	if(!max || max == 0) output = 'success';
	var level = document.getElementById('tmp_price_level').value;
	var dr = document.getElementById('tmp_visit_dr').value;
	var i;
	for(i=1; i<=max; i++) {
		var item = document.getElementById('proc_bill_id_'+i);
		if(item == null) continue;
		item = document.getElementById('proc_bill_id_'+i).value;
		var code = document.getElementById('proc_code_'+i).value;
		var ctype = document.getElementById('proc_type_'+i).value;
		var cunits = document.getElementById('proc_units_'+i).value;
		var cmod = document.getElementById('proc_modifier_'+i).value;
		var cdesc = document.getElementById('proc_title_'+i).value;
		var cplan = document.getElementById('proc_plan_'+i).value;
		var on_fee= document.getElementById('proc_on_fee_'+i).value;
		var jst = document.getElementById('proc_justify_'+i);
		if(jst == null) {
			jst = -1;
		} else {
		 jst = document.getElementById('proc_justify_'+i).value;
		}
		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: 'update',
			code: code, ctype: ctype, cmod: cmod, cunits: cunits, on_fee: on_fee, 
			cdesc: cdesc, cplan: cplan, type: 'procedures', suppress: 'yes',
			item: item, level: level, dr: dr, justify: jst
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT update ['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
		if(output == 'error') break;
	}
	
	if(output == 'error') {
		ajaxGracefulStop(msg);
		return false;
	}

	// NOW IF THE NEW PROCEDURE HAS INFORMATION, SAVE IT
	var code = document.getElementById('proc_code');
	if(code != null) code = document.getElementById('proc_code').value;
	if(code) {
		var ctype = document.getElementById('proc_type').value;
		var cunits = document.getElementById('proc_units').value;
		if(cunits = '') cunits = 1;
		var cmod = document.getElementById('proc_modifier').value;
		var cdesc = document.getElementById('proc_title').value;
		var cplan = document.getElementById('proc_plan').value;
		var on_fee = document.getElementById('proc_on_fee').value;
		var jst = document.getElementById('proc_justify');
		if(jst == null) {
			jst = -1;
		} else {
		 jst = document.getElementById('proc_justify').value;
		}
		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: 'update',
			code: code, ctype: ctype, cmod: cmod, cunits: cunits, on_fee: on_fee,
			cdesc: cdesc, cplan: cplan, type: 'procedures', suppress: 'yes', 
			level: level, dr: dr, justify: jst
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT add ['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
	} else output = 'success';

	ajaxGracefulStop(msg);
	if(output == 'error')  return false;
	return true;
}

function saveAllDatedDocuments(patientID,frm,frmId)
{
	var div = document.getElementById('save-notification');
	if(div != null) {
		div.innerHTML = 'Updating Existing Plans....';
		div.style.display = 'block';
	}
	var base = '../../..';
	var output = 'error';
	var msg = '';
	
	// LOOP THROUGH ALL THE PROCEDURES CURRENTLY ON THIS FORM
	var max = document.getElementById('tmp_proc_cnt');
	if(max == null) {
		msg = 'No Dated Documnet Items Were Set';
		ajaxGracefulStop(msg);
		return false;
	}
	max = document.getElementById('tmp_proc_cnt').value;
	if(!max || max < 0) output = 'success';
	var i;
	for(i=0; i<=max; i++) {
		var item = document.getElementById('ddoc_id_'+i);
		if(item == null) continue;
		item = document.getElementById('ddoc_id_'+i).value;
		var cplan = document.getElementById('ddoc_nt_'+i).value;
		var postData = { 
			pid: patientID, frmdir: frm, id: frmId, action: 'update',
			cplan: cplan, type: 'dated_document', suppress: 'yes', item: item
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT update ['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
		if(output == 'error') break;
	}
	
	if(output == 'error') {
		ajaxGracefulStop(msg);
		return false;
	}

	// NOW IF THE NEW PROCEDURE HAS INFORMATION, SAVE IT
	var code = document.getElementById('ddoc_doc_id');
	if(code != null) code = document.getElementById('ddoc_doc_id').value;
	if(code) {
		var ctype = document.getElementById('ddoc_type').value;
		var date = document.getElementById('ddoc_dt').value;
		var title = document.getElementById('ddoc_title').value;
		var cplan = document.getElementById('ddoc_nt').value;
		var postData = { 
			enc: encID, pid: patientID, frmdir: frm, id: frmId, action: 'add',
			code: code, ctype: ctype, cplan: cplan, type: 'dated_document', 
			suppress: 'yes', start_dt: date
		};

		$.ajax({
			type: "POST",
			url: base + '/library/wmt-v2/lists.ajax.php',
			datatype: "html",
			data: postData,
			success: function(result) {
				if(result['error']) {
					msg = 'Could NOT add ['+code+'], details:\n'+result['error'];
				} else {
					output = result;
				}
			},
			async: false
		});
	} else output = 'success';

	ajaxGracefulStop(msg);
	if(output == 'error')  return false;
	return true;
}
