<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/restoreSession.php"></script>
<?php if(!$pop_form) { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.js" type="text/javascript"></script>
<?php } else { ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js" type="text/javascript"></script>
<?php } ?>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js" type="text/javascript"></script>
<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmt.forms.js" type="text/javascript"></script>
<?php if($v_major > 4) { ?>
<!-- script type="text/javascript" src="<?php // echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-11-1/index.js"></script -->
<?php } else { ?>
<!-- script src="<?php // echo $GLOBALS['webroot']; ?>/library/js/jquery-1.7.2.min.js" type="text/javascript"></script -->
<?php } ?>

<script type="text/javascript">
<?php
foreach($modules as $module) {
	// Gender filter, field prefix and alternate are all in codes
	// as alternate|prefix|gender|field name|button 1|button2
	$field_prefix = $field_name = $button1 = $button2 = '';
	$chp_options = array();
	if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
	if(!isset($chp_options[0])) $chp_options[0] = '';
	if(!isset($chp_options[1])) $chp_options[1] = '';
	if(!isset($chp_options[2])) $chp_options[2] = '';
	if(!isset($chp_options[3])) $chp_options[3] = '';
	if(!isset($chp_options[4])) $chp_options[4] = '';
	if(!isset($chp_options[5])) $chp_options[5] = '';

	if($chp_options[1] != '') $field_prefix = $chp_options[1];
	if($chp_options[2] != '' && $chp_options[2] != $pat_sex) continue;
	if($chp_options[3] != '') $button1 = $chp_options[3];
	if($chp_options[4] != '') $button2 = $chp_options[4];
	if($chp_options[0] != '') $field_name = $module['option_id'];

	// IS THERE A SPECIFIC FOOTER SCRIPT
	if(is_file('./js/'.$module['option_id'].'.js')) 
		include('./js/'.$module['option_id'].'.js');
	if(is_file(FORM_JS_DIR . $module['option_id'].'.js')) { 
		include(FORM_JS_DIR . $module['option_id'].'.js');
	}
} 
?>
</script>
