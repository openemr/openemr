<?php
if(!isset($frmdir)) $frmdir = '';
if(!isset($chp_title)) $chp_title = 'Prenatal Visits';
$last_preg = '0000-00-00';
$old= sqlQuery('SELECT pid, id, pp_date_of_pregnancy FROM form_whc_pp '.
	'WHERE pid=? AND pp_date_of_pregnancy <= ? ORDER BY pp_date_of_pregnancy '.
	'DESC LIMIT 1',array($pid, $dt['form_dt']));
if($old{'pp_date_of_pregnancy'}) $last_preg = $old{'pp_date_of_pregnancy'};
if(strlen($last_preg) == 7) $last_preg .= '-00';
if(!isset($dt['form_dt'])) $dt['form_dt'] = '';
$pn = GetPrenatalVisits($pid, '', $last_preg, $dt['form_dt']);
$chp_printed = PrintChapter($chp_title, $chp_printed);
if(($dt{'pre_preg_wt'} && $dt{'pre_preg_wt'} != 0) || 
		($dt{'pre_preg_ht'} && $dt{'pre_preg_ht'} != 0) || 
		($dt{'pre_preg_BMI'} && $dt{'pre_preg_BMI'} != 0) ||
			$dt{'pre_preg_BMI_status'}) {
?>
	<tr>
		<td class='wmtPrnLabel wmtPrnBorder1B'>&nbsp;Pre-Pregnancy:</td>
		<td class='wmtPrnBorder1B'><span class='wmtPrnLabel'>Weight:&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='wmtPrnBody'><?php echo htmlspecialchars($dt{'pre_preg_wt'}, ENT_QUOTES);?></span></td>
		<td class='wmtPrnBorder1B'><span class='wmtPrnLabel'>Height:&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='wmtPrnBody'><?php echo htmlspecialchars($dt{'pre_preg_ht'}, ENT_QUOTES); ?></span></td>
	<td class='wmtPrnBorder1B'><span class='wmtPrnLabel'>BMI:&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='wmtPrnBody'><?php echo htmlspecialchars($dt{'pre_preg_BMI'}, ENT_QUOTES); ?></span></td>
	<td class='wmtPrnBorder1B'><span class='wmtPrnLabel'>BMI Status:&nbsp;&nbsp;&nbsp;&nbsp;</span><span class='wmtPrnBody'><?php echo htmlspecialchars($dt{'pre_preg_BMI_status'}, ENT_QUOTES); ?></span></td>
	<tr>
</table>
<table width='100%' border='0' cellpadding='1' cellspacing='0'>
<?php
}
?>
  <tr>
    <td style="width: 4%;" class="wmtPrnBody4 wmtPrnBorder1B">&nbsp;Date&nbsp;&nbsp;</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Weeks Gest.</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Fundal<br />Height</td>
    <td style="width: 7%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Pres.</td>
    <td style="width: 7%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">FHR</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Fetal<br/>Move</td>
    <td style="width: 8%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Preterm<br/>Labor</td>
    <td style="width: 9%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Cervix Exam</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">BP</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Weight</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Weight Gain</td>
    <td class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B" colspan="2">Urine<br>Pro&nbsp;&nbsp;&nbsp;&nbsp;Glu</td>
    <td style="width: 5%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Edema</td>
		<?php if($client_id != 'wcs') { ?>
    <td style="width: 5%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Pain Scale</td>
		<?php } ?>
    <td style="width: 8%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Next Appt</td>
    <td style="width: 6%;" class="wmtPrnBody4 wmtPrnC wmtPrnBorder1L wmtPrnBorder1B">Dr.<br />Init</td>
  </tr>
  <?php
	$cols = 16;
	if($client_id != 'wcs') $cols = 17;
	if(count($pn)) {
  	foreach($pn as $prev) {
	?>
  <tr>
  	<td class='wmtPrnBody4 wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_date'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_weeks_gest'], ENT_QUOTES); ?></td>
   	<td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_fundal_height'], ENT_QUOTES); ?></td>
   	<td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php	echo ListLook($prev['pn_present'],'WHC_Presentation'); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo checkSettingMode('wmt::fhr_text_entry','',$frmdir) ? htmlspecialchars($prev['pn_fhr'], ENT_QUOTES) : ListLook($prev['pn_fhr'],'WHC_FHR'); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_fetal_move'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo $client_id == 'wcs' ? htmlspecialchars($prev['pn_preterm_labor'], ENT_QUOTES) : ListLook($prev['pn_preterm_labor'],'WHC_Labor'); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_cervix_exam'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_bp'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_weight'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo htmlspecialchars($prev['pn_weight_gain'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B' style='width: 3%'>&nbsp;<?php echo htmlspecialchars($prev['pn_urine_pro'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B' style='width: 3%'>&nbsp;<?php echo htmlspecialchars($prev['pn_urine_glu'], ENT_QUOTES); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo ListLook($prev['pn_edema'],'WHC_Edema'); ?></td>
		<?php if($client_id != 'wcs') { ?>
  	<td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo ListLook($prev['pn_pain_scale'],'WHC_Pain'); ?></td>
		<?php } ?>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo ListLook($prev['pn_next_appt'],'WHC_Weeks'); ?></td>
    <td class='wmtPrnBody4 wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;<?php echo ListLook($prev['pn_dr_init'],'WHC_Initials'); ?></td>
	</tr>
	<tr>
		<td class='wmtPrnBody4 wmtPrnBorder1B'>Comment</td>
    <td class='wmtPrnBody2 wmtPrnBorder1L wmtPrnBorder1B' colspan='<?php echo $cols; ?>' >&nbsp;<?php echo htmlspecialchars($prev['pn_comment'], ENT_QUOTES); ?></td>
  </tr>
	<?php
		}
	} else {
	?>
	<tr>
		<td>&nbsp;</td>
		<td colspan="<?php echo ($cols-1); ?>" class='wmtPrnBody3'>No Visits on File</td>
	</tr>

	<?php 
	}
	$nt = trim($dt{'visit_problems'});
	if($nt) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
	?>

	<tr>
		<td colspan='2' class='wmtPrnBody2'>Problems:</td>
	</tr>
	<tr>
		<td>&nbsp;<td>
		<td colspan="<?php echo ($cols-1); ?>" class='wmtPrnBody2'><?php echo htmlspecialchars($nt, ENT_QUOTES); ?></td>
	</tr>
	<?php
	}

	$nt = trim($dt{'visit_comments'});
	if($nt) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
	?>
	<tr>
		<td colspan='2' class='wmtPrnBody2'>Comments:</td>
	</tr>
	<tr>
		<td>&nbsp;<td>
		<td colspan="<?php echo ($cols-1); ?>" class='wmtPrnBody2'><?php echo htmlspecialchars($nt, ENT_QUOTES); ?></td>
	</tr>
	<?php 
	}
  ?>

