<?php 
$rows = checkSettingMode('wmt::hpi_rows','',$frmdir);
if(!$rows) $rows = 10;
if(!isset($dt['fyi_portal_hpi'])) $dt['fyi_portal_hpi'] = '';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if($portal_mode) $field_prefx = 'fyi_portal_';
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
        <td class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>" colspan="2">History of Present Illness:</td>
				<td style="width: 40%;">&nbsp;</td>
				<td><div style="float: right;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>hpi');" href="javascript:;"><span>Clear</span></a></div></td>
				<?php if($portal_mode) { ?>
				<td>&nbsp;</td>
				<?php } else { ?>
				<td><div style="float: right; padding-right: 8px;"><a class="css_button" tabindex="-1" onClick="get_hpi('<?php echo $field_prefix; ?>hpi');" href="javascript:;"><span>Select Another HPI</span></div></a></td>
				<?php } ?>
      </tr>
    </table>
    <div style="margin: 6px;">
			<textarea name="<?php echo $field_prefix; ?>hpi" id="<?php echo $field_prefix; ?>hpi" class="FullInput mce" rows="<?php echo $rows; ?>"><?php echo htmlspecialchars($dt{$field_prefix.'hpi'}, ENT_QUOTES, '', FALSE); ?></textarea>
		</div>
<?php if(!$portal_mode && $dt['fyi_portal_hpi'] != '') { ?>
		<span class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>" ><?php echo (($portal_mode)?'Notes:':'Notes input by the patient via the portal:'); ?></span><br>
		<div style="padding: 6px;" ><textarea name="fyi_portal_hpi" id="fyi_portal_hpi" rows="<?php echo $rows; ?>" class="wmtFullInput"><?php echo htmlspecialchars($dt['fyi_portal_hpi'], ENT_QUOTES, '', FALSE); ?></textarea></div>
<?php } ?>
