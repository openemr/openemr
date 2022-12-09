<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
include_once("$srcdir/wmt-v2/wmtstandard.inc");
$pid = $doc_id = $url = $task = $cnt = $item_id = $prefix = '';
$mode = 'list';
if(isset($_GET['view'])) $mode = 'view';
if(isset($_GET['retrieve'])) $mode = 'retrieve';
if(isset($_GET['single'])) $mode = strip_tags($_GET['single']);
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(isset($_GET['task'])) $task = strip_tags($_GET['task']);
if(isset($_GET['cnt'])) $cnt = strip_tags($_GET['cnt']);
if(isset($_GET['item_id'])) $item_id = strip_tags($_GET['item_id']);
if(isset($_GET['prefix'])) $prefix = strip_tags($_GET['prefix']);
if(isset($_GET['doc_id'])) $doc_id = strip_tags($_GET['doc_id']);
if($pid == 0 || $pid == '') ReportMissingPID();
$addr = "../controller.php?document&$mode&patient_id=$pid";
if($doc_id) $addr .= "&doc_id=$doc_id";
if($task) $addr .= "&task=$task";
if($item_id) $addr .= "&item_id=$item_id";
if($prefix) $addr .= "&prefix=$prefix";
if($cnt) $addr .= "&cnt=$cnt";
$sql = "SELECT * FROM `documents` WHERE `id` = ?";

if($doc_id && $mode == 'single') {
	$fres = sqlStatement($sql, array($doc_id));
	$frow = sqlFetchArray($fres);
	$url = $frow{'url'};
	$addr = $url;
	// echo "Address: $addr<br>\n";

	// echo "URL before strip: $url<br>\n";
	$url = preg_replace("|^(.*)://|","",$url);
	// echo "URL After strip: $url<br>\n";
		
	$from_all = explode("/",$url);
	$from_filename = array_pop($from_all);
	$from_patientid = array_pop($from_all);
	// $from_all = explode("/",$frow{'mimetype'});
	$temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_patientid . '/' . $from_filename;
	echo "Temp URL: $temp_url   File: $from_filename   Pat: $from_patientid<br>\n";
	
	if (file_exists($temp_url)) {
		echo "Basename: ".basename($url)."<br>\n";
		echo "That file does exist!<br>\n";
		echo "<br>\n";
		$f = fopen($temp_url, 'r');
		//normal case when serving the file referenced in database
    header('Content-Description: File Transfer');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
		$as_file = true;
	  header("Content-Disposition: " . ($as_file ? "attachment" : "inline") . "; filename=\"" . basename($url) . "\"");
	  header("Content-Type: " . $frow{'mimetype'});
		header("Content-Length: " . filesize($temp_url));
		fpassthru($f);
		// @readfile($temp_url);
		exit;
	}	 else {
		echo "There seems to be an issue referencing this document<br>\n";
		echo " -->$temp_url<br>\n";
		echo "Please Contact Support<br>\n";
		echo "<br>\n";
		exit;
	}
		 
}
?>

<html>
<head>
<script type="text/javascript">
function LoadDocumentView() {
	location.href='<?php echo $addr; ?>';
}
</script>
<body onload="LoadDocumentView();">
</body>

</head>
</html>
