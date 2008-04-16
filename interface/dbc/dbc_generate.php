<?php
/** 
 * VEKTIS
 *
 * @author Cristian NAVALICI
 * @version 1.0 feb 2008
 *
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");

// Check authorization.
$thisauth = acl_check('admin', 'dbc');
if (!$thisauth) die("Not authorized.");

?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>DBC Generation</title>
</head>

<body <?php echo $top_bg_line;?>>

<form enctype="multipart/form-data" action="<?echo $_SERVER['PHP_SELF'] ?>" method="POST">
    <input type="radio" name="resim" value="1" checked="checked" />Simulation <br />
    <input type="radio" name="resim" value="0" />Real <br />

    <input type="submit" value="Generate the zip file" name="submitfile" />
</form>

<?php
if ( $_POST['submitfile'] ) {
    $option = (int)$_POST['resim'];
    $archive = dbc_generatefile('all', $option);
    echo 'The resulted archive file is <a href="'. DB_WORKINGLNK . basename($archive) .'">' .$archive. '</a>' ;
}
?>

</body>
</html>
