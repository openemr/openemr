<?php 
$chp_printed = TRUE;
PrintChapter($chp_title);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td style="width: 22px;">&#9670;</td>
		<td><b>If the family answers</b> "Yes"<b> or</b> "Do Not Know"<b> to ANY of the questions below then</b> TEST - IT'S THE OHIO LAW!</td>
		<td colspan="3">&nbsp;</td>
	<tr>
		<td>&nbsp;</td>
		<td><?php echo $dt['test_needed'] == 1 ? 'X' : '0'; ?>&nbsp;&nbsp;TEST! <b>at ages 1 and 2 years.</b></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><?php echo $dt['test_needed'] == 2 ? 'X' : '0'; ?>&nbsp;&nbsp;TEST! <b>between ages 3 and 6 years if the child has no test history.</b></td>
		<td class="wmtPrnC" style="width: 40px;"><b>Yes</b></td>
		<td class="wmtPrnC" style="width: 80px;"><b>Do Not Know</b></td>
		<td class="wmtPrnC" style="width: 40px;"><b>No</b></td>
	</tr>
	<tr style="border-bottom: solid 1px black;">
		<td>&#9670;</td>
		<td><b>If the family answers</b> "No"<b> to all questions, provide prevention guidance and follow up at the next visit.</b></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td><b>1.</b></td>
		<td>Is the child on Medicaid?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q1'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q1'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q1'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>2.</b></td>
		<td>Does the child live in a high zip code? (There is a reference list somewhere.)</td>
    <td class="wmtPrnC"><?php echo ($dt{'q2'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q2'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q2'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>3.</b></td>
		<td>Does the child live in or regulary visit a home, child care facility or school built before 1950?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q3'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q3'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q3'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>4.</b></td>
		<td>Does the child live in or regulary visit a home, child care facility or school built before 1978 that has deteriorated paint?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q4'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q4'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q4'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>5.</b></td>
		<td>Does the child live in or regulary visit a home built before 1978 with recent ongoing or planned renovation/remodeling?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q5'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q5'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q5'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>6.</b></td>
		<td>Does the child have a sibling or playmate that has or did have lead poisoning?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q6'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q6'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q6'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>7.</b></td>
		<td>Does the child frequently come in contact with and adult who has a hobby or works with lead? Examples are construction, welding, pottery, painting or casting ammunition.</td>
    <td class="wmtPrnC"><?php echo ($dt{'q7'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q7'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q7'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
	<tr>
		<td><b>8.</b></td>
		<td>Does the child live near an active or former lead smelter, battery recycling plant or other industry know to generate airborne lead dust?</td>
    <td class="wmtPrnC"><?php echo ($dt{'q8'} == 'y') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q8'} == 'd') ? 'X' : '0'; ?></td>
    <td class="wmtPrnC"><?php echo ($dt{'q8'} == 'n') ? 'X' : '0'; ?></td>
	</tr>
