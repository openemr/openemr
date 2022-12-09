<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($include_pat_summary)) $include_pat_summary=false;
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabel">Instructions:</td>
			</tr>
			<tr>
				<td colspan="3"><textarea name="<?php echo $field_prefix; ?>pat_instruct" id="<?php echo $field_prefix; ?>pat_instruct" class="FullInput mce" rows="8"><?php echo htmlspecialchars($dt{$field_prefix.'pat_instruct'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
      </tr>
			<tr>
    		<td><div style="float: left; padding-left: 10px; padding-bottom: 6px;"><a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $field_prefix; ?>pat_instruct','<?php echo $id; ?>','instruct');" tabindex="-1" class="css_button"><span>Print Patient Instructions</span></a></div></td>
				<?php if($include_pat_summary) { ?>
   			<td class="wmtNoPrint"><div style="float: left;"><a href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>','summary');" tabindex="-1" class="css_button"><span>Print Patient Summary</span></a></div></td>
				<?php } else { ?>
				<td>&nbsp;</td>
				<?php } ?>
				<td><div style="float: right; padding-right: 10px; padding-bottom: 6px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>pat_instruct');" href="javascript:;"><span>Clear the Instructions</span></a></div></td>
			</tr>
    </table>
<?php ?>
