<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/../globals.php");
require_once $GLOBALS['OE_SITE_DIR'] . "/config.php";

$content_type = "text/plain";
$claim_file_dir = $GLOBALS['OE_SITE_DIR'] . "/edi/";

$fname = $_GET['key'];
$fname = preg_replace("[/]","",$fname);
$fname = preg_replace("[\.\.]","",$fname);
$fname = preg_replace("[\\\\]","",$fname);

if (strtolower(substr($fname,(strlen($fname)-4))) == ".pdf") {
  $content_type = "application/pdf";
}

$fname = $claim_file_dir . $fname;

if (!file_exists($fname)) {
   echo xl("The claim file: ") . $_GET['key'] . xl(" could not be accessed.");
}
elseif ($_GET['action'] == "print") {
?>
<html>
<head>
<?php if (function_exists(html_header_show)) html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<br><p><h3><?php xl('Printing results:','e')?></h3><a href="billing_report.php"><?php xl('back','e')?></a><ul>
</ul>
</body>
</html>
<?php
}
else {
	$fp = fopen($fname, 'r');

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: $content_type");
	header("Content-Length: " . filesize($fname));
	header("Content-Disposition: attachment; filename=" . basename($fname));

	// dump the picture and stop the script
	fpassthru($fp);
}
exit;
?>
