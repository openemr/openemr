<?php
if(!isset($field_prefix)) $field_prefix='';
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Review Notes:</td>
				<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>review_nt');" href="javascript:;"><span>Clear the Review</span></a></div></td>
			</tr>
			<tr>
        <td colspan="2"><textarea name="<?php echo $field_prefix; ?>review_nt" id="<?php echo $field_prefix; ?>review_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'review_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
      </tr>
    </table>
<?php ?>
