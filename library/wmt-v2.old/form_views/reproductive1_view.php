<?php 
$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
</table>
<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="wmtPrnHeader">&nbsp;Reproductive Life Plan&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 74px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_soon_preg'},'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">Do you want to become pregnant within the next year?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_soon_preg_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_want_kids'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Do you want to have children one day?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_want_kids_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
</table>
<?php if(strtolower(substr($dt{'rp1_want_kids'},0,1)) == 'y') { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 74px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_bc_chc'}, 'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">Are you currently using birth control?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_bc'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_how_many_kids'}, ENT_QUOTES, '', FALSE); ?></td>
		<td class="wmtPrnBody">How many children would you like?</td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_age_to_have'}, ENT_QUOTES, '', FALSE); ?></td>
		<td class="wmtPrnBody">What age would you like to have children?</td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_age_apart'}, ENT_QUOTES, '', FALSE); ?></td>
		<td class="wmtPrnBody bkkL">How far apart would you like your children to be?</td>
	</tr>
</table>
<?php } ?>
<?php if(strtolower(substr($dt{'rp1_want_kids'},0,1)) == 'n') { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 74px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_bc_chc'}, 'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">Are you currently using birth control?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_bc'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td class="wmtPrnBody">What will you do if you become pregnant?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rpt=1_get_preg_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
</table>
<?php } ?>
</fieldset>
<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="wmtPrnHeader">&nbsp;Dietary Information&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 55px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_diet_unhealthy'},'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">Do you tend to eat an unhealthy diet?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_diet_unhealthy_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_diet_overeat'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Do you tend to over eat?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_diet_overeat_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_diet_undereat'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Do you tend to under eat?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_diet_undereat_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
</table>
</fieldset>
<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="wmtPrnHeader">&nbsp;Emotional Health&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 55px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_emo_bounce'},'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">When you feel sad do you bounce back quickly?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_emo_bounce_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_emo_sad'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Do you feel sad for more than two weeks at a time?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_emo_sad_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_emo_anx'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Do you feel nervous, anxious or worried?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_emo_anx_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_emo_abuse'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Is there anyone in your life that is physically abusive?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_emo_abuse_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_emo_mean'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Is there anyone in your life who often says hurtful or mean things?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_emo_mean_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
</table>
</fieldset>
<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="wmtPrnHeader">&nbsp;Personal Goals&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 55px;" class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_vitamin_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody" style="width: 350px;">Take a daily vitamin?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_vitamin_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_smoke_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Quit or reduce the amount I smoke?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_smoke_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_condom_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Increase or always use a condom?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_condom_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_bc_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Use birth control continuously?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_bc_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_exercise_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Start or increase amount of exercise?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_exercise_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_gain_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Increase my weight?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_gain_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_lose_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Decrease my weight?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_lose_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_maintain_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Maintain my weight?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_maintain_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_alcohol_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Quit or reduce the amount of alcohol I use?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_alcohol_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<tr>
		<td class="wmtPrnBody">&nbsp;<?php echo ListLook($dt{'rp1_pg_drug_chc'},'YesNo'); ?></td>
		<td class="wmtPrnBody">Quit or reduce the amount of drugs I use?</td>
		<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pg_drug_nt'}, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
</table>
</fieldset>
<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="wmtPrnHeader">&nbsp;Professional Goals&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtPrnBody" style="width: 150px;">Education Plan:<td>
			<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pro_ed'}, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
		<tr>
			<td class="wmtPrnBody">Employment Plan:<td>
			<td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'rp1_pro_emp'}, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
	</table>
</fieldset>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
