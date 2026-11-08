<?php

/**
 * TokenIntrospectionRestControllerIsolatedTest - Isolated tests for TokenIntrospectionRestController.
 *
 * Complements the DB/HTTP-backed suite in tests/Tests/Api/IntrospectionTest.php by exercising
 * the pure request-parameter validation branch (`validateInitialRequestParameters`) and the
 * inactive-response shape produced by `postAction` when validation fails. These branches do
 * not require the OAuth stack or a database and run at the isolated tier for fast feedback.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\TokenIntrospectionRestController;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

final class TokenIntrospectionRestControllerIsolatedTest extends TestCase
{
    private function newController(): TokenIntrospectionRestController
    {
        $controller = new TokenIntrospectionRestController(new OEGlobalsBag([]));
        $controller->setLogger(new NullLogger());
        // ServerConfig::__construct reads the ambient session + globals + $_SERVER;
        // inject a mock so validateInitialRequestParameters()'s JWT-assertion probe
        // (which transitively calls getServerConfig()->getTokenUrl()) does not try to
        // read $_SERVER['HTTP_HOST'] / SessionWrapperFactory in the isolated process.
        $serverConfig = $this->createMock(ServerConfig::class);
        $serverConfig->method('getTokenUrl')->willReturn('http://localhost/oauth2/default/token');
        $controller->setServerConfig($serverConfig);
        return $controller;
    }

    private function newSession(HttpRestRequest $request): SessionInterface
    {
        $storage = (new MockFileSessionStorageFactory())->createStorage($request);
        $session = new Session($storage);
        $session->start();
        $request->setSession($session);
        return $session;
    }

    /**
     * @param array<string, mixed> $body
     */
    private function newPostRequest(array $body): HttpRestRequest
    {
        // HttpRestRequest::create() populates the server vars needed to construct a
        // valid PSR-7 URI from inside convertRestRequestToPsrRequest(); a bare
        // `new HttpRestRequest(..., ['REQUEST_METHOD' => 'POST'])` yields "http://:/"
        // and nyholm/psr7's Uri parser rejects it.
        return HttpRestRequest::create(
            'http://localhost/oauth2/default/introspect',
            'POST',
            $body
        );
    }

    // ---------------------------------------------------------------
    // validateInitialRequestParameters() - pure parameter-shape branches
    // ---------------------------------------------------------------

    public function testValidateReturnsFalseWhenTokenIsEmpty(): void
    {
        // Both the missing-token and empty-string-token shapes must be rejected
        // at the parameter-validation stage. The controller treats empty() the
        // same way for both, but the test asserts both boundaries explicitly so
        // a regression that only catches "not set" but not "set to ''" shows up.
        $request = $this->newPostRequest([
            'token' => '',
            'client_id' => 'some-client',
            'client_secret' => 'some-secret',
        ]);

        $controller = $this->newController();

        self::assertFalse(
            $controller->validateInitialRequestParameters($request),
            'validateInitialRequestParameters must reject requests with an empty token'
        );
    }

    public function testValidateReturnsFalseWhenClientIdIsMissingAndHintIsNotRefreshToken(): void
    {
        // validateInitialRequestParameters reads `token_hint` (not
        // `token_type_hint` — the latter is read by the main tokenIntrospection
        // body for the DB-backed path). Pass `token_hint=access_token` so this
        // test actually exercises the non-refresh branch rather than falling
        // through to the default empty-hint value.
        $request = $this->newPostRequest([
            'token' => 'some-opaque-token',
            'token_hint' => 'access_token',
        ]);

        $controller = $this->newController();

        self::assertFalse(
            $controller->validateInitialRequestParameters($request),
            'Missing client_id without a JWT assertion and without the refresh_token hint must be rejected'
        );
    }

    public function testValidateReturnsTrueWhenClientIdIsMissingButHintIsRefreshToken(): void
    {
        // The controller intentionally skips the client_id requirement for refresh_token hints
        // because the client_id is embedded inside the refresh token payload itself.
        $request = $this->newPostRequest([
            'token' => 'some-opaque-token',
            'token_hint' => 'refresh_token',
        ]);

        $controller = $this->newController();

        self::assertTrue(
            $controller->validateInitialRequestParameters($request),
            'Refresh-token hint must bypass the client_id-required check (client_id is inside the token)'
        );
    }

    public function testValidateReturnsTrueWhenClientIdIsProvided(): void
    {
        $request = $this->newPostRequest([
            'token' => 'some-opaque-token',
            'client_id' => 'some-client',
        ]);

        $controller = $this->newController();

        self::assertTrue(
            $controller->validateInitialRequestParameters($request),
            'A request with both token and client_id must pass initial-parameter validation'
        );
    }

    // ---------------------------------------------------------------
    // postAction() - inactive-response path (no DB / no OAuth stack needed)
    // ---------------------------------------------------------------

    public function testPostActionReturnsInactiveJsonWhenValidationFails(): void
    {
        // Missing token -> validateInitialRequestParameters is false -> returnInactiveResponse.
        $request = $this->newPostRequest(['client_id' => 'some-client']);
        $session = $this->newSession($request);
        $session->set('scratch', 'alive');

        $response = $this->newController()->postAction($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertJson($body);
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(['active' => false], $decoded, 'Inactive response must be a bare {"active":false} object');
    }

    public function testPostActionInvalidatesSessionAfterInactiveResponse(): void
    {
        // RFC 7662 responses are side-effect sensitive - postAction must drop the session
        // regardless of which branch produced the response.
        $request = $this->newPostRequest(['client_id' => 'some-client']);
        $session = $this->newSession($request);
        $session->set('scratch', 'alive');
        $originalId = $session->getId();

        $this->newController()->postAction($request);

        self::assertFalse($session->has('scratch'), 'Session attribute must not survive postAction');
        self::assertNotSame($originalId, $session->getId(), 'Session identifier must rotate after postAction');
    }

    // ---------------------------------------------------------------
    // Response shape via validation-failure path exercises returnInactiveResponse() /
    // getBasicResponse() end-to-end without touching the OAuth stack.
    // ---------------------------------------------------------------

    public function testInactiveResponseDoesNotLeakStatusField(): void
    {
        // The 'status' field is only set on tokens that parsed successfully but were
        // revoked/mismatched. A failure at the parameter-validation stage must NOT emit
        // 'status' - a leaking 'status' there would let callers distinguish "bad token"
        // from "unknown client", which the RFC 7662 inactive-response guidance disallows.
        $request = $this->newPostRequest([]);
        $this->newSession($request);

        $response = $this->newController()->postAction($request);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayNotHasKey('status', $decoded);
        self::assertArrayNotHasKey('client_id', $decoded);
        self::assertArrayNotHasKey('scope', $decoded);
        self::assertArrayNotHasKey('sub', $decoded);
    }
}
