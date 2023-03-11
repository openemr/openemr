<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'pat_blood_type', 'pat_rh_factor', 'last_chol', 
	'last_lipid', 'last_hepc', 'last_lipo', 'last_tri', 'last_urine_alb', 
	'last_hgba1c', 'last_hgba1c_val'
);
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Blood&nbsp;&amp;&nbsp;Urine Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Blood Type:</td>
			<td colspan="2"><select name="<?php echo $field_prefix; ?>pat_blood_type" id="<?php echo $field_prefix; ?>pat_blood_type" class="wmtInput" style="width: 60px;">
				<?php ListSel($dt{$field_prefix.'pat_blood_type'},'Blood_Types'); ?>
			</select>
			&nbsp;&nbsp;<select name="<?php echo $field_prefix; ?>pat_rh_factor" id="<?php echo $field_prefix; ?>pat_rh_factor" class="wmtInput" style="width: 90px;">
				<?php ListSel($dt{$field_prefix.'pat_rh_factor'},'RH_Factor'); ?>
			</select></td>
			<td>Last Cholesterol Check:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_chol" id="<?php echo $field_prefix; ?>last_chol" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_chol'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_chol" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_chol", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_chol"});
			</script>
			<td>Last Hepatitis C Test:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_hepc" id="<?php echo $field_prefix; ?>last_hepc" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_hepc'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hepc" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hepc", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_hepc"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('pat_blood_type','pat_rh_factor','last_chol','last_hepc');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2"><span class="wmtBorderHighlight" style="width: 45px; padding-right: 25px;" id="tmp_<?php echo $field_prefix; ?>pat_blood_type" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['pat_blood_type']['content'],'Blood_Types'), ENT_QUOTES); ?></span>
				&nbsp;&nbsp;<span class="wmtBorderHighlight" style="width: 45px; padding-right: 30px;" id="tmp_<?php echo $field_prefix; ?>pat_rh_factor" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['pat_rh_factor']['content'],'RH_Factor'), ENT_QUOTES); ?></span></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_chol" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_chol']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_hepc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_hepc']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td>Last Lipid Panel:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_lipid" id="<?php echo $field_prefix; ?>last_lipid" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_lipid'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_lipid" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_lipid", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_lipid"});
				</script>
			<td style="width: 22%;">Last Lipoprotein:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_lipo" id="<?php echo $field_prefix; ?>last_lipo" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_lipo'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_lipo" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_lipo", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_lipo"});
				</script>
			<td style="width: 22%;">Last Triglycerides:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_tri" id="<?php echo $field_prefix; ?>last_tri" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_tri'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_tri" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_tri", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_tri"});
				</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_lipid','last_lipo','last_tri');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_lipid" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_lipid']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_lipo" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_lipo']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_tri" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_tri']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td>Last Urine Micro Alb:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_urine_alb" id="<?php echo $field_prefix; ?>last_urine_alb" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_urine_alb'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_urine_alb" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_urine_alb", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_urine_alb"});
				</script>
			<td>Last HgbA1c:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_hgba1c" id="<?php echo $field_prefix; ?>last_hgba1c" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(($dt{$field_prefix.'last_hgba1c'}),ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hgba1c" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hgba1c", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_hgba1c"});
				</script>
			<td>Last HgbA1c Value:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_hgba1c_val" id="<?php echo $field_prefix; ?>last_hgba1c_val" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hgba1c_val'},ENT_QUOTES); ?>" /></td>
			<td>&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_urine_alb','last_hgba1c','last_hgba1c_val');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_urine_alb" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_urine_alb']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_hgba1c" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(($pat_entries['last_hgba1c']['content']), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_hgba1c_val" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hgba1c_val']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
