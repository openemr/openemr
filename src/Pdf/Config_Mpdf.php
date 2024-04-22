<?php

/**
 * This class returns a generic mPDF config array
 *
 * @package OpenEMR
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2023 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Pdf;

class Config_Mpdf
{
    public static function getConfigMpdf()
    {
        return array(
            'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
            'mode' => $GLOBALS['pdf_language'],
            'format' => $GLOBALS['pdf_size'],
            'default_font_size' => $GLOBALS['pdf_font_size'] ?? '9',
            'default_font' => 'dejavusans',
            'margin_left' => $GLOBALS['pdf_left_margin'],
            'margin_right' => $GLOBALS['pdf_right_margin'],
            'margin_top' => $GLOBALS['pdf_top_margin'],
            'margin_bottom' => $GLOBALS['pdf_bottom_margin'],
            'margin_header' => '',
            'margin_footer' => '',
            'orientation' => $GLOBALS['pdf_layout'],
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,
            'autoScriptToLang' => true,
            'keep_table_proportions' => true,
        );
    }
}
