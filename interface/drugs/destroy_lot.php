<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 $sanitize_all_escapes  = true;
 $fake_register_globals = false;

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("drugs.inc.php");
 require_once("$srcdir/formdata.inc.php");
 require_once("$srcdir/htmlspecialchars.inc.php");

 function QuotedOrNull($fld) {
  if ($fld) return "'".add_escape_custom($fld)."'";
  return "NULL";
 }

 $drug_id = $_REQUEST['drug'];
 $lot_id  = $_REQUEST['lot'];
 $info_msg = "";

 if (!acl_check('admin', 'drugs')) die(xlt('Not authorized'));
 if (!$drug_id) die(xlt('Drug ID missing!'));
 if (!$lot_id ) die(xlt('Lot ID missing!'));
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo xlt('Destroy Lot') ?></title>
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
   "destroy_date = "     . QuotedOrNull($_POST['form_date']) . ", "  .
   "destroy_method = '"  . add_escape_custom($_POST['form_method'])    . "', " .
   "destroy_witness = '" . add_escape_custom($_POST['form_witness'])   . "', " .
   "destroy_notes = '"   . add_escape_custom($_POST['form_notes'])     . "' "  .
   "WHERE drug_id = ? AND inventory_id = ?", array($drug_id,$lot_id) );

  // Close this window and redisplay the updated list of drugs.
  //
  echo "<script language='JavaScript'>\n";
  if ($info_msg) echo " alert('".addslashes($info_msg)."');\n";
  echo " window.close();\n";
  echo " if (opener.refreshme) opener.refreshme();\n";
  echo "</script></body></html>\n";
  exit();
 }

 $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = ? " .
  "AND inventory_id = ?", array($drug_id,$lot_id));
?>

<form method='post' name='theform' action='destroy_lot.php?drug=<?php echo attr($drug_id) ?>&lot=<?php echo attr($lot_id) ?>'>
<center>

<table border='0' width='100%'>

 <tr>
  <td valign='top' width='1%' nowrap><b><?php echo xlt('Lot Number'); ?>:</b></td>
  <td>
   <?php echo text($row['lot_number']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Manufacturer'); ?>:</b></td>
  <td>
   <?php echo text($row['manufacturer']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Quantity On Hand'); ?>:</b></td>
  <td>
   <?php echo text($row['on_hand']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Expiration Date'); ?>:</b></td>
  <td>
   <?php echo text($row['expiration']) ?>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Date Destroyed'); ?>:</b></td>
  <td>
   <input type='text' size='10' name='form_date' id='form_date'
    value='<?php echo $row['destroy_date'] ? attr($row['destroy_date']) : date("Y-m-d"); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='<?php echo xla('yyyy-mm-dd date destroyed'); ?>' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php echo xla('Click here to choose a date'); ?>'>
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Method of Destruction'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_method' maxlength='250'
    value='<?php echo text($row['destroy_method']) ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Witness'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_witness' maxlength='250'
    value='<?php echo text($row['destroy_witness']) ?>' style='width:100%' />
  </td>
 </tr>

 <tr>
  <td valign='top' nowrap><b><?php echo xlt('Notes'); ?>:</b></td>
  <td>
   <input type='text' size='40' name='form_notes' maxlength='250'
    value='<?php echo text($row['destroy_notes']) ?>' style='width:100%' />
  </td>
 </tr>

</table>

<p>
<input type='submit' name='form_save' value='<?php echo xla('Submit') ;?>' />

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>

</center>
</form>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_date"});
</script>
</body>
</html>
