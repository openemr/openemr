<?php

/**
 * Bootstrap 4's @media print block (page-break-inside: avoid on tr/img,
 * min-width: 992px !important on body, @page { size: a3 }) causes mPDF,
 * which identifies as print media by default, to emit one mostly-blank
 * page per block element - a 1-2 page patient report ballooned into
 * 2,219 blank pages (see mpdf/mpdf#1266).
 *
 * Config_Mpdf sets CSSselectMedia => 'screen' so mPDF skips @media print
 * blocks in any stylesheet it is handed. These tests lock in the mPDF
 * behavior, the Config_Mpdf configuration, and the rationale.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Pdf;

use Mpdf\Mpdf;
use OpenEMR\Pdf\Config_Mpdf;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PdfPrintMediaCssTest extends TestCase
{
    /**
     * The minimal rule set bisected out of the compiled style_pdf.css
     * (Bootstrap 4.6 _print.scss) that reproduces the blank-page explosion.
     */
    private const GUILTY_CSS = '@media print{blockquote,img,pre,tr{page-break-inside:avoid}'
        . '@page{size:a3}.container,body{min-width:992px!important}}';

    /**
     * Globals that Config_Mpdf::getConfigMpdf() reads through OEGlobalsBag,
     * which sources the singleton's values directly from $GLOBALS.
     */
    private const PDF_GLOBALS = [
        'pdf_language' => 'en',
        'pdf_size' => 'LETTER',
        'pdf_font_size' => '10',
        'pdf_left_margin' => '5',
        'pdf_right_margin' => '5',
        'pdf_top_margin' => '5',
        'pdf_bottom_margin' => '8',
        'pdf_layout' => 'P',
    ];

    /** @var array<string, mixed> */
    private array $originalPdfGlobals = [];

    protected function setUp(): void
    {
        $this->originalPdfGlobals = array_intersect_key($GLOBALS, self::PDF_GLOBALS);
        foreach (self::PDF_GLOBALS as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }

    protected function tearDown(): void
    {
        foreach (array_keys(self::PDF_GLOBALS) as $key) {
            unset($GLOBALS[$key]);
        }
        foreach ($this->originalPdfGlobals as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }

    public function testScreenMediaRendersCompactly(): void
    {
        $this->assertLessThan(
            10,
            $this->renderPages(['CSSselectMedia' => 'screen']),
            'With CSSselectMedia=screen, the Bootstrap print block must be ignored'
        );
    }

    public function testDefaultPrintMediaStillExplodesDocumentingWhyTheOverrideExists(): void
    {
        // If mPDF ever fixes mpdf/mpdf#1266 this will start failing, which
        // is the signal that CSSselectMedia (and this test) can be
        // reevaluated.
        $this->assertGreaterThan(
            50,
            $this->renderPages([]),
            'mPDF default (print media) is expected to explode on the Bootstrap print block'
        );
    }

    public function testConfigMpdfSelectsScreenMedia(): void
    {
        $this->assertSame('screen', Config_Mpdf::getConfigMpdf()['CSSselectMedia']);
    }

    private function fixtureHtml(): string
    {
        $rows = '';
        for ($i = 0; $i < 300; $i++) {
            $rows .= "<tr><td>label $i</td><td>value $i</td></tr>";
        }

        return '<style>' . self::GUILTY_CSS . '</style>'
            . '<table>' . $rows . '</table>';
    }

    /**
     * @param array<string, mixed> $configOverride
     */
    private function renderPages(array $configOverride): int
    {
        $pdf = new Mpdf(array_merge([
            'tempDir' => sys_get_temp_dir(),
            'format' => 'LETTER',
        ], $configOverride));
        $pdf->WriteHTML($this->fixtureHtml());

        $pages = $pdf->page;
        if (!is_int($pages)) {
            self::fail('mPDF page count is not an int: ' . get_debug_type($pages));
        }
        return $pages;
    }
}
