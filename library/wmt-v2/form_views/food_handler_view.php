<?php 
$printed_by = '';
$printed_dt = '';
$chp_printed = TRUE;
if($dt{'result_printed_by'}) 
	$printed_by = getUserDisplayName($dt{'result_printed_by'}, 'first');
if($dt{'result_printed_dt'} && 
			($dt{'result_printed_dt'} != '0000-00-00 00:00:00')) 
					$printed_dt = $dt{'result_printed_dt'};
$result_dt = substr($dt{'a_result_dt'},0,10);
if(substr($dt{'b_result_dt'},0,10) > $result_dt) 
		$result_dt = substr($dt{'a_result_dt'},0,10);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td class="wmtPrnLabel"><?php echo $dt{'a_collected'} ? 'Nasal Swab Collected' : 'Nasal Swab Not Collected'; ?></td>
		<td class="wmtPrnLabel">Nasal swab result:</td>
		<td class="wmtPrnLabel"><?php echo ListLook($dt{'a_result'},'Pos_Neg'); ?></td>
		<td class="wmtPrnLabel">Result Notes:</td>
		<td style="width: 45%;"><?php echo htmlspecialchars($dt{'a_result_nt'},ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnLabel"><?php echo $dt{'b_collected'} ? 'Stool Sample Collected' : 'Stool Sample Not Collected'; ?></td>
		<td class="wmtLabel">Stool Sample result:</td>
		<td class="wmtLabel"><?php echo ListLook($dt{'b_result'},'Pos_Neg'); ?></td>
		<td class="wmtLabel">Result Notes:</td>
		<td><?php echo htmlspecialchars($dt{'b_result_nt'},ENT_QUOTES); ?></td>
	</tr>
<?php if($printed_by || $printed_dt) { ?>
	<tr>
		<td class="wmtLabel" colspan="4">Certificate printed by: <?php echo htmlspecialchars($printed_by,ENT_QUOTES); ?> on <?php echo htmlspecialchars($printed_dt,ENT_QUOTES); ?></td>
	</tr>
<?php } ?>
