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

use OpenEMR\Core\OEGlobalsBag;

class Config_Mpdf
{
    public static function getConfigMpdf()
    {
        return [
            'tempDir' => OEGlobalsBag::getInstance()->get('MPDF_WRITE_DIR'),
            'mode' => OEGlobalsBag::getInstance()->get('pdf_language'),
            'format' => OEGlobalsBag::getInstance()->get('pdf_size'),
            'default_font_size' => OEGlobalsBag::getInstance()->get('pdf_font_size') ?? '9',
            'default_font' => 'dejavusans',
            'margin_left' => OEGlobalsBag::getInstance()->get('pdf_left_margin'),
            'margin_right' => OEGlobalsBag::getInstance()->get('pdf_right_margin'),
            'margin_top' => OEGlobalsBag::getInstance()->get('pdf_top_margin'),
            'margin_bottom' => OEGlobalsBag::getInstance()->get('pdf_bottom_margin'),
            'margin_header' => '',
            'margin_footer' => '',
            'orientation' => OEGlobalsBag::getInstance()->get('pdf_layout'),
            'shrink_tables_to_fit' => 1,
            'use_kwt' => true,
            'autoScriptToLang' => true,
            'keep_table_proportions' => true,
        ];
    }
}
