<?
$ignoreAuth=true;
include_once("./globals.php");
?>
<html>
<body>

<script LANGUAGE="JavaScript">
 top.location.href='<?echo "$rootdir/login/login_frame.php"?>';
</script>

<a href='<?echo "$rootdir/login/login_frame.php"?>'><?xl('Follow manually','e')?></a>

<p>
<?xl('OpenEMR requires Javascript to perform user authentication.','e')?>

</body>
</html>
