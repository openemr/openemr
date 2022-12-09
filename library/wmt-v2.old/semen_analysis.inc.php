<?php 
	if(!isset($dt['tmp_notify_doc'])) { $dt['tmp_notify_doc'] = ''; }
	if(!isset($dt['tmp_notify_sean'])) { $dt['tmp_notify_sean'] = ''; }
	if(!isset($dt['tmp_notify_other'])) { $dt['tmp_notify_other'] = ''; }
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="3" style="table-layout: fixed;">
			<tr>
				<td class="bkkLabel" style="width: 160px;">Parameter</td>
				<td class="bkkLabel">Result</td>
				<td style="width: 100px;">&nbsp;</td>
				<td class="bkkLabel" style="width: 100px;">Post-wash</td>
				<td class="bkkLabel">Result</td>
				<td style="width: 100px;">&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Appearance','e'); ?>:</td>
				<td><select name="anl_appear" id="anl_appear" class="bkkInput" tabindex="1000">
					<?php ListSel($analysis{'anl_appear'},'Semen_Appearance'); ?></td>
				</select></td>
				<td>&nbsp;</td>
        <td class="bkkLabel"><?php xl('Volume','e'); ?>:</td>
				<td><input name="anl_wash_volume" id="anl_wash_volume" class="bkkDateInput" type="text" tabindex="1800" value="<?php echo $analysis{'anl_wash_volume'}; ?>" />&nbsp;&nbsp;<span class="bkkBody">mL</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Liquefaction','e'); ?>:</td>
				<td><input name="anl_liq" id="anl_liq" class="bkkDateInput" type="text" tabindex="1010" value="<?php echo $analysis{'anl_liq'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;(&lt;60&nbsp;min)</span></td>
				<td>&nbsp;</td>
        <td class="bkkLabel"><?php xl('Concentration','e'); ?>:</td>
				<td><input name="anl_wash_form" id="anl_wash_form" class="bkkDateInput" type="text" tabindex="1810" value="<?php echo $analysis{'anl_wash_form'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M/mL</span></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Viscosity','e'); ?>:</td>
				<td><select name="anl_visc" id="anl_visc" class="bkkInput" tabindex="1020" onchange="LabelViscosity();">
					<?php ListSel($analysis{'anl_visc'},'Zero_To_Three'); ?>
				</select><span class="bkkBody">&nbsp;&nbsp;(1,2)</span></td>
				<td id="visc_label" style="color: red;"><?php echo $visc_label_text; ?></td>
        <td class="bkkLabel"><?php xl('Motility','e'); ?>:</td>
				<td><input name="anl_wash_mot" id="anl_wash_mot" class="bkkDateInput" type="text" tabindex="1820" value="<?php echo $analysis{'anl_wash_mot'}; ?>" onblur="TwoDecimal('anl_wash_mot');" /><span class="bkkBody">&nbsp;&nbsp;%</span></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Color','e'); ?>:</td>
				<td><select name="anl_color" id="anl_color" class="bkkInput" tabindex="1030" onchange="LabelColor();">
					<?php ListSel($analysis{'anl_color'},'Semen_Color'); ?>
				</select><span class="bkkBody">&nbsp;&nbsp;(gray/white, yellow)</span></td>
				<td id="color_label" style="color: red;"><?php echo $color_label_text; ?></td>
        <td class="bkkLabel"><?php xl('Progression','e'); ?>:</td>
				<td><select name="anl_wash_prog" id="anl_wash_prog" class="bkkDateInput" tabindex="1830" onchange="LabelWashProgression();">
					<?php ListSel($analysis{'anl_wash_prog'},'One_To_Three'); ?></td>
				</select></td>
				<td id="wash_prog_label" style="color: red;"><?php echo $wash_prog_label_text; ?></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Volume','e'); ?>:</td>
				<td><input name="anl_volume" id="anl_volume" class="bkkDateInput" type="text" tabindex="1040" value="<?php echo $analysis{'anl_volume'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;mL (1.5-5.0 mL)</span></td>
				<td>&nbsp;</td>
        <td class="bkkLabel"><?php xl('TMS','e'); ?>:</td>
				<td><input name="anl_wash_tms" id="anl_wash_tms" class="bkkDateInput" type="text" tabindex="1840" value="<?php echo $analysis{'anl_wash_tms'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M</span></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('pH','e'); ?>:</td>
				<td><input name="anl_ph" id="anl_ph" class="bkkDateInput" type="text" tabindex="1050" value="<?php echo $analysis{'anl_ph'}; ?>" onChange="OneDecimal('anl_ph'); "/><span class="bkkBody">&nbsp;&nbsp;(&gt;7.2)</span></td>
				<td>&nbsp;</td>
        <td class="bkkLabel"><?php xl('Recovery Rate','e'); ?>:</td>
				<td><input name="anl_wash_rate" id="anl_wash_rate" class="bkkDateInput" type="text" tabindex="1850" value="<?php echo $analysis{'anl_wash_rate'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;(&gt;&nbsp;8%&nbsp;expected)</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Count ','e'); ?>1:</td>
				<td><input name="anl_cnt1" id="anl_cnt1" class="bkkDateInput" type="text" tabindex="1060" value="<?php echo $analysis{'anl_cnt1'}; ?>" onblur="CalcSpermOne();" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Count ','e'); ?>2:</td>
				<td><input name="anl_cnt2" id="anl_cnt2" class="bkkDateInput" type="text" tabindex="1070" value="<?php echo $analysis{'anl_cnt2'}; ?>" onblur="CalcSpermOne();" /><span class="bkkBody">&nbsp;&nbsp;M/mL&nbsp;(&gt;20&nbsp;M/mL)</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Concentration','e'); ?>:</td>
				<td><input name="anl_form" id="anl_form" class="bkkDateInput" type="text" tabindex="1080" value="<?php echo $analysis{'anl_form'}; ?>" /></td>
				<td>&nbsp;</td>
				<td class="bkkLabel">Inventory Used</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Total Sperm Count (TSC)','e'); ?>:</td>
				<td><input name="anl_tsc" id="anl_tsc" class="bkkDateInput" type="text" tabindex="1100" value="<?php echo $analysis{'anl_tsc'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M&nbsp;(&gt;39&nbsp;M)</span></td>
				<td>&nbsp;</td>
				<td class="bkkLabel">Gradient</td>
				<td colspan="2"><input name="anl_grad" id="anl_grad" class="bkkDateInput" type="text" tabindex="1900" value="<?php echo $analysis{'anl_grad'}; ?>" /></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Motility','e'); ?>:</td>
				<td><input name="anl_mot" id="anl_mot" class="bkkDateInput" type="text" tabindex="1110" value="<?php echo $analysis{'anl_mot'}; ?>" onblur="TwoDecimal('anl_mot'); CalcTMS();" /><span class="bkkBody">&nbsp;&nbsp;%&nbsp;(&gt;40%)</span></td>
				<td>&nbsp;</td>
				<td class="bkkLabel">Gradient Lot #</td>
				<td colspan="2"><input name="anl_grad_lot" id="anl_grad_lot" class="bkkDateInput" type="text" tabindex="1910" value="<?php echo $analysis{'anl_grad_lot'}; ?>" /></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Progression','e'); ?>:</td>
				<td><select name="anl_prog" id="anl_prog" class="bkkInput" tabindex="1120" onchange="LabelProgression();">
					<?php ListSel($analysis{'anl_prog'},'One_To_Three'); ?>
				</select><span class="bkkBody">&nbsp;&nbsp;(3)</span></td>
				<td id="prog_label" style="color: red;"><?php echo $prog_label_text; ?></td>
				<td class="bkkLabel">Wash Medium</td>
				<td colspan="2"><input name="anl_medium" id="anl_medium" class="bkkDateInput" type="text" tabindex="1920" value="<?php echo $analysis{'anl_medium'}; ?>" /></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Total Motile Sperm (TMS)','e'); ?>:</td>
				<td><input name="anl_tms" id="anl_tms" class="bkkDateInput" type="text" tabindex="1140" value="<?php echo $analysis{'anl_tms'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M&nbsp;(&gt;15.6&nbsp;M)</span></td>
				<td>&nbsp;</td>
				<td class="bkkLabel">Medium Lot #</td>
				<td colspan="2"><input name="anl_medium_lot" id="anl_medium_lot" class="bkkDateInput" type="text" tabindex="1930" value="<?php echo $analysis{'anl_medium_lot'}; ?>" /></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Agglutination','e'); ?>:</td>
				<td><select name="anl_agg" id="anl_agg" class="bkkInput" tabindex="1150" onchange="LabelAgglutination();">
					<?php ListSel($analysis{'anl_agg'},'Zero_To_Three'); ?>
				</select><span class="bkkBody">&nbsp;&nbsp;(0,1,2)</span></td>
				<td id="agg_label" style="color: red;"><?php echo $agg_label_text; ?></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Round Cells','e'); ?>:</td>
				<td><input name="anl_round" id="anl_round" class="bkkDateInput" type="text" tabindex="1160" value="<?php echo $analysis{'anl_round'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M/mL&nbsp;(&lt;&nbsp;1&nbsp;M/mL)</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Leukocytes','e'); ?>:</td>
				<td><input name="anl_leuk" id="anl_leuk" class="bkkDateInput" type="text" tabindex="1170" value="<?php echo $analysis{'anl_leuk'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;M/mL&nbsp;(&lt;1&nbsp;M/mL)</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Morphology','e'); ?>:</td>
				<td><input name="anl_morph" id="anl_morph" class="bkkDateInput" type="text" tabindex="1180" value="<?php echo $analysis{'anl_morph'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;%&nbsp;(&gt;5%&nbsp;Kruger)</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Stain Acceptable','e'); ?>:</td>
				<td><select name="anl_accept" id="anl_accept" class="bkkDateInput" tabindex="1190">
					<?php ListSel($analysis{'anl_accept'},'YesNo'); ?>
				</select></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Major Defect','e'); ?>:</td>
				<td colspan="2"><input name="anl_defect" id="anl_defect" class="bkkFullInput" type="text" tabindex="1200" value="<?php echo $analysis{'anl_defect'}; ?>" /></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('GII','e'); ?>:</td>
				<td><input name="anl_gii" id="anl_gii" class="bkkDateInput" type="text" tabindex="1210" value="<?php echo $analysis{'anl_gii'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;%&nbsp;</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('GIII','e'); ?>:</td>
				<td><input name="anl_giii" id="anl_giii" class="bkkDateInput" type="text" tabindex="1220" value="<?php echo $analysis{'anl_giii'}; ?>" /><span class="bkkBody">&nbsp;&nbsp;%&nbsp;</span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('DNA Fragmentation','e'); ?>:</td>
				<td><select name="anl_dna" id="anl_dna" class="bkkDateInput" tabindex="1230">
					<?php ListSel($analysis{'anl_dna'},'YesNo'); ?>
				</select></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Comments','e'); ?>:</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td colspan="6"><textarea name="anl_note" id="anl_note" class="bkkFullInput" tabindex="1290" rows="4"><?php echo $analysis{'anl_note'}; ?></textarea></td>
			</tr>
			<tr>
				<td colspan="6" class="bkkLabel"><input name="anl_cells" id="anl_cells" type="checkbox" tabindex="1300" <?php echo (($analysis{'anl_cells'} == '1')?'checked':''); ?> /><label for="anl_cells">&nbsp;Due to the presence of cell clumps/debris in the semen specimen, the results reported may be inaccurate.</label></td>
			</tr>
			<tr>
				<td colspan="6" class="bkkLabel"><input name="anl_decrease" id="anl_decrease" type="checkbox" tabindex="1310" <?php echo (($analysis{'anl_decrease'} == '1')?'checked':''); ?> /><label for="anl_decrease">&nbsp;Decreased sperm motility may be a result of non-viable or non-motile sperm. Clinical correlation required.</label></td>
			</tr>
			<tr>
				<td colspan="6" class="bkkLabel"><input name="tmp_notify_doc" id="tmp_notify_doc" type="checkbox" tabindex="1320" <?php echo (($dt['tmp_notify_doc'] == '1')?'checked':''); ?> /><label for="tmp_notify_doc">&nbsp;Send doctor Ahlering a message notifying him that these results are ready?</label></td>
			</tr>
			<tr>
				<td colspan="6" class="bkkLabel"><input name="tmp_notify_sean" id="tmp_notify_sean" type="checkbox" tabindex="1330" <?php echo (($dt['tmp_notify_sean'] == '1')?'checked':''); ?> /><label for="tmp_notify_sean">&nbsp;Send Dr. Gliedt a message notifying him that these results are ready?</label></td>
			</tr>
			<tr>
				<td colspan="6" class="bkkLabel"><input name="tmp_notify_other" id="tmp_notify_other" type="checkbox" tabindex="1340" <?php echo (($dt['tmp_notify_other'] == '1')?'checked':''); ?> /><label for="tmp_notify_other">&nbsp;Send a message to&nbsp;&nbsp;</label>
					<select name="tmp_notify_name" id="tmp_notify_name" class="bkkInput" tabindex="1350"><?php UserSelect($dt['tmp_notify_name']); ?></select>&nbsp;&nbsp;notifying them that these results are ready?</td>
			</tr>
		</table>
<?php if($analysis['anl_msg_trail']) { ?>
		<br>
		<span class="bkkLabel" style="padding-left: 6px;">Messages Sent:</span><br>
		<div class="bkkBody" style="padding-left: 6px;"><?php echo $analysis{'anl_msg_trail'}; ?></div><br>
<?php } ?>
