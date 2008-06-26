<?php
require_once(dirname(__FILE__) . "/../globals.php");
require_once(dirname(__FILE__) . "/../../includes/config.php");

$fconfig = $GLOBALS['oer_config']['freeb'];
$content_type = "text/plain";
$claim_file_dir = "$webserver_root/edi/";

$fname = $_GET['key'];
$fname = preg_replace("[/]","",$fname);
$fname = preg_replace("[\.\.]","",$fname);
$fname = preg_replace("[\\\\]","",$fname);

if (strtolower(substr($fname,(strlen($fname)-4))) == ".pdf") {
  $content_type = "application/pdf";
  if (!strpos($fname,'-batch')) $claim_file_dir = $fconfig['claim_file_dir'];
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
<?php 
	$estring = $fconfig['print_command'] . " -P " . $fconfig['printer_name'] . " " . $fconfig['printer_extras'] . " " . $fname;
	$rstring = exec(escapeshellcmd($estring));
	echo xl("Document") . $fname . xl("sent to printer.");
?>
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
