<?
include_once("../globals.php");
?>

<html>
<head>
<title>Navigation</title>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $nav_bg_line;?> topmargin=0 rightmargin=0 leftmargin=5 marginheight=0 bottommargin=0 link=#000000 vlink=#000000 alink=#000000>

<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/summary/summary_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/summary/patient_summary.php'" target="Main" class="menu">Summary</a>
</td>
<td valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/history/history_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/history/patient_history.php'" target="Main" class="menu">History</a>
</td>
<td valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/encounter/encounter_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/encounter/patient_encounter.php?mode=new'" target="Main" class="menu">Encounter</a>
</td>
<td valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/transaction/transaction_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/transaction/patient_transaction.php'" target="Main" class="menu">Transaction</a>
</td>
<td valign="middle">
<a href="<?echo $GLOBALS['web_root'];?>/controller.php?document&list&patient_id=<?=$pid?>" target="Main" class="menu">Documents</a>
</td>
<td valign="middle">
<a href="javascript:parent.Title.location.href='<?echo $rootdir;?>/patient_file/report/report_title.php';parent.Main.location.href='<?echo $rootdir;?>/patient_file/report/patient_report.php'" target="Main" class="menu">Report</a>
</td>
<td align="right" valign="middle">
<a href="../main/main_screen.php" target="_top" class="logout">Close</a>&nbsp;&nbsp;
</td>
</tr>
</table>

</body>
</html>
