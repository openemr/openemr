<?php
$ignoreAuth = true;
include_once ("../globals.php");
include_once ("$srcdir/classes/Filtreatment_class.php");
?>
<HTML>
<HEAD>
    <TITLE><?php xl ('Login','e'); ?></TITLE>
</HEAD>

<?php
$ob         = new Filtreatment();
$_rootdir = $ob->doTreatment($rootdir, 'XSS');
?>

<frameset rows="<?echo "$GLOBALS[logoBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame src="<?echo $_rootdir;?>/login/filler.php" name="Filler Top" scrolling="no" noresize frameborder="NO">
  <frame src="<?echo $_rootdir;?>/login/login_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="<?echo $_rootdir;?>/login/login.php" name="Login" scrolling="auto" frameborder="NO">
  <!--<frame src="<?echo $_rootdir;?>/login/filler.php" name="Filler Bottom" scrolling="no" noresize frameborder="NO">-->
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>



</HTML>
