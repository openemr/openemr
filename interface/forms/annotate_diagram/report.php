<?php
 /**
 * Copyright Medical Information Integration,LLC info@mi-squared.com
 * 
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * Rewrite and modifications by sjpadgett@gmail.com Padgetts Consulting 2016.
 *
 * @package OpenEMR
 * @author  Medical Information Integration,LLC <info@mi-squared.com>
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */
 
require_once(__DIR__.'/../../globals.php');
require_once($GLOBALS['srcdir'].'/api.inc');
require_once("$srcdir/options.inc.php");
    $sanitize_all_escapes=true;
    $fake_register_globals=false;
?>
<html>
<head>
    <!-- <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['rootdir'] ?>/forms/annotate_diagram/mapdiagram/template/css/mapdiagram.css?v=$$TIME$$" /> --><!--Rem remove TIME for production -->
</head>
<style>body {
    background-color: white;
}
div .tab{
display:inline-block !important;
width:auto;height:auto;
}
.outer-container {
    margin: 10px;
}
#container {
    position: relative;
    border:2px solid black;
    box-sizing:border-box;
    min-width:1px;
    min-height:1px;
    display:inline-block;
    overflow: hidden;
}
.symcursor{ cursor: url("redblock.png") 8 8, auto;}
.txtcursor{ cursor: url("xhair.cur") 16 16, auto;}
.marker {
    position: absolute;
    //border: 1px solid black; 
    background-color: #FF8282;
    min-width:14px; 
    min-height: 14px;
    text-align:center;
}   
.count {
    font-size: 14px;
    //font-weight: bold;
    color: black;
    margin:2px;
}
.xmark {
    position: absolute;
    text-align:center;
}
.xcnt {
    font-size: 14px;
    //font-weight: bold;
    color: red;
    margin:2px;
}

.dytxt {
    display:none;
    background-color:lightgrey;
    //border:0px solid red;
    position:absolute;
    width: 70px; height:16px; // change if font size changed
    font-size: 14px;
    color: red;
    margin:2px;
    
}
#mode {
    display: none;
    padding:4px;
    background-color: white;
    border:1px solid black;
}
.rblock {
    color:red;
    display: inline-block;
}

label, input { display:block; }
fieldset { padding:0; border:0; }
</style>
<body>
<?php
function annotate_diagram_report( $pid, $encounter, $cols, $formid){
    $x = array(); $y = array(); $label = ''; $detail = '';

    $fdata = formFetch("form_annotate_diagram", $formid);
    $imgname =(string)$GLOBALS['rootdir'].'/forms/annotate_diagram/'.($fdata['imagedata']);

    $tmp = stripcslashes ($fdata['data']);
    $coordinates = preg_split('/}/', $tmp, -1, PREG_SPLIT_NO_EMPTY);

    $i = 0;
    foreach( $coordinates as $idata => $coordinate ) {
        $coordinate = ltrim($coordinate, '/\'/');
        if( $coordinate ){
            $info = preg_split('/\^/', $coordinate, -1);
            $x[$i] = (int)$info[0]; $y[$i] = (int)$info[1]; $label = rtrim($info[2]); $detail[$i] = urldecode( $info[3]);
            $legend[$i]=$label;
            $i++;
        }
    }
/* Start image and markers print */
    echo "<div class='outer-container'>";
    echo"<div id='container' class='#container'>";
    print "<img src=$imgname id='main-img'/>";
    $arrlength=count($legend);
    for($c=0;$c<$arrlength;$c++){
      if($legend[$c] != ""){
        if( $legend[$c][0] != '' ){
            if($legend[$c] < '~'){
                echo '<div class="marker" style="top:'.$y[$c].'px; left:'.$x[$c].'px;">';
                echo '<span class="count">'. $legend[$c] . '</span>';
            }
            else{
                echo '<div class="xmark" style="top:'.$y[$c].'px; left:'.$x[$c].'px;">';
                echo '<span class="xcnt">'. $legend[$c] . '</span>';
            }
        }
        else{
            $ltmp = ltrim($legend[$c],"");
            echo '<div class="xmark" style="top:'.$y[$c].'px; left:'.$x[$c].'px;">';
            echo '<span class="xcnt">'. $ltmp . '</span>';
        }
      echo "</div>";
      }
    }
// Start label print
    echo "</div><div id='legend' class='legend'><div class='body'>";
    echo "<ul style='list-style-type:disc'>";
    for($c=0;$c<$arrlength;$c++){
        if(!isSpecial($legend[$c]))  
            echo "<li><span class='legend-item'><b>" . ($legend[$c]) . "</b> " . ($detail[$c]) . "</span></li>";
    }
    echo "</ul></div></div></div>";
// Done - clear 
    echo "<p style='clear: both' />";

}
function isSpecial($elem){
        if ( $elem > '~' || $elem[0] == '' || $elem == 'N'){
            return true;
        }
        else
            return false;
}
?>
</body>
</html>