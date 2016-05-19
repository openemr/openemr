<?php
 include_once("../globals.php");
 include_once("$srcdir/acl.inc");

 $ie_auth = ((acl_check('encounters','notes','','write') ||
              acl_check('encounters','notes_a','','write')) &&
             acl_check('patients','med','','write'));
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Navigation','e'); ?></title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">
// This is invoked to pop up some window when a popup item is selected.
function selpopup(selobj) {
 var i = selobj.selectedIndex;
 var opt = selobj.options[i];
 if (i > 0) {
  var width  = 750;
  var height = 550;
  if (opt.text == 'Export' || opt.text == 'Import') {
   width  = 500;
   height = 400;
  }
  else if (opt.text == 'Refer') {
   width  = 700;
   height = 500;
  }
  dlgopen(opt.value, '_blank', width, height);
 }
 selobj.selectedIndex = 0;
}
</script>
</head>

<body class="body_nav">

<div id="nav_topmenu">
<form>
<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">
 <tr>
  <td align="center" valign="middle">
   <a href="javascript:top.restoreSession();parent.Title.location.href='<?php echo $rootdir;?>/patient_file/summary/summary_title.php';parent.Main.location.href='<?php echo $rootdir;?>/patient_file/summary/patient_summary.php'" target="Main" class="menu"><?php xl('Summary','e'); ?></a>
  </td>
  <td align="center" valign="middle">
   <a href="javascript:top.restoreSession();parent.Title.location.href='<?php echo $rootdir;?>/patient_file/history/history_title.php';parent.Main.location.href='<?php echo $rootdir;?>/patient_file/history/patient_history.php'" target="Main" class="menu"><?php xl('History','e'); ?></a>
  </td>
  <td align="center" valign="middle">
   <a href="javascript:top.restoreSession();parent.Title.location.href='<?php echo $rootdir;?>/patient_file/encounter/encounter_title.php';parent.Main.location.href='<?php echo $rootdir;?>/patient_file/encounter/patient_encounter.php?mode=new'" target="Main" class="menu"><?php xl('Encounter','e'); ?></a>
  </td>
  <td align="center" valign="middle">
   <a href="javascript:top.restoreSession();parent.Title.location.href='<?php echo $rootdir;?>/patient_file/transaction/transaction_title.php';parent.Main.location.href='<?php echo $rootdir;?>/patient_file/transaction/patient_transaction.php'" target="Main" class="menu"><?php xl('Transaction','e'); ?></a>
  </td>
  <td align="center" valign="middle">
   <a href="<?php echo $GLOBALS['web_root'];?>/controller.php?document&list&patient_id=<?php echo $pid?>"
    target="Main" class="menu" onclick="top.restoreSession()"><?php xl('Documents','e'); ?></a>
  </td>
  <td align="center" valign="middle">
   <a href="javascript:top.restoreSession();parent.Title.location.href='<?php echo $rootdir;?>/patient_file/report/report_title.php';parent.Main.location.href='<?php echo $rootdir;?>/patient_file/report/patient_report.php'" target="Main" class="menu"><?php xl('Report','e'); ?></a>
  </td>
  <td align="center" align="right" valign="middle">
   <a href="../main/main_screen.php" target="_top" class="logout" onclick="top.restoreSession()"><?php xl('Close','e'); ?></a>&nbsp;&nbsp;
  </td>
  <td align="right" valign="middle">
    <select onchange='selpopup(this)'>
     <option value=''><?php xl('Popups','e'); ?></option>
<?php if ($ie_auth) { ?>
     <option value='problem_encounter.php'><?php xl('Issues','e'); ?></option>
<?php } ?>
     <option value='../../custom/export_xml.php'><?php xl('Export','e'); ?></option>
     <option value='../../custom/import_xml.php'><?php xl('Import','e'); ?></option>
     <option value='../reports/appointments_report.php?patient=<?php echo $pid ?>'><?php xl('Appts','e'); ?></option>
<?php if (file_exists("$webserver_root/custom/refer.php")) { ?>
     <option value='../../custom/refer.php'><?php xl('Refer','e'); ?></option>
<?php } ?>
<?php if (file_exists("$webserver_root/custom/fee_sheet_codes.php")) { ?>
 <option value='printed_fee_sheet.php'><?php xl('Superbill','e'); ?></option>
<?php } ?>
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
     <option value='front_payment.php'><?php xl('Prepay','e'); ?></option>
     <option value='pos_checkout.php'><?php xl('Checkout','e'); ?></option>
<?php } else { ?>
     <option value='front_payment.php'><?php xl('Payment','e'); ?></option>
<?php } ?>
     <option value='letter.php'><?php xl('Letter','e'); ?></option>
    </select>
  </td>
 </tr>
</table>
</form>
</div>

</body>
</html>
