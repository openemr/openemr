<?php
if(!isset($auto_post_pqrs)) $auto_post_pqrs = false;
if(!isset($is_billed)) $is_billed = false;
if(!isset($pqrs_selected)) $pqrs_selected = array();
?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/pqrs_functions.js"></script>
    <table width="100%" border="0" cellspacing="0" cellpadding="4">
      <tr>
        <td class="wmtLabel" colspan="3">&nbsp;&nbsp;&nbsp;Check any measures completed:</td>
        <td><div style="float: right; margin-right: 20px;"><a class="css_button" tabindex="-1" onclick="return togglePQRSToNull();" href="javascript:;"><span>Clear All</span></a></div></td>
      </tr>
			<?php if($auto_post_pqrs && $is_billed) { ?>
			<tr>
				<td colspan="4" class="wmtLabelRed"><i>&nbsp;*&nbsp;This encounter has been billed, any changes made here will not affect the billing</i></td>
			</tr>
			<?php } ?>

			<!-- OUTER LOOP WILL BE THE MAIN CATEGORIES -->
			<?php foreach($pqrs_categories as $cat) { ?>
      <tr>
				<td style="width: 10%;">&nbsp;</td>
        <td class="wmtLabel" colspan="3"><?php echo htmlspecialchars($cat['notes'], ENT_QUOTES, '', FALSE); ?> - <i><?php echo htmlspecialchars($cat['title'], ENT_QUOTES, '', FALSE); ?></i></td>
      </tr>

			<?php
			foreach($pqrs_keys as $key) {
				if($cat['option_id'] != $key['notes']) continue;
			?>
				
			<tr>
				<td>&nbsp;</td>
				<td style="width: 20px;">&nbsp;</td>
				<td class="wmtBody"><input name="<?php echo 'tmp_pqrs_'.$key['option_id']; ?>" id="<?php echo 'tmp_pqrs_'.$key['option_id']; ?>" type="checkbox" value="<?php echo $key['option_id']; ?>" <?php echo in_array($key['option_id'], $pqrs_selected) ? 'checked ' : ''; ?> />&nbsp;&nbsp;
				<label for="<?php echo 'tmp_pqrs_'.$key['option_id']; ?>"><?php echo htmlspecialchars($key['option_id'],ENT_QUOTES,'',FALSE); ?></label> - <i><?php echo htmlspecialchars($key['title'],ENT_QUOTES,'',FALSE); ?></i></td>
			</tr>
			<?php 	} ?>
			<?php } ?>
		</table>
				
		<!-- This div is for all the 'DO NOT USE' keys to retain history -->
		<div style="display: none;">
			<?php
				foreach($pqrs_unused_keys as $key) {
					if(in_array($key['option_id'], $pqrs_selected)) {
						GenerateHiddenPQRS('tmp_pqrs_'.$key['option_id'],$key['option_id']);
					}
				}
			?>
		</div>
<?php ?>
