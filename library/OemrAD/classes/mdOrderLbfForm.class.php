<?php

namespace OpenEMR\OemrAd;

/**
 * OrderLbfForm Class
 */
class OrderLbfForm {
	
	function __construct(){
	}

	/*
	public static function olbf_init() {
		global $rootdir, $pid, $popmode, $newordermode;

		if($newordermode !== true) {
			return;
		}

		$mode = strip_tags($_GET['mode']);

		// if($mode == 'refresh') {
		// 	$_SESSION['order_tmp_request_data'] = $_REQUEST;
		// 	$_SESSION['order_tmp_post_data'] = $_POST;
		// } else if($mode == 're_refresh') { 
		// 	$_REQUEST = $_SESSION['order_tmp_request_data'];
		// 	$_POST = $_SESSION['order_tmp_post_data'];
		// } else {
		// 	unset($_SESSION['order_tmp_request_data']);
		// 	unset($_SESSION['order_tmp_post_data']);
		// }
	}*/

	/*
	//Action
	function action_rto_head() {
		self::rto_head();
	}
	*/

	/*
	public static function rto_head() {
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$('#rto_action').change(function(){
					var rto_action_val = $(this).find('option:selected').val();
					var rto_status_val = $('#rto_status option:selected').val();
					
					if(rto_action_val != "" && rto_status_val == "") {
						$("#rto_status").val("p").change();
					} 
				});
			});
		</script>
		<?php
	}
	*/

	/*
	function rto_report_head() {
		?>
		<script type="text/javascript">
			async function goToOrder(id, pid, pubpid, pname, dobstr) {
				await setPatient(pid);
	        	setPatientData(pid, pubpid, pname, dobstr);
	        	parent.left_nav.loadFrame('RTop', 'RTop', '/forms/rto1/new.php?pop=db&id=' + id);
	        	//top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto1/new.php?pop=db&id="+id;
	        }

	        async function setPatient(pid) {
				var bodyObj = { set_pid : pid};
				const result = await $.ajax({
					type: "GET",
					url: "<?php echo $GLOBALS['webroot'].'/interface/new/ajax/set_patient.php'; ?>",
					datatype: "json",
					data: bodyObj
				});

				return true;
			}

			function setPatientData(pid, pubpid, pname, dobstr) {
				//parent.left_nav.setPatient(pname, pid, pubpid, '',dobstr);
				goParentPid(pid);
			}
		</script>
		<style type="text/css">
			.summeryContainer table {
				width: auto!important;
				border: 0px !important;
			}

			.summeryContainer table tr:nth-child(odd), .summeryContainer table tr:nth-child(odd),
			.summeryContainer table tr:nth-child(even), .summeryContainer table tr:nth-child(even) {
				background-color: transparent !important;
			}

			.summeryContainer table td, .summeryContainer table td {
				padding: 2px 5px !important;
			}
		</style>
		<?php
	}
	*/

	/*
	function imaging_order_report_head() {
		?>
		<script type="text/javascript">
		  $(document).ready(function(){
		    $(".rto_note_container").each(function() {
		    	var contentHeight = $(this).find('.content.summeryContainer').children().outerHeight();
		    	if(contentHeight > 62) {
		    		$(this).find('.actBtn').show();
		    	}	

		    	var contentHeight1 = $(this).find('.content.summeryNoteContainer').children().outerHeight();
		    	if(contentHeight1 > 47) {
		    		$(this).find('.actBtn').show();
		    	}
		    });

		    jQuery('[data-toggle="tooltip"]').tooltip({
		        content: function(){
		          var element = $( this );
		          //return element.attr('title')
		          return element.html();
		        },
		        track: true
		    });
		  });
		</script>

		<script type="text/javascript">
			async function goToOrder(id, pid, pubpid, pname, dobstr) {
				await setPatient(pid);
	        	setPatientData(pid, pubpid, pname, dobstr);
	        	parent.left_nav.loadFrame('RTop', 'RTop', '/forms/rto1/new.php?pop=db&id=' + id);
	        	//top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto1/new.php?pop=db&id="+id;
	        }

	        async function setPatient(pid) {
				var bodyObj = { set_pid : pid};
				const result = await $.ajax({
					type: "GET",
					url: "<?php echo $GLOBALS['webroot'].'/interface/new/ajax/set_patient.php'; ?>",
					datatype: "json",
					data: bodyObj
				});

				return true;
			}

			function setPatientData(pid, pubpid, pname, dobstr) {
				//parent.left_nav.setPatient(pname, pid, pubpid, '',dobstr);
				goParentPid(pid);
			}
		</script>
		<style type="text/css">
			.summeryContainer table {
				width: auto!important;
				border: 0px !important;
			}

			.summeryContainer table tr:nth-child(odd), .summeryContainer table tr:nth-child(odd),
			.summeryContainer table tr:nth-child(even), .summeryContainer table tr:nth-child(even) {
				background-color: transparent !important;
			}

			.summeryContainer table td, .summeryContainer table td {
				padding: 2px 5px !important;
			}

			.summeryContainer {
				height: 100%;
				font-size: 12px;
				display: inline-block;
			}
			.summeryContainer table tr {
				//display: none;
			}
			.summeryContainer table tr:nth-child(1), 
			.summeryContainer table tr:nth-child(2),
			.summeryContainer table tr:nth-child(3),
			.summeryContainer table tr:nth-child(4) {
				//display: table-row !important;
			}

			.ui-tooltip-content table {
			  font-size: 12px;
			}

			.ui-tooltip-content > div {
				font-size: 12px;
			}
		</style>

		<style type="text/css">
			.rto_note_container .read-more-state {
			  display: none;
			}

			.rto_note_container .content.summeryNoteContainer {
				max-height: 45px;
			    overflow: hidden;
			    height: auto;
			    position: relative;
			    width: auto;
			    margin-bottom: 8px;
			}

			.rto_note_container .content.summeryContainer {
				max-height: 60px;
			    overflow: hidden;
			    height: auto;
			    position: relative;
			    width: auto;
			    margin-bottom: 8px;
			}

			.rto_note_container .actBtn {
				display: none;
			}

			.rto_note_container .actBtn .readmore {
				display: block;
			}

			.rto_note_container .actBtn .lessmore {
				display: none;
			}

			.read-more-state:checked ~ .content {
			  opacity: 1;
			  max-height: 999em !important;
			}

			.read-more-state:checked ~ .actBtn .lessmore {
				display: block;
			}

			.read-more-state:checked ~ .actBtn .readmore {
				display: none;
			}

			@media print {
				.rto_note_container .content.summeryNoteContainer {
					max-height: 100%;
				}

				.rto_note_container .content.summeryContainer {
					max-height: 100%;
				}
			}
		</style>
		<?php
	}*/

	/*
	function new_order_init() {
		?>
		<script type="text/javascript">
		    window.parent.postMessage({
		        'func': 'newOrderInit'
		    }, "*");
		</script>
		<?php
	}*/

	/*
	public static function set_rto_status() {
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
		<?php
	}*/

	/*
	public static function olbf_head() {
		global $rootdir, $pid, $popmode, $newordermode, $pageno, $neworderid, $rtoformname, $rtoformtitle, $mode;

		if($newordermode !== true) {
			return;
		}

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

			.fRtoDate {
				height: auto;
				padding: 0px !important;
			}

			.fRtoId, .fRtoCase {
				height: auto;
				padding: 0px !important;
			}

			.fRtoAction, .fRtoStatus, .fRtoId, .fRtoCase, .fRtoDate {
				width: 100%;
				margin: 0px!important;
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

			.wmtFInput {
				padding: 1px!important;
				margin: 0px !important;
				font-family: Arial,Helvetica,asns-serif;
	    		font-size: 12px;
	    		width: 100%;
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
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/forms/rto1/ldf_form.php" ?>'+'?pid='+pid+'&formname='+lformname+'&visitid='+rto_id+'&id='+form_id+'&submod=popup';
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
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/forms/rto1/lbf_view_logs.php" ?>'+'?pid='+pid+'&rto_id='+rto_id;
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
		<?php
	}
	*/

	/*
	public static function orderScript() {
		?>
		<script type="text/javascript">
			var order_cnt = "";
			var order_case_type = "";
			function sel_case(pid, cnt = "", type = "") {
				order_cnt = cnt;
				order_case_type = type;
			  	var href = "<?php echo $GLOBALS['webroot'].'/interface/forms/cases/case_list.php?mode=choose&popup=pop&pid='; ?>"+pid;
			  	dlgopen(href, 'findCase', 'modal-xl', 'modal-xl', '', '<?php echo xlt('Case List'); ?>');
			}

			function setCase(case_id, case_dt, desc) {
				var decodedDescString = atob(desc);

				if(order_case_type == "filter") {
					document.getElementById('tmp_case_id').value = case_id;
				} else {
					if(order_cnt && order_cnt != "") {
						var rto_case = document.getElementById('rto_case_'+order_cnt);
						rto_case.value = case_id;
						rto_case.dispatchEvent(new Event('change'));

						document.getElementById('case_description_title_'+order_cnt).innerHTML = decodedDescString;
					} else {
						document.getElementById('rto_case').value = case_id;
					}
				}
			}

			async function checkCaseValidation(pid, r_action = false, c_id = false) {
				var rto_action = r_action;
				var case_id = c_id;
				
				if(rto_action === false) {
					rto_action = document.getElementById('rto_action').value;
				}

				if(case_id === false) {
					case_id = document.getElementById('rto_case').value;
				}
				
				if(!rto_action || rto_action == "") {
					return true;
				}

				if(!case_id || case_id == 0) {
			        var cCount = await caseCount(pid);
			        if(Number(cCount) > 0) {
			            alert('<?php echo xls("You must choose a case"); ?>');
			            return false;
			        }
			    } else {
			    	var isRecentCaseInActive = await checkRecentInactive(pid, case_id);
			        if(isRecentCaseInActive == true) {
			            var msg1 = '<?php echo xls('Selected case is inactive. Choose "OK" to save the chosen case and change the case state from inactive to active.  Choose "Cancel" to choose another case or create a new case'); ?>?';
			            var confirmRes  = confirm(msg1);
			            if(confirmRes) {
			                var activateCaseDAta = await activateCase(pid, case_id)
			            } else if(!confirmRes) {
			                return false;
			            }
			        }
				}
			} 

			function getFormData($form){
			    var unindexed_array = $form.serializeArray();
			    var indexed_array = {};

			    $.map(unindexed_array, function(n, i){
			        indexed_array[n['name']] = n['value'];
			    });

			    return indexed_array;
			}
		</script>
		<?php
	}
	*/

	/*
	public static function rto_init() {
		global $fieldList, $dateFields, $newordermode, $hideClass, $dt, $rto_page_details, $pageno, $showNewOrder;

		$dt['layout_form'] = false;

		$hideClass = $newordermode == true ? 'hideContent' : '';
		$showNewOrder = (!isset($newordermode) || $newordermode === false || ($newordermode === true && isset($rto_page_details['total_pages']) && ($rto_page_details['total_pages'] == 0 ||$rto_page_details['total_pages'] == $pageno))) ? true : false;

		if($newordermode !== true) {
			return;
		}

		$order_action = isset($_REQUEST['rto_action']) ? $_REQUEST['rto_action'] : "";
		$layoutData = self::getLayoutForm($order_action);
		if(!empty($layoutData) && !empty($layoutData['grp_form_id'])) {
			$dt['layout_form'] = true;
		}

		$fieldList = array(
			'rto_action' => 'rto_action',
			'date' => 'rto_date1',
			'rto_ordered_by' => 'rto_ordered_by',
			'id' => 'rto_id',
			'test_target_dt' => 'rto_test_target_dt',
			'rto_status' => 'rto_status',
			'rto_resp_user' => 'rto_resp',
			'rto_notes' => 'rto_notes',
			'rto_target_date' => 'rto_target_date',
			'rto_num' => 'rto_num',
			'rto_frame' => 'rto_frame',
			'rto_date' => 'rto_date',
			'rto_repeat' => 'rto_repeat',
			'rto_stop_date' => 'rto_stop_date'
		);

		$dateFields = array('date', 'rto_date', 'rto_target_date', 'rto_stop_date');
	}*/

	/*
	public static function rto_data_setup() {
		global $dt, $rto, $cnt, $mode, $fieldList, $dateFields, $newordermode, $pageno, $pagenoQtr;

		$rto['layout_form'] = false;
		$pagenoQtr = isset($pageno) ? '&pageno='.$pageno : '';

		if($newordermode === true) {
			$order_action_n = isset($_REQUEST['rto_action_'.$cnt]) ? $_REQUEST['rto_action_'.$cnt] : "";

			if(empty($order_action_n)) {
				$order_action_n = isset($rto['rto_action']) ? $rto['rto_action'] : '';
			}

			$layoutData_n = self::getLayoutForm($order_action_n);
			if(!empty($layoutData_n) && !empty($layoutData_n['grp_form_id'])) {
				$rto['layout_form'] = true;
			}

			if($mode == 'refresh' || $mode == 'lbf_save') {
				$tmp_layout_form = $dt['layout_form'];
				$dt = $_POST;
				$dt['layout_form'] = $tmp_layout_form;

				foreach ($fieldList as $key => $value) {
					if(in_array($key, $dateFields)) {
						if(!empty($_POST[$value.'_'.$cnt])) {
							$rto[$key] = date("Y-m-d", strtotime($_POST[$value.'_'.$cnt]));
						}
					} else {
						$rto[$key] = $_POST[$value.'_'.$cnt];
					}
				}

				foreach ($dateFields as $key => $field) {
					if(!empty($_POST[$field])) {
						$dt[$field] = date("Y-m-d", strtotime($_POST[$field]));
					}
				}
			}
		}
	}
	*/

	/*
	public static function before_rto_save() {
		global $dt, $cnt, $newordermode, $pid, $rto_data_bup;

		if($newordermode !== true) {
			return;
		}

		$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
		$rto_action = isset($dt['rto_action_'.$cnt]) ? $dt['rto_action_'.$cnt] : '';
		
		$layoutData = self::getLayoutForm($rto_action);
		$layoutFormData = self::getRtoLayoutFormData($pid, $rto_id);

		if(empty($layoutData)) {
			$layout_form_id = isset($layoutFormData['form_id']) ? $layoutFormData['form_id'] : '';
			$formdir = isset($layoutFormData['grp_form_id']) ? $layoutFormData['grp_form_id'] : '';
			$formname = isset($layoutFormData['grp_title']) ? $layoutFormData['grp_title'] : '';

			self::manageAction($pid, $rto_id, $layout_form_id);
		}

		if(isset($dt['rto_id_'.$cnt])) {
			$rto_data_f =  !empty($rto_data_bup) ? $rto_data_bup[0] : array();
			self::manageFormState($dt['rto_id_'.$cnt], $pid, $cnt, $dt, $rto_data_f);
		}
	}
	*/

	/*
	public static function manageFormState($form_id, $pid, $cnt, $dt, $rto_data) {
		$fieldList = array(
			'rto_action'  => 'rto_action',
			'rto_status'  => 'rto_status',
			'rto_ordered_by' => 'rto_ordered_by',
			'rto_resp_user' => 'rto_resp',
			'rto_notes' => 'rto_notes'
		);

		foreach($fieldList as $key => $fieldItem) {
			$oldV = isset($rto_data[$key]) ? $rto_data[$key] : '';
			$newV = isset($dt[$fieldItem.'_'.$cnt]) ? $dt[$fieldItem.'_'.$cnt] : '';

			$isNeedToLog = self::needToLog($newV, $oldV);

			if($isNeedToLog === true) {
				$sql = "INSERT INTO `form_value_logs` ( field_id, form_name, new_value, old_value, pid, form_id, username ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
				sqlInsert($sql, array(
					$key,
					"form_rto",
					$newV,
					$oldV,
					$pid,
					$form_id,
					$_SESSION['authUserID']
				));
			}
		}
	}
	*/

	/*
	public static function needToLog($value, $old_value) {
		if($value !== $old_value) {
			return true;
		}

		return false;
	}*/

	/*
	public static function fetchAlertLogs($form_id) {
		$result = sqlStatement("SELECT fl.*, u.username as user_name  FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username WHERE fl.form_name = ? AND fl.form_id = ? ", array('form_rto',$form_id));

		$data = array();
		while ($row = sqlFetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}*/

	/*
	public static function olbf_setup($pid) {
		global $dt, $newordermode, $mode;

		if($newordermode !== true) {
			return;
		}

		if($mode == "delrto") {
			$cnt = trim($_GET['itemID']);
			$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
			
			$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
			$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;

			self::manageAction($pid, $rto_id, $form_id);
		}
	}*/

	/*
	function delete_rto_form($pid) {
		$rto_id = isset($_REQUEST['rto_id']) ? $_REQUEST['rto_id'] : '';
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
		self::manageAction($pid, $rto_id, $form_id);

		echo "Hi there";
		exit();
	}*/

	/*
	public static function updateDataAction($formname, $formdir, $rto_id, $id, $pid) {
		sqlStatement("UPDATE form_order_layout SET form_name = ?, formdir = ? WHERE rto_id = ? AND 	form_id = ? AND pid = ? ", array($formname, $formdir, $rto_id, $id, $pid));
	}*/

	/*
	public static function manageAction($pid, $rto_id, $id) {
		sqlStatement("DELETE FROM form_order_layout WHERE pid = ? AND rto_id = ? ", array($pid, $rto_id));
		sqlStatement("DELETE FROM lbf_data WHERE form_id = ? ", array($id));
	}*/

	/*
	function forms_head($pid) {
		global $rootdir;
		?>
		<script type="text/javascript">
			// Called to open the data entry form a specified encounter form instance.
			function openRtoForm(formdir, formname, formid) {
			  var url = '<?php echo "$rootdir/patient_file/rto/view_form.php?formname=" ?>' +
			    formdir + '&id=' + formid;
			  if (formdir == 'newpatient' || !parent.twAddFrameTab) {
			    top.restoreSession();
			    location.href = url;
			  }
			  else {
			    parent.twAddFrameTab('enctabs', formname, url);
			  }
			  return false;
			}
		</script>
		<?php
	}*/

	/*
	function new_order_list($pid) {
		global $divnos, $GLOBALS, $attendant_id, $encounter, $esign, $rootdir;
		$layoutData = self::getAllRtoLayoutFormData($pid);

		$divnos = isset($divnos) ? $divnos : 1;
		foreach ($layoutData as $key => $iter) {
			$formdir = $iter['formdir'];
			$form_id = $iter['form_id'];
			$rto_id = $iter['rto_id'];
			$form_name = ($formdir == 'newpatient') ? xl('Visit Summary') : xl_form_title($iter['form_name']);
		?>
			<tr id="<?php echo 'neworder_'.$formdir.'~'.$form_id; ?>" class="text onerow">
				<td style="border-bottom:1px solid">
					<div class='form_header'>
						<a href='#' onclick='divtoggle("<?php echo 'spanid_'.$divnos ?>","<?php echo 'divid_'.$divnos ?>");' class='small' id='<?php echo "aid_".$divnos ?>'>
							<div class='formname'><?php echo text($form_name); ?></div>
							<?php echo xlt('by') . " " . text($form_author); ?>
							(<span id="<?php echo 'spanid_'.$divnos; ?>" class="indicator"><?php echo ($divnos == 1 ? xlt('Collapse') : xlt('Expand')); ?></span>)
						</a>
					</div>
					<div class='form_header_controls'>
						<a class="css_button_small form-edit-button" id="form-edit-button-LBF_chiro_rehab-33" href="#" title="Edit this form" onclick="return openRtoForm('LBF_chiro_rehab', 'Daily Chiro', '1')"><span>Edit</span></a>
						<a target="_blank" href="<?php echo $rootdir.'/forms/LBF/printable.php?formname='.urlencode($formdir).'&formid='.urlencode($iter['form_id']).'&visitid='.urlencode($encounter).'&patientid='.urlencode($pid) ?>" class="css_button_small" title="Print this form" onclick="top.restoreSession()"><span>Print</span></a>
						<a href="<?php echo $rootdir.'/patient_file/rto/delete_form.php?'.'formname='.$formdir.'&id='.$iter['id'].'&rto_id='.$rto_id.'&pid='.$pid ?>" class="css_button_small" title="Delete this form" onclick="top.restoreSession()"><span>Delete</span></a>
					</div>
				</td>
			</tr>
			<tr>
				<td valign="top" class="formrow">
					<div class="tab" id="<?php echo 'divid_'.$divnos ?>" style="display: <?php echo ($divnos == 1 ? 'block' : 'none'); ?>">
						<?php
						// Use the form's report.php for display.  Forms with names starting with LBF
				        // are list-based forms sharing a single collection of code.
				        //
				        if (substr($formdir, 0, 3) == 'LBF') {
				            include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

				            call_user_func("lbf_report", $attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
				        } else {
				            include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
				            call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
				        }

				        if ($esign->isLogViewable()) {
            				$esign->renderLog();
        				}
						?>
					</div>
				</td>
			</tr>
		<?php
		$divnos=$divnos+1;
		}
	}
	*/

	/*
	public static function lbf_form_action_btn() {
		global $GLOBALS, $newordermode, $rto, $cnt, $pid;

		if($newordermode !== true) {
			return;
		}

		$order_action = isset($_REQUEST['rto_action_'.$cnt]) ? $_REQUEST['rto_action_'.$cnt] : "";

		if(empty($order_action)) {
			$order_action = isset($rto['rto_action']) ? $rto['rto_action'] : '';
		}

		$rto_id = isset($rto['id']) ? $rto['id'] : '';

		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;


		$layoutData = self::getLayoutForm($order_action);
		$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';

		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && empty($rtoData)) {
			$lformname = $layoutData['grp_form_id'];
			$url = "../../../interface/forms/LBF/order_new.php?formname=".$lformname."&visitid=".$rto_id."&id=".$form_id."&submod=true";
			?>
				<button type="button" class="css_button_small lbfbtn" onClick="open_ldf_form('<?php echo $pid; ?>', '<?php echo $lformname; ?>', '<?php echo $rto_id; ?>', '<?php echo $form_id; ?>','<?php echo $form_title; ?>')">Enter details</button>
			<?php
		}

		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
			echo "&nbsp;". xl('Summary','e').":";
		}
	}
	*/

	/*
	public static function lbf_new_form_action_btn() {
		global $GLOBALS, $newordermode, $dt, $pid;

		if($newordermode !== true) {
			return;
		}

		$order_action = isset($_REQUEST['rto_action']) ? $_REQUEST['rto_action'] : "";

		if(empty($order_action)) {
			$order_action = isset($dt['rto_action']) ? $dt['rto_action'] : '';
		}

		$rto_id = isset($rto['id']) ? $rto['id'] : '';

		self::getLBFActionBtn($order_action, $rto_id, $pid);
	}
	*/

	/*
	public static function getLBFActionBtn($order_action, $rto_id, $pid) {
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;


		$layoutData = self::getLayoutForm($order_action);
		$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';

		if(!empty($order_action) && !empty($layoutData) && !empty($layoutData['grp_form_id']) && empty($rtoData)) {
			$lformname = $layoutData['grp_form_id'];
			$url = "../../../interface/forms/LBF/order_new.php?formname=".$lformname."&visitid=".$rto_id."&id=".$form_id."&submod=true";
			?>
				<button type="button" class="css_button_small lbfbtn" onClick="saveRtoData('<?php echo $lformname; ?>', '<?php echo $form_title; ?>')">Save Order & Enter details</button>
			<?php
		}

		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
			echo "&nbsp;". xl('Summary','e').":";
		}
	}*/

	/*
	public static function lbf_form_notes() {
		global $GLOBALS, $newordermode, $rto, $cnt, $pid;

		if($newordermode !== true) {
			return;
		}

		$order_action = isset($_REQUEST['rto_action_'.$cnt]) ? $_REQUEST['rto_action_'.$cnt] : "";

		if(empty($order_action)) {
			$order_action = isset($rto['rto_action']) ? $rto['rto_action'] : '';
		}

		$rto_id = isset($rto['id']) ? $rto['id'] : '';

		self::getLBFFormData($order_action, $rto_id, $pid);
	}*/

	/*
	public static function getLBFFormData($order_action, $rto_id, $pid) {
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;


		$layoutData = self::getLayoutForm($order_action);
		$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
		$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';

		$formData = self::getLbfFromData($form_id);

		// $oe_summary_val = '--';
		// foreach ($formData as $key => $field) {
		// 	if(isset($field['field_id']) && $field['field_id'] == "oe_summary") {
		// 		$oe_summary_val = isset($field['field_value']) ? $field['field_value'] : "";
		// 	}
		// }
		
		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
			$lformname = $layoutData['grp_form_id'];
			
			ob_start();
			// Use the form's report.php for display.  Forms with names starting with LBF
			// are list-based forms sharing a single collection of code.
			//
			if (substr($form_dir, 0, 3) == 'LBF') {
				include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

				call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
			} else {
				include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
				call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
			} 
			$summeryOutput = ob_get_clean();

			?>
			<div>
				<div class="summeryContainer" data-toggle="tooltip" title="">
					<?php echo $summeryOutput; ?>
				</div>
			</div>

			<button type="button" class="css_button_small lbfbtn" onClick="open_ldf_form('<?php echo $pid; ?>', '<?php echo $lformname; ?>', '<?php echo $rto_id; ?>', '<?php echo $form_id; ?>','<?php echo $form_title; ?>')">Read More</button>
			<br/>
			<br/>
			<?php
		}

		if(!empty($rto_id) && 1 != 1) {
		?>
		<button type="button" class="css_button_small lbfbtn lbfviewlogs" onClick="open_view_logs('<?php echo $pid; ?>', '<?php echo $rto_id; ?>')">View logs</button>
		<?php
		}
	}*/

	/*
	function getRTOSummary($rto_id, $pid, $rto_data = array()) {
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
		$order_action = !empty($rto_data['rto_action']) ? $rto_data['rto_action'] : '';

		$layoutData = self::getLayoutForm($order_action);
		$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
		$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';

		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
			$lformname = $layoutData['grp_form_id'];
			?>
			<div class="summeryContainer">
			<?php
				// Use the form's report.php for display.  Forms with names starting with LBF
				// are list-based forms sharing a single collection of code.
				//
				if (substr($form_dir, 0, 3) == 'LBF') {
					include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

					call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
				} else {
					include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
					call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
				}
			?>
			</div>
			<?php
		} else {
			echo htmlspecialchars($rto_data['rto_notes'],ENT_QUOTES);
		}

	}*/

	/*
	function getImagingOrdersSummary($rto_id, $pid, $rto_data = array()) {
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
		$order_action = !empty($rto_data['rto_action']) ? $rto_data['rto_action'] : '';

		$layoutData = self::getLayoutForm($order_action);
		$form_title = isset($layoutData['grp_title']) ? $layoutData['grp_title'] : '';
		$form_dir = isset($layoutData['grp_form_id']) ? $layoutData['grp_form_id'] : '';
		?>
		<div class='rto_note_container'>
		<input type="checkbox" class="read-more-state" id="order-note-<?php echo $rto_id; ?>" />
		
		<?php
		if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
			$lformname = $layoutData['grp_form_id'];
			?>
			<div class="content summeryContainer" data-toggle="tooltip" title="">
			<?php
				// Use the form's report.php for display.  Forms with names starting with LBF
				// are list-based forms sharing a single collection of code.
				//
				if (substr($form_dir, 0, 3) == 'LBF') {
					include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

					call_user_func("lbf_report", $pid, '', 2, $form_id, $form_dir, true);
				} else {
					include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
					call_user_func($form_dir . "_report", $pid, '', 2, $form_id);
				}
			?>
			</div>
			<?php
		} else {
			?>
			<div class="content summeryNoteContainer" data-toggle='tooltip' title=''>
				<div><?php echo htmlspecialchars($rto_data['rto_notes'],ENT_QUOTES); ?></div>
			</div>
			<?php
		}

		?>	
			<div class="actBtn">
				<label for="order-note-<?php echo $rto_id; ?>" class="readmore css_button" role="button">Read More</label>
				<label for="order-note-<?php echo $rto_id; ?>" class="lessmore css_button" role="button" >Read Less</label>
			</div>
		</div>
		<?php

	}*/

	/*
	public static function lbf_form_date() {
		global $GLOBALS, $newordermode, $rto, $cnt, $pid;

		if($newordermode !== true) {
			return;
		}

		?>
		<input name='rto_date1_<?php echo $cnt; ?>' id='rto_date1_<?php echo $cnt; ?>' class='wmtInput wmtFInput' type='text' readonly style='width: 85px;' value="<?php echo oeFormatShortDate($rto['date']); ?>" />
		<?php
	}*/

	/*
	public static function lbf_form() {
		global $GLOBALS, $newordermode, $rto, $cnt, $pid;

		return false;

		if($newordermode !== true) {
			return;
		}

		$order_action = isset($_REQUEST['rto_action_'.$cnt]) ? $_REQUEST['rto_action_'.$cnt] : "";

		if(empty($order_action)) {
			$order_action = isset($rto['rto_action']) ? $rto['rto_action'] : '';
		}

		$rto_id = isset($rto['id']) ? $rto['id'] : '';

		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;


		$layoutData = self::getLayoutForm($order_action);

		if(!empty($layoutData) && !empty($layoutData['grp_form_id'])) {
			$lformname = $layoutData['grp_form_id'];
			$url = "../../../interface/forms/LBF/order_new.php?formname=".$lformname."&visitid=".$rto_id."&id=".$form_id."&submod=true";

			?>
				<!-- <tr>
				<td colspan="6"> -->
				<!-- <iframe id="<?php //echo 'lbf_iframe_'.$cnt; ?>" class="lbf_form_iframe"  frameborder="0" scrolling="no" onload="resizeIframe(this)">
				</iframe> -->
				<script type="text/javascript">
					// var iframe = $( "#<?php //echo 'lbf_iframe_'.$cnt; ?>" );
					// iframe.attr("src","<?php //echo $url; ?>");
					// iframe.attr("src", iframe.data("src")); 
					//$( "#<?php //echo 'lbf_form_'.$cnt; ?>" ).load('<?php //echo $url; ?>', loadParam);
				</script>
				<!-- </td>
				</tr> -->
			<?php
		}
	}*/

	/*
	public static function getLbfFromData($form_id) {
		$result = sqlStatement("SELECT * FROM lbf_data WHERE form_id = ? ", array($form_id));
		$data = array();
		while ($row = sqlFetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}*/

	/*
	public static function getLayoutForm($rto_action) {
		$row = sqlQuery("SELECT * FROM layout_group_properties AS gp WHERE gp.grp_rto_action = ? LIMIT 1", array($rto_action));
		return $row;
	}*/

	public static function getRtoLayoutFormData($pid, $rto_id) {
		$row = sqlQuery("SELECT * FROM form_order_layout AS fol WHERE fol.pid = ? AND fol.rto_id = ? LIMIT 1", array($pid, $rto_id));
		return $row;
	}

	/*
	public static function getAllRtoLayoutFormData($pid) {
		$result = sqlStatement("SELECT * FROM form_order_layout AS fol WHERE fol.pid = ? ", array($pid));

		$data = array();
		while ($row = sqlFetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}*/

	/*
	public static function getRtoData($field_id = '') {
		$result = sqlStatement("SELECT fr.*, fol.form_id AS form_id, fol.formdir, fol.form_name, ld.field_id, gp.grp_rto_action AS grp_rto_action, gp.grp_title AS grp_title, gp.grp_form_id AS grp_form_id FROM form_rto AS fr LEFT JOIN form_order_layout AS fol ON fol.pid = fr.pid AND fol.rto_id = fr.id LEFT JOIN lbf_data AS ld ON ld.form_id = fol.form_id AND fol.form_id IS NOT NULL AND ld.field_id = ? LEFT JOIN layout_group_properties AS gp ON gp.grp_rto_action = fr.rto_action WHERE gp.grp_rto_action IS NOT NULL AND (fr.rto_notes != '') AND (ld.field_id IS NULL OR ( ld.field_id IS NOT NULL AND (ld.field_value = '' OR ld.field_value IS NULL )))", array($field_id));
		$data = array();
		while ($row = sqlFetchArray($result)) {
			$data[] = $row;
		}
		return $data;
	}*/

	/*
	public static function addRtoForm(
	    $rto_id,
	    $form_name,
	    $form_id,
	    $formdir,
	    $pid,
	    $authorized = "0",
	    $date = "NOW()",
	    $user = "",
	    $group = "",
	    $therapy_group = 'not_given'
	) {

	    global $attendant_type;
	    if (!$user) {
	        $user = $_SESSION['authUser'];
	    }

	    if (!$group) {
	        $group = $_SESSION['authProvider'];
	    }

	    if ($therapy_group == 'not_given') {
	        $therapy_group = $attendant_type == 'pid' ? null : $_SESSION['therapy_group'];
	    }

	    //print_r($_SESSION['therapy_group']);die;
	        $arraySqlBind = array();
	    $sql = "insert into form_order_layout (date, rto_id, form_name, form_id, pid, " .
	        "user, groupname, authorized, formdir, therapy_group_id) values (";
	    if ($date == "NOW()") {
	        $sql .= "$date";
	    } else {
	        $sql .= "?";
	                array_push($arraySqlBind, $date);
	    }

	    $sql .= ", ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	        array_push($arraySqlBind, $rto_id, $form_name, $form_id, $pid, $user, $group, $authorized, $formdir, $therapy_group);
	    return sqlInsert($sql, $arraySqlBind);
	}*/

	/*
	public static function saveLbfNoteValue($form_id, $form_field, $note, $update = false) {
		if($update === false) {
			$sql = "INSERT INTO `lbf_data`( form_id, field_id, field_value ) VALUES (?, ?, ?) ";
			return sqlInsert($sql, array(
				$form_id,
				$form_field,
				$note
			));
		} else {
			return sqlStatement("UPDATE lbf_data SET field_value = ? WHERE form_id = ? AND 	field_id = ?", array($note, $form_id, $form_field));
		}
	}*/

	/*
	function addMessageOrderLog($pid, $type = '', $orderList = array(), $msgLogId = '', $to = '') {
		if(!empty($orderList)) {
			foreach ($orderList as $oi => $orderItem) {
				$orderId = isset($orderItem['order_id']) ? $orderItem['order_id'] : "";
				if(!empty($orderId)) {
					$type = $type;
					$createdBy = $_SESSION['authUserID'];
					$relation_id = isset($msgLogId) && !empty($msgLogId) ? $msgLogId : NULL;
					$operationType = 'Sent';

					self::saveOrderLog($type, $orderId, $relation_id, $to, $pid, $operationType, $createdBy);
				}
			}
		}
	}*/

	/*
	function add_email_message_order_log($pid) {
		global $msgLogId, $email_data;

		$baseDocList = isset($_REQUEST['baseDocList']) && !empty($_REQUEST['baseDocList']) ? json_decode($_REQUEST['baseDocList'], true) : array();
		$orderList = isset($baseDocList['selectedOrder']) ? $baseDocList['selectedOrder'] : array();

		if(!empty($orderList)) {
			foreach ($orderList as $oi => $orderItem) {
				$orderId = isset($orderItem['id']) ? $orderItem['id'] : "";
				if(!empty($orderId)) {
					$type = "EMAIL";
					$createdBy = $_SESSION['authUserID'];
					$relation_id = isset($msgLogId) && !empty($msgLogId) ? $msgLogId : NULL;
					$operationType = 'Sent';

					self::saveOrderLog($type, $orderId, $relation_id, $email_data['email'], $pid, $operationType, $createdBy);
				}
			}
		}
	}*/

	/*
	function add_fax_message_order_log($pid) {
		global $msgLogId, $fax_data;

		$baseDocList = isset($_REQUEST['baseDocList']) && !empty($_REQUEST['baseDocList']) ? json_decode($_REQUEST['baseDocList'], true) : array();
		$orderList = isset($baseDocList['selectedOrder']) ? $baseDocList['selectedOrder'] : array();

		if(!empty($orderList)) {
			foreach ($orderList as $oi => $orderItem) {
				$orderId = isset($orderItem['id']) ? $orderItem['id'] : "";
				if(!empty($orderId)) {
					$type = "FAX";
					$createdBy = $_SESSION['authUserID'];
					$relation_id = isset($msgLogId) && !empty($msgLogId) ? $msgLogId : NULL;
					$operationType = 'Sent';

					self::saveOrderLog($type, $orderId, $relation_id, $fax_data['fax_number'], $pid, $operationType, $createdBy);
				}
			}
		}
	}*/

	/*
	function add_order_log($pid) {
		global $noteId, $dt, $test;

		$type = "INTERNAL_NOTE";
		$assined_to_user =  $dt['rto_resp_user'];
		$createdBy = $_SESSION['authUserID'];
		$relation_id = isset($noteId) && !empty($noteId) ? $noteId : NULL;
		$operationType = 'Created';

		self::saveOrderLog($type, $test, $relation_id, NULL, $pid, $operationType, $createdBy);
	}*/

	/*
	public static function add_reminder_log($pid) {
		global $dt, $cnt, $noteId;

		$rto_id = isset($dt['rto_id_'.$cnt]) ? $dt['rto_id_'.$cnt] : '';
		$rto_resp = isset($dt['rto_resp_'.$cnt]) ? $dt['rto_resp_'.$cnt] : '';

		$type = "INTERNAL_NOTE";
		$assined_to_user =  $rto_resp;
		$createdBy = $_SESSION['authUserID'];
		$relation_id = isset($noteId) && !empty($noteId) ? $noteId : NULL;
		$operationType = 'Reminder';

		self::saveOrderLog($type, $rto_id, $relation_id, NULL, $pid, $operationType, $createdBy);
	}*/

	/*
	function add_internal_note($pid) {
		global $newnoteid;

		if(!empty($newnoteid)) {
			$internalData = self::getInternalNote($newnoteid);

			if(!empty($internalData)) {
				$type = "INTERNAL_NOTE";
				$createdBy = $_SESSION['authUserID'];
				$relation_id = isset($newnoteid) && !empty($newnoteid) ? $newnoteid : NULL;
				$operationType = 'Forwarded';
				$orderId = isset($internalData['rto_id']) ? $internalData['rto_id'] : NULL;

				self::saveOrderLog($type, $orderId, $relation_id, NULL, $pid, $operationType, $createdBy);
			}
		}
	}*/

	/*
	public static function addMessageNote($pid) {
		global $noteid;

		if(!empty($noteid)) {
			$internalData = self::getInternalNote($noteid);

			if(!empty($internalData)) {
				$type = "INTERNAL_NOTE";
				$createdBy = $_SESSION['authUserID'];
				$relation_id = isset($noteid) && !empty($noteid) ? $noteid : NULL;
				$operationType = 'Forwarded';
				$orderId = isset($internalData['rto_id']) ? $internalData['rto_id'] : NULL;

				self::saveOrderLog($type, $orderId, $relation_id, NULL, $pid, $operationType, $createdBy);
			}
		}
	}*/

	/*
	public static function saveOrderLog($type = '', $rtoId = '', $relationId = '', $sentTo = '', $pid = '', $operation = '', $createdBy = '') {
		$sql = "INSERT INTO `rto_action_logs` ( type, rto_id, foreign_id, sent_to, pid, operation, created_by ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
		$responce = sqlInsert($sql, array(
			$type,
			$rtoId,
			$relationId,
			$sentTo,
			$pid,
			$operation,
			$createdBy
		));

		return $responce;
	}*/

	/*
	public static function getInternalNote($noteId) {
		$result = sqlQuery("SELECT * FROM `rto_action_logs` WHERE type = ? AND foreign_id = ? ORDER BY created_date DESC LIMIT 1", array("INTERNAL_NOTE", $noteId));
		return $result;
	}*/

	/*
	public static function fetchRtoLogs($rto_id, $pid = '') {
		$typeList = array(
			'INTERNAL_NOTE' => 'Internal Note',
			'EMAIL' => 'Email',
			'FAX' => 'Fax'
		); 

		if(!empty($rto_id)) {
			$result = sqlStatement("SELECT rl.*, u.username as user_name, u.fname as u_fname, u.mname as u_mname, u.lname as u_lname, pd.fname AS patient_data_fname, pd.lname AS patient_data_lname FROM rto_action_logs rl LEFT JOIN users As u ON u.id = rl.created_by LEFT JOIN patient_data as pd ON rl.pid = pd.pid WHERE rto_id = ? ", array($rto_id));

			$data = array();
			while ($row = sqlFetchArray($result)) {

				$patientName = htmlspecialchars($row['patient_data_fname'] . ' ' . $row['patient_data_lname']);

				$name = $row['u_lname'];
	            if ($row['u_fname']) {
	                $name .= ", " . $row['u_fname'];
	            }

	            $sentToTitle = '';

	            if($row['type'] == "INTERNAL_NOTE") {
	            	$sentToTitle = $patientName;
	            } else {
	            	$sentToTitle = $row['sent_to'];
	            }

	            $row['user_fullname'] = $name;
	            $row['patient_name'] = $patientName;
	            $row['sent_to_title'] = $sentToTitle;
	            $row['type_title'] = isset($typeList[$row['type']]) ? $typeList[$row['type']] : "";

				$data[] = $row;
			}
			return $data;
		}

		return false;
	}*/

	/*
	public static function getMessageData($msgId) {
		$result = sqlQuery("SELECT * FROM `message_log` WHERE id = ? LIMIT 1", array($msgId));
		return $result;
	}*/

	/*
	public static function getInternalMsgData($noteId) {
		$result = sqlQuery("SELECT * FROM `pnotes` WHERE id = ? LIMIT 1", array($noteId));
		return $result;
	}*/

	/*
	function getRtoLbfData($rto_id, $pid) {
		$rtoData = self::getRtoLayoutFormData($pid, $rto_id);
		$form_id = isset($rtoData['form_id']) ? $rtoData['form_id'] : 0;
		$formData = self::getLbfFromData($form_id);

		$fieldData = array();
		foreach ($formData as $fk => $field) {
			$fieldData[$field['field_id']] = $field['field_value'];
		}
		return $fieldData;
	}*/

	/*
	function getLayoutFormFields($form_id) {
		$result = sqlStatement("SELECT lo.field_id, lo.title, lo.list_id, lgp.* from layout_group_properties lgp left join layout_options lo on lo.form_id = lgp.grp_form_id WHERE lgp.grp_form_id = ? and lgp.grp_group_id = '' ", array($form_id));

		$items = array();
		while ($row = sqlFetchArray($result)) {
			$items[] = $row;
		}

		return $items;
	}*/

	/*
	function getFormFieldByTitle($fields = array(), $field_id = '') {
		$field = array();

		if(!empty($fields) && !empty($field_id)) {
			foreach ($fields as $fk => $fItem) {
				if(isset($fItem['title']) && $fItem['title'] == $field_id) {
					$field = $fItem;
				}
			}
		}

		return $field;
 	}*/
}