<?php
foreach($modules as $module) {
	// GENDER FILTER, FIELD PREFIX AND ALTERNATE ARE ALL IN CODES AS
	// alternate|prefix|gender
	$field_prefix = $field_name = '';
	$chp_options = array();
	if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
	$cnt = 0;
	while($cnt < 10) {
		if(!isset($chp_options[$cnt])) $chp_options[$cnt] = '';
		$cnt++;
	}

	if($chp_options[1] != '') $field_prefix = $chp_options[1];
	if($chp_options[2] != '' && $chp_options[2] != $pat_sex) continue;
	if($chp_options[0] != '') $field_name = $module['option_id'];

	$chp_printed = false;
	$hdr_printed = false;
	$sub = '';
	$chp_title = $module['title'];

	$this_module = $module['option_id'];
	if($chp_options[0]) $this_module = $chp_options[0];
	if($chp_options[0] != '') {
		include(FORM_V1_VIEWS . $this_module . '.print.inc.php');
	} else {
		// Is there a form specific module?
		if(is_file(FORMS_DIR . "$frmdir/views/$this_module" . '_view.php')) {
			include(FORMS_DIR . "$frmdir/views/$this_module" . '_view.php');
		} else {
			include(FORM_VIEWS . $this_module . '_view.php');
		}
	}
	if($chp_printed) CloseChapter();
}
?>
