<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array('t_ace_t', 't_ace_a', 't_ace_c', 't_ace_e', 
	't_ace_tot');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel" colspan="3">T-Ace Questionnaire:
				<div style="float: right; "><a class="css_button" tabindex="-1" onClick="clear_t_ace('<?php echo $field_prefix; ?>');" href="javascript:;"><span>Clear the Questionnaire</span></a></div></td>
				<td style="width: 18px;">&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 18px;">&nbsp;</td>
        <td class="wmtBody"><b>T</b>&nbsp;&nbsp;<i>Tolerance:</i>&nbsp;&nbsp;How many drinks does it take to make you feel high?</td>
				<td><input name="<?php echo $field_prefix; ?>t_ace_t" id="<?php echo $field_prefix; ?>t_ace_t" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'t_ace_t'}, ENT_QUOTES, '', FALSE); ?>" onchange="total_t_ace('<?php echo $field_prefix; ?>');" title="Please Enter A Numeric Value" /></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody"><b>A</b>&nbsp;&nbsp;Have people <i>annoyed</i> you by criticizing your drinking?</td>
				<td class="wmtR"><select name="<?php echo $field_prefix; ?>t_ace_a" id="<?php echo $field_prefix; ?>t_ace_a" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'t_ace_a'},'Yes_No'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody"><b>C</b>&nbsp;&nbsp;Have you ever felt you ought to <i>cut down</i> on your drinking?</td>
				<td class="wmtR"><select name="<?php echo $field_prefix; ?>t_ace_c" id="<?php echo $field_prefix; ?>t_ace_c" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'t_ace_c'},'Yes_No'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="wmtBody"><b>E</b>&nbsp;&nbsp;<i>Eye opener</i>&nbsp;&nbsp;Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?</td>
				<td class="wmtT wmtR"><select name="<?php echo $field_prefix; ?>t_ace_e" id="<?php echo $field_prefix; ?>t_ace_e" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'t_ace_c'},'Yes_No'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
				<td class="wmtBody"><b>Test Score:&nbsp;&nbsp;&nbsp;&nbsp;</b>The T-ACE, which is based on the CAGE, is valuable for identifying a range of use, including lifetime and prenatal use, based on the DSM-III-R criteria. A score of 2 or more is considered positive. Affirmative answers to questions A, C or E = 1 point each. Reporting tolerance to more than two drinks (the T question) = 2 points.</td>
				<td class="wmtB"><input name="<?php echo $field_prefix; ?>t_ace_tot" id="<?php echo $field_prefix; ?>t_ace_tot" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'t_ace_tot'}, ENT_QUOTES, '', FALSE); ?>" title="Please Enter A Numeric Value" /></td>
			</tr>
    </table>
		<script type="text/javascript">
function clear_t_ace(pre)
{
	document.getElementById(pre+'t_ace_t').value = '';
	document.getElementById(pre+'t_ace_a').selectedIndex = 0;
	document.getElementById(pre+'t_ace_c').selectedIndex = 0;
	document.getElementById(pre+'t_ace_e').selectedIndex = 0;
}

function total_t_ace(pre)
{
	var tot = new Number;
	tot = document.getElementById(pre+'t_ace_tot').value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  // alert("T Value: "+document.getElementById(pre+'t_ace_t').value);
  t = parseInt(document.getElementById(pre+'t_ace_t').value);
	if(!isNaN(t)) {
		if(t > 2) new_tot += 2;
	}
	t = 0;
  if(document.getElementById(pre+'t_ace_a').selectedIndex == 1) t = 1;
	new_tot += t;
	// alert("New Value: "+new_tot);
	t = 0;
  if(document.getElementById(pre+'t_ace_c').selectedIndex == 1) t = 1;
	new_tot += t;
	t = 0;
  if(document.getElementById(pre+'t_ace_e').selectedIndex == 1) t = 1;
	new_tot += t;

	new_tot= parseInt(new_tot);
	document.getElementById(pre+'t_ace_tot').value= new_tot;
	return true;
}
		</script>
<?php ?>
