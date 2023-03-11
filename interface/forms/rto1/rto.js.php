<?php

$form_refresh_url = $rootdir.'/forms/rto1/new.php?mode=refresh&pid='.$pid.'&pop='.$popmode.'&pageno='.$pageno;
$form_lbf_save_url = $rootdir.'/forms/rto1/new.php?mode=lbf_save&pid='.$pid.'&pop='.$popmode;
$form_rto_save_url = $rootdir.'/forms/rto1/new.php?mode=new_rto_save&pid='.$pid.'&pop='.$popmode;
$form_url = $rootdir.'/forms/rto1/new.php?pid='.$pid.'&pop='.$popmode;
$reopen_url = $rootdir.'/forms/rto1/new.php?pid='.$pid.'&pop='.$popmode;

$base_form_url = $rootdir.'/forms/rto1/new.php';
$base_form_qtr = 'pid='.$pid.'&pop='.$popmode;

?>

<script type="text/javascript">
	$(document).ready(function(){
		//$('#rto_action').change(function(){
			var rto_action_val = $('#rto_action').find('option:selected').val();
			var rto_status_val = $('#rto_status option:selected').val();
			
			if(rto_action_val != "" && rto_status_val == "") {
				$("#rto_status").val("p").change();
			} 
		//});
	});
</script>

<style type="text/css">
	.lbf_form_iframe {
		width: 100%;
		height: 0px;
	}
	.lbfbtn {}
	.hideContent {
		display: none !important;
	}
	.lastRow > div {
		margin-top: 15px;
		border-top: 2px solid;
		margin-bottom: 15px;
	}
	.summeryContainer {
		height: 100%;
		font-size: 12px;
		display: inline-block;
	}
	.summeryContainer table tr {
		display: none;
	}
	.summeryContainer table tr:nth-child(1), 
	.summeryContainer table tr:nth-child(2),
	.summeryContainer table tr:nth-child(3),
	.summeryContainer table tr:nth-child(4) {
		display: table-row !important;
	}
	.paginationContainer {
		text-align: center;
	}
	.pagination li > input {
		border: 1px solid #ddd;
		margin-left: 5px!important;
	}

	.pagination li > input:hover,
	.pagination li.actionBtn > input:hover {
		border: 1px solid #ddd;
		background-color: #ddd!important;
	}

	.pagination li.actionBtn > input {
		background-color: #fff!important;
		color: #333333 !important;
		outline: none;
	}

	.pagination li.actionBtn.disabled > input {
		background-color: #fff;
		color: #777 !important;
		pointer-events: none;
	}

	.pagination li.pageBtn > input {
		background-color: #fff;
		color: #333333 !important;
	}

	.pagination li.pageBtn.active > input {
		background-color: #2672ec!important;
		color: #FFF !important;
		pointer-events: none;
	}

	.newmodDate {
		vertical-align: top;
	}

	.newmodDate .dateTitle {
		vertical-align: top;
		margin-top: 2px;
		display: inline-block;
	}
	td .lastRow.page:last-child {
		display: none;
	}

	select.orderType:read-only {
		pointer-events: none;
	}

	.fRtoAction, .fRtoStatus {
		height: auto;
	}

	.filterTable {
		width: 100%;
	}

	.filterBtn {
		float: right;
	}

	.filterContainer {
		padding-left: 5px; padding-right: 5px;
		margin-bottom: 20px;
		margin-top: 10px;
	}

	.headerContainer {
		display: grid;
		grid-template-columns: auto 1fr;
		grid-gap: 10px;
	}

	.filterFieldContainer {
		display: grid;
	    grid-template-columns: 1fr 1fr 1fr 1fr;
	    grid-column-gap: 10px;
	    grid-row-gap: 0px;
	}

	.filterFieldContainer > div {
		display: grid;
	    grid-template-columns: auto 1fr;
	    height: auto;
	}

	.innerContainer {
		padding-top: 10px;
		padding-bottom: 10px;
	}

	.fieldValueLabel {
		font-family: Arial,Helvetica,asns-serif;
		font-size: 12px;
	}
</style>

<script type="text/javascript">
	var formData = {};
	$(document).ready(function(){
		$('select[name^="rto_action_"]').change(function(){
			var url = '<?php echo $form_refresh_url; ?>';
			$('form[name="form_rto"]').attr('action', url).submit();
		});

		var rtoActionEle = $('select[name="rto_action"]');
		rtoActionEle.on('focusin', function(){
		    $.data(this, 'rto_action_val', $(this).val());
		});

		$('select[name="rto_action"]').change(function(){
			var url = '<?php echo $form_refresh_url; ?>';
			var old_val = $.data(this, 'rto_action_val');
			
			if(old_val != "") {
				if (!confirm("Notes fields will not transfer between order types. Do you want to still update the order type?\n\nPress Ok to Update  or Cancel to undo the changes.")) {
					$(this).val($.data(this, 'rto_action_val'));
				} else {
					$('#rto_notes').val("");
				}
			}

			$('form[name="form_rto"]').attr('action', url).submit();
		});

		var formData1 = $('form[name="form_rto"]').serializeArray();
		formData = getFormData($('form[name="form_rto"]'));

		<?php if($mode == "new_rto_save" && !empty($neworderid)) { ?>
			open_ldf_form('<?php echo $pid; ?>', '<?php echo $rtoformname; ?>', '<?php echo $neworderid; ?>', 0, '<?php echo $rtoformtitle; ?>');
		<?php } ?>

		$('.datepicker').datetimepicker({
			<?php $datetimepicker_timepicker = false; ?>
		  	<?php $datetimepicker_showseconds = false; ?>
		 		<?php $datetimepicker_formatInput = true; ?>
		  	<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
		  	<?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
	});

	function resizeIframe(obj) {
    	//obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
    	if(obj.contentWindow.document.querySelector('body > .container')) {
    		var objHeight = obj.contentWindow.document.querySelector('body > .container').offsetHeight;
    		obj.style.height = (objHeight) + "px";
    	}
  	}

  	if (window.addEventListener) {
	    window.addEventListener("message", onMessage, false);        
	} else if (window.attachEvent) {
	    window.attachEvent("onmessage", onMessage, false);
	}

	function onMessage(event) {
	    // Check sender origin to be trusted
	    //if (event.origin !== "http://example.com") return;

	    var data = event.data;      
	    if (typeof(window[data.func]) == "function") {
	        window[data.func].call(null, data.message);
	    }
	}

	// Function to be called from iframe
	function saveData(rto_id) {
	    //alert(message);
	    var url = '<?php echo $form_lbf_save_url; ?>&rto_id='+rto_id;
		$('form[name="form_rto"]').attr('action', url).submit();
	}

	function onResizeIframe() {
		[].forEach.call(document.querySelectorAll('iframe'), function(iframe) {
			resizeIframe(iframe);
		});
	}

	function newOrderInit() {
		[].forEach.call(document.querySelectorAll('iframe'), function(iframe) {
			iframe.style.height = '0px';
			resizeIframe(iframe);
		});
	}

	// This invokes the find-addressbook popup.
	function open_ldf_form(pid, lformname, rto_id, form_id, form_title) {
		var url = '<?php echo $GLOBALS['webroot']."/interface/forms/rto1/ldf_form.php" ?>'+'?pid='+pid+'&formname='+lformname+'&visitid='+rto_id+'&id='+form_id+'&submod=popup';
	  	let title = form_title;
	  	dlgopen(url, 'ldf_form', 900, 500, '', title);
	}

	function setInternalMsg(note_id) {
		//parent.left_nav.forceDual();
        parent.left_nav.loadFrame('RBot', 'RTop', "main/messages/internal_note.php?mode=edit&noteid="+note_id+"&tabmode=true");
	}

	function setMsg(type, message_id, pid) {
		loadMessage(type, message_id, pid)
	}


	function loadMessage(type, id, pid) {
		var url = "<?php echo $GLOBALS['webroot'] ?>/interface/main/messages/portal_message.php?pid="+pid+"&id=" + id;
		if (type == 'PHONE') url = "<?php echo $webroot ?>/interface/main/messages/phone_call.php?pid="+pid+"&id=" + id;
		if (type == 'SMS') url = "<?php echo $webroot ?>/interface/main/messages/sms_message.php?pid="+pid+"&id=" + id;
		if (type == 'EMAIL') url = "<?php echo $GLOBALS['webroot'] ?>/interface/main/messages/email_message.php?pid="+pid+"&id=" + id;
		if (type == 'FAX') url = "<?php echo $GLOBALS['webroot'] ?>/interface/main/messages/fax_message.php?pid="+pid+"&id=" + id;
		if (type == 'P_LETTER') url = "<?php echo $webroot ?>/interface/main/messages/postal_letter.php?pid="+pid+"&id=" + id;
		dlgopen(url, 'view_msg', 700, 500);
	}

	function open_view_logs(pid, rto_id) {
		var url = '<?php echo $GLOBALS['webroot']."/interface/forms/rto1/lbf_view_logs.php" ?>'+'?pid='+pid+'&rto_id='+rto_id;
	  	let title = "View logs";
	  	dlgopen(url, 'view_logs', 900, 500, '', title);
	}

	// used to display the patient demographic and encounter screens
    function goToMessage(id) {
    	top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/messages.php?task=edit&noteid="+id;
    }

	function lbfFormPopup(pid, formname, visitid, formid) {
		var url = '<?php echo $form_refresh_url; ?>';
		$('form[name="form_rto"]').attr('action', url).submit();
	}

	function addNewFormPopup(pid, rto_id) {
		var url = '<?php echo $form_refresh_url; ?>';
		//$('form[name="form_rto"]').attr('action', url).submit();
		window.location = '<?php echo $reopen_url; ?>';
	}

	function saveRtoData(formname, form_title) {
		var url = '<?php echo $form_rto_save_url; ?>'+'&rto_formname='+formname+'&rto_form_title='+form_title;
			$('form[name="form_rto"]').attr('action', url).submit();
	}

	function changePage(page, section= '') {
		//document.forms[0].action='<?php echo $form_url; ?>'+'&mode=rtodisp&all=all&pageno='+page;
		//document.forms[0].submit();
		var sectionStr = section != '' ? '#'+section : '';
		//window.location.href = '<?php //echo $base_form_url; ?>'+'?'+'<?php //echo $base_form_qtr; ?>'+'&mode=rtodisp&all=all&pageno='+page+sectionStr;
		filterSubmit(page, sectionStr);
	}

	async function SubmitRTONew(base,wrap,formID)
	{
		var rto_resp_user = $('#rto_resp_user').val();

		if(rto_resp_user == "") {
			alert("Please select assigned To");
			return false;
		}

		if(!validateRTO(false)) return false;

		var caseData = await checkCaseValidation('<?php echo $pid; ?>');
		if(caseData === false) {
			return false;
		}

		SetScrollTop();
	  	document.forms[0].action=base+'&mode=rto&wrap='+wrap;

	  	if(formID != '' && formID != 0) {
	 		document.forms[0].action=base+'&mode=rto&wrap='+wrap+'&id='+formID;
		}
		document.forms[0].submit();
	}
</script>

<style type="text/css">
	.actionBtnContainer {
		display: grid;
		grid-gap: 2px;
	}
	.actionBtnContainer .css_button_small, .actionBtnContainer .btn {
		width: 100%;
		text-align: center;
		white-space: nowrap;
	}
	.updateBtn {
		background-color: green !important;
	}
	.ordeStatusFilterContainer {
		grid-row: 1 /span 2;
		grid-column: 4 /span 1;
	}
	.ordeActionFilterContainer {
		grid-row: 1 /span 2;
		grid-column: 1 /span 1;
	}
	.statInputContainer {
		display: grid;
	    grid-template-columns: auto 1fr;
	    grid-column-gap: 10px;
	    align-items: center;
	    justify-items: left;
	}
</style>

<script type="text/javascript">
	$(document).ready(function(){
		jQuery('[data-toggle="tooltip"]').tooltip({
			classes: {
	            "ui-tooltip": "ui-corner-all uiTooltipContainer",
	            "ui-tooltip-content" : "ui-tooltip-content uiTooltipContent"
	        },
			position: {
			    my: "left center", at: "right center"
			},
		    content: function(){
		      var element = $( this );
		      //return element.attr('title')
		      return element.html();
		    }
		});
	});
</script>