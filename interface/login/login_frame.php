<?
$ignoreAuth=true;
include_once("../globals.php");

?>



<HTML>
<HEAD>
<TITLE>
Login
</TITLE>
</HEAD>
<frameset rows="<?echo "$GLOBALS[logoBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="<?echo $rootdir;?>/login/filler.php" name="Filler Top" scrolling="no" noresize frameborder="NO">
  <frame src="<?echo $rootdir;?>/login/login_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="<?echo $rootdir;?>/login/login.php" name="Login" scrolling="auto" frameborder="NO">
  <!--<frame src="<?echo $rootdir;?>/login/filler.php" name="Filler Bottom" scrolling="no" noresize frameborder="NO">-->
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>



</HTML>
