<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

	require_once("../globals.php");

	$ffname = $GLOBALS['hylafax_basedir'] . '/recvq/' . $_GET['file'];

	if (!file_exists($ffname)) {
		die(xl("Cannot access ") . $ffname);
	}

	ob_start();

	passthru("tiff2pdf $ffname");

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/pdf");
	header("Content-Length: " . ob_get_length());
	header("Content-Disposition: inline; filename=" . basename($ffname, '.tif') . '.pdf');

	ob_end_flush();

	exit;
?>