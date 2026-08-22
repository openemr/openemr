<?php

/**
 * Integration tests for the four vendor-specific HL7 order generators.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Orders\Hl7OrderGenerationException;
use OpenEMR\Common\Orders\Hl7OrderResult;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\ProcedureOrderFixtureManager;
use OpenEMR\Tests\Fixtures\ProcedureProviderFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Exercises every implementation of the gen_hl7_order contract against a real
 * procedure order in the database.
 *
 * Historically each lab declared a global function literally named
 * `gen_hl7_order()` with a different signature, so only one implementation
 * could be loaded per PHP process and only one could be tested. They now have
 * distinct names, take an `int` order id, return {@see Hl7OrderResult} and
 * throw {@see Hl7OrderGenerationException}, so all four load together and all
 * four are covered here.
 */
class GenHl7OrderIntegrationTest extends TestCase
{
    /** Carriage return: HL7 separates segments with CR, never LF. */
    private const int CR = 13;

    /** Line feed: must never appear in a generated message. */
    private const int LF = 10;

    /** NPI stamped onto the ordering provider so the tests can find it in the output. */
    private const string ORDERING_PROVIDER_NPI = '9876543210';

    /** Facility account code required by the LabCorp generator. */
    private const string FACILITY_ACCOUNT = 'TESTACCT01';

    /** Marker on the procedure_type rows this test owns, so tearDown can find them. */
    private const string PROCEDURE_TYPE_MARKER = 'test-fixture-gen-hl7-order';

    /** Marker on the form_vitals row this test owns, so tearDown can find it. */
    private const string VITALS_MARKER = 'test-fixture-gen-hl7-order-vitals';

    /**
     * Procedure codes carried by tests/Tests/Fixtures/procedure-order-codes.php,
     * keyed by code with the procedure name the fixture uses.
     */
    private const array PROCEDURE_CODES = [
        '80053' => 'Comprehensive Metabolic Panel',
        '85025' => 'Complete Blood Count',
    ];

    private ProcedureProviderFixtureManager $providerFixtures;

    private ProcedureOrderFixtureManager $orderFixtures;

    private int $orderId = 0;

    private int $orderingProviderId = 0;

    private ?string $originalOrderingProviderNpi = null;

    private bool $hadSpecimenFastingRequest = false;

    private ?string $originalSpecimenFastingRequest = null;

    public static function setUpBeforeClass(): void
    {
        // Every generator is a global function in an include file rather than a
        // service, so load all four up front. gen_universal_hl7, labcorp and
        // quest resolve their own includes through $webserver_root.
        $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
        $webserver_root = $projectDir;
        require_once $projectDir . '/interface/orders/gen_hl7_order.inc.php';
        require_once $projectDir . '/interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php';
        require_once $projectDir . '/interface/procedure_tools/labcorp/gen_hl7_order.inc.php';
        require_once $projectDir . '/interface/procedure_tools/quest/gen_hl7_order.inc.php';
    }

    protected function setUp(): void
    {
        $this->providerFixtures = new ProcedureProviderFixtureManager();
        $this->orderFixtures = new ProcedureOrderFixtureManager(null, null, $this->providerFixtures);
        $this->orderFixtures->installFixtures();

        $orderIds = $this->orderFixtures->getInstalledOrderIds();
        $this->assertNotCount(0, $orderIds, 'Procedure order fixtures should install at least one order');
        $this->orderId = $orderIds[0];

        $this->installProcedureTypes();
        $this->installVitals();
        $this->stampOrderingProviderNpi();

        // Self pay with a facility account code: the state in which all four
        // generators produce a message. The insurance and missing-account
        // branches get their own tests below.
        $this->setOrderColumns(['billing_type' => '', 'account' => self::FACILITY_ACCOUNT]);

        // The LabCorp generator reads this request parameter without guarding
        // for its absence. The real caller is interface/forms/procedure_order,
        // which always posts it; supply it here so the generator sees the same
        // shape of input it does in production. Capture whatever was there
        // first so tearDown puts the process back how it found it rather than
        // deleting a key another test owns.
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
        $this->restoreOrderingProviderNpi();
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM procedure_type WHERE description = ?',
            [self::PROCEDURE_TYPE_MARKER]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM forms WHERE formdir = ? AND form_id IN (SELECT id FROM form_vitals WHERE note = ?)',
            ['vitals', self::VITALS_MARKER]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM form_vitals WHERE note = ?', [self::VITALS_MARKER]);
        $this->orderFixtures->removeFixtures();
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testGeneratorProducesWellFormedOrmMessage(Hl7OrderGenerator $generator): void
    {
        $result = $generator->generate($this->orderId);

        $this->assertNotSame('', $result->hl7, 'Generator produced an empty HL7 message');
        $this->assertStringNotContainsString(
            chr(self::LF),
            $result->hl7,
            'HL7 segments must be separated by CR alone, never LF'
        );
        $this->assertStringEndsWith(chr(self::CR), $result->hl7, 'Final segment must be CR terminated');

        $segments = self::segments($result->hl7);
        $this->assertNotCount(0, $segments);
        foreach ($segments as $segment) {
            $this->assertMatchesRegularExpression(
                '/^[A-Z][A-Z0-9]{2}\|/',
                $segment,
                'Every segment must open with a three character segment id followed by the field separator'
            );
        }

        $msh = self::fields(self::onlySegment($segments, 'MSH'));
        $this->assertSame('ORM^O01', $msh[8], 'MSH-9 message type');
        $this->assertSame('2.3', $msh[11], 'MSH-12 HL7 version');
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testMshRoutingFieldsComeFromTheProcedureProvider(Hl7OrderGenerator $generator): void
    {
        $result = $generator->generate($this->orderId);
        $msh = self::fields(self::onlySegment(self::segments($result->hl7), 'MSH'));

        $this->assertSame('^~\\&', $msh[1], 'MSH-2 encoding characters');
        $this->assertSame($this->providerColumn('send_app_id'), $msh[2], 'MSH-3 sending application');
        $this->assertSame($this->providerColumn('send_fac_id'), $msh[3], 'MSH-4 sending facility');
        $this->assertSame($this->providerColumn('recv_app_id'), $msh[4], 'MSH-5 receiving application');
        $this->assertSame($this->providerColumn('recv_fac_id'), $msh[5], 'MSH-6 receiving facility');
    }

    /**
     * Each generator reads its routing identifiers from whichever
     * procedure_providers row the order points at, so pointing the order at a
     * different lab must move the whole MSH header with it.
     */
    #[Test]
    #[DataProvider('labFixtureNameProvider')]
    public function testRoutingFollowsTheLabTheOrderPointsAt(string $labFixtureName): void
    {
        $lab = $this->providerFixtures->getProviderByName($labFixtureName);
        $this->assertIsArray($lab, "Provider fixture '{$labFixtureName}' should be installed");
        $labId = $lab['ppid'];
        $this->assertIsInt($labId);

        QueryUtils::sqlStatementThrowException(
            'UPDATE procedure_order SET lab_id = ? WHERE procedure_order_id = ?',
            [$labId, $this->orderId]
        );

        foreach (Hl7OrderGenerator::cases() as $generator) {
            $hl7 = $generator->generate($this->orderId)->hl7;
            $msh = self::fields(self::onlySegment(self::segments($hl7), 'MSH'));
            $this->assertSame($this->providerColumn('send_fac_id'), $msh[3], "MSH-4 for {$generator->name}");
            $this->assertSame($this->providerColumn('recv_app_id'), $msh[4], "MSH-5 for {$generator->name}");
            $this->assertSame($this->providerColumn('recv_fac_id'), $msh[5], "MSH-6 for {$generator->name}");
        }
    }

    /**
     * @param list<string> $requiredSegments
     * @param list<string> $forbiddenSegments
     */
    #[Test]
    #[DataProvider('segmentExpectationProvider')]
    public function testGeneratorEmitsExpectedSegments(
        Hl7OrderGenerator $generator,
        array $requiredSegments,
        array $forbiddenSegments
    ): void {
        $segments = self::segments($generator->generate($this->orderId)->hl7);
        $segmentIds = array_map(static fn (string $segment): string => substr($segment, 0, 3), $segments);

        foreach ($requiredSegments as $required) {
            $this->assertContains($required, $segmentIds, "Missing required {$required} segment");
        }
        foreach ($forbiddenSegments as $forbidden) {
            $this->assertNotContains($forbidden, $segmentIds, "Unexpected {$forbidden} segment");
        }
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testPatientDemographicsAppearInPid(Hl7OrderGenerator $generator): void
    {
        $pid = self::onlySegment(self::segments($generator->generate($this->orderId)->hl7), 'PID');

        $this->assertStringContainsString(
            $this->patientColumn('lname') . '^' . $this->patientColumn('fname'),
            $pid,
            'PID-5 patient name'
        );
        $this->assertStringContainsString(
            str_replace('-', '', $this->patientColumn('DOB')),
            $pid,
            'PID-7 date of birth'
        );
        $this->assertStringContainsString($this->patientColumn('city'), $pid, 'PID-11 city');
        $this->assertStringContainsString($this->patientColumn('postal_code'), $pid, 'PID-11 postal code');
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testOrderingProviderAppearsInPatientVisitAndCommonOrder(Hl7OrderGenerator $generator): void
    {
        $segments = self::segments($generator->generate($this->orderId)->hl7);
        $expectedProvider = self::ORDERING_PROVIDER_NPI . '^' . $this->providerLastName();

        $pv1 = self::fields(self::onlySegment($segments, 'PV1'));
        $this->assertStringStartsWith($expectedProvider, $pv1[7], 'PV1-8 attending doctor');

        foreach (self::segmentsOfType($segments, 'ORC') as $orc) {
            $this->assertStringStartsWith($expectedProvider, self::fields($orc)[12], 'ORC-12 ordering provider');
        }

        foreach (self::segmentsOfType($segments, 'OBR') as $obr) {
            $this->assertStringContainsString($expectedProvider, $obr, 'OBR ordering provider');
        }
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testObservationRequestsCarryEveryOrderedProcedureCode(Hl7OrderGenerator $generator): void
    {
        $hl7 = $generator->generate($this->orderId)->hl7;
        $observationRequests = self::segmentsOfType(self::segments($hl7), 'OBR');

        $this->assertCount(
            count(self::PROCEDURE_CODES),
            $observationRequests,
            'One OBR per orderable procedure code'
        );

        $joined = implode(chr(self::CR), $observationRequests);
        foreach (self::PROCEDURE_CODES as $code => $name) {
            $this->assertStringContainsString($code . '^' . $name, $joined);
        }
    }

    #[Test]
    public function testPlacerOrderNumberFormatDiffersByGenerator(): void
    {
        $facility = $this->providerColumn('send_fac_id');
        $zeroPadded = str_pad((string) $this->orderId, 4, '0', STR_PAD_LEFT);

        $this->assertSame($zeroPadded, $this->placerOrderNumber(default_gen_hl7_order($this->orderId)));
        $this->assertSame(
            $facility . '-' . $zeroPadded,
            $this->placerOrderNumber(universal_gen_hl7_order($this->orderId))
        );
        $this->assertSame(
            $facility . '-' . $zeroPadded,
            $this->placerOrderNumber(quest_gen_hl7_order($this->orderId))
        );
        $this->assertSame((string) $this->orderId, $this->placerOrderNumber(labcorp_gen_hl7_order($this->orderId)));
    }

    #[Test]
    public function testOnlyLabCorpPopulatesRequisitionData(): void
    {
        $this->assertSame('', default_gen_hl7_order($this->orderId)->requisitionData);
        $this->assertSame('', universal_gen_hl7_order($this->orderId)->requisitionData);
        $this->assertSame('', quest_gen_hl7_order($this->orderId)->requisitionData);
        $this->assertNotSame('', labcorp_gen_hl7_order($this->orderId)->requisitionData);
    }

    #[Test]
    public function testLabCorpRequisitionDataIsAStructuredBarcodeRecord(): void
    {
        $requisition = labcorp_gen_hl7_order($this->orderId)->requisitionData;

        $this->assertSame(strtoupper($requisition), $requisition, 'The 2D barcode record is uppercased');
        $this->assertStringNotContainsString(chr(self::LF), $requisition);

        $records = self::segments($requisition);
        $this->assertSame(
            ['H', 'P', 'C', 'A', 'T', 'M', 'D', 'L', 'E'],
            array_map(static fn (string $record): string => substr($record, 0, 1), $records),
            'Record types and their order'
        );

        $byType = self::requisitionRecordsByType($requisition);

        $this->assertStringContainsString(
            strtoupper($this->patientColumn('lname')),
            $byType['P'],
            'P record carries the patient name'
        );
        $this->assertStringContainsString((string) $this->orderId, $byType['P'], 'P record carries the order id');

        foreach (array_keys(self::PROCEDURE_CODES) as $code) {
            $this->assertStringContainsString((string) $code, $byType['T'], 'T record carries the ordered tests');
        }

        $this->assertStringContainsString('E78.5', $byType['D'], 'D record carries the order diagnosis');
        $this->assertMatchesRegularExpression('/^L\|\d+\|$/', $byType['L'], 'L record carries the record length');
        $this->assertSame('E|0|', $byType['E']);
    }

    /**
     * An order carrying no diagnosis at all — neither `order_diagnosis` on the
     * order nor `diagnoses` on any of its codes — still has to produce a
     * requisition, with an empty diagnosis field on the D record.
     *
     * Regression test for #13546. The D and M record arrays were omitted from
     * the loop that seeds every other record array, so on this path `$D[1]` was
     * never created and trimming its trailing '^' separator passed null to
     * strlen() and substr(). PHP reports that as a deprecation rather than an
     * exception, so the requisition string itself was unaffected — the only
     * symptom was the diagnostics, which under `failOnDeprecation` take the
     * PHPUnit process exit code to 1 without marking any test failed. Assert on
     * the diagnostics directly so a regression is a red test rather than an
     * exit code nobody reads.
     */
    #[Test]
    public function testLabCorpRequisitionIsDiagnosticFreeForAnOrderWithNoDiagnoses(): void
    {
        $this->setOrderColumns(['order_diagnosis' => '']);
        QueryUtils::sqlStatementThrowException(
            'UPDATE procedure_order_code SET diagnoses = ? WHERE procedure_order_id = ?',
            ['', $this->orderId]
        );

        /** @var list<string> $diagnostics */
        $diagnostics = [];
        set_error_handler(
            static function (int $severity, string $message) use (&$diagnostics): bool {
                $diagnostics[] = $severity . ': ' . $message;
                return true;
            },
            E_WARNING | E_DEPRECATED
        );
        try {
            $requisition = labcorp_gen_hl7_order($this->orderId)->requisitionData;
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $diagnostics, 'Generating the requisition must raise no warnings or deprecations');

        $byType = self::requisitionRecordsByType($requisition);
        $this->assertSame('D|||', $byType['D'], 'The D record carries an empty diagnosis list');
        $this->assertSame('M||||||', $byType['M'], 'The M record is empty but still has all six of its fields');
    }

    #[Test]
    #[DataProvider('generatorProvider')]
    public function testUnknownOrderIdThrows(Hl7OrderGenerator $generator): void
    {
        $unknownOrderId = $this->orderId + 100000;

        $this->expectException(Hl7OrderGenerationException::class);
        $this->expectExceptionMessage((string) $unknownOrderId);
        $generator->generate($unknownOrderId);
    }

    #[Test]
    public function testLabCorpThrowsWhenFacilityAccountCodeIsMissing(): void
    {
        $this->setOrderColumns(['account' => '']);

        $this->expectException(Hl7OrderGenerationException::class);
        $this->expectExceptionMessage('facility location account code');
        labcorp_gen_hl7_order($this->orderId);
    }

    #[Test]
    #[DataProvider('insuranceBillingGeneratorProvider')]
    public function testInsuranceBilledOrderWithoutPayersThrows(Hl7OrderGenerator $generator): void
    {
        $this->setOrderColumns(['billing_type' => 'T']);

        $this->expectException(Hl7OrderGenerationException::class);
        $this->expectExceptionMessage('does not have any payers on record');
        $generator->generate($this->orderId);
    }

    #[Test]
    public function testDefaultGeneratorIgnoresBillingTypeAndOmitsInsuranceWithoutPayers(): void
    {
        $this->setOrderColumns(['billing_type' => 'T']);

        $segments = self::segments(default_gen_hl7_order($this->orderId)->hl7);
        $segmentIds = array_map(static fn (string $segment): string => substr($segment, 0, 3), $segments);

        $this->assertContains('MSH', $segmentIds);
        $this->assertNotContains('IN1', $segmentIds, 'The default generator emits IN1 only for payers on file');
    }

    /**
     * @return array<string, array{Hl7OrderGenerator}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function generatorProvider(): array
    {
        return [
            'default' => [Hl7OrderGenerator::DefaultLab],
            'universal' => [Hl7OrderGenerator::Universal],
            'labcorp' => [Hl7OrderGenerator::LabCorp],
            'quest' => [Hl7OrderGenerator::Quest],
        ];
    }

    /**
     * Partial names of the four procedure_providers fixtures, as accepted by
     * ProcedureProviderFixtureManager::getProviderByName().
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function labFixtureNameProvider(): array
    {
        return [
            'generic' => ['Generic Lab'],
            'ammon' => ['Ammon Lab'],
            'labcorp' => ['LabCorp'],
            'quest' => ['Quest'],
        ];
    }

    /**
     * Generators that refuse to build a message when the order is billed to
     * insurance but the patient has no payers on file.
     *
     * @return array<string, array{Hl7OrderGenerator}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function insuranceBillingGeneratorProvider(): array
    {
        return [
            'universal' => [Hl7OrderGenerator::Universal],
            'labcorp' => [Hl7OrderGenerator::LabCorp],
            'quest' => [Hl7OrderGenerator::Quest],
        ];
    }

    /**
     * Segments each generator must and must not emit for a self pay order with
     * no payers and no guarantor on file.
     *
     * @return array<string, array{Hl7OrderGenerator, list<string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function segmentExpectationProvider(): array
    {
        return [
            'default' => [
                Hl7OrderGenerator::DefaultLab,
                ['MSH', 'PID', 'NTE', 'PV1', 'GT1', 'ORC', 'OBR', 'DG1'],
                ['IN1'],
            ],
            'universal' => [
                Hl7OrderGenerator::Universal,
                ['MSH', 'PID', 'NTE', 'PV1', 'IN1', 'GT1', 'ORC', 'OBR', 'DG1'],
                [],
            ],
            'labcorp' => [
                Hl7OrderGenerator::LabCorp,
                ['MSH', 'PID', 'PV1', 'IN1', 'DG1', 'ZCI', 'ORC', 'OBR'],
                ['NTE', 'GT1'],
            ],
            'quest' => [
                Hl7OrderGenerator::Quest,
                ['MSH', 'PID', 'NTE', 'PV1', 'IN1', 'GT1', 'ORC', 'OBR', 'DG1', 'OBX'],
                [],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private static function segments(string $message): array
    {
        return array_values(array_filter(explode(chr(self::CR), $message), static fn (string $s): bool => $s !== ''));
    }

    /**
     * @return list<string>
     */
    private static function fields(string $segment): array
    {
        return explode('|', $segment);
    }

    /**
     * @param list<string> $segments
     * @return list<string>
     */
    private static function segmentsOfType(array $segments, string $segmentId): array
    {
        return array_values(array_filter(
            $segments,
            static fn (string $segment): bool => str_starts_with($segment, $segmentId . '|')
        ));
    }

    /**
     * Index the records of a LabCorp 2D barcode requisition by their leading
     * record-type letter.
     *
     * @return array<string, string>
     */
    private static function requisitionRecordsByType(string $requisition): array
    {
        $records = self::segments($requisition);
        return array_combine(
            array_map(static fn (string $record): string => substr($record, 0, 1), $records),
            $records
        );
    }

    /**
     * @param list<string> $segments
     */
    private static function onlySegment(array $segments, string $segmentId): string
    {
        $matches = self::segmentsOfType($segments, $segmentId);
        self::assertCount(1, $matches, "Expected exactly one {$segmentId} segment");
        return $matches[0];
    }

    /**
     * ORC-2 and OBR-2 both carry the placer order number, and each generator
     * formats it differently.
     */
    private function placerOrderNumber(Hl7OrderResult $result): string
    {
        $segments = self::segments($result->hl7);
        $fromCommonOrder = self::fields(self::segmentsOfType($segments, 'ORC')[0])[2];
        foreach (self::segmentsOfType($segments, 'OBR') as $obr) {
            $this->assertSame($fromCommonOrder, self::fields($obr)[2], 'OBR-2 must match ORC-2');
        }
        return $fromCommonOrder;
    }

    /**
     * Add the procedure_type rows the ordered codes refer to. The default
     * generator joins procedure_type on the procedure name to find the
     * specimen, and the LabCorp generator looks it up by procedure code, so
     * without these rows neither emits an OBR the way production does.
     */
    private function installProcedureTypes(): void
    {
        foreach (self::PROCEDURE_CODES as $code => $name) {
            QueryUtils::sqlStatementThrowException(
                <<<'SQL'
                INSERT INTO procedure_type
                SET name = ?, procedure_code = ?, procedure_type = 'ord', specimen = 'BLOOD', description = ?
                SQL,
                [$name, (string) $code, self::PROCEDURE_TYPE_MARKER]
            );
        }
    }

    /**
     * Add the encounter vitals the LabCorp generator reads for the ZCI segment
     * and the height/weight fields of the 2D barcode requisition. Without them
     * that generator dereferences a `false` result row.
     */
    private function installVitals(): void
    {
        $encounter = QueryUtils::fetchSingleValue(
            <<<'SQL'
            SELECT f.encounter
            FROM forms f
            WHERE f.formdir = 'procedure_order' AND f.form_id = ?
            SQL,
            'encounter',
            [$this->orderId]
        );
        $this->assertIsInt($encounter, 'Order fixture must be linked to an encounter');

        $patientId = QueryUtils::fetchSingleValue(
            'SELECT pid FROM forms WHERE formdir = ? AND form_id = ?',
            'pid',
            ['procedure_order', $this->orderId]
        );
        $this->assertIsInt($patientId);

        $vitalsId = QueryUtils::sqlInsert(
            <<<'SQL'
            INSERT INTO form_vitals
            SET pid = ?, activity = 1, date = NOW(), weight = ?, height = ?, bps = ?, bpd = ?,
                waist_circ = ?, note = ?
            SQL,
            [$patientId, '180.500000', '70.000000', '120', '80', '34.000000', self::VITALS_MARKER]
        );

        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO forms
            SET date = NOW(), encounter = ?, form_name = 'Vitals', form_id = ?, pid = ?,
                user = 'admin', groupname = 'Default', authorized = 1, formdir = 'vitals'
            SQL,
            [$encounter, $vitalsId, $patientId]
        );
    }

    /**
     * The shared user fixture has no NPI, which leaves the provider identifier
     * component of PV1, ORC and OBR empty. Give the ordering provider one for
     * the duration of the test so those fields can be asserted on.
     */
    private function stampOrderingProviderNpi(): void
    {
        $providerId = QueryUtils::fetchSingleValue(
            'SELECT provider_id FROM procedure_order WHERE procedure_order_id = ?',
            'provider_id',
            [$this->orderId]
        );
        $this->assertIsInt($providerId, 'Order fixture must reference an ordering provider');
        $this->orderingProviderId = $providerId;

        $npi = QueryUtils::fetchSingleValue('SELECT npi FROM users WHERE id = ?', 'npi', [$providerId]);
        $this->originalOrderingProviderNpi = is_string($npi) ? $npi : null;

        QueryUtils::sqlStatementThrowException(
            'UPDATE users SET npi = ? WHERE id = ?',
            [self::ORDERING_PROVIDER_NPI, $providerId]
        );
    }

    private function restoreOrderingProviderNpi(): void
    {
        if ($this->orderingProviderId === 0) {
            return;
        }
        QueryUtils::sqlStatementThrowException(
            'UPDATE users SET npi = ? WHERE id = ?',
            [$this->originalOrderingProviderNpi, $this->orderingProviderId]
        );
    }

    /**
     * @param array<string, string> $columns
     */
    private function setOrderColumns(array $columns): void
    {
        foreach ($columns as $column => $value) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE procedure_order SET `' . $column . '` = ? WHERE procedure_order_id = ?',
                [$value, $this->orderId]
            );
        }
    }

    private function providerLastName(): string
    {
        $lastName = QueryUtils::fetchSingleValue(
            'SELECT lname FROM users WHERE id = ?',
            'lname',
            [$this->orderingProviderId]
        );
        $this->assertIsString($lastName);
        return $lastName;
    }

    private function patientColumn(string $column): string
    {
        $value = QueryUtils::fetchSingleValue(
            <<<'SQL'
            SELECT pd.fname, pd.lname, pd.mname, pd.DOB, pd.sex, pd.city, pd.state, pd.postal_code
            FROM procedure_order po
            JOIN forms f ON f.formdir = 'procedure_order' AND f.form_id = po.procedure_order_id
            JOIN patient_data pd ON pd.pid = f.pid
            WHERE po.procedure_order_id = ?
            SQL,
            $column,
            [$this->orderId]
        );
        $this->assertIsString($value, "patient_data.{$column} should be a string");
        return $value;
    }

    private function providerColumn(string $column): string
    {
        $value = QueryUtils::fetchSingleValue(
            <<<'SQL'
            SELECT pp.send_app_id, pp.send_fac_id, pp.recv_app_id, pp.recv_fac_id, pp.name
            FROM procedure_order po
            JOIN procedure_providers pp ON pp.ppid = po.lab_id
            WHERE po.procedure_order_id = ?
            SQL,
            $column,
            [$this->orderId]
        );
        $this->assertIsString($value, "procedure_providers.{$column} should be a string");
        return $value;
    }
}
