<?
$ignoreAuth=true;
include_once("./globals.php");
?>
<html>
<body>

<script LANGUAGE="JavaScript">
 top.location.href='<?echo "$rootdir/login/login_frame.php"?>';
</script>

<a href='<?echo "$rootdir/login/login_frame.php"?>'>Follow manually</a>

<p>
OpenEMR requires Javascript to perform user authentication.

</body>
</html>
