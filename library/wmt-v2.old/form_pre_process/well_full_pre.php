<?php 
$wellness_modules = LoadList('well_'.$frmdir);

foreach($wellness_modules as $wmod) {
	$winc = $wmod['option_id'] . '_pre.php';
	if(is_file('./pre_process/' . $winc)) {
		include('./pre_process' . $winc);
	} else if(is_file(FORM_PREPROCESS . $winc)) {
		include(FORM_PREPROCESS . $winc);
	}
}
?>
