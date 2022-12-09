<?php
if(!isset($allergies)) $allergies = array();
if(!isset($allergy_add_allowed)) 
				$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
if(!isset($dt['fyi_allergy_nt'])) $dt['fyi_allergy_nt'] = '';
if(isset($fyi->fyi_allergy_nt)) $dt['fyi_allergy_nt'] = $fyi->fyi_allergy_nt;
?>
<div class='wmtPrnMainContainer'>
  <div class='wmtPrnCollapseBar'>
    <span class='wmtPrnChapter'><?php echo xl('Allergies'); ?></span>
  </div>
<div class='wmtPrnCollapseBox'>
	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td class='wmtPrnLabelCenterBorderB' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Title','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Reaction','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Comments','e'); ?></td>
		</tr>
<?php
if(count($allergies) > 0) {
	foreach($allergies as $prev) {
?>
		<tr>
			<td class='wmtPrnBodyBorderB'><?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<?php if($allergy_add_allowed) { ?>
			<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['reaction'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<?php } else  { ?>
			<td class='wmtPrnBodyBorderLB'><?php echo ListLook($prev['outcome'],'outcome'); ?>&nbsp;</td>
			<?php } ?>
			<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
<?php
	}
} else {
?>
		<tr>
			<td class='wmtPrnLabelBorderB'>&nbsp;</td>
			<td class='wmtPrnLabelBorderLB'><?php xl('None on File','e'); ?></td>
			<td class='wmtPrnLabelBorderLB'>&nbsp;</td>
			<td class='wmtPrnLabelBorderLB'>&nbsp;</td>
		</tr>
<?php
}
if(!empty($dt['fyi_allergy_nt'])) {
?>
		<tr>
			<td class="wmtPrnLabel" colspan="2"><?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class="wmtPrnBody" colspan="4"><?php echo htmlspecialchars($dt['fyi_allergy_nt'], ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php 
}
?>
	</table>
</div>
<?php 
//  THIS IS NOT USED FOR PRINT OR SMALL VIEWS NOW BUT IMPLEMENTS LIKE THIS
/**
if($review = checkSettingMode('wmt::allergy_review','',$frmdir)) {
	$caller = 'allergy';
	$chk_title = 'Allergies';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.print.php');
}
**/
?>
</div>
