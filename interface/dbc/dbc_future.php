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
$result = df_future_events();
$i      = 1;
?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>DBC Report</title>
</head>

<body <?php echo $top_bg_line;?>>

<h3>List of opened DBC's without future events</h3>

<table id = "tbl_future">
    <tr>
        <th>Nr</th><th>PID</th><th>Name</th><th>DBC ID</th><th>Opening date</th><th>Age(days)</th>
    </tr>
    <?php
    foreach ( $result as $r ) {
        $dutch_name = dutch_name($r['pid']);
        $age  = df_dbc_age($r['ax_id']);
        echo "<tr>
        <td align='center'>$i</td>
        <td align='center'>{$r['pid']}</td>
        <td>$dutch_name</td>
        <td align='center'>{$r['ax_id']}</td>
        <td>{$r['ax_odate']}</td>
        <td>$age</td>
        </tr>";
        $i++;
    } // foreach
    ?>
</table>
</body>
</html>
