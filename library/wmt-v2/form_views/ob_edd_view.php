<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($chp_title)) $chp_title = 'Estimated Delivery Dates';
$local_fields = array('init_lmp', 'init_lmp_edd', 'init_exam', 'init_exam_wks', 
	'init_exam_edd', 'init_ultra', 'init_ultra_wks', 'init_ultra_edd', 'init_edd',
	'init_edd_by', 'upd_quick', 'upd_quick_edd', 'upd_fundal', 'upd_fundal_edd',
	'upd_ultra', 'upd_ultra_wks', 'upd_ultra_edd', 'upd_edd', 'upd_by');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
}
$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
  <tr>
    <td class="wmtPrnBorder1R" style="width: 50%; ">
    <table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="wmtPrnLabel wmtPrnBorder1B" style="width: 25%;">Initial EDD</td>
        <td class="wmtPrnLabel wmtPrnC wmtPrnBorder1B" colspan="3">EDD Confirmation</td>
      </tr>
      <tr>
        <td class="wmtPrnBody">&nbsp;&nbsp;&nbsp;LMP:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'init_lmp'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">=</td>
        <td class="wmtPrnBody">EDD:&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'init_lmp_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">&nbsp;&nbsp;&nbsp;Initial Exam:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'init_exam'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">&#61;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'init_exam_wks'}, ENT_QUOTES, '', FALSE); ?>&nbsp;&nbsp;WKS =</td>
        <td class="wmtPrnBody">EDD:&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'init_exam_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">&nbsp;&nbsp;&nbsp;Ultrasound:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'init_ultra'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">&#61;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'init_ultra_wks'}, ENT_QUOTES, '', FALSE); ?>&nbsp;&nbsp;WKS =</td>
        <td class="wmtPrnBody">EDD:&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'init_ultra_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">&nbsp;&nbsp;&nbsp;Initial EDD:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'init_edd'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">Initialed By:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'init_edd_by'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
    </table></td>
    <td style="width: 50%"><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="wmtPrnLabel wmtPrnC wmtPrnBorder1B" colspan="4">18 - 20 Week EDD Update</td>
      </tr>
      <tr>
        <td class="wmtPrnBody">Quickening:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_quick'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">&#43; 22 Weeks &#61;</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_quick_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">Fundal Ht. At Umbil.</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_fundal'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody" align="center">&#43; 20 Weeks &#61;</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_fundal_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">Ultrasound:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_ultra'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody" align="center">&#61;&nbsp;&nbsp;<?php echo htmlspecialchars($dt{'upd_ultra_wks'}, ENT_QUOTES, '', FALSE); ?>&nbsp;&nbsp;WKS &#61;</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_ultra_edd'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td class="wmtPrnBody">Final EDD:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_edd'}, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody wmtPrnC">Initialed By:</td>
        <td class="wmtPrnBody">&nbsp;<?php echo htmlspecialchars($dt{'upd_by'}, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
    </table></td>
  </tr> 
<?php 
if($client_id == 'sfa') {
	if($dt{'xfer_to'} || $dt{'xfer_care'} || $dt{'xfer_on'} ) {
		if(!$dt{'xfer_to'}) $dt{'xfer_to'} = 'Not Specified';
		if(!$dt{'xfer_on'}) $dt{'xfer_on'} = 'Date Not Specified';
?>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="wmtPrnLabel wmtPrnBorder1T" style="width: 190px;">Patient Care Transferred To:</td>
		<td class="wmtPrnBody wmtPrnBorder1T"><?php echo $dt{'xfer_to'}; ?>&nbsp;&nbsp;&nbsp;as of&nbsp;&nbsp;&nbsp;<?php echo $dt{'xfer_on'}; ?></td>
	</tr>
<?php
	}
}
?>
