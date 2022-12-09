<?php
if(!isset($create)) $create = FALSE;
if(!isset($dt{'form_complete'})) $dt{'form_complete'} = '';
if(!isset($content)) $content = '';
$_suppress = checkSettingMode('wmt::suppress_signature','',$frmdir);
$in_archive = FALSE;
if(strtolower($dt{'form_complete'}) == 'a' && !$create && $content) {
	if(stripos($content, 'Digitally Signed') !== FALSE) $in_archive = TRUE;
}
if(!$in_archive && isset($visit) && !$_suppress) {
?>
	<br>
	<?php if($visit->student_by) { ?>
	<div style="float: left; text-align: left; width: 100%;" class='wmtPrnLabel'><?php echo $visit->student_by; ?></div><br>
	<?php } ?>
	<div style="float: left; text-align: left; width: 100%;" class='wmtPrnLabel'><?php echo $visit->signed_by; ?></div><br>
	<?php if($visit->approved_by) { ?>
	<div style="float: left; text-align: left; width: 100%;" class='wmtPrnLabel'><?php echo $visit->approved_by; ?></div><br>
	<?php } ?>
<?php } ?>
