<?php

/**
 * Database-backed unit tests for Eye Form refractions, visual acuity history, materials, and preferences.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Forms\EyeMag;

use GdImage;
use Mpdf\Mpdf;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Forms\EyeMag\ReportRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function OpenEMR\Forms\EyeMag\Clinical\display_VisualAcuitiesModern;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_append_materials_handout_pdf;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_append_report_materials;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_dilation_med_options;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_ensure_handout_category;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_labels;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_iop_method_options;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_material_encounter;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_material_handout;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_materials_for_report;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_motility_display_preference;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_patient_photo;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_prior_refractions;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_report_documents;
use function OpenEMR\Forms\EyeMag\Clinical\eye_mag_save_materials;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_manage;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_metadata;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_library_save_bytes;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_paths;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_preference;
use function OpenEMR\Forms\EyeMag\Drawing\eye_mag_drawing_background_save_preference;

require_once dirname(__DIR__, 4) . '/interface/forms/eye_mag/php/clinical_helpers.php';
require_once dirname(__DIR__, 4) . '/interface/forms/eye_mag/php/drawing_backgrounds.php';

final class EyeMagDatabaseWorkflowsTest extends TestCase
{
    private const TEST_PID = 994201;

    private const TEST_ENCOUNTER = 994202;

    private const PRIOR_FORM_ID = 29400;

    private const CURRENT_FORM_ID = 29401;

    private const TEST_PROVIDER_ID = 994204;

    private const HANDOUT_DOC_ID = 994210;

    private const ENCOUNTER_DOC_ID = 994211;

    private const REPORT_ARTIFACT_DOC_ID = 994212;

    private string $tempDir;

    /** @var array<string, array{present: bool, value: mixed}> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/eye-db-unit-' . bin2hex(random_bytes(5));
        mkdir($this->tempDir . '/sites/default/images/eye_mag', 0755, true);

        $bag = OEGlobalsBag::getInstance();
        foreach (['disable_translation', 'OE_SITE_DIR', 'OE_SITE_WEBROOT', 'temporary_files_dir', 'patient_photo_category_name'] as $key) {
            $this->savedGlobals[$key] = [
                'present' => $bag->has($key),
                'value' => $bag->get($key),
            ];
        }
        $bag->set('disable_translation', true);
        $bag->set('OE_SITE_DIR', $this->tempDir . '/sites/default');
        $bag->set('OE_SITE_WEBROOT', '/openemr/sites/default');
        $bag->set('temporary_files_dir', $this->tempDir);

        $this->cleanupTestRows();
    }

    protected function tearDown(): void
    {
        try {
            $this->cleanupTestRows();
        } finally {
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
    }

    #[Test]
    public function refractionsPriorLookupAndVisualAcuityHistoryFormatClinicalMeasurements(): void
    {
        // Insert prior and current refraction + acuity + wearing rows.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_eye_refraction (id, pid, BALANCED, MRODSPH, MRODCYL, MRODAXIS, MROSSPH, ARODSPH, CRODSPH, CRCOMMENTS, CTLODSPH, CTLODBC, CTLODDIAM, CTL_COMMENTS)
             VALUES (?, ?, '', '-1.75', '-0.50', '90', '-1.25', '', '', '', '', '', '', ''),
                    (?, ?, '', '-2.25', '-0.75', '180', '-2.00', '-2.50', '-2.00', 'Cycloplegia 1%', '-2.00', '8.6', '14.2', 'Daily disposable')",
            [self::PRIOR_FORM_ID, self::TEST_PID, self::CURRENT_FORM_ID, self::TEST_PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_eye_acuity (id, pid, LIODVA, LIOSVA, SCODVA, SCOSVA, PHODVA, PHOSVA, MRODVA, MROSVA, ARODVA, AROSVA, CRODVA, CROSVA, CTLODVA, CTLOSVA)
             VALUES (?, ?, '', '', 'CF 3 FT', 'HM 2 FT', '20/60', 'LP', '20/30 -2', '20/40 +1', '', '', '', '', '', ''),
                    (?, ?, '', '', '20/80', 'NLP', '20/40', '', '20/20', '20/25', '20/25', '20/30', '20/20', '20/25', '20/20', '20/25')",
            [self::PRIOR_FORM_ID, self::TEST_PID, self::CURRENT_FORM_ID, self::TEST_PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_eye_mag_wearing (ENCOUNTER, FORM_ID, PID, RX_NUMBER, RX_TYPE, ODSPH, ODCYL, ODAXIS, ODVA, ODADD, OSSPH, OSCYL, OSAXIS, OSVA, OSADD, ODHPD, ODHBASE, COMMENTS)
             VALUES (?, ?, ?, 1, '1', '-1.50', '-0.50', '175', '20/25', '+2.00', '-1.25', '', '', '20/30', '+2.00', '2', 'BI', 'Current bifocals')",
            [self::TEST_ENCOUNTER, self::CURRENT_FORM_ID, self::TEST_PID]
        );

        $sections = ReportRenderer::refractions(self::TEST_PID, self::TEST_ENCOUNTER, self::CURRENT_FORM_ID);
        $labels = array_column($sections, 'label');
        $this->assertContains('Current Rx #1 · Bifocals', $labels);
        $this->assertContains('Auto Refraction', $labels);
        $this->assertContains('Manifest Refraction', $labels);
        $this->assertContains('Cycloplegic Refraction', $labels);
        $this->assertContains('Contact Lens', $labels);
        $this->assertSame('2 BI', $sections[0]['od']['prism']);

        $priors = eye_mag_prior_refractions(self::TEST_PID, self::CURRENT_FORM_ID, 3);
        $this->assertCount(1, $priors);
        $this->assertSame((string) self::PRIOR_FORM_ID, (string) ($priors[0]['id'] ?? ''));

        $vaHtml = display_VisualAcuitiesModern(
            self::TEST_PID,
            [
                [
                    'form_id' => self::CURRENT_FORM_ID,
                    'visit_date' => '2026-10-15',
                    'SCODVA' => '20/80',
                    'SCOSVA' => 'NLP',
                    'PHODVA' => '20/40',
                    'MRODVA' => '20/20',
                    'MROSVA' => '20/25',
                    'ARODVA' => '20/25',
                    'AROSVA' => '20/30',
                ],
                [
                    'form_id' => self::PRIOR_FORM_ID,
                    'visit_date' => '2026-06-01',
                    'SCODVA' => 'CF 3 FT',
                    'SCOSVA' => 'HM 1 M',
                    'PHODVA' => '20/60',
                    'PHOSVA' => 'LP',
                    'MRODVA' => '20/30 -2',
                    'MROSVA' => '20/40 +1',
                ],
            ],
            '2026-11-10'
        );

        $this->assertStringContainsString('Snellen 20/x (worse toward top)', $vaHtml);
        $this->assertStringContainsString('Best Corrected Visual Acuity Over Time', $vaHtml);
        $this->assertStringContainsString('CF 3 FT', $vaHtml);
        $this->assertStringContainsString('20/20', $vaHtml);
    }

    #[Test]
    public function listPreferencesHandoutCategoriesMaterialsAndDrawingDefaultsPersistCleanly(): void
    {
        // Practice list preferences reflect defaults and custom list_options entries.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO list_options (list_id, option_id, title, notes, seq, is_default, activity)
             VALUES ('Eye_Dilation_Meds', 'TROPICAMIDE', 'Custom Tropic 1%', 'Tropicamide 1%', 10, 0, 1),
                    ('Eye_IOP_Methods', 'TPN', 'Tono-Pen', '', 20, 0, 0),
                    ('Eye_Motility_Display', 'H', 'H chart', '', 10, 1, 1)"
        );

        $dilation = array_column(eye_mag_dilation_med_options(), 'title', 'field');
        $this->assertSame('Custom Tropic 1%', $dilation['TROPICAMIDE'] ?? '');
        $iopOptions = eye_mag_iop_method_options();
        $this->assertSame('Tono-Pen', $iopOptions['TPN']['title']);
        $this->assertFalse($iopOptions['TPN']['active']);
        $this->assertSame('Tono-Pen', eye_mag_iop_method_labels()['TPN']);
        $this->assertSame('H', eye_mag_motility_display_preference());

        OEGlobalsBag::getInstance()->set('patient_photo_category_name', '');
        $this->assertSame([], eye_mag_patient_photo(self::TEST_PID));
        OEGlobalsBag::getInstance()->set('patient_photo_category_name', 'Nonexistent_Eye_Test_Category');
        $this->assertSame([], eye_mag_patient_photo(self::TEST_PID));

        // Handout category creation / lookup and materials persistence.
        $handoutCategoryId = eye_mag_ensure_handout_category();
        $this->assertGreaterThan(0, $handoutCategoryId);
        $this->assertSame($handoutCategoryId, eye_mag_ensure_handout_category());

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir)
             VALUES (NOW(), ?, 'Eye Exam', ?, ?, 'admin', 'Default', 1, 0, 'eye_mag')",
            [self::TEST_ENCOUNTER, self::CURRENT_FORM_ID, self::TEST_PID]
        );
        $this->assertSame(self::TEST_ENCOUNTER, eye_mag_material_encounter(self::CURRENT_FORM_ID, self::TEST_PID));

        $handoutPdf = new Mpdf(['tempDir' => $this->tempDir]);
        $handoutPdf->WriteHTML('<p>Post-operative drop schedule</p>');
        $postopPath = $this->tempDir . '/postop.pdf';
        file_put_contents($postopPath, (string) $handoutPdf->Output('', 'S'));

        $octImage = imagecreatetruecolor(120, 90);
        $this->assertInstanceOf(GdImage::class, $octImage);
        $octPath = $this->tempDir . '/oct.png';
        imagepng($octImage, $octPath);
        imagedestroy($octImage);

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO documents (id, type, size, date, url, mimetype, foreign_id, docdate, hash, list_id, name, encounter_id, deleted, revision)
             VALUES (?, 'file_url', 120, NOW(), ?, 'application/pdf', 0, CURDATE(), '', 0, 'Post-Op Drops.pdf', 0, 0, NOW()),
                    (?, 'file_url', 240, NOW(), ?, 'image/png', ?, CURDATE(), '', 0, 'OCT_Scan.png', ?, 0, NOW()),
                    (?, 'file_url', 90, NOW(), 'file:///tmp/rpt.pdf', 'application/pdf', ?, CURDATE(), '', 0, 'Eye_Report_994201_994202.pdf', ?, 0, NOW())",
            [
                self::HANDOUT_DOC_ID, 'file://' . $postopPath,
                self::ENCOUNTER_DOC_ID, 'file://' . $octPath, self::TEST_PID, self::TEST_ENCOUNTER,
                self::REPORT_ARTIFACT_DOC_ID, self::TEST_PID, self::TEST_ENCOUNTER,
            ]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO categories_to_documents (category_id, document_id) VALUES (?, ?)",
            [$handoutCategoryId, self::HANDOUT_DOC_ID]
        );

        $this->assertSame('Post-Op Drops.pdf', eye_mag_material_handout(self::HANDOUT_DOC_ID, self::TEST_PID)['name'] ?? '');

        $encounterDocs = eye_mag_report_documents(self::TEST_PID, self::TEST_ENCOUNTER);
        $encounterDocIds = array_column($encounterDocs, 'id');
        $this->assertContains(self::ENCOUNTER_DOC_ID, $encounterDocIds);
        $this->assertNotContains(self::REPORT_ARTIFACT_DOC_ID, $encounterDocIds);

        eye_mag_save_materials(self::CURRENT_FORM_ID, self::TEST_PID, [
            'MATERIALS_TO_PRINT' => 'Use artificial tears PRN.',
            'MATERIALS_TEXT_INCLUDED' => '1',
            'MATERIALS_HANDOUT_IDS' => json_encode([self::HANDOUT_DOC_ID]),
        ]);
        // Malformed payload preserves the previously saved handout list rather than wiping it.
        eye_mag_save_materials(self::CURRENT_FORM_ID, self::TEST_PID, [
            'MATERIALS_TO_PRINT' => 'Use artificial tears PRN.',
            'MATERIALS_TEXT_INCLUDED' => '1',
            'MATERIALS_HANDOUT_IDS' => '{malformed',
        ]);

        $snapshot = eye_mag_materials_for_report(self::CURRENT_FORM_ID, self::TEST_PID);
        $this->assertTrue($snapshot->includeText);
        $this->assertCount(1, $snapshot->handouts);
        $this->assertCount(1, $snapshot->documents);

        $pdf = new Mpdf(['tempDir' => $this->tempDir]);
        $pdf->WriteHTML('<p>Report body</p>');
        $this->assertTrue(eye_mag_append_materials_handout_pdf($pdf, self::CURRENT_FORM_ID, self::TEST_PID));
        eye_mag_append_report_materials($pdf, self::CURRENT_FORM_ID, self::TEST_PID);
        $this->assertNotEmpty($pdf->imageVars);

        // Provider drawing background preference save, read, legacy prefix recovery, and delete reset.
        $paths = eye_mag_drawing_background_paths('RETINA', self::TEST_PROVIDER_ID);
        $savedKey = eye_mag_drawing_background_library_save_bytes($paths, 'png-overlay-bytes', 'Peripheral tear');
        eye_mag_drawing_background_save_preference('RETINA', self::TEST_PROVIDER_ID, $savedKey);
        $this->assertSame($savedKey, eye_mag_drawing_background_preference('RETINA', self::TEST_PROVIDER_ID));

        eye_mag_drawing_background_library_manage('RETINA', self::TEST_PROVIDER_ID, 'delete', $savedKey);
        $this->assertTrue(eye_mag_drawing_background_library_metadata($paths, $savedKey, true)->deleted);
        $this->assertSame('default', eye_mag_drawing_background_preference('RETINA', self::TEST_PROVIDER_ID));
    }

    private function cleanupTestRows(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM form_eye_mag_wearing WHERE PID = ?', [self::TEST_PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_eye_acuity WHERE pid = ?', [self::TEST_PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_eye_refraction WHERE pid = ?', [self::TEST_PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_eye_mag_materials WHERE pid = ?', [self::TEST_PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_eye_mag_prefs WHERE id = ?', [self::TEST_PROVIDER_ID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM forms WHERE pid = ?', [self::TEST_PID]);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM categories_to_documents WHERE document_id IN (?, ?, ?)',
            [self::HANDOUT_DOC_ID, self::ENCOUNTER_DOC_ID, self::REPORT_ARTIFACT_DOC_ID]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM documents WHERE id IN (?, ?, ?)',
            [self::HANDOUT_DOC_ID, self::ENCOUNTER_DOC_ID, self::REPORT_ARTIFACT_DOC_ID]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM list_options WHERE list_id IN ('Eye_Dilation_Meds', 'Eye_IOP_Methods', 'Eye_Motility_Display')"
        );
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
