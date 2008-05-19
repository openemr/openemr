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
<title>DBC Administration</title>
</head>

<body <?php echo $top_bg_line;?>>

<p><a href="dbc_generate.php" target="Main">Generate the DBC file</a></p>
<p><a href="dbc_future.php" target="Main">List open DBC's w/out future events</a></p>
<p><a href="dbc_totaldbc.php" target="Main">List open DBC's (2007/2008)</a></p>
<p><a href="dbc_dbcvalues.php" target="Main">DBC Values</a></p>
<p><a href="dbc_nodbc.php" target="Main">Opened ZTNs; no DBC's</a></p>

</body>
</html>
