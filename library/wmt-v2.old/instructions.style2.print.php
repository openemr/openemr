<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title) || $pane_title == '') $pane_title= xl('Patient Instructions','r');
$delay = '24';
if(isset($GLOBALS['wmt::pat_summ_time_delay'])) $delay = $GLOBALS['wmt::pat_summ_time_delay'];
?>
		
<fieldset style='border: solid 1px black; padding: 6px;'><legend class='bkkPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<span class="bkkPrnBody" style="width: 100%; white-space: pre-wrap;"><?php echo htmlspecialchars($pat_instructions, ENT_QUOTES, '', FALSE); ?></span>
	<br/>
	<ul>
	<li class="bkkPrnBody">Go to an emergency room for any emergencies.</li>
	<li class="bkkPrnBody"><?php echo ($client_id == 'sfa') ? 'Please' : 'Have your pharmacy'; ?> contact the office if refills are needed.</li>
	<li class="bkkPrnBody">Please provide at least&nbsp;<?php echo $delay; ?>&nbsp;hours notice to cancel or reschedule an appointment.</li>
	<li class="bkkPrnBody">Please notify the office of changes in address, phone number, or insurance.</li>
	</ul>
</fieldset>

<?php
$pane_printed = true;
?>
