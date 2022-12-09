<?php
if(!isset($ge_notes)) $ge_notes = array();
if(count($ge_notes) > 0) {
	foreach($ge_notes as $key => $nt) {
		ProcessROSKeyComment($pid, $id, $frmn, $key, $nt);
	}
}
?>
