<?php

/**
 * Procedure API Endpoint Test Cases
 *
 * Covers:
 *   - GET /api/procedure         (collection)
 *   - GET /api/procedure/{uuid}  (single + 404 shape)
 *
 * The `testGetAll` case is the regression gate for the dangling
 * `LEFT JOIN` in `ProcedureService::getAll()` fixed in this PR —
 * pre-fix, the endpoint returned an HTML "Query Error" page (SQL
 * syntax error) instead of a JSON response, breaking every API
 * client that touched it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProcedureApiTest extends TestCase
{
    private const PROCEDURE_API_ENDPOINT = "/apis/default/api/procedure";

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // Each test's setUp() calls setAuthToken() which registers +
        // enables a fresh OAuth client. Without this cleanup, repeated
        // runs leave client rows + their tokens in the DB. Mirrors the
        // teardown pattern in PatientApiTest.
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    public function testGetAllReturnsJsonProcessingResult(): void
    {
        // Pre-fix the service's SQL carried a dangling `LEFT JOIN`
        // (introduced in PR #9357 — the keywords were left in place
        // when a planned join was dropped), so the endpoint SQL-errored
        // out before any procedure rows could be returned. The response
        // was an HTML error page, not JSON, and the HTTP status was
        // actually 200 because the error was emitted after headers were
        // sent — which breaks every machine client that trusts the
        // Content-Type.
        $response = $this->testClient->get(self::PROCEDURE_API_ENDPOINT);
        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray(
            $body,
            'response must parse as JSON (regression gate on the dangling LEFT JOIN SQL error)'
        );
        $this->assertArrayHasKey('data', $body);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertArrayHasKey('internalErrors', $body);
        $this->assertSame([], $body['validationErrors']);
        $this->assertSame([], $body['internalErrors']);
        $this->assertIsArray($body['data']);
    }

    #[Test]
    public function testGetOneWithUnknownUuidReturnsValidationError(): void
    {
        // ProcedureService::getOne rejects an unknown uuid with a
        // validation message rather than the more conventional 404 that
        // other BaseService-derived endpoints produce (it runs
        // `BaseValidator::validateId("uuid", "procedure_order", ...)`
        // which treats "no match" as a validation failure, not a lookup
        // miss). The REST layer passes that through as a 400 with the
        // validationErrors envelope populated — this test locks that
        // contract so a future refactor to proper 404 semantics shows
        // up as a visible shape change.
        $response = $this->testClient->getOne(
            self::PROCEDURE_API_ENDPOINT,
            '00000000-0000-4000-8000-000000000000'
        );
        $this->assertSame(400, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('validationErrors', $body);
        $this->assertIsArray($body['validationErrors']);
        $this->assertArrayHasKey('uuid', $body['validationErrors']);
    }
}
