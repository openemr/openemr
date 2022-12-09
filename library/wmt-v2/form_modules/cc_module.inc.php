<?php
$rows = checkSettingMode('wmt::cc_rows','',$frmdir);
if(!$rows) $rows = 5;
if(!isset($field_prefix)) $field_prefix='';
if(!isset($dt[$field_prefix.'rec_review'])) $dt[$field_prefix.'rec_review'] = '';
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel" colspan="2">Chief Complaint:</td>
				<td style="width: 40%">&nbsp;</td>
				<td><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>cc');" href="javascript:;"><span>Clear</span></a></td>
				<td><div style="float: right; padding-right: 8px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);"><span>View Documents</span></a></div></td>
			</tr>
			<tr>
				<?php if($rows == 1) { ?>
				<td colspan="5"><input name="<?php echo $field_prefix; ?>cc" id="<?php echo $field_prefix; ?>cc" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'cc'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<?php } else { ?>
				<td colspan="5"><textarea name="<?php echo $field_prefix; ?>cc" id="<?php echo $field_prefix; ?>cc" class="wmtFullInput" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'cc'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
				<?php } ?>
			</tr>
			<tr>
				<td style="width: 18px"><input name="<?php echo $field_prefix; ?>rec_review" id="<?php echo $field_prefix; ?>rec_review" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'rec_review'} == 1)?' checked ':''); ?> /></td>
				<td class="wmtBody">Medical Records Reviewed</td>
			</tr>
    </table>
<?php ?>
