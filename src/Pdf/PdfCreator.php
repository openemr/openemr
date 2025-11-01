<?php

/**
 * PdfCreator class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Pdf;

use Knp\Snappy\Pdf;

class PdfCreator
{
    /**
     * Path to the wkhtmltopdf binary
     */
    private readonly string $binaryPath;

    /**
     * Path to the temporary folder for PDF generation
     */
    private readonly string $tempPath;

    /**
     * Determines the path to the wkhtmltopdf binary based on the operating system
     *
     * @return string Path to the wkhtmltopdf binary
     * @throws \RuntimeException If the operating system cannot be determined
     */
    private function getBinaryPath(): string
    {
        $binroot = $GLOBALS['vendor_dir'] . "/openemr/wkhtmltopdf-openemr/bin";

        // This will not necessarily reflect actual machine bus width but php bus size.
        $bit = str_contains(php_uname("m"), '64') ? "64" : "32";
        $thisos = strtolower(php_uname("s"));
        $wkexe = match (true) {
            str_contains($thisos, "darwin") => "{$binroot}/osx/wkhtmltopdf{$bit}-osx",
            str_contains($thisos, "win") => "{$binroot}/win/wkhtmltopdf{$bit}.exe",
            str_contains($thisos, "linux") => "{$binroot}/linux/wkhtmltopdf{$bit}-linux",
            default => throw new \RuntimeException(xlt("Can not determine OS!"))
        };
        chmod($wkexe, octdec(755));
        return $wkexe;
    }

    /**
     * Initializes the PDF creator with the appropriate binary path and temp folder
     */
    public function __construct()
    {
        $this->binaryPath = $this->getBinaryPath();
        $this->tempPath = $GLOBALS['OE_SITE_DIR'] . "/documents/temp";
    }

    /**
     * Generates a PDF from HTML content
     *
     * @param string|array $htmlin HTML content or array of HTML strings (each element as a page)
     * @param array $options Options for PDF generation
     * @return string|null Generated PDF content, or null on error
     */
    public function getPdf(string|array $htmlin, array $options): ?string
    {
        $pdfwk = new Pdf($this->binaryPath);
        $pdfwk->setTemporaryFolder($this->tempPath);
        return $pdfwk->getOutputFromHtml($htmlin, $options);
    }
}
