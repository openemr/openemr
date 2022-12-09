<?php
if(!isset($field_prefix)) $field_prefix='';
if($dt[$field_prefix.'t_ace_a'] != '' || $dt[$field_prefix.'t_ace_c'] != '' ||
	$dt[$field_prefix.'t_ace_e'] != '' || $dt[$field_prefix.'t_ace_tot'] != '' ||
		$dt[$field_prefix.'t_ace_t']!= '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
			<tr>
				<td class="wmtPrnLabel" colspan="3">T-Ace Questionnaire:</td>
				<td style="width: 18px;">&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 18px;">&nbsp;</td>
        <td class="wmtPrnBody"><b>T</b>&nbsp;&nbsp;<i>Tolerance:</i>&nbsp;&nbsp;How many drinks does it take to make you feel high?</td>
				<td class="wmtPrnLabel wmtPrnR">&nbsp;<?php echo htmlspecialchars($dt{$field_prefix.'t_ace_t'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtPrnBody"><b>A</b>&nbsp;&nbsp;Have people <i>annoyed</i> you by criticizing your drinking?</td>
				<td class="wmtPrnLabel wmtPrnR">&nbsp;<?php echo ListLook($dt{$field_prefix.'t_ace_a'},'Yes_No'); ?></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtPrnBody"><b>C</b>&nbsp;&nbsp;Have you ever felt you ought to <i>cut down</i> on your drinking?</td>
				<td class="wmtPrnLabel wmtR">&nbsp;<?php echo ListLook($dt{$field_prefix.'t_ace_c'},'Yes_No'); ?></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtPrnBody"><b>E</b>&nbsp;&nbsp;<i>Eye opener</i>&nbsp;&nbsp;Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?</td>
				<td class="wmtPrnLabel wmtPrnT wmtPrnR">&nbsp;<?php echo ListLook($dt{$field_prefix.'t_ace_c'},'Yes_No'); ?></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
				<td class="wmtPrnBody"><b>Test Score:&nbsp;&nbsp;&nbsp;&nbsp;</b>The T-ACE, which is based on the CAGE, is valuable for identifying a range of use, including lifetime and prenatal use, based on the DSM-III-R criteria. A score of 2 or more is considered positive. Affirmative answers to questions A, C or E = 1 point each. Reporting tolerance to more than two drinks (the T question) = 2 points.</td>
				<td class="wmtPrnLabel wmtPrnR wmtPrnB">&nbsp;<?php echo htmlspecialchars($dt{$field_prefix.'t_ace_tot'}, ENT_QUOTES, '', FALSE); ?></td>
			</tr>
<?php
}
?>
