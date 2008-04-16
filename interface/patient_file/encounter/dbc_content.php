<?php
 // middle frame
 include_once("../../globals.php");
 include_once("$srcdir/sql.inc");
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body <?php echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='4'
 bottommargin='0' marginheight='0'>

<?php
// if we called this page with a parameter, then display the requested DBC
if ( $_GET['c'] ) {
	$code = (int)$_GET['c'];
	$_SESSION['show_axid'] = $code;
}


if ( $_SESSION['show_axid'] ) {
	$dbc = content_diagnose($_SESSION['show_axid']); 
} else {
	$dbc = last_diagnose(); $_SESSION['show_axid'] = $dbc['ax_id'];
}
//var_dump($_SESSION['show_axid']);
if ( !empty($dbc) ) {
        // unserialize contents in $as1, $as2...$as5
        $as1 = unserialize($dbc['ax_as1']);
                $as1c = $as1['content']; $mainpos = (int)$as1['mainpos']; // mainpos is written in both places
        $as2 = unserialize($dbc['ax_as2']);
                $as2c = $as2['content']; 		
        $as3 = unserialize($dbc['ax_as3']);
        $as4 = unserialize($dbc['ax_as4']);
        $as5 = unserialize($dbc['ax_as5']);

        // as1 transformation
        $counter = 1;
        foreach ( $as1c as $a) {
                $as1_str .= what_as($a);
                if ( $counter == $mainpos ) $as1_str .= ' (MD)';
                $as1_str .= '<br />'; $counter++; 
        }
        // as2 transformation
        foreach ( $as2c as $a) {
                $as2_str .= what_as($a['code']). '(' .$a['trekken']. ')';
                if ( $counter == $mainpos ) $as2_str .= ' (MD)';
                $as2_str .= '<br />'; $counter++;
        }

        // as3 and as4
        $as3_str = what_as($as3); $as4_str = what_as($as4);
        // as5
        $as5_str = what_as($as5['gaf1']) .'<br />'. what_as($as5['gaf2']) .'<br />'. what_as($as5['gaf3']);
        
        $content = "<tr><th>{$dbc['ax_odate']}</th></tr>
        <tr><td valign='top'>AS I</td><td>$as1_str</td></tr>
        <tr><td valign='top'>AS II</td><td>$as2_str</td></tr>
        <tr><td valign='top'>AS III</td><td>$as3_str</td></tr>
        <tr><td valign='top'>AS IV</td><td>$as4_str</td></tr>
        <tr><td valign='top'>AS V</td><td>$as5_str</td></tr>
        ";	
} else {
        $content = 'No DBC.';
}
?>
<table class='text'>
	<?php echo $content;?>
</table>

</body>
</html>
