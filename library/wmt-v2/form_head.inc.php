<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<?php
$js_location = $GLOBALS['webroot'] . '/library/js';
if($v_major > 4) $js_location = $GLOBALS['assets_static_relative'];
?>
<html>
<head>
<title><?php echo $ftitle; ?></title>
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
<!-- <style type="text/css">@import url(<?php //echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="<?php //echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php //include_once($GLOBALS['srcdir'] . 'dynarch_calendar_en.inc.php'); ?> -->
<?php } ?>

<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

<?php \OpenEMR\Core\Header::setupHeader(['jquery', 'jquery-ui', 'datetime-picker']); ?>

<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<?php if($v_major > 4) { ?>
	<?php if($v_minor || $v_patch) { ?>
<!-- <script type="text/javascript" src="<?php //echo $js_location; ?>/jquery-min-3-1-1/index.js"></script>
<script type="text/javascript" src="<?php //echo $js_location; ?>/jquery-ui-1-11-4/jquery-ui.min.js"></script> -->
	<?php } else { ?>
<!-- <script type="text/javascript" src="<?php //echo $js_location; ?>/jquery-min-1-9-1/index.js"></script>
<script type="text/javascript" src="<?php //echo $js_location; ?>/jquery-ui-1-11-4/jquery-ui.min.js"></script> -->
	<?php } ?>
<?php } else { ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/overlib_mini.js"></script>
<!-- <script type="text/javascript" src="<?php //echo $js_location; ?>/jquery.1.7.2.min.js"></script>
<script type="text/javascript" src="<?php //echo $js_location; ?>/jquery-ui.js"></script> -->
<?php } ?>

<?php include_once($GLOBALS['srcdir'] . '/wmt-v2/form_head.js.php'); ?>

</head>

<?php
$save_notification_display = '';
if($first_pass) {
	$dt['tmp_form_disp_mode']='block';
	foreach($modules as $module) {
		$dt['tmp_' . $module['option_id'] . '_disp_mode'] = 'block';
	}
	$dt['tmp_scroll_top'] = '';

	if($form_mode == 'new') {
		$dt['form_complete'] = 'c';
		$dt['form_priority'] = 'n';
		$dt['form_dt'] = date('Y-m-d');
	}
}

$save_notification_display = 'visibility: hidden;';
$load = '';
if($form_focus) $load .= "AdjustFocus('$form_focus');";
if($continue && $pop_form) $load .= ' refreshVisitSummary();';
if($continue == 'print') $load.= ' printForm();';
if($continue == 'referral') $load.= ' printReferral();';
if($continue == 'instructions') $load.= ' printInstructions();';
if(!$save_notification_display) $load .= ' delayedHideDiv();';
?>