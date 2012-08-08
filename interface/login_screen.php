<?php
$ignoreAuth=true;
include_once("./globals.php");
?>
<html>
<body>

<script LANGUAGE="JavaScript">
 top.location.href='<?php echo "$rootdir/login/login_frame.php?site=".$_SESSION['site_id']; ?>';
</script>

<a href='<?php echo "$rootdir/login/login_frame.php?site=".$_SESSION['site_id']; ?>'><?php xl('Follow manually','e'); ?></a>

<p>
<?php xl('OpenEMR requires Javascript to perform user authentication.','e'); ?>

</body>
</html>
