<?php

// COMMENT THIS OUT FOR BELOW 5.0.1
use OpenEMR\Core\Header;

$js_location = $GLOBALS['webroot'] . '/library/js';
if($v_major > 4) $js_location = $GLOBALS['assets_static_relative'];
?>
<html>
<head>
<title><?php echo $ftitle; ?></title>
<?php Header::setupHeader(['common', 'opener', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-bs', 'oemr_ad', 'datetime-picker']); ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'];?>/library/wmt/wmt.default.css" type="text/css">

<?php include_once($GLOBALS['srcdir'] . '/wmt-v2/form_head.js.php'); ?>

<?php
	// IS THERE A SPECIFIC SCRIPT
	if(is_file('./js/forms/' . $frmdir . '.js')) {
	 	echo '<script src="/js/forms/' . $frmdir . '.js" >';
	}
	
	if(is_file($include_root . '/forms/' . $frmdir .'/js/' . $frmdir . '.js')) { 
		echo '<script type="text/javascript" src="'.$GLOBALS['webroot'].'/interface/forms/cases/js/cases.js"></script>';
		echo '<script type="text/javascript" src="'.$GLOBALS['webroot'].'/interface/coverage/js/coverage.js"></script>';
	}
?>

<style type="text/css">
	.small-form .card {
		border-radius: 0px;
	}

	.small-form .card > .card-header {
		padding-top: 8px;
    	padding-bottom: 8px;
    	padding: 8px 15px;
	}

	.small-form .card .card-body {
		padding: 10px 15px;
	}

	.small-form .card .card-header > h6 {
		color: #007bff;
	}

	.small-form input.form-control,
	.small-form select.form-control,
	.small-form span.form-control {
		height: calc(1.5em + 0.5rem + 2px);
	    padding: 0.25rem 0.5rem;
	    font-size: .875rem;
	    line-height: 1.5;
	    border-radius: 0.2rem;
	}

	.small-form textarea.form-control {
		padding: 0.25rem 0.5rem;
    	font-size: .875rem;
    	line-height: 1.5;
    	border-radius: 0.2rem;
	}

	.small-form .form-group > label,
	.small-form label,
	.small-form span,
	.small-form a {
		font-size: .875rem;
	}

	.small-form .form-group label {
		margin-bottom: 0.3rem;
	}

	.small-form .btn {
		padding: 0.25rem 0.5rem;
    	font-size: .875rem;
    	line-height: 1.5;
    	border-radius: 0.2rem;
	}

	.small-form #lpc_ele_container .m-btn-remove {
		min-height: 32px;
	}
</style>

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
