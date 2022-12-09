<?php
if(!isset($field_prefx)) $field_prefix = '';
$this_module = 'sub_maint';
$this_table = 'form_sub_maint';
include(FORM_BRICKS . 'module_loader.inc.php');
$chp_printed = printChapter($chp_title, FALSE);
?>
	<tr>
		<td colspan="2" class="bold">&nbsp;&nbsp;Measurement to Ensure Appropriate Use:</td>
	</tr>
	<tr>
		<td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
		<td class="bold" style="width: 80px;">&nbsp;<?php echo ListLook($dt{'sm_opt1'},'yesno'); ?></td>
		<td class="bold">Assessed and encouraged patient to take medication as prescribed</td>
	</tr>
	<tr>
		<td style="padding-top: 0px;">&nbsp;</td>
		<td style="padding-top: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Consider pill count/dose reduction</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt1_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt2'},'yesno'); ?></td>
		<td class="bold">Assessed appropriateness of dosage</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Buprenorphine 12 mg to 16mg combined with naloxone is recommended for maintenance</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Doses higher than this should be an exception</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;The need for higher dose(s) should be carefully evaluated</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt2_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt3'},'yesno'); ?></td>
		<td class="bold">Conduct urine drug screens as appropriate to assess use of illicit substances</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt3_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt4'},'yesno'); ?></td>
		<td class="bold">Assessed participation in professional counseling and support services</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt4_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt5'},'yesno'); ?></td>
		<td class="bold">Assessed whether benefits of treatment with buprenorphine-containing products outweigh risks associated with buprenorphine-containing products</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt5_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt6'},'yesno'); ?></td>
		<td class="bold">Assessed whether patient is making adequate progress toward treatment goals</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Considered results of urine drug screens as part of the evidence of the patient complying with the treatment program</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Consider referral to more intensive forms of treatment for patients not making progress</td>
	<tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt6_nt'}; ?></td>
	</tr>
	<tr><td colspan="2"><div style="border-bottom: solid 1px grey; height: 3px; margin-top: 4px; margin-bottom: 6px; margin-left: 10px; margin-right: 10px;"></td></tr>
	<tr>
		<td class="bold">&nbsp;<?php echo ListLook($dt{'sm_opt7'},'yesno'); ?></td>
		<td class="bold">Scheduled next visit at interval commensurate with patient stability</td>
	</tr>
	<tr>
		<td style="padding-top: 0px; padding-bottom: 0px;">&nbsp;</td>
		<td style="padding-top: 0px; padding-bottom: 0px;" class="bold">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&#8227;&nbsp;&nbsp;Weekly, or more frequent visits recommended for the first month</td>
	</tr>
	<tr>
		<td style="text-align: right;">&nbsp;</td>
		<td class="text" style="width: 98%;"><?php echo $dt{'sm_opt7_nt'}; ?></td>
	</tr>
