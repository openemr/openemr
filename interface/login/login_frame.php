<?php
$ignoreAuth = true;
include_once ("../globals.php");
?>
<HTML>
<head>
<?php html_header_show(); ?>
    <TITLE><?php xl ('Login','e'); ?></TITLE>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
</HEAD>

<frameset rows="<?php echo "$GLOBALS[logoBarHeight],$GLOBALS[titleBarHeight]" ?>,*" cols="*" frameborder="NO" border="0" framespacing="0">
  <frame class="logobar" src="<?php echo $rootdir;?>/login/filler.php" name="Filler Top" scrolling="no" noresize frameborder="NO">
  <frame class="titlebar" src="<?php echo $rootdir;?>/login/login_title.php" name="Title" scrolling="no" noresize frameborder="NO">
  <frame src="<?php echo $rootdir;?>/login/login.php" name="Login" scrolling="auto" frameborder="NO">
  <!--<frame src="<?php echo $rootdir;?>/login/filler.php" name="Filler Bottom" scrolling="no" noresize frameborder="NO">-->
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
