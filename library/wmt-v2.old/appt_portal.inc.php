<?php
if(!isset($appt)) $appt = array();
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td class="bkkLabel bkkBorder1B bkkC bkkDateCell">Appt Date</td>
		<td class="bkkLabel bkkBorder1B ">&nbsp;&nbsp;Time</td>
		<td class="bkkLabel bkkBorder1B ">&nbsp;&nbsp;Reason</td>
		<td class="bkkLabel bkkBorder1B ">&nbsp;&nbsp;Provider</td>
		<td class="bkkLabel bkkBorder1B ">&nbsp;&nbsp;Notes</td>
	</tr>
<?php
$bg = 'bkkLight';
$cnt=1;
if(count($appt) > 0) {
	foreach($appt as $prev) {
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody">&nbsp;<?php echo $prev['pc_eventDate']; ?>&nbsp;</td>
		<td class="bkkBody"><?php echo $prev['pc_startTime']; ?>&nbsp;</td>
		<td class="bkkBody"><?php echo $prev['pc_catname']; ?>&nbsp;</td>
		<td class="bkkBody"><?php echo $prev['lname'].', '.$prev['fname']; ?>&nbsp;</td>
		<td class="bkkBody"><?php echo $prev['pc_hometext']; ?>&nbsp;</td>
	</tr>
		
<?php
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$cnt++;
	}
} else {
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody">&nbsp;</td>
		<td class="bkkLabel" colspan="4">No Appointments To Display</td>
	</tr>
<?php 
}
?>
</table>
