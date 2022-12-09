<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array('init_lmp', 'init_lmp_edd', 'init_exam', 'init_exam_wks', 
	'init_exam_edd', 'init_ultra', 'init_ultra_wks', 'init_ultra_edd', 'init_edd',
	'init_edd_by', 'upd_quick', 'upd_quick_edd', 'upd_fundal', 'upd_fundal_edd',
	'upd_ultra', 'upd_ultra_wks', 'upd_ultra_edd', 'upd_edd', 'upd_by');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="wmtBorder1R" style="width: 50%; ">
    <table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="wmtLabel wmtBorder1B" style="width: 25%;">Initial EDD</td>
        <td class="wmtLabel wmtC wmtBorder1B" colspan="3">EDD Confirmation</td>
      </tr>
      <tr>
        <td class="wmtBody">&nbsp;&nbsp;&nbsp;LMP:</td>
        <td><input name="init_lmp" id="init_lmp" class="wmtInput" style="width: 85px" type="text" value="<?php echo htmlspecialchars($dt{'init_lmp'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" onchange="FutureFromDate('init_lmp', '281', 'init_lmp_edd'); document.getElementById('db_last_mp').value = this.value;"/></td>
        <td class="wmtBody wmtC">=</td>
        <td class="wmtBody">EDD:&nbsp;&nbsp;&nbsp;<input name="init_lmp_edd" id="init_lmp_edd" class="wmtInput" style="width: 85px" type="text" value="<?php echo htmlspecialchars($dt{'init_lmp_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">&nbsp;&nbsp;&nbsp;Initial Exam:</td>
        <td><input name="init_exam" id="init_exam" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'init_exam'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
        <td class="wmtBody wmtC">&#61;&nbsp;&nbsp;<input name="init_exam_wks" id="init_exam_wks" style="width: 30px" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{'init_exam_wks'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;&nbsp;WKS =</td>
        <td class="wmtBody">EDD:&nbsp;&nbsp;&nbsp;<input name="init_exam_edd" id="init_exam_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'init_exam_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">&nbsp;&nbsp;&nbsp;Ultrasound:</td>
        <td><input name="init_ultra" id="init_ultra" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'init_ultra'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
        <td class="wmtBody wmtC">&#61;&nbsp;&nbsp;<input name="init_ultra_wks" id="init_ultra_wks" style="width: 30px" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{'init_ultra_wks'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;&nbsp;WKS =</td>
        <td class="wmtBody">EDD:&nbsp;&nbsp;&nbsp;<input name="init_ultra_edd" id="init_ultra_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'init_ultra_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">&nbsp;&nbsp;&nbsp;Initial EDD:</td>
        <td><input name="init_edd" id="init_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'init_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
        <td class="wmtBody wmtC">Initialed By:</td>
        <td><input name="init_edd_by" id="init_edd_by" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{'init_edd_by'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
    </table></td>
    <td style="width: 50%"><table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="wmtLabel wmtC wmtBorder1B" colspan="4">18 - 20 Week EDD Update</td>
      </tr>
      <tr>
        <td class="wmtBody">Quickening:</td>
        <td><input name="upd_quick" id="upd_quick" class="wmtInput" style="width: 85px" type="text" value="<?php echo htmlspecialchars($dt{'upd_quick'}, ENT_QUOTES, '', FALSE); ?>" title="YYY-MM-DD" /></td>
        <td class="wmtBody wmtC">&#43; 22 Weeks &#61;</td>
        <td><input name="upd_quick_edd" id="upd_quick_edd" class="wmtInput" style="width: 85px" type="text" value="<?php echo htmlspecialchars($dt{'upd_quick_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">Fundal Ht. At Umbil.</td>
        <td><input name="upd_fundal" id="upd_fundal" class="wmtInput" type="text" style="width: 85px;" value="<?php echo htmlspecialchars($dt{'upd_fundal'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="wmtBody" align="center">&#43; 20 Weeks &#61;</td>
        <td><input name="upd_fundal_edd" id="upd_fundal_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'upd_fundal_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">Ultrasound:</td>
        <td><input name="upd_ultra" id="upd_ultra" class="wmtInput" type="text" style="width: 85px;" value="<?php echo htmlspecialchars($dt{'upd_ultra'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="wmtBody" align="center">&#61;&nbsp;&nbsp;<input name="upd_ultra_wks" id="upd_ultra_wks" style="width: 30px" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{'upd_ultra_wks'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;&nbsp;WKS &#61;</td>
        <td><input name="upd_ultra_edd" id="upd_ultra_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'upd_ultra_edd'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
      <tr>
        <td class="wmtBody">Final EDD:</td>
        <td><input name="upd_edd" id="upd_edd" class="wmtInput" type="text" style="width: 85px" value="<?php echo htmlspecialchars($dt{'upd_edd'}, ENT_QUOTES, '', FALSE); ?>" onChange="calc_all_weeks();" title="YYYY-MM-DD" /></td>
        <td class="wmtBody wmtC">Initialed By:</td>
        <td><input name="upd_by" id="upd_by" class="wmtInput" style="width: 85px;" type="text" value="<?php echo htmlspecialchars($dt{'upd_by'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
    </table></td>
  </tr> 
</table>
	<?php if($client_id == 'sfa') { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
	<tr>
		<td class="wmtBorder1T" style="width: 12px"><input name="xfer_care" id="xfer_care" type="checkbox" value="1" <?php echo (($dt{'xfer_care'} == 1)?' checked ':''); ?> /></td>
		<td class="wmtLabel wmtBorder1T" style="width: 190px;"><label for="xfer_care">Patient Care Transferred To:</label></td>
    <td class="wmtBorder1T"><input name="xfer_to" id="xfer_to" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{'xfer_to'}, ENT_QUOTES, '', FALSE); ?>" /></td>
		<td class="wmtLabel wmtBorder1T wmtC" style="width: 35px;">as of</td>
		<td class="wmtDateCell wmtR wmtBorder1T"><input name="xfer_on" id="xfer_on" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{'xfer_on'}, ENT_QUOTES, '', FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" onFocus="setEmptyDate('xfer_on');" title="YYYY-MM-DD" /></td>
			<td class="wmtCalendarCell wmtL wmtBorder1T"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_xfer_on" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"xfer_on", ifFormat:"%Y-%m-%d", button:"img_xfer_on"});
			</script>
	</tr>
	<?php } ?>
</table>

<script type="text/javascript">
function FutureFromDate(thisDate, numDays, target)
{
	var lmp = document.getElementById(thisDate).value;
	if(lmp == 0 || lmp == '') return false;	
	lmp = new Date(lmp);
	if(lmp == 'Invalid Date') {
		alert("Not a Valid Date, Use 'YYYY-MM-DD' to Calculate EDD");
		return false;
	}
	var seconds = lmp.getTime();
	seconds = seconds + (86400000 * numDays);
	var edd= new Date();
	edd.setTime(seconds);
  var myYear= edd.getFullYear();
  var myMonth= "00" + (edd.getMonth()+1);
  myMonth= myMonth.slice(-2);
  var myDays= "00" + edd.getDate();
  myDays= myDays.slice(-2);
	myYear= myYear + "-" + myMonth + "-" + myDays;
	document.getElementById(target).value= myYear;
}

</script>
<?php ?>
