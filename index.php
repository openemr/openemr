<?php
include_once("library/sqlconf.php");

?>

<html>
<head>


</head>
<?php
include_once("library/sqlconf.php");

if ($config == 1) {
?>
<body ONLOAD="javascript:top.location.href='<?echo "interface/login/login_frame.php"?>';">
<?php
}
else {
?>
<body ONLOAD="javascript:top.location.href='<?echo "setup.php"?>';">
<?php
}
?>


</body>
</html>
