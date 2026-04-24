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
use OpenEMR\Services\Storage\CacheDirectory;

class Config_Mpdf
{
    public static function getConfigMpdf()
    {
        return [
            'tempDir' => (new CacheDirectory())->for('openemr-mpdf'),
            'mode' => OEGlobalsBag::getInstance()->get('pdf_language'),
            'format' => OEGlobalsBag::getInstance()->get('pdf_size'),
            'default_font_size' => OEGlobalsBag::getInstance()->getInt('pdf_font_size'),
            'default_font' => 'dejavusans',
            'margin_left' => OEGlobalsBag::getInstance()->getInt('pdf_left_margin'),
            'margin_right' => OEGlobalsBag::getInstance()->getInt('pdf_right_margin'),
            'margin_top' => OEGlobalsBag::getInstance()->getInt('pdf_top_margin'),
            'margin_bottom' => OEGlobalsBag::getInstance()->getInt('pdf_bottom_margin'),
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
