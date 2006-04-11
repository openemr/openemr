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

 if (!acl_check('admin', 'drugs')) die("Not authorized!");
 if (!$drug_id) die("Drug ID missing!");
?>
<html>
<head>
<title><? echo $lot_id ? "Edit" : "Add New" ?> Lot</title>
<link rel=stylesheet href='<? echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>

<script language="JavaScript">
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body <?echo $top_bg_line;?>>
<?php
 // If we are saving, then save and close the window.
 //
 if ($_POST['form_save'] || $_POST['form_delete']) {
  if ($lot_id) {
   if ($_POST['form_save']) {
    sqlStatement("UPDATE drug_inventory SET " .
     "lot_number = '"   . $_POST['form_lot_number']      . "', " .
     "manufacturer = '" . $_POST['form_manufacturer']    . "', " .
     "expiration = "    . QuotedOrNull($form_expiration) . ", "  .
     "on_hand = '"      . $_POST['form_on_hand']         . "' " .
     "WHERE drug_id = '$drug_id' AND lot_number = '$lot_id'");
   } else {
    sqlStatement("DELETE FROM drug_inventory WHERE drug_id = '$drug_id' AND lot_number = '$lot_id'");
   }
  } else {
   $drug_id = sqlInsert("INSERT INTO drug_inventory ( " .
    "drug_id, lot_number, manufacturer, expiration, on_hand " .
    ") VALUES ( " .
    "'$drug_id', "                            .
    "'" . $_POST['form_lot_number']   . "', " .
    "'" . $_POST['form_manufacturer'] . "', " .
    QuotedOrNull($form_expiration)    . ", "  .
    "'" . $_POST['form_on_hand']      . "' " .
    ")");
  }

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('$info_msg');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 if ($lot_id) {
  $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = '$drug_id' " .
   "AND lot_number = '$lot_id'");
 }
?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' name='theform' action='add_edit_lot.php?drug=<?php echo $drug_id ?>&lot=<?php echo $lot_id ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><? xl('Lot Number','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_lot_number' maxlength='40' value='<? echo $lot_id ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Manufacturer','e'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_manufacturer' maxlength='250' value='<? echo $row['manufacturer'] ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('Expiration','e'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_expiration' value='<? echo $row['expiration'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd date of expiration' />
   <a href="javascript:show_calendar('theform.form_expiration')"
    title="Click here to choose a date"
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><? xl('On Hand','e'); ?>:</b></td>
  <td>
   <input type='text' size='5' name='form_on_hand' maxlength='7' value='<? echo $row['on_hand'] ?>' />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='Save' />

&nbsp;
<input type='button' value='Cancel' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
