<?php
if(!isset($enable_inputs)) $enable_inputs = false;
if(!isset($top_title_time)) $top_title_time = false;
?>
<div class="card mt-3 mb-3 bg-light">
  <div class="card-body pt-3 py-3">
  	<div class="d-flex align-items-center">
      <div>
         <div class="form-inline">
            <label for="form_dt"><?php echo xl('Date'); ?>:</label>
            <input name="form_dt" id="form_dt" type="text" class="form-control datepicker ml-sm-2" value="<?php echo attr(oeFormatShortDate($dt['form_dt'])); ?>" />
         </div>
      </div>
      <?php if($top_title_time) { ?>
      <div class="ml-4">
         <div class="form-inline">
            <label for="form_time"><?php echo xl('Time'); ?>:</label>
            <input name="form_time" id="form_time" type="text" class="form-control ml-sm-2" value="<?php echo attr($dt['form_time']); ?>" title="<?php echo xl('Use Military Format'); ?>" />
            <a href="javascript:;" class="css_button" tabindex="-1" onclick="ShortTimeStamp('form_time');"><span><?php echo xl('Set Time To Now'); ?></span></a>
         </div>
      </div>
    	<?php } ?>
      <div class="mr-auto ml-4">
         <div class="form-inline">
         		<label><?php echo xl('Patient'); ?>:</label>
            <span class="ml-2"><?php echo text($patient->full_name); ?></span>
         </div>
      </div>
      <div class="ml-4">
         <div class="form-inline">
         		<label><?php echo xl('DOB'); ?>:</label>
         		<?php if($enable_inputs) { ?>
							<input name="pat_dob" id="pat_dob" class="form-control datepicker" type="text" value="<?php echo attr(oeFormatShortDate($patient->DOB)); ?>" title="Use <?php echo $date_title_fmt; ?> Format" /></td>
						<?php } else { ?>
							<span class="ml-2"><?php echo text(oeFormatShortDate($patient->DOB)); ?></span>
						<?php } ?>
         </div>
      </div>
      <div class="ml-4">
         <div class="form-inline">
            <label><?php echo xl('Age'); ?>:</label>
            <span class="ml-2"><?php echo text($patient->age); ?></span>
         </div>
      </div>
      <div class="ml-4">
         <div class="form-inline">
            <label><?php echo xl('ID No'); ?>:</label>
            <span class="ml-2"><?php echo text($patient->pubpid); ?></span>
         </div>
      </div>
   	</div>
  </div>
</div>
<?php if($v_major < 5 && (!$v_minor && !$v_patch)) { ?>
<script type="text/javascript">
	Calendar.setup({inputField:"form_dt", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_form_dt"});
</script>
<?php } ?>