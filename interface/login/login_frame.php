<?php
$ignoreAuth = true;
include_once ("../globals.php");
?>
<HTML>
<head>
<?php html_header_show(); ?>
<TITLE><?php xl ('OpenEMR Login','e'); ?></TITLE>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<link rel=stylesheet href="../themes/login.css" type="text/css">

</HEAD>

<?php
  include 'header.php';
  include 'login_title.php';
  include 'login.php';
?>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>