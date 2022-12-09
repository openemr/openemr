<?php
if(!isset($field_prefix)) $field_prefix='';
?>
		<?php include($GLOBALS['srcdir'].'/wmt-v2/diagnosis.inc.php'); ?> 
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Other Plan Notes:</td>
				<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>plan');" href="javascript:;"><span>Clear the Plan</span></a></div></td>
			</tr>
			<tr>
				<td colspan="2"><textarea name="<?php echo $field_prefix; ?>plan" id="<?php echo $field_prefix; ?>plan" class="wmtFullInput" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'plan'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
      </tr>
    </table>
<?php ?>
