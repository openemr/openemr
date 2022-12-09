<?php 
if(!isset($field_prefix)) $field_prefix = '';
$local_fields = array('lifestyle_nt' => 'Lifestyle Notes',
	'born_nt' => 'Born and Raised',
	'sohist_nt' => 'Social History',
	'education_nt' => 'Education',
	'military_nt' => 'Military Service',
	'legal_nt' => 'Legal',
	'work_nt' => 'Work History',
	'abuse_nt' => 'Sexual &amp; Physical Abuse',
	'relationships_nt' => 'Relationships',
	'religion_nt' => 'Religion',
	'recreation_nt' => 'Recreation &amp; Hobbies',
	'exercise_nt' => 'Exercise',
	'music_nt' => 'Music',
	'social_nt' => 'Other Notes');
foreach($local_fields as $key => $title) {
	if(!isset($dt[$field_prefix.$key])) $dt[$field_prefix.$key] = '';
	$nt = trim($dt[$field_prefix.$key]);
	if($nt) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintOverhead($title,$nt);
	}
}
?>
