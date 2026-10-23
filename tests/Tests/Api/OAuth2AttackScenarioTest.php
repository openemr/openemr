<?php

/**
 * OAuth2 attack-scenario regression gate suite.
 *
 * Each test method runs ONE deny-path attack and asserts the server
 * rejects it with the expected shape (status + error field, or
 * header-absence). If any one of these ever starts succeeding, a
 * regression has silently landed. The suite's job is to fail CI
 * before that regression ships.
 *
 * Covers DCR malformation, token-endpoint client-auth bypass, password
 * grant deny-paths, refresh-token misuse, bearer-token presentation
 * errors, introspection input shapes, CORS origin enforcement, and
 * information-disclosure invariants.
 *
 * Each test is self-contained: DCR + any required state is created and
 * torn down per test. No shared mutable state between tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use DateTimeImmutable;
use GuzzleHttp\Client;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256Signer;
use Lcobucci\JWT\Token\Builder as JwtTokenBuilder;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type AttackResponse array{status: int, body: string, headers: array<array<string>>}
 */
class OAuth2AttackScenarioTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        // Capture the original password-grant global BEFORE any skip path:
        // PHPUnit still runs tearDown() for a test skipped from setUp().
        // Without this, a skip here would leave the "was-set" flag false,
        // and tearDown() would then remove a value an earlier test may
        // have established, mutating global state across tests in the
        // same PHPUnit process.
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            if (getenv('CI') !== false) {
                self::fail('OAuth flow requires a real webserver (Apache/nginx) — hard failure in CI');
            }
            $this->markTestSkipped('OAuth flow requires a real webserver');
        }
        $globals->set('oauth_password_grant', 3);
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->originalPasswordGrantSettingWasSet) {
            $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
        } else {
            $globals->remove('oauth_password_grant');
            unset($GLOBALS['oauth_password_grant']);
        }
        foreach ($this->registeredClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_token` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_refresh_token` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
        }
    }

    // ----- DCR attack scenarios -----

    #[Test]
    public function testDcrRejectsNonUrlRedirectUri(): void
    {
        $resp = $this->dcr(['redirect_uris' => ['not-a-url']]);
        $this->assertRejected($resp, 'DCR must reject a redirect_uri that is not a URL');
    }

    #[Test]
    public function testDcrRejectsEmptyRedirectUrisArray(): void
    {
        $resp = $this->dcr(['redirect_uris' => []]);
        $this->assertRejected($resp, 'DCR must reject an empty redirect_uris array');
    }

    #[Test]
    public function testDcrRejectsRelativeRedirectUri(): void
    {
        $resp = $this->dcr(['redirect_uris' => ['/relative/path']]);
        $this->assertRejected($resp, 'DCR must reject a relative redirect_uri');
    }

    #[Test]
    public function testDcrRejectsWebSchemeWithoutHost(): void
    {
        // parse_url('https:relative-path') yields a scheme ('https') but no
        // host; the controller must still reject it because an http/https
        // URI without a host is not a valid callback target.
        $resp = $this->dcr(['redirect_uris' => ['https:relative-path']]);
        $this->assertRejected($resp, 'DCR must reject an http/https redirect_uri with no host');
    }

    #[Test]
    public function testDcrRejectsNonWebSchemeWithoutDot(): void
    {
        // RFC 8252 §7.1 private-use URI schemes should be a reverse domain
        // name (e.g. `com.example.app`) and therefore contain a period.
        // A non-web scheme with no period is not a valid native-app
        // callback and must be rejected.
        $resp = $this->dcr(['redirect_uris' => ['foo:/path']]);
        $this->assertRejected($resp, 'DCR must reject a non-web redirect_uri whose scheme is not a reverse-domain form');
    }

    #[Test]
    public function testDcrAcceptsRfc8252PrivateUseSchemeRedirectUri(): void
    {
        // RFC 8252 §7.1 private-use URI scheme — scheme is a reverse domain
        // name, no host component. Must be accepted so native-app clients
        // can register.
        $resp = $this->dcr([
            'redirect_uris' => ['com.openemr.attacktest:/oauth2redirect/example-provider'],
        ]);
        $this->assertSame(
            200,
            $resp['status'],
            'DCR must accept an RFC 8252 private-use scheme redirect_uri. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testDcrRejectsUnsupportedTokenEndpointAuthMethod(): void
    {
        $resp = $this->dcr(['token_endpoint_auth_method' => 'telepathic_hum']);
        $this->assertRejected($resp, 'DCR must reject an unsupported token_endpoint_auth_method value');
    }

    // ----- Token endpoint — client-auth attack scenarios -----

    #[Test]
    public function testTokenEndpointRejectsUnregisteredClient(): void
    {
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => 'nope-this-client-does-not-exist-' . bin2hex(random_bytes(4)),
            'client_secret' => 'bogus',
        ]);
        $this->assertRejected($resp, 'Token endpoint must reject an unregistered client_id');
    }

    #[Test]
    public function testTokenEndpointRejectsWrongClientSecret(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongSecret');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec . 'tamper',
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Token endpoint must reject a wrong client_secret');
    }

    #[Test]
    public function testClientCredentialsRejectsAlgNoneAssertion(): void
    {
        [$cid, $_csec] = $this->dcrConfidentialPasswordGrantClient('AlgNoneAssertion');
        $header = $this->base64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->base64url((string) json_encode([
            'iss' => $cid,
            'sub' => $cid,
            'aud' => $this->baseUrl . '/oauth2/default/token',
            'exp' => time() + 60,
            'jti' => bin2hex(random_bytes(16)),
        ]));
        $forged = $header . '.' . $payload . '.anything';
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => $cid,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $forged,
            'scope' => 'api:oemr',
        ]);
        $this->assertRejected($resp, 'client_credentials must reject an alg=none JWT client assertion');
    }

    #[Test]
    public function testClientCredentialsRejectsAssertionSignedByForeignKey(): void
    {
        [$cid, $_csec] = $this->dcrConfidentialPasswordGrantClient('ForeignKeyAssertion');
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $foreignPrivateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $signer = new RsaSha256Signer();
        $assertion = (new JwtTokenBuilder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy($cid)
            ->relatedTo($cid)
            ->permittedFor($this->baseUrl . '/oauth2/default/token')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->modify('+60 seconds'))
            ->getToken($signer, $foreignPrivateKey)
            ->toString();
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => $cid,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $assertion,
            'scope' => 'api:oemr',
        ]);
        $this->assertRejected($resp, 'client_credentials must reject a JWT client assertion signed by a key the client did not register');
    }

    // ----- Token endpoint — password grant deny-paths -----

    #[Test]
    public function testPasswordGrantRejectsWrongPassword(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongPassword');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'definitely-not-the-password',
        ]);
        $this->assertRejected($resp, 'Password grant must reject a wrong user password');
    }

    #[Test]
    public function testPasswordGrantRejectsUnknownUser(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('UnknownUser');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'this-user-does-not-exist-' . bin2hex(random_bytes(4)),
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Password grant must reject an unknown username');
    }

    #[Test]
    public function testPasswordGrantRejectsWrongUserRole(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongUserRole');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:port',
            'user_role' => 'patient',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Password grant must reject when user_role (patient) does not match the registered user (staff admin)');
    }

    #[Test]
    public function testPasswordGrantAcceptsClientSecretBasicAuth(): void
    {
        // RFC 6749 §2.3.1: `client_secret_basic` presents client
        // credentials in the Authorization header. Every other
        // password-grant test in this suite uses `client_secret_post`
        // (credentials in the form body). This locks the Basic auth
        // path so a future refactor cannot silently drop it.
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'ClientSecretBasicAccept',
            ['token_endpoint_auth_method' => 'client_secret_basic']
        );
        $resp = $this->postTokenRawWithBasicAuth($cid, $csec, [
            'grant_type' => 'password',
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertSame(
            200,
            $resp['status'],
            'Basic-auth password grant must succeed when the client registered with client_secret_basic. Body: ' . $resp['body']
        );
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertIsString($body['access_token'] ?? null, 'Response must carry a non-empty access_token');
        $this->assertNotSame('', $body['access_token'], 'access_token must not be an empty string');
    }

    #[Test]
    public function testPasswordGrantRejectsBasicAuthWithWrongSecret(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'ClientSecretBasicRejectWrong',
            ['token_endpoint_auth_method' => 'client_secret_basic']
        );
        $resp = $this->postTokenRawWithBasicAuth($cid, $csec . 'tamper', [
            'grant_type' => 'password',
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Basic-auth password grant must reject a wrong client_secret');
    }

    // ----- Authorize endpoint — response_type coverage -----

    #[Test]
    public function testAuthorizeRejectsImplicitResponseType(): void
    {
        // response_type=token selects the implicit grant per RFC 6749
        // §4.2. The implicit grant is deprecated by RFC 9700 (OAuth 2.0
        // Security BCP) §2.1.2 and OAuth 2.1 drops it entirely. OpenEMR
        // does not enable the implicit grant in its authorization
        // server, so a /authorize GET with response_type=token must be
        // rejected rather than issue a token in the redirect fragment.
        [$cid, $_csec] = $this->dcrConfidentialAuthCodeClient('ImplicitResponseType');
        $resp = $this->authorizeGet($cid, ['response_type' => 'token']);
        $this->assertAuthorizeRejected($resp, 'Authorize must reject response_type=token (implicit grant disabled)');
    }

    #[Test]
    public function testAuthorizeRejectsIdTokenResponseType(): void
    {
        // response_type=id_token is the OIDC implicit flow (OIDC Core
        // §3.2) and inherits the implicit-grant deprecation per RFC
        // 9700 §2.1.2. OpenEMR enables only the auth-code grant for
        // SMART-on-FHIR, so id_token must be rejected rather than
        // return an id_token in the fragment.
        [$cid, $_csec] = $this->dcrConfidentialAuthCodeClient('IdTokenResponseType');
        $resp = $this->authorizeGet($cid, ['response_type' => 'id_token']);
        $this->assertAuthorizeRejected($resp, 'Authorize must reject response_type=id_token (OIDC implicit flow disabled)');
    }

    #[Test]
    public function testAuthorizeRejectsUnknownResponseType(): void
    {
        // RFC 6749 §4.1.2.1 requires rejection of unrecognized
        // response_type values. "foo" matches no registered grant and
        // must be rejected rather than default to any known behavior.
        [$cid, $_csec] = $this->dcrConfidentialAuthCodeClient('UnknownResponseType');
        $resp = $this->authorizeGet($cid, ['response_type' => 'foo']);
        $this->assertAuthorizeRejected($resp, 'Authorize must reject an unknown response_type value');
    }

    #[Test]
    public function testAuthorizeRejectsEmptyResponseType(): void
    {
        // An empty response_type is a malformed request per RFC 6749
        // §4.1.1 (response_type is REQUIRED). The server must reject
        // rather than default to response_type=code.
        [$cid, $_csec] = $this->dcrConfidentialAuthCodeClient('EmptyResponseType');
        $resp = $this->authorizeGet($cid, ['response_type' => '']);
        $this->assertAuthorizeRejected($resp, 'Authorize must reject an empty response_type parameter');
    }

    #[Test]
    public function testAuthorizeRejectsMissingResponseType(): void
    {
        // RFC 6749 §4.1.1 makes response_type REQUIRED. A request that
        // omits it entirely must be rejected rather than default to
        // response_type=code.
        [$cid, $_csec] = $this->dcrConfidentialAuthCodeClient('MissingResponseType');
        $resp = $this->authorizeGet($cid, ['response_type' => null]);
        $this->assertAuthorizeRejected($resp, 'Authorize must reject a request that omits response_type entirely');
    }

    // ----- Refresh-token deny-paths -----

    #[Test]
    public function testRefreshWithMadeUpTokenIsRejected(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('MadeUpRefresh');
        $resp = $this->postTokenRaw([
            'grant_type' => 'refresh_token',
            'refresh_token' => 'completely-fabricated-token-' . bin2hex(random_bytes(16)),
            'client_id' => $cid,
            'client_secret' => $csec,
        ]);
        $this->assertRejected($resp, 'Refresh token endpoint must reject a totally made-up refresh_token value');
    }

    // ----- Bearer-token presentation attacks (on an authenticated FHIR endpoint) -----

    #[Test]
    public function testFhirRejectsAuthorizationBasicScheme(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Basic ' . base64_encode('a:b')]);
        $this->assertSame(401, $resp['status'], 'Authorization: Basic must not satisfy a Bearer-protected endpoint');
    }

    #[Test]
    public function testFhirRejectsBearerAlgNoneJwt(): void
    {
        $header = $this->base64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->base64url((string) json_encode([
            'sub' => 'admin',
            'exp' => time() + 3600,
            'scopes' => ['api:oemr', 'user/patient.read'],
        ]));
        $forged = $header . '.' . $payload . '.anything';
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer ' . $forged]);
        $this->assertSame(401, $resp['status'], 'Bearer with alg=none JWT must be rejected');
    }

    #[Test]
    public function testFhirRejectsBearerSignedByForeignKey(): void
    {
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $foreignPrivateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $signer = new RsaSha256Signer();
        $forged = (new JwtTokenBuilder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy('http://attacker.example/oauth2/default')
            ->relatedTo('admin')
            ->permittedFor('attacker-client-id')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->modify('+1 hour'))
            ->withClaim('scopes', ['api:oemr', 'user/patient.read'])
            ->getToken($signer, $foreignPrivateKey)
            ->toString();
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer ' . $forged]);
        $this->assertSame(401, $resp['status'], 'Bearer JWT signed by a key the server does not trust must be rejected');
    }

    #[Test]
    public function testFhirRejectsBearerMalformedJwt(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer not.a.jwt']);
        $this->assertSame(401, $resp['status'], 'Bearer with a malformed JWT must be rejected');
    }

    #[Test]
    public function testFhirRejectsEmptyAuthorizationHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => '']);
        $this->assertSame(401, $resp['status'], 'Bearer with an empty Authorization header value must be rejected');
    }

    #[Test]
    public function testFhirRejectsMissingAuthorizationHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', []);
        $this->assertSame(401, $resp['status'], 'Request to an auth-gated endpoint with no Authorization header must be rejected');
    }

    // ----- Introspection input-shape attacks -----

    #[Test]
    public function testIntrospectionTreatsMadeUpTokenAsInactive(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('IntrospectMadeUp');
        $resp = $this->rawPostForm('/oauth2/default/introspect', [
            'token' => 'completely-fabricated-value-' . bin2hex(random_bytes(16)),
            'client_id' => $cid,
            'client_secret' => $csec,
        ]);
        $this->assertSame(200, $resp['status'], 'Introspection with a made-up token should return 200 per RFC 7662');
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertFalse($body['active'] ?? null, 'Introspection must report active:false for an unknown token');
    }

    #[Test]
    public function testIntrospectionWithoutClientIdReturnsInactive(): void
    {
        $resp = $this->rawPostForm('/oauth2/default/introspect', [
            'token' => 'anything',
        ]);
        $this->assertSame(200, $resp['status'], 'Introspection without client_id should still return 200');
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertFalse($body['active'] ?? null, 'Introspection without client_id must report active:false');
    }

    #[Test]
    public function testIntrospectionReportsInactiveWhenAuthenticatingClientDidNotMintTheToken(): void
    {
        // RFC 7662 lets the introspection endpoint treat a token not
        // issued to the authenticating client as if it were unknown.
        // Prevents a confidential client from probing other clients'
        // tokens by presenting them for introspection with its own
        // credentials.
        [$cidA, $csecA] = $this->dcrConfidentialPasswordGrantClient('IntrospectCrossClientMinter');
        [$cidB, $csecB] = $this->dcrConfidentialPasswordGrantClient('IntrospectCrossClientProber');
        $tokens = $this->passwordGrantForTest($cidA, $csecA, 'openid api:oemr');

        // Client B presents client A's access token under B's own
        // client credentials.
        $resp = $this->rawPostForm('/oauth2/default/introspect', [
            'client_id' => $cidB,
            'client_secret' => $csecB,
            'token' => $tokens['access_token'],
        ]);
        $this->assertSame(
            200,
            $resp['status'],
            'Introspection probing another client\'s token must still return 200 per RFC 7662 shape. Body: ' . $resp['body']
        );
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertFalse(
            $body['active'] ?? null,
            'Introspection must report active=false for a token issued to a different client. Body: ' . $resp['body']
        );
    }

    // ----- CORS enforcement -----

    #[Test]
    public function testCorsPreflightFromDisallowedOriginEmitsNoAllowOriginHeader(): void
    {
        $resp = $this->rawOptions('/apis/default/fhir/Patient', [
            'Origin' => 'https://attacker.example',
            'Access-Control-Request-Method' => 'POST',
        ]);
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Preflight from a disallowed origin must not reflect the Origin into Access-Control-Allow-Origin. Received headers: '
            . json_encode(array_keys($resp['headers']))
        );
    }

    #[Test]
    public function testCorsResponseFromDisallowedOriginEmitsNoAllowOriginHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/version', ['Origin' => 'https://attacker.example']);
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Response from a disallowed origin must not reflect the Origin into Access-Control-Allow-Origin. Received headers: '
            . json_encode(array_keys($resp['headers']))
        );
    }

    #[Test]
    public function testCorsResponseEmitsAllowOriginForBearerAuthFromTokensOwnClientRegisteredOrigin(): void
    {
        // Legitimate flow: a SMART app presents its own token, from the
        // origin its own client registered. Response must emit
        // Access-Control-Allow-Origin so the app's JS can read the body.
        $smartAppOrigin = 'https://smart-app-regression.example';
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'SmartAppOwnOrigin',
            ['redirect_uris' => [$smartAppOrigin . '/cb']]
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:oemr');

        $resp = $this->rawGet('/apis/default/api/version', [
            'Origin' => $smartAppOrigin,
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ]);
        $this->assertSame(200, $resp['status'], 'Bearer-auth GET must succeed. Body: ' . $resp['body']);
        $headers = array_change_key_case($resp['headers']);
        $this->assertArrayHasKey(
            'access-control-allow-origin',
            $headers,
            'Legitimate bearer-auth response from the token-owning client\'s registered origin must emit Allow-Origin'
        );
        $this->assertContains(
            $smartAppOrigin,
            $headers['access-control-allow-origin'],
            'Allow-Origin must echo the request Origin exactly'
        );
    }

    #[Test]
    public function testCorsResponseDoesNotEmitAllowOriginForOriginRegisteredUnderDifferentClient(): void
    {
        // The core per-client scoping gate. Two separately-registered
        // clients: the "victim" SMART app whose redirect_uri is
        // smart-app-regression.example, and an attacker-registered
        // client whose redirect_uri is attacker-regression-cors.example.
        // Both origins land in the global union. A request bearing the
        // victim app's token with Origin: attacker's-origin must NOT
        // receive Allow-Origin -- an attacker's origin cannot be
        // paired with a different client's token in a response.
        $smartAppOrigin = 'https://smart-app-regression-victim.example';
        $attackerOrigin = 'https://attacker-regression-cors.example';
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'VictimApp',
            ['redirect_uris' => [$smartAppOrigin . '/cb']]
        );
        // Attacker's DCR -- unauth per RFC 7591, by design.
        $this->dcr([
            'client_name' => 'OAuth2AttackScenarioTest-AttackerCorsOrigin-' . bin2hex(random_bytes(3)),
            'redirect_uris' => [$attackerOrigin . '/cb'],
            'scope' => 'openid api:oemr',
        ]);

        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:oemr');
        $resp = $this->rawGet('/apis/default/api/version', [
            'Origin' => $attackerOrigin,
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ]);
        $this->assertSame(
            200,
            $resp['status'],
            'Bearer auth itself must succeed; the gate is response-side Allow-Origin, not auth. Body: ' . $resp['body']
        );
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Allow-Origin must not reflect an origin registered under a DIFFERENT OAuth2 client. Received headers: '
            . json_encode(array_keys($resp['headers']))
        );
    }

    #[Test]
    public function testCorsResponseDoesNotEmitAllowOriginForRegisteredOriginOnUnauthenticatedRequest(): void
    {
        // Register a client so its redirect_uri's origin lands in the
        // global union. Then hit a protected endpoint WITHOUT a bearer
        // token. The pre-fix behaviour would reflect the registered
        // origin into Allow-Origin on the 401 response, which was a
        // reflection oracle if the request also carried a session
        // cookie. The per-client gate keys off clientId, which is unset
        // for unauthenticated requests, so Allow-Origin is suppressed.
        $origin = 'https://registered-but-unauth-cors.example';
        $this->dcr([
            'client_name' => 'OAuth2AttackScenarioTest-RegisteredUnauth-' . bin2hex(random_bytes(3)),
            'redirect_uris' => [$origin . '/cb'],
            'scope' => 'openid',
        ]);

        $resp = $this->rawGet('/apis/default/api/version', ['Origin' => $origin]);
        $this->assertSame(
            401,
            $resp['status'],
            'Unauthenticated request to /api/version must still be rejected with 401'
        );
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Allow-Origin must not be emitted on unauthenticated responses even if the Origin is in the global union'
        );
    }

    #[Test]
    public function testCorsResponseMatchesAnyOfClientsMultipleRegisteredOrigins(): void
    {
        // oauth_clients.redirect_uri stores one or more URIs separated
        // by "|". A client with two redirect_uris must receive
        // Allow-Origin for requests from EITHER origin, as long as the
        // request is bearer-authenticated under that same client's
        // token. Exercises the explode('|', ...) path in
        // CORSAllowedOriginRegistry::getOriginsForClient().
        $firstOrigin = 'https://multi-origin-first.example';
        $secondOrigin = 'https://multi-origin-second.example';
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'MultiOriginClient',
            ['redirect_uris' => [$firstOrigin . '/cb', $secondOrigin . '/cb']]
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:oemr');

        foreach ([$firstOrigin, $secondOrigin] as $origin) {
            $resp = $this->rawGet('/apis/default/api/version', [
                'Origin' => $origin,
                'Authorization' => 'Bearer ' . $tokens['access_token'],
            ]);
            $this->assertSame(200, $resp['status'], "Bearer-auth GET from $origin must succeed. Body: " . $resp['body']);
            $headers = array_change_key_case($resp['headers']);
            $this->assertArrayHasKey(
                'access-control-allow-origin',
                $headers,
                "Allow-Origin must be emitted for request from $origin — client registered both origins"
            );
            $this->assertContains(
                $origin,
                $headers['access-control-allow-origin'],
                "Allow-Origin for request from $origin must echo that exact origin"
            );
        }
    }

    #[Test]
    public function testCorsResponseDoesNotEmitAllowOriginWhenClientIsDisabled(): void
    {
        // Admin-disabled client with a still-live token: the Bearer
        // strategy checks $client->isEnabled() during authorizeRequest
        // and throws accessDenied before setting the clientId request
        // attribute. The CORS listener therefore never consults
        // getOriginsForClient at all -- clientId is unset, so the
        // "clientId must be a non-empty string" branch in
        // onKernelResponse short-circuits before the registry lookup.
        // Response is 401 and no Allow-Origin header is emitted,
        // which is what prevents the attacker's JS from reading the
        // 401 body cross-origin.
        $origin = 'https://disabled-client-origin.example';
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'DisabledClient',
            ['redirect_uris' => [$origin . '/cb']]
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:oemr');
        // Administratively disable the client.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 0 WHERE `client_id` = ?',
            [$cid]
        );

        $resp = $this->rawGet('/apis/default/api/version', [
            'Origin' => $origin,
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ]);
        $this->assertSame(
            401,
            $resp['status'],
            'Bearer strategy must reject a token whose client is disabled. Body: ' . $resp['body']
        );
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Allow-Origin must not be emitted for a request whose client is disabled upstream of the CORS gate'
        );
    }

    // ----- Information-disclosure invariants -----

    #[Test]
    public function testDcrResponseContainsNoPrivateKeyMaterial(): void
    {
        [$cid, $csec, $raw] = $this->dcrFull('NoPrivateKeyLeak');
        $this->assertStringNotContainsStringIgnoringCase('BEGIN PRIVATE KEY', $raw);
        $this->assertStringNotContainsStringIgnoringCase('BEGIN RSA PRIVATE KEY', $raw);
        $this->assertStringNotContainsStringIgnoringCase('BEGIN EC PRIVATE KEY', $raw);
        // Negative control: the client_secret value is in the response (so this
        // test is not meaningless) but is itself not a private key.
        $this->assertStringContainsString($csec, $raw);
    }

    #[Test]
    public function testDiscoveryEndpointAdvertisesOnlyPublicEndpoints(): void
    {
        $resp = $this->rawGet('/oauth2/default/.well-known/openid-configuration', []);
        $this->assertSame(200, $resp['status']);
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $flat = (string) json_encode($body);
        // These are server-internal paths that must not appear in discovery.
        foreach (['/interface/', '/admin/', '/library/', '/sites/default/documents/'] as $forbiddenFragment) {
            $this->assertStringNotContainsString(
                $forbiddenFragment,
                $flat,
                "Discovery must not advertise an internal path (`$forbiddenFragment`)"
            );
        }
    }

    #[Test]
    public function testJwkEndpointPublishesOnlyPublicKeys(): void
    {
        $resp = $this->rawGet('/oauth2/default/jwk', []);
        $this->assertSame(200, $resp['status']);
        $decoded = json_decode($resp['body'], true);
        $this->assertIsArray($decoded, 'JWK response must be valid JSON');
        $this->assertArrayHasKey('keys', $decoded);
        $this->assertIsArray($decoded['keys']);
        // RFC 7518 §6 defines these as the private fields for RSA and EC keys.
        // Checking the decoded structure (rather than a substring match on the
        // body) catches whitespace variants like `"d" :` and private fields
        // whose names happen to collide with substrings in other field values.
        $privateFields = ['d', 'p', 'q', 'dp', 'dq', 'qi'];
        foreach ($decoded['keys'] as $index => $key) {
            $this->assertIsArray($key, "JWK entry $index must be an object");
            foreach ($privateFields as $privateField) {
                $this->assertArrayNotHasKey(
                    $privateField,
                    $key,
                    "JWK entry $index must not expose private key field `$privateField`"
                );
            }
        }
        $this->assertStringNotContainsStringIgnoringCase('BEGIN PRIVATE KEY', $resp['body']);
    }

    #[Test]
    public function testTokenEndpointErrorForMissingGrantTypeDoesNotLeakStack(): void
    {
        $resp = $this->rawPostForm('/oauth2/default/token', [
            'client_id' => 'anything',
        ]);
        $this->assertGreaterThanOrEqual(
            400,
            $resp['status'],
            'Token endpoint must reject a request with no grant_type'
        );
        $this->assertLessThan(
            500,
            $resp['status'],
            'Rejection must be a client-error 4xx, not a server failure that happens to satisfy the "not 200" check'
        );
        $this->assertStringNotContainsString(
            '/vendor/',
            $resp['body'],
            'Token endpoint 4xx responses must not expose vendor stack paths'
        );
        $this->assertStringNotContainsString(
            '/src/RestControllers/',
            $resp['body'],
            'Token endpoint 4xx responses must not expose server source paths'
        );
    }

    #[Test]
    public function testVersionEndpointRequiresAuthentication(): void
    {
        // The /api/version OpenAPI annotation declares openemr_auth as a
        // required security scheme and documents a 401 response. The runtime
        // must match that contract rather than silently returning the
        // installed-version row to any unauthenticated caller.
        $resp = $this->rawGet('/apis/default/api/version', []);
        $this->assertSame(
            401,
            $resp['status'],
            'GET /apis/default/api/version must require authentication to match its OpenAPI contract'
        );
    }

    #[Test]
    public function testProductEndpointRequiresAuthentication(): void
    {
        // Same contract as /api/version — the OpenAPI annotation declares
        // openemr_auth as required and 401 as a documented response.
        $resp = $this->rawGet('/apis/default/api/product', []);
        $this->assertSame(
            401,
            $resp['status'],
            'GET /apis/default/api/product must require authentication to match its OpenAPI contract'
        );
    }

    // ----- Bulk FHIR scope-downgrade regression gates -----

    #[Test]
    public function testPatientExportDeniesNarrowerScopeToken(): void
    {
        // A token holding user/Patient.read but not user/Patient.$export
        // must not authorize the bulk Patient export. Locks the
        // dispatcher's operation-level scope enforcement
        // (ScopeEntity::containsScope line 324 rejects on operation
        // mismatch).
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'PatientExportScopeDowngrade',
            ['scope' => 'openid api:fhir user/Patient.read']
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:fhir user/Patient.read');
        $resp = $this->rawGet('/apis/default/fhir/Patient/$export', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
            'Accept' => 'application/fhir+ndjson',
            'Prefer' => 'respond-async',
        ]);
        $this->assertSame(
            401,
            $resp['status'],
            'Patient $export must deny a token lacking the $export operation scope. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testGroupExportDeniesNarrowerScopeToken(): void
    {
        // A token holding user/Group.read but not user/Group.$export
        // must not authorize bulk Group export.
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'GroupExportScopeDowngrade',
            ['scope' => 'openid api:fhir user/Group.read']
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:fhir user/Group.read');
        $resp = $this->rawGet('/apis/default/fhir/Group/anything/$export', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
            'Accept' => 'application/fhir+ndjson',
            'Prefer' => 'respond-async',
        ]);
        $this->assertSame(
            401,
            $resp['status'],
            'Group $export must deny a token lacking the $export operation scope. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testSystemExportDeniesNarrowerScopeToken(): void
    {
        // A token holding user/Patient.read (a narrow resource scope)
        // must not authorize the system-wide /fhir/$export, which the
        // dispatcher resolves to a wildcard-resource $export permission.
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'SystemExportScopeDowngrade',
            ['scope' => 'openid api:fhir user/Patient.read']
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:fhir user/Patient.read');
        $resp = $this->rawGet('/apis/default/fhir/$export', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
            'Accept' => 'application/fhir+ndjson',
            'Prefer' => 'respond-async',
        ]);
        $this->assertSame(
            401,
            $resp['status'],
            'System-wide $export must deny a token lacking the $export operation scope. Body: ' . $resp['body']
        );
    }

    // ----- Cross-scope lateral escalation regression gates -----

    #[Test]
    public function testUserRoleTokenCannotReachPortalRoute(): void
    {
        // A users-role bearer token holds user/* scopes. Portal routes
        // (/apis/{site}/portal/*) are built with patient/* scope lookups
        // under the dispatcher's role-driven logic. The user-role token
        // must not satisfy the portal route's scope check — the
        // dispatcher derives scopeType from user_role, so there is no
        // path by which a user-role token's user/* scopes could be
        // consulted against a portal patient-role route.
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'CrossScopeUserToPortal',
            ['scope' => 'openid api:oemr api:port user/Patient.read']
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:port user/Patient.read');
        $resp = $this->rawGet('/apis/default/portal/patient', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
        ]);
        // 401 (unauthenticated-shape rejection by the dispatcher), 403
        // (authenticated but lacking cross-scope permission), or 404
        // (route unresolved under this webserver config — nginx test
        // env doesn't route the portal api prefix to PHP) are all
        // correct deny shapes. The invariant locked here is that the
        // token's user/* scopes don't satisfy a patient/* route — not
        // the specific HTTP code.
        $this->assertContains(
            $resp['status'],
            [401, 403, 404],
            'User-role token must not reach a portal route. Body: ' . $resp['body']
        );
    }

    #[Test]
    public function testPatientRoleTokenCannotReachStandardApiRoute(): void
    {
        // A patient-role bearer token holds patient/* scopes. Standard
        // /api/* routes require the dispatcher to build user/* lookups,
        // which won't match patient/* scopes. The dispatcher's role-
        // driven scope-type derivation makes this architecturally
        // impossible; this test locks that invariant.
        //
        // Build a portal patient fixture (needed because the password
        // grant with user_role=patient requires it) and mint a
        // patient-role token.
        $portalFixtures = new \OpenEMR\Tests\Fixtures\PortalPatientFixtureManager();
        $fixture = $portalFixtures->installPortalPatient(
            portalLoginUsername: 'crossscope-patient-' . bin2hex(random_bytes(4)),
            plainPassword: 'CrossScopePortalPassword1!',
            email: 'crossscope-patient-' . bin2hex(random_bytes(4)) . '@test.example'
        );
        try {
            // Register with api:oemr included so the token passes the
            // standard-api bearer-gate (which requires api:oemr); the
            // deny we want to lock is the dispatcher's role-driven
            // scope-type check on /api/facility, not the bearer gate.
            [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
                'CrossScopePatientToStandardApi',
                ['scope' => 'openid api:oemr api:port patient/Patient.read']
            );
            $tokenResp = $this->postTokenRaw([
                'grant_type' => 'password',
                'client_id' => $cid,
                'client_secret' => $csec,
                'scope' => 'openid api:oemr api:port patient/Patient.read',
                'user_role' => 'patient',
                'username' => $fixture['portal_login_username'],
                'password' => $fixture['plain_password'],
                'email' => $fixture['email'],
            ]);
            $this->assertSame(200, $tokenResp['status'], 'Patient-role token must mint. Body: ' . $tokenResp['body']);
            $body = json_decode($tokenResp['body'], true);
            $this->assertIsArray($body);
            $accessToken = $body['access_token'] ?? null;
            $this->assertIsString($accessToken);

            // Patient-role token tries /api/facility (a standard-api
            // route requiring user-role authorization).
            $resp = $this->rawGet('/apis/default/api/facility', [
                'Authorization' => 'Bearer ' . $accessToken,
            ]);
            // 401 (unauthenticated-shape) or 403 (authenticated but
            // lacking cross-scope permission) are both correct deny
            // shapes.
            $this->assertContains(
                $resp['status'],
                [401, 403],
                'Patient-role token must not reach a standard /api route. Body: ' . $resp['body']
            );
        } finally {
            $portalFixtures->removePortalPatientFixtures();
        }
    }

    // ----- CORS wildcard+credentials invariant -----

    #[Test]
    public function testCorsResponseDoesNotCombineWildcardAllowOriginWithCredentials(): void
    {
        // CORS forbids `Access-Control-Allow-Origin: *` paired with
        // `Access-Control-Allow-Credentials: true`. The response-side
        // listener emits the request's Origin (never `*`) and only when
        // the authenticated client's scope allows it. The semgrep rule
        // openemr-oauth2-cors-reflect-origin-without-allowlist blocks
        // source-side introduction. This test adds a runtime invariant:
        // no response ever emits both headers in that forbidden shape.
        //
        // Drive two request shapes so the listener actually runs:
        //   1. Unauthenticated request with an Origin — covers the
        //      pre-auth path (CORS listener should omit both headers).
        //   2. Authenticated bearer-token request from a client whose
        //      own registered origin is used — covers the per-client
        //      scoped path.
        // Path 1: unauthenticated.
        $resp1 = $this->rawGet('/apis/default/fhir/metadata', [
            'Origin' => 'https://probe.example',
        ]);
        $this->assertNoCorsWildcardWithCredentials($resp1, 'CORS invariant (unauthenticated)');

        // Path 2: authenticated bearer from a client whose registered
        // redirect_uri base matches the Origin header below.
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient(
            'CorsWildcardCredentialsInvariant',
            [
                'redirect_uris' => ['https://probe.example/cb'],
                'scope' => 'openid api:oemr user/Patient.read',
            ]
        );
        $tokens = $this->passwordGrantForTest($cid, $csec, 'openid api:oemr user/Patient.read');
        $resp2 = $this->rawGet('/apis/default/fhir/metadata', [
            'Authorization' => 'Bearer ' . $tokens['access_token'],
            'Origin' => 'https://probe.example',
        ]);
        $this->assertNoCorsWildcardWithCredentials($resp2, 'CORS invariant (authenticated bearer)');
    }

    /**
     * @param AttackResponse $resp
     */
    private function assertNoCorsWildcardWithCredentials(array $resp, string $context): void
    {
        $headers = array_change_key_case($resp['headers']);
        $allowOrigin = $headers['access-control-allow-origin'] ?? [];
        $allowCredentials = $headers['access-control-allow-credentials'] ?? [];
        $hasWildcardOrigin = in_array('*', $allowOrigin, true);
        $hasCredentialsTrue = in_array(
            'true',
            array_map(strtolower(...), array_map(strval(...), $allowCredentials)),
            true
        );
        $this->assertFalse(
            $hasWildcardOrigin && $hasCredentialsTrue,
            $context . ': Access-Control-Allow-Origin: * must never pair with '
                . 'Access-Control-Allow-Credentials: true. Headers: ' . json_encode($headers)
        );
    }

    // ----- Shared helpers -----

    /**
     * @param array<string, mixed> $overrides
     * @return AttackResponse
     */
    private function dcr(array $overrides = []): array
    {
        $payload = array_merge([
            'application_type' => 'private',
            'redirect_uris' => ['https://attacker-regression.example/cb'],
            'client_name' => 'OAuth2AttackScenarioTest-' . bin2hex(random_bytes(3)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['attack-regression@test.example'],
            'scope' => 'openid',
        ], $overrides);
        $http = $this->buildClient();
        $response = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        if (is_array($data) && isset($data['client_id']) && is_string($data['client_id'])) {
            $this->registeredClientIds[] = $data['client_id'];
        }
        return [
            'status' => $response->getStatusCode(),
            'body' => $body,
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * DCR + enable password grant + fix is_enabled=1 (admin-UI equivalent).
     *
     * @param array<string, mixed> $overrides
     * @return array{0: string, 1: string}
     */
    private function dcrConfidentialPasswordGrantClient(string $tag, array $overrides = []): array
    {
        [$cid, $csec, $_body] = $this->dcrFull($tag, $overrides);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $cid]
        );
        return [$cid, $csec];
    }

    /**
     * DCR + enable authorization_code grant + is_enabled=1. Suitable
     * for /authorize endpoint deny-path tests.
     *
     * @param array<string, mixed> $overrides
     * @return array{0: string, 1: string}
     */
    private function dcrConfidentialAuthCodeClient(string $tag, array $overrides = []): array
    {
        [$cid, $csec, $_body] = $this->dcrFull($tag, $overrides);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['authorization_code', $cid]
        );
        return [$cid, $csec];
    }

    /**
     * @return array{access_token: string}
     */
    private function passwordGrantForTest(string $clientId, string $clientSecret, string $scope): array
    {
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertSame(200, $resp['status'], 'password-grant must succeed. Body: ' . $resp['body']);
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertIsString($body['access_token'] ?? null);
        $this->assertNotSame(
            '',
            $body['access_token'],
            'access_token must not be an empty string -- an empty token would make downstream deny-path tests pass vacuously'
        );
        return ['access_token' => $body['access_token']];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array{0: string, 1: string, 2: string}
     */
    private function dcrFull(string $tag, array $overrides = []): array
    {
        $resp = $this->dcr(array_merge([
            'client_name' => 'OAuth2AttackScenarioTest-' . $tag . '-' . bin2hex(random_bytes(3)),
            'scope' => 'openid api:oemr offline_access',
        ], $overrides));
        $this->assertSame(200, $resp['status'], 'DCR for the attack-suite fixture must succeed. Body: ' . $resp['body']);
        $data = json_decode($resp['body'], true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        return [$data['client_id'], $data['client_secret'], $resp['body']];
    }

    /**
     * @param array<string, string> $form
     * @return AttackResponse
     */
    private function postTokenRaw(array $form): array
    {
        return $this->rawPostForm('/oauth2/default/token', $form);
    }

    /**
     * @param array<string, string> $form
     * @return AttackResponse
     */
    private function postTokenRawWithBasicAuth(string $clientId, string $clientSecret, array $form): array
    {
        $response = $this->buildClient()->post($this->baseUrl . '/oauth2/default/token', [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($clientId . ':' . $clientSecret),
            ],
            'form_params' => $form,
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, string> $form
     * @return AttackResponse
     */
    private function rawPostForm(string $path, array $form): array
    {
        $response = $this->buildClient()->post($this->baseUrl . $path, [
            'form_params' => $form,
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * GET /oauth2/default/authorize with a baseline set of valid
     * parameters plus caller-supplied overrides. A null value for a
     * key omits that parameter entirely from the query string —
     * useful for exercising "missing response_type" vs
     * "response_type=".
     *
     * No redirect-following: the deny-path assertions compare the
     * status and body of the immediate response, not what the server
     * would redirect to.
     *
     * @param array<string, string|null> $overrides
     * @return AttackResponse
     */
    private function authorizeGet(string $clientId, array $overrides = []): array
    {
        $params = array_merge([
            'client_id' => $clientId,
            'redirect_uri' => 'https://client.example/cb',
            'response_type' => 'code',
            'scope' => 'openid api:oemr',
            'state' => 'attack-' . bin2hex(random_bytes(4)),
            'nonce' => 'n-' . bin2hex(random_bytes(4)),
        ], $overrides);
        $params = array_filter($params, static fn ($v): bool => $v !== null);
        $response = $this->buildClient()->get(
            $this->baseUrl . '/oauth2/default/authorize?' . http_build_query($params),
            ['allow_redirects' => false]
        );
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * Assert an /authorize deny-path response. The authorize endpoint
     * may reject either as a direct 4xx with a JSON error body, or as
     * a 302 redirect to the client's redirect_uri with error+
     * error_description parameters (RFC 6749 §4.1.2.1). Both shapes
     * are acceptable — the invariant is "server did not proceed to
     * the login/consent flow for the bad response_type."
     *
     * @param AttackResponse $response
     */
    private function assertAuthorizeRejected(array $response, string $context): void
    {
        $status = $response['status'];
        if ($status >= 300 && $status < 400) {
            $location = $response['headers']['Location'][0] ?? '';
            $this->assertStringContainsString(
                'error=',
                (string) parse_url($location, PHP_URL_QUERY),
                $context . ' — 3xx redirect must carry an `error` query parameter. Location: ' . $location
            );
            return;
        }
        $this->assertGreaterThanOrEqual(400, $status, $context . ' — status ' . $status . ' body: ' . $response['body']);
        $this->assertLessThan(500, $status, $context . ' — status ' . $status . ' body: ' . $response['body']);
        $body = json_decode($response['body'], true);
        $this->assertIsArray($body, $context . ' — response body is not JSON: ' . $response['body']);
        $this->assertArrayHasKey('error', $body, $context . ' — response body has no `error` field: ' . $response['body']);
    }

    /**
     * @param array<string, string> $headers
     * @return AttackResponse
     */
    private function rawGet(string $path, array $headers): array
    {
        $response = $this->buildClient()->get($this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return AttackResponse
     */
    private function rawOptions(string $path, array $headers): array
    {
        $response = $this->buildClient()->request('OPTIONS', $this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * Asserts a response has an OAuth2 rejection shape: 4xx + JSON body
     * with an `error` field. Both halves matter: a 404 would also pass
     * `assertNotSame(200, …)` but masks a broken endpoint.
     *
     * @param AttackResponse $response
     */
    private function assertRejected(array $response, string $context): void
    {
        $this->assertGreaterThanOrEqual(400, $response['status'], $context . ' — status ' . $response['status'] . ' body: ' . $response['body']);
        $this->assertLessThan(500, $response['status'], $context . ' — status ' . $response['status'] . ' body: ' . $response['body']);
        $body = json_decode($response['body'], true);
        $this->assertIsArray($body, $context . ' — response body is not JSON: ' . $response['body']);
        $this->assertArrayHasKey('error', $body, $context . ' — response body has no `error` field: ' . $response['body']);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }
}
