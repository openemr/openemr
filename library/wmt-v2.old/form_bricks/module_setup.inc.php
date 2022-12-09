<?php
if(!isset($frmdir)) $frmdir = '';
if(!isset($encounter)) $encounter = '';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($local_fields)) $local_fields = array();
foreach($local_fields as $key) {
	if(!isset($dt[$field_prefix . $key])) $dt[$field_prefix . $key] = '';
}
$this_module = $module['option_id'];
if($chp_options[0]) $this_module = $chp_options[0];
$this_table = 'form_' . $this_module;
$nohist = checkSettingMode('wmt::noload_module_history','',$this_module);
?>
