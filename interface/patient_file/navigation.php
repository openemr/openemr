<?
include_once("../globals.php");
?>

<html>
<head>
<title>Navigation</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script language="JavaScript">
// This is invoked to pop up some window when a popup item is selected.
function selpopup(selobj) {
 var i = selobj.selectedIndex;
 if (i > 0) {
  var parms = (i == 1) ?
   'menubar=1,resizable=1' :
   'width=400,height=300,resizable=1';
  window.open(selobj.options[i].value, '_blank', parms);
 }
 selobj.selectedIndex = 0;
}
</script>

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=0 leftmargin=5 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td align="center" valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/summary/summary_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/summary/patient_summary.php'" target="Main" class="menu">Summary</a>
</td>
<td align="center" valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/history/history_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/history/patient_history.php'" target="Main" class="menu">History</a>
</td>
<td align="center" valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/encounter/encounter_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/encounter/patient_encounter.php?mode=new'" target="Main" class="menu">Encounter</a>
</td>
<td align="center" valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/transaction/transaction_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/transaction/patient_transaction.php'" target="Main" class="menu">Transaction</a>
</td>
<td align="center" valign="middle">
<a href="<?echo $GLOBALS['web_root'];?>/controller.php?document&list&patient_id=<?=$pid?>" target="Main" class="menu">Documents</a>
</td>
<td align="center" valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/report/report_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/report/patient_report.php'" target="Main" class="menu">Report</a>
</td>
<td align="center" align="right" valign="middle">
<a href="../main/main_screen.php" target="_top" class="logout">Close</a>&nbsp;&nbsp;
</td>
<td align="right" valign="middle">
 <form>
  <select onchange='selpopup(this)' style='background-color:transparent'>
   <option value=''>Popups</option>
   <option value='problem_encounter.php'>Issues</option>
   <option value='../../custom/export_demographics.php'>Export</option>
  </select>
 </form>
 <!-- <a href="<?echo $rootdir;?>/patient_file/problem_encounter.php" target="_blank" class="menu">Issues</a> -->
</td>
</tr>
</table>

</body>
</html>
