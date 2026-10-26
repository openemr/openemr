<?php

/**
 * DornGenHl7OrderDiagnosesTest.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use Composer\Autoload\ClassLoader;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\Dorn\DornGenHl7Order;
use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use OpenEMR\Tests\Fixtures\ProcedureProviderFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The DORN order generator must nest each ordered test's diagnoses beneath
 * that test's own OBR segment, not repeat every test's diagnoses under each OBR.
 *
 * @see https://github.com/openemr/openemr/issues/13726
 */
class DornGenHl7OrderDiagnosesTest extends TestCase
{
    /** Marker on the insurance_data row this test owns, so tearDown can find it. */
    private const string GUARANTOR_MARKER = 'test-fixture-dorn-guarantor';

    private ProcedureOrderFixtureManager $orderFixtures;

    private int $orderId = 0;

    private int $patientId = 0;

    private bool $hadSpecimenFastingRequest = false;

    private ?string $originalSpecimenFastingRequest = null;

    /**
     * @codeCoverageIgnore Fixture wiring; runs before coverage attribution.
     */
    public static function setUpBeforeClass(): void
    {
        // The module registers its namespace from openemr.bootstrap.php only when
        // it is enabled, so register it here the same way.
        $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4(
            'OpenEMR\\Modules\\Dorn\\',
            $projectDir . '/interface/modules/custom_modules/oe-module-dorn/src/'
        );
        // lookup_code_descriptions(), which the generator calls for every DG1 segment.
        require_once $projectDir . '/custom/code_types.inc.php';
    }

    protected function setUp(): void
    {
        $this->orderFixtures = new ProcedureOrderFixtureManager(null, null, new ProcedureProviderFixtureManager());
        $this->orderFixtures->installFixtures();

        $orderIds = $this->orderFixtures->getInstalledOrderIds();
        $this->assertNotCount(0, $orderIds, 'Procedure order fixtures should install at least one order');
        $this->orderId = $orderIds[0];

        $patientId = QueryUtils::fetchSingleValue(
            'SELECT pid FROM forms WHERE formdir = ? AND form_id = ?',
            'pid',
            ['procedure_order', $this->orderId]
        );
        $this->assertIsInt($patientId);
        $this->patientId = $patientId;

        // Client billed, so the generator needs no payers; no order-level
        // diagnosis, so every DG1 segment comes from an ordered test.
        QueryUtils::sqlStatementThrowException(
            "UPDATE procedure_order SET billing_type = 'C', order_diagnosis = '' WHERE procedure_order_id = ?",
            [$this->orderId]
        );

        // The generator refuses an order whose patient has no guarantor.
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO insurance_data
            SET pid = ?, type = 'primary', date = '2000-01-01', subscriber_fname = 'Test',
                subscriber_mname = '', subscriber_lname = ?, subscriber_relationship = 'self',
                subscriber_street = '1 Main St', subscriber_city = 'Springfield',
                subscriber_state = 'IL', subscriber_postal_code = '62701'
            SQL,
            [$this->patientId, self::GUARANTOR_MARKER]
        );

        // Posted by interface/forms/procedure_order on every real order; the
        // generator reads it without guarding for its absence.
        $existing = $_REQUEST['form_specimen_fasting'] ?? null;
        $this->hadSpecimenFastingRequest = is_string($existing);
        $this->originalSpecimenFastingRequest = is_string($existing) ? $existing : null;
        $_REQUEST['form_specimen_fasting'] = 'NO';
    }

    protected function tearDown(): void
    {
        if ($this->hadSpecimenFastingRequest && $this->originalSpecimenFastingRequest !== null) {
            $_REQUEST['form_specimen_fasting'] = $this->originalSpecimenFastingRequest;
        } else {
            unset($_REQUEST['form_specimen_fasting']);
        }
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM insurance_data WHERE pid = ? AND subscriber_lname = ?',
            [$this->patientId, self::GUARANTOR_MARKER]
        );
        $this->orderFixtures->removeFixtures();
    }

    /**
     * Diagnoses of the two fixture tests (80053 and 85025) and the DG1 codes
     * expected beneath each test's OBR. PHP turns the numeric procedure codes
     * into integer keys.
     *
     * @return array<string, array{string, string, array<int|string, list<string>>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function diagnosesProvider(): array
    {
        return [
            'one diagnosis per test' => [
                'ICD10:E11.9',
                'ICD10:I10',
                ['80053' => ['E11.9'], '85025' => ['I10']],
            ],
            'two diagnoses on the first test' => [
                'ICD10:E11.9;ICD10:E78.5',
                'ICD10:I10',
                ['80053' => ['E11.9', 'E78.5'], '85025' => ['I10']],
            ],
            'second test without a diagnosis' => [
                'ICD10:E11.9',
                '',
                ['80053' => ['E11.9'], '85025' => []],
            ],
        ];
    }

    /**
     * @param array<int|string, list<string>> $expected
     */
    #[Test]
    #[DataProvider('diagnosesProvider')]
    public function eachObrCarriesOnlyItsOwnTestsDiagnoses(
        string $firstTestDiagnoses,
        string $secondTestDiagnoses,
        array $expected
    ): void {
        $this->setTestDiagnoses(1, $firstTestDiagnoses);
        $this->setTestDiagnoses(2, $secondTestDiagnoses);

        $hl7 = '';
        $error = (new DornGenHl7Order())->genHl7Order($this->orderId, $hl7);

        $this->assertSame('', $error);
        $this->assertSame($expected, self::diagnosesByTest($hl7));
    }

    private function setTestDiagnoses(int $sequence, string $diagnoses): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE procedure_order_code SET diagnoses = ? WHERE procedure_order_id = ? AND procedure_order_seq = ?',
            [$diagnoses, $this->orderId, $sequence]
        );
    }

    /**
     * The DG1 diagnosis codes that follow each OBR, keyed by the OBR's procedure code.
     *
     * @return array<int|string, list<string>>
     */
    private static function diagnosesByTest(string $hl7): array
    {
        $diagnoses = [];
        $procedureCode = null;
        foreach (explode("\r", $hl7) as $segment) {
            $fields = explode('|', $segment);
            if ($fields[0] === 'OBR') {
                $procedureCode = explode('^', $fields[4] ?? '')[0];
                $diagnoses[$procedureCode] = [];
            } elseif ($fields[0] === 'DG1' && $procedureCode !== null) {
                $diagnoses[$procedureCode][] = explode('^', $fields[3] ?? '')[0];
            }
        }
        return $diagnoses;
    }
}
