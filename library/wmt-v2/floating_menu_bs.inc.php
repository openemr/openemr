<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
if(!isset($relink_list)) $relink_list = '';
if(!isset($encounter)) $encounter = '';
if(!isset($relink_list)) $relink_list = "'do_all', 'do_meds'";
if(!isset($growth_data)) $growth_data = '';
if(!isset($weight_field)) $weight_field = 'vitals_weight';
if(!isset($height_field)) $height_field = 'vitals_height';
if(!isset($stamp_field)) $stamp_field = 'vitals_timestamp';
$lab_enabled = false;
if(isset($GLOBALS['wmt_lab_enable'])) $lab_enabled = $GLOBALS['wmt_lab_enable'];
$portal_enabled = false;
if(isset($GLOBALS['wmt::pat_entry_portal'])) $portal_enabled = $GLOBALS['wmt::pat_entry_portal'];
$actions = LoadList($frmdir.'_actions','active','seq','','AND UPPER(notes) LIKE "%MENU%"');
?>

<nav class="navbar navbar-expand-sm sticky-top navbar-light bg-light border-bottom border-left border-right mx-0" style="background-color: #e3f2fd;">
	<ul class="nav justify-content-end pt-0 pb-0">
		<?php if(!checkSettingMode('wmt::float_menu_continue_suppress','',$frmdir)) { ?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary" id="save_and_continue" href="javascript:;" tabindex="-1" onclick="AutoSave('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>', 'save_and_continue');"><?php echo xl('S &amp; C'); ?></a>
		</li>
		<?php } ?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary ml-1" href="javascript:;" tabidex="-1" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 1000, 600);"><?php echo xl('Docs'); ?></a>
		</li>

		<?php if($portal_enabled) { ?>
		<?php if(checkSettingMode('wmt::float_menu_portal_link','',$frmdir)) { ?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary ml-1 floating-menu-link" href="javascript:;" id="btn_hide" tabindex="-1"><?php echo xl('Portal Data'); ?></a>
		</li>
		<?php } ?>
		<?php } ?>

		<?php if($lab_enabled) { ?>
		<?php if(checkSettingMode('wmt::float_menu_lab_link','',$frmdir)) { ?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary ml-1" href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/reports/laboratory/lab_analysis.php?popup=1&pid=<?php echo $pid; ?>', '_blank', 900, 800);" ><?php echo xl('Lab Analysis'); ?></a>
		</li>
		<?php } ?>
		<?php } ?>

		<!-- KEEP THIS ONE AS A QUICK WAY TO SET UP ONE OPTION -->
		<?php 
		if($rto_type = checkSettingMode('wmt::float_menu_quick_rto','',$frmdir)) {
			$title = 'Create Order';
			$type = $rto_type;
			if(strpos($rto_type, '::') !== false) {
				list($type, $title) = explode('::', $rto_type);
			}	
		?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary ml-1" href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=<?php echo $type; ?>&pid=<?php echo $pid; ?>', '_blank', 1200, 400);" ><?php echo $title; ?></a>
		</li>
		<?php } ?>

		<!-- IF WE HAVE TASKS/ACTIONS DEFINED AS QUICK LINKS BUILD THAT LIST -->
		<?php if(count($actions)) { ?>
		<li class="nav-item">
			<div class="dropdown">
				<a class="btn btn-sm btn-primary ml-1 dropdown-toggle" role="button" data-toggle="dropdown" aria-expanded="false" href="javascript:;" tabindex="-1" onclick="linkOpen('actions');" ><?php echo xl('Tasks / Orders'); ?></a>
				<div class="dropdown-menu">
						<?php foreach($actions as $action) { ?>
						<a class="dropdown-item" href="javascript:;" onclick="createOrder('<?php echo $frmdir.'_actions'; ?>','<?php echo $action['option_id']; ?>');"><?php echo htmlspecialchars($action['title'],ENT_QUOTES,'',FALSE); ?></a>
						<?php } ?>
						<a class="dropdown-item" href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid=<?php echo $pid; ?>', '_blank', 1200, 400);"><?php echo xl('Review All'); ?></a>
				</div>
			</div>
		</li>
		<?php } else { ?>
		<li class="nav-item">
			<a class="btn btn-sm btn-primary ml-1" href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid=<?php echo $pid; ?>', '_blank', 1200, 400);" ><?php echo xl('Orders'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_summary_link','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','summary');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"
			<?php if(checkSettingMode('wmt::auto_check_summary_amc','',$frmdir)) { ?> onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/custom/amc_auto_check.php?pid=<?php echo $pid; ?>&enc=<?php echo $encounter; ?>&form=<?php echo $frmdir; ?>','_blank',400,400);"
			<?php } ?>><?php echo xl('Summary'); ?></a>
		</li>
		<?php } ?>


		<?php if(!checkSettingMode('wmt::float_menu_suppress_print','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"
			<?php if(checkSettingMode('wmt::auto_check_summary_amc','',$frmdir)) { ?>
				onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/custom/amc_auto_check.php?pid=<?php echo $pid; ?>&enc=<?php echo $encounter; ?>','_blank',400,400);"
			<?php } ?>><?php echo xl('Print'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_eRx','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/eRx.php?page=medentry', '_blank', '80%', '90%');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('eRx'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_problem_link','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','problems');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Print Problems'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_vital_trend','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/trend_vitals.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Trend Vitals'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_growth_chart_pdf','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="popGrowthChart('pdf','<?php echo $growth_data; ?>', '<?php echo date('Y-m-d'); ?>', '<?php echo $stamp_field; ?>', '<?php echo $weight_field; ?>', '<?php echo $height_field; ?>', '<?php echo $pid; ?>', '<?php echo $GLOBALS['webroot']; ?>');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Growth (Pdf)'); ?></a>
		</li>
		<?php } ?>
		<?php if(checkSettingMode('wmt::float_menu_growth_chart_html','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="popGrowthChart('html','<?php echo $growth_data; ?>', '<?php echo date('Y-m-d'); ?>', '<?php echo $stamp_field; ?>', '<?php echo $weight_field; ?>', '<?php echo $height_field; ?>', '<?php echo $pid; ?>', '<?php echo $GLOBALS['webroot']; ?>');" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Growth (Html)'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_sugar_trend','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/trend_sugar.php?pid=<?php echo $pid; ?>', '_blank', 1100, 600);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Blood Sugar Graph'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_quick_pick','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/quick/new.php?pid=<?php echo $pid; ?>&enc=<?php echo $encounter; ?>', '_blank', 800, 600);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Quick Pick'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_relink','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript: refresh_links(<?php echo $relink_list; ?>);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Re-Link'); ?></a>
		</li>
		<?php } ?>

		<?php if($clear = checkSettingMode('wmt::float_menu_clear','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript: <?php echo $clear; ?>;" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php xl('Clear','e'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_appointment','',$frmdir)) { ?>
		<li class="nav-item">
			<a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/add_edit_event.php?patientid=<?php echo $pid; ?>', '_blank', 550, 500);" class="btn btn-sm btn-primary ml-1" ><?php xl('Appt','e'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_cancel','',$frmdir)) {
		$target = $href = '';
		if ($pop_form) {
			$href = 'javascript: window.close();';
		} else if($frmdir == 'dashboard') {
			$href = $GLOBALS['rootdir'] . "/patient_file/summary/demographics.php";
			if(!$GLOBALS['concurrent_layout']) $target = 'target="Main"';
		} else {
			$href = $GLOBALS['form_exit_url']; 
		}
		?>
		<li class="nav-item">
			<a href="<?php echo $href; ?>" class="btn btn-sm btn-primary ml-1" onclick="return cancelClicked();" <?php echo $target; ?> ><?php xl('Cancel','e'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_coding','',$frmdir) && IsDoctor()) { ?>
		<li class="nav-item">
			<a href="javascript:;" class="btn btn-sm btn-primary ml-1" onclick="wmtOpen('<?php echo $GLOBALS['rootdir']; ?>/forms/popup_coding/new.php?caller=<?php echo $frmdir; ?>', '_blank', 350, 600);" ><?php xl('E &amp; M','e'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_procedures','',$frmdir) && IsDoctor()) { ?>
		<li class="nav-item">
			<a href="javascript:;" class="btn btn-sm btn-primary ml-1" onclick="wmtOpen('<?php echo $GLOBALS['rootdir']; ?>/forms/procedures/new.php?caller=<?php echo $frmdir; ?>', '_blank', 950, 500);" ><?php xl('Procedures','e'); ?></a>
		</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_save_quit','',$frmdir)) { ?>
	  	<li class="nav-item">
	  		<a href="javascript: document.forms[0].submit();" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Save &amp; Quit'); ?></a>
	  	</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_msg','',$frmdir)) { ?>
	  <?php 
		$msg_href = $GLOBALS['rootdir'] . '/main/messages/add_edit_message.php' .
			"?mode=addnew&reply_to=$pid&enc=$encounter&frmdir=$frmdir&fid=$id";
		?>
	  	<li class="nav-item">
	  		<a href="javascript:;" onclick="wmtOpen('<?php echo $msg_href; ?>','_blank',700,300);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('New Msg'); ?></a>
	  	</li>
		<?php } ?>

		<?php if(checkSettingMode('wmt::float_menu_reminders','',$frmdir)) { ?>
	  <?php 
		$href = $GLOBALS['rootdir'].'/patient_file/reminder/clinical_reminders.php' .
			"?patient_id=$pid&pop=pop";
		?>
	  	<li class="nav-item">
	  		<a href="javascript:;" onclick="wmtOpen('<?php echo $href; ?>','_blank',600,500);" tabindex="-1" class="btn btn-sm btn-primary ml-1 floating-menu-link"><?php echo xl('Reminders'); ?></a>
	  	</li>
		<?php } ?>

	</ul>
</nav>

<script type="text/javascript">
// THIS IS FOR THE PORTAL DADA DISPLAY BUTTON FUNTTIONALITY
$( "#btn_hide" ).click( function() {
	if($( ".wmtPortalData" ).css('display') == 'none') {
		$( ".wmtPortalData" ).show();
		$( "#tmp_show_portal_data" ).val('yes');
	} else {
		$( ".wmtPortalData" ).hide();
		$( "#tmp_show_portal_data" ).val('no');
	}
} );

//  THIS IS FOR THE LINK DROP DOWN OPTIONS
var timeout	= 500;
var closetimer	= 0;
var link	= 0;
var oldlink = 0;
var flag = 0;

function linkOpen(id)
{
	flag=10;

	oldlink = link;
	link = document.getElementById(id);
	if((link.style.visibility == '') || (link.style.visibility == 'hidden')) {
		if(oldlink) oldlink.style.visibility = 'hidden';
		if(oldlink) oldlink.style.display = 'none';
		link.style.visibility = 'visible';
		link.style.display = 'block';
	} else {
		link.style.visibility = 'hidden';
		link.style.display = 'none';
	}
}

function linkClose()
{
	if(flag == 10) {
		flag=11;
		return;
	}
	if(link) link.style.visibility = 'hidden';
	if(link) link.style.display = 'none';
}

// CLOSE THE LINK LAYER WHEN CLICK OUTSIDE
document.onclick = linkClose;
//=================================================

// AJAX FOR LIVE LINK OPTIONS
function createOrder(list, option) {
	var output = 'error';
	if(!list) {
		alert('ERROR - No Action List Specified, Please Notify Support');
		return false;
	}
	if(!option) {
		alert('ERROR - Empty Option Link, Please Notify Support');
		return false;
	}
	
	$.ajax({
		type: "POST",
		url: "<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/auto_order.ajax.php",
		data: {
			list: list,
			id: option
		},
		success: function(result) {
			if(result['error']) {
				output = false;
				alert('That Task Could NOT Be Created\n'+result['error']);
			} else {
				output = result;
				document.getElementById('task-create-notification').style.display='block';
				DelayedHideDiv('task-create-notification',2000);
			}
		},
		async: true
	});
	return output;
}

</script>