<?php

/**
 * Direct coverage for TokenIntrospectionRestController.
 *
 * The controller previously had no dedicated tests despite being the
 * RFC 7662 introspection entry point. The flow was hard to exercise
 * because two branches inside `tokenIntrospection()` reached straight
 * for `QueryUtils::querySingleRow("SELECT * FROM oauth_clients ...")`,
 * a static call with no test seam, and the JWT-validation path
 * constructed `JWTClientAuthenticationService` inline.
 *
 * The accompanying refactor moved the raw `oauth_clients` lookup into
 * `ClientRepository::getRawClientRow()` and cached the JWT auth service
 * behind a setter — every introspection branch is now mockable through
 * the existing `setClientRepository()` / new
 * `setJWTClientAuthenticationService()` seams. These tests pin the
 * RFC 7662 contract (any error → `{ "active": false }` + 200) and use
 * a recording logger to verify which gate fired, since the response
 * body alone can't distinguish "no such client" from "bad secret".
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\RestControllers;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeyParser;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\RestControllers\TokenIntrospectionRestController;
use OpenEMR\Services\JWTClientAuthenticationService;
use OpenEMR\Services\TrustedUserService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\AbstractLogger;
use Stringable;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorageFactory;

final class TokenIntrospectionRestControllerTest extends TestCase
{
    private const TEST_CLIENT_ID = 'test-client-abc';
    private const TEST_TOKEN = 'opaque-bearer-token';

    // -----------------------------------------------------------------
    // validateInitialRequestParameters — request-shape gate
    // -----------------------------------------------------------------

    public function testValidateInitialRequestParametersRejectsEmptyToken(): void
    {
        $request = $this->buildRequest([]);
        $controller = $this->buildController();

        self::assertFalse($controller->validateInitialRequestParameters($request));
    }

    public function testValidateInitialRequestParametersAcceptsRequestWithJwtClientAssertion(): void
    {
        // JWT-shaped body: a JWT assertion authenticates the client without
        // a separate client_id field. The gate must let this through to
        // the introspection flow regardless of token_type_hint.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_assertion' => 'fake.jwt.signature',
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        ]);
        $controller = $this->buildController();

        self::assertTrue($controller->validateInitialRequestParameters($request));
    }

    public function testValidateInitialRequestParametersAllowsRefreshTokenHintWithoutClientId(): void
    {
        // RFC 7662 carve-out: when the caller advertises a refresh_token,
        // the client_id can be derived from the token itself, so the
        // gate must not require a separate client_id parameter.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'token_hint' => 'refresh_token',
        ]);
        $controller = $this->buildController();

        self::assertTrue($controller->validateInitialRequestParameters($request));
    }

    public function testValidateInitialRequestParametersRejectsAccessTokenHintWithoutClientId(): void
    {
        // Access-token introspection requires the caller to identify
        // itself; without client_id we can't bind the response to a
        // confidential client. Per the inline comment, OpenEMR returns
        // active:false here rather than 401 to keep Inferno happy.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'token_hint' => 'access_token',
        ]);
        $controller = $this->buildController();

        self::assertFalse($controller->validateInitialRequestParameters($request));
    }

    public function testValidateInitialRequestParametersAcceptsRequestWithClientId(): void
    {
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => self::TEST_CLIENT_ID,
        ]);
        $controller = $this->buildController();

        self::assertTrue($controller->validateInitialRequestParameters($request));
    }

    // -----------------------------------------------------------------
    // postAction — RFC 7662 inactive response
    // -----------------------------------------------------------------

    public function testPostActionReturnsInactiveBodyWhenInitialValidationFails(): void
    {
        // RFC 7662 §2.2: the endpoint always answers; never reveal
        // which gate rejected. An empty token must come back as 200
        // OK with `active:false`, not 400.
        $request = $this->buildRequest([]);
        $controller = $this->buildController();

        $response = $controller->postAction($request);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame(['active' => false], $this->decodeJsonBody($response->getBody()));
    }

    // -----------------------------------------------------------------
    // tokenIntrospection — JWT-assertion authentication path
    // -----------------------------------------------------------------

    public function testIntrospectionRejectsClientIdMismatchBetweenJwtAndRequest(): void
    {
        // Phishing-shaped: the JWT carries client_id "alice" but the
        // request body's client_id is "bob". Either is malformed or
        // attacker-supplied; reject before consulting the DB.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => 'bob',
            'client_assertion' => 'fake.jwt.signature',
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setJWTClientAuthenticationService($this->buildJwtAuthService(
            extractedClientId: 'alice',
        ));

        $response = $controller->postAction($request);

        self::assertActiveFalse($response, 'Phishing-shaped mismatch must produce active:false');
        self::assertLogContains($logger, 'Client ID mismatch');
    }

    public function testIntrospectionRejectsUnregisteredClient(): void
    {
        // No row in oauth_clients for the JWT-asserted client_id —
        // "Not a registered client". The repository's null return
        // is the test seam; pre-refactor this was a static QueryUtils
        // call with no way to inject the empty-result branch.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_assertion' => 'fake.jwt.signature',
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setJWTClientAuthenticationService($this->buildJwtAuthService(
            extractedClientId: self::TEST_CLIENT_ID,
        ));
        $controller->setClientRepository($this->buildClientRepo(rawRow: null));

        $response = $controller->postAction($request);

        self::assertActiveFalse($response);
        self::assertLogContains($logger, 'Not a registered client');
    }

    public function testIntrospectionRejectsDisabledClient(): void
    {
        // Row exists but `is_enabled = 0` — "Client failed security".
        // Disabled clients cannot introspect even valid tokens.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_assertion' => 'fake.jwt.signature',
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setJWTClientAuthenticationService($this->buildJwtAuthService(
            extractedClientId: self::TEST_CLIENT_ID,
        ));
        $controller->setClientRepository($this->buildClientRepo(rawRow: [
            'client_id' => self::TEST_CLIENT_ID,
            'is_enabled' => 0,
        ]));

        $response = $controller->postAction($request);

        self::assertActiveFalse($response);
        self::assertLogContains($logger, 'Client failed security');
    }

    // -----------------------------------------------------------------
    // tokenIntrospection — no-assertion path (legacy client_id+secret)
    // -----------------------------------------------------------------

    public function testIntrospectionNoAssertionRequiresClientSecretWhenConfidential(): void
    {
        // Confidential clients must present client_secret — without it
        // we can't authenticate. Public clients (is_confidential=0)
        // would skip this check, so the test fixture pins
        // is_confidential=1.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => self::TEST_CLIENT_ID,
            // client_secret intentionally omitted
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setClientRepository($this->buildClientRepo(rawRow: [
            'client_id' => self::TEST_CLIENT_ID,
            'is_enabled' => 1,
            'is_confidential' => 1,
            'client_secret' => 'encrypted-secret-blob',
        ]));

        $response = $controller->postAction($request);

        self::assertActiveFalse($response);
        self::assertLogContains($logger, 'Invalid client app type');
    }

    public function testIntrospectionNoAssertionAcceptsEnabledClientWithStringIsEnabledValue(): void
    {
        // Regression for a misplaced paren in the no-assertion path:
        // pre-fix the disabled-client gate read
        //   intval($client['is_enabled'] !== 1)
        // — the `!== 1` was *inside* `intval()`. With ADODB returning
        // tinyint columns as PHP strings ('1', '0'), `'1' !== 1` is
        // true, `intval(true) === 1` → enabled clients fired the
        // "Client failed security" branch and never reached
        // introspection. Post-fix:
        //   intval($client['is_enabled']) !== 1
        // — string '1' coerces via intval() to 1, gate passes.
        //
        // Public client (is_confidential=0) so the secret check skips
        // and the disabled-client gate is the one in scope here. The
        // request still ends up active:false because downstream flow
        // (JsonWebKeyParser) isn't wired in this test, but the
        // assertion below proves the disabled-client gate did not
        // fire — a pre-fix run would have logged "Client failed
        // security" before the downstream throw.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => self::TEST_CLIENT_ID,
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setClientRepository($this->buildClientRepo(rawRow: [
            'client_id' => self::TEST_CLIENT_ID,
            'is_enabled' => '1',
            'is_confidential' => 0,
            'client_secret' => null,
        ]));

        $controller->postAction($request);

        foreach ($logger->records as $record) {
            self::assertStringNotContainsString(
                'Client failed security',
                $record['message'],
                'Pre-fix regression: string is_enabled "1" must not be treated as disabled',
            );
        }
    }

    public function testIntrospectionNoAssertionRejectsBadClientSecret(): void
    {
        // Wrong secret — must reject. The crypto layer decrypts to the
        // canonical secret; a mismatch is "Client failed security".
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => self::TEST_CLIENT_ID,
            'client_secret' => 'wrong-secret',
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setClientRepository($this->buildClientRepo(rawRow: [
            'client_id' => self::TEST_CLIENT_ID,
            'is_enabled' => 1,
            'is_confidential' => 1,
            'client_secret' => 'encrypted-secret-blob',
        ]));
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willReturn('actual-secret');
        $controller->setCryptoGen($crypto);

        $response = $controller->postAction($request);

        self::assertActiveFalse($response);
        self::assertLogContains($logger, 'Client failed security');
    }

    // -----------------------------------------------------------------
    // tokenIntrospection — RFC 7662 swallow-all-errors contract
    // -----------------------------------------------------------------

    public function testIntrospectionReturnsInactiveOnInternalException(): void
    {
        // RFC 7662 contract: any internal error must surface as
        // active:false, not as a 5xx leak. JsonWebKeyParser throws
        // mid-flow; the catch at the bottom of tokenIntrospection()
        // rewrites the response.
        $request = $this->buildRequest([
            'token' => self::TEST_TOKEN,
            'client_id' => self::TEST_CLIENT_ID,
            'token_type_hint' => 'access_token',
            'client_assertion' => 'fake.jwt.signature',
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
        ]);
        $logger = new RecordingLogger();
        $controller = $this->buildController(logger: $logger);
        $controller->setJWTClientAuthenticationService($this->buildJwtAuthService(
            extractedClientId: self::TEST_CLIENT_ID,
        ));
        $controller->setClientRepository($this->buildClientRepo(rawRow: [
            'client_id' => self::TEST_CLIENT_ID,
            'is_enabled' => 1,
            'is_confidential' => 1,
        ]));
        $parser = $this->createMock(JsonWebKeyParser::class);
        $parser->method('parseAccessToken')->willThrowException(new \RuntimeException('parse exploded'));
        $controller->setJsonWebKeyParser($parser);
        $controller->setAccessTokenRepository($this->createMock(AccessTokenRepository::class));
        $controller->setRefreshTokenRepository($this->createMock(RefreshTokenRepository::class));
        $controller->setTrustedUserService($this->createMock(TrustedUserService::class));

        $response = $controller->postAction($request);

        self::assertActiveFalse($response, 'Any internal error must surface as active:false per RFC 7662');
        self::assertLogContains($logger, 'parse exploded');
    }

    // -----------------------------------------------------------------
    // Lazy-init getter error path
    // -----------------------------------------------------------------

    public function testGetJWTClientAuthenticationServiceCachesAcrossCalls(): void
    {
        // The getter was previously rebuilt fresh on every call. After
        // the refactor it lazy-caches and exposes a setter for tests
        // — pin the cache so a future regression doesn't quietly
        // reintroduce per-call construction (which discards the
        // setLogger() side effect each time).
        $controller = $this->buildController();
        $injected = $this->buildJwtAuthService();
        $controller->setJWTClientAuthenticationService($injected);

        self::assertSame($injected, $controller->getJWTClientAuthenticationService());
        self::assertSame($injected, $controller->getJWTClientAuthenticationService());
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * @param array<string, scalar|null> $body POST body params.
     */
    private function buildRequest(array $body): HttpRestRequest
    {
        // Use the static create() so the request carries a parseable URI
        // (host + path). The controller's convertRestRequestToPsrRequest()
        // routes through nyholm/psr7's Uri class, which throws on an
        // empty `http://:/`. Path-only is enough — the introspection
        // flow doesn't read the URI itself.
        $request = HttpRestRequest::create('https://example.test/oauth2/introspection', 'POST', $body);
        $sessionFactory = new MockFileSessionStorageFactory();
        $session = new Session($sessionFactory->createStorage($request));
        $session->start();
        $request->setSession($session);
        return $request;
    }

    private function buildController(?RecordingLogger $logger = null): TokenIntrospectionRestController
    {
        $coreKernel = $this->createMock(Kernel::class);
        $coreKernel->method('getEventDispatcher')->willReturn(new EventDispatcher());
        $coreKernel->method('isDev')->willReturn(true);
        $globalsBag = new OEGlobalsBag(['kernel' => $coreKernel]);

        $controller = new TokenIntrospectionRestController($globalsBag);
        $controller->setLogger($logger ?? new RecordingLogger());

        // ServerConfig is consulted lazily; pre-set with a stub so the
        // JWT-auth-service construction (when not pre-injected) doesn't
        // blow up on an empty token URL. Tests that exercise the JWT
        // path inject their own service via the setter and never reach
        // the inner construction anyway.
        $serverConfig = $this->createMock(ServerConfig::class);
        $serverConfig->method('getTokenUrl')->willReturn('https://example.test/oauth2/token');
        $serverConfig->method('getFhirUrl')->willReturn('https://example.test/fhir');
        $controller->setServerConfig($serverConfig);

        return $controller;
    }

    /**
     * @return JWTClientAuthenticationService&MockObject
     */
    private function buildJwtAuthService(?string $extractedClientId = null): JWTClientAuthenticationService
    {
        $service = $this->createMock(JWTClientAuthenticationService::class);
        $service->method('hasJWTClientAssertion')->willReturn(true);
        if ($extractedClientId !== null) {
            $service->method('extractClientIdFromJWT')->willReturn($extractedClientId);
        }
        return $service;
    }

    /**
     * @param array<string, mixed>|null $rawRow Result for getRawClientRow().
     * @return ClientRepository&MockObject
     */
    private function buildClientRepo(?array $rawRow): ClientRepository
    {
        $repo = $this->createMock(ClientRepository::class);
        $repo->method('getRawClientRow')->willReturn($rawRow);
        // Default ClientEntity for the JWT-validation step downstream
        // — tests that fail before reaching it never observe this.
        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier(self::TEST_CLIENT_ID);
        $clientEntity->setIsEnabled(true);
        $repo->method('getClientEntity')->willReturn($clientEntity);
        return $repo;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonBody(StreamInterface $body): array
    {
        $body->rewind();
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        return $decoded;
    }

    private static function assertActiveFalse(
        ResponseInterface $response,
        string $message = 'Expected RFC 7662 active:false response',
    ): void {
        self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $message);
        $body = $response->getBody();
        $body->rewind();
        $decoded = json_decode($body->getContents(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        self::assertArrayHasKey('active', $decoded);
        self::assertFalse($decoded['active'], $message);
    }

    private static function assertLogContains(RecordingLogger $logger, string $needle): void
    {
        foreach ($logger->records as $record) {
            if (str_contains($record['message'], $needle)) {
                return;
            }
        }
        self::fail("Expected a logged message containing '{$needle}'. Logged: "
            . implode(' | ', array_column($logger->records, 'message')));
    }
}

/**
 * Minimal in-memory PSR-3 logger used to assert which gate inside
 * tokenIntrospection() fired. Public because PHP < 8.4 doesn't allow
 * an inner class declaration here.
 *
 * @internal
 */
final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{level: mixed, message: string, context: array<mixed>}> */
    public array $records = [];

    /**
     * PSR-3 LoggerInterface declares the parameter as untyped `array`.
     * Mirror that exactly to satisfy contravariance — a tightened
     * generic shape (e.g. `array<string, mixed>`) would narrow the
     * accepted set and PHPStan rejects it.
     *
     * @param array<mixed> $context
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }
}
