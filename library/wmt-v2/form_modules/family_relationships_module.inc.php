<?php
include_once($GLOBALS['srcdir'].'/wmt/wmt.class.php');
/* RETRIEVE MOST RECENT HISTORY DATA IF NOT SET BY A PRIOR MODULE */
if(!isset($hx_data) || !is_object($hx_data)) {
	$old = sqlQuery('SELECT `id`, `date` FROM `form_psyc_history` WHERE ' .
		'`pid` = ? ORDER BY `date` DESC', array($pid));
	if(!isset($old{'id'})) $old{'id'} = '';
	$hx_data = new wmtForm('psyc_history', $old{'id'});
	$pat_data = wmtPatient::getPidPatient($pid);
}
$fdf_list = explode('|',$hx_data->family_array);
$cdf_list = explode('|',$hx_data->child_array);
?>
<table width="100%"	border="0" cellspacing="2" cellpadding="0">
	<tr>
		<td>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:150px">
						Place of Birth:
					</td>
					<td class="wmtLabel" style="width:250px">
						<input name="hx_birthplace" type="text" class="wmtFullInput" style="width:200px" value="<?php echo $hx_data->birthplace; ?>" />
					</td>
					<td class="wmtLabel" style="width:120px">
						Adopted:
					</td>
					<td class="wmtRadio">
						<input name="hx_adopted_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->adopted_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_adopted_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->adopted_flag)? ' checked':''); ?> value="1" />Yes
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel">
						Siblings:
					</td>
					<td class="wmtLabel">
						<input name="hx_brothers" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->brothers; ?>" />
						<span style="margin-right:10px">brothers</span>											
						<input name="hx_sisters" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->sisters; ?>" />
						<span style="margin-right:10px">sisters</span>
					</td>											
					<td class="wmtLabel">
						Birth Order:
					</td>
					<td class="wmtLabel">
						<input name="hx_born_order" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->born_order; ?>" />
						<span style="margin-left:5px;margin-right:5px"> of </span>	
						<input name="hx_siblings" type="text" class="wmtInput" style="width:20px" value="<?php echo $hx_data->siblings; ?>" />
					</td>											
				</tr>
			</table>

			<table width="100%" border="0" cellspacing="0" cellpadding="2" style="margin-top:10px">
				<tr>
					<td class="wmtLabel" style="width:300px">
						Relationship Status of Parents During Childhood:
					</td>
					<td class="wmtLabel">
						<input name="hx_parent_marital" type="text" class="wmtFullInput" value="<?php echo $hx_data->parent_marital; ?>" />
					</td>
				</tr>

				<tr>
					<td class="wmtLabel">
						Current Relationship Status of Parents:
					</td>
					<td class="wmtLabel">
						<input name="hx_current_marital" type="text" class="wmtFullInput" value="<?php echo $hx_data->current_marital; ?>" />
					</td>
				</tr>
			</table>
			
			<fieldset>
				<legend>Family Dynamics & Function</legend>

				<table id="familyTable" width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtHeader">Name</td>
						<td class="wmtHeader">Relationship</td>
						<td class="wmtHeader">Dynamic</td>
						<td class="wmtHeader">Issues</td>
					</tr>

<?php 
	$show = "";
	for ($i=0; $i < 10; $i++) {
		if (!$fdf_list[$i] && $i > 1) $show = "style='display:none'";
		$fdf_data = ($fdf_list[$i]) ? explode('^',$fdf_list[$i]) : array(); 
		$x = $i + 1;
?>
					<tr <?php echo $show ?>>
						<td class="wmtLabel">
							<input name="hx_family_name_<?php echo $x ?>" class="wmtInput" value="<?php echo $fdf_data[0] ?>" />
						</td>
						<td class="wmtLabel">
							<select name="hx_family_relation_<?php echo $x ?>">
								<?php ListSel($fdf_data[1], 'PSYC_Family')?>
							</select>
						</td>
						<td class="wmtLabel">
							<select name="hx_family_dynamic_<?php echo $x ?>">
								<?php ListSel($fdf_data[2], 'PSYC_Dynamics')?>
							</select>
						</td>
						<td class="wmtCheck" style="width:100%">
							<?php ListCheck('hx_family_issues_'.$x,$fdf_data[3],'PSYC_Issues'); ?>
						</td>
					</tr><tr <?php echo $show ?>><td></td>
						<td class="wmtLabel" style="width:100%" colspan="3">
							<input class="wmtFullInput" name="hx_family_notes_<?php echo $x ?>" value="<?php echo $fdf_data[4] ?>" />
						</td>
					</tr>
<?php } ?>
				</table>
				<!-- input type="button" id="addFamily" value="Add Family" / -->
<script>
$("#addFamily").click(function(){
	$('tr:hidden:first','#familyTable').css('display','');
	$('tr:hidden:first','#familyTable').css('display','');
});
</script>									
			</fieldset>
			
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:150px">
						Relationship Status:
					</td>
					<td class="wmtLabel" style="width:250px">
						<select name="hx_marital">
							<?php ListSel($hx_data->marital,'marital') ?>
						</select>
					</td>
					<td class="wmtLabel" width="150px">
						Sexual Orientation:
					</td>
					<td class="wmtLabel">
						<select name="hx_orientation">
							<?php ListSel($hx_data->orientation,'PSYC_Orientation') ?>
						</select>
					</td>
				</tr>
			</table>

			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:150px">
						Marriages / Partnerships:
					</td>
					<td class="wmtLabel" style="width:80px">
						<input name="hx_marriages" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->marriages; ?>" />
					</td>
					<td class="wmtLabel" style="width:140px">
						Divorces / Separations:
					</td>
					<td class="wmtLabel" style="width:80px">
						<input name="hx_divorces" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->divorces; ?>" />
					</td>
					<td class="wmtLabel" style="width:120px">
						Number of Children:
					</td>
					<td class="wmtLabel">
						<input name="hx_children" type="text" class="wmtFullInput" style="width:30px" value="<?php echo $hx_data->children; ?>" />
					</td>
				</tr>
			</table>
								
			<fieldset>
				<legend>Child Dynamics & Function</legend>

				<table id="childTable" width="100%" border="0" cellspacing="0" cellpadding="2">
					<tr>
						<td class="wmtHeader">Name</td>
						<td class="wmtHeader">Relationship</td>
						<td class="wmtHeader">Age</td>
						<td class="wmtHeader">Dynamic</td>
						<td class="wmtHeader">Issues</td>
					</tr>

<?php 
	$show = '';
	$cdf_list = explode('|',$hx_data->child_array);
	for ($i=0; $i < 10 ; $i++) {
		if (!$cdf_list[$i] && $i > 1) $show = "style='display:none'";
		$cdf_data = ($cdf_list[$i]) ? explode('^',$cdf_list[$i]) : array(); 
		$x = $i + 1;
?>
					<tr <?php echo $show ?>>
						<td class="wmtLabel">
							<input class="wmtInput" name="hx_child_name_<?php echo $x ?>" value="<?php echo $cdf_data[0] ?>" />
						</td>
						<td class="wmtLabel">
							<select name="hx_child_relation_<?php echo $x ?>">
								<?php ListSel($cdf_data[1], 'PSYC_Children')?>
							</select>
						</td>
						<td class="wmtLabel">
							<input name="hx_child_age_<?php echo $x ?>" type="text" class="wmtFullInput" style="width:50px" value="<?php echo $cdf_data[2]; ?>" />
						</td>
						<td class="wmtLabel">
							<select name="hx_child_dynamic_<?php echo $x ?>">
								<?php ListSel($cdf_data[3], 'PSYC_Dynamics')?>
							</select>
						</td>
						<td class="wmtLabel" style="width:100%">
							<?php ListCheck('hx_child_issues_'.$x,$cdf_data[4],'PSYC_Issues'); ?>
						</td>
					</tr>
					
					<tr <?php echo $show ?>><td></td>
						<td class="wmtLabel" colspan="4">
							<input class="wmtFullInput" name="hx_child_notes_<?php echo $x ?>" value="<?php echo $cdf_data[5] ?>" />
						</td>
					</tr>
										
<?php } ?>
				</table>
				<!-- input type="button" id="addChild" value="Add Child" / -->
<script>
$("#addChild").click(function(){
	$('tr:hidden:first','#childTable').css('display','');
	$('tr:hidden:first','#childTable').css('display','');
});
</script>									
			</fieldset>
								
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="wmtLabel" style="width:450px">
						Currently Paying Child Support:
					</td>
					<td class="wmtRadio">
						<input name="hx_support_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->support_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_support_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->support_flag)? ' checked':''); ?> value="1" />Yes
						<input name="hx_support_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->support_flag)? ' checked':''); ?> value="2" />N/A
					</td>
				</tr>

				<tr>
					<td class="wmtLabel">
						Family Support for Recovery:
					</td>
					<td class="wmtRadio">
						<input name="hx_family_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->family_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_family_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->family_flag)? ' checked':''); ?> value="1" />Yes
					</td>
				</tr>
				
				<tr>
					<td class="wmtLabel" style="padding-left:25px;padding-top:0">
						If family supportive, would they participate in Family Care program?
					</td>
					<td class="wmtRadio">
						<input name="hx_care_flag" class="wmtRadio" type="radio" <?php echo ((!$hx_data->care_flag)? ' checked':''); ?> value="0" />No
						<input name="hx_care_flag" class="wmtRadio" type="radio" style="margin-left:10px" <?php echo (($hx_data->care_flag)? ' checked':''); ?> value="1" />Yes
					</td>
				</tr>
				
				
				<tr>
					<td class="wmtLabel" valign="top" style="padding-top:15px" colspan="6">
						Family & Relationship Comments:
						<textarea name="hx_family_comments" id="hx_family_comments" class="wmtFullInput" rows="4" style="height:97px"><?php echo $hx_data->family_comments; ?></textarea>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
