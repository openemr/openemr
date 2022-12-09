<?php
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">Notes:</td>
			</tr>
			<tr>
				<td><textarea name="<?php echo $field_prefix; ?>birth_nt" id="<?php echo $field_prefix; ?>birth_nt" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'. ?>" rows="5"><?php echo htmlspecialchars($dt{$field_prefix.'birth_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
		</table>
