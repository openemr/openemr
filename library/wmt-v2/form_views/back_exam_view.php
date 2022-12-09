<?php 
if(!isset($frmdir)) $frmdir = '';
if(!isset($encounter)) $encounter = '';
if(!isset($field_prefix)) $field_prefix = '';

$be_sections = array('be_spg', 'be_spc', 'be_spt', 'be_spl', 'be_motor', 
	'be_sns', 'be_rfx', 'be_skin', 'be_misc', 'be_vascular', 'be_lymph',
	'be_waddell'); 
$motor_keys = array('Trapezius', 'Deltoid', 'Biceps', 'Wrist Ext', 'Triceps',
	'Fing Flex', 'Intrinsics', 'Hip Flex', 'Quad', 'Tib Ant', 'EHL', 
	'Gastro-Soleus');
$sense_keys = array('Face', 'C1', 'C2', 'C3', 'C4', 'C5', 'C6', 'C7', 'C8',
	'T1', 'T4', 'L1', 'L2', 'L3', 'L4', 'L5', 'S1', '');
$dtr_keys = array('Biceps', 'Wrist Ext', 'Triceps', 'Quad', 'Gastroc');
$pathr_keys = array('Hoffmans', 'Babinski', 'Jaw', 'Glenohumeral', 
	'lhermitte', 'Clonus');
$skin_keys = array('Shiny', 'Flaky', 'Varicosities', 'Rubor', 'Hair', 'Dry',
	'Ulcers', 'Pallor', 'Cyanosis', 'Edema');
$leg_raise_keys = array('Leg Pain', 'Back Pain Only');
$rom_keys = array('Shoulder', 'Hip Int Rot', 'Hip Ext Rot', 'Knee');
$stable_keys = array('Shoulder', 'Elbow', 'Hip', 'Knee', 'Ankle');

if(!isset($client_id)) {
	if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';
	$client_id = $GLOBALS['wmt::client_id'];
}

if($frmdir == 'back_exam') {
	// WAS CALLED AS A STAND-ALONE
	$sql = 'SELECT * FROM form_back_exam WHERE id = ? AND pid = ?';
	$binds = array($id, $pid);
} else {
	// GET ENCOUNTER LEVEL FORM
	$sql = 'SELECT * FROM form_back_exam WHERE link_id = ? AND link_name = ? ' .
		'AND pid = ?';
	$binds = array($encounter, 'encounter', $pid);
}

if(!function_exists('setPairData')) {
	function setPairData(&$dt, $pairs, $prefix = '') {
		foreach($pairs as $pair) {
			$vals = explode($GLOBALS['wmt::secondary_parse'],$pair);
			// echo "Exploded Pair [$pair] into  [$vals[0]] amd ($vals[1])<br>\n";
			if(!isset($vals[1])) $vals[1] = '';
			$dt[$prefix . $vals[0]] = $vals[1];
			// echo "Set [" . $prefix . $vals[0] . "] amd ($vals[1])<br>\n";
		}
	}
}

$be = '';
$slice = 8;
if($frmdir != 'back_exam') $slice = 14;
$be = sqlQuery($sql, $binds);
if($be) {
	$be = array_slice($be, $slice);
	foreach($be as $key => $val) {
		$dt[$key] = $val;
	}

	$motor_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_motor'});
	setPairData($dt,$motor_pairs, 'be_mtr_');
	$light_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_sns_light'});
	setPairData($dt,$light_pairs, 'be_sns_light_');
	$pin_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_sns_pin'});
	setPairData($dt,$pin_pairs, 'be_sns_pin_');
	$dtr_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_rfx_dtr'});
	setPairData($dt,$dtr_pairs, 'be_rfx_dtr_');
	$pathr_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_rfx_path'});
	setPairData($dt,$pathr_pairs, 'be_rfx_path_');
	$skin_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_skin'});
	setPairData($dt,$skin_pairs, 'be_skin_');
	$leg_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_leg_raise'});
	setPairData($dt,$leg_pairs, 'be_misc_leg_');
	$rom_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_rom'});
	setPairData($dt,$rom_pairs, 'be_misc_rom_');
	$stable_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_stable'});
	setPairData($dt,$stable_pairs, 'be_misc_stable_');
	$fab_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_misc_faber']);
	setPairData($dt,$fab_pairs, 'be_misc_faber_');
	$vascular_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_vascular']);
	setPairData($dt,$vascular_pairs, 'be_vascular_');
	$lymph_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_lymph']);
	setPairData($dt,$lymph_pairs, 'be_lymph_');
	$waddell_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_waddell']);
	setPairData($dt,$waddell_pairs, 'be_waddell_');
	$norm_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_sec_norm']);
	setPairData($dt,$norm_pairs);

	foreach($be_sections as $val) {
		if(!isset($dt[$val . '_norm_exam'])) $dt[$val . '_norm_exam'] = '';
	}
	$chp_printed = PrintChapter($module['title']);	
	
	$nt = trim($dt{'be_dictate'});
	if($nt) {
?>

	<tr>
		<td class="wmtPrnLabel">Notes / Plan:</td>
		<td colspan="4">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="5" class="wmtPrnIndentText"><?php echo htmlspecialchars($dt{'be_dictate'}, ENT_QUOTES); ?></td>
	</tr>
	<?php } ?>
</table>
<br>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;General Spine Exam&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;", 2 => "width: 65px;");
	printCheckLine($dt,'Orientation','be_spg','or','Oriented x3', '', 0, $style);
	$style = array(0 => "", 1 => "", 2 => "padding-left: 25px;");
	printSelNoteRight($dt,'Body Habits','be_spg','bh','Notes:','Body_Habits', '', 0, $style);
	printSelNoteRight($dt,'Gait','be_spg','gait',-1,'Nrm_Pth_Abn','be_spg_nt',6);
	printSelNoteRight($dt,'Ambulatory Aids','be_spg','amb',-1,'Amb_Aids');
	printSelNoteRight($dt,'Toe Walk (Left)','be_spg','t_l',-1,'Nrm_Pth_Abn');
	printSelNoteRight($dt,'Toe Walk (Right)','be_spg','t_r',-1,'Nrm_Pth_Abn');
	printSelNoteRight($dt,'Heel Walk (Left)','be_spg','h_l',-1,'Nrm_Pth_Abn');
	printSelNoteRight($dt,'Heel Walk (Right)','be_spg','h_r',-1,'Nrm_Pth_Abn');
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Cervical Spine Exam&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;");
	printSelNoteRight($dt,'Alignment','be_spc','align','Notes:','Nrm_Pth_Abn','',0,$style);
	printSelNoteRight($dt,'Rotation ROM (Left)','be_spc','r_l',-1,'ROM','be_spc_nt',10);
	printSelNoteRight($dt,'Rotation ROM (Right)','be_spc','r_r',-1,'ROM');
	printSelNoteRight($dt,'Flexion ROM','be_spc','flex',-1,'ROM');
	printSelNoteRight($dt,'Extension ROM','be_spc','ext',-1,'ROM');
	printSelNoteRight($dt,'Tenderness at','be_spc','tender',-1,'BE_Tender');
	printSelNoteRight($dt,'Masses/Step-Off at','be_spc','mass',-1,'Abs_Pres');
	printSelNoteRight($dt,'Spurling Test (Left)','be_spc','sp_l',-1,'Abs_Pres');
	printSelNoteRight($dt,'Spurling Test (Right)','be_spc','sp_r',-1,'Abs_Pres');
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Thoracic Spine Exam&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;");
	printSelNoteRight($dt,'Alignment','be_spt','align','Notes:','Nrm_Pth_Abn','',0,$style);
	printSelNoteRight($dt,'Tenderness at','be_spt','tender',-1,'BE_Tender','be_spt_nt',2);
	printSelNoteRight($dt,'Masses/Step-Off at','be_spt','mass',-1,'Abs_Pres');
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Lumbar Spine Exam&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;", 2=> "width: 140px;");
	printSelNoteRight($dt,'Extension','be_spl','ext_flex','Flexion','Ext_Flex_Pain','',0,$style);
	$style = array(0 => "", 1 => "", 2 => "padding-left: 25px;");
	printSelNoteRight($dt,'Alignment','be_spl','align','Notes:','Nrm_Pth_Abn');
	printSelNoteRight($dt,'Flexion ROM','be_spl','flex_rom',-1,'ROM',1,'be_spl_nt',6);
	printSelNoteRight($dt,'Flexion Pain','be_spl','flex_pain',-1,'BE_Tender');
	printSelNoteRight($dt,'Extension Pain','be_spl','ext_pain',-1,'L_Ext_Pain');
	printSelNoteRight($dt,'Tenderness at','be_spl','tender',-1,'BE_Tender');
	printSelNoteRight($dt,'Masses/Step-Off at','be_spl','mass',-1,'Abs_Pres');
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Motor Testing</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
		<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
		<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
		<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
	</tr>
<?php 
	$cell_cnt = 1;
	foreach($motor_keys as $key => $name) {
		if($cell_cnt == 1) echo "<tr>\n";
		$id_left = 'be_mtr_l_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		$id_right = 'be_mtr_r_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		printSelColsLR($dt,$name,$id_left,$id_right,'','1-9','Num');
		$cell_cnt++;
		if($cell_cnt > 4) {
			echo "</tr>\n";
			$cell_cnt = 1;
		}
	}

PrintOverhead('Notes:', $dt{'be_motor_nt'},12,'',1);
?>
</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Sensory Testing&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td style="width: 30%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td colspan="3" class="wmtPrnLabel">Light Touch</td></tr>
			<tr>
				<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
		<?php 
			$cell_cnt = 1;
			foreach($sense_keys as $name) {
				if($cell_cnt == 1) echo "<tr>\n";
				$id_left = 'be_sns_light_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_sns_light_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','1-9','Num');
				$cell_cnt++;
				if($cell_cnt > 1) {
					echo "</tr>\n";
					$cell_cnt = 1;
				}
			}
		?>
		</table>
	</td>
	
	<td style="width: 30%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td colspan="3" class="wmtPrnLabel">Pin Prick</td></tr>
			<tr>
				<td>&nbsp;</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
		<?php 
			$cell_cnt = 1;
			foreach($sense_keys as $name) {
				if($cell_cnt == 1) echo "<tr>\n";
				$id_left = 'be_sns_pin_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_sns_pin_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','1-9','Num');
				$cell_cnt++;
				if($cell_cnt > 1) {
					echo "</tr>\n";
					$cell_cnt = 1;
				}
			}
		?>
		</table>
	</td>

	<td class="wmtPrnT">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php PrintOverhead('Notes:', $dt{'be_sns_nt'},3); ?>
		</table>	
	</td></tr>
	
</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Coordination / Reflexes&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtPrnLabel">Coordination</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<tr>
			<?php printSelColsLR($dt,'Alt Supination / Pronation of Forearm','be_rfx_coor_l','be_rfx_coor_r','','Nrm_Pth_Abn'); ?>
			</tr>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtPrnLabel">Deep Tendon Reflexes</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<?php 
			foreach($dtr_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_rfx_dtr_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_rfx_dtr_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','1-9','Num');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtPrnLabel">Reflexes - Pathologic</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<?php 
			foreach($pathr_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_rfx_path_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_rfx_path_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','Abs_Pres');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>
</tr>
<?php PrintOverhead('Notes:', $dt{'be_rfx_nt'},3); ?>
</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Skin Exam&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$cell_cnt = 1;
	foreach($skin_keys as $name) {
		if($cell_cnt == 1) echo "<tr>\n";
		$item = 'be_skin_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		PrintSelCol($dt,$name,$item,'Abs_Pres');
		$cell_cnt++;
		if($cell_cnt > 4) {
			echo "</tr>\n";
			$cell_cnt = 1;
		}
	}
	PrintOverhead('Notes:', $dt{'be_skin_nt'},4);
?>
</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;ROM / Stability / Misc&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtPrnLabel">Straight Leg Raise Pain</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<?php 
			$cnt = 1;
			foreach($leg_raise_keys as $name) {
				echo "<tr>\n";
				$list = ($cnt == 1) ? 'SLR_Pain' : 'SLR_Back_Pain';
				$id_left = 'be_misc_leg_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_misc_leg_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'',$list);
				echo "</tr>\n";
			}
			?>
			<tr><td>&nbsp;</td></tr>
			<tr>
			<?php printSelColsLR($dt,'Fabere Test','be_misc_faber_l','be_misc_faber_r','','Abs_Pres'); ?>
			</tr>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmPrntLabel">Range Of Motion</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<?php 
			foreach($rom_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_misc_rom_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_misc_rom_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','ROM');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtPrnLabel">Stability</td><td class="wmtPrnLabel">Left</td><td class="wmtPrnLabel">Right</td>
			</tr>
			<?php 
			foreach($stable_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_misc_stable_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_misc_stable_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				printSelColsLR($dt,$name,$id_left,$id_right,'','Stable');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>
	<?php PrintOverhead('Notes:', $dt{'be_misc_nt'},3); ?>

</tr>
</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Vascular Pulses&nbssp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtPrnLabel">Vascular Pulses</td>
			<td class="wmtPrnLabel">Left</td>
			<td class="wmtPrnLabel">Right</td>
			<td class="wmtPrnIndentLabel">Notes:</td>
		</tr>
<?php 
	$style = array(0 => "width: 160px;", 1 => "width: 120px;", 2 => "width: 140px;");
	printSelLRNoteRight($dt,'Radial','be_vascular','radial','-1','Nrm_Pth_Abn','be_vascular_nt',6, $style);
	printSelLRNoteRight($dt,'DP','be_vascular','dp','-1','Nrm_Pth_Abn','',0);
	printSelLRNoteRight($dt,'PT','be_vascular','pt',-1,'Nrm_Pth_Abn','',0);
	printSelLRNoteRight($dt,'Upper Ext','be_vascular','upper_ext',-1,'Nrm_Pth_Abn','',0);
	printSelLRNoteRight($dt,'Lower Ext','be_vascular','lower_ext',-1,'Nrm_Pth_Abn','',0);
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Lymphadenopathy&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtPrnLabel">Lymphadenopathy</td>
			<td class="wmtPrnLabel">Left</td>
			<td class="wmtPrnLabel">Right</td>
			<td class="wmtPrnIndentLabel">Notes:</td>
		</tr>
<?php 
	$style = array(0 => "width: 160px;", 1 => "width: 120px;");
	printSelLRNoteRight($dt,'Supraclavacular Zone','be_lymph','supra','-1','Abs_Pres','be_lymph_nt',4, $style);
	printSelLRNoteRight($dt,'Infraclavicular Zone','be_lymph','infra','-1','Abs_Pres','',0);
	printSelLRNoteRight($dt,'Submandibular','be_lymph','sub',-1,'Abs_Pres','',0);
?>
	</table>
</fieldset>

<fieldset style="margin: 5px; padding: 5px;"><legend class="wmtPrnLabel">&nbsp;Waddell Signs</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;", 2 => "padding-left: 25px;");
	printSelNoteRight($dt,'Flip Test','be_waddell','flip','Notes:','Abs_Pres','',0,$style);
	printSelNoteRight($dt,'Rotation','be_waddell','rotate','-1','Abs_Pres','be_waddell_nt',5);
	printSelNoteRight($dt,'Superific Tenderness','be_waddell','super',-1,'Abs_Pres');
	printSelNoteRight($dt,'Head Compression','be_waddell','head',-1,'Abs_Pres');
	printSelNoteRight($dt,'Non-Anatomic','be_waddell','non',-1,'Abs_Pres');
?>
	</table>
</fieldset>
</div>
</div>
<?php
	$chp_printed = FALSE;
} // END OF IF THE EXAM EXISTS
?>
