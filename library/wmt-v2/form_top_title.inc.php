<?php
if(!isset($enable_inputs)) $enable_inputs = false;
if(!isset($top_title_time)) $top_title_time = false;
?>
<div style="margin: 28px 6px 6px 12px; border: solid 1px red;">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
			<td style="width: 60px;" class="wmtLabel"><?php echo xl('Date'); ?>:</td>
			<td style="width: 130px;"><input name="form_dt" id="form_dt" style="width: 120px;" type="text" class="datepicker" value="<?php echo attr(oeFormatShortDate($dt['form_dt'])); ?>" />&nbsp;
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_form_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
<?php } ?>
		<?php if($top_title_time) { ?>
		<td class="wmtLabel" style="width: 60px;"><?php echo xl('Time'); ?>:</td>
		<td><input name="form_time" id="form_time" style="width: 60px;" type="text" value="<?php echo attr($dt['form_time']); ?>" title="<?php echo xl('Use Military Format'); ?>" />
		<a href="javascript:;" class="css_button" tabindex="-1" onclick="ShortTimeStamp('form_time');"><span><?php echo xl('Set Time To Now'); ?></span></td>
		<?php } ?>
		<td>&nbsp;</td>
    <td class="wmtLabel" style="width: 100px"><?php echo xl('Patient'); ?>:</td>
		<td><?php echo text($patient->full_name); ?></td>
    <td class="wmtLabel" style="width: 40px"><?php echo xl('DOB'); ?>:</td>
		<td style="width: 130px">
		<?php if($enable_inputs) { ?>
			<input name="pat_dob" id="pat_dob" class="datepicker" style="width: 120px;" type="text" value="<?php echo attr(oeFormatShortDate($patient->DOB)); ?>" title="Use <?php echo $date_title_fmt; ?> Format" /></td>
		<?php 
		} else {
			echo text(oeFormatShortDate($patient->DOB));
		}
		 ?>
    <td class="wmtLabel" style="width: 40px"><?php echo xl('Age'); ?>:</td>
		<td style="width: 50px"><?php echo text($patient->age); ?></td>
    <td class="wmtLabel" style="width: 50px"><?php echo xl('ID No'); ?>:</td>
		<td><?php echo text($patient->pubpid); ?></td>
  </tr>
</table>
<script type="text/javascript">
Calendar.setup({inputField:"form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_form_dt"});
</script>
</div>
