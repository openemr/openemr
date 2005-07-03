<?
include_once("../globals.php");

$_SESSION["encounter"] = "";

if (isset($_GET[mode]) && $_GET{mode} == "loadcalendar") {
?>
<HTML>
<HEAD>
<TITLE>
OpenEMR
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="main_title.php" name="Title" scrolling="no" noresize frameborder="NO">
<?
 if ($GLOBALS['athletic_team']) {
  echo "  <frame src='../reports/players_report.php?embed=1' " .
   "name='Main' scrolling='auto' noresize frameborder='no'>\n";
 } else {
  echo "  <frame src='calendar/index.php?pid=" . $_GET['pid'];
  if (isset($_GET['date'])) echo "&date=" . $_GET['date'];
  echo "' name='Main' scrolling='auto' noresize frameborder='NO'>\n";
 }
?>
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
<?
} else {
?>
<HTML>
<HEAD>
<TITLE>
OpenEMR
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[navBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="main_navigation.php" name="Navigation" scrolling="no" noresize frameborder="NO">
  <frame src="main_title.php" name="Title" scrolling="no" noresize frameborder="NO">
<?
 if ($GLOBALS['athletic_team']) {
  echo "  <frame src='../reports/players_report.php?embed=1' " .
   "name='Main' scrolling='auto' noresize frameborder='no'>\n";
 } else {
  echo "  <frame src='main.php?mode=" . $_GET['mode'];
  echo "' name='Main' scrolling='auto' noresize frameborder='NO'>\n";
 }
?>
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
<?
}
?>
