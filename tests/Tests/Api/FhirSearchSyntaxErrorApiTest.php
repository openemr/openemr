<?php

/**
 * A FHIR search whose parameter value doesn't parse answers 400 with an OperationOutcome naming the parameter,
 * through a resource's own controller (Patient) and through the generic one (Observation, a mapped resource),
 * while a valid search that matches nothing still answers 200 with an empty Bundle.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class FhirSearchSyntaxErrorApiTest extends TestCase
{
    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $this->testClient = new ApiTestClient(self::baseUrl(), false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @return array<string, array{string, array<string, string>, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedSearchProvider(): array
    {
        return [
            'Patient birthdate' => ['/apis/default/fhir/Patient', ['birthdate' => 'not-a-date'], 'birthdate'],
            'Patient birthdate comparator' => ['/apis/default/fhir/Patient', ['birthdate' => 'xx2020-01-01'], 'birthdate'],
            'Observation date (mapped resource)' => ['/apis/default/fhir/Observation', ['date' => 'not-a-date'], 'date'],
            'Encounter date' => ['/apis/default/fhir/Encounter', ['date' => 'not-a-date'], 'date'],
            'ServiceRequest authored' => ['/apis/default/fhir/ServiceRequest', ['authored' => 'not-a-date'], 'authored'],
        ];
    }

    /**
     * @param array<string, string> $search
     */
    #[DataProvider('malformedSearchProvider')]
    public function testAMalformedSearchValueAnswers400WithAnOperationOutcome(string $endpoint, array $search, string $parameter): void
    {
        $response = $this->testClient->get($endpoint, $search);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $issue = $this->singleIssue($response);
        $this->assertSame('error', $issue['severity'] ?? null);
        $this->assertSame('invalid', $issue['code'] ?? null);
        $this->assertSame(
            "Search parameter '" . $parameter . "' must be a FHIR date or dateTime, such as 2020-01-31 or 2020-01-31T13:45:00Z.",
            $issue['diagnostics'] ?? null
        );
        // The parser's message, which the search trait wraps as "Inner message: ...", isn't sent to the client.
        $this->assertStringNotContainsString('Inner message', (string) $response->getBody());
    }

    public function testAValidSearchThatMatchesNothingStillAnswers200WithAnEmptyBundle(): void
    {
        $response = $this->testClient->get('/apis/default/fhir/Patient', ['family' => 'NoSuchFamily' . bin2hex(random_bytes(6))]);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = $this->json($response);
        $this->assertSame('Bundle', $body['resourceType'] ?? null);
        $this->assertSame(0, $body['total'] ?? null);
    }

    public function testAWellFormedDateStillSearches(): void
    {
        $response = $this->testClient->get('/apis/default/fhir/Observation', ['date' => 'ge2999-01-01']);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('Bundle', $this->json($response)['resourceType'] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    private function singleIssue(ResponseInterface $response): array
    {
        $body = $this->json($response);
        $this->assertSame('OperationOutcome', $body['resourceType'] ?? null);
        $issues = $body['issue'] ?? null;
        $this->assertIsArray($issues);
        $this->assertCount(1, $issues);
        $issue = $issues[0];
        $this->assertIsArray($issue);
        /** @var array<string, mixed> $issue */
        return $issue;
    }

    /**
     * @return array<string, mixed>
     */
    private function json(ResponseInterface $response): array
    {
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        /** @var array<string, mixed> $body */
        return $body;
    }

    private static function baseUrl(): string
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true);
        return is_string($baseUrl) && $baseUrl !== '' ? $baseUrl : "https://localhost";
    }
}
