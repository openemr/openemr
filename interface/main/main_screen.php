<?
 include_once("../globals.php");
 $_SESSION["encounter"] = "";

 if ($GLOBALS['athletic_team']) {
  $frame1url = "../reports/players_report.php?embed=1";
 } else {
  if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
   $frame1url = "calendar/index.php?pid=" . $_GET['pid'];
   if (isset($_GET['date'])) $frame1url .= "&date=" . $_GET['date'];
  } else {
   if ($GLOBALS['concurrent_layout'])
    $frame1url = "main_info.php";
   else
    $frame1url = "main.php?mode=" . $_GET['mode'];
  }
 }
?>
<html>
<head>
<title>
<?php echo $openemr_name ?>
</title>
<script type="text/javascript" src="../../library/topdialog.js"></script>

<script language='JavaScript'>

// login.php makes sure the session ID captured here is different for each
// new login.  We maintain it here because most browsers do not have separate
// cookie storage for different top-level windows.  This function should be
// called just prior to invoking any server script that requires correct
// session data.  onclick="top.restoreSession()" usually does the job.
//
function restoreSession() {
 document.cookie = '<?php echo session_name() . '=' . session_id(); ?>; path=/';
 return true;
}

</script>

<?php if ($GLOBALS['concurrent_layout']) { // start new layout ?>

</head>
<!-- border (mozilla) and framespacing (ie) are the same thing.      -->
<!-- frameborder specifies a 3d look, not whether there are borders. -->

<frameset rows='<?php echo $GLOBALS['titleBarHeight'] ?>,*' frameborder='1' border='1' framespacing='1' onunload='imclosing()'>
 <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' noresize />
 <frameset cols='130,*' id='fsbody' frameborder='1' border='4' framespacing='4'>
  <frameset rows='*,0' frameborder='0' border='0' framespacing='0'>
   <frame src='left_nav.php' name='left_nav' />
   <frame src='daemon_frame.php' name='Daemon' scrolling='no' frameborder='0'
    border='0' framespacing='0' />
  </frameset>
  <frameset rows='60%,*' id='fsright' bordercolor='#999999' frameborder='1'>
   <frame src='<?php echo $frame1url ?>' name='RTop' scrolling='auto' />
   <frame src='authorizations/authorizations.php' name='RBot' scrolling='auto' />
  </frameset>
 </frameset>
</frameset>

<?php } else { // start old layout ?>

</head>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*"
  cols="*" frameborder="no" border="0" framespacing="0"
  onunload="imclosing()">
  <frame src="main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="no">
  <frame src="main_title.php" name="Title" scrolling="no" noresize frameborder="no">
  <frame src='<?php echo $frame1url ?>' name='Main' scrolling='auto' noresize frameborder='no'>
</frameset>
<noframes><body bgcolor="#FFFFFF">
</body></noframes>

<?php } // end old layout ?>

</html>
