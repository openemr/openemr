<?php
if($GLOBALS['date_display_format'] == 1) {
	$date_img_fmt = '%m/%d/%Y';
	$date_title_fmt = 'MM/DD/YYYY';
	$date_approximate_fmt = '00/00/2004 or 03/00/2009';	
} else if($GLOBALS['date_display_format'] == 2) {
	$date_img_fmt = '%d/%m/%Y';
	$date_title_fmt = 'DD/MM/YYYY';
	$date_approximate_fmt = '00/00/2004 or 00/05/2009';	
} else {
	$date_img_fmt = '%Y-%m-%d';
	$date_title_fmt = 'YYYY-MM-DD';
	$date_approximate_fmt = '2004-00-00 or 2009-05-00';	
}
?>
