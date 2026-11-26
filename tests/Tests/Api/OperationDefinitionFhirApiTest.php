<?php

/**
 * FHIR OperationDefinition API tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @copyright Copyright (c) 2026 Marcello Costagliola <marcello.costagliola1@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests for the FHIR OperationDefinition endpoints, driven over HTTP through ApiTestClient:
 *   GET /fhir/OperationDefinition             (list -> returns a Bundle)
 *   GET /fhir/OperationDefinition/:operation  (read one -> returns a single OperationDefinition)
 *
 * The server defines one operation, $bulkdata-status, in code; there is no database state.
 *
 * OpenEMR vs FHIR conventions (tests pin current server behavior, not the spec):
 * - The list route is on the authorization skip list, so it answers without a token; the
 *   read-one route is not, and needs one.
 * - The list Bundle is typed "collection", and each entry holds the OperationDefinition
 *   itself rather than a BundleEntry with fullUrl and resource.
 * - The definition has no id; read-one looks it up by name.
 * - An unknown operation returns 404 with an empty JSON array, not OperationOutcome.
 */
class OperationDefinitionFhirApiTest extends TestCase
{
    private const ENDPOINT = "/apis/default/fhir/OperationDefinition";

    /** The only operation the server defines. */
    private const BULKDATA_STATUS = '$bulkdata-status';

    private ApiTestClient $testClient;

    /**
     * Authenticate a client.
     */
    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    /**
     * Revoke the token.
     */
    protected function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    // ---------------------------------------------------------------------
    // List
    // ---------------------------------------------------------------------

    /**
     * The list is a collection Bundle with the single $bulkdata-status definition.
     */
    public function testListReturnsBulkDataStatusDefinition(): void
    {
        $result = $this->testClient->get(self::ENDPOINT);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame("collection", $bundle['type'] ?? null);
        $this->assertSame(1, $bundle['total'] ?? null);
        $this->assertIsArray($bundle['entry'] ?? null);
        $this->assertCount(1, $bundle['entry']);
        $this->assertIsArray($bundle['entry'][0] ?? null);
        $this->assertBulkDataStatusDefinition($bundle['entry'][0]);
    }

    /**
     * The list answers without a token: the route is on the authorization skip list.
     */
    public function testListWithoutTokenReturnsBundle(): void
    {
        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        // Deliberately do NOT call setAuthToken().
        $result = $unauthClient->get(self::ENDPOINT);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $bundle = $this->decodeJsonArray($result);
        $this->assertSame("Bundle", $bundle['resourceType'] ?? null);
        $this->assertSame(1, $bundle['total'] ?? null);
    }

    // ---------------------------------------------------------------------
    // Read one
    // ---------------------------------------------------------------------

    /**
     * Reading $bulkdata-status returns the definition itself.
     */
    public function testGetOneReturnsBulkDataStatusDefinition(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, self::BULKDATA_STATUS);
        $this->assertSame(Response::HTTP_OK, $result->getStatusCode());

        $this->assertBulkDataStatusDefinition($this->decodeJsonArray($result));
    }

    /**
     * An operation the server does not define returns 404 with an empty JSON array.
     */
    public function testGetOneUnknownOperationReturnsNotFound(): void
    {
        $result = $this->testClient->getOne(self::ENDPOINT, '$no-such-operation');
        $this->assertSame(Response::HTTP_NOT_FOUND, $result->getStatusCode());

        $this->assertSame([], $this->decodeJsonArray($result), "404 body should be an empty JSON array");
    }

    /**
     * Read-one without a token is rejected with 401 and an OAuth denial body.
     */
    public function testGetOneUnauthorizedReturns401(): void
    {
        $unauthClient = new ApiTestClient(self::baseUrl(), false);
        $result = $unauthClient->getOne(self::ENDPOINT, self::BULKDATA_STATUS);
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $result->getStatusCode());

        $body = $this->decodeJsonArray($result);
        $this->assertIsString($body['message'] ?? null);
        $this->assertStringContainsString(
            'denied the request',
            $body['message'],
            '401 should be an OAuth authorization denial, not a routing or server error'
        );
        $this->assertArrayNotHasKey('resourceType', $body, '401 body should not be a FHIR resource');
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    /**
     * Base URL of the app under test, from OPENEMR_BASE_URL_API like the other API tests.
     */
    private static function baseUrl(): string
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : "https://localhost";
    }

    /**
     * Decode a JSON body and assert it is an array (object or list).
     *
     * @return array<array-key, mixed>
     */
    private function decodeJsonArray(ResponseInterface $response): array
    {
        $body = (string) $response->getBody()->getContents();
        $this->assertNotEmpty($body, "Response should have a JSON body");
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded, "Response body should be JSON");
        return $decoded;
    }

    /**
     * Assert the $bulkdata-status definition: an active operation with one required "job"
     * input parameter of type string.
     *
     * @param array<array-key, mixed> $definition
     */
    private function assertBulkDataStatusDefinition(array $definition): void
    {
        $this->assertSame("OperationDefinition", $definition['resourceType'] ?? null);
        $this->assertSame(self::BULKDATA_STATUS, $definition['name'] ?? null);
        $this->assertSame("active", $definition['status'] ?? null);
        $this->assertSame("operation", $definition['kind'] ?? null);

        $this->assertIsArray($definition['parameter'] ?? null);
        $this->assertCount(1, $definition['parameter']);
        $parameter = $definition['parameter'][0] ?? null;
        $this->assertIsArray($parameter);
        $this->assertSame("job", $parameter['name'] ?? null);
        $this->assertSame("in", $parameter['use'] ?? null);
        $this->assertSame(1, $parameter['min'] ?? null);
        $this->assertIsArray($parameter['type'] ?? null);
        $this->assertSame("string", $parameter['type']['code'] ?? null);
    }
}
