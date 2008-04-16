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
<title>Vektis Administration</title>
</head>

<body <?php echo $top_bg_line;?>>
<p>Today: <?php echo date('d-m-Y'); ?></p>
<p> Number of DBCs ready for Vektis (ready/to be checked): <?php echo vk_number_ready(1) .'/'. vk_number_ready(3);  ?></p>
<p><a href="vk_list.php" target="Main" >List the ready DBC's</a></p>
<p><a href="vk_generate.php" target="Main" onClick="return confirm('Are you sure?');" >Generate the Vektis File</a></p>
<p><a href="vk_validate.php" target="Main">Vektis Validation</a></p>
<p><a href="vk_resubmit.php" target="Main">Resubmitted DBC administration</a></p>

</body>
</html>
