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

// ===============================
// commiting to the database the send file content
if ( $_POST['comdb'] ) {
    vk_db_commit(); 
    echo 'The informations were commited to database. Press <a href="vk_main.php" target="Main">here</a> to return to the Vektis main menu.';
}

// EOS
// ===============================

?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<title>Vektis Administration</title>
</head>

<body <?php echo $top_bg_line;?>>

</body>
</html>
