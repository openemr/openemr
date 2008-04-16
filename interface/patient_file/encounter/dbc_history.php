<?php
 include_once("../../globals.php");
 include_once("$srcdir/sql.inc");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>

<body <?php echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='4'
 bottommargin='0' marginheight='0'>
<?php //var_dump($_SESSION); ?>
<?php $list_diag = lists_diagnoses('ax_odate DESC');  ?>

<table>
<tr>
	<td>Previous DSM IV Diagnoses</td><td><a href="dbc_historyfull.php" target="_blank">&nbsp; ** History **</a></td>
</tr>
<?php 
$target = ( $GLOBALS['concurrent_layout'] ) ? "Content" : "Codes" ;

if ( $list_diag ) {
    foreach($list_diag as $ldiag) {
	$dopen = ( $ldiag['ax_open'] ) ? 'opened' : 'closed';
	echo '<tr><td><a target="'.$target.'" href="dbc_content.php?c=' .$ldiag['ax_id']. '">' .$ldiag['ax_odate']. ' (' .$dopen.')</a></td></tr>';
    }	
} else {
    echo "<tr><td>No Content.</td></tr>";
}	
?>
</table>

</body>
</html>
