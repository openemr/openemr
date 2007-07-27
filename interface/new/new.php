<?php 
include_once("../globals.php");
?>
<html>

<head>
<link rel=stylesheet href="<?php echo xl($css_header,'e');?>" type="text/css">

<script LANGUAGE="JavaScript">

 function validate() {
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
  var f = document.forms[0];
  if (f.refsource.selectedIndex <= 0) {
   alert('Please select a referral source!');
   return false;
  }
<?php } ?>
  top.restoreSession();
  return true;
 }

</script>

</head>

<body <?php echo $top_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'
 onload="javascript:document.new_patient.fname.focus();">

<?php if ($GLOBALS['concurrent_layout']) { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 onsubmit='return validate()'>
<span class='title'><?php xl('New Patient','e');?></span>
<?php } else { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 target='_top' onsubmit='return validate()'>
<a class="title" href="../main/main_screen.php" target="_top" onclick="top.restoreSession()">
<?php xl('New Patient','e');?></a>
<?php } ?>

<br><br>

<center>

<?php if ($GLOBALS['omit_employers']) { ?>
   <input type='hidden' name='title' value='' />
<?php } ?>

<table border='0'>

<?php if (!$GLOBALS['omit_employers']) { ?>
 <tr>
  <td>
   <span class='bold'><?php xl('Title','e');?>: </span>
  </td>
  <td>
   <select name='title'>
    <option value="Mrs."><?php xl('Mrs.','e');?></option>
    <option value="Ms."><?php xl('Ms.','e');?></option>
    <option value="Mr."><?php xl('Mr.','e');?></option>
    <option value="Dr."><?php xl('Dr.','e');?></option>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?php xl('First Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='fname'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Middle Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='mname'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Last Name','e');?>: </span>
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
 foreach (array('', 'Patient', 'Employee', 'Walk-In', 'Newspaper', 'Radio',
  'T.V.', 'Direct Mail', 'Coupon', 'Referral Card', 'Other') as $rs)
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
   <span class='bold'><?php xl('Patient Number','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='5' name='pubpid'>
   <span class='text'><?php xl('omit to autoassign','e');?> &nbsp; &nbsp; </span>
  </td>
 </tr>

 <tr>
  <td colspan='2'>
   &nbsp;<br>
   <input type='submit' name='form_create' value=<?php xl('Create New Patient','e'); ?> />
  </td>
  <td>
  </td>
 </tr>

</table>
</center>
</form>

</body>
</html>
