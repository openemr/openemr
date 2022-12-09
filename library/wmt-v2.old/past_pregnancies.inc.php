<?php 
$delete_allow = \OpenEMR\Common\Acl\AclMain::aclCheckCore('admin','super');
if(!isset($unlink_allow)) $unlink_allow = false;;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($show_ob_totals)) $show_ob_totals = false;
if(!isset($pat_entries['fyi_pp_nt'])) 
		$pat_entries['fyi_pp_nt'] = $portal_data_layout;
if(!isset($obhist)) $obhist = array();
if(!isset($dt['pp_date_of_pregnancy'])) $dt['pp_date_of_pregnancy'] = '';
if(!isset($dt['pp_ga_weeks'])) $dt['pp_ga_weeks'] = '';
if(!isset($dt['pp_conception'])) $dt['pp_conception'] = '';
if(!isset($dt['pp_labor_length'])) $dt['pp_labor_length'] = '';
if(!isset($dt['pp_weight_lb'])) $dt['pp_weight_lb'] = '';
if(!isset($dt['pp_weight_oz'])) $dt['pp_weight_oz'] = '';
if(!isset($dt['pp_sex'])) $dt['pp_sex'] = '';
if(!isset($dt['pp_delivery'])) $dt['pp_delivery'] = '';
if(!isset($dt['pp_anes'])) $dt['pp_anes'] = '';
if(!isset($dt['pp_place'])) $dt['pp_place'] = '';
if(!isset($dt['pp_preterm'])) $dt['pp_preterm'] = '';
if(!isset($dt['pp_comment'])) $dt['pp_comment'] = '';
if(!isset($dt['pp_doc'])) $dt['pp_doc'] = '';
if(!isset($dt['fyi_pp_nt'])) $dt['fyi_pp_nt'] = '';
if(!isset($dt['db_pregnancies'])) $dt['db_pregnancies'] = $dashboard->db_pregnancies;
?>
		<table width="100%" border="0" cellspacing="0" cellpadding="<?php echo (($portal_mode)?'2':'2'); ?>">
			<tr>
				<td style="width: 5%" class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3C'); ?>">Date</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" style="width: 6%">Conception</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" style="width: 4%">GA</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" style="width: 5%">Length</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" colspan="2">Weight</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" style="width: 4%">Sex</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" style="width: 8%" rowspan="2"><?php echo ($client_id == 'mcrm' || substr($client_id,-3) == '_oh') ? 'Outcome' : 'Delivery Type'; ?></td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" style="width: 7%" rowspan="2">Anesthesia</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" style="width: 12%" rowspan="2">Place of Delivery</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" style="width: 4%">Preterm</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" style="width: 10%;" rowspan="2"><?php echo ($client_id == 'mcrm') ? 'OB/GYN ' : ''; ?>Doctor</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC':'Label3CBordL'); ?>" ><?php echo ($client_id == 'mcrm') ? 'Complications' : 'Comments'; ?> /</td>
<?php
	if($unlink_allow && $delete_allow) {
?>
				<td class="Label3CBordLB" rowspan="2" style="width: 185px">&nbsp;</td>
<?php } else if($unlink_allow || $delete_allow || $portal_mode) { ?>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" rowspan="2" style="width: 130px">&nbsp;</td>
<?php } else { ?>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" rowspan="2" style="width: 75px">&nbsp;</td>
<?php
	} 
?>
			</tr>
			<tr>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordB'); ?>">YYYY-MM</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>">Method</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>">Weeks</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>">of Labor</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" style="width: 2%">lb.</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordB'); ?>" style="width: 2%">oz.</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>">M/F</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>">Labor</td>
				<td class="<?php echo (($portal_mode)?'bkkLabel2 bkkC bkkBorder1B':'Label3CBordLB'); ?>" ><?php echo ($client_id == 'mcrm') ? 'Medications' : 'Complications'; ?></td>
			</tr>
<?php 
$cnt=1;
$bg = 'bkkLight';
$portal_data_exists = false;
if(count($obhist) > 0) {
	foreach($obhist as $preg) {
?>
 			<tr class="<?php echo (($portal_mode)? $bg : ''); ?><?php echo ((!$portal_mode && ($preg['pp_source'] == 9))?' wmtHighlight':''); ?>" >
 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<input name="pp_date_of_pregnancy_<?php echo $cnt; ?>" id="pp_date_of_pregnancy_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_date_of_pregnancy'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_date_of_pregnancy'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<select name="pp_conception_<?php echo $cnt; ?>" id="pp_conception_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>">
 						<?php ListSel($preg['pp_conception'],'PP_Conception'); ?>
 					</select>
				<?php } else { ?>
					<?php echo ListLook($preg['pp_conception'],'PP_Conception'); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<input name="pp_ga_weeks_<?php echo $cnt; ?>" id="pp_ga_weeks_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_ga_weeks'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_ga_weeks'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<input name="pp_labor_length_<?php echo $cnt; ?>" id="pp_labor_length_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_labor_length'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_labor_length'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<input name="pp_weight_lb_<?php echo $cnt; ?>" id="pp_weight_lb_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_weight_lb'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_weight_lb'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<input name="pp_weight_oz_<?php echo $cnt; ?>" id="pp_weight_oz_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_weight_oz'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_weight_oz'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<select name="pp_sex_<?php echo $cnt; ?>" id="pp_sex_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>">
 						<?php ListSel($preg['pp_sex'],'PP_Sex'); ?>
 					</select>
				<?php } else { ?>
					<?php echo ListLook($preg['pp_sex'],'PP_Sex'); ?>
				<?php } ?>
				</td>

 				<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
					<select name="pp_delivery_<?php echo $cnt; ?>" id="pp_delivery_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>">
 						<?php ListSel($preg['pp_delivery'],'PP_Delivery'); ?>
 					</select>
				<?php } else { ?>
					<?php echo ListLook($preg['pp_delivery'],'PP_Delivery'); ?>
				<?php } ?>
				</td>

 			<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
				<select name="pp_anes_<?php echo $cnt; ?>" id="pp_anes_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>">
 					<?php ListSel($preg['pp_anes'],'PP_Anesthesia'); ?>
 				</select>
				<?php } else { ?>
					<?php echo ListLook($preg['pp_anes'],'PP_Anesthesia'); ?>
				<?php } ?>
				</td>

 			<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
				<input name="pp_place_<?php echo $cnt; ?>" id="pp_place_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_place'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_place'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 		 	<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
				<select name="pp_preterm_<?php echo $cnt; ?>" id="pp_preterm_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>">
 					<?php ListSel($preg['pp_preterm'],'PP_Preterm'); ?>
 				</select>
				<?php } else { ?>
					<?php echo ListLook($preg['pp_preterm'], 'PP_Preterm'); ?>
				<?php } ?>
				</td>

 		  <td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
				<input name="pp_doc_<?php echo $cnt; ?>" id="pp_doc_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_doc'], ENT_QUOTES, '', FALSE); ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_doc'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

 			<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>">
				<?php if(!$portal_mode || ($preg['pp_source'] == 9)) { ?>
				<input name="pp_comment_<?php echo $cnt; ?>" id="pp_comment_<?php echo $cnt; ?>" class="<?php echo (($portal_mode)?'bkkFullInput2':'FullInput2'); ?>" type="text" value="<?php echo htmlspecialchars($preg['pp_comment'], ENT_QUOTES, '', FALSE); ?>" /><input name="pp_id_<?php echo $cnt; ?>" id="pp_id_<?php echo $cnt; ?>" type="hidden" value="<?php echo $preg['id']; ?>" /><input name="pp_num_links_<?php echo $cnt; ?>" id="pp_num_links_<?php echo $cnt; ?>" type="hidden" value="<?php echo $preg['num_links']; ?>" />
				<?php } else { ?>
					<?php echo htmlspecialchars($preg['pp_comment'], ENT_QUOTES, '', FALSE); ?>
				<?php } ?>
				</td>

			<td class="<?php echo (($portal_mode)?'bkkBody2':'Body2BordLB'); ?>" >
			<?php if($unlink_allow && !$portal_mode) { ?>
				<div style="display: inline-block; padding-left: 0px;"><a class="css_button_small" tabindex="-1" onClick="return UnlinkPastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" href="javascript:;" title="Unlink this history from this visit"><span>Un-Link</span></a></div>
<?php
			}
			if(!$portal_mode || ($portal_mode && ($preg['pp_source'] == 9))) { ?>
				<div style="display: inline-block; padding-left: 0px;"><a class="css_button_small" tabindex="-1" onClick="return UpdatePastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" href="javascript:;" title="Update this entry"><span>Update</span></a></div>
			<?php 
			}	
			if($delete_allow || ($portal_mode && ($preg['pp_source'] == 9))) { ?>
			<div style="display: inline-block; padding-left: 0px;"><a class="css_button_small" tabindex="-1" onClick="return DeletePastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $id; ?>');" href="javascript:;" title="Delete this entry"><span>Delete</span></a></div>
			<?php } ?>
 		</tr>
<?php
		if($preg['pp_source'] == 9) { $portal_data_exists = true; }
		$bg = ($bg == 'bkkAltLight' ? 'bkkLight' : 'bkkAltLight');
		$cnt++;
	}
}
?>

		<tr class="<?php echo ($portal_mode) ? $bg : ''; ?>">
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordB'); ?>"><input name="pp_date_of_pregnancy" id="pp_date_of_pregnancy" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_date_of_pregnancy'], ENT_QUOTES, '', FALSE); ?>" /></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><select name="pp_conception" id="pp_conception" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>">
			<?php ListSel($dt['pp_conception'],'PP_Conception'); ?>
			</select></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_ga_weeks" id="pp_ga_weeks" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_ga_weeks'], ENT_QUOTES, '', FALSE); ?>" /></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_labor_length" id="pp_labor_length" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_labor_length'], ENT_QUOTES, '', FALSE); ?>" /></td> 
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_weight_lb" id="pp_weight_lb" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_weight_lb'], ENT_QUOTES, '', FALSE); ?>" /></td> 
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_weight_oz" id="pp_weight_oz" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_weight_oz'], ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><select name="pp_sex" id="pp_sex" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>">
			<?php ListSel($dt['pp_sex'],'PP_Sex'); ?>
			</select></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><select name="pp_delivery" id="pp_delivery" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>">
			<?php ListSel($dt['pp_delivery'],'PP_Delivery'); ?>
			</select></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><select name="pp_anes" id="pp_anes" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>">
			<?php ListSel($dt['pp_anes'],'PP_Anesthesia'); ?>
			</select></td>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_place" id="pp_place" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_place'], ENT_QUOTES, '', FALSE); ?>" /></td>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><select name="pp_preterm" id="pp_preterm" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>">
			<?php ListSel($dt['pp_preterm'],'PP_Preterm'); ?>
			</select></td>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_doc" id="pp_doc" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_doc'], ENT_QUOTES, '', FALSE); ?>" /></td>
 			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>"><input name="pp_comment" id="pp_comment" class="<?php echo ($portal_mode) ? 'bkkFullInput2' : 'FullInput2'; ?>" type="text" value="<?php echo htmlspecialchars($dt['pp_comment'], ENT_QUOTES, '', FALSE); ?>" /></td>
<?php if($unlink_allow || $delete_allow || $portal_mode) { ?>
<?php } ?>
			<td class="<?php echo (($portal_mode)?'bkkBorder1B':'Body2BordLB'); ?>">&nbsp;</td>
		</tr>
		<tr>
			<td class="wmtCollapseBar wmtBorder1B" colspan="5"><a class="css_button" onClick="return SubmitPastPregnancy('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $id; ?>');" href="javascript:;"><span>Add Another</span></a></td>
<?php $cols = (($unlink_allow || $delete_allow || $portal_mode) ? 9 : 8);  ?>
			<td class="wmtCollapseBar wmtBorder1B" colspan="<?php echo $cols; ?>">
				<input name="tmp_pp_cnt" id="tmp_pp_cnt" type="hidden" value="<?php echo ($cnt-1); ?>" />&nbsp;
				<?php if(!$portal_mode && $portal_data_exists) { ?>
				<div style="float: right; padding-right: 12px;"><b><i>** Highlighted items have been entered through the portal, 'Update' to Verify/Accept</i></b></div>
				<?php } ?>
			</td>
		</tr>
<?php if($show_ob_totals) { ?>
		<tr>
			<td>&nbsp;</td>
			<td class="<?php echo (($portal_mode)?'bkkLabel bkkR':'wmtLabel wmtR'); ?>" colspan="3">Total Pregnancies:&nbsp;&nbsp;</td>
			<td colspan="3"><input name="db_pregnancies" id="db_pregnancies" type="text" class="<?php echo (($portal_mode)?'bkkInput bkkC':'wmtInput wmtC'); ?>" style="width: 60px;" value="<?php echo htmlspecialchars($dt{'db_pregnancies'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<?php if($client_id != 'sfa') { ?>
			<td colspan="2">&nbsp;</td>
			<?php } ?>
			<td class="<?php echo (($portal_mode)?'bkkLabel bkkR':'wmtLabel wmtR'); ?>" colspan="2">Total Deliveries:&nbsp;&nbsp;</td>
			<td><input name="db_deliveries" id="db_deliveries" type="text" class="<?php echo (($portal_mode)?'bkkInput bkkC':'wmtInput wmtC'); ?>" style="width: 60px;" value="<?php echo htmlspecialchars($dt{'db_deliveries'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<?php if($client_id == 'sfa') { ?>
			<td class="<?php echo (($portal_mode)?'bkkLabel bkkR':'wmtLabel wmtR'); ?>" colspan="2">Living Children:&nbsp;&nbsp;</td>
			<td><input name="db_live_births" id="db_live_births" type="text" class="<?php echo (($portal_mode)?'bkkInput bkkC':'wmtInput wmtC'); ?>" style="width: 60px;" value="<?php echo htmlspecialchars($dt{'db_live_births'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			<?php } else { ?>
			<td>&nbsp;</td>
			<?php } ?>
	<?php if($unlink_allow || $delete_allow || $portal_mode) { ?>
	<?php } ?>
			<td>&nbsp;</td>
		</tr>
<?php } ?>

<?php
$cols = (($portal_mode || $delete_allow || $unlink_allow) ? '14' : '13');
?>
	<tr>
		<td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>">Notes:</td>
	</tr>
	<tr>
		<td colspan="<?php echo $cols; ?>"><textarea name="fyi_pp_nt" id="fyi_pp_nt" rows="4" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>"><?php echo htmlspecialchars($dt['fyi_pp_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
</table>
<?php
if($pat_entries_exist && !$portal_mode) {
	if($pat_entries['fyi_pp_nt']['content'] && (strpos($dt{$field_prefix.'fyi_pp_nt'},$pat_entries['fyi_pp_nt']['content']) === false)) {
?>
		<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
		<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_<?php echo $field_prefix; ?>fyi_pp_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_pp_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
?>
