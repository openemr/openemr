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

<!--
<form method="POST" action="dbc_prestatie.php">
    <input type="text" id="zorg" name="zorg"/>
    <input type="text" id="pgroep" name="pgroep"/>
    <input type="text" id="dbcid" name="dbcid"/>
    <input type="submit" value="send">
</form>
-->

</body>
</html>
