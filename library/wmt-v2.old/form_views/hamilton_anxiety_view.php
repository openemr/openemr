<?php ?>
	<tr>
		<td colspan="2" class="wmtLabel">Hamilton Rating Scale for Anxiety</td>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="wmtPrnBody">Anxious Mood</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_mood'},'Hamilton_Scale'); ?></td>
		<td class="wmtPrnBody">Somatic</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_somatic'},'Hamilton_Scale'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">Tension</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_tense'},'Hamilton_Scale'); ?></td>
		<td class="wmtPrnBody">Cardiovascular</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_cardio'},'Hamilton_Scale'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">Fears</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_fear'},'Hamilton_Scale'); ?></td>
		<td class="wmtPrnBody">Respiratory</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_resp'},'Hamilton_Scale'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">Insomnia</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_insomnia'},'Hamilton_Scale'); ?></td>
		<td class="wmtPrnBody">Gastrointestinal</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_gastro'},'Hamilton_Scale'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">Intellect</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_intellect'},'Hamilton_Scale'); ?></td>
		<td class="wmtPrnBody">Genitourinary</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'anx_genito'},'Hamilton_Scale'); ?></td>
	</tr>
	<tr>
		<td colspan="4"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<td class="wmtPrnLabel" colspan="2"><?php echo $dt['referral'] == 1 ? 'Referral made' : 'No Referral Made'; ?></td> 
		<td class="wmtPrnLabel">Total</td>
		<td class="wmtPrnLabel"><?php echo $dt{'anx_total'}; ?></td>
	</tr>
