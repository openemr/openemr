<?php
if(!isset($disposition)) { $disposition= array(); }
?>
	<tr>
		<td class="bkkPrnLabel bkkPrnDateCell bkkPrnBorder1B">&nbsp;<?php xl('Date','e') ?>:</td>
		<td class="bkkPrnLabel bkkPrnDateCell bkkPrnBorder1B">&nbsp;<?php xl('Staff Member','e') ?>:</td>
		<td class="bkkPrnLabel bkkPrnBorder1B">&nbsp;<?php xl('Disposition Entry','e') ?>:</td>
		<td class="bkkPrnLabel bkkPrnBorder1B">&nbsp;<?php xl('Location / Comment','e') ?>:</td>
	</tr>
<?php
if(count($disposition) > 0) {
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
			<td class="bkkPrnBody">&nbsp;<?php echo $track['disp_date']; ?></td>
			<td class="bkkPrnBody">&nbsp;<?php echo $track['user']; ?></td>
			<td class="bkkPrnBody">&nbsp;<?php echo ListLook($track['disp_category'], 'Disposition_Category'); ?></td>
			<td class="bkkPrnBody">&nbsp;<?php echo $cryo_desc; ?></td>
		</tr>
		<?php if($cryo_add) { ?>
		<tr>
			<td class="bkkPrnBody">&nbsp;</td>
			<td class="bkkPrnBody" colspan="3"><?php echo $cryo_add; ?></td>
		</tr>
		<?php } ?>
	<?php
	 }
} else {
?>
	<tr>
		<td>&nbsp;</td>
		<td class="bkkPrnLabel">None On File</td>
		<td>&nbsp;</td>
	</tr>
<?php } ?>
