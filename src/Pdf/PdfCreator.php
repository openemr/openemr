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
use Exception;

class PdfCreator
{
    private $binaryPath;

    private $tempPath;

    private function getBinaryPath()
    {

        $binroot = $GLOBALS['vendor_dir'] . "/openemr/wkhtmltopdf-openemr/bin";

        // This will not necessarily reflect actual machine bus width but php bus size.
        $intsize = strlen(decbin(~ 0));
        $bit = empty(strstr(php_uname("m"), '64')) ? "32" : "64";
        try {
            $thisos = strtolower(php_uname());
            if (str_contains($thisos, "darwin")) {
                $wkexe = $binroot . "/osx/wkhtmltopdf" . $bit . "-osx";
            } elseif (str_contains($thisos, "win")) {
                $wkexe = $binroot . "/win/wkhtmltopdf" . $bit . ".exe";
            } elseif (str_contains($thisos, "linux")) {
                $wkexe = $binroot . "/linux/wkhtmltopdf" . $bit . "-linux";
            } else {
                throw new Exception(xlt("Can not determine OS!"));
            }
            chmod($wkexe, octdec(755));
            return $wkexe;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function __construct()
    {
        global $webserver_root;
        $this->binaryPath = $this->getBinaryPath();
        $this->tempPath = $GLOBALS['OE_SITE_DIR'] . "/documents/temp";
    }

    // Can be array of html with each element as a page.
    public function getPdf($htmlin, $options)
    {
        $pdfwk = new Pdf($this->binaryPath);
        $pdfwk->setTemporaryFolder($this->tempPath);
        return $pdfwk->getOutputFromHtml($htmlin, $options);
    }
}
