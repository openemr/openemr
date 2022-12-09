<?php ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td colspan="2" class="wmtLabel">This section can be read to the patient as an explanation of the questionnaire</td>
		<!-- td><div style="float: right; padding-right: 15px"><a class="css_button_small" tabindex="-1" onclick="return ClearScreen();" href="javascript:;"><span>Clear</span></a></div></td -->
	</tr>
	<tr>
		<td class="wmtBody" colspan="2">I'm going to read to you a list of questions concerning information about your potential involvement with drug, excluding alcohol and tobacco, during the last 12 months.<br><br>
When the words "drug abuse" are used, they mean the use of prescribed or over-the-counter medications/drugs in excess of the directions and any non-medical use of drugs. The various classes of drugs may include: cannabis (e.g., marijuana, hash), solvents, tranquilizers (e.g., Valium), barbituates, cocaine, stimulants (e.g., speed), hallucinogens (e.g., LSD), or narcotics (e.g., heroin). Remember that the questions <u>do not include alcohol or tobacco</u>.<br><br>
If you have difficulty with a statement, then choose the response that is mostly right.<br>
You may choose to answer or not answer any of the questions in this section.</td>
	</tr>
	<tr>
		<td colspan="4"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q1'); TotalScreen();">1. Have you used drugs other than those required for medical reasons?</td>
		<td style="width: 50px;"><select name="q1" id="q1" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q1'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q2'); TotalScreen();">2. Do you abuse more than one drug at a time?</td>
		<td><select name="q2" id="q2" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q2'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q3'); TotalScreen();">3. Are you always able to stop using drugs when you want to?&nbsp;(If you never use drugs, answer "Yes".)</td>
		<td><select name="q3" id="q3" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q3'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q4'); TotalScreen();">4. Have you had "blackouts" or "flashbacks" as a result of drug use?</td>
		<td><select name="q4" id="q4" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q4'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q5'); TotalScreen();">5. Do you ever feel bad or guilty about your drug use?&nbsp;(If you never use drugs, choose "No".)</td>
		<td><select name="q5" id="q5" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q5'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q6'); TotalScreen();">6. Does your spouse (or parents) ever complain about your involvement with drugs?</td>
		<td><select name="q6" id="q6" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q6'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q7'); TotalScreen();">7. Have you neglected your family because of your use of drugs?</td>
		<td><select name="q7" id="q7" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q7'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q8'); TotalScreen();">8. Have you engaged in illegal activities in order to obtain drugs?</td>
		<td><select name="q8" id="q8" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q8'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q9'); TotalScreen();">9. Have you ever experienced withdrawal symptoms (felt sick) when you stopped taking drugs?</td>
		<td><select name="q9" id="q9" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q9'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('q10'); TotalScreen();">10. Have you ever had medical problems as a result of your drug use?&nbsp;(e.g., memory loss, hepatitis, convulsions, bleeding, etc.)?</td>
		<td><select name="q10" id="q10" class="wmtInput" onChange="TotalScreen();">
		<?php echo ListSel($dt{'q10'},'YesNo'); ?>
		</select></td>
	</tr>
	<tr>
		<td colspan="4"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<!-- td class="wmtLabel" colspan="2"><input name="referral" id="referral" type="checkbox" value="1" <?php // echo $dt['referral'] == 1 ? 'checked' : ''; ?> /><label for="referral">&nbsp;&nbsp;Referral made</label></td --> 
		<td class="wmtLabel">Total</td>
		<td><input name="total" id="total" class="wmtInput" style="width: 50px;" type="text" value="<?php echo $dt{'total'}; ?>" /></td>
	</tr>
</table>

<script type="text/javascript">
function TotalScreen()
{
	var tot = new Number;
	tot = document.forms[0].elements['total'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
	var max = parseInt(11);

  for (i=1; i<max; i++) {
    // t = parseInt(document.forms[0].elements[i].options[document.forms[0].elements[i].selectedIndex].value);
		var s = document.getElementById('q'+i);
		t = parseInt(0);
    var chc = s.options[s.selectedIndex].value.substring(0,1);
		if(i == 3) {
			if(chc.toLowerCase() == 'n') t = parseInt(1);
		} else {
			if(chc.toLowerCase() == 'y') t = parseInt(1);
		}
	  if(!isNaN(t)) new_tot += t;
	  new_tot= parseInt(new_tot);
  }

	document.forms[0].elements['total'].value= new_tot;
}
</script>
