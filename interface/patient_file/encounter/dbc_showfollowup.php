<?php
include_once("../../../interface/globals.php");
$today = dateformat();
$time = date('H:i');
$providerid = $_SESSION['authId'];

$dbc = last_diagnose(); 

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
	
} // empty $dbc
?>
<html>
<head>
    <title>DBC follow up content</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>

<body bgcolor="#A4FF8B">
<p>DBC id: <?=$dbc['ax_id']?> opening date: <?=$dbc['ax_odate']?></p>

<table cellspacing="3" cellpadding="2">
    <tr>
        <td width="50px" align="center">AS I</td><td><?=$as1_str?></td>
    </tr>
    <tr>
        <td align="center">AS II</td><td><?=$as2_str?></td>
    </tr>
    <tr>
        <td align="center">AS III</td><td><?=$as3_str?></td>
    </tr>
    <tr>
        <td align="center">AS IV</td><td><?=$as4_str?></td>
    </tr>
    <tr>
        <td align="center">AS V</td><td><?=$as5_str?></td>
    </tr>
</table>

<input type="button" onclick="window.close()" name="close" value="Sluiten" />

<body>
</html>
