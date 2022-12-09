<?php ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td class="bkkLabel"><?php xl('Date Thawed','e'); ?>:</td>
				<td><input name="anl_thaw_date" id="anl_thaw_date" class="bkkDateInput" type="text" tabindex="2000" value="<?php echo $analysis['anl_thaw_date']; ?>" />&nbsp;&nbsp;
					<img src="../../pic/show_calendar.gif" width="24" height="22" id="img_thaw_dt" border="0" alt="[?]" style="cursor:pointer; vertical-align: bottom;" tabindex="-1" title="<?php xl('Click here to choose a date','e'); ?>">&nbsp;&nbsp;
				</td>
				<td>&nbsp;</td>
        <td style="width: 50%" class="bkkLabel"><?php xl('Comments','e'); ?>:</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Post-thaw Concentration','e'); ?>:</td>
				<td><input name="anl_thaw_form" id="anl_thaw_form" class="bkkInput" type="text" tabindex="2010" value="<?php echo $analysis{'anl_thaw_form'}; ?>" /></td>
				<td>&nbsp;</td>
        <td rowspan="5"><textarea name="anl_thaw_note" id="anl_thaw_note" class="bkkFullInput" tabindex="2100" rows="5"><?php echo $analysis{'anl_thaw_note'}; ?></textarea></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Post-thaw Motility','e'); ?>:</td>
				<td><input name="anl_thaw_mot" id="anl_thaw_mot" class="bkkInput" type="text" tabindex="2020" value="<?php echo $analysis{'anl_thaw_mot'}; ?>" onblur="TwoDecimal('anl_thaw_mot');" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Post-thaw Progression','e'); ?>:</td>
				<td><select name="anl_thaw_prog" id="anl_thaw_prog" class="bkkInput" tabindex="2030" onchange="LabelThawProgression();">
					<?php ListSel($analysis{'anl_thaw_prog'},'One_To_Three'); ?>
				</select></td>
				<td><span id="thaw_prog_label" style="color: red;"><?php echo $thaw_prog_label_text; ?></span></td>
			</tr>
			<tr>
        <td class="bkkLabel"><?php xl('Post-thaw TMS','e'); ?>:</td>
				<td><input name="anl_thaw_tms" id="anl_thaw_tms" class="bkkInput" type="text" tabindex="2040" value="<?php echo $analysis{'anl_thaw_tms'}; ?>" /></td>
				<td>&nbsp;</td>
			</tr>
		</table>
<?php  ?>
