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

// take the content to display
$openztn = df_opztn_nodbc();
?>
<html>

<head>

<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
<LINK href="<?php echo $css_dbc ?>" rel="stylesheet" type="text/css">
<title>Opened ZTNs with no DBCs</title>
</head>

<body <?php echo $top_bg_line;?>>

<h3>Patient List with opened ZTN but no opened DBC</h3>

<table class="tbllist">
<tr><th>PID</th><th>ZTN id</th><th>Open ZTN</th><th>DBC info</th></tr>

<?php
foreach ( $openztn as $ok => $ov ) {
    if ( empty($ov['dbc']) ) $dbc = 'Geen DBC!';
    else $dbc = "ID: {$ov['dbc']['ax_id']} / DOPEN: {$ov['dbc']['ax_odate']} / CDATE: {$ov['dbc']['ax_cdate']}";


    $str  = "<tr><td>$ok</td>";
    $str .= "<td>{$ov['ztn']['cn_ztn']}</td>";
    $str .= "<td>{$ov['ztn']['cn_dopen']}</td>";
    $str .= "<td>$dbc</td></tr>";

    echo $str;
} // for each
?>

</table>

</body>
</html>
