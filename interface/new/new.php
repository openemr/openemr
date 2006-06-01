<?
include_once("../globals.php");
?>
<html>

<head>
<link rel=stylesheet href="<?echo xl($css_header,'e');?>" type="text/css">

<script LANGUAGE="JavaScript">

 function validate() {
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
  var f = document.forms[0];
  if (f.refsource.selectedIndex <= 0) {
   alert('Please select a referral source!');
   return false;
  }
<?php } ?>
  return true;
 }

</script>

</head>

<body <?echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'
 onload="javascript:document.new_patient.fname.focus();">

<form name='new_patient' method='post' action="new_patient_save.php"
 target='_top' onsubmit='return validate()'>
<a class="title" href="../main/main_screen.php" target="_top"><?xl('New Patient','e');?></a>

<br><br>

<center>

<table border='0'>

 <tr>
  <td>
   <span class='bold'><?xl('Title','e');?>: </span>
  </td>
  <td>
   <select name='title'>
    <option value="Mr."><?xl('Mr.','e');?></option>
    <option value="Mrs."><?xl('Mrs.','e');?></option>
    <option value="Ms."><?xl('Ms.','e');?></option>
    <option value="Dr."><?xl('Dr.','e');?></option>
   </select>
  </td>
  <td rowspan='5' class='bold'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?xl('First Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='fname'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?xl('Middle Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='mname'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?xl('Last Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='lname'>
  </td>
 </tr>

<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
 <tr>
  <td>
   <span class='bold'><?php xl('Referral Source','e'); ?>: </span>
  </td>
  <td>
   <select name='refsource'>
<?php
 foreach (array('', 'Patient', 'Employee', 'Walk-In', 'Newspaper',
  'Direct Mail', 'Coupon', 'Other') as $rs)
 {
  echo "    <option value='$rs'>$rs</option>\n";
 }
?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?xl('Patient Number','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='5' name='pubpid'>
   <span class='text'><?xl('omit to autoassign','e');?> &nbsp; &nbsp; </span>
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;<br>
   <input type='submit' name='form_create' value='Create New Patient' />
  </td>
  <td>
  </td>
 </tr>

</table>
</center>
</form>

</body>
</html>
