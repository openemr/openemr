function addDocumentLink(doc_id, pid, tag) {
	var action = 'add';
	if(arguments.length > 3) var action = arguments[3];
	var output = 'error';
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ajax/link_document.ajax.php",
		datatype: "html",
		data: {
			action: action,
			pid: pid,
			doc_id: doc_id,
			link_type: tag
		},
		success: function(result) {
			if(result['error']) {
				output = '';
				alert('There was a problem saving the link - details: \n'+result['error']);
			} else {
				output = result;
			}
		},
		async: true
	});
	return output;
}

function breakLink(doc_id, pid, doc_type, prefix) {
	if(!confirm("Delete This Image Link - Are You Sure?\n\n (The Document Will NOT Be Deleted)")) return false;
	addDocumentLink(doc_id, pid, doc_type, 'delete');
	var anchor_name = 'link_' + prefix + doc_type + '_doc';
	var anchor_target = document.getElementById(anchor_name);
	var img_name = 'img_' + prefix + doc_type + '_div';
	var img_target = document.getElementById(img_name);
	if(anchor_target != null) {
		var anchor = 'No document is currently attached';
		anchor_target.innerHTML = '<i>' + anchor + '</i>';
	}
	if(img_target != null) {
		var img = '<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_add.png" width="20" height="20" id="img_ ' + prefix + doc_type + '_doc" border="0" alt="[d]" style="vertical-align: bottom; cursor:pointer;" onclick="wmtOpen(\'<?php echo $GLOBALS['webroot']; ?>/custom/document_popup.php?pid=' + pid + '&task=link&link_type=' + doc_type + '&prefix=' + prefix + '\', \'_blank\', 800, 600);" title"Click here to link a document">&nbsp;';
		img_target.innerHTML = img;
	}
}

function setDatedDocument(doc_id, pid, doc_dt, category, title) {
	var doc_type = '';
	var prefix = '';
	var item_id = '';
  if(arguments.length > 5) doc_type = arguments[5];
  if(arguments.length > 6) prefix = arguments[6];
  if(arguments.length > 7) item_id = arguments[7];
	if(doc_type) {
		addDocumentLink(doc_id, pid, doc_type);
		var anchor_name = 'link_' + prefix + doc_type + '_doc';
		var anchor_target = document.getElementById(anchor_name);
		var img_name = 'img_' + prefix + doc_type + '_div';
		var img_target = document.getElementById(img_name);
		if(anchor_target != null) {
			var anchor = 'Document: <a href="javascript:;" onclick="wmtOpen(\'<?php echo $GLOBALS['webroot']; ?>/controller.php?document&retrieve&patient_id=' + pid + '&document_id=' + doc_id + '&as_file=false\', \'_blank\', 800, 600);"><i>' + title + ' (' + category + ')</i></a>';
			anchor_target.innerHTML = anchor;
		}
		if(img_target != null) {
			var img = '<img src="<?php echo $GLOBALS['webroot']; ?>/images/link_break.png" width="20" height="20" id="img_' + prefix + doc_type + '_doc_unlink" border="0" alt="[x]" style="vertical-align: bottom; cursor:pointer;" onclick="breakLink(\'' + doc_id +'\', \'' + pid + '\', \'' + doc_type + '\', \'' + prefix + '\');" title"Click here to remove the link">&nbsp;';
			img_target.innerHTML = img;
		}
		if(doc_dt) {
			var dt_fld = document.getElementById(prefix + doc_type);
			if(dt_fld != null) dt_fld.value = doc_dt;
		}
  } else {
		// THIS IS THE PART USED BY THE DATED DOCUMENTS LIST
  	// alert('The List One ['+doc_id+'] ('+pid+') -'+category+'- ['+title+']');
	  document.getElementById('ddoc_doc_id').value = doc_id;
	  if(document.getElementById('ddoc_dt').value == '') 
      document.getElementById('ddoc_dt').value = doc_dt;
	  document.getElementById('ddoc_type').value = category;
	  document.getElementById('ddoc_title').value = title;
  }
}
