<?php

/**
 * Patient Transaction API Endpoint Test Cases
 *
 * Covers the patient-transaction endpoints at the api tier:
 *   GET  /api/patient/{pid}/transaction  — list for a patient
 *   POST /api/patient/{pid}/transaction  — create
 *   PUT  /api/transaction/{tid}          — update
 *
 * Transactions store two rows per record: one row in `transactions`
 * (header: date / pid / groupname / title / user) and one row in
 * `lbt_data` (per-field body/refer_date/etc). Only `type=LBTref` is
 * fully validated server-side — the test uses that type.
 *
 * Locks PR #14653's PatientTransactionService::insert return-shape
 * invariant (returns `["id" => $transactionId, "form_id" => $lbtDataId]`
 * instead of just the form_id) at the api tier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PatientTransactionApiTest extends TestCase
{
    private const FIXTURE_BODY_PREFIX = "test-fixture-PatientTransactionApiTest";
    private const DEMO_PATIENT_PID = 1;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // Every POST writes one `transactions` row (header) + up to 11
        // `lbt_data` rows (one per field_id). Find the header rows by
        // matching the body-field value and sweep both tables.
        $headerIds = QueryUtils::fetchTableColumn(
            "SELECT t.id FROM `transactions` t "
            . "JOIN `lbt_data` d ON d.form_id = t.id AND d.field_id = 'body' "
            . "WHERE d.field_value LIKE ?",
            'id',
            [self::FIXTURE_BODY_PREFIX . "%"]
        );
        if (count($headerIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($headerIds), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `lbt_data` WHERE form_id IN ($placeholders)",
                $headerIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `transactions` WHERE id IN ($placeholders)",
                $headerIds
            );
        }
    }

    private function listEndpoint(): string
    {
        return "/apis/default/api/patient/" . self::DEMO_PATIENT_PID . "/transaction";
    }

    /**
     * @param array<mixed> $node
     * @param list<array<string, mixed>> $out
     */
    private function collectRows(array $node, array &$out): void
    {
        foreach ($node as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (array_key_exists('body', $item) || array_key_exists('form_id', $item)) {
                /** @var array<string, mixed> $item */
                $out[] = $item;
            } else {
                $this->collectRows($item, $out);
            }
        }
    }

    /**
     * @return array{id: int, form_id: int, body: string}
     */
    private function postFixtureTransaction(): array
    {
        $body = self::FIXTURE_BODY_PREFIX . "-" . uniqid();
        $response = $this->testClient->post($this->listEndpoint(), [
            'type' => 'LBTref',
            'referralDate' => '2026-01-01',
            'body' => $body,
            'groupname' => 'Default',
            'referByNpi' => '0000000000',
            'referToNpi' => '0000000000',
            'referDiagnosis' => '',
            'riskLevel' => 'low',
            'includeVitals' => '',
            'authorization' => '',
            'visits' => '',
            'validFrom' => '',
            'validThrough' => '',
        ]);
        $raw = (string) $response->getBody();
        $this->assertEquals(201, $response->getStatusCode(), 'POST should 201. Body: ' . $raw);
        $payload = json_decode($raw, true);
        $this->assertIsArray($payload);
        // handleProcessingResult may wrap in {validationErrors, internalErrors, data: [{...}]}
        // or return the single row directly depending on $isMultipleResultResponse.
        if (isset($payload['data']) && is_array($payload['data'])) {
            $row = $payload['data'];
            if (array_is_list($row) && isset($row[0])) {
                $row = $row[0];
            }
        } else {
            $row = $payload;
        }
        $this->assertIsArray($row, 'POST body shape: ' . $raw);
        $this->assertArrayHasKey('id', $row, 'POST must return the transaction id (header row), not just form_id. Body: ' . $raw);
        $this->assertArrayHasKey('form_id', $row);
        $this->assertIsNumeric($row['id']);
        $this->assertIsNumeric($row['form_id']);
        return [
            'id' => (int) $row['id'],
            'form_id' => (int) $row['form_id'],
            'body' => $body,
        ];
    }

    #[Test]
    public function testPostReturnsBothIds(): void
    {
        // PR #14653 fixed PatientTransactionService::insert to return the
        // transactions.id (header row) in addition to the lbt_data.id. This
        // test locks the shape at the api tier — both keys are present.
        $created = $this->postFixtureTransaction();
        $this->assertGreaterThan(0, $created['id']);
        $this->assertGreaterThan(0, $created['form_id']);
    }

    #[Test]
    public function testGetAll(): void
    {
        $created = $this->postFixtureTransaction();

        $response = $this->testClient->get($this->listEndpoint());
        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $data = $body['data'];
        $this->assertIsArray($data);
        $flat = [];
        $this->collectRows($data, $flat);
        $bodies = array_column($flat, 'body');
        $this->assertContains($created['body'], $bodies, 'fixture transaction must surface in the list by body text');
    }

    #[Test]
    public function testPutUpdatesTransaction(): void
    {
        $created = $this->postFixtureTransaction();

        $putResponse = $this->testClient->put(
            "/apis/default/api/transaction",
            (string) $created['id'],
            [
                'type' => 'LBTref',
                'referralDate' => '2026-02-01',
                'body' => $created['body'] . "-updated",
                'groupname' => 'Default',
                'referByNpi' => '0000000000',
                'referToNpi' => '0000000000',
                'referDiagnosis' => 'ICD10:X',
                'riskLevel' => 'high',
                'includeVitals' => '',
                'authorization' => '',
                'visits' => '',
                'validFrom' => '',
                'validThrough' => '',
            ]
        );
        $this->assertEquals(200, $putResponse->getStatusCode(), 'PUT should 200. Body: ' . (string) $putResponse->getBody());

        $follow = $this->testClient->get($this->listEndpoint());
        $followBody = json_decode((string) $follow->getBody(), true);
        $this->assertIsArray($followBody);
        // GetPatientTransactions wraps rows in a doubly-nested data array:
        // {"data":[[{row}, {row}, ...]], "links":{...}}. Flatten with
        // iterator_to_array on a RecursiveIteratorIterator so the test
        // doesn't depend on the exact nesting depth.
        $flat = [];
        $data = $followBody['data'] ?? [];
        if (is_array($data)) {
            $this->collectRows($data, $flat);
        }
        $match = null;
        foreach ($flat as $row) {
            if (($row['body'] ?? null) === $created['body'] . "-updated") {
                $match = $row;
                break;
            }
        }
        $this->assertIsArray($match, 'updated transaction should surface the new body text in the list');
    }
}
