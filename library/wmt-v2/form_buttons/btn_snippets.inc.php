<?php 
if(!isset($this_module)) $this_module = '';
if(!isset($use_break)) $use_break = FALSE;
if(!isset($use_snippets)) 
	$use_snippets = checkSettingMode('wmt::use_snippets','',$frmdir);
if($use_snippets && $snippet_type = isSnippetField($field_prefix.$field_name)) {
	if($use_break) echo '<br>';
?>
<div style="float: right; padding-right: 8px;"><a class="btn btn-primary btn-sm css_button_small" tabindex="-1" onclick="wmtOpen('<?php echo FORM_BRICKS_JS; ?>form_snippet.php?module=<?php echo $this_module; ?>&frmdir=<?php echo $frmdir; ?>&fld=<?php echo $field_prefix . $field_name; ?>&type=<?php echo $snippet_type; ?>', '_blank', '55%', '90%');"><span><?php xl('Use Snippets','e'); ?></span></a></div>
<?php
}
?>
