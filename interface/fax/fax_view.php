<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

	require_once("../globals.php");

	$ffname = '';
	$jobid = $_GET['jid'];
	if ($jobid) {
		$jfname = $GLOBALS['hylafax_basedir'] . "/sendq/q$jobid";
		if (!file_exists($jfname))
			$jfname = $GLOBALS['hylafax_basedir'] . "/doneq/q$jobid";
		$jfhandle = fopen($jfname, 'r');
		if (!$jfhandle) {
			echo "I am in these groups: ";
			passthru("groups");
			echo "<br />";
			die(xl("Cannot open ") . $jfname);
		}
		while (!feof($jfhandle)) {
			$line = trim(fgets($jfhandle));
			if (substr($line, 0, 12) == '!postscript:') {
				$ffname = $GLOBALS['hylafax_basedir'] . '/' .
					substr($line, strrpos($line, ':') + 1);
				break;
			}
		}
		fclose($jfhandle);
		if (!$ffname) {
			die(xl("Cannot find postscript document reference in ") . $jfname);
		}
	}
	else if ($_GET['scan']) {
		$ffname = $GLOBALS['scanner_output_directory'] . '/' . $_GET['scan'];
	}
	else {
		$ffname = $GLOBALS['hylafax_basedir'] . '/recvq/' . $_GET['file'];
	}

	if (!file_exists($ffname)) {
		die(xl("Cannot find ") . $ffname);
	}

	if (!is_readable($ffname)) {
		die(xl("I do not have permission to read ") . $ffname);
	}

	ob_start();

	$ext = substr($ffname, strrpos($ffname, '.'));
	if ($ext == '.ps')
		passthru("TMPDIR=/tmp ps2pdf '$ffname' -");
	else if ($ext == '.pdf' || $ext == '.PDF')
		readfile($ffname);
	else
		passthru("tiff2pdf '$ffname'");

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/pdf");
	header("Content-Length: " . ob_get_length());
	header("Content-Disposition: inline; filename=" . basename($ffname, $ext) . '.pdf');

	ob_end_flush();

	exit;
?>