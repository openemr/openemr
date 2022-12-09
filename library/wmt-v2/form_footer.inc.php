<?php
if(!isset($bill_form)) $bill_form = false;
?>
<table width="100%" border="0">
  <tr>
    <td><a id="save_and_quit" href="javascript:;" onclick="return validateForm();" tabindex='-1' class='css_button'><span><?php echo xl('Save Data'); ?></span></a></td>
    <td><a id="save_and_print" href="javascript: submit_print_form('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','','<?php echo $id; ?>');"  tabindex="-1" class="css_button"><span><?php echo xl('Printable Form'); ?></span></a></td>

<?php if(!checkSettingMode('wmt::suppress_status','',$frmdir)) { ?>
    <td class="wmtLabel"><?php echo xl('Form Status'); ?>:&nbsp;
      <select class="wmtInput" name="form_complete" id="form_complete">
        <?php ApprovalSelect($dt['form_complete'],'Form_Status',$id,'c',$approval['allowed']); ?>
      </select >
    </td>
    <td class="wmtLabel"><?php echo xl('Form Priority'); ?>:&nbsp;
      <select class="wmtInput" name="form_priority" id="form_priority">
        <?php 
				if($bill_form) {
					ListSel($dt['form_priority'],'Form_Bill');
				} else {
					ListSel($dt['form_priority'],'Form_Priority');
				}
				?>
      </select>
    </td>
<?php } else { ?>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
<?php } ?>
   	<td><a class='css_button' tabindex='-1' onclick='return cancelClicked()' href="<?php echo $pop_form  ? 'javascript: window.close();' : $GLOBALS['form_exit_url']; ?>"><span><?php echo xl('Cancel'); ?></span></a></td>

  </tr>
</table>

<?php include($GLOBALS['srcdir'].'/wmt-v2/report_signatures.inc.php'); ?>
