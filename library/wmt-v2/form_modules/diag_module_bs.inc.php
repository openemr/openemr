<?php
if(!isset($field_prefix)) $field_prefix='';
?>
		<?php include($GLOBALS['srcdir'].'/wmt-v2/diagnosis_bs.inc.php'); ?> 

    <div class="form-row">
	    <div class="form-group col-lg-12">
	    	<div>
	    		<label><?php echo xl('Other Plan Notes'); ?></label>
	    		<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('<?php echo $field_prefix; ?>plan');" href="javascript:;"><span><?php echo xl('Clear the Plan'); ?></span></a></div>
	    	</div>
			  <textarea name="<?php echo $field_prefix; ?>plan" id="<?php echo $field_prefix; ?>plan" class="form-control" placeholder="Other Plan Notes" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'plan'}, ENT_QUOTES, '', FALSE); ?></textarea>
	    </div>
	  </div>
<?php ?>
