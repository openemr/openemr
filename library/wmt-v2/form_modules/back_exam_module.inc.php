<?php 
if(!isset($frmdir)) $frmdir = '';
if(!isset($encounter)) $encounter = '';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($noload)) $noload = FALSE;
if(!isset($client_id)) {
	if(!isset($GLOBALS['wmt::client_id'])) $GLOBALS['wmt::client_id'] = '';
	$client_id = $GLOBALS['wmt::client_id'];
}

$be_sections = array('be_spg', 'be_spc', 'be_spt', 'be_spl', 'be_mtr', 
	'be_sns', 'be_rfx', 'be_skin', 'be_misc', 'be_vascular', 'be_lymph',
	'be_waddell'); 

foreach($be_sections as $s) {
	if(!isset($dt['tmp_'.$s.'_disp'])) $dt['tmp_'.$s.'_disp'] = 'block';
	if(!isset($dt['tmp_'.$s.'_button_disp'])) $dt['tmp_'.$s.'_button_disp'] = 'block';
}

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

$sql = '';
unset($binds);
$binds = array();
if($form_mode == 'new' && $first_pass) {
	// IS THERE ALREADY AN ENCOUNTER LEVEL FORM?
	$old = sqlQuery('SELECT id FROM form_back_exam WHERE link_id = ? '.
		'AND link_name = ? AND pid = ?',array($encounter, 'encounter', $pid));
	if(!isset($old{'id'})) $old{'id'} = '';
	// IS THE SYSTEM SET TO LOAD A HISTORICAL ENTRY?
	if(!$old{'id'} && !$noload) {
		$old = sqlQuery('SELECT id FROM form_back_exam WHERE pid = ? '.
			'ORDER BY `date` DESC LIMIT 1',array($pid));
		if(!isset($old{'id'})) $old{'id'} = '';
	}
	if($old{'id'}) {
		$sql = 'SELECT * FROM form_back_exam WHERE id = ? AND pid = ?';
		$binds = array($old{'id'}, $pid);
	}
} else if($frmdir == 'back_exam') {
	// WAS CALLED AS A STAND-ALONE
	if($id) {
		$sql = 'SELECT * FROM form_back_exam WHERE id = ? AND pid = ?';
		$binds = array($id, $pid);
	}
} else {
	// GET ENCOUNTER LEVEL FORM
	$sql = 'SELECT * FROM form_back_exam WHERE link_id = ? AND link_name = ? ' .
		'AND pid = ?';
	$binds = array($encounter, 'encounter', $pid);
}

$be = '';
$slice = 8;
if($frmdir != 'back_exam') $slice = 14;
if($sql) $be = sqlQuery($sql, $binds);
if($be) {
	$dt['be_id'] = $be['id'];
	$be = array_slice($be, $slice);
	foreach($be as $key => $val) {
		$dt[$key] = $val;
	}
} else {
	$be = sqlListFields('form_back_exam');
	$be = array_slice($be, $slice);
	foreach($be as $fld) {
		$dt[$fld] = '';
	}
	$dt['be_id' ] = '';
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

$motor_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_motor'});
setPairData($dt, $motor_pairs, 'be_mtr_');
$light_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_sns_light'});
setPairData($dt, $light_pairs, 'be_sns_light_');
$pin_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_sns_pin'});
setPairData($dt, $pin_pairs, 'be_sns_pin_');
$dtr_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_rfx_dtr'});
setPairData($dt, $dtr_pairs, 'be_rfx_dtr_');
$pathr_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_rfx_path'});
setPairData($dt, $pathr_pairs, 'be_rfx_path_');
$skin_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_skin'});
setPairData($dt, $skin_pairs, 'be_skin_');
$leg_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_leg_raise'});
setPairData($dt, $leg_pairs, 'be_misc_leg_');
$rom_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_rom'});
setPairData($dt, $rom_pairs, 'be_misc_rom_');
$stable_pairs = explode($GLOBALS['wmt::primary_parse'],$dt{'be_misc_stable'});
setPairData($dt, $stable_pairs, 'be_misc_stable_');
$fab_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_misc_faber']);
setPairData($dt, $fab_pairs, 'be_misc_faber_');
$vascular_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_vascular']);
setPairData($dt, $vascular_pairs, 'be_vascular_');
$lymph_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_lymph']);
setPairData($dt, $lymph_pairs, 'be_lymph_');
$waddell_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_waddell']);
setPairData($dt, $waddell_pairs, 'be_waddell_');
$norm_pairs = explode($GLOBALS['wmt::primary_parse'], $dt['be_sec_norm']);
setPairData($dt, $norm_pairs);

foreach($be_sections as $val) {
	if(!isset($dt[$val . '_norm_exam'])) $dt[$val . '_norm_exam'] = '';
}

if($draw_display) {
	
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td class="wmtLabel">Notes / Plan:</td>
		<td>&nbsp;<input name="be_id" id="be_id" type="hidden" value="<?php echo $dt['be_id']; ?>" /></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('be_dictate');" href="javascript:;"><span>Clear Notes / Plan</span></a></div></td>
	</tr>
	<tr>
		<td colspan="5"><textarea name="be_dictate" id="be_dictate" class="wmtFullInput" rows="4"><?php echo htmlspecialchars($dt{'be_dictate'}, ENT_QUOTES); ?></textarea></td>
	</tr>

	<tr>
		<td><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
		<td><div style="float: left;"><a class="css_button" tabindex="-1" onClick="showAllBackExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
				<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllBackExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
		<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="SetBackExamNormal('<?php echo $client_id; ?>','<?php echo $pat_sex; ?>');" href="javascript:;"><span>Set Exam ALL Normal</span></a></div></td>
		<td><div style="float: right; padding-right; 10px"><a class="css_button" tabindex="-1" onClick="ClearBackExam('<?php echo $client_id; ?>');" href="javascript:;"><span>Clear Exam</span></a></div></td>
	</tr>
</table>
<br>

<?php examSectionHeader($dt, 'be_spg','General','General Spine Exam'); ?>

<div id="tmp_be_spg_disp" style="display: <?php echo $dt['tmp_be_spg_disp']; ?>;">
	<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
		$style = array(0 => "width: 140px;", 1 => "width: 140px;", 2 => "width: 65px;");
		examCheckLine($dt, 'Orientation','be_spg','or','Oriented x3',1,'',0, $style);
		examSelNoteRight($dt, 'Body Habits','be_spg','bh','Notes:','Body_Habits',1,'','0');
		examSelNoteRight($dt, 'Gait','be_spg','gait',-1,'Nrm_Pth_Abn',1,'be_spg_nt',7);
		examSelNoteRight($dt, 'Ambulatory Aids','be_spg','amb',-1,'Amb_Aids',1,'',0);
		examSelNoteRight($dt, 'Toe Walk (Left)','be_spg','t_l',-1,'Nrm_Pth_Abn',1,'',0);
		examSelNoteRight($dt, 'Toe Walk (Right)','be_spg','t_r',-1,'Nrm_Pth_Abn',1,'',0);
		examSelNoteRight($dt, 'Heel Walk (Left)','be_spg','h_l',-1,'Nrm_Pth_Abn',1,'',0);
		examSelNoteRight($dt, 'Heel Walk (Right)','be_spg','h_r',-1,'Nrm_Pth_Abn',1,'',0);
?>
		</table>
	</fieldset>
</div>

<?php examSectionHeader($dt, 'be_spc','Cervical','Cervical Spine Exam'); ?>

<div id="tmp_be_spc_disp" style="display: <?php echo $dt['tmp_be_spc_disp']; ?>;">
	<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
		$style = array(0 => "width: 140px;", 1 => "width: 140px;");
		examSelNoteRight($dt, 'Alignment','be_spc','align','Notes:','Nrm_Pth_Abn',1,'',0,$style);
		examSelNoteRight($dt, 'Rotation ROM (Left)','be_spc','r_l',-1,'ROM',1,'be_spc_nt',10);
		examSelNoteRight($dt, 'Rotation ROM (Right)','be_spc','r_r',-1,'ROM',1,'',0);
		examSelNoteRight($dt, 'Flexion ROM','be_spc','flex',-1,'ROM',1,'',0);
		examSelNoteRight($dt, 'Extension ROM','be_spc','ext',-1,'ROM',1,'',0);
		examSelNoteRight($dt, 'Tenderness at','be_spc','tender',-1,'BE_Tender',1,'',0);
		examSelNoteRight($dt, 'Masses/Step-Off at','be_spc','mass',-1,'Abs_Pres',1,'',0);
		examSelNoteRight($dt, 'Spurling Test (Left)','be_spc','sp_l',-1,'Abs_Pres',1,'',0);
		examSelNoteRight($dt, 'Spurling Test (Right)','be_spc','sp_r',-1,'Abs_Pres',1,'',0);
?>
		</table>
	</fieldset>
</div>

<?php examSectionHeader($dt, 'be_spt','Thoracic','Thoracic Spine Exam'); ?>

<div id="tmp_be_spt_disp" style="display: <?php echo $dt['tmp_be_spt_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;");
	examSelNoteRight($dt, 'Alignment','be_spt','align','Notes:','Nrm_Pth_Abn',1,'',0,$style);
	examSelNoteRight($dt, 'Tenderness at','be_spt','tender',-1,'BE_Tender',1,'be_spt_nt',2);
	examSelNoteRight($dt, 'Masses/Step-Off at','be_spt','mass',-1,'Abs_Pres',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_spl','Lumbar','Lumbar Spine Exam'); ?>

<div id="tmp_be_spl_disp" style="display: <?php echo $dt['tmp_be_spl_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;");
	examSelNoteRight($dt, 'Extension','be_spl','ext_flex','Flexion','Ext_Flex_Pain',1,'',0,$style);
	examSelNoteRight($dt, 'Alignment','be_spl','align','Notes:','Nrm_Pth_Abn',1,'',0);
	examSelNoteRight($dt, 'Flexion ROM','be_spl','flex_rom',-1,'ROM',1,'be_spl_nt',6);
	examSelNoteRight($dt, 'Flexion Pain','be_spl','flex_pain',-1,'BE_Tender',1,'',0);
	examSelNoteRight($dt, 'Extension Pain','be_spl','ext_pain',-1,'L_Ext_Pain',1,'',0);
	examSelNoteRight($dt, 'Tenderness at','be_spl','tender',-1,'BE_Tender',1,'',0);
	examSelNoteRight($dt, 'Masses/Step-Off at','be_spl','mass',-1,'Abs_Pres',1,'',0);
?>
	</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_mtr','Motor','Motor Testing'); ?>

<div id="tmp_be_mtr_disp" style="display: <?php echo $dt['tmp_be_mtr_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
		<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
		<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
		<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
	</tr>
<?php 
	$cell_cnt = 1;
	foreach($motor_keys as $key => $name) {
		if($cell_cnt == 1) echo "<tr>\n";
		$id_left = 'be_mtr_l_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		$id_right = 'be_mtr_r_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		examSelColsLR($dt, $name,$id_left,$id_right,'','1-9',1,'Num');
		$cell_cnt++;
		if($cell_cnt > 4) {
			echo "</tr>\n";
			$cell_cnt = 1;
		}
	}
?>
</table>
<?php 
$field_name = 'be_motor_nt';
$note_save = $module['notes'];
$module['notes'] = 'Notes:';
include $GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php';
$module['notes'] = $note_save;
?>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_sns','Sensory','Sensory Testing'); ?>

<div id="tmp_be_sns_disp" style="display: <?php echo $dt['tmp_be_sns_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td style="width: 30%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td colspan="3" class="wmtLabel">Light Touch</td></tr>
			<tr>
				<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
		<?php 
			$cell_cnt = 1;
			foreach($sense_keys as $name) {
				if($cell_cnt == 1) echo "<tr>\n";
				$id_left = 'be_sns_light_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_sns_light_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','1-9',1,'Num');
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
			<tr><td colspan="3" class="wmtLabel">Pin Prick</td></tr>
			<tr>
				<td>&nbsp;</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
		<?php 
			$cell_cnt = 1;
			foreach($sense_keys as $name) {
				if($cell_cnt == 1) echo "<tr>\n";
				$id_left = 'be_sns_pin_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_sns_pin_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','1-9',1,'Num');
				$cell_cnt++;
				if($cell_cnt > 1) {
					echo "</tr>\n";
					$cell_cnt = 1;
				}
			}
		?>
		</table>
	</td>

	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td class="wmtLabel">Notes</td></tr>
			<tr><td><textarea name="be_sns_nt" id="be_sns_nt" class="wmtFullInput" rows="25"><?php echo $dt['be_sns_nt']; ?></textarea></td></tr>
		</table>	
	</td></tr>
	
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_rfx','Reflex','Coordination / Reflexes'); ?>

<div id="tmp_be_rfx_disp" style="display: <?php echo $dt['tmp_be_rfx_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Coordination</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
			<tr>
			<?php examSelColsLR($dt, 'Alt Supination /<br>Pronation of Forearm','be_rfx_coor_l','be_rfx_coor_r','','Nrm_Pth_Abn'); ?>
			</tr>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Deep Tendon Reflexes</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
			<?php 
			foreach($dtr_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_rfx_dtr_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_rfx_dtr_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','1-9',1,'Num');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Reflexes - Pathologic</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
			<?php 
			foreach($pathr_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_rfx_path_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_rfx_path_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','Abs_Pres');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>
</tr>
</table>

<?php 
$field_name = 'be_rfx_nt';
$note_save = $module['notes'];
$module['notes'] = 'Notes:';
include $GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php';
$module['notes'] = $note_save;
?>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_skin','Skin','Skin Exam'); ?>

<div id="tmp_be_skin_disp" style="display: <?php echo $dt['tmp_be_skin_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$cell_cnt = 1;
	foreach($skin_keys as $name) {
		if($cell_cnt == 1) echo "<tr>\n";
		$item = 'be_skin_' . 
				strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
		examSelCol($dt, $name,$item,'Abs_Pres');
		$cell_cnt++;
		if($cell_cnt > 4) {
			echo "</tr>\n";
			$cell_cnt = 1;
		}
	}
?>
</table>

<?php 
$field_name = 'be_skin_nt';
$note_save = $module['notes'];
$module['notes'] = 'Notes:';
include $GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php';
$module['notes'] = $note_save;
?>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_misc','Misc','ROM / Stablity / Misc'); ?>

<div id="tmp_be_misc_disp" style="display: <?php echo $dt['tmp_be_misc_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Straight Leg Raise Pain</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
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
				examSelColsLR($dt, $name,$id_left,$id_right,'',$list);
				echo "</tr>\n";
			}
			?>
			<tr><td>&nbsp;</td></tr>
			<tr>
			<?php examSelColsLR($dt, 'Fabere Test','be_misc_faber_l','be_misc_faber_r','','Abs_Pres'); ?>
			</tr>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Range Of Motion</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
			<?php 
			foreach($rom_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_misc_rom_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_misc_rom_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','ROM');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>

	<td class="wmtT" style="width: 33.3%;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtLabel">Stability</td><td class="wmtLabel">Left</td><td class="wmtLabel">Right</td>
			</tr>
			<?php 
			foreach($stable_keys as $name) {
				echo "<tr>\n";
				$id_left = 'be_misc_stable_l_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				$id_right = 'be_misc_stable_r_' . 
						strtolower(str_replace(array('-', ' '), array('_', '_'), $name));
				examSelColsLR($dt, $name,$id_left,$id_right,'','Stable');
				echo "</tr>\n";
			}
			?>
		</table>
	</td>
</tr>
</table>

<?php 
$field_name = 'be_misc_nt';
$note_save = $module['notes'];
$module['notes'] = 'Notes:';
include $GLOBALS['srcdir'].'/wmt-v2/specified_text_box.inc.php';
$module['notes'] = $note_save;
?>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_vascular','Vascular','Vascular Pulses'); ?>

<div id="tmp_be_vascular_disp" style="display: <?php echo $dt['tmp_be_vascular_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtLabel">Vascular Pulses</td>
			<td class="wmtLabel">Left</td>
			<td class="wmtLabel">Right</td>
			<td class="wmtLabel">Notes:</td>
		</tr>
<?php 
	$style = array(0 => "width: 160px;", 1 => "width: 120px;");
	examSelLRNoteRight($dt, 'Radial','be_vascular','radial','-1','Nrm_Pth_Abn',1,'be_vascular_nt',6, $style);
	examSelLRNoteRight($dt, 'DP','be_vascular','dp','-1','Nrm_Pth_Abn',1,'',0);
	examSelLRNoteRight($dt, 'PT','be_vascular','pt',-1,'Nrm_Pth_Abn',1,'',0);
	examSelLRNoteRight($dt, 'Upper Ext','be_vascular','upper_ext',-1,'Nrm_Pth_Abn',1,'',0);
	examSelLRNoteRight($dt, 'Lower Ext','be_vascular','lower_ext',-1,'Nrm_Pth_Abn',1,'',0);
?>
	</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_lymph','Lymphadenopathy','Lymphadenopathy'); ?>

<div id="tmp_be_lymph_disp" style="display: <?php echo $dt['tmp_be_lymph_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtLabel">Lymphadenopathy</td>
			<td class="wmtLabel">Left</td>
			<td class="wmtLabel">Right</td>
			<td class="wmtLabel">Notes:</td>
		</tr>
<?php 
	$style = array(0 => "width: 160px;", 1 => "width: 120px;");
	examSelLRNoteRight($dt, 'Supraclavacular Zone','be_lymph','supra','-1','Abs_Pres',1,'be_lymph_nt',4, $style);
	examSelLRNoteRight($dt, 'Infraclavicular Zone','be_lymph','infra','-1','Abs_Pres',1,'',0);
	examSelLRNoteRight($dt, 'Submandibular','be_lymph','sub',-1,'Abs_Pres',1,'',0);
?>
	</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'be_waddell','Waddell','Waddell Signs'); ?>

<div id="tmp_be_waddell_disp" style="display: <?php echo $dt['tmp_be_waddell_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?php 
	$style = array(0 => "width: 140px;", 1 => "width: 140px;");
	examSelNoteRight($dt, 'Flip Test','be_waddell','flip','Notes:','Abs_Pres',1,'',0,$style);
	examSelNoteRight($dt, 'Rotation','be_waddell','rotate','-1','Abs_Pres',1,'be_waddell_nt',5);
	examSelNoteRight($dt, 'Superific Tenderness','be_waddell','super',-1,'Abs_Pres',1,'',0);
	examSelNoteRight($dt, 'Head Compression','be_waddell','head',-1,'Abs_Pres',1,'',0);
	examSelNoteRight($dt, 'Non-Anatomic','be_waddell','non',-1,'Abs_Pres',1,'',0);
?>
	</table>
</fieldset>
</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
		<td><div style="float: left; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="showAllBackExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllBackExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
			<!-- div style="float: left; padding-right: 10px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);"><span>View Documents</span></a></div --></td>
	</tr>
</table>
<?php 
} // END OF DRAW DISPLAY
?>
