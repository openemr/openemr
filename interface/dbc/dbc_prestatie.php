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

// get the results
$zorg = $_POST['zorg'];
$pgroep = $_POST['pgroep'];
$dbcid = $_POST['dbcid'];

dt_prestatiecode($zorg, $pgroep, $dbcid);

$i      = 1;
?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>DBC Report</title>
</head>

<body <?php echo $top_bg_line;?>>

</body>
</html>
