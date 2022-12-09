<?php ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td colspan="2" class="wmtLabel">Hamilton Rating Scale for Anxiety</td>
		<td>&nbsp;</td>
		<!-- td><div style="float: right; padding-right: 15px"><a class="css_button_small" tabindex="-1" onclick="return ClearScreen();" href="javascript:;"><span>Clear</span></a></div></td -->
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_mood'); TotalAnxiety();">Anxious Mood</td>
		<td><select name="anx_mood" id="anx_mood" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_mood'},'Hamilton_Scale'); ?>
		</select></td>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_somatic'); TotalAnxiety();">Somatic</td>
		<td><select name="anx_somatic" id="anx_somatic" class="wmtInput" onChange="TotalAnxiety();">
			<?php echo ListSel($dt{'anx_somatic'},'Hamilton_Scale'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_tense'); TotalAnxiety();">Tension</td>
		<td><select name="anx_tense" id="anx_tense" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_tense'},'Hamilton_Scale'); ?>
		</select></td>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_cardio'); TotalAnxiety();">Cardiovascular</td>
		<td><select name="anx_cardio" id="anx_cardio" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_cardio'},'Hamilton_Scale'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_fear'); TotalAnxiety();">Fears</td>
		<td><select name="anx_fear" id="anx_fear" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_fear'},'Hamilton_Scale'); ?>
		</select></td>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_resp'); TotalAnxiety();">Respiratory</td>
		<td><select name="anx_resp" id="anx_resp" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_resp'},'Hamilton_Scale'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_insomnia'); TotalAnxiety();">Insomnia</td>
		<td><select name="anx_insomnia" id="anx_insomnia" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_insomnia'},'Hamilton_Scale'); ?>
		</select></td>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_gastro'); TotalAnxiety();">Gastrointestinal</td>
		<td><select name="anx_gastro" id="anx_gastro" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_gastro'},'Hamilton_Scale'); ?>
		</select></td>
	</tr>
	<tr>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_intellect'); TotalAnxiety();">Intellect</td>
		<td><select name="anx_intellect" id="anx_intellect" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_intellect'},'Hamilton_Scale'); ?>
		</select></td>
		<td class="wmtBody wmtClick" onclick="toggleThroughSelect('anx_genito'); TotalAnxiety();">Genitourniary</td>
		<td><select name="anx_genito" id="anx_genito" class="wmtInput" onChange="TotalAnxiety();">
		<?php echo ListSel($dt{'anx_genito'},'Hamilton_Scale'); ?>
		</select></td>
	</tr>
	<tr>
		<td colspan="4"><div class="wmtDottedB"></div></td>
	</tr>
	<tr>
		<td class="wmtLabel" colspan="2"><input name="referral" id="referral" type="checkbox" value="1" <?php echo $dt['referral'] == 1 ? 'checked' : ''; ?> /><label for="referral">&nbsp;&nbsp;Referral made</label></td> 
		<td class="wmtLabel">Total</td>
		<td><input name="anx_total" id="anx_total" class="wmtInput" type="text" value="<?php echo $dt{'anx_total'}; ?>" /></td>
	</tr>
</table>

<script type="text/javascript">
function TotalAnxiety()
{
	var tot = new Number;
	tot = document.forms[0].elements['anx_total'].value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;

  var l = document.forms[0].elements.length;
  for (i=0; i<l; i++) {
    if(document.forms[0].elements[i].type.indexOf('select') == -1) continue;
    t = parseInt(document.forms[0].elements[i].options[document.forms[0].elements[i].selectedIndex].value);
	  if(!isNaN(t)) new_tot += t;
	  new_tot= parseInt(new_tot);
  }

	document.forms[0].elements['anx_total'].value= new_tot;
}
</script>
