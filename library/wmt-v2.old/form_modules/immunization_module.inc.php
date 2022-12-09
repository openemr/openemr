<?php
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($noload)) $noload = FALSE;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($injection_type)) $injection_type = 'immunization';

if($draw_display) {
?>

<fieldset><legend class="wmtHeader">&nbsp;<?php echo ucfirst(xl($injection_type)); ?>&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="3">
		<tr>
			<td style="width: 50%"><table width="100%" border="0">
				<tr>
					<td class="wmtLabel"><?php echo ucfirst(xl($injection_type)); ?>:</td>
					<td colspan="3"><select name="ij1_cpt" id="ij1_cpt" class="wmtFullInput" onChange="handleDrugChoice(this);">
								<?php 
								if($link_inventory) {
									InventorySel($dt{'ij1_cpt'},'active','name',true,'-- Please Select --');
								} else {
									InjectionSel($dt{'ij1_cpt'},'Injection_CPT');
								}
								?>
							</select></td>
				</tr>
				<tr>
					<td class="wmtLabel">Lot #:</td>
					<td>
					<?php if($link_inventory) { ?>
					<select name="ij1_inv_id" id="ij1_inv_id" class="wmtFullInput" onchange="handleLotSelect(this);">
					<?php DrugLotSel($dt{'ij1_inv_id'},$dt{'ij1_cpt'},'active','lot_number',true); ?>
					</select>
					<?php } else { ?>
					<input name="ij1_lot" id="ij1_lot" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'ij1_lot'}, ENT_QUOTES,'',false); ?>" />
					<?php } ?>
					</td>
					<td class="wmtLabel">Expiration:</td>
					<td>
					<?php if($link_inventory) { ?>
					<span class="wmtBody" id="ij1_expire"><?php echo htmlspecialchars(oeFormatShortDate($expire), ENT_QUOTES, '', FALSE); ?></span>
					<?php } else { ?>
					<input name="ij1_expire" id="ij1_expire" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars(oeFormatShortDate($dt{'ij1_expire'}), ENT_QUOTES,'',false); ?>" />
					<?php } ?>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Dosage:</td>
					<td><input name="ij1_dose" id="ij1_dose" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'ij1_dose'}, ENT_QUOTES,'',false); ?>" <?php echo ($form_mode == 'new') ? 'onchange="handleDoseChange();"' : ''; ?> /></td>
					<td class="wmtLabel">Dose UoM:</td>
					<td>
						<?php 
						if($link_inventory) {
							echo '<span class="wmtBody" id="ij1_dose_unit">';
							echo $dose_unit,'</span>';
						} else {
							echo '<select name="ij1_dose_unit" id="ij1_dose_unit" class="wmtFullInput">';
							ListSel($dt{'ij1_dose_unit'},'drug_units');
							echo '</select>';
						}
						?>
					</td>
				</tr>
				<tr>
					<td class="wmtLabel">Site:</td>
					<td><select name="ij1_site" id="ij1_site" class="wmtFullInput">
						<?php ListSel($dt{'ij1_site'},'proc_body_site'); ?>
					</select></td>
					<td class="wmtLabel">Route:</td>
					<td><select name="ij1_route" id="ij1_route" class="wmtFullInput">
						<?php ListSel($dt{'ij1_route'},'drug_route'); ?>
					</select></td>
				</tr>
				<tr>
					<td class="wmtLabel">NDC #:</td>
					<td>
					<?php if($link_inventory) { ?>
					<span id="ij1_ndc"><?php echo htmlspecialchars($ndc_number, ENT_QUOTES, '', FALSE); ?></span>
					<?php } else { ?>
					<input name="ij1_ndc" id="ij1_ndc" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{'ij1_ndc'}, ENT_QUOTES,'',false); ?>" />
					<?php } ?>
					</td>
					<td class="wmtLabel">Manufacturer:</td>
					<?php if($link_inventory) { ?>
					<span id="ij1_manufacturer"><?php echo htmlspecialchars($manufacturer, ENT_QUOTES, '', FALSE); ?></span>
					<?php } else { ?>
					<td><select name="ij1_manufacturer" id="ij1_manufacturer" class="wmtFullInput"><?php ListSel($dt{'ij1_manufacturer'},'Vaccine_Manufacturers'); ?></select></td>
					<?php } ?>
				</tr>
				<?php if(!$link_inventory) { ?>
				<tr>
					<td class="wmtLabel">Source:</td>
					<td><select name="ij1_source" id="ij1_source" class="wmtFullInput">
						<?php ListSel($dt{'ij1_source'},'Injection_Source'); ?>
					</select></td>
				</tr>
				<?php } ?>
			</table></td>
			<td style="width: 50%"><table width="100%" border="0">
				<tr>
					<td class="wmtLabel">Date Immunization Information Statement Given:</td>
					<td><input name="ij1_vis_stmt" id="ij1_vis_stmt" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['ij1_vis_stmt']),ENT_QUOTES,'',FALSE); ?>" onblur="setEmptyDate('ij1_vis_stmt');" />&nbsp;&nbsp;
					<img src="../../pic/show_calendar.gif" width="24" height="22" id="img_ij1_vis_stmt" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" onblur="setEmptyDate('ij1_vis_stmt');" title="<?php xl('Click here to choose a date','e'); ?>"></td>
				</tr>
				<tr>
					<td class="wmtLabel">Date of VIS Statement:</td>
					<td><input name="ij1_vis_date" id="ij1_vis_date" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars(oeFormatShortDate($dt['ij1_vis_date']),ENT_QUOTES,'',FALSE); ?>" onblur="setEmptyDate('ij1_vis_date');" />&nbsp;&nbsp;
					<img src="../../pic/show_calendar.gif" width="24" height="22" id="img_ij1_vis_date" border="0" alt="[?]" style="cursor:pointer; vertical-align: middle;" tabindex="-1" onblur="setEmptyDate('ij1_vis_date');" title="<?php xl('Click here to choose a date','e'); ?>"></td>
				</tr>
				<tr>
						<td class="wmtLabel">Comments:</td>
				</tr>
				<tr>
					<td colspan="3"><textarea name="ij1_observation" id="ij1_observation" class="wmtFullInput" rows="2"><?php echo htmlspecialchars($dt{'ij1_observation'}, ENT_QUOTES,'',false); ?></textarea></td>
				</tr>
			</table></td>
	</table>
</fieldset>
<br/>

<div style="visibility: hidden;">
<?php 
if($link_inventory) {
	foreach($drug_units as $unit) {
		echo '<input name="drug_units_'.$unit['option_id'].'" id="drug_units_'.$unit['option_id'].'" type="hidden" value="'.$unit['title'].'" />';
		echo "\n";
	}
	foreach($drug_list as $item) {
		echo '<input name="drug_'.$item['drug_id'].'[]" id="drug_ndc_'.$item['drug_id'].'" type="hidden" value="'.$item['ndc_number'].'" />';
		echo "\n";
		echo '<input name="drug_'.$item['drug_id'].'[]" id="drug_unit_'.$item['drug_id'].'" type="hidden" value="'.$item['unit'].'" />';
		echo "\n";
		echo '<input name="drug_'.$item['drug_id'].'[]" id="drug_route_'.$item['drug_id'].'" type="hidden" value="'.$item['route'].'" />';
		echo "\n";
	}
		echo '<input name="drug_amt_remain" id="drug_amt_remain" type="hidden" value="" />';
		echo "\n";
}
?>
</div>
