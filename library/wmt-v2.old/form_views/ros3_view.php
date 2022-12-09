<?php
// For the ROS section, we will do 5 sections on each sub-section to tighten 
// up the print view, first time through is for blank choices with a comment, 
// second time through is to concatonate all the 'no' answers
// and print on one line, next pass is to detail 'no' answers with comments,
// 4th pass is all 'yes' choices, last pass is yes with comment lines.
if(!isset($rs{'ros_yes'})) { $rs{'ros_yes'} = '|'; }	
if(!isset($rs{'ros_no'})) { $rs{'ros_no'} = '|'; }	
$yes = explode('|', $rs{'ros_yes'});
$no = explode('|', $rs{'ros_no'});
$comments = LoadROSFormComments($id, $frmn);

// FIRST DETAIL ANY COMMENTS WITH NO 'YES/NO' CHOICE
foreach($comments as $key => $note) {
	if(in_array($key, $yes)) continue;
	if(in_array($key, $no)) continue;
	$title = GetListTitleByKey($key,$ros_key);
	PrintROS('', $note, $title);
}

// NOW LIST ALL THE 'NO' CHOICES WITH NO COMMENT
$prnt = '';
foreach($no as $choice) {
	if(isset($comments[$choice])) continue;
	$title = GetListTitleByKey($choice,$ros_key);
	$prnt = AppendItem($prnt, $title);
}
if($prnt) PrintCompoundROS('Patient Denies: '.$prnt);

// NOW LIST ALL THE 'YES' CHOICES WITH NO COMMENT
$prnt = '';
foreach($yes as $choice) {
	if(isset($comments[$choice])) continue;
	$title = GetListTitleByKey($choice,$ros_key);
	$prnt = AppendItem($prnt, $title);
}
if($prnt) PrintCompoundROS('Patient Indicates: '.$prnt);

// NOW LIST ALL THE 'NO' CHOICES WITH A COMMENT
$prnt = '';
foreach($no as $choice) {
	if(!isset($comments[$choice])) continue;
	$note = trim($comments[$choice]);
	$title = GetListTitleByKey($choice,$ros_key);
	// echo "Calling Print With 'n'<br>\n";
	PrintROS('n',$note,$title,'','n');
}

// NOW LIST ALL THE 'YES' CHOICES WITH A COMMENT
$prnt = '';
foreach($yes as $choice) {
	if(!isset($comments[$choice])) continue;
	$note = trim($comments[$choice]);
	$title = GetListTitleByKey($choice,$ros_key);
	// echo "Calling Print With 'y'<br>\n";
	PrintROS('y',$note,$title,'','y');
}

$hdr_printed=false;
if($rs{'ros_nt'}) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintOverhead('Other Notes:',$rs{'ros_nt'}, 3);
}
?>
