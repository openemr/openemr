<?php ?>
	<tr>
		<td class="wmtPrnBody">1. Have you used drugs other than those required for medical reasons?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q1'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">2. Do you abuse more than one drug at a time?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q2'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">3. Are you always able to stop using drugs when you want to?&nbsp;(If you never use drugs, answer "Yes".)</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q3'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">4. Have you had "blackouts" or "flashbacks" as a result of drug use?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q4'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">5. Do you ever feel bad or guilty about your drug use?&nbsp;(If you never use drugs, choose "No".)</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q5'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">6. Does your spouse (or parents) ever complain about your involvement with drugs?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q6'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">7. Have you neglected your family because of your use of drugs?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q7'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">8. Have you engaged in illegal activities in order to obtain drugs?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q8'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">9. Have you ever experienced withdrawal symptoms (felt sick) when you stopped taking drugs?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q9'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">10. Have you ever had medical problems as a result of your drug use?&nbsp;(e.g., memory loss, hepatitis, convulsions, bleeding, etc.)?</td>
		<td class="wmtPrnBody"><?php echo ListLook($dt{'q10'},'YesNo'); ?></td>
	</tr>
	<tr>
		<td colspan="4"><div class="wmtPrnDottedB"></div></td>
	</tr>
	<tr>
		<!-- td class="wmtLabel" colspan="2"><input name="referral" id="referral" type="checkbox" value="1" <?php // echo $dt['referral'] == 1 ? 'checked' : ''; ?> /><label for="referral">&nbsp;&nbsp;Referral made</label></td --> 
		<td class="wmtPrnLabel">Total</td>
		<td class="wmtPrnBody"><?php echo $dt{'total'}; ?></td>
	</tr>
