<?php
$flds = sqlListFields('form_breast_exam');
foreach($flds as $fld) {
	if(substr($fld,0,4) != 'bre_') continue;
	if(!isset($dt[$fld])) $dt[$fld] = '';
}
if($form_mode == 'new') {
  $fres = sqlQuery("SELECT * FROM form_breast_exam WHERE pid = ?".
		" ORDER BY form_dt DESC LIMIT 1", array($pid));
	foreach($fres as $fld => $val) {
		if(substr($fld,0,4) != 'bre_') continue;
		$dt[$fld] = $val;
	}
} else if($form_mode == 'update') {
  $fres = sqlQuery("SELECT * FROM form_breast_exam WHERE link_id=?".
		" AND link_form=?", array($id, $frmdir));
	foreach($fres as $fld => $val) {
		if(substr($fld,0,4) != 'bre_') continue;
		$dt[$fld] = $val;
	}
} else {
	if($id) {
		unset($gyn);
		$gyn = array();
		$gyn['link_id'] = $id;
		$gyn['link_form'] = $frmdir;
		$gyn['form_dt'] = $dt['form_dt'];
		foreach($_POST as $k => $var) {
			if(substr($k,0,4) != 'bre_') continue;
			if(is_string($var)) $var = trim($var);
			$gyn[$k] = $var;
		}
		$exists = sqlQuery('SELECT * FROM form_breast_exam WHERE pid=? AND '.
			'link_id=? AND link_form = ?', array($pid, $id, $frmdir));
		if(!isset($exists{'id'})) $exists{'id'} = '';
		if($exists{'id'}) {
  		$binds = array($_SESSION['authProvider'], $_SESSION['authUser'],
							$_SESSION['userauthorized']);
  		$q1 = '';
  		foreach ($gyn as $key => $val){
    		$q1 .= "$key=?, ";
				$binds[] = $val;
  		}
			$binds[] = $pid;
			$binds[] = $frmdir;
			$binds[] = $id;
  		sqlStatement('UPDATE form_breast_exam SET groupname=?, user=?, '.
						"authorized=?, activity=1, $q1 date=NOW() WHERE pid=? ".
						'AND link_form=? AND link_id=?', $binds);
		} else {
  		wmtFormSubmit('form_breast_exam',$gyn,'',$_SESSION['userauthorized'],$pid);
		}
	}
}
if($form_mode != 'save' || $continue) {
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 50%;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="3" class="wmtLabelRed">Breast Exam Dated: <?php echo oeFormatShortDate($dt{'form_dt'}); ?></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Right Breast</td>
			</tr>
			<tr>
				<td><select name="bre_br_axil" id="bre_br_axil" class="wmtInput">
				<?php ListSel($dt{'bre_br_axil'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_br_axil');">Axillary Nodes</td>
				<td><input name="bre_br_axil_nt" id="bre_br_axil_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_br_axil_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_br_mass" id="bre_br_mass" class="wmtInput">
				<?php ListSel($dt{'bre_br_mass'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_br_mass');">Mass</td>
				<td><input name="bre_br_mass_nt" id="bre_br_mass_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_br_mass_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_br_imp" id="bre_br_imp" class="wmtInput">
				<?php ListSel($dt{'bre_br_imp'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_br_imp');">Implant</td>
				<td><input name="bre_br_imp_nt" id="bre_br_imp_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_br_imp_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_br_rmv" id="bre_br_rmv" class="wmtInput">
				<?php ListSel($dt{'bre_br_rmv'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_br_rmv');">Masectomy</td>
				<td><input name="bre_br_rmv_nt" id="bre_br_rmv_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_br_rmv_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td>Notes:</td>
				<td colspan="2"><textarea name="bre_br_nt" id="bre_br_nt" class="wmtFullInput" rows="3"><?php echo $dt{'bre_br_nt'}; ?></textarea></td>
			</tr>
		
			<tr>
			<td class="wmtLabel" colspan="3">Right Nipple</td>
			</tr>
			<tr>
				<td><select name="bre_nr_ev" id="bre_nr_ev" class="wmtInput">
				<?php ListSel($dt{'bre_nr_ev'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nr_ev');">Everted</td>
				<td><input name="bre_nr_ev_nt" id="bre_nr_ev_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nr_ev_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nr_in" id="bre_nr_in" class="wmtInput">
				<?php ListSel($dt{'bre_nr_in'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nr_in');">Inverted</td>
				<td><input name="bre_nr_in_nt" id="bre_nr_in_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nr_in_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nr_mass" id="bre_nr_mass" class="wmtInput">
				<?php ListSel($dt{'bre_nr_mass'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nr_mass');">Mass</td>
				<td><input name="bre_nr_mass_nt" id="bre_nr_mass_nt" class="wmtFullInput" type="text" value="<?php echo $dt{'bre_nr_mass_nt'}; ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nr_dis" id="bre_nr_dis" class="wmtInput">
				<?php ListSel($dt{'bre_nr_dis'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nr_dis');">Discharge</td>
				<td><input name="bre_nr_dis_nt" id="bre_nr_dis_nt" class="wmtFullInput" type="text" value="<?php echo $dt{'bre_nr_dis_nt'}; ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nr_ret" id="bre_nr_ret" class="wmtInput">
				<?php ListSel($dt{'bre_nr_ret'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nr_ret');">Retraction</td>
				<td><input name="bre_nr_ret_nt" id="bre_nr_ret_nt" class="wmtFullInput" type="text" value="<?php echo $dt{'bre_nr_ret_nt'}; ?>" /></td>
			</tr>
			<tr>
				<td>Notes:</td>
				<td colspan="2"><textarea name="bre_nr_nt" id="bre_nr_nt" class="wmtFullInput" rows="2"><?php echo $dt{'bre_nr_nt'}; ?></textarea></td>
			</tr>
		</table></td>

		<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td colspan="3"><div style="float:left; pedding-left: 12px;"><a class="css_button" tabindex="-1" onClick="toggleBreastExamNull();" href="javascript:;"><span>Clear Exam</span></a></div>
				<div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="toggleBreastExamNormal();" href="javascript:;"><span>Set Normal</span></a></div></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Left Breast</td>
			</tr>
			<tr>
				<td><select name="bre_bl_axil" id="bre_bl_axil" class="wmtInput">
				<?php ListSel($dt{'bre_bl_axil'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_bl_axil');">Axillary Nodes</td>
				<td><input name="bre_bl_axil_nt" id="bre_bl_axil_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_bl_axil_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_bl_mass" id="bre_bl_mass" class="wmtInput">
				<?php ListSel($dt{'bre_bl_mass'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_bl_mass');">Mass</td>
				<td><input name="bre_bl_mass_nt" id="bre_bl_mass_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_bl_mass_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_bl_imp" id="bre_bl_imp" class="wmtInput">
				<?php ListSel($dt{'bre_bl_imp'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_bl_imp');">Implant</td>
				<td><input name="bre_bl_imp_nt" id="bre_bl_imp_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_bl_imp_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_bl_rmv" id="bre_bl_rmv" class="wmtInput">
				<?php ListSel($dt{'bre_bl_rmv'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_bl_rmv');">Masectomy</td>
				<td><input name="bre_bl_rmv_nt" id="bre_bl_rmv_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_bl_rmv_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td>Notes:</td>
				<td colspan="2"><textarea name="bre_bl_nt" id="bre_bl_nt" class="wmtFullInput" rows="3"><?php echo htmlspecialchars($dt{'bre_bl_nt'}, ENT_QUOTES); ?></textarea></td>
			</tr>
			<tr>
				<td class="wmtLabel" colspan="3">Left Nipple</td>
			</tr>
			<tr>
				<td><select name="bre_nl_ev" id="bre_nl_ev" class="wmtInput">
				<?php ListSel($dt{'bre_nl_ev'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nl_ev');">Everted</td>
				<td><input name="bre_nl_ev_nt" id="bre_nl_ev_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nl_ev_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nl_in" id="bre_nl_in" class="wmtInput">
				<?php ListSel($dt{'bre_nl_in'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nl_in');">Inverted</td>
				<td><input name="bre_nl_in_nt" id="bre_nl_in_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nl_in_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nl_mass" id="bre_nl_mass" class="wmtInput">
				<?php ListSel($dt{'bre_nl_mass'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nl_mass');">Mass</td>
				<td><input name="bre_nl_mass_nt" id="bre_nl_mass_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nl_mass_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nl_dis" id="bre_nl_dis" class="wmtInput">
				<?php ListSel($dt{'bre_nl_dis'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nl_dis');">Discharge</td>
				<td><input name="bre_nl_dis_nt" id="bre_nl_dis_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nl_dis_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td><select name="bre_nl_ret" id="bre_nl_ret" class="wmtInput">
				<?php ListSel($dt{'bre_nl_ret'},'YesNo'); ?>
				</select></td>
				<td class="wmtClick" onclick="toggleThroughSelect('bre_nl_ret');">Retraction</td>
				<td><input name="bre_nl_ret_nt" id="bre_nl_ret_nt" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'bre_nl_ret_nt'}, ENT_QUOTES); ?>" /></td>
			</tr>
			<tr>
				<td>Notes:</td>
				<td colspan="2"><textarea name="bre_nl_nt" id="bre_nl_nt" class="wmtFullInput" rows="2"><?php echo htmlspecialchars($dt{'bre_nl_nt'}, ENT_QUOTES); ?></textarea></td>
			</tr>
		</table></td>
	</tr>
</table>

<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/form_js/breast_exam.js" type="text/javascript"></script>
<?php
}
?>
