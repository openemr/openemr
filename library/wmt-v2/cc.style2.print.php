<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title = xl('Chief Complaint / Reason','r');
if(!isset($brdr)) $brdr = 'border: solid 1px black;';
if(!isset($cc)) $cc = '';
if($cc) {		
?>
<fieldset style="<?php echo $brdr; ?>"><legend class="bkkPrnHeader">&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<p class="bkkPrnBody"><?php echo htmlspecialchars($cc, ENT_QUOTES, '', FALSE); ?>&nbsp;</p>
</fieldset>
<?php
	$pane_printed = true;
}
?>
