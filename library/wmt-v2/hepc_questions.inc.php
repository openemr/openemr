<?php 
if(!isset($hepc_print_option)) { $hepc_print_option = false; }
if(!isset($dt['db_hepc_callback'])) { $dt['db_hepc_callback'] = ''; }
if(!isset($dt['db_hepc_method_ml'])) { $dt['db_hepc_method_ml'] = ''; }
if(!isset($dt['db_hepc_method_ph'])) { $dt['db_hepc_method_ph'] = ''; }
if(!isset($patient['ss'])) { $patient['ss'] = ''; }
?>
		<table width="98%" border="0" cellspacing="0" cellpadding="0" style="margin: 4px;">
			<tr>
				<td class="wmtLabel">Have you ever been tested for Hepatitis C:</td>
				<td class="wmtBody"><input name="db_hepc_test" id="db_hepc_test_yes" type="checkbox" value="1" <?php echo (($dt{'db_hepc_test'} == '1')?' checked ':''); ?> onclick="TogglePair('db_hepc_test_yes','db_hepc_test_no');" /><label for="db_hepc_test_yes">&nbsp;Yes&nbsp;&nbsp;</label>&nbsp;&nbsp;&nbsp;
				<input name="db_hepc_test" id="db_hepc_test_no" type="checkbox" value="2" <?php echo (($dt{'db_hepc_test'} == '2')?' checked ':''); ?> onclick="TogglePair('db_hepc_test_no','db_hepc_test_yes');" /><label for="db_hepc_test_no">&nbsp;No</label></td>
				<td>
				<div style="float: right; margin-right: 10px;"><a href="javascript:;" class="css_button" tabindex="-1" onclick="toggleHepCNull();"><span>Clear Section</span></a></div>
				</td>
      </tr>
			<tr>
				<td class="wmtLabel" colspan="4">Please review the following list of risk factors and check all that apply:</td>
			</tr>
			<tr>
				<td class="wmtBody"><input name="tmp_hepc_trans" id="tmp_hepc_trans" type="checkbox" value="bld_trans" <?php echo (($dt{'tmp_hepc_trans'} == 'bld_trans')?' checked ':''); ?> /><label for="tmp_hepc_trans">&nbsp;Blood transfusion before 1992</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_dia" id="tmp_hepc_dia" type="checkbox" value="dialysis" <?php echo (($dt{'tmp_hepc_dia'} == 'dialysis')?' checked ':''); ?> /><label for="tmp_hepc_dia">&nbsp;Long term dialysis</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_use" id="tmp_hepc_use" type="checkbox" value="drug_use" <?php echo (($dt{'tmp_hepc_use'} == 'drug_use')?' checked ':''); ?> /><label for="tmp_hepc_use">&nbsp;Injectable drug use <span style="text-decoration: underline;">even once</span></label></td> 
			</tr>
			<tr>
				<td class="wmtBody"><input name="tmp_hepc_drug" id="tmp_hepc_drug" type="checkbox" value="history_drug" <?php echo (($dt{'tmp_hepc_drug'} == 'history_drug')?' checked ':''); ?> /><label for="tmp_hepc_drug">&nbsp;History of drug use</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_tat" id="tmp_hepc_tat" type="checkbox" value="tattoo" <?php echo (($dt{'tmp_hepc_tat'} == 'tattoo')?' checked ':''); ?> /><label for="tmp_hepc_tat">&nbsp;Tattoos or body piercings</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_hepc" id="tmp_hepc_hepc" type="checkbox" value="hepc" <?php echo (($dt{'tmp_hepc_hepc'} == 'hepc')?' checked ':''); ?> /><label for="tmp_hepc_hepc">&nbsp;Close contact with an individual with Hepatitis C</label></td> 
			</tr>
			<tr>
				<td class="wmtBody"><input name="tmp_hepc_sex" id="tmp_hepc_sex" type="checkbox" value="sex" <?php echo (($dt{'tmp_hepc_sex'} == 'sex')?' checked ':''); ?> /><label for="tmp_hepc_sex">&nbsp;Sex for drugs or money</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_risk" id="tmp_hepc_risk" type="checkbox" value="risk_sex" <?php echo (($dt{'tmp_hepc_risk'} == 'risk_sex')?' checked ':''); ?> /><label for="tmp_hepc_risk">&nbsp;History of high risk sex</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_jail" id="tmp_hepc_jail" type="checkbox" value="jail" <?php echo (($dt{'tmp_hepc_jail'} == 'jail')?' checked ':''); ?> /><label for="tmp_hepc_jail">&nbsp;Incarceration lasting longer than 6 months</label></td>
			</tr>
			<tr>
				<td class="wmtBody"><input name="tmp_hepc_hiv" id="tmp_hepc_hiv" type="checkbox" value="hiv" <?php echo (($dt{'tmp_hepc_hiv'} == 'hiv')?' checked ':''); ?> /><label for="tmp_hepc_hiv">&nbsp;HIV positive</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_combat" id="tmp_hepc_combat" type="checkbox" value="combat" <?php echo (($dt{'tmp_hepc_combat'} == 'combat')?' checked ':''); ?> /><label for="tmp_hepc_combat">&nbsp;Fist Fighting or combat experience</label></td> 
				<td class="wmtBody"><input name="tmp_hepc_job" id="tmp_hepc_job" type="checkbox" value="job" <?php echo (($dt{'tmp_hepc_job'} == 'job')?' checked ':''); ?> /><label for="tmp_hepc_job">&nbsp;Job Related</label></td>
			</tr>
			<tr>
				<td class="wmtBody" colspan="2"><input name="tmp_hepc_oth" id="tmp_hepc_oth" type="checkbox" value="other" <?php echo (($dt{'tmp_hepc_oth'} == 'other')?' checked ':''); ?> /><label for="tmp_hepc_oth">&nbsp;Other (provide details in the box below)</label></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3"><textarea name="db_hepc_other" id="db_hepc_other" class="wmtFullInput" rows="3"><?php echo $dt{'db_hepc_other'}; ?></textarea></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Please indicate below the best method for contacting you regarding your lab results.</td>
			</tr>
			<tr>
				<td class="wmtBody" colspan="2">
					<input name="db_hepc_method_ph" id="db_hepc_method_ph" type="checkbox" value="1" <?php echo (($dt{'db_hepc_method_ph'} == '1')?' checked ':''); ?> /><label for="db_hepc_method_ph">&nbsp;Phone call at&nbsp;</label>
					<input name="db_hepc_phone" id="db_hepc_phone" class="wmtInput" type="text" style="width: 120px;" value="<?php echo $dt{'db_hepc_phone'}; ?>" />&nbsp;&nbsp;May we leave a message asking you to call back?</td>
				<td class="wmtBody"><input name="db_hepc_callback" id="db_hepc_callback_yes" type="checkbox" value="1" <?php echo (($dt{'db_hepc_callback'} == '1')?' checked ':''); ?> onclick="TogglePair('db_hepc_callback_yes','db_hepc_callback_no');" /><label for="db_hepc_callback_yes">&nbsp;Yes&nbsp;&nbsp;</label>&nbsp;&nbsp;&nbsp;
				<input name="db_hepc_callback" id="db_hepc_callback_no" type="checkbox" value="2" <?php echo (($dt{'db_hepc_callback'} == '2')?' checked ':''); ?> onclick="TogglePair('db_hepc_callback_no','db_hepc_callback_yes');" /><label for="db_hepc_callback_no">&nbsp;No</label></td>
			</tr>
			<tr>
				<td class="wmtBody"><input name="db_hepc_method_ml" id="db_hepc_method_ml" type="checkbox" value="1" <?php echo (($dt{'db_hepc_method_ml'} == '1')?' checked ':''); ?> /><label for="db_hepc_method_ml">&nbsp;Written reminder mailed to:</label></td>
				<td colspan="2"><input name="db_hepc_addr" id="db_hepc_addr" class="wmtFullInput" type="text" value="<?php echo $dt{'db_hepc_addr'}; ?>" /></td>
			</tr>
			<tr>
				<td class="wmtBody" colspan="3">Data Collected On:&nbsp;&nbsp;
					<input name="db_hepc_dt" id="db_hepc_dt" class="wmtDateInput" type="text" value="<?php echo $dt{'db_hepc_dt'}; ?>" onblur="setEmptyDate('db_hepc_dt');" title="YYYY-MM-DD" /></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Comments:</td>
			</tr>
			<tr>
				<td colspan="3"><textarea name="db_hepc_nt" id="db_hepc_nt" class="wmtFullInput" rows="3"><?php echo $dt{'db_hepc_nt'}; ?></textarea></td>
			</tr>
			<tr><td colspan="3">&nbsp;</td></tr>
			<tr>
				<td colspan="3"><span class="wmtLabel" style="padding-right: 4px;">I have requested Hepatitis C screening. I understand that Hepatitis C is a reportable disease, that Hepatitis C testing involves a blood test and that this test, while confidential, will not be provided to individuals seeking anonymous testing. Your responses to the above are confidential, and HepC Alliance or authorized researchers may use the information you provide in research; however, no information that would make it possible to identify you will be included in any reports. In addition I authorize the County Health Department, Testing Site, and the HepC Alliance to contact me regarding the results of my lab tests.</span></td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
				<td class="wmtBorder1B"><span class="wmtLabel"><i>Signature on File</i></span><span style="float: right;" class="wmtR"><input name="db_hepc_pt_dt" id="db_hepc_pt_dt" class="wmtDateInput" value="<?php echo $dt{'db_hepc_pt_dt'}; ?>" onclick="setEmptyDate('db_hepc_pt_dt');" title="Click for today, or enter in YYYY-MM-DD format"/></span></td>
				<td>&nbsp;</td>
				<td class="wmtBorder1B"><span class="wmtLabel"><i>Signature on File</i></span><span class="wmtR" style="float: right;"><input name="db_hepc_tester_dt" id="db_hepc_tester_dt" class="wmtDateInput" value="<?php echo $dt{'db_hepc_tester_dt'}; ?>" onclick="setEmptyDate('db_hepc_tester_dt');" title="Click for today, or enter in YYYY-MM-DD format" /></span></td>
			</tr>
			<tr>
				<td><span class="wmtBody">SSN:</span><span style="float: right;" class="wmtR wmtBorder1B"><input name="db_hepc_pt_ssn" id="db_hepc_pt_ssn" class="wmtInput" style="width: 180px;" value="<?php echo $dt{'db_hepc_pt_ssn'}; ?>" onclick="setEmptyTo('db_hepc_pt_ssn','<?php echo $patient['ss']; ?>');" title="Click for SSN on file, or enter the SSN"/></span></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<?php if($hepc_print_option) { ?>
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td colspan="3" class="wmtCollapseBar wmtBorder1T">
   			<a href="javascript: submit_print_section('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','hepc');" tabindex="-1" class="css_button" <?php echo (($pop_form)?"":"onclick='top.restoreSession();'"); ?> ><span>Print This Section</span></a></td>
			</tr>
    </table>
		<?php } ?>
