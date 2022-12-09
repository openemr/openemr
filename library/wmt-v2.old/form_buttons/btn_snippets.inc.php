<?php 
if(!isset($use_snippets)) 
	$use_snippets = checkSettingMode('wmt::use_snippets','',$frmdir);
if($use_snippets) { 
?>
<div style="float: right; padding-right: 8px;"><a class="css_button_small" tabindex="-1" onclick="wmtOpen('<?php echo FORM_BRICKS_JS; ?>form_snippet.php?module=<?php echo $field_name; ?>&frmdir=<?php echo $frmdir; ?>&fld=<?php echo $field_prefix . $field_name; ?>', '_blank', '55%', '90%');"><span><?php xl('Use Snippets','e'); ?></span></a></div>
<?php
}
?>
