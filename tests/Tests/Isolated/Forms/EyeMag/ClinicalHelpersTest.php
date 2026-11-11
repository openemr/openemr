<?php

/**
 * Isolated tests for Eye Form clinical helpers, material embedding, wear cells, and neuro guidance strings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use Mpdf\Mpdf;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\PatientHandout;
use OpenEMR\Forms\EyeMag\PatientMaterials;
use OpenEMR\Forms\EyeMag\PatientReportDocument;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function OpenEMR\Forms\EyeMag\Clinical\display_VisualAcuitiesModern;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_colgroup;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_comment_tail_cells;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_distance_cells;
use function OpenEMR\Forms\EyeMag\Clinical\display_wear_lower_cells;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_append_handout_pages;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_dilation_med_defaults;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_document_ids;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_embed_materials;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_defaults;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_material_ids;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_neuro_prior;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_report_filename;

require_once dirname(__DIR__, 5) . '/library/htmlspecialchars.inc.php';
require_once dirname(__DIR__, 5) . '/library/translation.inc.php';
require_once dirname(__DIR__, 5) . '/interface/forms/eye_mag/php/clinical_helpers.php';

final class ClinicalHelpersTest extends TestCase
{
    private string $tempDir;

    /** @var array<string, array{present: bool, value: mixed}> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/eye-clinical-helpers-' . bin2hex(random_bytes(5));
        mkdir($this->tempDir, 0755, true);

        $bag = OEGlobalsBag::getInstance();
        foreach (['disable_translation', 'temporary_files_dir'] as $key) {
            $this->savedGlobals[$key] = [
                'present' => $bag->has($key),
                'value' => $bag->get($key),
            ];
        }
        $bag->set('disable_translation', true);
        $bag->set('temporary_files_dir', $this->tempDir);
    }

    protected function tearDown(): void
    {
        $bag = OEGlobalsBag::getInstance();
        foreach ($this->savedGlobals as $key => $state) {
            if ($state['present']) {
                $bag->set($key, $state['value']);
            } else {
                $bag->remove($key);
            }
        }
        $this->removeDirectory($this->tempDir);
    }

    #[Test]
    public function neuroPriorRendersReadOnlyExamChartsOveractionsAndEscapedFields(): void
    {
        $exam = [
            'STEREO_FLY' => 'U/A',
            'STEREO_ANIMALS' => '2',
            'STEREO_CIRCLES' => '7',
            'STEREO_FIXATION_PREF' => 'OD',
            'STEREOPSIS' => '40 arcsec',
            'MOTILITYNORMAL' => '1',
            'MOTILITY_RS' => '2',
            'MOTILITY_RRSO' => '1',
            'MOTILITY_RRSO_OVER' => '3',
            'MOTILITY_LLIO_OVER' => '2',
            'ACT' => 'on',
            'ACT5CCDIST' => '14 XT',
            'ODCOLOR' => '11/11',
            'OSCOLOR' => '11/11',
            'NPC' => '6 cm',
            'NEURO_COMMENTS' => 'Follow <closely>',
        ];

        $xHtml = eye_mag_neuro_prior($exam, 'X');
        $this->assertStringContainsString('data-display="X"', $xHtml);
        $this->assertStringContainsString('data-field="MOTILITY_RS" data-grade="2"', $xHtml);
        $this->assertStringContainsString('+3</text>', $xHtml);
        $this->assertStringContainsString('+2</text>', $xHtml);
        $this->assertStringContainsString('<details class="eye-neuro-prior-act-details" open>', $xHtml);
        $this->assertStringContainsString('14 XT', $xHtml);
        $this->assertStringContainsString('Follow &lt;closely&gt;', $xHtml);

        $hHtml = eye_mag_neuro_prior(['STEREO_FLY' => 'Yes'], 'H');
        $this->assertStringContainsString('data-display="H"', $hHtml);
        $this->assertStringNotContainsString('<details class="eye-neuro-prior-act-details" open>', $hHtml);
    }

    #[Test]
    public function visualAcuityHistoryShowsEmptyAlertWhenNoQualifyingPriorVisitsExist(): void
    {
        $emptyHtml = display_VisualAcuitiesModern(10, [], '2026-11-10');
        $this->assertStringContainsString('Visual acuity history will appear after a prior visit is available.', $emptyHtml);

        $futureFiltered = display_VisualAcuitiesModern(
            10,
            [
                ['visit_date' => 'invalid-date', 'form_id' => 1],
                ['visit_date' => '2026-12-31', 'form_id' => 2],
            ],
            '2026-11-10'
        );
        $this->assertStringContainsString('Visual acuity history will appear after a prior visit is available.', $futureFiltered);
    }

    #[Test]
    public function documentAndMaterialIdHelpersNormalizeDeduplicateAndPreserveLegacyFallback(): void
    {
        $this->assertSame([12, 8, 99], eye_mag_document_ids('[12, "8", "12", 0, -4, "abc", [5], "99"]'));
        $this->assertSame([4, 9], eye_mag_document_ids([4, '9', '09', '', null]));
        $this->assertSame([], eye_mag_document_ids('not-json'));

        // NULL handout_document_ids falls back to legacy handout_document_id; [] means none.
        $this->assertSame([41], eye_mag_material_ids(['handout_document_id' => 41, 'handout_document_ids' => null]));
        $this->assertSame([], eye_mag_material_ids(['handout_document_id' => 41, 'handout_document_ids' => '[]']));
        $this->assertSame([7, 15], eye_mag_material_ids(['handout_document_id' => 41, 'handout_document_ids' => '[7, 15, 7]']));
    }

    #[Test]
    public function defaultsAndReportFilenameExposeStableKeysAndUniquePdfNames(): void
    {
        $dilationDefaults = eye_mag_dilation_med_defaults();
        $this->assertSame(
            ['CYCLOMYDRIL', 'TROPICAMIDE', 'NEO25', 'NEO10', 'CYCLOGYL', 'ATROPINE'],
            array_keys($dilationDefaults)
        );

        $iopDefaults = eye_mag_iop_method_defaults();
        $this->assertSame(['AP', 'TPN', 'FT', 'POST'], array_keys($iopDefaults));

        $first = eye_mag_report_filename(42, 108);
        $second = eye_mag_report_filename(42, 108);
        $this->assertMatchesRegularExpression('/\AEye_Report_42_108_\d{8}_\d{6}_[a-f0-9]{12}\.pdf\z/', $first);
        $this->assertNotSame($first, $second);
    }

    #[Test]
    public function embedMaterialsAndAppendHandoutPagesAttachOriginalsAndImportValidPdfs(): void
    {
        $sourcePdf = new Mpdf(['tempDir' => $this->tempDir]);
        $sourcePdf->WriteHTML('<p>Page one handout</p>');
        $sourcePdf->AddPage();
        $sourcePdf->WriteHTML('<p>Page two handout</p>');
        $validPdfBytes = $sourcePdf->Output('', 'S');
        $this->assertIsString($validPdfBytes);
        $this->assertStringStartsWith('%PDF-', $validPdfBytes);

        $materials = new PatientMaterials(
            'Warm compresses twice daily.',
            true,
            [
                new PatientHandout(11, 'Blepharitis.pdf', $validPdfBytes),
                new PatientHandout(12, 'Unavailable.pdf', ''),
            ],
            [
                new PatientReportDocument(21, 'OCT.pdf', 'application/pdf', $validPdfBytes),
                new PatientReportDocument(22, 'SlitLamp.jpg', 'image/jpeg', 'jpeg-data'),
                new PatientReportDocument(23, 'Cornea.png', 'image/png', 'png-data'),
                new PatientReportDocument(24, 'Blink.gif', 'image/gif', 'gif-data'),
                new PatientReportDocument(25, 'Empty.pdf', 'application/pdf', ''),
                new PatientReportDocument(26, 'Unknown.bin', 'application/octet-stream', 'raw'),
            ]
        );

        $reportPdf = new Mpdf(['tempDir' => $this->tempDir]);
        $reportPdf->WriteHTML('<p>Eye exam summary</p>');
        $this->assertTrue(eye_mag_embed_materials($reportPdf, 99, 7, $materials));
        $this->assertCount(6, $reportPdf->associatedFiles);

        // Calling eye_mag_embed_materials a second time does not duplicate identical attachments.
        $this->assertTrue(eye_mag_embed_materials($reportPdf, 99, 7, $materials));
        $this->assertCount(6, $reportPdf->associatedFiles);

        $this->assertTrue(eye_mag_append_handout_pages($reportPdf, $validPdfBytes));
        $this->assertFalse(eye_mag_append_handout_pages($reportPdf, 'not-a-valid-pdf'));
    }

    #[Test]
    public function wearTableCellHelpersRenderEscapedInputsForBothEyes(): void
    {
        ob_start();
        display_wear_colgroup();
        display_wear_distance_cells(1, 'OD', ['ODPRISM' => '2 BO', 'ODVERTEXDIST' => '12', 'ODSLABOFF' => '1.5']);
        display_wear_lower_cells(1, 'OD', ['ODMPDD' => '31', 'ODMPDN' => '29']);
        display_wear_lower_cells(1, 'OS', ['OSMPDD' => '31.5', 'OSMPDN' => '29.5', 'BPDD' => '62.5', 'BPDN' => '58.5']);
        display_wear_comment_tail_cells();
        $html = (string) ob_get_clean();

        $this->assertStringContainsString('<colgroup>', $html);
        $this->assertStringContainsString('id="ODPRISM_1"', $html);
        $this->assertStringContainsString('value="2 BO"', $html);
        $this->assertStringContainsString('for="BPDD_1"', $html);
        $this->assertStringContainsString('id="BPDD_1"', $html);
        $this->assertStringContainsString('value="62.5"', $html);
    }

    #[Test]
    public function neuroPatternStringsEmitValidJsonDictionary(): void
    {
        ob_start();
        require dirname(__DIR__, 5) . '/interface/forms/eye_mag/php/neuro_pattern_strings.php';
        $script = (string) ob_get_clean();

        $this->assertStringContainsString('window.eyeNeuroPatternStrings = {', $script);
        $this->assertStringContainsString('ivPattern: ', $script);
        $this->assertStringContainsString('viPattern: ', $script);
        $this->assertStringContainsString('iiiPattern: ', $script);
        $this->assertStringContainsString('disclaimer: "Measurement guidance only', $script);
        $this->assertStringContainsString('</script>', $script);
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
