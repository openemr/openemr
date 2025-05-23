<?php

/**
 * PatientPortalPDFDocumentCreator is used for generating pdf documents from html documents that have been submitted
 * via a patient either from the patient portal or in a patient smart app.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Pdf;

use Mpdf\Mpdf;
use HTMLPurifier_Config;
use OpenEMR\Pdf\Config_Mpdf;

class PatientPortalPDFDocumentCreator
{
    public function createPdfObject($htmlIn)
    {
        $config_mpdf = Config_Mpdf::getConfigMpdf();
        $pdf = new Mpdf($config_mpdf);
        if ($_SESSION['language_direction'] == 'rtl') {
            $pdf->SetDirectionality('rtl');
        }

        // snatch style tags content to insert after content purified
        $style_flag = preg_match('#<\s*?style\b[^>]*>(.*?)</style\b[^>]*>#s', $htmlIn, $style_matches);
        $style = str_replace('<style type="text/css">', '<style>', $style_matches);
        $pos = stripos($htmlIn, "<style>");
        $pos1 = stripos($htmlIn, "</style>");

        // purify html
        $config = HTMLPurifier_Config::createDefault();
        $config->set('URI.AllowedSchemes', array('data' => true, 'http' => true, 'https' => true));
        $purify = new \HTMLPurifier($config);
        $htmlIn = $purify->purify($htmlIn);
        // need to create custom stylesheet for templates
        // also our styles_pdf.scss isn't being compiled!!!
        // replace existing style tag in template after purifies removes! why!!!
        // e,g this scheme gets removed <html><head><body> etc
        $stylesheet = "<style>.signature {vertical-align: middle;max-height:65px; height:65px !important;width:auto !important;}</style>";
        if ($pos !== false && $pos1 !== false && !empty($style[0] ?? '')) {
            $stylesheet = str_replace('</style>', $stylesheet, $style[0]);
        }
        $htmlIn = "<!DOCTYPE html><html><head>" . $stylesheet . "</head><body>$htmlIn</body></html>";
        $pdf->writeHtml($htmlIn);
        return $pdf;
    }
    public function createPdfDocument($cpid, $formFilename, $documentCategory, $htmlIn): \Document
    {

        $pdf = $this->createPdfObject($htmlIn);
        if (!$cpid) {
            throw new \InvalidArgumentException("Missing Patient ID");
            echo js_escape("ERROR " . xla("Missing Patient ID"));
            exit();
        }
        $data = $pdf->Output($formFilename, 'S');
        $d = new \Document();
        $rc = $d->createDocument($cpid, $documentCategory, $formFilename, 'application/pdf', $data);
        if (empty($rc)) {
            return $d;
        } else {
            throw new \RuntimeException("Failed to create document: " . $rc);
        }
    }
}
