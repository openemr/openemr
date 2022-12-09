<?php
if(!isset($enable_inputs)) $enable_inputs = false;
if(!isset($top_title_time)) $top_title_time = false;
?>
<div style="margin: 28px 6px 6px 12px; border: solid 1px red;">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
  <tr>
			<td style="width: 60px;" class="wmtLabel"><?php echo xl('Date'); ?>:</td>
			<td style="width: 130px;"><input name="form_dt" id="form_dt" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['form_dt']), ENT_QUOTES); ?>" />&nbsp;
			<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="24" height="22" id="img_form_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>"></td>
		<?php if($top_title_time) { ?>
		<td class="wmtLabel" style="width: 60px;"><?php echo xl('Time'); ?>:</td>
		<td><input name="form_time" id="form_time" style="width: 60px;" type="text" value="<?php echo htmlspecialchars($dt['form_time'], ENT_QUOTES); ?>" title="<?php echo xl('Use Military Format'); ?>" />
		<a href="javascript:;" class="css_button" tabindex="-1" onclick="ShortTimeStamp('form_time');"><span><?php echo xl('Set Time To Now'); ?></span></td>
		<?php } ?>
		<td>&nbsp;</td>
    <td class="wmtLabel" style="width: 100px"><?php echo xl('Patient'); ?>:</td>
		<td><?php echo htmlspecialchars($patient->full_name, ENT_QUOTES); ?></td>
    <td class="wmtLabel" style="width: 40px"><?php echo xl('DOB'); ?>:</td>
		<td style="width: 100px">
		<?php if($enable_inputs) { ?>
			<input name="pat_dob" id="pat_dob" style="width: 80px;" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($patient->DOB), ENT_QUOTES); ?>" title="Use <?php echo $date_title_fmt; ?> Format" /></td>
		<?php 
		} else {
			echo htmlspecialchars(oeFormatShortDate($patient->DOB), ENT_QUOTES);
		}
		 ?>
    <td class="wmtLabel" style="width: 40px"><?php echo xl('Age'); ?>:</td>
		<td style="width: 50px"><?php echo htmlspecialchars($patient->age, ENT_QUOTES); ?></td>
    <td class="wmtLabel" style="width: 50px"><?php echo xl('ID No'); ?>:</td>
		<td><?php echo htmlspecialchars($patient->pubpid, ENT_QUOTES); ?></td>
  </tr>
</table>
<script type="text/javascript">
Calendar.setup({inputField:"form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_form_dt"});
</script>
</div>
