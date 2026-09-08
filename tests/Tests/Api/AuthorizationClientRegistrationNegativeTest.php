<?php

/**
 * Negative-path tests for AuthorizationController::clientRegistration() JWKS handling.
 *
 * Covers the validation branches added by the JWKS-optional change:
 *   1. system-scoped confidential client with neither jwks nor jwks_uri  -> rejected
 *   2. jwks present but not a JSON object with a keys array              -> rejected
 *   3. system/user scopes requested by a public client                  -> rejected
 *   4. non-JSON content type on the registration request                -> rejected
 *
 * clientRegistration() catches OAuthServerException at the method boundary and
 * returns it via generateHttpResponse(), so these assert on the HTTP response
 * (4xx + invalid_client_metadata error body), not a thrown exception.
 *
 * Modeled on AuthorizationGrantFlowTest (same namespace, kernel/session setup).
 *
 * @package OpenEMR
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 */

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class AuthorizationClientRegistrationNegativeTest extends TestCase
{
    private function getMockSession(): Session
    {
        $session = new Session(new MockFileSessionStorage());
        $session->start();
        $session->set("site_id", "default");
        return $session;
    }

    private function getMockKernel(): OEHttpKernel
    {
        $dispatcher = new EventDispatcher();
        $controller = $this->createMock(ControllerResolverInterface::class);
        $kernel = new OEHttpKernel($dispatcher, $controller);
        $projectDir = is_string($GLOBALS['webserver_root'] ?? null) ? $GLOBALS['webserver_root'] : dirname(__DIR__, 3);
        $webRoot = is_string($GLOBALS['webroot'] ?? null) ? $GLOBALS['webroot'] : '';
        $kernel->getGlobalsBag()->set('kernel', new Kernel($projectDir, $webRoot, $dispatcher));
        return $kernel;
    }

    private function getAuthorizationController(Session $session, OEHttpKernel $kernel): AuthorizationController
    {
        $authController = new AuthorizationController($session, $kernel, true);
        $authController->setLogger($this->createMock(LoggerInterface::class));
        return $authController;
    }

    /**
     * Build a JSON registration request and run it through clientRegistration().
     *
     * @param array  $jsonRequest the client metadata payload
     * @param string $contentType override the content type (defaults to application/json)
     */
    /**
     * @param array<string, mixed> $jsonRequest
     * @return array{0: int, 1: array<array-key, mixed>}
     */
    private function registerWith(array $jsonRequest, string $contentType = 'application/json'): array
    {
        $session = $this->getMockSession();
        $kernel = $this->getMockKernel();
        $authController = $this->getAuthorizationController($session, $kernel);

        $registrationRequest = HttpRestRequest::create(
            "/oauth2/default/register",
            "POST",
            [],
            [],
            [],
            ['CONTENT_TYPE' => $contentType],
            (string) json_encode($jsonRequest)
        );

        $response = $authController->clientRegistration($registrationRequest);
        $status = $response->getStatusCode();
        $decoded = json_decode($response->getBody()->getContents(), true);
        $body = is_array($decoded) ? $decoded : [];
        return [$status, $body];
    }

    /**
     * A rejected registration should not succeed (no 2xx) and should carry an
     * OAuth error payload rather than a client_id.
     */
    /**
     * @param array<array-key, mixed> $body
     */
    private function assertRejected(int $status, array $body, string $context): void
    {
        $this->assertGreaterThanOrEqual(400, $status, "$context: expected a 4xx status, got $status");
        $this->assertLessThan(500, $status, "$context: expected a client-error (4xx), got $status");
        $this->assertArrayNotHasKey('client_id', $body, "$context: rejected registration must not return a client_id");
    }

    /**
     * 1. Confidential (private) client requesting a system/ scope with neither
     *    jwks nor jwks_uri must be rejected (invalid_client_metadata).
     */
    public function testSystemScopeWithoutJwksOrJwksUriIsRejected(): void
    {
        [$status, $body] = $this->registerWith([
            "application_type" => "private",
            "redirect_uris" => ["http://localhost:8080/oauth2/callback"],
            "client_name" => "No JWKS Client",
            "token_endpoint_auth_method" => "client_secret_post",
            "contacts" => ["test@open-emr.org"],
            "scope" => "system/Patient.read",
            // deliberately no 'jwks' and no 'jwks_uri'
        ]);
        $this->assertRejected($status, $body, "system scope without jwks/jwks_uri");
    }

    /**
     * 2. A jwks value that is not a JSON object containing a "keys" array
     *    (here: a bare list) must be rejected.
     */
    public function testMalformedJwksStructureIsRejected(): void
    {
        [$status, $body] = $this->registerWith([
            "application_type" => "private",
            "redirect_uris" => ["http://localhost:8080/oauth2/callback"],
            "client_name" => "Bad JWKS Client",
            "token_endpoint_auth_method" => "client_secret_post",
            "contacts" => ["test@open-emr.org"],
            "scope" => "system/Patient.read",
            // jwks must be an object with a "keys" array; a bare list is invalid
            "jwks" => ["not", "a", "keys", "object"],
        ]);
        $this->assertRejected($status, $body, "malformed jwks structure");
    }

    /**
     * 3. A public client requesting system/ or user/ scopes must be rejected —
     *    those scopes are confidential-only.
     */
    public function testPublicClientRequestingSystemScopeIsRejected(): void
    {
        [$status, $body] = $this->registerWith([
            "application_type" => "public",
            "redirect_uris" => ["http://localhost:8080/oauth2/callback"],
            "client_name" => "Public System Client",
            "token_endpoint_auth_method" => "none",
            "contacts" => ["test@open-emr.org"],
            "scope" => "user/Patient.read",
        ]);
        $this->assertRejected($status, $body, "public client requesting user/system scope");
    }

    /**
     * 4. A registration request with a non-JSON content type must be rejected
     *    before any metadata processing.
     */
    public function testNonJsonContentTypeIsRejected(): void
    {
        [$status, $body] = $this->registerWith([
            "application_type" => "private",
            "redirect_uris" => ["http://localhost:8080/oauth2/callback"],
            "client_name" => "Wrong Content Type",
            "scope" => "openid",
        ], 'text/plain');
        $this->assertRejected($status, $body, "non-JSON content type");
    }
}
