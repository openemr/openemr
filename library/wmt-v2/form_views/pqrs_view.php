<?php
$pqrs_selected = array();
$pqrs = sqlQuery("SELECT * FROM wmt_pqrs WHERE link_id=? AND ".
				"link_name=?", array($id, $frmdir));
if($pqrs{'pqrs_choices'}) $pqrs_selected = explode('|',$pqrs{'pqrs_choices'});

if(count($pqrs_selected) > 0) $chp_printed = PrintChapter($chp_title, $chp_printed);
foreach($pqrs_selected as $pqrs_code) {
	$desc = GetPQRSTitleByKey($pqrs_code);
	$category = GetPQRSSectionByKey($pqrs_code);
	PrintOverhead($category, $pqrs_code.' : '.$desc);
}
?>
