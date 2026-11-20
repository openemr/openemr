<?php

/**
 * Isolated tests for Eye Form report rendering, PDF layout, motility SVG, and base drawing detection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use GdImage;
use Mpdf\Mpdf;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\PatientHandout;
use OpenEMR\Forms\EyeMag\PatientMaterials;
use OpenEMR\Forms\EyeMag\PatientReportDocument;
use OpenEMR\Forms\EyeMag\ReportRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 5) . '/library/htmlspecialchars.inc.php';
require_once dirname(__DIR__, 5) . '/library/translation.inc.php';

final class ReportRendererTest extends TestCase
{
    private string $tempDir;

    private bool $hadDisableTranslation = false;

    private bool $previousDisableTranslation = false;

    protected function setUp(): void
    {
        $bag = OEGlobalsBag::getInstance();
        $this->hadDisableTranslation = $bag->has('disable_translation');
        $this->previousDisableTranslation = $bag->getBoolean('disable_translation');
        $bag->set('disable_translation', true);

        $this->tempDir = sys_get_temp_dir() . '/eye-report-renderer-' . bin2hex(random_bytes(5));
        mkdir($this->tempDir . '/interface/forms/eye_mag/images', 0755, true);
    }

    protected function tearDown(): void
    {
        $bag = OEGlobalsBag::getInstance();
        if ($this->hadDisableTranslation) {
            $bag->set('disable_translation', $this->previousDisableTranslation);
        } else {
            $bag->remove('disable_translation');
        }
        $this->removeDirectory($this->tempDir);
    }

    #[Test]
    public function renderRefractionSummaryOutputsNothingWhenEmptyAndEscapesDocumentedRows(): void
    {
        ob_start();
        ReportRenderer::renderRefractionSummary([]);
        $this->assertSame('', ob_get_clean());

        $rowDefaults = [
            'sphere' => '',
            'cylinder' => '',
            'axis' => '',
            'prism' => '',
            'base_curve' => '',
            'diameter' => '',
            'distance' => '',
            'mid' => '',
            'add' => '',
            'near' => '',
        ];
        $sections = [
            [
                'label' => 'Manifest <Rx>',
                'od' => array_merge($rowDefaults, ['eye' => 'OD', 'sphere' => '-2.00', 'cylinder' => '-0.75', 'axis' => '180', 'distance' => '20/20']),
                'os' => array_merge($rowDefaults, ['eye' => 'OS', 'sphere' => '-1.50', 'distance' => '20/25']),
                'notes' => 'Trial frame <verified>',
            ],
        ];

        ob_start();
        ReportRenderer::renderRefractionSummary($sections);
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('Manifest &lt;Rx&gt;', $html);
        $this->assertStringContainsString('-2.00', $html);
        $this->assertStringContainsString('—', $html);
        $this->assertStringContainsString('Trial frame &lt;verified&gt;', $html);
    }

    #[Test]
    public function materialsTextHandlesEmptyScreenPreviewAndPdfModes(): void
    {
        $emptyMaterials = new PatientMaterials('   ', true, [], []);
        ob_start();
        ReportRenderer::materialsText(
            10,
            20,
            null,
            false,
            static fn(int $formId, int $pid): PatientMaterials => $emptyMaterials,
            static fn(Mpdf $pdf, int $formId, int $pid, PatientMaterials $m): bool => true,
            true
        );
        $this->assertSame('', ob_get_clean());

        $richMaterials = new PatientMaterials(
            "Line 1 <drop>\nLine 2",
            true,
            [
                new PatientHandout(101, 'Dry Eye Guide.pdf', '%PDF-1.4 valid'),
                new PatientHandout(102, '', ''),
            ],
            [
                new PatientReportDocument(201, 'VF Summary.pdf', 'application/pdf', '%PDF-1.4 doc'),
                new PatientReportDocument(202, 'Fundus Photo.jpg', 'image/jpeg', 'jpeg-bytes'),
                new PatientReportDocument(203, '', 'application/pdf', ''),
            ]
        );

        // Inline screen preview renders print button, embedded objects, image, and unavailable notices.
        ob_start();
        ReportRenderer::materialsText(
            10,
            20,
            null,
            false,
            static fn(int $formId, int $pid): PatientMaterials => $richMaterials,
            static fn(Mpdf $pdf, int $formId, int $pid, PatientMaterials $m): bool => false,
            true
        );
        $previewHtml = (string) ob_get_clean();

        $this->assertStringContainsString('eye-report-print-pdf', $previewHtml);
        $this->assertStringContainsString('Line 1 &lt;drop&gt;', $previewHtml);
        $this->assertStringContainsString('Dry Eye Guide.pdf', $previewHtml);
        $this->assertStringContainsString('Document #102', $previewHtml);
        $this->assertStringContainsString('VF Summary.pdf', $previewHtml);
        $this->assertStringContainsString('Fundus Photo.jpg', $previewHtml);
        $this->assertStringContainsString('Document #203', $previewHtml);
        $this->assertStringContainsString('data:application/pdf;base64,', $previewHtml);
        $this->assertStringContainsString('data:image/jpeg;base64,', $previewHtml);

        // PDF mode with an mPDF instance embeds originals and adds a page break.
        $mpdf = new Mpdf(['tempDir' => $this->tempDir]);
        $embedCalled = false;
        ob_start();
        ReportRenderer::materialsText(
            10,
            20,
            $mpdf,
            true,
            static fn(int $formId, int $pid): PatientMaterials => $richMaterials,
            static function (Mpdf $pdf, int $formId, int $pid, PatientMaterials $m) use (&$embedCalled): bool {
                $embedCalled = ($formId === 20 && $pid === 10 && count($m->handouts) === 2);
                return true;
            }
        );
        $pdfHtml = (string) ob_get_clean();

        $this->assertTrue($embedCalled);
        $this->assertStringContainsString('<pagebreak />', $pdfHtml);
        $this->assertStringContainsString('The original PDF is embedded', $pdfHtml);
    }

    #[Test]
    public function drawingIsUntouchedBaseDistinguishesUnmarkedNoiseFromClinicalStrokes(): void
    {
        $renderer = new ReportRenderer($this->tempDir);
        $this->assertFalse($renderer->drawingIsUntouchedBase('anything', 'MISSING'));

        $basePath = $this->tempDir . '/interface/forms/eye_mag/images/OU_RETINA_BASE.jpg';
        $baseImage = $this->solidImage(40, 30, 245, 245, 245);
        imagejpeg($baseImage, $basePath, 95);
        imagedestroy($baseImage);

        $baseBytes = (string) file_get_contents($basePath);
        $this->assertTrue($renderer->drawingIsUntouchedBase($baseBytes, 'RETINA'));
        $this->assertFalse($renderer->drawingIsUntouchedBase('not-an-image', 'RETINA'));

        // Mismatched dimensions return false cleanly.
        $wrongSize = $this->solidImage(20, 20, 245, 245, 245);
        $this->assertFalse($renderer->drawingIsUntouchedBase($this->encodePng($wrongSize), 'RETINA'));
        imagedestroy($wrongSize);

        // A high-contrast clinical stroke exceeds the 40-channel-sum threshold and returns false.
        $marked = imagecreatefromjpeg($basePath);
        $this->assertInstanceOf(GdImage::class, $marked);
        $red = imagecolorallocate($marked, 200, 10, 10);
        $this->assertIsInt($red);
        imagesetpixel($marked, 10, 10, $red);
        $this->assertFalse($renderer->drawingIsUntouchedBase($this->encodePng($marked), 'RETINA'));
        imagedestroy($marked);
    }

    #[Test]
    public function flushPdfStripsInlineFontSizesOutsideLetterheadOnlyForPdfOutput(): void
    {
        ob_start();
        echo '<p style="font-size:14px;">Screen text</p>';
        ReportRenderer::flushPdf(false);
        $this->assertSame('<p style="font-size:14px;">Screen text</p>', ob_get_clean());

        ob_start();
        ob_start();
        echo '<div class="rpt-letterhead"><span style="font-size:18pt;color:#000;">Clinic Header</span></div><!--/rpt-letterhead-->'
            . '<div style="font-size: 13px; color: #222;"><span style=\'font-weight:bold;font-size:0.9rem\'>Body</span></div>';
        ReportRenderer::flushPdf(true);
        $flushed = (string) ob_get_clean();

        $this->assertStringStartsWith('<div class="rpt-body">', $flushed);
        $this->assertStringContainsString('font-size:18pt;color:#000;', $flushed);
        $this->assertStringNotContainsString('font-size: 13px', $flushed);
        $this->assertStringNotContainsString('font-size:0.9rem', $flushed);
        $this->assertStringContainsString('color: #222;', $flushed);
        $this->assertStringContainsString('font-weight:bold;', $flushed);
    }

    #[Test]
    public function zoneEmitAndPdfZoneLayoutFormatScreenAndPairedPdfColumns(): void
    {
        ob_start();
        ReportRenderer::zoneHeading('Anterior Segment', false);
        ReportRenderer::rlHeader(false);
        $screenSection = (string) ob_get_clean()
            . '<tr><td style="width:120px;color:#111;">Clear</td><td>Cornea</td><td style="width:120px;">Clear</td></tr>';

        $pdfZones = [];
        ob_start();
        ReportRenderer::zoneEmit(
            $screenSection,
            'ANTSEG',
            50,
            10,
            $pdfZones,
            false,
            static function (string $zone): void {
                echo '<img alt="' . attr($zone) . '" src="antseg.jpg">';
            }
        );
        $screenHtml = (string) ob_get_clean();

        $this->assertSame([], $pdfZones);
        $this->assertStringContainsString('eye-zone-drawing', $screenHtml);
        $this->assertStringContainsString('width:30%;', $screenHtml);
        $this->assertStringNotContainsString('width:120px', $screenHtml);

        // Queue PDF zones: one with drawing, two without drawings (paired), one fullwidth, one trailing single.
        ob_start();
        ReportRenderer::zoneHeading('Retina', true);
        $pdfHeadingSection = (string) ob_get_clean() . '<table><tr><td style="width:95px;">Flat</td></tr></table>';

        ReportRenderer::zoneEmit(
            $pdfHeadingSection,
            'RETINA',
            50,
            10,
            $pdfZones,
            true,
            static function (): void {
                echo '<img src="retina.jpg">';
            }
        );
        ReportRenderer::zoneEmit('<table><tr><td>Ext</td></tr></table>', 'EXT', 50, 10, $pdfZones, true, static function (): void {
        });
        ReportRenderer::zoneEmit('<table><tr><td>Neuro</td></tr></table>', 'NEURO', 50, 10, $pdfZones, true, static function (): void {
        });
        $pdfZones[] = ['section' => '<div>Scleral Depression</div>', 'drawing' => '<img src="sd.jpg">', 'fullwidth' => true];
        ReportRenderer::zoneEmit('<table><tr><td>Plan</td></tr></table>', 'IMPPLAN', 50, 10, $pdfZones, true, static function (): void {
        });

        $this->assertCount(5, $pdfZones);

        ob_start();
        ReportRenderer::pdfZoneLayout($pdfZones);
        $layoutHtml = (string) ob_get_clean();

        $this->assertStringContainsString('retina.jpg', $layoutHtml);
        $this->assertStringContainsString('Ext', $layoutHtml);
        $this->assertStringContainsString('Neuro', $layoutHtml);
        $this->assertStringContainsString('colspan="2" style="text-align:center;padding:0;"><div>Scleral Depression</div><img src="sd.jpg">', $layoutHtml);
        $this->assertStringContainsString('Plan', $layoutHtml);
    }

    #[Test]
    public function motilityDiagramAndActGridRenderScreenAndPdfNotations(): void
    {
        $values = [
            'up' => 2,
            'down' => 0,
            'left' => 5,
            'right' => -1,
            'upperLeft' => 1,
            'upperRight' => 3,
            'lowerLeft' => 0,
            'lowerRight' => 2,
            'upperLeftOver' => 2,
            'lowerRightOver' => 4,
        ];

        $xTag = ReportRenderer::motilityDiagram('X', $values, false);
        $this->assertStringContainsString('width:58px;height:58px;', $xTag);
        $xSvg = $this->decodeSvgDataUri($xTag);
        $this->assertStringContainsString('+2</text>', $xSvg);
        $this->assertStringContainsString('+4</text>', $xSvg);

        $hTag = ReportRenderer::motilityDiagram('h', $values, true);
        $this->assertStringContainsString('width:15mm;height:15mm;', $hTag);
        $hSvg = $this->decodeSvgDataUri($hTag);
        $this->assertStringContainsString('x1="21.00" y1="25.00" x2="21.00" y2="95.00"', $hSvg);
        $this->assertStringContainsString('+2</text>', $hSvg);

        $gazeValues = [5 => '12 XT', 10 => '4 RHT', 11 => '2 RHT'];
        ob_start();
        ReportRenderer::actGrid('ccDist', $gazeValues, false, static fn(mixed $cell): string => text((string) $cell));
        $screenGrid = (string) ob_get_clean();
        $this->assertStringContainsString('fa-reply rotate-left', $screenGrid);
        $this->assertStringContainsString('12 XT', $screenGrid);

        ob_start();
        ReportRenderer::actGrid('ccDist', $gazeValues, true, static fn(mixed $cell): string => text((string) $cell));
        $pdfGrid = (string) ob_get_clean();
        $this->assertStringContainsString('data:image/svg+xml;base64,', $pdfGrid);
        $this->assertStringContainsString('4 RHT', $pdfGrid);

        $this->assertNotSame(ReportRenderer::actArrow(true), ReportRenderer::actArrow(false));
    }

    private function solidImage(int $width, int $height, int $red, int $green, int $blue): GdImage
    {
        $image = imagecreatetruecolor($width, $height);
        $this->assertInstanceOf(GdImage::class, $image);
        $color = imagecolorallocate($image, $red, $green, $blue);
        $this->assertIsInt($color);
        imagefill($image, 0, 0, $color);
        return $image;
    }

    private function encodePng(GdImage $image): string
    {
        ob_start();
        imagepng($image);
        return (string) ob_get_clean();
    }

    private function decodeSvgDataUri(string $imgTag): string
    {
        $matched = preg_match('/src="data:image\/svg\+xml;base64,([^"]+)"/', $imgTag, $matches);
        $this->assertSame(1, $matched);
        $decoded = base64_decode($matches[1], true);
        $this->assertIsString($decoded);
        return $decoded;
    }

    private function removeDirectory(string $directory): void
    {
        if (!file_exists($directory) && !is_link($directory)) {
            return;
        }
        if (is_link($directory) || is_file($directory)) {
            unlink($directory);
            return;
        }
        $entries = scandir($directory);
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $this->removeDirectory($directory . '/' . $entry);
            }
        }
        rmdir($directory);
    }
}
