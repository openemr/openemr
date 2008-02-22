<?php
require_once("globals.php");
?>

<html>
<head>
<? html_header_show();?>

<link rel=stylesheet href="style.css" type="text/css">

</head>
<body bgcolor=#ffffff topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class=text><?xl('Logged out.','e')?></span>

<br><br>

<?xl('This page will inline include the login page, so that we do not have to click relogin every time.','e')?>

<br><br>

<a class=link href="login_screen.php"><?xl('Relogin','e')?></a>

<br><br>

</body>
</html>
