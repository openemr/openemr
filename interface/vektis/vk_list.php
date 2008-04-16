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
?>

<html>
<head><title>DBC List ready for Vektis</title>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<body <?php echo $top_bg_line;?>>

<a href="<?php echo $_SERVER['HTTP_REFERER'];?>" target="Main">Terug</a>
<?php 
    $vkready = vk_vektis_ready();
    if ( $vkready ) {
        echo '<table><tr><th>DBC id</th><th>Patient</th><th>Open date</th><th>Close date</th></tr>';
        foreach ( $vkready as $vk ) {
            $str = '<tr>';
            $str .= "<td>{$vk['ax_id']}</td>";
                $pid = what_patient($vk['ax_id']);
                $name = dutch_name($pid);
            $str .= "<td>$name (PID: $pid)</td>";
            $str .= "<td>{$vk['ax_odate']}</td>";
            $str .= "<td>{$vk['ax_cdate']}</td>";
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