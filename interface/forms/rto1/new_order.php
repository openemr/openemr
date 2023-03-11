<?php

require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/api.inc");
//require_once("{$GLOBALS['srcdir']}/acl.inc");
require_once("{$GLOBALS['srcdir']}/calendar.inc");
require_once("{$GLOBALS['srcdir']}/pnotes.inc");
require_once("{$GLOBALS['srcdir']}/forms.inc");
require_once("{$GLOBALS['srcdir']}/translation.inc.php");
require_once("{$GLOBALS['srcdir']}/formatting.inc.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/rto.class.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmt.msg.inc");
include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : "";
$frmdir = isset($_REQUEST['frmdir']) ? $_REQUEST['frmdir'] : "";
$newordermode = isset($_REQUEST['newordermode']) ? true : false;
$rtoformname = isset($_REQUEST['rto_formname']) ? $_REQUEST['rto_formname'] : '';
$rtoformtitle = isset($_REQUEST['rto_form_title']) ? $_REQUEST['rto_form_title'] : '';
$rto_id = isset($_REQUEST['rto_id']) ? $_REQUEST['rto_id'] : "";
$popmode = isset($_REQUEST['pop']) ? $_REQUEST['pop'] : "no";

$default_settings = checkSettingMode('wmt::'.$popmode,'',$frmdir);
$defaults = array();
if($default_settings) $defaults = explode('::',$default_settings);

$mode = 'new';
if(isset($_REQUEST['mode'])) $mode = strip_tags($_REQUEST['mode']);


$dt = array();
$flds = sqlListFields('form_rto');
foreach($flds as $key => $fld) { $dt[$fld]=''; }
foreach($_POST as $key => $val) {
	$val = trim($val);
	$dt[$key] = $val;
	if(strpos($key, '_date') !== false) $dt[$key] = DateToYYYYMMDD($val);
}

if(count($defaults) > 0) $dt['rto_action'] = $defaults[0];
if(count($defaults) > 1) $dt['rto_status'] = $defaults[1];
if(count($defaults) > 2) $dt['rto_resp_user'] = $defaults[2];
if(count($defaults) > 3) $dt['rto_num'] = $defaults[3];
if(count($defaults) > 4) $dt['rto_frame'] = $defaults[4];
if(count($defaults) > 5) $dt['rto_notes'] = $defaults[5];

if(!isset($frmdir)) $frmdir = '';
if(!isset($rto_data)) $rto_data = array();
if(!isset($dt{'rto_action'})) $dt{'rto_action'} = '';
if(!isset($dt{'rto_ordered_by'})) $dt{'rto_ordered_by'} = '';
if(!isset($dt{'rto_status'})) $dt{'rto_status'} = '';
if(!isset($dt{'rto_resp_user'})) $dt{'rto_resp_user'} = '';
if(!isset($dt{'rto_notes'})) $dt{'rto_notes'} = '';
if(!isset($dt{'rto_target_date'})) $dt{'rto_target_date'} = '';
if(!isset($dt{'rto_num'})) $dt{'rto_num'} = '';
if(!isset($dt{'rto_frame'})) $dt{'rto_frame'} = '';
if(!isset($dt{'rto_date'})) $dt{'rto_date'} = '';
if(!isset($dt{'rto_stop_date'})) $dt{'rto_stop_date'} = '';
if(!isset($dt{'rto_repeat'})) $dt{'rto_repeat'} = '';
if($GLOBALS['date_display_format'] == 1) {
	$date_title_fmt = 'MM/DD/YYYY';
} else if($GLOBALS['date_display_format'] == 2) {
	$date_title_fmt = 'DD/MM/YYYY';
} else $date_title_fmt = 'YYYY-MM-DD';
$date_title_fmt = 'Please Format Date As '.$date_title_fmt;

$save_url = $rootdir.'/forms/rto1/new_order.php?pid='.$pid.'&frmdir='.$frmdir.'&newordermode='.$newordermode;

/* OEMR - Changes */
$dt['layout_form'] = false;
$hideClass = $newordermode == true ? 'hideContent' : '';
$showNewOrder = (!isset($newordermode) || $newordermode === false || ($newordermode === true && isset($rto_page_details['total_pages']) && ($rto_page_details['total_pages'] == 0 ||$rto_page_details['total_pages'] == $pageno))) ? true : false;

if($newordermode === true) {
	$order_action = isset($_REQUEST['rto_action']) ? $_REQUEST['rto_action'] : "";
	$layoutData_n = getLayoutForm($order_action);
	if(!empty($layoutData_n) && !empty($layoutData_n['grp_form_id'])) {
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

	$rtoData_n = getRtoLayoutFormData($pid, $rto_id);
	$form_id_n = isset($rtoData_n['form_id']) ? $rtoData_n['form_id'] : 0;
}
/* End */

if($mode == 'new') {
	$dt['rto_date'] = date('Y-m-d');
	$dt['rto_ordered_by'] = $_SESSION['authUser'];
} else if($mode == 'save' || $mode == 'rto' || $mode == 'new_rto_save') {
	if(empty($rto_id)) {
		include_once("rto_save.php");
		if($test && !empty($test)) {
			$rto_id = $test;
		}
	} else if(!empty($rto_id)) {
		//RTO Data
		$rto_data_bup = getRTObyId($pid, $rto_id);
		UpdateRTO($pid,$rto_id,$dt['rto_num'],
			$dt['rto_frame'],$dt['rto_status'],$dt['rto_notes'],
			$dt['rto_resp_user'],$dt['rto_action'],
			$dt['rto_date'],$dt['rto_target_date'],
			$dt['rto_ordered_by'],false,$dt['rto_repeat'],
			$dt['rto_stop_date'], $dt['rto_case'], $dt['rto_stat']);

		// OEMR - Change
		rtoBeforeSave($pid);

		unset($rto_data_bup);
	}
} 

if($mode == 'rto_close' || $mode == 'save') {
	?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Redirecting</title>
        <?php Header::setupHeader(['opener', 'common', 'datetime-picker', 'jquery-ui',]); ?>
        <script type="text/javascript">
            function closePopup(pid, test) {
                if (opener.closed || ! opener.addNewFormPopup)
                alert("<?php echo htmlspecialchars( xl('The destination form was closed; I cannot act on your selection.'), ENT_QUOTES); ?>");
                else
                opener.addNewFormPopup(pid, test);
                window.close();
                return false;
            }

            closePopup('<?php echo $pid; ?>', '<?php echo $test; ?>');
        </script>
    </head>
    <body>
    </body>
    </html>
    <?PHP
}

if(!empty($rto_id)) {
	$save_url .= '&rto_id='.$rto_id;
}

?>
<html>
<head>
	<meta charset="utf-8">
	<title>New Order</title>

	<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
	<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/library/wmt-v2/wmt.default.css'; ?>" type="text/css">

	<?php Header::setupHeader(['common','esign','dygraphs', 'opener', 'dialog', 'datetime-picker', 'jquery', 'jquery-ui-base', 'oemr_ad']);  ?>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

	<style type="text/css">
		.hideContent {
			display: none !important;
		}
		.wmtFInput {
			padding: 1px!important;
			margin: 0px !important;
			font-family: Arial,Helvetica,asns-serif;
    		font-size: 15px;
    		width: 100%;
		}
		.inlineBlock {
			display: inline-block;
		}

		.inlineBlock.rightBlock {
			margin-left: 10px;
		}
		.lbfviewlogs {
			display: none;
		}
		.rto_form_table {
			width: 100%;
		}
		.form_rto {
			height: 100%;
			margin: 0px;
		}
		.inner_form_container {
			display: grid;
		    grid-template-rows: 1fr auto;
		    height: 100%;
		}
		.bodyContainer {
			overflow: auto;
			padding: 10px 10px;
		}
		.btnContainer {
			padding: 10px 10px;
		}
		.statInputContainer {
			display: grid;
		    grid-template-columns: auto 1fr;
		    grid-column-gap: 5px;
		    align-items: center;
		}
		.statInputContainer input {
			margin-top: 0px !important;
		}
	</style>

	<?php include('rto1.js.php'); ?>

	<script type="text/javascript">
		var formData = {};
		var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';

		$(document).ready(function(){
			$('select[name="rto_action"]').on('focusin', function(){
			    $.data(this, 'rto_action_val', $(this).val());
			});

			$('select[name="rto_action"]').change(function(){
				var old_val = $.data(this, 'rto_action_val');
				if(old_val != "") {
					if (!confirm("Notes fields will not transfer between order types. Do you want to still update the order type?\n\nPress Ok to Update  or Cancel to undo the changes.")) {
						$(this).val($.data(this, 'rto_action_val'));
					} else {
						$('#rto_notes').val("");
					}
				}

				$('form[name="form_rto"]').submit();
			});
		});

		var formData1 = $('form[name="form_rto"]').serializeArray();
		formData = getFormData($('form[name="form_rto"]'));

		<?php if($mode == "new_rto_save" && !empty($neworderid)) { ?>
			open_ldf_form('<?php echo $pid; ?>', '<?php echo $rtoformname; ?>', '<?php echo $neworderid; ?>', 0, '<?php echo $rtoformtitle; ?>');
		<?php } ?>

		function lbfFormPopup(pid, formname, visitid, formid) {
			var url = '<?php echo $save_url; ?>'+'&mode=rto_refresh';
			$('form[name="form_rto"]').attr('action', url).submit();
		}

		function formClose() {
			var url = '<?php echo $save_url; ?>'+'&mode=rto_close';
			$('form[name="form_rto"]').attr('action', url).submit();
		}

		// This invokes the find-addressbook popup.
		function open_ldf_form(pid, lformname, rto_id, form_id, form_title) {
			var url = '<?php echo $GLOBALS['webroot']."/interface/forms/rto1/ldf_form.php" ?>'+'?pid='+pid+'&formname='+lformname+'&visitid='+rto_id+'&id='+form_id+'&submod=popup';
		  	let title = form_title;
		  	dlgopen(url, 'ldf_form', 900, 500, '', title);
		}

		function saveRtoData(formname, form_title) {
			var url = '<?php echo $save_url; ?>'+'&rto_formname='+formname+'&rto_form_title='+form_title+'&mode=new_rto_save';
			$('form[name="form_rto"]').attr('action', url).submit();
		}

		function updateBorder(sel) {
			if(sel.options[sel.selectedIndex].value != '') {
				sel.style.border = 'solid 1px grey';
			}
		}

		function validateRTO() {
			<?php
			if(!$popup) echo "	top.restoreSession();\n";
			?>
			var skip = true;
			var item = '';
			if(arguments.length) skip = arguments[0];
			if(arguments.length > 1) item = arguments[1];
			var i;
			var sel;
			var val;
			var f = document.forms[0];
			var l = f.elements.length;
			var msg;
			for (i=0; i<l; i++) {
				if(f.elements[i].name.indexOf('rto_resp_') == 0) {
					// alert("We did find the field");
					<?php if(checkSettingMode('wmt::rto_assign_require','',$frmdir)) { ?>
					sel = f.elements[i];
					val = sel.options[sel.selectedIndex].value;
					// alert("This is my user: "+val);
					if(!skip || f.elements[i].name != 'rto_resp_user') {
						if(!item || f.elements[i].name == 'rto_resp_user_'+item) {
							if(!val) {
								sel.style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}

				if(f.elements[i].name.indexOf('rto_action') == 0) {
					<?php if(checkSettingMode('wmt::rto_action_require','',$frmdir)) { ?>
					sel = f.elements[i];
					val = sel.options[sel.selectedIndex].value;
					if(!skip || f.elements[i].name != 'rto_action') {
						if(!item || f.elements[i].name == 'rto_action_'+item) {
							if(!val) {
								sel.style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}

				if(f.elements[i].name.indexOf('rto_target_date') == 0) {
					<?php if(checkSettingMode('wmt::rto_target_require','',$frmdir)) { ?>
					val = f.elements[i].value;
					if(!skip || f.elements[i].name != 'rto_target_date') {
						if(!item || f.elements[i].name == 'rto_target_date_'+item) {
							if(!val || val == '0000-00-00' || val == '00/00/0000') {
								f.elements[i]..style.border = 'solid 1px red';
								msg = 'Fields bordered in red are required';
							}
						}
					}
					<?php } ?>
				}
			}
			if(msg) {
				alert(msg);
				return false;
			}	
			return true;
		}

		async function SubmitRTONew(pid){
			var rto_resp_user = $('#rto_resp_user').val();

			if(rto_resp_user == "") {
				alert("Please select assigned To");
				return false;
			}

			if(!validateRTO(false)) return false;

			var caseData = await checkCaseValidation(pid);


			if(caseData === false) {
				return false;
			}

			var url = '<?php echo $save_url; ?>'+'&mode=save';
			$('form[name="form_rto"]').attr('action', url).submit();
		}
	</script>
</head>
<body>
	<form method='post' action="<?php echo $save_url ?>" name='form_rto' class="form_rto"> 
	<div class="inner_form_container">
		<div class="bodyContainer">
			<table class="rto_form_table">
				<tr>
					<td class='wmtLabel2' style='width: 100px;'><?php xl('Order Type','e'); ?>:</td>
					<td>
						<select name='rto_action' id='rto_action' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='10'" : ""; ?> onchange="updateBorder(this);" ><?php ListSel($dt['rto_action'], 'RTO_Action'); ?>
						</select>
					</td>
					<td class='wmtLabel2' style='width: 95px;'><?php xl('Ordered By','e'); ?>:</td>
					<td>
						<select name='rto_ordered_by' id='rto_ordered_by' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='20'" : ""; ?>><?php UserSelect($dt['rto_ordered_by']); ?></select>
					</td>
				</tr>
				<tr>
					<td class='wmtLabel2'><?php xl('Status','e'); ?>:</td>
					<td>
						<select name='rto_status' id='rto_status' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='40'" : ""; ?>>
							<?php ListSel($dt['rto_status'], 'RTO_Status'); ?>
						</select>
					</td>
					<td class='wmtLabel2'><?php xl('Assigned To','e'); ?>:</td>
					<td>
						<select name='rto_resp_user' id='rto_resp_user' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='50'" : ""; ?> onchange="updateBorder(this);" >
							<?php MsgUserGroupSelect($dt['rto_resp_user'], true, false, false, array(), true); ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class='wmtLabel2'><?php xl('Case','e'); ?>:</td>
					<td>
						<input name="rto_case" id="rto_case" type="text" value="<?php echo $dt['rto_case']; ?>" onclick="sel_case('<?php echo $pid; ?>');" class="wmtInput wmtFInput" title="Click to select or add a case for this appointment" />
					</td>
					<td colspan="2">
						<div class="statInputContainer">
							<span><?php echo xlt('Stat') ?>:</span>
							<input type="checkbox" name='rto_stat' id='rto_stat' <?php echo $dt['rto_stat'] === "1" ? "checked" : "" ?> value="1">
						</div>
					</td>
				</tr>
				<tr>
					<td class='wmtLabel2'><div class="<?php echo $hideClass; ?>"><?php xl('Due Date','e'); ?>:</div></td>
					<td colspan="3">
						<div class="inlineBlock <?php echo $hideClass; ?>">
							<input name='rto_target_date' id='rto_target_date' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='80'" : ""; ?> style='text-align: right; width: 85px;' type='text' value="<?php echo oeFormatShortDate($dt['rto_target_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title='<?php echo $date_title_fmt; ?>' />
							<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_rto_target_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' tabindex='-1' title="<?php xl('Click here to choose a date','e'); ?>">
							<div style='display: inline-block;'>
								<span class='wmtLabel2'>&nbsp;&nbsp;-<?php xl('or','e'); ?>-&nbsp;&nbsp;&nbsp;&nbsp;</span>
								<select name='rto_num' id='rto_num' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='90'" : ""; ?> onchange="SetRTOStatus('rto_status'); FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');"><?php ListSel($dt['rto_num'], 'RTO_Number'); ?>
								</select>
							</div>
						</div>
						<div class="inlineBlock rightBlock <?php echo $hideClass; ?>">
							<select name='rto_frame' id='rto_frame' class='wmtInput wmtFInput' <?php echo ($frmdir == 'rto') ? "tabindex='100'" : ""; ?> onchange="SetRTOStatus('rto_status'); FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');"><?php ListSel($dt['rto_frame'], 'RTO_Frame'); ?></select>
							<span class='wmtLabel2'>&nbsp;<?php xl('From','e'); ?>&nbsp;</span>
							<input name='rto_date' id='rto_date' class='wmtInput wmtFInput' type='text' <?php echo ($frmdir == 'rto') ? "tabindex='110'" : ""; ?> style='width: 85px;' value="<?php echo oeFormatShortDate($dt['rto_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" onchange="FutureDate('rto_date','rto_num','rto_frame','rto_target_date','<?php echo $GLOBALS['date_display_format']; ?>');" title='<?php echo $date_title_fmt; ?>' />
							<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_rto_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title="<?php xl('Click here to choose a date','e'); ?>">
						</div>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel2"><div class="<?php echo $hideClass; ?>">Recurring:</div></td>
					<td class="wmtBody2">
						<div class="<?php echo $hideClass; ?>">
							<input name='rto_repeat' id='rto_repeat' type='checkbox' value='1' <?php echo ($frmdir == 'rto') ? "tabindex='140'" : ""; ?> <?php echo $dt['rto_repeat'] == 1 ? 'checked="checked"' : ''; ?> />
							<label for='rto_repeat'>&nbsp;Yes (as above)</label>
						</div>
					</td>
					<td class="wmtLabel2"><div class="<?php echo $hideClass; ?>">&nbsp;Stop Date:</div></td>
					<td>
						<div class="<?php echo $hideClass; ?>">
							<input name='rto_stop_date' id='rto_stop_date' class='wmtInput wmtFInput' type='text' style='' <?php echo ($frmdir == 'rto') ? "tabindex='150'" : ""; ?> value="<?php echo oeFormatShortDate($dt['rto_stop_date']); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title='Specify a date to stop this order if applicable' />
							<img src='../../pic/show_calendar.gif' width='22' height='20' id='img_stop_dt' border='0' alt='[?]' style='cursor:pointer; vertical-align: middle;' title="<?php xl('Click here to choose a date','e'); ?>">
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="4" class='wmtLabel2'>
						<div class="<?php echo ($dt['layout_form'] === true) ? 'hideContent' : '' ?>"><?php xl('Notes','e'); ?>:</div>
						<?php
							/* OEMR - Changes */
							if($newordermode === true) {
								if(!empty($order_action) && !empty($layoutData_n) && !empty($layoutData_n['grp_form_id']) && empty($rtoData_n)) {
									$url = "../../../interface/forms/LBF/order_new.php?formname=".$layoutData_n['grp_form_id']."&visitid=".$rto_id."&id=".$form_id_n."&submod=true";
									?>
										<button type="button" class="css_button_small lbfbtn" onClick="saveRtoData('<?php echo $layoutData_n['grp_form_id']; ?>', '<?php echo isset($layoutData_n['grp_title']) ? $layoutData_n['grp_title'] : ''; ?>')"><?php xl('Save Order & Enter details','e'); ?></button>
									<?php
								}

								if(!empty($layoutData_n) && !empty($layoutData_n['grp_form_id']) && !empty($rtoData_n)) {
									echo "&nbsp;". xl('Summary','e').":";
								}
							}

							getLBFFormData($rto_id, $pid, $rtoData_n, $layoutData_n);
							/* End */
						?>
						<div class="<?php echo ($dt['layout_form'] === true) ? 'hideContent' : '' ?>">
							<textarea name='rto_notes' id='rto_notes' class='wmtFullInput' <?php echo ($frmdir == 'rto') ? "tabindex='200'" : ""; ?> rows='4'><?php echo $dt['rto_notes']; ?></textarea>
						</div>
						<input name='tmp_rto_cnt' id='tmp_rto_cnt' type='hidden' tabindex='-1' value="0" />
					</td>
				</tr>
			</table>
		</div>
		<div class="btnContainer">
			<a class='css_button add_btn' onClick="SubmitRTONew('<?php echo $pid; ?>');" href='javascript:;'><span style='text-transform: none;'><?php xl('Add Order','e'); ?></span></a>
			<a class='css_button add_btn' onClick="formClose();" href='javascript:;'><span style='text-transform: none;'><?php xl('Close','e'); ?></span></a>
		</div>
	</div>
	</form>
</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/rto.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.forms.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<script type="text/javascript">
<?php
if($GLOBALS['date_display_format'] == 1) {
	$date_fmt = '%m/%d/%Y';
} else if($GLOBALS['date_display_format'] == 2) {
	$date_fmt = '%d/%m/%Y';
} else $date_fmt = '%Y-%m-%d';
?>
Calendar.setup({inputField:"rto_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_dt"});
Calendar.setup({inputField:"rto_target_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_target_dt"});
Calendar.setup({inputField:"rto_stop_date", ifFormat:"<?php echo $date_fmt; ?>", button:"img_rto_stop_dt"});
</script>
</html>