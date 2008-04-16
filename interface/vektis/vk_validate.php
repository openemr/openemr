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
$thisauth = acl_check('admin', 'vektis');
if (!$thisauth) die("Not authorized.");

?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>Vektis Validation</title>
</head>

<body <?php echo $top_bg_line;?>>

<form enctype="multipart/form-data" action="<?echo $_SERVER['PHP_SELF'] ?>" method="POST">
    <input type="radio" name="resim" value="1" checked="checked" />Simulation <br />
    <input type="radio" name="resim" value="2" />Real <br />

    <input type="hidden" name="MAX_FILE_SIZE" value="500000" />
    Upload the Vektis returning file: <input name="returnfile" type="file" />
    <input type="submit" value="Upload and validate" name="submitfile" />
</form>

<?php
if ( $_POST['submitfile'] ) {
    $upddir = 'retuploads/';
    $updfile = $upddir . basename($_FILES['returnfile']['name']);

    if (move_uploaded_file($_FILES['returnfile']['tmp_name'], $updfile)) {
        echo "File was successfully uploaded.<br />";
        // accept only 1-2
        $state = ( ($_POST['resim'] == 1) || ($_POST['resim'] == 2) ) ? $_POST['resim'] : 1;
        vk_parse_returning_file($updfile, $state);
    } else {
        echo "Possible file upload attack!\n";
    }
}
?>

</body>
</html>
