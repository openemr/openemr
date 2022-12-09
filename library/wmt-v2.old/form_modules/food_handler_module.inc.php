<?php 
$printed_by = '';
$printed_dt = '';
if(!isset($draw_display)) $draw_display = TRUE;
if($dt{'result_printed_by'}) 
	$printed_by =  getUserDisplayName($dt{'result_printed_by'}, 'first');
if($dt{'result_printed_dt'} && 
			($dt{'result_printed_dt'} != '0000-00-00 00:00:00')) 
					$printed_dt = $dt{'result_printed_dt'};
if($draw_display) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<!--td style="width: 20px;"><input name="a_collected" id="a_collected" type="checkbox" value="1" <?php // echo $dt{'a_collected'} ? 'checked="checked"' : ''; ?> onchange="setTimeStamp(this, 'a_collected_dt');" /><input type="hidden" name="a_collected_dt" id="a_collected_dt" value="<?php // echo $dt{'a_collected_dt'}; ?>" /></td -->
		<td class="wmtLabel"><label for="a_collected">Nasal swab collected</label></td>
		<td class="wmtLabel wmtClick" onclick="toggleThroughSelect('a_result');">Nasal swab result:</td>
		<td><select name="a_result" id="a_result" class="wmtInput">
		<?php echo ListSel($dt{'a_result'},'Pos_Neg'); ?>
		</select></td>
		<td class="wmtLabel">Result Notes:</td>
		<td style="width: 45%;"><input name="a_result_nt" id="a_result_nt" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'a_result_nt'},ENT_QUOTES); ?>" /></td>
	</tr>
	<tr>
		<!-- td style="width: 20px;"><input name="b_collected" id="b_collected" type="checkbox" value="1" <?php // echo $dt{'b_collected'} ? 'checked="checked"' : ''; ?> onchange="setTimeStamp(this, 'b_collected_dt');" /><input type="hidden" name="b_collected_dt" id="b_collected_dt" value="<?php // echo $dt{'b_collected_dt'}; ?>" /></td -->
		<td class="wmtLabel"><label for="b_collected">Stool Sample collected</label></td>
		<td class="wmtLabel wmtClick" onclick="toggleThroughSelect('b_result');">Stool Sample result:</td>
		<td><select name="b_result" id="b_result" class="wmtInput">
		<?php echo ListSel($dt{'b_result'},'Pos_Neg'); ?>
		</select></td>
		<td class="wmtLabel">Result Notes:</td>
		<td style="width: 45%;"><input name="b_result_nt" id="b_result_nt" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'b_result_nt'},ENT_QUOTES); ?>" /></td>
	</tr>
	<input name="referral" id="referral" type="hidden" value="<?php echo $dt{'referral'}; ?>" />
<?php if($dt['referral']) { ?>
	<tr>
		<td colspan="6"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<td class="wmtLabel">&nbsp;&nbsp;Certificate Printed By:</td>
		<td colspan="4"><div id="queue_label" style="float: left; margin-left: 18px;"><?php echo $printed_by . ' on ' . $printed_dt; ?></div>
		</td> 
	</tr>
<?php } ?>
</table>

<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/form_js/food_handler.js" type="text/javascript"></script>
<?php } ?>
