  <table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td class="bkkLabel"><?php xl('Received By','e'); ?>:</td>
			<td style="width: 200px;"><select name="iui_receiver" id="iui_receiver" class="bkkInput">
				<?php UserSelect($dt['iui_receiver']); ?></select></td>
			<td style="width: 40%;">&nbsp;</td>
			<!--td><input name="iui_received_date" id="iui_received_date" class="bkkDateInput" type="text" value="<?php // echo $dt['iui_received_date']; ?>" />&nbsp;&nbsp;
			<img src="../../pic/show_calendar.gif" width="24" height="22" id="img_iui)rcv_date" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php // xl('Click here to choose a date','e'); ?>"></td -->
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Performed By','e'); ?>:</td>
			<td><select name="iui_performer" id="iui_performer" class="bkkInput">
				<?php ProviderSelect($dt['iui_performer']); ?></select></td>
			<!--td><input name="iui_received_date" id="iui_received_date" class="bkkDateInput" type="text" value="<?php // echo $dt['iui_received_date']; ?>" />&nbsp;&nbsp;
			<img src="../../pic/show_calendar.gif" width="24" height="22" id="img_iui)rcv_date" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" title="<?php // xl('Click here to choose a date','e'); ?>"></td -->
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Procedure Explained','e'); ?>:</td>
			<td><select name="iui_explained" id="iui_explained" class="bkkInput">
				<?php echo ListSel($dt{'iui_explained'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Patient Placed in Supine Position','e'); ?>:</td>
			<td><select name="iui_pt_supine" id="iui_pt_supine" class="bkkInput">
				<?php echo ListSel($dt{'iui_pt_supine'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Speculum Exam Done','e'); ?>:</td>
			<td><select name="iui_spec_exam" id="iui_spec_exam" class="bkkInput">
				<?php echo ListSel($dt{'iui_spec_exam'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Catheter Placed in CX - Difficulty','e'); ?>? :</td>
			<td><select name="iui_cath_difficult" id="iui_cath_difficult" class="bkkInput">
				<?php echo ListSel($dt{'iui_cath_difficult'},'YesNo'); ?></select><span class="bkkBody">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notes:</span></td>
			<td><input name="iui_cath_nt" id="iui_cath_nt" class="bkkFullInput" type="text" value="<?php echo $dt{'iui_cath_nt'}; ?>" /></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Specimen Infused Slowly Into CX','e'); ?>:</td>
			<td><select name="iui_specimen_slow" id="iui_specimen_slow" class="bkkInput">
				<?php echo ListSel($dt{'iui_specimen_slow'},'YesNo'); ?></select><span class="bkkBody">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Notes:</span></td>
			<td><input name="iui_specimen" id="iui_specimen" class="bkkFullInput" type="text" value="<?php echo $dt{'iui_specimen'}; ?>" /></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Patient Remained in Supine Position for','e'); ?>:</td>
			<td><input name="iui_supine_min" id="iui_supine_min" class="bkkDateInput" type="text" value="<?php echo $dt{'iui_supine_min'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;minutes</span></td>
		</tr>
		<tr>
			<td class="bkkLabel"><?php xl('Post Procedure Instructions Explained','e'); ?>:</td>
			<td><select name="iui_post_explained" id="iui_post_explained" class="bkkInput">
				<?php echo ListSel($dt{'iui_post_explained'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="2"><?php xl('If fresh specimen, advised sexual intercourse again this pm or within 12 to 24 hours if able','e'); ?>:</td>
			<td><select name="iui_have_sex" id="iui_have_sex" class="bkkInput">
				<?php echo ListSel($dt{'iui_have_sex'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="2"><?php xl('Instructed To Avoid Hot Tubs for 24 Hours','e'); ?>:</td>
			<td><select name="iui_hot_tub" id="iui_hot_tub" class="bkkInput">
				<?php echo ListSel($dt{'iui_hot_tub'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="2"><?php xl('Advised patient to check UPT in 12 to 14 days and call with the results','e'); ?>:</td>
			<td><select name="iui_UPT" id="iui_UPT" class="bkkInput">
				<?php echo ListSel($dt{'iui_UPT'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel">Medications:</td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="3"><textarea name="iui_meds" id="iui_meds" class="bkkFullInput" rows="3"><?php echo $dt{'iui_meds'}; ?></textarea></td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="2"><?php xl('Instructed patient to call if pain or bleeding, fever or any other concerns','e'); ?>:</td>
			<td><select name="iui_call" id="iui_call" class="bkkInput">
				<?php echo ListSel($dt{'iui_call'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="2"><?php xl('Instructed patient to check up in 12 days and call us with results','e'); ?>:</td>
			<td><select name="iui_checkup" id="iui_checkup" class="bkkInput">
				<?php echo ListSel($dt{'iui_checkup'},'YesNo'); ?></select></td>
		</tr>
		<tr>
			<td class="bkkLabel">Comments:</td>
		</tr>
		<tr>
			<td class="bkkLabel" colspan="3"><textarea name="iui_nt" id="iui_nt" class="bkkFullInput" rows="4"><?php echo $dt{'iui_nt'}; ?></textarea></td>
		</tr>
	</table>
