<?php
$hdr = 'General:';
$nt = trim($dt{'ee1_ge_gen_nt'});
EE1_PrintNote($nt, $chp_title, $hdr);
$hdr_printed = false;

$hdr = 'Neck:';
$nt = trim($dt{'ee1_ge_nk_nt'});
EE1_PrintNote($nt, $chp_title, $hdr);
$hdr_printed = false;

$hdr = 'Face/Scalp:';
$nt = trim($dt{'ee1_ge_hd_nt'});
EE1_PrintNote($nt, $chp_title, $hdr);
$hdr_printed = false;

$hdr = 'Cranial Nerves:';
$nt = trim($dt{'ee1_ge_neu_cn_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Ears:';
$nt = trim($dt{'ee1_ge_ear_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Tuning Fork:';
$nt = trim($dt{'ee1_ge_lym_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Eyes:';
$nt = trim($dt{'ee1_ge_eye_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Nose:';
$nt = trim($dt{'ee1_ge_nose_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Oral Cavity:';
$nt = trim($dt{'ee1_ge_mouth_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Oropharynx:';
$nt = trim($dt{'ee1_ge_thrt_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Nasopharynx:';
$nt = trim($dt{'ee1_ge_thy_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;

$hdr = 'Larynx/Hypopharynx:';
$nt = trim($dt{'ee1_ge_ms_nt'});
EE1_PrintNote($nt, $chp_printed, $hdr);
$hdr_printed = false;
?>
