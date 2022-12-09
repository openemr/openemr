<?php
$k = 'wmt::suppress_clear_field::'.$frmdir.'::'.$field_prefix.$field_name;
if(!isset($GLOBALS[$k])) $GLOBALS[$k] = '';
if(!$GLOBALS[$k]) {
?>
<div style="float: right; padding-right: 8px;"><a class="css_button_small" tabindex="-1" onClick="ClearThis('<?php echo $field_prefix.$field_name; ?>');" href="javascript:;"><span>Clear Notes</span></a></div>
<?php
}
?>
