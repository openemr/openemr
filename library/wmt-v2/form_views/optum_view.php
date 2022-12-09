	<tr>
		<td>1. Do you intend to quit smoking in the near future or are you trying to quit now?</td>
		<td style="width: 50px;"><?php echo htmlspecialchars(ListLook($dt{'q1'},'YesNo',''),ENT_QUOTES); ?> </td>
	</tr>
	<tr>
		<td>2. Have you tried to quit smoking in the past?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q2'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>3. Do you think smoking is harming your health</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q3'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>4. Do you have family or friends who will support you in your effort to quit?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q4'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>5. Do you find it hard to stay on track when you quit smoking?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q5'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>6. Are you worried about weight gain if you quit smoking?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q6'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>7. Are you worried about how you will deal with stress if you quit?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q7'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>8. Are you confident that you can quit smoking for good?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q8'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>9. Do you feel you are motivated to quit smoking?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q9'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>10. Do you have other smokers around you at home or at work?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q10'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td>11. Do you believe that secondhand smoke can harm your family and friends?</td>
		<td><?php echo htmlspecialchars(ListLook($dt{'q11'},'YesNo'),ENT_QUOTES); ?></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="wmtPrnLabel"><?php echo $dt['referral'] == 1 ? 'Referral Was Generated to Optum' : 'No Referral to Optum Made'; ?>
			<div style="float: right; margin-right: 18px;"><?php echo $dt['queued'] ? 'Referall Queued On: ' . $dt['queued'] : ''; ?></div></td> 
	</tr>
