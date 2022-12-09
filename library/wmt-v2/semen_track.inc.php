<?php
if(!isset($disposition)) { $disposition= array(); }
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
?>
<table width="100%"	border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="bkkLabel bkkDateCell bkkAltLight bkkBorder1B">&nbsp;<?php xl('Date','e'); ?>:</td>
		<td class="bkkLabel bkkDateCell bkkAltLight bkkBorder1B">&nbsp;<?php xl('Staff Member','e'); ?>:</td>
		<td class="bkkLabel bkkAltLight bkkBorder1B">&nbsp;<?php xl('Disposition Entry','e'); ?>:</td>
		<td class="bkkLabel bkkAltLight bkkBorder1B">&nbsp;<?php xl('Location / Comment','e'); ?>:</td>
<?php if($delete_allow) { ?>
		<td class="bkkAltLight bkkBorder1B" style="width: 65px;">&nbsp;</td>
<?php } ?>
	</tr>
<?php
$cnt=1;
foreach($disposition as $track) {
	$cryo_desc = '';
	$cryo_add = '';
	if($track['disp_category'] == 'cryo') {
		$cryo_desc = ListLook($track['disp_cryo_tank'],'Cryo_Tanks');
		$tmp = ListLook($track['disp_cryo_bin'],'Tank_Bins');
		if($tmp) {
			if($cryo_desc) { $cryo_desc .= ' : '; }
			$cryo_desc .= $tmp;
		}
		$tmp = trim($track['disp_cryo_loc']);
		if($tmp) {
			if($cryo_desc) { $cryo_desc .= ' : '; }
			$cryo_desc .= $tmp;
		}
		if($track['disp_cryo_vials']) { $cryo_add = $track['disp_cryo_vials'].
				'&nbsp;Vials'; }
		if($cryo_add) { $cryo_add .= ':&nbsp;'; }
		if($track['disp_cryo_vial_amt']) {
			$cryo_add .= 'Amount- '.$track['disp_cryo_vial_amt'];
		} else {
			$cryo_add .= 'Amount Not Specified ';
		}
		if($track['disp_cryo_media']) {
			if($cryo_add) { $cryo_add .= ':&nbsp;'; }
			$cryo_add .= 'Media- '.$track['disp_cryo_media'];
		} 
		if($track['disp_cryo_media_lot']) {
			if($cryo_add) { $cryo_add .= ':&nbsp;'; }
			$cryo_add .= 'Media Lot# '.$track['disp_cryo_media_lot'];
		} 
	}
	if($track['disp_category'] == 'iui') {
		$cryo_desc = 'Patient ID: '.$track['disp_pat_id'];
		if($track['disp_pat_name']) { $cryo_desc .= ' - '.$track['disp_pat_name']; }
	}
	$disp_nt = trim($track['disp_nt']);
	if($cryo_desc && $disp_nt) { $cryo_desc .= ' - '; }
	$cryo_desc .= $disp_nt;
?>
	<tr>
		<td class="bkkDateCell" style="line-height: 0;">&nbsp;<input name="disp_date_<?php echo $cnt; ?>" id="disp_date_<?php echo $cnt; ?>" class="bkkDateInput" type="text" readonly="readonly" value="<?php echo $track['disp_date']; ?>" tabindex="-1" />
		<input name="disp_id_<?php echo $cnt; ?>" id="disp_id_<?php echo $cnt; ?>" class="bkkInput" type="hidden" value="<?php echo $track['id']; ?>" tabindex="-1" /></td>
		<td style="line-height: 0;">&nbsp;<input name="disp_user_<?php echo $cnt; ?>" id="disp_user_<?php echo $cnt; ?>" class="bkkFullInput" type="text" readonly="readonly" value="<?php echo $track['user']; ?>" tabindex="-1" /></td>
		<td style="line-height: 0;">&nbsp;<input name="disp_category_<?php echo $cnt; ?>" id="disp_category_<?php echo $cnt; ?>" class="bkkFullInput" type="text" readonly="readonly" value="<?php echo ListLook($track['disp_category'], 'Disposition_Category'); ?>" tabindex="-1" /></td>
		<td style="line-height: 0;">&nbsp;<input name="tmp_cryo_disp_<?php echo $cnt; ?>" id="tmp_cryo_disp_<?php echo $cnt; ?>" readonly="readonly" type="text" class="bkkFullInput" value="<?php echo $cryo_desc; ?>" tabindex="-1" />
	<?php if($delete_allow) { ?>
		<td style="line-height:0;"><div style="float:right; padding-right: 6px;"><a href="javascript:;" class="css_button_small" tabindex="-1" onClick="return DeleteDisposition('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');"><span><?php xl('Delete','e'); ?></span></a></div>
		</td>
	<?php } ?>
	</tr>
	<?php if($cryo_add){ ?>
	<tr>
		<td>&nbsp;</td>
		<td colspan="3" class="bkkBody"><input name="tmp_disp_add" id="tmp_disp_add" class="bkkFullInput" readonly="readonly" tabindex="-1" type="text" value="<?php echo $cryo_add; ?>" /></td>
	</tr>
	<?php } ?>
<?php
	$cnt++;
}
?>
	<tr>
		<td class="bkkDateCell"><input name="disp_date" id="disp_date" class="bkkDateInput" type="text" value="<?php echo $dt['disp_date']; ?>" onclick="setEmptyDate('disp_date');" />
		</td>
		<td class="bkkDateCell"><input name="tmp_disp_user" id="tmpe_disp_user" class="bkkFullInput" type="text" readonly="readonly" value="<?php echo $_SESSION['authUser']; ?>" /></td>
		<td><select name="disp_category" id="disp_category" class="bkkFullInput" onchange="DisplayOptionalDiv();">
			<?php ListSel($dt['disp_category'],'Disposition_Category'); ?></select>
		</td>
		<td><input name="disp_nt" id="disp_nt" class="bkkFullInput" type="text" value="<?php echo $dt['disp_nt']; ?>" /></td>
<?php if($delete_allow) { ?>
		<td>&nbsp;</td>
<?php } ?>
	</tr>
</table>
<div id="cryo_tank_display" style="<?php echo $cryo_input_display; ?>;">
<table width="100%"	border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="bkkDateCell">&nbsp;</td>
		<td class="bkkLabel" style="width: 70px;">Tank:</td>
		<td><select name="disp_cryo_tank" id="disp_cryo_tank" class="bkkInput">
			<?php ListSel($dt['disp_cryo_tank'],'Cryo_Tanks'); ?></select>
		</td>
		<td class="bkkLabel" style="width: 80px;">Bin:</td>
		<td><select name="disp_cryo_bin" id="disp_cryo_bin" class="bkkInput">
			<?php ListSel($dt['disp_cryo_bin'],'Tank_Bins'); ?></select>
		</td>
		<td class="bkkLabel" style="width: 85px;">Location:&nbsp;</td>
		<td colspan="3"><input name="disp_cryo_loc" id="disp_cryo_loc" class="bkkFullInput" type="text" value="<?php echo $dt['disp_cryo_loc']; ?>" />
		<td style="width: 65px;">&nbsp;</td>
		</td>
	</tr>
	<tr>
		<td class="bkkDateCell">&nbsp;</td>
		<td class="bkkLabel"># of Vials:</td>
		<td><input name="disp_cryo_vials" id="disp_cryo_vials" type="text" style="width: 65px;" class="bkkInput" value="<?php echo $dt['disp_cryo_vials']; ?>" /></td>
		<td class="bkkLabel">Amt Per Vial:</td>
		<td><input name="disp_cryo_vial_amt" id="disp_cryo_vial_amt" type="text" style="width: 65px;" class="bkkInput" value="<?php echo $dt['disp_cryo_vial_amt']; ?>" /></td>
		<td class="bkkLabel">Freeze Media:</td>
		<td><input name="disp_cryo_media" id="disp_cryo_media" type="text" class="bkkInput" value="<?php echo $dt['disp_cryo_media']; ?>" />
		<td class="bkkLabel" style="width: 40px;">Lot #:</td>
		<td><input name="disp_cryo_media_lot" id="disp_cryo_media_lot" type="text" style="width: 95px;" class="bkkInput" value="<?php echo $dt['disp_cryo_media_lot']; ?>" />
		<td>&nbsp;</td>
	</td>
	</tr>
</table>
</div>
<div id="iui_pat_display" style="<?php echo $iui_input_display; ?>;">
<table width="100%"	border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="bkkDateCell">&nbsp;</td>
		<td class="bkkLabel" style="width: 70px;">Patient:</td>
		<td class="bkkDateCell"><input name="disp_pat_id" id="disp_pat_id" class="bkkInput" type="text" value="<?php echo $dt['disp_pat_id']; ?>" onclick="sel_patient('disp_pat_name','disp_pat_id');" /></td>
		</td>
		<td><input name="disp_pat_name" id="disp_pat_name" class="bkkFullInput" type="text" value="<?php echo $dt['disp_pat_name']; ?>" /></td>
			<td style="width: 65px;">&nbsp;</td>
		</td>
	</tr>
	</table>
</div>
<table width="100%"	border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="bkkCollapseBar bkkBorder1T"><div style="padding-left: 8px;"><a class="css_button" onClick="return SubmitDisposition('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href='javascript:;'><span>Add Another</span></a></div></td>
		<td class="bkkCollapseBar bkkBorder1T">&nbsp;</td>
	</tr>
</table>
