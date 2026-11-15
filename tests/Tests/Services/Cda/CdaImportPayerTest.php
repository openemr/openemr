<?php

/**
 * A QRDA Category I import's payer (the Patient Characteristic Payer entry: a Source of Payment
 * Typology code and the coverage's dates) becomes the patient's primary policy, holding only what
 * the document says: the payer and the dates, with no policy number, plan name or subscriber.
 * Before, InsertPayers() saved none, since the coverage validator refused an insert with no
 * subscriber and the import only logged that.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Cda;

use Carecoordination\Model\CarecoordinationTable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRCoverage;
use OpenEMR\Services\Cda\CdaTemplateImportDispose;
use OpenEMR\Services\Cda\CdaTemplateParse;
use OpenEMR\Services\Cda\XmlExtended;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCoverageService;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\Qdm\QdmRequestOne;
use OpenEMR\Services\Qdm\Services\PatientService as QdmPatientService;
use OpenEMR\Validators\CoverageValidator;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CdaImportPayerTest extends TestCase
{
    // A synthetic patient, and the Source of Payment Typology code 9999 (Unavailable / Unknown),
    // which no insurance company in a test database carries. Every row made here is removed.
    private const PID = 990652;
    private const SOP_CODE = '9999';
    private const COMPANY_NAME = 'QRDA Insurance Company Payer ' . self::SOP_CODE;

    // The columns a document import leaves at their defaults: it carries none of them.
    private const NOT_IN_THE_DOCUMENT = [
        'plan_name', 'policy_number', 'group_number', 'subscriber_lname', 'subscriber_mname', 'subscriber_fname',
        'subscriber_relationship', 'subscriber_ss', 'subscriber_DOB', 'subscriber_street', 'subscriber_postal_code',
        'subscriber_city', 'subscriber_state', 'subscriber_country', 'subscriber_phone', 'subscriber_employer',
        'subscriber_employer_street', 'subscriber_employer_postal_code', 'subscriber_employer_state',
        'subscriber_employer_country', 'subscriber_employer_city', 'copay', 'subscriber_sex',
    ];

    private const DATA_ABSENT_REASON = [
        'valueCode' => 'unknown',
        'url' => 'http://hl7.org/fhir/StructureDefinition/data-absent-reason',
    ];

    private CdaTemplateImportDispose $dispose;
    private CarecoordinationTable&MockObject $table;
    private int $lastCompanyBefore;

    protected function setUp(): void
    {
        $this->assertSame(0, $this->rowsLeft(), 'no rows for the synthetic patient or company before the test');
        $this->lastCompanyBefore = self::number(QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(`id`), 0) AS n FROM `insurance_companies`', 'n'));
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_data` (`pid`, `pubpid`, `fname`, `lname`, `DOB`, `sex`, `street`, `city`, `state`, `postal_code`, `date`)'
            . ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
            [self::PID, 'qrda-payer-' . self::PID, 'Pia', 'Payertest', '1960-05-01', 'Female', '1 Main St', 'Barre', 'VT', '05641']
        );
        $this->dispose = new CdaTemplateImportDispose();
        $this->table = $this->createMock(CarecoordinationTable::class);
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_data' AND `uuid` IN (SELECT `uuid` FROM `insurance_data` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `insurance_data` WHERE `pid` = ?', [self::PID]);
        // The placeholder companies the import made during the test: this test's code's, and on
        // master's code one for an entry without a code.
        $made = "SELECT `id` FROM `insurance_companies` WHERE `id` > ? AND `name` LIKE 'QRDA Insurance Company Payer%'";
        QueryUtils::sqlStatementThrowException("DELETE FROM `addresses` WHERE `foreign_id` IN ($made)", [$this->lastCompanyBefore]);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_companies' AND `uuid` IN"
            . " (SELECT `uuid` FROM `insurance_companies` WHERE `id` IN ($made))",
            [$this->lastCompanyBefore]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `insurance_companies` WHERE `id` > ? AND `name` LIKE 'QRDA Insurance Company Payer%'",
            [$this->lastCompanyBefore]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `uuid_registry` WHERE `table_name` = 'patient_data' AND `uuid` IN (SELECT `uuid` FROM `patient_data` WHERE `pid` = ?)",
            [self::PID]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_data` WHERE `pid` = ?', [self::PID]);
        $this->assertSame(0, $this->rowsLeft(), 'the synthetic rows are removed');
    }

    public function testAnImportedPayerIsSavedAsThePrimaryPolicy(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);

        $this->assertSame(
            [['type' => 'primary', 'date' => '2024-01-15', 'cqm_sop' => self::SOP_CODE]],
            $this->policies(),
            "the document's payer is the patient's primary policy, from the payer's start date"
        );
    }

    public function testACypressDocumentsPayerReachesTheEcqmPatient(): void
    {
        $document = file_get_contents(__DIR__ . '/../../ECQM/test_patient_qrda/BH Adult_1.xml');
        $this->assertIsString($document);
        $xml = (new XmlExtended())->fromString($document);
        $this->assertIsArray($xml);
        // The Patient Data section, cut to its payer entry: the other entries' parsing isn't this test's.
        $entries = self::at($xml, 'component', 'structuredBody', 'component', 2, 'section', 'entry');
        $this->assertIsArray($entries);
        $payerEntries = array_values(array_filter(
            $entries,
            fn($entry): bool => self::at($entry, 'observation', 'templateId', 'root') === '2.16.840.1.113883.10.20.24.3.55'
        ));
        $this->assertCount(1, $payerEntries);
        $parsed = (new CdaTemplateParse($document))->parseQRDAPatientDataSection(['section' => ['entry' => $payerEntries]]);
        $payers = self::at($parsed, 'field_name_value_array', 'payer');
        $this->assertIsArray($payers);
        // <effectiveTime><low value='19850122163900'/><high nullFlavor='UNK'/></effectiveTime>, code 1 (Medicare)
        $this->assertSame([1 => ['status' => 'completed', 'code' => '1', 'low_date' => '19850122163900', 'high_date' => null]], $payers);

        // The document's entry with this test's own payer code, so the company it makes is this test's to remove.
        $payers[1]['code'] = self::SOP_CODE;
        $this->dispose->InsertPayers($payers, self::PID, $this->table, 0);

        $this->assertSame([['type' => 'primary', 'date' => '1985-01-22', 'cqm_sop' => self::SOP_CODE]], $this->policies());
        $this->assertNull($this->storedPolicy()['date_end'], 'an unknown end date (nullFlavor) is none');
        $qdmPatients = (new QdmPatientService(new QdmRequestOne((string) self::PID), new CodeTypesService()))->executeQuery();
        $this->assertInstanceOf(\ADORecordSet::class, $qdmPatients);
        $patient = QueryUtils::fetchArrayFromResultSet($qdmPatients);
        $this->assertIsArray($patient);
        $this->assertSame(
            ['payer_eff_date' => '1985-01-22', 'payer_code' => (int) self::SOP_CODE],
            ['payer_eff_date' => $patient['payer_eff_date'] ?? null, 'payer_code' => self::number($patient['payer_code'] ?? null)],
            "the eCQM patient's payer (Patient Characteristic Payer) is the document's"
        );
    }

    public function testTheImportedPolicyHoldsOnlyWhatTheDocumentSays(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115083000-0500', '20241231')], self::PID, $this->table, 0);

        $row = $this->storedPolicy();
        $this->assertIsNumeric($row['provider']);
        $this->assertSame($this->companyId(), (string) $row['provider'], "the provider is the company carrying the document's payer code");
        $this->assertSame('2024-01-15', $row['date']);
        $this->assertSame('2024-12-31', $row['date_end'], "the document's end date");
        foreach (self::NOT_IN_THE_DOCUMENT as $column) {
            $this->assertNull($row[$column], "$column isn't in the document, so none is stored");
        }
        $this->assertSame('TRUE', $row['accept_assignment'], 'the column default');
        $this->assertSame('', $row['policy_type'], 'the column default');
    }

    public function testItsFhirCoverageNamesThePayerAndPeriodAndSaysWhatIsUnknown(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);
        $uuid = UuidRegistry::uuidToString($this->storedPolicy()['uuid']);

        $coverage = $this->coverageRead($uuid);

        $this->assertSame('active', $coverage['status'] ?? null);
        $this->assertSame(['reference' => 'Patient/' . $this->patientUuid()], $coverage['beneficiary'] ?? null);
        $this->assertSame([['reference' => 'Organization/' . $this->companyUuid()]], $coverage['payor'] ?? null);
        $period = $coverage['period'] ?? null;
        $this->assertIsArray($period);
        $start = $period['start'] ?? null;
        $this->assertIsString($start);
        $this->assertStringStartsWith('2024-01-15T', $start);
        $this->assertArrayNotHasKey('end', $period);
        $this->assertArrayNotHasKey('subscriberId', $coverage, 'no subscriber id: the document has no member number');
        $this->assertArrayNotHasKey('class', $coverage, 'no plan or group');
        $this->assertArrayNotHasKey('dependent', $coverage);

        // US Core Coverage: a member id or a subscriber id (us-core-15) and a relationship (1..1)
        // are required; with none stored, each says it's unknown.
        $this->assertSame(
            [[
                'extension' => [self::DATA_ABSENT_REASON],
                'type' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v2-0203', 'code' => 'MB', 'display' => 'Member Number']]],
            ]],
            $coverage['identifier'] ?? null
        );
        $this->assertSame(
            ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/data-absent-reason', 'code' => 'unknown', 'display' => 'Unknown']]],
            $coverage['relationship'] ?? null
        );
    }

    /**
     * @param array<mixed> $relationship the relationship the read is expected to carry
     */
    #[DataProvider('storedRelationships')]
    public function testAStoredRelationshipReadsAsItIsAndABlankOneAsUnknown(?string $stored, array $relationship, ?string $dependent): void
    {
        // A policy with a payer (FHIR serves only those), its relationship as stored.
        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `insurance_data` SET `subscriber_relationship` = ? WHERE `pid` = ?',
            [$stored, self::PID]
        );

        $coverage = $this->coverageRead(UuidRegistry::uuidToString($this->storedPolicy()['uuid']));

        $this->assertSame($relationship, $coverage['relationship'] ?? null);
        $this->assertSame($dependent, $coverage['dependent'] ?? null);
    }

    /**
     * @return array<string, array{?string, array<mixed>, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedRelationships(): array
    {
        $unknown = ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/data-absent-reason', 'code' => 'unknown', 'display' => 'Unknown']]];
        $relationship = 'http://terminology.hl7.org/CodeSystem/subscriber-relationship';
        return [
            'none' => [null, $unknown, null],
            'blank, as the legacy forms and the XML import store none' => ['', $unknown, null],
            'self' => ['self', ['coding' => [['system' => $relationship, 'code' => 'self', 'display' => 'Self']]], null],
            'spouse' => ['spouse', ['coding' => [['system' => $relationship, 'code' => 'spouse', 'display' => 'Spouse']]], '1'],
        ];
    }

    /**
     * @param array<string, string> $entered the details entered on the policy
     */
    #[DataProvider('enteredPolicies')]
    public function testImportingAgainLeavesAPolicyEnteredInOpenEmr(array $entered): void
    {
        // A primary policy entered in OpenEMR before the document is imported.
        $columns = array_keys($entered);
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`uuid`, `pid`, `type`, `provider`, `date`, `date_end`, `" . implode('`, `', $columns) . '`)'
            . " VALUES (?, ?, 'primary', 0, '2023-01-01', '2026-06-30'" . str_repeat(', ?', count($columns)) . ')',
            [UuidRegistry::getRegistryForTable('insurance_data')->createUuid(), self::PID, ...array_values($entered)]
        );
        $stored = $this->storedPolicy();

        $this->dispose->InsertPayers([1 => $this->payer('19850122163900', '20201231')], self::PID, $this->table, 0);

        $this->assertSame(
            $stored,
            $this->storedPolicy(),
            "a document's payer doesn't replace a policy entered in OpenEMR: its payer, dates and details stay"
        );
        $this->assertSame(0, $this->companiesMade(), 'no placeholder company for a policy not saved');
    }

    /**
     * @return array<string, array{array<string, string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function enteredPolicies(): array
    {
        return [
            'a policy number and a subscriber' => [[
                'policy_number' => 'STAFF-1', 'plan_name' => 'Staff Plan', 'subscriber_lname' => 'Payertest', 'subscriber_fname' => 'Sam',
                'subscriber_relationship' => 'spouse', 'subscriber_DOB' => '1958-02-02', 'subscriber_sex' => 'Male',
            ]],
            'a policy number only' => [['policy_number' => 'STAFF-1']],
            'a subscriber only' => [['subscriber_lname' => 'Payertest']],
            'a group number only' => [['group_number' => 'GRP-7']],
            'the relationship only' => [['subscriber_relationship' => 'self']],
        ];
    }

    public function testImportingAgainUpdatesTheImportedPolicyToTheDocumentsPeriod(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115', '20241231')], self::PID, $this->table, 0);
        $first = $this->storedPolicy();
        $this->assertSame(['2024-01-15', '2024-12-31'], [$first['date'], $first['date_end']]);

        $this->dispose->InsertPayers([1 => $this->payer('20240115', '20241231')], self::PID, $this->table, 0);
        $this->assertSame($first, $this->storedPolicy(), 'the same document again changes nothing');

        // A later document whose coverage has no end yet: its period replaces the earlier one.
        $this->dispose->InsertPayers([1 => $this->payer('20250301')], self::PID, $this->table, 0);
        $this->assertSame(
            array_replace($first, ['date' => '2025-03-01', 'date_end' => null]),
            $this->storedPolicy(),
            "the later document's period, on the same policy; the rest as stored"
        );

        // The first document again: its period, not a mix of the two.
        $this->dispose->InsertPayers([1 => $this->payer('20240115', '20241231')], self::PID, $this->table, 0);
        $this->assertSame($first, $this->storedPolicy(), 'back to the first document\'s period');
    }

    #[DataProvider('payerCodes')]
    public function testAnEntryWithoutAPayerCodeSavesNothing(?string $code): void
    {
        $this->dispose->InsertPayers([1 => ['code' => $code] + $this->payer('20240115')], self::PID, $this->table, 0);

        $this->assertSame([], $this->policies(), 'no policy');
        $this->assertSame(0, $this->companiesMade(), 'no company (master made a placeholder with no payer code for it)');
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function payerCodes(): array
    {
        return [
            'none' => [null],
            'a nullFlavor, which the import stages as blank' => [''],
            'not a code' => ['UNK'],
            'a code with a trailing newline' => ["9999\n"],
            'longer than the INT column holds' => ['99999999999'],
        ];
    }

    #[DataProvider('documentDates')]
    public function testThePolicyStartsOnTheDateTheDocumentWrites(string $lowDate, ?string $stored, int $companiesMade): void
    {
        $this->dispose->InsertPayers([1 => $this->payer($lowDate)], self::PID, $this->table, 0);

        $this->assertSame(
            $stored === null ? [] : [['type' => 'primary', 'date' => $stored, 'cqm_sop' => self::SOP_CODE]],
            $this->policies()
        );
        $this->assertSame($companiesMade, $this->companiesMade());
    }

    /**
     * @return array<string, array{string, ?string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function documentDates(): array
    {
        return [
            'a date' => ['20240115', '2024-01-15', 1],
            'a timestamp with an offset' => ['20240115083000-0500', '2024-01-15', 1],
            // the day the document writes, not that moment in the server's time zone (2024-01-14 in UTC)
            'an offset ahead of the server' => ['20240115000000+1400', '2024-01-15', 1],
            'fractional seconds' => ['20240115083000.123-0500', '2024-01-15', 1],
            'a year only: no start date, no policy, no company' => ['2024', null, 0],
            'a month only: no start date, no policy, no company' => ['202401', null, 0],
            'not a date: no policy, no company' => ['unknown', null, 0],
            // eight digits, so the payer's company is made before the validator refuses the date
            'no such day: refused, no policy' => ['20240230', null, 1],
        ];
    }

    #[DataProvider('documentEndDates')]
    public function testThePolicyEndsOnTheDayTheDocumentGives(?string $highDate, ?string $stored, int $policies): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115', $highDate)], self::PID, $this->table, 0);

        $this->assertCount($policies, $this->policies());
        $this->assertSame($policies, $this->companiesMade(), 'a company only for a policy saved');
        if ($policies === 1) {
            $this->assertSame($stored, $this->storedPolicy()['date_end']);
        }
    }

    /**
     * @return array<string, array{?string, ?string, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function documentEndDates(): array
    {
        return [
            'a day' => ['20241231235959-0500', '2024-12-31', 1],
            'none' => [null, null, 1],
            'a nullFlavor, which the import stages as blank: none' => ['', null, 1],
            'a month: no policy' => ['202412', null, 0],
            'a year: no policy' => ['2024', null, 0],
        ];
    }

    public function testAnEndBeforeTheStartIsRefused(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115', '20231231')], self::PID, $this->table, 0);

        $this->assertSame([], $this->policies());
    }

    public function testTheImportContextAsksOnlyForWhatADocumentCarries(): void
    {
        $validator = new CoverageValidator();
        $payerOnly = ['pid' => self::PID, 'type' => 'primary', 'provider' => 7, 'date' => '2024-01-15', 'date_end' => null];

        $this->assertTrue($validator->validate($payerOnly, CoverageValidator::DATABASE_IMPORT_CONTEXT)->isValid());
        foreach (['pid', 'type', 'provider', 'date'] as $field) {
            $without = $payerOnly;
            unset($without[$field]);
            $this->assertSame(
                [$field],
                array_keys($this->messages($validator->validate($without, CoverageValidator::DATABASE_IMPORT_CONTEXT))),
                "$field is required"
            );
        }
        $this->assertArrayHasKey(
            'date',
            $this->messages($validator->validate(['date' => '20240115'] + $payerOnly, CoverageValidator::DATABASE_IMPORT_CONTEXT))
        );
        $this->assertArrayHasKey(
            'type',
            $this->messages($validator->validate(['type' => 'quaternary'] + $payerOnly, CoverageValidator::DATABASE_IMPORT_CONTEXT))
        );
        $this->assertArrayHasKey(
            'uuid',
            $this->messages($validator->validate(['uuid' => '550e8400-e29b-41d4-a716-446655440000'] + $payerOnly, CoverageValidator::DATABASE_IMPORT_CONTEXT)),
            'a uuid names a stored policy'
        );
        $this->assertSame(
            ['uuid' => 'Coverage UUID must be provided'],
            $this->messages((new InsuranceService())->updateImported($payerOnly)),
            'an update names the policy'
        );
    }

    public function testRestAndFhirInsertsStillAskForTheSubscriber(): void
    {
        $this->dispose->InsertPayers([1 => $this->payer('20240115')], self::PID, $this->table, 0);
        $payerOnly = ['pid' => self::PID, 'type' => 'secondary', 'provider' => $this->companyId(), 'date' => '2024-02-01', 'date_end' => null];

        // the REST API's insert, and the validator's insert context it uses
        $restInsert = (new InsuranceService())->insert($payerOnly);
        foreach (['policy_number', 'subscriber_lname', 'subscriber_fname', 'subscriber_relationship', 'subscriber_DOB', 'subscriber_street'] as $field) {
            $this->assertArrayHasKey($field, $this->messages($restInsert), "the REST insert still requires $field");
        }

        // FHIR Coverage's create, with no relationship (so nothing is filled in from the patient)
        $fhirCoverageService = new FhirCoverageService();
        $fhirCoverageService->setLogger($this->createMock(LoggerInterface::class));
        $fhirInsert = $fhirCoverageService->insert(new FHIRCoverage([
            'status' => 'active',
            'subscriberId' => 'MEMBER-1',
            'order' => 2,
            'beneficiary' => ['reference' => 'Patient/' . $this->patientUuid()],
            'payor' => [['reference' => 'Organization/' . $this->companyUuid()]],
            'period' => ['start' => '2024-02-01'],
        ]));
        $this->assertArrayHasKey('subscriber_lname', $this->messages($fhirInsert), 'a FHIR create still requires the subscriber');

        $this->assertCount(1, $this->policies(), 'neither saved a policy');
    }

    /**
     * A payer as the import hands it to InsertPayers(): the Source of Payment Typology code, the
     * entry's status and its effectiveTime as written in the document.
     *
     * @return array{code: string, status: string, low_date: string, high_date: ?string}
     */
    private function payer(string $lowDate, ?string $highDate = null): array
    {
        return ['code' => self::SOP_CODE, 'status' => 'active', 'low_date' => $lowDate, 'high_date' => $highDate];
    }

    /**
     * @return list<array<mixed>>
     */
    private function policies(): array
    {
        return QueryUtils::fetchRecords(
            // cqm_sop is an INT column: read as text so the value compares the same with or without bound values
            'SELECT `i`.`type`, `i`.`date`, CAST(`c`.`cqm_sop` AS CHAR) AS `cqm_sop` FROM `insurance_data` AS `i`'
            . ' JOIN `insurance_companies` AS `c` ON `c`.`id` = `i`.`provider` WHERE `i`.`pid` = ? ORDER BY `i`.`id`',
            [self::PID]
        );
    }

    /**
     * The patient's one policy, every column.
     *
     * @return array<mixed>
     */
    private function storedPolicy(): array
    {
        $rows = QueryUtils::fetchRecords('SELECT * FROM `insurance_data` WHERE `pid` = ?', [self::PID]);
        $this->assertCount(1, $rows);
        return $rows[0];
    }

    /**
     * The FHIR Coverage read of a stored policy, as JSON.
     *
     * @return array<mixed>
     */
    private function coverageRead(string $uuid): array
    {
        $fhirCoverageService = new FhirCoverageService();
        $fhirCoverageService->setLogger($this->createMock(LoggerInterface::class));
        $read = $fhirCoverageService->getOne($uuid);
        $this->assertTrue($read->isValid());
        $data = $read->getData();
        $this->assertIsArray($data);
        $resource = $data[0] ?? null;
        $this->assertInstanceOf(FHIRCoverage::class, $resource);
        $coverage = json_decode((string) json_encode($resource), true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($coverage);
        return $coverage;
    }

    /**
     * The placeholder companies the import made during the test.
     */
    private function companiesMade(): int
    {
        return self::number(QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM `insurance_companies` WHERE `id` > ? AND `name` LIKE 'QRDA Insurance Company Payer%'",
            'n',
            [$this->lastCompanyBefore]
        ));
    }

    private function companyId(): string
    {
        $id = QueryUtils::fetchSingleValue('SELECT `id` FROM `insurance_companies` WHERE `cqm_sop` = ?', 'id', [self::SOP_CODE]);
        $this->assertIsNumeric($id);
        return (string) $id;
    }

    private function companyUuid(): string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT `uuid` FROM `insurance_companies` WHERE `cqm_sop` = ?', 'uuid', [self::SOP_CODE]);
        $this->assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }

    private function patientUuid(): string
    {
        $uuid = QueryUtils::fetchSingleValue('SELECT `uuid` FROM `patient_data` WHERE `pid` = ?', 'uuid', [self::PID]);
        $this->assertIsString($uuid);
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * The value at a path of keys in nested arrays, or null where the path leaves them.
     */
    private static function at(mixed $value, string|int ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($value)) {
                return null;
            }
            $value = $value[$key] ?? null;
        }
        return $value;
    }

    /**
     * @return array<mixed>
     */
    private function messages(ProcessingResult $result): array
    {
        $this->assertFalse($result->isValid());
        $messages = $result->getValidationMessages();
        $this->assertIsArray($messages);
        return $messages;
    }

    private function rowsLeft(): int
    {
        $count = 0;
        foreach (
            [
                'SELECT COUNT(*) AS n FROM `patient_data` WHERE `pid` = ?' => [self::PID],
                'SELECT COUNT(*) AS n FROM `insurance_data` WHERE `pid` = ?' => [self::PID],
                'SELECT COUNT(*) AS n FROM `insurance_companies` WHERE `name` = ? OR `cqm_sop` = ?' => [self::COMPANY_NAME, self::SOP_CODE],
            ] as $sql => $binds
        ) {
            $count += self::number(QueryUtils::fetchSingleValue($sql, 'n', $binds));
        }
        return $count;
    }

    private static function number(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
