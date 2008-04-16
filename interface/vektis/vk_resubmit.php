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

$resub = vk_vektis_ready(3);
$self = $_SERVER['PHP_SELF'];

if ( $_GET['dbcid'] ) {
    $dbcid = (int)$_GET['dbcid'];
    if ( $dbcid ) { 
        vk_dbc_resubmit($dbcid);
        header("refresh:0 url=$self");
    }
}

?>

<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css' />
<link href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css" />

<title>Resubmitted DBCs Administration</title>
</head>

<body <?php echo $top_bg_line;?>>
<a href="vk_main.php" target="Main">Terug</a>

<?php
    if ( $resub ) {
        echo '<table id="tbl_resub"><tr><th>DBC id</th><th>ZTN id</th><th>Patient</th><th>Open date</th><th>Close date</th><th></th></tr>';
        foreach ( $resub as $vk ) {
            $link = $self .'?dbcid='. $vk['ax_id'];
            $str = '<tr>';
            $str .= "<td>{$vk['ax_id']}</td>";
            $str .= "<td>{$vk['ax_ztn']}</td>";
                $pid = what_patient($vk['ax_id']);
                $name = dutch_name($pid);
            $str .= "<td>$name (PID: $pid)</td>";
            $str .= "<td>{$vk['ax_odate']}</td>";
            $str .= "<td>{$vk['ax_cdate']}</td>";
            $str .= "<td><div class='vk_link'><a href='$link'>Recall</a></div></td>";
            $str .= '</tr>';
            echo $str;
        } 
        echo '</table>';
    } else {
        echo '<p>No list to display.</p>';
    }
?>

</body>
</html>
