<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array(
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="bkkLabel" colspan="3">Questionnaire:
				<div style="float: right; "><a class="css_button" tabindex="-1" onClick="clear_epds('<?php echo $field_prefix; ?>');" href="javascript:;"><span>Clear the Questionnaire</span></a></div></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td colspan="2" class="bkkBody">Society tells us that having a baby should be a happy, exciting and joyous time, but for 15-20% of new moms, that isn't the case. We care about you and how you are feeling! Please check the response below that comes closest to how you have felt in the <b><ul>past 7 days</ul></b> (not just today). Please do not skip any questions and be sure to answer the questions on your own and without the input of others.</td>
			</tr>
			<tr>
        <td class="bkkBody"><b>1.</b>&nbsp;&nbsp;I have been able to laugh and see the funny side of things.</td>
				<td><select name="<?php echo $field_prefix; ?>epds_q_1" id="<?php echo $field_prefix; ?>epds_q_1" class="bkkInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'t_ace_t'}, ENT_QUOTES, '', FALSE); ?>" onchange="total_epds('<?php echo $field_prefix; ?>');">
					<?php ListSel($dt{$field_prefix.'epds_q_1','EPDS_List_1'); ?>
				</select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>2.</b>&nbsp;&nbsp;I have looked forward with enjoyment to things.</td>
				<td class="bkkR"><select name="<?php echo $field_prefix; ?>epds_q_2" id="<?php echo $field_prefix; ?>epds_q_2" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_2'},'EPDS_List_2'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>3.</b>&nbsp;&nbsp;I have blamed myself unnecessarily when things went wrong.
				<td class="bkkR"><select name="<?php echo $field_prefix; ?>epds_q_3" id="<?php echo $field_prefix; ?>epds_q_3" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_3'},'EPDS_List_3'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>4.</b>&nbsp;&nbsp;I have been anxious or worried for no good reason.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_4" id="<?php echo $field_prefix; ?>epds_q_4" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_4'},'EPDS_List_4'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>5.</b>&nbsp;&nbsp;I have felt scared or panicky for no very good reason.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_5" id="<?php echo $field_prefix; ?>epds_q_5" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_5'},'EPDS_List_5'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>6.</b>&nbsp;&nbsp;Things have been getting on top of me.
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_6" id="<?php echo $field_prefix; ?>epds_q_6" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_6'},'EPDS_List_6'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>7.</b>&nbsp;&nbsp;I have been so unhappy that I have had difficulty sleeping.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_7" id="<?php echo $field_prefix; ?>epds_q_7" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_7'},'EPDS_List_7'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>8.</b>&nbsp;&nbsp;I have felt sad or miserable.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_8" id="<?php echo $field_prefix; ?>epds_q_8" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_8'},'EPDS_List_8'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>9.</b>&nbsp;&nbsp;I have been so unhappy that I have been crying.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_9" id="<?php echo $field_prefix; ?>epds_q_9" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_9'},'EPDS_List_9'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
        <td class="bkkBody"><b>10.</b>&nbsp;The thought of harming myself has occurred to me.</td>
				<td class="bkkT bkkR"><select name="<?php echo $field_prefix; ?>epds_q_10" id="<?php echo $field_prefix; ?>epds_q_10" class="bkkInput" onchange="total_epds('<?php echo $field_prefix; ?>');">
				<?php ListSel($dt{$field_prefix.'epds_q_10'},'EPDS_List_10'); ?></select></td>
      </tr>
			<tr>
				<td>&nbsp;</td>
				<td class="bkkBody"><b>Test Score:&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
				<td class="wmtB"><input name="<?php echo $field_prefix; ?>epds_tot" id="<?php echo $field_prefix; ?>epds_tot" class="bkkInput" readonly="readonly" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'epds_tot'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
    </table>
		<script type="text/javascript">
function clear_epds(pre)
{
	document.getElementById(pre+'epds_tot').value = '';
	document.getElementById(pre+'epds_q_1').selectedIndex = 0;
	document.getElementById(pre+'epds_q_2').selectedIndex = 0;
	document.getElementById(pre+'epds_q_3').selectedIndex = 0;
	document.getElementById(pre+'epds_q_4').selectedIndex = 0;
	document.getElementById(pre+'epds_q_5').selectedIndex = 0;
	document.getElementById(pre+'epds_q_6').selectedIndex = 0;
	document.getElementById(pre+'epds_q_7').selectedIndex = 0;
	document.getElementById(pre+'epds_q_8').selectedIndex = 0;
	document.getElementById(pre+'epds_q_9').selectedIndex = 0;
	document.getElementById(pre+'epds_q_10').selectedIndex = 0;
}

function total_epds(pre)
{
	var tot = new Number;
	tot = document.getElementById(pre+'epds_tot').value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
	for ( var i=1; i<11; i++) {
		var sel = document.getElementById(pre+'epds_q_'+i);
		for ( var o=0; o < sel.options.length; o++) {
			if(sel.options[o].selected) t = parseInt(sel.options[o].value);
		}
		new_tot += t;
	}

	new_tot= parseInt(new_tot);
	document.getElementById(pre+'epds_tot').value= new_tot;
	return true;
}
		</script>
<?php ?>
