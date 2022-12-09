<?php 
unset($local_fields);
$local_fields = array();
$flds = sqlListFields('form_ortho_exam');
$flds = array_slice($flds,14);
foreach($flds as $key => $fld) {
	$local_fields[] = $fld;
}
include(FORM_BRICKS . 'module_setup.inc.php');
$loader_use_prefix = TRUE;
include(FORM_BRICKS . 'module_loader.inc.php');
$loader_use_prefix = FALSE;
$oe_sections = array('ge', 'post', 'neu', 'orth', 'palp', 
	'rom', 'msc', 'tnd', 'myo');

foreach($oe_sections as $s) {
	if(!isset($dt['tmp_oe_'.$s.'_disp'])) $dt['tmp_oe_'.$s.'_disp'] = 'block';
	if(!isset($dt['tmp_oe_'.$s.'_button_disp'])) $dt['tmp_oe_'.$s.'_button_disp'] = 'block';
}

$multi_labels = array('post_api', 'neu_sense', 'orth_cerv', 'orth_lum', 
	'orth_sac', 'orth_hip', 'orth_shou', 'orth_elbow', 'orth_wrist', 'orth_knee',
	'orth_ankle', 'msc_derm', 'msc_neck', 'msc_scm', 'msc_inter', 'msc_fing_ex', 
	'msc_wrist_ex', 'msc_tri', 'msc_cuff', 'msc_delt', 'msc_lat', 'msc_fing_fl', 
	'msc_wrist_fl', 'msc_bi', 'msc_hip', 'msc_pec', 'msc_psoas', 'msc_tfl',
	'msc_glut_med', 'msc_quad', 'msc_glut_max', 'msc_ham', 'msc_tib', 'msc_per', 
	'msc_ext', 'msc_gastroc', 'tnd_pat', 'tnd_ham', 'tnd_ach', 'tnd_bi', 
	'tnd_tri', 'tnd_rad');

foreach($multi_labels as $lbl) {
	if(isset($dt[$field_prefix . $lbl])) {
		$dt[$field_prefix . $lbl] = explode('^|', $dt[$field_prefix . $lbl]);
	}
}

if($draw_display) {
?>
<script type="text/javascript">
	var ortho_sections = {};

	<?php 
	foreach($oe_sections as $s) {
		echo "	ortho_sections['$s'] = '$s';\n";
	}
	?>
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
	<tr>
		<td class="wmtLabel">Notes / Plan:</td>
		<td>&nbsp;<input name="ortho_exam_id" id="ortho_exam_id" type="hidden" value="<?php echo $dt['ortho_exam_id']; ?>" /></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td><div style="float: right; padding-right: 12px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('oe_dictate');" href="javascript:;"><span>Clear Notes / Plan</span></a></div></td>
	</tr>
	<tr>
		<td colspan="5"><textarea name="oe_dictate" id="oe_dictate" class="wmtFullInput" rows="4"><?php echo htmlspecialchars($dt{'oe_dictate'}, ENT_QUOTES); ?></textarea></td>
	</tr>

	<tr>
		<td><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
		<td><div style="float: left;"><a class="css_button" tabindex="-1" onClick="showAllOrthoExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
				<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllOrthoExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
		<!-- td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="SetOrthoExamNormal('<?php // echo $client_id; ?>','<?php // echo $pat_sex; ?>');" href="javascript:;"><span>Set Exam ALL Normal</span></a></div></td -->
		<td><div style="float: right; padding-right; 10px"><a class="css_button" tabindex="-1" onClick="ClearOrthoExam('<?php echo $client_id; ?>');" href="javascript:;"><span>Clear Exam</span></a></div></td>
	</tr>
</table>
<br>

<?php examSectionHeader($dt, 'oe_ge','General','General Appearance'); ?>

<div id="tmp_oe_ge_disp" style="display: <?php echo $dt['tmp_oe_ge_disp']; ?>;">
	<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
		$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
		examSelNoteRight($dt, 'Distress','oe_ge','distress','Notes:','Distress',1,'oe_ge_nt','4', $style);
		examSelNoteRight($dt, 'Station','oe_ge','station',-1,'NormAbnorm',1,'',0);
		examSelNoteRight($dt, 'Gait','oe_ge','gait',-1,'NormAbnorm',1,'',0);
?>
		</table>
	</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_post','Posture','Posture'); ?>

<div id="tmp_oe_post_disp" style="display: <?php echo $dt['tmp_oe_post_disp']; ?>;" class="form-group">
	<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
		$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
		examSelNoteRight($dt, 'Cervical Rotation','oe_post','cr','Notes:','left_right',1,'oe_post_nt',11, $style);
		examSelNoteRight($dt, 'Cervical Shift','oe_post','cs',-1,'left_right',1,'oe_spc_nt',0);
		examSelNoteRight($dt, 'Cervical Tilt','oe_post','ct',-1,'left_right',1,'',0);
		examSelNoteRight($dt, 'Elevated Shoulder on the','oe_post','es',-1,'left_right',1,'',0);
		examSelNoteRight($dt, 'Antalgic Lean','oe_post','al',-1,'left_right',1,'',0);
		examSelNoteRight($dt, 'Elevated Hip on the','oe_post','eh',-1,'left_right',1,'',0);
		examMultiSelNoteRight($dt, 'Abnormal Posture Indicators','oe_post','api',-1,'NormAbnormPosture',1,'',0);
?>
		</table>
	</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_neu','Neuro','Neurological'); ?>

<div id="tmp_oe_neu_disp" style="display: <?php echo $dt['tmp_oe_neu_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examSelNoteRight($dt, 'CN 2 - 12','oe_neu','cn_2_12','Notes:','NormAbnorm',1,'oe_neu_nt',24,$style);
	examMultiSelNoteRight($dt, 'Sharp Sensation','oe_neu','sense',-1,'LRSpine',1,'',0);
	examSelNoteRight($dt, 'Lower Ext. Touch Sensation','oe_neu','low',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Upper Ext. Touch Sensation','oe_neu','up',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Proprioception','oe_neu','prop',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Alertness','oe_neu','alert',-1,'YesNo',1,'',0);
	examSelNoteRight($dt, 'Attention Span - Concentration','oe_neu','attn',-1,'YesNo',1,'',0);
	examSelNoteRight($dt, 'Fundamental Knowledge','oe_neu','fund',-1,'YesNo',1,'',0);
	examSelNoteRight($dt, 'Language','oe_neu','lang',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Coordination (finger/nose)','oe_neu','coor_f',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Coordination (heel/shin)','oe_neu','coor_h',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Memory','oe_neu','mem',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Muscle Atrophy','oe_neu','atr',-1,'Abs_Pres',1,'',0);
	examSelNoteRight($dt, 'Orientation of Time, Place...','oe_neu','orient',-1,'YesNo',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_orth','Orth','Orthopedic Tests'); ?>

<div id="tmp_oe_orth_disp" style="display: <?php echo $dt['tmp_oe_orth_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examMultiSelNoteRight($dt, 'Cervical','oe_orth','cerv','Notes:','OrthCervChc',1,'oe_orth_nt',20,$style);
	examMultiSelNoteRight($dt,'Lumbar','oe_orth','lum',-1,'OrthLumChc',1,'',0);
	examMultiSelNoteRight($dt,'Sacrum','oe_orth','sac',-1,'OrthSacChc',1,'',0);
	examMultiSelNoteRight($dt,'Hip','oe_orth','hip',-1,'OrthHipChc',1,'',0);
	examMultiSelNoteRight($dt,'Shoulder','oe_orth','shou',-1,'OrthShChc',1,'',0);
	examMultiSelNoteRight($dt,'Elbow','oe_orth','elbow',-1,'OrthElbChc',1,'',0);
	examMultiSelNoteRight($dt,'Wrist','oe_orth','wrist',-1,'OrthWristChc',1,'',0);
	examMultiSelNoteRight($dt,'Knee','oe_orth','knee',-1,'OrthKneeChc',1,'',0);
	examMultiSelNoteRight($dt,'Ankle','oe_orth','ankle',-1,'OrthAnkChc',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_palp','Palp','Palpation'); ?>

<div id="tmp_oe_palp_disp" style="display: <?php echo $dt['tmp_oe_palp_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examSelNoteRight($dt, 'Cervical Alignment','oe_palp','cerv','Notes:','NormAbnorm',1,'oe_palp_nt',4,$style);
	examSelNoteRight($dt, 'Thoracic Alignment','oe_palp','thor',-1,'NormAbnorm',1,'',0);
	examSelNoteRight($dt, 'Lumbar Alignment','oe_palp','lum',-1,'NormAbnorm',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_rom','ROM','Range of Motion'); ?>

<div id="tmp_oe_rom_disp" style="display: <?php echo $dt['tmp_oe_rom_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examSelNoteRight($dt, 'Cervical Flexion','oe_rom','cerv_fl','Notes:','OrthoROM',1,'oe_rom_nt',20,$style);
	examSelNoteRight($dt, 'Cervical Flexion Pain','oe_rom','cerv_fl_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Cervical Extension','oe_rom','cerv_ex',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Cervical Extension Pain','oe_rom','cerv_ex_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Cervical Right Lateral Flexion','oe_rom','cerv_rlfl',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Cervical Right Lateral Flexion Pain','oe_rom','cerv_rlfl_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Cervical Left Lateral Flexion','oe_rom','cerv_llfl',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Cervical Left Lateral Flexion Pain','oe_rom','cerv_llfl_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Cervical Right Rotation','oe_rom','cerv_rr',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Cervical Right Rotation Pain','oe_rom','cerv_rr_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Cervical Left Rotation','oe_rom','cerv_lr',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Cervical Left Rotation Pain','oe_rom','cerv_lr_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Lumbar Flexion','oe_rom','lum_fl',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Lumbar Flexion Pain','oe_rom','lum_fl_p',-1,'OrthoPain',1,'',0);
	examSelNoteRight($dt, 'Lumbar Extension','oe_rom','lum_ex',-1,'OrthoROM',1,'',0);
	examSelNoteRight($dt, 'Lumbar Extension Pain','oe_rom','lum_ex_p',-1,'OrthoPain',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_msc','Musc','Muscle Testing'); ?>

<div id="tmp_oe_msc_disp" style="display: <?php echo $dt['tmp_oe_msc_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 300px;");
	examTwoColMultiSelect($dt, 'Dermatomes','oe_msc_derm','OrthoDerm','Neck Flex/Ext','oe_msc_neck','MuscleTest',TRUE,$style);
	examTwoColMultiSelect($dt, 'SCM','oe_msc_scm','MuscleTest','Interossei','oe_msc_inter','MuscleTest');
	examTwoColMultiSelect($dt, 'Finger Extensor','oe_msc_fing_ex','MuscleTest','Wrist Extensor','oe_msc_wrist_ex','MuscleTest');
	examTwoColMultiSelect($dt, 'Tricep','oe_msc_tri','MuscleTest','Rotator Cuff','oe_msc_cuff','MuscleTest');
	examTwoColMultiSelect($dt, 'Deltoid','oe_msc_delt','MuscleTest','Latissimus','oe_msc_lat','MuscleTest');
	examTwoColMultiSelect($dt, 'Finger Flexor','oe_msc_fing_fl','MuscleTest','Wrist Flexor','oe_msc_wrist_fl','MuscleTest');
	examTwoColMultiSelect($dt, 'Bicep','oe_msc_bi','MuscleTest','Hip Flexor','oe_msc_hip','MuscleTest');
	examTwoColMultiSelect($dt, 'Pectoralis','oe_msc_pec','MuscleTest','Psoas','oe_msc_psoas','MuscleTest');
	examTwoColMultiSelect($dt, 'TFL','oe_msc_tfl','MuscleTest','Gluteus Med','oe_msc_glut_med','MuscleTest');
	examTwoColMultiSelect($dt, 'Quadricep','oe_msc_quad','MuscleTest','Gluteus Max','oe_msc_glut_max','MuscleTest');
	examTwoColMultiSelect($dt, 'Hamstring','oe_msc_ham','MuscleTest','Tibialis Ant','oe_msc_tib','MuscleTest');
	examTwoColMultiSelect($dt, 'Peronei','oe_msc_per','MuscleTest','Ext Hallicus','oe_msc_ext','MuscleTest');
	examTwoColMultiSelect($dt, 'Gastroc','oe_msc_gastroc','MuscleTest');
?>
	<tr>
		<td class="wmtLabel"><?php echo text(xl('Notes')); ?>:</td>
	</tr>
	<tr>
		<td colspan="6"><textarea name="oe_msc_nt" id="oe_msc_nt" class="wmtFullInput" rows="5"><?php echo text($dt['oe_msc_nt']); ?></textarea></td>
	</tr>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_tnd','DTR','Deep Tendon Reflexes'); ?>

<div id="tmp_oe_tnd_disp" style="display: <?php echo $dt['tmp_oe_tnd_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examMultiSelNoteRight($dt, 'Patellar','oe_tnd','pat','Notes:','OrthoDTR',1,'oe_tnd_nt',8,$style);
	examMultiSelNoteRight($dt,'Hamstring','oe_tnd','ham',-1,'OrthoDTR',1,'',0);
	examMultiSelNoteRight($dt,'Achilles','oe_tnd','ach',-1,'OrthoDTR',1,'',0);
	examMultiSelNoteRight($dt,'Biceps','oe_tnd','bi',-1,'OrthoDTR',1,'',0);
	examMultiSelNoteRight($dt,'Triceps','oe_tnd','tri',-1,'OrthoDTR',1,'',0);
	examMultiSelNoteRight($dt,'Radial','oe_tnd','rad',-1,'OrthoDTR',1,'',0);
?>
</table>
</fieldset>
</div>

<?php examSectionHeader($dt, 'oe_myo','Myo','Myofascial Trigger Points'); ?>

<div id="tmp_oe_myo_disp" style="display: <?php echo $dt['tmp_oe_myo_disp']; ?>;">
<fieldset style="margin: 5px; padding: 5px;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-condensed">
<?php 
	$style = array(0 => "width: 300px;", 1 => "width: 250px;", 2 => "width: 65px;");
	examSelNoteRight($dt, 'Suboccipital','oe_myo','sub','Notes:','left_right_bi',1,'oe_myo_nt',20,$style);
	examSelNoteRight($dt, 'Cervical','oe_myo','cerv',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Scalene','oe_myo','scal',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Sternocleidomastoid','oe_myo','stern',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Trapezius','oe_myo','trap',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Levator Scapulae','oe_myo','lev',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Supraspinatus','oe_myo','supra',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Thoracic Paraspinal','oe_myo','thor',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Middle Trapezius','oe_myo','mid',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Teres','oe_myo','teres',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Rhomboid','oe_myo','rhom',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Lumbar Erector Spinae','oe_myo','lum',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Quadratus Lumborum','oe_myo','quad',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Gluteal','oe_myo','glut',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Piriformis','oe_myo','piri',-1,'left_right_bi',1,'',0);
	examSelNoteRight($dt, 'Psoas','oe_myo','psoas',-1,'left_right_bi',1,'',0);
?>
</table>
</fieldset>
</div>


<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
		<td><div style="float: left; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="showAllOrthoExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllOrthoExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
			<!-- div style="float: left; padding-right: 10px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);"><span>View Documents</span></a></div --></td>
	</tr>
</table>

<script type="text/javascript">
$(document).ready(function(){
	$(".select-picker").multiselect({
		maxWidth: '200px'
	});
});
</script>
<?php 
} // END OF DRAW DISPLAY
?>
