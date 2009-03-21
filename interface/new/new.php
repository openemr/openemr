<?php
include_once("../globals.php");

// Determine if the registration date should be requested.
$crow = sqlQuery("SELECT count(*) AS count FROM layout_options WHERE " .
  "form_id = 'DEM' AND field_id = 'regdate' AND uor > 0");
$regstyle = $crow['count'] ? "" : " style='display:none'";

$form_pubpid    = $_POST['pubpid'   ] ? trim($_POST['pubpid'   ]) : '';
$form_title     = $_POST['title'    ] ? trim($_POST['title'    ]) : '';
$form_fname     = $_POST['fname'    ] ? trim($_POST['fname'    ]) : '';
$form_mname     = $_POST['mname'    ] ? trim($_POST['mname'    ]) : '';
$form_lname     = $_POST['lname'    ] ? trim($_POST['lname'    ]) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_sex       = $_POST['sex'      ] ? trim($_POST['sex'      ]) : '';
$form_refsource = $_POST['refsource'] ? trim($_POST['refsource']) : '';
$form_dob       = $_POST['DOB'      ] ? trim($_POST['DOB'      ]) : '';
$form_regdate   = $_POST['regdate'  ] ? trim($_POST['regdate'  ]) : date('Y-m-d');
?>
<html>

<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo xl($css_header,'e');?>" type="text/css">
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '1';

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

<body class="body_top" onload="javascript:document.new_patient.fname.focus();">

<?php if ($GLOBALS['concurrent_layout']) { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 onsubmit='return validate()'>
<span class='title'><?php xl('Add Patient Record','e');?></span>
<?php } else { ?>
<form name='new_patient' method='post' action="new_patient_save.php"
 target='_top' onsubmit='return validate()'>
<a class="title" href="../main/main_screen.php" target="_top" onclick="top.restoreSession()">
<?php xl('Add Patient Record','e');?></a>
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
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'titles' ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
  echo "    <option value='" . $orow['option_id'] . "'";
  if ($orow['option_id'] == $form_title) echo " selected";
  echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?php xl('First Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='fname' value='<?php echo $form_fname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Middle Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='mname' value='<?php echo $form_mname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Last Name','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='15' name='lname' value='<?php echo $form_lname; ?>'>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Sex','e'); ?>: </span>
  </td>
  <td>
   <select name='sex'>
    <option value=''>Unassigned</option>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'sex' ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
  echo "    <option value='" . $orow['option_id'] . "'";
  if ($orow['option_id'] == $form_sex) echo " selected";
  echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>

<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
 <tr>
  <td>
   <span class='bold'><?php xl('Referral Source','e'); ?>: </span>
  </td>
  <td>
   <select name='refsource'>
    <option value=''>Unassigned</option>
<?php
$ores = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'refsource' ORDER BY seq");
while ($orow = sqlFetchArray($ores)) {
  echo "    <option value='" . $orow['option_id'] . "'";
  if ($orow['option_id'] == $form_refsource) echo " selected";
  echo ">" . $orow['title'] . "</option>\n";
}
?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td>
   <span class='bold'><?php xl('Birth Date','e');?>: </span>
  </td>
  <td>
   <input type='text' size='10' name='DOB' id='DOB'
    value='<?php echo $form_dob; ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_dob' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
   <script LANGUAGE="JavaScript">
    Calendar.setup({inputField:"DOB", ifFormat:"%Y-%m-%d", button:"img_dob"});
   </script>
  </td>
 </tr>

 <tr<?php echo $regstyle ?>>
  <td>
   <span class='bold'><?php xl('Registration Date','e');?>: </span>
  </td>
  <td>
   <input type='text' size='10' name='regdate' id='regdate'
    value='<?php echo $form_regdate; ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_regdate' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
   <script LANGUAGE="JavaScript">
    Calendar.setup({inputField:"regdate", ifFormat:"%Y-%m-%d", button:"img_regdate"});
   </script>
  </td>
 </tr>

 <tr>
  <td>
   <span class='bold'><?php xl('Patient Number','e');?>: </span>
  </td>
  <td>
   <input type='entry' size='5' name='pubpid' value='<?php echo $form_pubpid; ?>'>
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
<script language="Javascript">
<?php
if ($form_pubpid) { 
  echo "alert('" . xl('This patient ID is already in use!') . "');\n";
}
?>
</script>

</body>
</html>
