<?php 
$printed_dt = '';
$comment_hdr = FALSE;
$negative = ((strtolower($dt{'a_result'}) == 'negative') && 
		(strtolower($dt{'b_result'}) == 'negative'));
$cert = substr('000000' . $dt{'id'},-6);
?>

<br>
<div style="width 100%; margin: 12px; padding: 8px;">
<div style="float: right;"><span class="wmtPrnLabel">No. <?php echo $cert; ?></span></div>
<br>
<div style="width: 100% margin: 8px; text-align: center;">
<span class="wmtPrnLarge"><u>MEDICAL IN CONFIDENCE</u></span><br>
<span class="wmtPrnChapter">EUROMEDICAL FAMILY CLINIC</span><br>
<span class="wmtPrnLabel">VALIDATION OF FOOD/PESTICIDE HANDLER FITNESS CERTIFICATE</span><br>
</div>
<br>
<span class="wmtPrnBody">(Section 5(2) of the Occupational Safetyl and Health Decree, 1978, and Section 4(1) of the Food Act Genenral Hygiene Regulations, 1992).</span>
<br>
<br>
<div style="width: 100% margin: 8px; text-align: center;">
<span class="wmtPrnLabel">RE:&nbsp;&nbsp;<?php echo $patient->full_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;d.o.b.:&nbsp;&nbsp;<?php echo $patient->DOB; ?></span>
</div>
<br>
<?php if($negative) { ?>
<p class="wmtPrnBody">Stool/Nasal Culture tests of the above named are <b>NORMAL</b>, and validates for the next six monts the fitness certificate issued within the last two years, for the next six months. <?php echo ($pat_sex == 'f') ? 'She' : (($pat_sex == 'm') ? 'He' : 'S/He'); ?> is <b>FIT</b> to handle food/pesticides.
<?php } else { ?>
<p class="wmtPrnBody">The tests done on the above named are abnormal and <?php echo ($pat_sex == 'f') ? 'she' : (($pat_sex == 'm') ? 'he' : 's/he'); ?> is unfit to handle food/pesticides until medically cleared.
<?php } ?>
<br>
<br>
<?php 
if(strtolower($dt{'a_result'}) != 'negative') { 
	echo 'Nasal Swab Test Results [' . ucfirst($dt{'a_result'}) . ']<br>';
}
if($dt{'a_result_nt'}) {
	$comment_hdr = TRUE;
?>
	Remarks:<br>
	<?php echo htmlspecialchars($dt{'a_result_nt'},ENT_QUOTES); ?>
	<br>
<?php 
}
if(strtolower($dt{'b_result'}) != 'negative') { 
	echo 'Stool Sample Test Results [' . ucfirst($dt{'b_result'}) . ']<br>';
}
if($dt{'b_result_nt'}) { 
	if(!$comment_hdr) echo 'Remarks:<br>';
	echo htmlspecialchars($dt{'b_result_nt'},ENT_QUOTES); ?>
	<br>
<?php } ?>
</p>
<br>
<br>
<div class="wmtPrnBody" style="float:left;">Signature: .................................................</div>
<div class="wmtPrnBody" style="float: right; margin-right: 12px;">Date: <?php echo oeFormatShortDate(date('Y-m-d')); ?></div>
<br>
<br>
<div class="wmtPrnBody" style="float:left;">Designation: ...............................................</div>
</div>
