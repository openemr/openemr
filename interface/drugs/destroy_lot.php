<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");

 function QuotedOrNull($fld) {
  if ($fld) return "'$fld'";
  return "NULL";
 }

 $drug_id = $_REQUEST['drug'];
 $lot_id  = $_REQUEST['lot'];
 $info_msg = "";

 if (!acl_check('admin', 'drugs')) die(xl('Not authorized'));
 if (!$drug_id) die(xl('Drug ID missing!'));
 if (!$lot_id ) die(xl('Lot ID missing!'));
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl ('Destroy Lot','e') ?></title>
<link rel="stylesheet" href='<?php  echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<style  type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">
 var mypcc = '<?php  echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save']) {
  sqlStatement("UPDATE drug_inventory SET " .
   "destroy_date = "     . QuotedOrNull($form_date) . ", "  .
   "destroy_method = '"  . $_POST['form_method']    . "', " .
   "destroy_witness = '" . $_POST['form_witness']   . "', " .
   "destroy_notes = '"   . $_POST['form_notes']     . "' "  .
   "WHERE drug_id = '$drug_id' AND inventory_id = '$lot_id'");

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = '$drug_id' " .
  "AND inventory_id = '$lot_id'");
?>

<form method='post' name='theform' action='destroy_lot.php?drug=<?php echo $drug_id ?>&lot=<?php echo $lot_id ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><?php  xl('Lot Number','e'); ?>:</b></td>
  <td>
   <?php  echo $row['lot_number'] ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Manufacturer','e'); ?>:</b></td>
  <td>
   <?php  echo $row['manufacturer'] ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Quantity On Hand','e'); ?>:</b></td>
  <td>
   <?php  echo $row['on_hand'] ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Expiration Date','e'); ?>:</b></td>
  <td>
   <?php  echo $row['expiration'] ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Date Destroyed','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_date' id='form_date'
    value='<?php  echo $row['destroy_date'] ? $row['destroy_date'] : date("Y-m-d"); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title=<?php xl('yyyy-mm-dd date destroyed','e','\'','\''); ?> />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title=<?php xl('Click here to choose a date','e','\'','\''); ?>>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Method of Destruction','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_method' maxlength='250'
    value='<?php  echo $row['destroy_method'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Witness','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_witness' maxlength='250'
    value='<?php  echo $row['destroy_witness'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php  xl('Notes','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_notes' maxlength='250'
    value='<?php  echo $row['destroy_notes'] ?>' style='width:100%' />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php xl('Submit','e') ;?>' />

&nbsp;
<input type='button' value='<?php xl('Cancel','e'); ?>' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});
</script>
</body>
</html>
