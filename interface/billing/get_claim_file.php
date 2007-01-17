<?php

require(dirname(__FILE__) . "/../globals.php");

$fname = $_GET['key'];

$fname = preg_replace("[/]","",$fname);
$fname = preg_replace("[\.\.]","",$fname);
$fname = preg_replace("[\\\\]","",$fname);
$fconfig = $GLOBALS['oer_config']['freeb'];
$fname = $fconfig['claim_file_dir'] . $fname;



if (!file_exists($fname)) {
   echo xl("The claim file: ") . $_GET['key'] . xl(" could not be accessed.");
}
elseif ($_GET['action'] == "print") {
?>
<html>
<head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<br><p><h3><?php xl('Printing results:','e')?></h3><a href="billing_report.php"><?php xl('back','e')?></a><ul>
<?php 
	$estring = $fconfig['print_command'] . " -P " . $fconfig['printer_name'] . " " . $fconfig['printer_extras'] . " " . $fname;
	//echo $estring . "<br>";
	$rstring = exec(escapeshellcmd($estring));
	echo xl("Document"). $fname .xl("sent to printer.");
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

	if (strtolower(substr($fname,(strlen($fname)-4))) == ".pdf") { 
		header("Content-Type: application/pdf");
	}
	elseif (strtolower(substr($fname,(strlen($fname)-4))) == ".edi") {
			header("Content-Type: text/plain");
	}
	
	header("Content-Length: " . filesize($fname));
	header("Content-Disposition: attachment; filename=" . basename($fname));

	// dump the picture and stop the script
	fpassthru($fp);
}
exit;















?>