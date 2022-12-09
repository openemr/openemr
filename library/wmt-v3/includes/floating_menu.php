<?php
/** **************************************************************************
 *	FLOATING_MENU.PHP
 *
 *	Copyright (c)2017 - Medical Technology Services
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package pediatrics
 *  @subpackage wcc
 *  @version 3.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@mdtechsvcs.com>
 * 
 *************************************************************************** */

include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');

if(!isset($relink_list)) $relink_list = '';
if(!isset($relink_list)) $relink_list = "'do_all', 'do_meds'";

$lab_enabled = false;
if(isset($GLOBALS['wmt_lab_enable'])) $lab_enabled = $GLOBALS['wmt_lab_enable'];

$portal_enabled = false;
if(isset($GLOBALS['wmt::pat_entry_portal'])) $portal_enabled = $GLOBALS['wmt::pat_entry_portal'];

$actions = LoadList($frmdir.'_actions','active','seq','','AND UPPER(notes) LIKE "%MENU%"');
?>

<style type="text/css">
	dl.pedi { position:fixed; top:0; width:100%; z-index:50; vertical-align:top; margin:0 auto; background: #2672ec; }
	dl.pedi table { margin:0 auto; text-align:center; border-collapse:collapse; } 
	dl.pedi #sddm { margin: 0 auto; padding: 0; z-index: 30; }
	dl.pedi #sddm li { margin-left:12px; background:  #2672ec; }
	dl.pedi #sddm li:hover { margin-left:12px; background: #c9dbf2; }
	dl.pedi #sddm li a { color: #fff; }
	dl.pedi #sddm li a:hover { color: #000; background: #c9dbf2; }
	dl.pedi #sddm div a { color: #000; }
	dl.pedi #sddm div a:hover { color: #fff; background: #2672ec; }
</style>

<dl class='pedi'>
	<table style="">
		<tr><td style="padding:0 4px;vertical-align:top">
			<ul id="sddm">
<?php if (!checkSettingMode('wmt::float_menu_continue_suppress','',$frmdir)) { ?>
				<li><a href="javascript:;" tabindex="-1" id="SCont" onclick="ajaxSave('save');" >Save &amp; Continue</a></li>
<?php } ?>
				<li><a href="javascript:;" tabidex="-1" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);" >Documents</a></li>
<?php if ($portal_enabled) {
		if(checkSettingMode('wmt::float_menu_portal_link','',$frmdir)) { ?>
				<li><a href="javascript:;" id="btn_hide" tabindex="-1" class="floating-menu-link">Portal Data</a></li>
<?php }	} ?>
<?php if ($lab_enabled) {
		if(checkSettingMode('wmt::float_menu_lab_link','',$frmdir)) { ?>
				<li><a href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/reports/laboratory/lab_analysis.php?popup=1&pid=<?php echo $pid; ?>', '_blank', 900, 800);" >Lab Analysis</a></li>
<?php }	} ?>
	<!-- KEEP THIS ONE AS A QUICK WAY TO SET UP ONE OPTION -->
<?php if ($rto_type = checkSettingMode('wmt::float_menu_quick_rto','',$frmdir)) {
			$title = 'Create Order';
			$type = $rto_type;
			if (strpos($rto_type, '::') !== false) {
				list($type, $title) = explode('::', $rto_type);
			} ?>
				<li><a href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=<?php echo $type; ?>&pid=<?php echo $pid; ?>', '_blank', 1200, 400);" ><?php echo $title; ?></a></li>
<?php } ?>
	<!-- IF WE HAVE TASKS/ACTIONS DEFINED AS QUICK LINKS BUILD THAT LIST -->
<?php if (count($actions)) { ?>
				<li><a href="javascript:;" tabindex="-1" onclick="linkOpen('actions');" >Tasks/Orders</a>
					<div id="actions" style="display: none; width: 100%;">
						<table border="0" cellspacing="0" cellpadding="0" style="width: 100%;">
<?php 		foreach($actions as $action) { ?>
							<tr><td style="border-top: 1px solid #000000; padding: 0px;"><a href="javascript:;" onclick="createOrder('<?php echo $frmdir.'_actions'; ?>','<?php echo $action['option_id']; ?>');"><?php echo htmlspecialchars($action['title'],ENT_QUOTES,'',FALSE); ?></a></td></tr>
<?php 		} ?>
							<tr><td style="border-top: 1px solid #000000; padding: 0px;"><a href="javascript:;" tabindex="-1" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/rto/new.php?pop=yes&pid=<?php echo $pid; ?>', '_blank', 1200, 400);">Review All</a></td></tr>
						</table>
					</div>
				</li>
<?php } ?>
				<li><a href="javascript:;" tabindex="-1" onclick="linkOpen('prints');" >Printable</a>
					<div id="prints" style="display: none;">
						<table border="0" cellspacing="0" cellpadding="0";">
							<tr><td style="border-top: 1px solid #000000; padding: 0px;">
								<a href="javascript:" onclick="ajaxPrint('full');" tabindex="-1" class="floating-menu-link">Complete Form</a>
							</td></tr>
							<tr><td style="border-top: 1px solid #000000; padding: 0px;">
								<a href="javascript:" onclick="ajaxPrint('summary');"  tabindex="-1" class="floating-menu-link">Patient Summary</a>
							</td></tr>
							<tr><td style="border-top: 1px solid #000000; padding: 0px;">
								<a href="javascript:" onclick="ajaxPrint('agui');"  tabindex="-1" class="floating-menu-link">Guidance Sheet</a>
							</td></tr>
						</table>
					</div>
				</li>
<?php if (checkSettingMode('wmt::float_menu_vital_trend','',$frmdir)) { ?>
				<li><a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/trend_vitals.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);" tabindex="-1" class="floating-menu-link">Trend Vitals</a></li>
<?php } ?>
<?php if (checkSettingMode('wmt::float_menu_sugar_trend','',$frmdir)) { ?>
				<li><a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/trend_sugar.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);" tabindex="-1" class="floating-menu-link">Blood Sugar Graph</a></li>
<?php } ?>
<?php if (checkSettingMode('wmt::float_menu_quick_pick','',$frmdir)) { ?>
				<li><a href="javascript:; " onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/quick/new.php?pid=<?php echo $pid; ?>&enc=<?php echo $encounter; ?>&pop=1', '_blank', 1025, 600);" tabindex="-1" class="floating-menu-link">Quick Pick</a></li>
<?php } ?>
<?php if (checkSettingMode('wmt::float_menu_relink','',$frmdir)) { ?>
				<li><a href="javascript: refresh_links(<?php echo $relink_list; ?>);" tabindex="-1" class="floating-menu-link">Re-Link</a></li>
<?php } ?>
<?php if ($clear = checkSettingMode('wmt::float_menu_clear','',$frmdir)) { ?>
				<li><a href="javascript: <?php echo $clear; ?>;" tabindex="-1" class="floating-menu-link"><?php xl('Clear','e'); ?></a></li>
<?php } ?>
<?php if (true || checkSettingMode('wmt::float_menu_appointment','',$frmdir)) { ?>
				<li><a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/add_edit_event.php?patientid=<?php echo $pid; ?>', '_blank', 550, 500);" ><?php xl('Appointment','e'); ?></a></li>
<?php } ?>
<?php if (checkSettingMode('wmt::float_menu_cancel','',$frmdir)) { ?>
				<li><a href="<?php echo ($pop_form) ? 'javascript: window.close();' : $GLOBALS['form_exit_url']; ?>" onclick="return cancelClicked();" ><?php xl('Cancel','e'); ?></a></li>
<?php } ?>
<?php if (checkSettingMode('wmt::float_menu_coding','',$frmdir) && IsDoctor()) { ?>
				<li><a href="javascript:;" onclick="wmtOpen('<?php echo $GLOBALS['rootdir']; ?>/forms/popup_coding/new.php?caller=<?php echo $frmdir; ?>', '_blank', 550, 700);" ><?php xl('E&amp;M Coding','e'); ?></a></li>
<?php } ?>
<?php if (true || checkSettingMode('wmt::float_menu_save_quit','',$frmdir)) { ?>
				<li><a href="javascript: document.forms[0].submit();" tabindex="-1" class="floating-menu-link">Save &amp; Exit</a></li>
<?php } ?>
			</ul>
		</td></tr>
	</table>
</dl>

<script type="text/javascript">

// THIS IS FOR THE PORTAL DATA DISPLAY BUTTON FUNCTIONALITY
$( "#btn_hide" ).click( function() {
	if ($( ".wmtPortalData" ).css('display') == 'none') {
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

function linkOpen(id) {
	flag=10;

	oldlink = link;
	link = document.getElementById(id);
	if ((link.style.visibility == '') || (link.style.visibility == 'hidden')) {
		if (oldlink) oldlink.style.visibility = 'hidden';
		if (oldlink) oldlink.style.display = 'none';
		link.style.visibility = 'visible';
		link.style.display = 'block';
	} else {
		link.style.visibility = 'hidden';
		link.style.display = 'none';
	}
}

function linkClose() {
	if (flag == 10) {
		flag=11;
		return;
	}
	if (link) link.style.visibility = 'hidden';
	if (link) link.style.display = 'none';
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

