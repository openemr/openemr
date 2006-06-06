<?
include_once("../globals.php");
$_SESSION["encounter"] = "";
?>
<html>
<head>
<title>
<?php echo $openemr_name ?>
</title>
<script type="text/javascript" src="../../library/topdialog.js"></script>
</head>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*"
  cols="*" frameborder="no" border="0" framespacing="0"
  onunload="imclosing()">
  <frame src="main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="no">
  <frame src="main_title.php" name="Title" scrolling="no" noresize frameborder="no">
<?
 if ($GLOBALS['athletic_team']) {
  echo "  <frame src='../reports/players_report.php?embed=1' " .
   "name='Main' scrolling='auto' noresize frameborder='no'>\n";
 } else {
  if (isset($_GET[mode]) && $_GET{mode} == "loadcalendar") {
   echo "  <frame src='calendar/index.php?pid=" . $_GET['pid'];
   if (isset($_GET['date'])) echo "&date=" . $_GET['date'];
   echo "' name='Main' scrolling='auto' noresize frameborder='no'>\n";
  } else {
   echo "  <frame src='main.php?mode=" . $_GET['mode'];
   echo "' name='Main' scrolling='auto' noresize frameborder='no'>\n";
  }
 }
?>
</frameset>
<noframes><body bgcolor="#FFFFFF">
</body></noframes>
</html>
