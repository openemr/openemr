<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($client_id)) $client_id = '';
if(!empty($dt{$field_prefix.'last_colon'}) && $dt{$field_prefix.'last_colon'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Colonoscopy:',$dt{$field_prefix.'last_colon'});
}
if(!empty($dt{$field_prefix.'last_bone'}) && $dt{$field_prefix.'last_bone'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Bone Density:',$dt{$field_prefix.'last_bone'});
}
if(!empty($dt{$field_prefix.'last_chol'}) && $dt{$field_prefix.'last_chol'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Cholesterol Check:',$dt{$field_prefix.'last_chol'});
}
if(!empty($dt{$field_prefix.'last_rectal'}) && $dt{$field_prefix.'last_rectal'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Rectal Exam:',$dt{$field_prefix.'last_rectal'});
}
if(!empty($dt{$field_prefix.'last_db_eye'}) && $dt{$field_prefix.'last_db_eye'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Diabetic Eye Exam:',$dt{$field_prefix.'last_db_eye'});
}
if(!empty($dt{$field_prefix.'last_db_foot'}) && $dt{$field_prefix.'last_db_foot'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Diabetic Foot Exam:',$dt{$field_prefix.'last_db_foot'});
}
if(!empty($dt{$field_prefix.'last_pap'}) && $dt{$field_prefix.'last_pap'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Pap Smear:',$dt{$field_prefix.'last_pap'});
}
if(!empty($dt{$field_prefix.'last_mamm'}) && $dt{$field_prefix.'last_mamm'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Mammogram:',$dt{$field_prefix.'last_mamm'});
}
if(!empty($dt{$field_prefix.'last_mp'}) && $dt{$field_prefix.'last_mp'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last Period:',$dt{$field_prefix.'last_mp'});
}
if(!empty($dt{$field_prefix.'last_psa'}) && $dt{$field_prefix.'last_psa'} != 0) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Last PSA:',$dt{$field_prefix.'last_psa'});
}
if($client_id == 'qhc') {
	if(!empty($dt{$field_prefix.'last_pft'}) && $dt{$field_prefix.'last_pft'} != 0) {
		$chp_printed=PrintChapter($chp_title,$chp_printed);
		PrintSingleLine('Last Pulmonary Function Test:',$dt{$field_prefix.'last_pft'});
	}
	if(!empty($dt{$field_prefix.'last_aorta'}) && $dt{$field_prefix.'last_aorta'} != 0) {
		$chp_printed=PrintChapter($chp_title,$chp_printed);
		PrintSingleLine('Last Aorta Ultrasound:',$dt{$field_prefix.'last_aorta'});
	}
	if(!empty($dt{$field_prefix.'last_bladder'}) && $dt{$field_prefix.'last_bladder'} != 0) {
		$chp_printed=PrintChapter($chp_title,$chp_printed);
		PrintSingleLine('Last Bladder Ultrasound:',$dt{$field_prefix.'last_bladder'});
	}
}
?>
