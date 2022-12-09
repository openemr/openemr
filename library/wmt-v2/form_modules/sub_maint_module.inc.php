<?php 
$local_fields = array();
$cnt = 1;
while($cnt < 8) {
	$local_fields[] = 'sm_opt' . $cnt;
	$local_fields[] = 'sm_opt' . $cnt . '_nt';
	$cnt++;
}
include(FORM_BRICKS . 'module_setup.inc.php');
include(FORM_BRICKS . 'module_loader.inc.php');

if($draw_display) {
?>
  <table width="100%" border="0" cellpadding="2" cellspacing="0">
		<tr>
			<td colspan="2" class="bold">Measurement to Ensure Appropriate Use:<input name="sub_maint_id" id="sub_maint_id" type="hidden" value="<?php echo $dt['sub_maint_id']; ?>" /></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td style="width: 80px;" class="text"><select name="sm_opt1" id="sm_opt1" class="text"><?php ListSel($dt{'sm_opt1'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt1');">Assessed and encouraged patient to take medication as prescribed</span></td>
		</tr>
		<tr>
			<td style="padding-top: 0px;">&nbsp;</td>
			<td style="padding-top: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Consider pill count/dose reduction</td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt1_nt" id="sm_opt1_nt" style="width: 98%;"><?php echo $dt{'sm_opt1_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td><select name="sm_opt2" id="sm_opt2"><?php ListSel($dt{'sm_opt2'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt2');">Assessed appropriateness of dosage</span></td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Buprenorphine 12 mg to 16mg combined with naloxone is recommended for maintenance</td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Doses higher than this should be an exception</td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;The need for higher dose(s) should be carefully evaluated</td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt2_nt" id="sm_opt2_nt" style="width: 98%;"><?php echo $dt{'sm_opt2_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td><select name="sm_opt3" id="sm_opt3"><?php ListSel($dt{'sm_opt3'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt3');">Conduct urine drug screens as appropriate to assess use of illicit substances</span></td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt3_nt" id="sm_opt3_nt" style="width: 98%;"><?php echo $dt{'sm_opt3_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td><select name="sm_opt4" id="sm_opt4"><?php ListSel($dt{'sm_opt4'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt4');">Assessed participation in professional counseling and support services</span></td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt4_nt" id="sm_opt4_nt" style="width: 98%;"><?php echo $dt{'sm_opt4_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td><select name="sm_opt5" id="sm_opt5"><?php ListSel($dt{'sm_opt5'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt5');">Assessed whether benefits of treatment with buprenorphine-containing products outweigh risks associated with buprenorphine-containing products</span></td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt5_nt" id="sm_opt5_nt" style="width: 98%;"><?php echo $dt{'sm_opt5_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td class="text"><select name="sm_opt6" id="sm_opt6"><?php ListSel($dt{'sm_opt6'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt6');">Assessed whether patient is making adequate progress toward treatment goals</span></td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Considered results of urine drug screens as part of the evidence of the patient complying with the treatment program</td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Consider referral to more intensive forms of treatment for patients not making progress</td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt6_nt" id="sm_opt6_nt" style="width: 98%;"><?php echo $dt{'sm_opt6_nt'}; ?></textarea></td>
		</tr>
		<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px;"></td></tr>
		<tr>
			<td class="text"><select name="sm_opt7" id="sm_opt7"><?php ListSel($dt{'sm_opt7'},'yesno'); ?></select></td>
			<td><span class="clickable text" onclick="toggleThroughSelect('sm_opt7');">Scheduled next visit at interval commensurate with patient stability</span></td>
		</tr>
		<tr>
			<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
			<td style="padding-top: 0px; padding-bottom: 0px;" class="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Weekly, or more frequent visits recommended for the first month</td>
		</tr>
		<tr>
			<td style="text-align: right;">&nbsp;</td>
			<td><textarea name="sm_opt7_nt" id="sm_opt7_nt" style="width: 98%;"><?php echo $dt{'sm_opt7_nt'}; ?></textarea></td>
		</tr>
	</table>

<?php 
} // END OF DRAW DISPLAY
?>
