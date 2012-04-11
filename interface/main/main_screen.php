<?php
 include_once("../globals.php");
 require_once("$srcdir/formdata.inc.php");
 $_SESSION["encounter"] = "";

 // Fetching the password expiration date
 $is_expired=false;
 if($GLOBALS['password_expiration_days'] != 0){
 $is_expired = false;
 $q=formData('authUser','P');
 $result = sqlStatement("select pwd_expiration_date from users where username = '".$q."'");
 $current_date = date("Y-m-d");
 $pwd_expires_date = $current_date;
 if($row = sqlFetchArray($result)) {
  $pwd_expires_date = $row['pwd_expiration_date'];
 }

// Displaying the password expiration message (starting from 7 days before the password gets expired)
 $pwd_alert_date = date("Y-m-d", strtotime($pwd_expires_date . "-7 days"));

 if (strtotime($pwd_alert_date) != "" && strtotime($current_date) >= strtotime($pwd_alert_date) && 
     (!isset($_SESSION['expiration_msg']) or $_SESSION['expiration_msg'] == 0)) {

  $is_expired = true;
  $_SESSION['expiration_msg'] = 1; // only show the expired message once
 }
}

if ($is_expired) {
  $frame1url = "pwd_expires_alert.php"; //php file which display's password expiration message.
}
else if (!empty($_POST['patientID'])) {
  $patientID = 0 + $_POST['patientID'];
  $frame1url = "../patient_file/summary/demographics.php?set_pid=$patientID";
}
else if ($GLOBALS['athletic_team']) {
  $frame1url = "../reports/players_report.php?embed=1";
}
else if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
  $frame1url = "calendar/index.php?pid=" . $_GET['pid'];
  if (isset($_GET['date'])) $frame1url .= "&date=" . $_GET['date'];
}
else if ($GLOBALS['concurrent_layout']) {
  // new layout
  if ($GLOBALS['default_top_pane']) {
    $frame1url=$GLOBALS['default_top_pane'];
  } else {
    $frame1url = "main_info.php";
  }
}
else {
  // old layout
  $frame1url = "main.php?mode=" . $_GET['mode'];
}

$nav_area_width = $GLOBALS['athletic_team'] ? '230' : '130';
if (!empty($GLOBALS['gbl_nav_area_width'])) $nav_area_width = $GLOBALS['gbl_nav_area_width'];
?>
<html>
<head>
<title>
<?php echo $openemr_name ?>
</title>
<script type="text/javascript" src="../../library/topdialog.js"></script>

<script language='JavaScript'>
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

</head>

<?php if ($GLOBALS['concurrent_layout']) { // start new layout ?>

<?php if (empty($GLOBALS['gbl_tall_nav_area'])) { // not tall nav area ?>

<!-- border (mozilla) and framespacing (ie) are the same thing.      -->
<!-- frameborder specifies a 3d look, not whether there are borders. -->
<frameset rows='<?php echo $GLOBALS['titleBarHeight'] + 5 ?>,*' frameborder='1' border='1' framespacing='1' onunload='imclosing()'>
 <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' noresize />
 <frameset cols='<?php echo $nav_area_width; ?>,*' id='fsbody' frameborder='1' border='4' framespacing='4'>
  <frameset rows='*,0' frameborder='0' border='0' framespacing='0'>
   <frame src='left_nav.php' name='left_nav' />
   <frame src='daemon_frame.php' name='Daemon' scrolling='no' frameborder='0'
    border='0' framespacing='0' />
  </frameset>
<?php if (empty($GLOBALS['athletic_team'])) { ?>
  <frameset rows='60%,*' id='fsright' bordercolor='#999999' frameborder='1'>
<?php } else { ?>
  <frameset rows='100%,*' id='fsright' bordercolor='#999999' frameborder='1'>
<?php } ?>
   <frame src='<?php echo $frame1url ?>' name='RTop' scrolling='auto' />
   <frame src='messages/messages.php?form_active=1' name='RBot' scrolling='auto' />
  </frameset>
 </frameset>
</frameset>

<?php } else { // use tall nav area ?>

<frameset cols='<?php echo $nav_area_width; ?>,*' id='fsbody' frameborder='1' border='4' framespacing='4' onunload='imclosing()'>
 <frameset rows='*,0' frameborder='0' border='0' framespacing='0'>
  <frame src='left_nav.php' name='left_nav' />
  <frame src='daemon_frame.php' name='Daemon' scrolling='no' frameborder='0'
   border='0' framespacing='0' />
 </frameset>
 <frameset rows='<?php echo $GLOBALS['titleBarHeight'] + 5 ?>,*' frameborder='1' border='1' framespacing='1'>
  <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' />
<?php if (empty($GLOBALS['athletic_team'])) { ?>
  <frameset rows='60%,*' id='fsright' bordercolor='#999999' frameborder='1' border='4' framespacing='4'>
<?php } else { ?>
  <frameset rows='100%,*' id='fsright' bordercolor='#999999' frameborder='1' border='4' framespacing='4'>
<?php } ?>
   <frame src='<?php echo $frame1url ?>' name='RTop' scrolling='auto' />
   <frame src='messages/messages.php?form_active=1' name='RBot' scrolling='auto' />
  </frameset>
 </frameset>
</frameset>

<?php } // end tall nav area ?>

<?php } else { // start old layout ?>

</head>
<frameset rows="<?php echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*"
  cols="*" frameborder="no" border="0" framespacing="0"
  onunload="imclosing()">
  <frame src="main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="no">
  <frame src="main_title.php" name="Title" scrolling="no" noresize frameborder="no">
  <frame src='<?php echo $frame1url ?>' name='Main' scrolling='auto' noresize frameborder='no'>
</frameset>
<noframes><body bgcolor="#FFFFFF">
Frame support required
</body></noframes>

<?php } // end old layout ?>

</html>
