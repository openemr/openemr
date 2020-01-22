<?php
/**
 * fax_view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$ffname = '';
$jobid = $_GET['jid'];
if ($jobid) {
    $jfname = $GLOBALS['hylafax_basedir'] . "/sendq/q" . check_file_dir_name($jobid);
    if (!file_exists($jfname)) {
	    $jfname = $GLOBALS['hylafax_basedir'] . "/doneq/q" . check_file_dir_name($jobid);
	    
    }
    // We will create an array to concatenate all pages sent by fax,
    // using a tmp folder created in faxcache.
 
    $docs = array();
    $jfhandle = fopen($jfname, 'r');
    if (!$jfhandle) {
        echo "I am in these groups: ";
        passthru("groups");
        echo "<br />";
        die(xlt("Cannot open ") . text($jfname));
    }

    while (!feof($jfhandle)) {
	    $line = trim(fgets($jfhandle));

	    if (strstr($line, '!')) {
		    $docs[] = $GLOBALS['hylafax_basedir'] . '/' . substr($line, strrpos($line, ":")+1);
	    }

    }
    fclose($jfhandle);

    $tmp1 = array();
    $tmp2 = 0;
    $inames = '';
    $faxcache = $GLOBALS['OE_SITE_DIR'] . "/faxcache/tmp";
    if (!is_dir($faxcache)) {
	    exec("mkdir -p " . escapeshellarg($faxcache));
    }
    foreach ($docs as $filepath) {
	    $filebase = basename($filepath) . ".pdf";
	    if (preg_match('/ps/', $filepath)) {
		    $tmp0 = exec("ps2pdf " . escapeshellarg($filepath) . " " . escapeshellarg($faxcache) . "/" . escapeshellarg($filebase), $tmp1, $tmp2);
		    if ($tmp2) {
			    die(xlt("ps2pdf returned $tmp2: $tmp0 "));
		    }
	    }
	    else if (preg_match('/tif/', $filepath)) {
		    $tmp0 = exec("tiff2pdf -p letter -o " . escapeshellarg($faxcache) . "/" . escapeshellarg($filebase) . " " . escapeshellarg($filepath), $tmp1, $tmp2);
		    if ($tmp2) {
			    die(xlt("tiff2pdf returned $tmp2: $tmp0 "));
		    }
	    }
	    else {
		    $tmp0 = exec("cp " . escapeshellarg($filepath) . " " . escapeshellarg($faxcache) . "/" . escapeshellarg($filebase), $tmp1, $tmp2);
		    if ($tmp2) {
			    die(xlt("cp returned $tmp2: $tmp0 "));
		    }
	    }
	    $inames .= " " . $faxcache . "/" . $filebase;
    }

    if (count($docs) > 1) {
	    $ffname = $faxcache . "/combine.pdf";
	    $tmp0 = exec("gs -dNOPAUSE -sDEVICE=pdfwrite -sOUTPUTFILE=" . escapeshellarg($ffname) . " -dBATCH" . $inames, $tmp1, $tmp2);
	    if ($tmp2) {
		    die(xlt("gs returned $tmp2: $tmp0 "));
	    }
    }
    else {
	    $ffname = $filepath;
    }

    if (!file_exists($jfname)) {
        die(xlt("Cannot find document reference in ") . text($jfname));
    }
} else if ($_GET['scan']) {
    $ffname = $GLOBALS['scanner_output_directory'] . '/' . $_GET['scan'];
}
else {
	$ffname = $GLOBALS['hylafax_basedir'] . '/recvq/' . check_file_dir_name($_GET['file']);
}
if (!file_exists($ffname)) {
	die(xlt("Cannot find ") . text($ffname));
}
if (!is_readable($ffname)) {
	die(xlt("I do not have permission to read ") . text($ffname));
}


ob_start();

$ext = substr($ffname, strpos($ffname, '.'));
if ($ext == '.pdf' || $ext == '.PDF' || preg_match('/pdf/', $ext)) {
	readfile($ffname);
}
else if ($ext == '.ps' || preg_match('/ps/', $ext)) {
	passthru("ps2pdf " . escapeshellarg($ffname) . " -");
}
else {
	passthru("gm convert " . escapeshellarg($ffname) . " PDF:-");
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/pdf");
header("Content-Length: " . ob_get_length());
header("Content-Disposition: inline; filename=" . basename($ffname, $ext) . '.pdf');

ob_end_flush();

//Below is to remove the tmp folder.
exec("rm -f -r " . escapeshellarg($faxcache));

exit;
