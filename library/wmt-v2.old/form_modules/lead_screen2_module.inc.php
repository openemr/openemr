<?php 
if($draw_display) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td style="width: 22px;">&#9670;</td>
		<td><b>If the family answers</b> "Yes"<b> or</b> "Do Not Know"<b> to ANY of the questions below then</b> TEST - IT'S THE OHIO LAW!</td>
		<td colspan="3">&nbsp;</td>
	<tr>
		<td>&nbsp;</td>
		<td><label><input name="test_needed" id="tmp_test_needed_1" type="checkbox" value="1" <?php echo $dt['test_needed'] == 1 ? 'checked' : ''; ?> onclick="TogglePair('tmp_test_needed_1', 'tmp_test_needed_2'); " />&nbsp;&nbsp;TEST! <b>at ages 1 and 2 years.</b></label></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><label><input name="test_needed" id="tmp_test_needed_2" type="checkbox" value="2" <?php echo $dt['test_needed'] == 2 ? 'checked' : ''; ?> onclick="TogglePair('tmp_test_needed_2', 'tmp_test_needed_1'); " />&nbsp;&nbsp;TEST! <b>between ages 3 and 6 years if the child has no test history.</b></label></td>
		<td class="wmtCenter" style="width: 80px;"><b>Yes</b></td>
		<td class="wmtCenter" style="width: 140px;"><b>Do Not Know</b></td>
		<td class="wmtCenter" style="width: 80px;"><b>No</b></td>
	</tr>
	<tr style="border-bottom: solid 1px black;">
		<td>&#9670;</td>
		<td><b>If the family answers</b> "No"<b> to all questions, provide prevention guidance and follow up at the next visit.</b></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td><b>1.</b></td>
		<td>Is the child on Medicaid?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q1" id="q1_yes" type="checkbox" value="y" <?php echo ($dt{'q1'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q1_yes', 'q1_do', 'q1_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q1" id="q1_do" type="checkbox" value="d" <?php echo ($dt{'q1'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q1_do', 'q1_no', 'q1_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q1" id="q1_no" type="checkbox" value="n" <?php echo ($dt{'q1'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q1_no', 'q1_do', 'q1_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>2.</b></td>
		<td>Does the child live in a high zip code? (There is a reference list somewhere.)</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q2" id="q2_yes" type="checkbox" value="y" <?php echo ($dt{'q2'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q2_yes', 'q2_do', 'q2_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q2" id="q2_do" type="checkbox" value="d" <?php echo ($dt{'q2'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q2_do', 'q2_no', 'q2_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q2" id="q2_no" type="checkbox" value="n" <?php echo ($dt{'q2'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q2_no', 'q2_do', 'q2_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>3.</b></td>
		<td>Does the child live in or regulary visit a home, child care facility or school built before 1950?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q3" id="q3_yes" type="checkbox" value="y" <?php echo ($dt{'q3'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q3_yes', 'q3_do', 'q3_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q3" id="q3_do" type="checkbox" value="d" <?php echo ($dt{'q3'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q3_do', 'q3_no', 'q3_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q3" id="q3_no" type="checkbox" value="n" <?php echo ($dt{'q3'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q3_no', 'q3_do', 'q3_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>4.</b></td>
		<td>Does the child live in or regulary visit a home, child care facility or school built before 1978 that has deteriorated paint?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q4" id="q4_yes" type="checkbox" value="y" <?php echo ($dt{'q4'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q4_yes', 'q4_do', 'q4_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q4" id="q4_do" type="checkbox" value="d" <?php echo ($dt{'q4'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q4_do', 'q4_no', 'q4_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q4" id="q4_no" type="checkbox" value="n" <?php echo ($dt{'q4'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q4_no', 'q4_do', 'q4_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>5.</b></td>
		<td>Does the child live in or regulary visit a home built before 1978 with recent ongoing or planned renovation/remodeling?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q5" id="q5_yes" type="checkbox" value="y" <?php echo ($dt{'q5'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q5_yes', 'q5_do', 'q5_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q5" id="q5_do" type="checkbox" value="d" <?php echo ($dt{'q5'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q5_do', 'q5_no', 'q5_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q5" id="q5_no" type="checkbox" value="n" <?php echo ($dt{'q5'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q5_no', 'q5_do', 'q5_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>6.</b></td>
		<td>Does the child have a sibling or playmate that has or did have lead poisoning?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q6" id="q6_yes" type="checkbox" value="y" <?php echo ($dt{'q6'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q6_yes', 'q6_do', 'q6_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q6" id="q6_do" type="checkbox" value="d" <?php echo ($dt{'q6'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q6_do', 'q6_no', 'q6_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q6" id="q6_no" type="checkbox" value="n" <?php echo ($dt{'q6'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q6_no', 'q6_do', 'q6_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>7.</b></td>
		<td>Does the child frequently come in contact with and adult who has a hobby or works with lead? Examples are construction, welding, pottery, painting or casting ammunition.</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q7" id="q7_yes" type="checkbox" value="y" <?php echo ($dt{'q7'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q7_yes', 'q7_do', 'q7_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q7" id="q7_do" type="checkbox" value="d" <?php echo ($dt{'q7'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q7_do', 'q7_no', 'q7_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q7" id="q7_no" type="checkbox" value="n" <?php echo ($dt{'q7'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q7_no', 'q7_do', 'q7_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
	<tr>
		<td><b>8.</b></td>
		<td>Does the child live near an active or former lead smelter, battery recycling plant or other industry know to generate airborne lead dust?</td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q8" id="q8_yes" type="checkbox" value="y" <?php echo ($dt{'q8'} == 'y') ? 'checked' : ''; ?> onclick="ToggleTrio('q8_yes', 'q8_do', 'q8_no'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q8" id="q8_do" type="checkbox" value="d" <?php echo ($dt{'q8'} == 'd') ? 'checked' : ''; ?> onclick="TogglePair('q8_do', 'q8_no', 'q8_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
    <td class="wmtCenter"><label>&nbsp;&nbsp;&nbsp;<input name="q8" id="q8_no" type="checkbox" value="n" <?php echo ($dt{'q8'} == 'n') ? 'checked' : ''; ?> onclick="TogglePair('q8_no', 'q8_do', 'q8_yes'); DisplayTestNeeded();"/>&nbsp;&nbsp;&nbsp;</label></td>
	</tr>
</table>

<script type="text/javascript">

function DisplayTestNeeded()
{
	// MAYBE WE PASS IN THE PATIENT AGE AND AUTO CHECK THE TOP HERE?
}
</script>
<?php
}
?>
