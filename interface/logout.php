<?php
require_once("globals.php");
?>

<html>
<head>
<?php html_header_show(); ?>

<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">

</head>
<body bgcolor=#ffffff topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class=text><?php xl('Logged out.','e'); ?></span>

<br><br>

<?php xl('This page will inline include the login page, so that we do not have to click relogin every time.','e'); ?>

<br><br>

<a class=link href="login_screen.php"><?php xl('Relogin','e'); ?></a>

<br><br>

</body>
</html>
