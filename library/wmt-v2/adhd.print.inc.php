<?php 
$chp_printed= false;
$hdr_printed= false;
$_title= 'Attention Deficit Disorder Checklist';
$chc=ListLook($ad{'adhd_ph_1'},'YesNo');
$nt=trim($ad{'adhd_ph_1_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('1.&nbsp;&nbsp;History of distractibility, short attention span, impulsivity or restlessness as a child');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_2'},'YesNo');
$nt=trim($ad{'adhd_ph_2_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('2.&nbsp;&nbsp;History of not living up to potential in school or work');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_3'},'YesNo');
$nt=trim($ad{'adhd_ph_3_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('3.&nbsp;&nbsp;History of frequent behavior problems in school (detention, suspension, fighting, etc.)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_4'},'YesNo');
$nt=trim($ad{'adhd_ph_4_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('4.&nbsp;&nbsp;Substance abuse problems as a teenager or young adult');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_5'},'YesNo');
$nt=trim($ad{'adhd_ph_5_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('5.&nbsp;&nbsp;History of several or more driving accidents or infractions');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_6'},'YesNo');
$nt=trim($ad{'adhd_ph_6_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('6.&nbsp;&nbsp;Cigarette of nicotine habit (previous or current)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ph_7'},'YesNo');
$nt=trim($ad{'adhd_ph_7_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Past History', $hdr_printed);
  PrintSingleLine('7.&nbsp;&nbsp;Family history of ADHD, learning problems');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}

$hdr_printed= false;
$chc=ListLook($ad{'adhd_ci_1'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_1_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('1.&nbsp;&nbsp;Short attention span when attempting boring or monotonous tasks');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_2'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_2_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('2.&nbsp;&nbsp;Trouble listening or following instructions');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_3'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_3_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('3.&nbsp;&nbsp;Frequently forgetful or misplacing things');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_4'},'Rare_Some_Often', 'padding-left: 4px;');
$nt=trim($ad{'adhd_ci_4_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('4.&nbsp;&nbsp;Trouble starting or finishing books or novels');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_5'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_5_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('5.&nbsp;&nbsp;Tendency to become easily bored');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_6'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_6_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('6.&nbsp;&nbsp;Chronic procrastination');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_7'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_7_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('7.&nbsp;&nbsp;Trouble remembering appointments or obligations');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_8'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_8_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('8.&nbsp;&nbsp;Impatient, low frustration tolerance');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_9'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_9_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('9.&nbsp;&nbsp;Trouble completing or fnishing tasks');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_10'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_10_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('10.&nbsp;&nbsp;Rush through paperwork or tasks, frequent careless mistakes');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_ci_11'},'Rare_Some_Often');
$nt=trim($ad{'adhd_ci_11_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Inattention', $hdr_printed);
  PrintSingleLine('11.&nbsp;&nbsp;Trouble listening in conversation');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}

$hdr_printed= false;
$chc=ListLook($ad{'adhd_cr_1'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_1_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('1.&nbsp;&nbsp;Restlessness (tapping pencil, bouncing leg, etc.)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_2'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_2_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('2.&nbsp;&nbsp;Need to be in constant motion in order to think or relax');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_3'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_3_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('3.&nbsp;&nbsp;Trouble sitting still, or staying in one place for too long');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_4'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_4_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('4.&nbsp;&nbsp;An internal sense of nervousness/restlessness');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_5'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_5_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('5.&nbsp;&nbsp;Impulsive, act without thinking');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_6'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_6_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('6.&nbsp;&nbsp;Short fuse, quick to anger');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_7'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_7_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('7.&nbsp;&nbsp;Innappropriate comments, saying exactly what comes to mind');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_8'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_8_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('8.&nbsp;&nbsp;Difficulties falling asleep, turning off thoughts at night');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_9'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_9_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('9.&nbsp;&nbsp;Multiple, impulsive job/career changes');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_10'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_10_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('10.&nbsp;&nbsp;Preference for high stimulation or excitement');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_11'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_11_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('11.&nbsp;&nbsp;Argumentative, stubborn, "hard-headed"');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_12'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_12_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('12.&nbsp;&nbsp;Tendency toward addictions (food, alcohol, drugs, work)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cr_13'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cr_13_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Restless / Impulsive', $hdr_printed);
  PrintSingleLine('13.&nbsp;&nbsp;Frequent traffic violations, reckless driving');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}

$hdr_printed= false;
$chc=ListLook($ad{'adhd_cd_1'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_1_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('1.&nbsp;&nbsp;Chronically late or usually in a hurry or rush');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_2'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_2_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('2.&nbsp;&nbsp;Easily overwhelmed by tasks of daily living');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_3'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_3_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('3.&nbsp;&nbsp;Poor financial management (late of unpaid bills, excessive debt)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_4'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_4_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('4.&nbsp;&nbsp;Disorganized work/living area');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_5'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_5_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('5.&nbsp;&nbsp;Messy handwriting');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_6'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_6_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('6.&nbsp;&nbsp;Sense of underachievement or not living up to potential');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
$chc=ListLook($ad{'adhd_cd_7'},'Rare_Some_Often');
$nt=trim($ad{'adhd_cd_7_nt'});
if(!empty($chc)) { $chc.= ' - '; }
if(!empty($chc) || !empty($nt)) {
	$chp_printed= PrintChapter($_title, $chp_printed, 'padding-left: 4px;');
	$hdr_printed= PrintHeader('Current History - Disorganization', $hdr_printed);
  PrintSingleLine('7.&nbsp;&nbsp;Inconsistent work performance (deadlines, paperwork, lateness, etc.)');
	PrintSingleLine('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$chc,$nt);
}
if($chp_printed) { CloseChapter(); }
?>

