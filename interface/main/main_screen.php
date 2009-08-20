<?php
 include_once("../globals.php");
 $_SESSION["encounter"] = "";

 if ($GLOBALS['athletic_team']) {
  $frame1url = "../reports/players_report.php?embed=1";
 } else {
  if (isset($_GET['mode']) && $_GET['mode'] == "loadcalendar") {
   $frame1url = "calendar/index.php?pid=" . $_GET['pid'];
   if (isset($_GET['date'])) $frame1url .= "&date=" . $_GET['date'];
  } else {
   if ($GLOBALS['concurrent_layout']) {
    // new layout
    if ($GLOBALS['default_top_pane']) {
      $frame1url=$GLOBALS['default_top_pane'];
     } else {
     $frame1url = "main_info.php";
     }
    }
   else
    // old layout
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
<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>
</script>

<?php if ($GLOBALS['concurrent_layout']) { // start new layout ?>

</head>
<!-- border (mozilla) and framespacing (ie) are the same thing.      -->
<!-- frameborder specifies a 3d look, not whether there are borders. -->
<frameset rows='<?php echo $GLOBALS['titleBarHeight'] ?>,*' frameborder='1' border='1' framespacing='1' onunload='imclosing()'>
 <frame src='main_title.php' name='Title' scrolling='no' frameborder='1' noresize />
 <frameset cols='<?php echo $GLOBALS['athletic_team'] ? '230' : '130'; ?>,*' id='fsbody' frameborder='1' border='4' framespacing='4'>
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
   <frame src='authorizations/authorizations.php' name='RBot' scrolling='auto' />
  </frameset>
 </frameset>
</frameset>

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
