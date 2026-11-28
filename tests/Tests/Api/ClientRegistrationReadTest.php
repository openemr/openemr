<?php

/**
 * Regression tests for the DCR client-read endpoint:
 * GET /oauth2/{site}/client/{registration_uri_path}
 *
 * Covers:
 * - Happy path: DCR a client, read it back with the returned
 *   registration_access_token, confirm the response payload matches
 *   the registered metadata.
 * - hash_equals comparison: a wrong token yields 403, with constant-time
 *   semantics on the compare.
 * - is_enabled gate: a disabled client cannot read its own metadata.
 * - RAT rotation: using the RAT once invalidates it (the response body
 *   carries a fresh RAT for the next call).
 * - Method gate: only the RFC 7592 read is implemented, so update (PUT),
 *   delete (DELETE) and any other non-GET method get 405 with Allow: GET,
 *   and leave the registration and its token untouched.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils as PromiseUtils;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ClientRegistrationReadTest extends TestCase
{
    private Client $http;
    private ?string $clientId = null;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client([
            'base_uri' => $baseUrl,
            'verify' => false,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->clientId !== null) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$this->clientId]
            );
        }
    }

    #[Test]
    public function testReadWithValidTokenReturnsClientMetadata(): void
    {
        $registration = $this->registerClient();
        $readResponse = $this->readClient(
            $registration['registration_client_uri'],
            $registration['registration_access_token']
        );
        $this->assertSame(200, $readResponse['status']);
        $this->assertIsArray($readResponse['body']);
        $this->assertSame($registration['client_id'], $readResponse['body']['client_id']);
        $this->assertArrayHasKey('client_secret', $readResponse['body']);
        $this->assertSame(['https://client.example/cb'], $readResponse['body']['redirect_uris']);
    }

    #[Test]
    public function testReadWithWrongTokenReturns403(): void
    {
        $registration = $this->registerClient();
        $readResponse = $this->readClient(
            $registration['registration_client_uri'],
            'not-the-real-token'
        );
        $this->assertSame(403, $readResponse['status']);
    }

    #[Test]
    public function testReadRejectsWhenClientIsDisabled(): void
    {
        $registration = $this->registerClient();
        // Disable the client the same way the admin UI does.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 0 WHERE `client_id` = ?',
            [$registration['client_id']]
        );
        $readResponse = $this->readClient(
            $registration['registration_client_uri'],
            $registration['registration_access_token']
        );
        $this->assertSame(403, $readResponse['status']);
    }

    #[Test]
    public function testTwoConcurrentReadsWithSameTokenCannotBothSucceed(): void
    {
        $registration = $this->registerClient();
        $staleToken = $registration['registration_access_token'];
        $path = parse_url($registration['registration_client_uri'], PHP_URL_PATH);
        $this->assertIsString($path);

        // Fire both reads before awaiting either so their SELECTs can both
        // observe the same pre-rotation registration_token value. The
        // controller's rotation UPDATE pins the WHERE clause to the current
        // token, so at most one of the two UPDATEs matches a row — the other
        // request gets 403 from the affected-rows check. This is the
        // atomicity guarantee that prevents one-shot RAT double-redemption.
        $promiseA = $this->http->getAsync($path, [
            'headers' => ['Authorization' => 'Bearer ' . $staleToken],
        ]);
        $promiseB = $this->http->getAsync($path, [
            'headers' => ['Authorization' => 'Bearer ' . $staleToken],
        ]);
        $responses = PromiseUtils::unwrap(['a' => $promiseA, 'b' => $promiseB]);
        $this->assertInstanceOf(ResponseInterface::class, $responses['a']);
        $this->assertInstanceOf(ResponseInterface::class, $responses['b']);

        $statuses = [
            $responses['a']->getStatusCode(),
            $responses['b']->getStatusCode(),
        ];
        sort($statuses);
        $this->assertSame(
            [200, 403],
            $statuses,
            'Exactly one of two concurrent reads with the same registration token must succeed; the other must be rejected with 403'
        );
    }

    #[Test]
    public function testReadRotatesRegistrationAccessToken(): void
    {
        $registration = $this->registerClient();
        $firstRead = $this->readClient(
            $registration['registration_client_uri'],
            $registration['registration_access_token']
        );
        $this->assertSame(200, $firstRead['status']);
        $this->assertIsArray($firstRead['body']);
        $this->assertArrayHasKey('registration_access_token', $firstRead['body']);
        $this->assertIsString($firstRead['body']['registration_access_token']);
        $rotatedToken = $firstRead['body']['registration_access_token'];
        $this->assertNotSame(
            $registration['registration_access_token'],
            $rotatedToken,
            'The response body must carry a freshly minted registration_access_token'
        );

        // The original token must no longer be accepted.
        $secondReadWithOldToken = $this->readClient(
            $registration['registration_client_uri'],
            $registration['registration_access_token']
        );
        $this->assertSame(403, $secondReadWithOldToken['status']);

        // The rotated token must be accepted on the next call.
        $secondReadWithRotatedToken = $this->readClient(
            $registration['registration_client_uri'],
            $rotatedToken
        );
        $this->assertSame(200, $secondReadWithRotatedToken['status']);
    }

    #[Test]
    #[DataProvider('unsupportedMethodProvider')]
    public function testUnsupportedMethodReturns405AndLeavesRegistrationUntouched(string $method): void
    {
        $registration = $this->registerClient();
        $path = parse_url($registration['registration_client_uri'], PHP_URL_PATH);
        $this->assertIsString($path);

        // An RFC 7592 §2.2 update carries the full client metadata. Send a
        // changed client_name so a silently applied update would show up
        // in the follow-up read.
        $response = $this->http->request($method, $path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $registration['registration_access_token'],
                'Content-Type' => 'application/json',
            ],
            'body' => (string) json_encode([
                'client_id' => $registration['client_id'],
                'client_name' => 'renamed-by-' . $method,
                'redirect_uris' => ['https://client.example/cb'],
            ]),
        ]);

        $this->assertSame(405, $response->getStatusCode(), $method . ' must be rejected with 405');
        $this->assertSame('GET', $response->getHeaderLine('Allow'));
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_request', $body['error'] ?? null);
        $this->assertArrayNotHasKey('client_secret', $body, 'A rejected method must not echo client credentials');
        $this->assertArrayNotHasKey('registration_access_token', $body);

        // The rejected request must not have rotated the registration token,
        // changed the metadata, or removed the client: the original token
        // still reads the original registration.
        $readResponse = $this->readClient(
            $registration['registration_client_uri'],
            $registration['registration_access_token']
        );
        $this->assertSame(200, $readResponse['status'], 'The original registration token must still work after a rejected ' . $method);
        $this->assertIsArray($readResponse['body']);
        $this->assertSame($registration['client_id'], $readResponse['body']['client_id']);
        $this->assertSame($registration['client_name'], $readResponse['body']['client_name']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsupportedMethodProvider(): array
    {
        return [
            'PUT (RFC 7592 update)' => ['PUT'],
            'DELETE (RFC 7592 delete)' => ['DELETE'],
            'POST' => ['POST'],
            'PATCH' => ['PATCH'],
        ];
    }

    /**
     * @return array{client_id: string, client_name: string, registration_access_token: string, registration_client_uri: string}
     */
    private function registerClient(): array
    {
        $clientName = 'ClientRegistrationReadTest-' . bin2hex(random_bytes(3));
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => (string) json_encode([
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => $clientName,
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ]),
        ]);
        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertIsString($body['client_id'] ?? null);
        $this->assertIsString($body['registration_access_token'] ?? null);
        $this->assertIsString($body['registration_client_uri'] ?? null);
        $this->clientId = $body['client_id'];
        return [
            'client_id' => $body['client_id'],
            'client_name' => $clientName,
            'registration_access_token' => $body['registration_access_token'],
            'registration_client_uri' => $body['registration_client_uri'],
        ];
    }

    /**
     * @return array{status: int, body: mixed}
     */
    private function readClient(string $registrationClientUri, string $token): array
    {
        // registration_client_uri is an absolute URL; peel the path for the
        // test's base-URI-anchored http client.
        $path = parse_url($registrationClientUri, PHP_URL_PATH);
        $this->assertIsString($path);
        $response = $this->http->get($path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => json_decode((string) $response->getBody(), true),
        ];
    }
}
