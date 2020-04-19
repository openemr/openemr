<?php

/**
 * PdfCreator class
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Pdf;

use Knp\Snappy\Pdf;
use Symfony\Component\Debug\ExceptionHandler;

class PdfCreator
{

    private $binaryPath;

    private $tempPath;

    private function getBinaryPath()
    {

        $binroot = $GLOBALS['vendor_dir'] . "/openemr/wkhtmltopdf-openemr/bin";

        // This will not necessarily reflect actual machine bus width but php bus size.
        $intsize = strlen(decbin(~ 0));
        if (empty(strstr(php_uname("m"), '64'))) {
            $bit = "32";
        } else {
            $bit = "64";
        }
        try {
            $thisos = strtolower(php_uname());
            if (strpos($thisos, "darwin") !== false) {
                $wkexe = $binroot . "/osx/wkhtmltopdf" . $bit . "-osx";
            } elseif (strpos($thisos, "win") !== false) {
                $wkexe = $binroot . "/win/wkhtmltopdf" . $bit . ".exe";
            } elseif (strpos($thisos, "linux") !== false) {
                $wkexe = $binroot . "/linux/wkhtmltopdf" . $bit . "-linux";
            } else {
                throw new ExceptionHandler(xlt("Can not determine OS!"));
            }
            chmod($wkexe, octdec(755));
            return $wkexe;
        } catch (ExceptionHandler $e) {
            die($e->getMessage());
        }
    }

    public function __construct()
    {
        global $webserver_root;
        $this->binaryPath = $this->getBinaryPath();
        $this->tempPath = $GLOBALS['OE_SITE_DIR'] . "/documents/temp";
    }

    public function getPdfFromFile($files, $options)
    {
        try {
            $pdfwk = new Pdf($this->binaryPath);
            $pdfwk->setTemporaryFolder($this->tempPath);
            $pdfwkout = $pdfwk->getOutput($files, $options);
        } catch (ExceptionHandler $e) {
            echo xlt($e->xdebug_message);
        }
        return $pdfwkout;
    }

    // Can be array of html with each element as a page.
    public function getPdf($htmlin, $options)
    {
        try {
            $pdfwk = new Pdf($this->binaryPath);
            $pdfwk->setTemporaryFolder($this->tempPath);
            $pdfwkout = $pdfwk->getOutputFromHtml($htmlin, $options);
        } catch (ExceptionHandler $e) {
            echo xlt($e->xdebug_message);
        }
        return $pdfwkout;
    }

    public function write($html_file, $save_to, $options)
    {
        $pdfwk = new Pdf($this->binaryPath);
        $pdfwk->generateFromHtml(file_get_contents($html_file), $save_to);

        return $pdfFilePath;
    }
}
