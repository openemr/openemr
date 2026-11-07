<?php

/**
 * FHIR Observation API (HTTP write) tests for laboratory results (#14343).
 *
 * The client's own sequence through OAuth: create the order as a ServiceRequest, post a
 * result against it, read it back, change it with PUT, and get a 400 naming the element
 * when the result doesn't fit the order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ObservationLaboratoryFhirWriteApiTest extends TestCase
{
    private const OBSERVATION_URL = '/apis/default/fhir/Observation';
    private const SERVICE_REQUEST_URL = '/apis/default/fhir/ServiceRequest';
    private const GLUCOSE = '2345-7';

    private ApiTestClient $testClient;
    private FixtureManager $fixtureManager;
    private string $patientUuid;
    private string $orderUuid;
    /** @var array<string, int> */
    private array $countsBefore;

    protected function setUp(): void
    {
        $this->countsBefore = self::counts();
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthTokenOrFail(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $firstPatient = $this->fixtureManager->getPatientFixtures()[0];
        $this->assertIsArray($firstPatient);
        $patientRecord = QueryUtils::querySingleRow('SELECT uuid FROM patient_data WHERE pubpid = ?', [$firstPatient['pubpid']]);
        $this->assertIsArray($patientRecord);
        $this->patientUuid = UuidRegistry::uuidToString($patientRecord['uuid']);

        // The order comes first, through the API, as #14343 has the client do it.
        $order = $this->testClient->post(self::SERVICE_REQUEST_URL, [
            'resourceType' => 'ServiceRequest',
            'status' => 'active',
            'intent' => 'order',
            'category' => [['coding' => [['system' => 'http://snomed.info/sct', 'code' => '108252007', 'display' => 'Laboratory procedure']]]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::GLUCOSE, 'display' => 'test-fixture Glucose']]],
            'subject' => ['reference' => 'Patient/' . $this->patientUuid],
            'authoredOn' => '2026-03-04T08:00:00+00:00',
        ]);
        $orderBody = $order->getBody()->getContents();
        $this->assertSame(Response::HTTP_CREATED, $order->getStatusCode(), 'ServiceRequest POST (setup). Body: ' . $orderBody);
        $created = json_decode($orderBody, true);
        $this->assertIsArray($created);
        $this->assertIsString($created['uuid'] ?? null);
        $this->orderUuid = $created['uuid'];
    }

    protected function tearDown(): void
    {
        if (isset($this->fixtureManager)) {
            // The results and reports on this run's orders, then the orders and the patients.
            $reportIds = QueryUtils::fetchTableColumn(
                'SELECT rep.procedure_report_id FROM procedure_report rep'
                . ' JOIN procedure_order po ON po.procedure_order_id = rep.procedure_order_id'
                . ' JOIN patient_data pd ON pd.pid = po.patient_id WHERE pd.pubpid LIKE ?',
                'procedure_report_id',
                ['test-fixture%']
            );
            foreach ($reportIds as $reportId) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'procedure_result' AND uuid IN (SELECT uuid FROM procedure_result WHERE procedure_report_id = ?)",
                    [$reportId]
                );
                QueryUtils::sqlStatementThrowException('DELETE FROM procedure_result WHERE procedure_report_id = ?', [$reportId]);
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM uuid_registry WHERE table_name = 'procedure_report' AND uuid IN (SELECT uuid FROM procedure_report WHERE procedure_report_id = ?)",
                    [$reportId]
                );
                QueryUtils::sqlStatementThrowException('DELETE FROM procedure_report WHERE procedure_report_id = ?', [$reportId]);
            }
            $this->fixtureManager->removeServiceRequestFixtures();
            $this->fixtureManager->removePatientFixtures();
        }
        try {
            $this->testClient->cleanupRevokeAuth();
        } finally {
            $this->testClient->cleanupClient();
        }
        $this->assertSame($this->countsBefore, self::counts(), 'the test leaves no rows behind');
    }

    /**
     * @return array<string, int>
     */
    private static function counts(): array
    {
        $counts = [];
        foreach (['procedure_order', 'procedure_order_code', 'procedure_report', 'procedure_result'] as $table) {
            $value = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM `$table`", 'n');
            $counts[$table] = is_numeric($value) ? (int) $value : -1;
        }
        $value = QueryUtils::fetchSingleValue(
            "SELECT COUNT(*) AS n FROM uuid_registry WHERE table_name IN ('procedure_order', 'procedure_report', 'procedure_result')",
            'n'
        );
        $counts['uuid_registry'] = is_numeric($value) ? (int) $value : -1;
        return $counts;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function labResult(array $overrides = []): array
    {
        return array_merge([
            'resourceType' => 'Observation',
            'status' => 'final',
            'category' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category', 'code' => 'laboratory']]]],
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => self::GLUCOSE, 'display' => 'Glucose']]],
            'subject' => ['reference' => 'Patient/' . $this->patientUuid],
            'basedOn' => [['reference' => 'ServiceRequest/' . $this->orderUuid]],
            'effectiveDateTime' => '2026-03-04T09:30:00+00:00',
            'valueQuantity' => ['value' => 5.2, 'unit' => 'mmol/L', 'system' => 'http://unitsofmeasure.org', 'code' => 'mmol/L'],
            'referenceRange' => [['low' => ['value' => 3.9], 'high' => ['value' => 5.5]]],
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(string $body): array
    {
        $decoded = json_decode($body, true);
        self::assertIsArray($decoded, 'Expected a JSON object. Body: ' . $body);
        $keyed = [];
        foreach ($decoded as $key => $value) {
            $keyed[(string) $key] = $value;
        }
        return $keyed;
    }

    /**
     * Walks a decoded FHIR body by keys, returning null where a step is missing.
     */
    private static function at(mixed $data, string|int ...$path): mixed
    {
        foreach ($path as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return null;
            }
            $data = $data[$key];
        }
        return $data;
    }

    public function testCreateReadBackAndUpdate(): void
    {
        $create = $this->testClient->post(self::OBSERVATION_URL, $this->labResult());
        $createBody = $create->getBody()->getContents();
        $this->assertSame(Response::HTTP_CREATED, $create->getStatusCode(), 'POST should return 201. Body: ' . $createBody);
        $id = self::decode($createBody)['uuid'] ?? null;
        $this->assertIsString($id);

        $read = $this->testClient->getOne(self::OBSERVATION_URL, $id);
        $readBody = $read->getBody()->getContents();
        $this->assertSame(Response::HTTP_OK, $read->getStatusCode(), 'GET. Body: ' . $readBody);
        $observation = self::decode($readBody);
        $this->assertSame('final', $observation['status']);
        $this->assertSame('laboratory', self::at($observation, 'category', 0, 'coding', 0, 'code'));
        $this->assertSame(self::GLUCOSE, self::at($observation, 'code', 'coding', 0, 'code'));
        $this->assertEqualsWithDelta(5.2, self::at($observation, 'valueQuantity', 'value'), 0.0001);
        $this->assertEqualsWithDelta(3.9, self::at($observation, 'referenceRange', 0, 'low', 'value'), 0.0001);
        $this->assertEqualsWithDelta(5.5, self::at($observation, 'referenceRange', 0, 'high', 'value'), 0.0001);

        // The body the GET served, changed and sent back: what an explorer's PUT does.
        $observation['status'] = 'corrected';
        $observation['valueQuantity'] = ['value' => 6.4, 'unit' => 'mmol/L', 'system' => 'http://unitsofmeasure.org', 'code' => 'mmol/L'];
        $observation['note'] = [['text' => 'corrected after recalibration']];
        $put = $this->testClient->put(self::OBSERVATION_URL, $id, $observation);
        $putBody = $put->getBody()->getContents();
        $this->assertSame(Response::HTTP_OK, $put->getStatusCode(), 'PUT should return 200. Body: ' . $putBody);
        $updated = self::decode($putBody);
        $this->assertSame($id, $updated['id'] ?? null);
        $this->assertSame('corrected', $updated['status'] ?? null);
        $this->assertEqualsWithDelta(6.4, self::at($updated, 'valueQuantity', 'value'), 0.0001);
        $this->assertSame('corrected after recalibration', self::at($updated, 'note', 0, 'text'));
    }

    public function testACodeNotOnTheOrderIsA400NamingCode(): void
    {
        $response = $this->testClient->post(self::OBSERVATION_URL, $this->labResult([
            'code' => ['coding' => [['system' => 'http://loinc.org', 'code' => '2160-0', 'display' => 'Creatinine']]],
        ]));
        $body = $response->getBody()->getContents();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Body: ' . $body);
        $this->assertStringContainsString('Observation.code', $body);
    }

    public function testNoOrderIsA400NamingBasedOn(): void
    {
        $result = $this->labResult();
        unset($result['basedOn']);
        $response = $this->testClient->post(self::OBSERVATION_URL, $result);
        $body = $response->getBody()->getContents();

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Body: ' . $body);
        $this->assertStringContainsString('Observation.basedOn', $body);
    }
}
