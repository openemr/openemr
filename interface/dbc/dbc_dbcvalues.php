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

// get the results
$result = df_dbcvalues();
$i = 1;
?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>DBC Values Report</title>
</head>

<body <?php echo $top_bg_line;?>>

<h3>List of opened DBC's with current values.</h3>

<table id = "tbl_future">
    <tr>
        <th>Nr</th><th>PID</th><th>Name</th><th>DBC ID</th><th>Opening date</th>
        <th>Price(euro)</th><th>Other</th>
    </tr>
    <?php
    foreach ( $result as $r ) {
        $dutch_name = dutch_name($r['pid']);
        $tariff     = $r['tariff'] / 100;

        echo "<tr>
                <td align='center'>$i</td>
                <td align='center'>{$r['pid']}</td>
                <td>$dutch_name</td>
                <td align='center'>{$r['dbcid']}</td>
                <td>{$r['odate']}</td>
                <td>$tariff</td>
                <td>{$r['prestatie']}</td>
        </tr>";
        $i++;
    } // foreach
    ?>
</table>
</body>
</html>
