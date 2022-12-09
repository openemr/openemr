<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($nt_type)) $nt_type = 'progress';
if(!isset($chp_title)) $chp_title = 'Progress Notes / Narrative';
$use_time = checkSettingMode('wmt::prgress_nt_time','',$frmdir);
$use_counselor = checkSettingMode('wmt::prgress_nt_counselor','',$frmdir);
$separate_users = checkSettingMode('wmt::progress_nt_multi_user','',$frmdir);
$sql = 'SELECT * FROM form_progress_nt WHERE pid = ? AND prg_nt_type = ? ';
$sql .= 'AND form_dt <= ? ORDER BY form_dt ASC';
$binds = array($pid, $nt_type, $dt['form_dt'].' 00:00:00');
$res = sqlStatement($sql, $binds);
$prior = array();
while($row = sqlFetchArray($res)) {
	$text = substr($row{'form_dt'},0,10);
	if($row{'prg_nt_progress_flag'}) $text .= ' [ Case Note ]';
	if($row['prg_nt_converted']) {
		$text .= ' (Imported/Converted Entry)';
	} else if($use_time) {
		if($row['prg_nt_start_hour'] != '') $text .= ' from '.
			$row{'prg_nt_start_hour'} . ':' . $row{'prg_nt_start_min'}.
			' ' . $row{'prg_nt_start_apm'};
		if($row['prg_nt_end_hour'] != '') $text .= ' to '. $row{'prg_nt_end_hour'}.
			':' . $row{'prg_nt_end_min'} . ' ' . $row{'prg_nt_end_apm'};
	}
	if($separate_users) {
		$dr= UserDispNameFromID($row{'prg_nt_counselor'});
	} else if(!$use_counselor) {
		$tmp = wmtPrintVisit::getEncounter($row{'encounter'});
		$dr = $tmp->provider_full;
		// if($row{'prg_nt_counselor'}) $dr= UserDispNameFromID($row{'prg_nt_counselor'});
	} else $dr= UserDispNameFromID($row{'prg_nt_counselor'});
	$text .= ' By: '.$dr;
	if($row{'prg_nt_late_flag'}) $text .= ' ( Late Entry )';
	$prior[] = $text;
	$prior[] = $row{'prg_nt_notes'};
}
$class = 'wmtPrnLabel';
$nts_exist = true;
if(!count($prior)) {
	$prior[] = 'No Notes On File';
	$class = 'wmtPrnBody';
	$nts_exist = false;
}
if(!$nts_exist) 
	$nts_exist = !checkSettingMode('wmt::suppress_empty_prg_nt','',$frmdir);

$chp_printed = false;
if($nts_exist) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
  <table width="100%" border="0" cellspacing="0" cellpadding="3">
	<?php foreach($prior as $entry) { ?>
		<tr>
			<td class="<?php echo $class; ?>" style="white-space: normal; width: 100%;">
			<?php echo $entry; ?>
			</td>
		</tr>	
		<?php $class = ($class == 'wmtPrnLabel') ? 'wmtPrnBody' : 'wmtPrnLabel'; ?>
		<?php } ?>
<?php } ?>
