<?php
if(!isset($rto)) $rto = array();
if(!isset($portal_mode)) $portal_mode = false;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
	<tr>
		<td class="bkkLabel bkkBorder1B bkkC bkkDateCell">Due Date</td>
		<td class="bkkLabel bkkBorder1B bkkC">Order</td>
		<td class="bkkLabel bkkBorder1B bkkC">Assigned To</td>
		<td class="bkkLabel bkkBorder1B bkkC">Notes</td>
	</tr>
<?php
$bg = 'bkkAltLight';
$cnt=1;
if(isset($rto) & (count($rto) > 0)) {
	foreach($rto as $prev) {
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody"><?php echo $prev['rto_target_date']; ?>&nbsp;</td>
		<td class="bkkBody"><?php echo ListLook($prev['rto_action'],'RTO_Action'); ?></td>
		<td class="bkkBody"><?php echo UserLook($prev['rto_resp_user']); ?></td>
		<td class="bkkBody"><?php echo $prev['rto_notes']; ?></td>
	</tr>
		
<?php
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$cnt++;
	}
} else {
?>
	<tr class="<?php echo $bg; ?>">
		<td class="bkkBody">&nbsp;</td>
		<td class="bkkLabel" colspan="3">No Pending Orders/Actions To Display</td>
	</tr>
<?php 
}
?>
</table>
