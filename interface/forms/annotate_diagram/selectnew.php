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
 * @author  Jerry Padgett <sjpadgett@gmail.com> 
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://www.open-emr.org
 */


include_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/api.inc');
	$sanitize_all_escapes=true;
	$fake_register_globals=false;
	$images_dir = '../../forms/annotate_diagram/diagram/';
	$images_per_row = 7;
?>
<html>
<head>
<title><?php xl('Select New Diagram for Annotation','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.9.1.min.js"></script>
<style type="text/css">
body {
	background-color: skyblue;		
}
.outer {
	margin: 40px;
}
#container {
	position: relative;
	border:3px solid red;
    box-sizing:border-box;
    margin:10px;
	min-width:1px;
    min-height:1px;
    display:inline-block;
	overflow: hidden
}
#diagram {
	box-sizing:border-box;
    min-width:65px;
    min-height:65px;
	//background-color:blue;
	position: relative;
	//border:3px solid black;
}
#diagram ul {padding-left:10px;}
#diagram li {display: inline; margin-right: 3px;}
#diagram li img{
	border:1px solid black;
    width:100%;
    max-width:75px;
	//height:100%;
    //max-height:85px;
}
</style>
</head>
<body>
<?php
function get_files($images_dir,$exts = array('png')) {
	$files = array();
	
	if($handle = opendir($images_dir)) {
		while(false !== ($file = readdir($handle))) {
			$extension = strtolower(get_file_extension($file));
			if($extension && in_array($extension,$exts)) {
				$files[] = $file;
			}
		}
		closedir($handle);
	}
	return $files;
}
function get_file_extension($file_name) {
	return substr(strrchr($file_name,'.'),1);
}
echo '<div class="outer">';
echo '<h4 class="text-center">'. xl("Click Thumbnails to view then Click full image to select.","e") .'</h4>';
echo '<div id="diagram" class="diagram" style="text-align:left;"><ul>';
	$image_files = get_files($images_dir);
	if(count($image_files)) {
		$index = 0;
		foreach($image_files as $index=>$file){
			$index++;
			$thumbnail_image = $images_dir.$file;
			echo '<li><img src='.$thumbnail_image.' /></li>';
			if($index % $images_per_row == 0) { echo '<div class="clear"></div>'; }
		}
	echo '<div class="clear"></div>';
	}
	else {
		echo '<p>'.xl("There are no images in this gallery.","e").'</p>';
	}
echo '</ul></div>';
echo '<div id="container" class="container">';
//$t = xl("Click on diagram above to view then Click here to select.","e");
echo '<img src="" alt="" id="main-img" class="main-img" onClick="SelectImage(this);" />';
echo '</div></div>';
?>
</body>
<script type="text/JavaScript">
$(document).ready(function() {
//	if (window.showModalDialog)
//				window.returnValue = false;
	$("#diagram li img").click( function(){
		$('#main-img').attr('src',$(this).attr('src'));
	});
});
function getFrmTitle(iname) {
	iname = iname.match(/[^\/?#]+(?=$|[?#])/) + '';
	iname = iname.split('.');
	iname = iname[0].charAt(0).toUpperCase() + iname[0].slice(1);
	
   	var fTitle = prompt("Please enter this form name or clink OK for highlighted title", iname.replace(/[_-]/g, " "));
    if ( fTitle != "") {
        return fTitle;
    }
	else{
		fTitle = "Diagram";
	}
    if ( fTitle == null ) fTitle = "";
	return fTitle;
}
var SelectImage = function(obj) {
    var imgname = $(obj).attr("src");
	var fname = getFrmTitle(imgname);
	if (fname == null || fname == "")
		return false;
    if (opener.closed || !opener.SetDiagram( imgname, fname ))
        alert('The parent form was closed and lost scope; Close and try again.');
//    else{
//		if (window.showModalDialog) window.returnValue = true;
//	}
    window.close();
    return false;
};
</script>
</html>