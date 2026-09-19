<?php

/**
 * Isolated tests for OIDC relying-party helpers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Tamir Suliman <279790+allamiro@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OidcRp;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OidcRp\OidcIdTokenClaims;
use OpenEMR\Common\Auth\OidcRp\OidcIdTokenValidator;
use OpenEMR\Common\Auth\OidcRp\OidcIssuerUrl;
use OpenEMR\Common\Auth\OidcRp\OidcLoginService;
use OpenEMR\Common\Auth\OidcRp\OidcPkce;
use OpenEMR\Common\Auth\OidcRp\OidcProviderMetadata;
use OpenEMR\Common\Auth\OidcRp\OidcRpClient;
use OpenEMR\Common\Auth\OidcRp\OidcRpException;
use OpenEMR\Common\Auth\OidcRp\OidcRpSettings;
use OpenEMR\Common\Utils\HttpUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class OidcRpIsolatedTest extends TestCase
{
    #[Test]
    public function pkceVerifierIsNotTheChallenge(): void
    {
        $pkce = OidcPkce::create();
        $this->assertNotSame($pkce->verifier, $pkce->challenge);
        $this->assertSame(
            HttpUtils::base64url_encode(hash('sha256', $pkce->verifier, true)),
            $pkce->challenge,
        );
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unsafeIssuerProvider(): array
    {
        return [
            'metadata host' => ['https://metadata.google.internal/realms/openemr', false],
            'link local' => ['https://169.254.169.254/latest/meta-data', false],
            'ipv6 link local' => ['https://[fe80::1]/realms/openemr', false],
            'http without flag' => ['http://keycloak.example/realms/openemr', false],
            'http non-loopback with flag' => ['http://keycloak.example/realms/openemr', true],
            'ftp' => ['ftp://idp.example/realms/openemr', false],
            'userinfo' => ['https://user:pass@idp.example/realms/openemr', false],
        ];
    }

    #[Test]
    #[DataProvider('unsafeIssuerProvider')]
    public function issuerUrlRejectsUnsafeValues(string $url, bool $allowHttp): void
    {
        $this->expectException(OidcRpException::class);
        OidcIssuerUrl::assertSafe($url, $allowHttp);
    }

    #[Test]
    public function issuerUrlAllowsHttpWhenEnabled(): void
    {
        OidcIssuerUrl::assertSafe('http://localhost:8480/realms/openemr', true);
        OidcIssuerUrl::assertSafe('http://127.0.0.1:8480/realms/openemr', true);
        $this->assertSame('localhost', OidcIssuerUrl::hostOf('http://localhost:8480/realms/openemr'));
    }

    #[Test]
    public function discoveryIssuerMustMatch(): void
    {
        $this->expectException(OidcRpException::class);
        OidcProviderMetadata::fromDiscoveryDocument(
            [
                'issuer' => 'https://other.example/realms/openemr',
                'authorization_endpoint' => 'https://idp.example/auth',
                'token_endpoint' => 'https://idp.example/token',
                'jwks_uri' => 'https://idp.example/jwks',
            ],
            'https://idp.example/realms/openemr',
        );
    }

    #[Test]
    public function settingsRefuseAdministratorAclForNewUsers(): void
    {
        $settings = $this->settings(newUserAcl: 'Administrators');
        $this->assertSame('Clinicians', $settings->newUserAcl);
        $emergency = $this->settings(newUserAcl: 'Emergency Login');
        $this->assertSame('Clinicians', $emergency->newUserAcl);
        $empty = $this->settings(newUserAcl: '', newUserGroup: '');
        $this->assertSame('Clinicians', $empty->newUserAcl);
        $this->assertSame('Default', $empty->newUserGroup);
        $physicians = $this->settings(newUserAcl: 'Physicians', newUserGroup: 'Default');
        $this->assertSame('Physicians', $physicians->newUserAcl);
        $this->assertFalse($this->settings()->createUsers);
        $this->assertTrue($this->settings()->isReady());
    }

    #[Test]
    public function scopesAlwaysIncludeOpenid(): void
    {
        $this->assertSame(['openid', 'profile', 'email'], OidcRpSettings::normalizeScopes(''));
        $this->assertSame(['openid', 'profile', 'email'], OidcRpSettings::normalizeScopes('profile email'));
        $this->assertSame(['openid', 'profile'], OidcRpSettings::normalizeScopes('openid profile'));
        $this->assertSame(['openid', 'profile', 'email'], OidcRpSettings::normalizeScopes('openid profile email openid'));
    }

    #[Test]
    public function usernameClaimFallsBackToPreferredThenEmail(): void
    {
        $claims = new OidcIdTokenClaims(
            issuer: 'https://idp.example/realms/openemr',
            subject: 'abc',
            email: 'doc@example.com',
            emailVerified: true,
            preferredUsername: 'physician',
            givenName: 'Pat',
            familyName: 'Example',
            name: 'Pat Example',
            raw: ['custom_login' => 'pat.example'],
        );
        $this->assertSame('pat.example', $claims->username('custom_login'));
        $this->assertSame('physician', $claims->username('missing'));
        $this->assertSame('Pat', $claims->firstName());
        $this->assertSame('Example', $claims->lastName());
    }

    #[Test]
    public function usernameDoesNotFallBackToUnverifiedEmail(): void
    {
        $claims = new OidcIdTokenClaims(
            issuer: 'https://idp.example/realms/openemr',
            subject: 'abc',
            email: 'doc@example.com',
            emailVerified: false,
            preferredUsername: '',
            givenName: 'Pat',
            familyName: 'Example',
            name: 'Pat Example',
            raw: [],
        );
        $this->assertSame('', $claims->username('missing'));
    }

    #[Test]
    public function usernameFallsBackToVerifiedEmail(): void
    {
        $claims = new OidcIdTokenClaims(
            issuer: 'https://idp.example/realms/openemr',
            subject: 'abc',
            email: 'doc@example.com',
            emailVerified: true,
            preferredUsername: '',
            givenName: 'Pat',
            familyName: 'Example',
            name: 'Pat Example',
            raw: [],
        );
        $this->assertSame('doc@example.com', $claims->username('missing'));
    }

    #[Test]
    public function explicitlyConfiguredEmailUsernameMustBeVerified(): void
    {
        $claims = new OidcIdTokenClaims(
            issuer: 'https://idp.example',
            subject: 'user-1',
            email: 'admin@example.com',
            emailVerified: false,
            preferredUsername: 'other-user',
            givenName: '',
            familyName: '',
            name: '',
            raw: ['email' => 'admin@example.com'],
        );
        $this->expectException(OidcRpException::class);
        $claims->username('email');
    }

    #[Test]
    public function pendingLoginCannotNavigateAroundLocalMfa(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('site_id', 'clinic one');
        $script = dirname(__DIR__, 6) . '/interface/patient_file/summary/demographics.php';
        $this->assertNull(OidcLoginService::pendingLoginRedirect($session, $script, '/openemr'));
        $session->set(OidcRpSettings::SESSION_NEW_LOGIN, true);
        $this->assertSame(
            '/openemr/interface/main/main_screen.php?site=clinic%20one',
            OidcLoginService::pendingLoginRedirect($session, $script, '/openemr'),
        );
        $this->assertNull(OidcLoginService::pendingLoginRedirect(
            $session,
            dirname(__DIR__, 6) . '/interface/main/main_screen.php',
            '/openemr',
        ));
        $session->remove(OidcRpSettings::SESSION_NEW_LOGIN);
        $this->assertNull(OidcLoginService::pendingLoginRedirect($session, $script, '/openemr'));
    }

    #[Test]
    public function postLogoutRedirectUriIsAbsolute(): void
    {
        $this->assertSame(
            'https://emr.example/interface/login/login.php',
            OidcLoginService::absolutePostLogoutRedirectUri(
                '',
                'https://emr.example',
                '',
                'http://ignored.example',
            ),
        );
        $this->assertSame(
            'https://emr.example/custom/login',
            OidcLoginService::absolutePostLogoutRedirectUri(
                '/custom/login',
                'https://emr.example/',
                '/openemr',
                'http://ignored.example',
            ),
        );
        $this->assertSame(
            'https://already.example/logout',
            OidcLoginService::absolutePostLogoutRedirectUri(
                'https://already.example/logout',
                'https://emr.example',
                '/openemr',
                'http://ignored.example',
            ),
        );
    }

    #[Test]
    public function authorizationRequestUsesPkceAndNonce(): void
    {
        $settings = $this->settings();
        $metadata = new OidcProviderMetadata(
            issuer: $settings->issuer,
            authorizationEndpoint: $settings->issuer . '/protocol/openid-connect/auth',
            tokenEndpoint: $settings->issuer . '/protocol/openid-connect/token',
            jwksUri: $settings->issuer . '/protocol/openid-connect/certs',
            endSessionEndpoint: $settings->issuer . '/protocol/openid-connect/logout',
        );
        $factory = ServiceContainer::getRequestFactory();
        $client = new OidcRpClient(new Client(), $factory, ServiceContainer::getStreamFactory(), new NullLogger());
        $request = $client->buildAuthorizationRequest($settings, $metadata, 'https://localhost/interface/login/oidc_callback.php');
        $this->assertStringContainsString('code_challenge_method=S256', $request['authorization_url']);
        $this->assertStringContainsString('response_type=code', $request['authorization_url']);
        $this->assertNotSame('', $request['state']);
        $this->assertNotSame('', $request['nonce']);
        $this->assertNotSame('', $request['code_verifier']);
    }

    #[Test]
    public function validIdTokenIsAccepted(): void
    {
        $bundle = $this->signedIdToken();
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $claims = $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
        $this->assertSame('admin', $claims->username('preferred_username'));
        $this->assertSame('user-1', $claims->subject);
        $this->assertTrue($claims->emailVerified);
    }

    #[Test]
    public function matchingAzpIsAccepted(): void
    {
        $bundle = $this->signedIdToken(azp: 'openemr-sso');
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $claims = $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
        $this->assertSame('user-1', $claims->subject);
    }

    #[Test]
    public function mismatchedAzpIsRejected(): void
    {
        $bundle = $this->signedIdToken(azp: 'other-client');
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $this->expectException(OidcRpException::class);
        $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
    }

    #[Test]
    public function multipleAudiencesWithoutAzpAreRejected(): void
    {
        $bundle = $this->signedIdToken(extraAudience: 'other-client');
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $this->expectException(OidcRpException::class);
        $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
    }

    #[Test]
    public function multipleAudiencesWithMatchingAzpAreAccepted(): void
    {
        $bundle = $this->signedIdToken(azp: 'openemr-sso', extraAudience: 'other-client');
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $claims = $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
        $this->assertSame('user-1', $claims->subject);
    }

    #[Test]
    public function unverifiedEmailClaimIsFalse(): void
    {
        $bundle = $this->signedIdToken(emailVerified: false);
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $claims = $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
        $this->assertFalse($claims->emailVerified);
    }

    #[Test]
    public function expiredIdTokenIsRejected(): void
    {
        $bundle = $this->signedIdToken(expired: true);
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $this->expectException(OidcRpException::class);
        $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
    }

    #[Test]
    public function wrongNonceIsRejected(): void
    {
        $bundle = $this->signedIdToken();
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $this->expectException(OidcRpException::class);
        $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], 'other-nonce');
    }

    #[Test]
    public function missingExpIsRejected(): void
    {
        $bundle = $this->signedIdToken(omitExp: true);
        $validator = new OidcIdTokenValidator($bundle['http'], $bundle['clock']);
        $this->expectException(OidcRpException::class);
        $validator->validate($bundle['jwt'], $bundle['settings'], $bundle['metadata'], $bundle['nonce']);
    }

    #[Test]
    public function discoveryRejectsTokenEndpointOnAnotherHost(): void
    {
        $document = json_encode([
            'issuer' => 'https://idp.example/realms/openemr',
            'authorization_endpoint' => 'https://idp.example/auth',
            'token_endpoint' => 'https://evil.example/token',
            'jwks_uri' => 'https://idp.example/jwks',
        ], JSON_THROW_ON_ERROR);
        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], $document),
            ])),
        ]);
        $client = new OidcRpClient(
            $http,
            ServiceContainer::getRequestFactory(),
            ServiceContainer::getStreamFactory(),
            new NullLogger(),
        );
        $this->expectException(OidcRpException::class);
        $client->discover($this->settings());
    }

    #[Test]
    public function discoveryIssuerTrailingSlashIsNotNormalized(): void
    {
        $this->expectException(OidcRpException::class);
        OidcProviderMetadata::fromDiscoveryDocument(
            [
                'issuer' => 'https://idp.example/realms/openemr/',
                'authorization_endpoint' => 'https://idp.example/auth',
                'token_endpoint' => 'https://idp.example/token',
                'jwks_uri' => 'https://idp.example/jwks',
            ],
            'https://idp.example/realms/openemr',
        );
    }

    #[Test]
    public function authorizationRequestPreservesExistingQueryParameters(): void
    {
        $settings = $this->settings();
        $metadata = new OidcProviderMetadata(
            issuer: $settings->issuer,
            authorizationEndpoint: $settings->issuer . '/protocol/openid-connect/auth?foo=1',
            tokenEndpoint: $settings->issuer . '/protocol/openid-connect/token',
            jwksUri: $settings->issuer . '/protocol/openid-connect/certs',
            endSessionEndpoint: '',
        );
        $factory = ServiceContainer::getRequestFactory();
        $client = new OidcRpClient(new Client(), $factory, ServiceContainer::getStreamFactory(), new NullLogger());
        $request = $client->buildAuthorizationRequest($settings, $metadata, 'https://localhost/interface/login/oidc_callback.php');
        $this->assertStringContainsString('/protocol/openid-connect/auth?foo=1&response_type=code', $request['authorization_url']);
        $this->assertStringNotContainsString('auth?foo=1?', $request['authorization_url']);
    }

    #[Test]
    public function tokenExchangeDoesNotFollowRedirects(): void
    {
        $settings = $this->settings();
        $metadata = new OidcProviderMetadata(
            issuer: $settings->issuer,
            authorizationEndpoint: $settings->issuer . '/protocol/openid-connect/auth',
            tokenEndpoint: $settings->issuer . '/protocol/openid-connect/token',
            jwksUri: $settings->issuer . '/protocol/openid-connect/certs',
            endSessionEndpoint: '',
        );
        $http = new Client(array_merge(OidcRpClient::httpClientOptions(), [
            'handler' => HandlerStack::create(new MockHandler([
                new Response(307, ['Location' => 'https://evil.example/token'], 'redirect'),
                new Response(200, ['Content-Type' => 'application/json'], '{"id_token":"stolen"}'),
            ])),
        ]));
        $client = new OidcRpClient(
            $http,
            ServiceContainer::getRequestFactory(),
            ServiceContainer::getStreamFactory(),
            new NullLogger(),
        );
        $this->expectException(OidcRpException::class);
        $client->exchangeAuthorizationCode(
            $settings,
            $metadata,
            'code',
            'https://localhost/interface/login/oidc_callback.php',
            'verifier',
        );
    }

    #[Test]
    public function redirectUriUsesSiteAddressAndWebRoot(): void
    {
        $settings = $this->settings();
        $this->assertSame(
            'https://localhost:9300/interface/login/oidc_callback.php',
            $settings->resolveRedirectUri('https://localhost:9300', ''),
        );
        $override = $this->settings(redirectUri: 'https://emr.example/oidc/cb');
        $this->assertSame('https://emr.example/oidc/cb', $override->resolveRedirectUri('https://ignored', '/openemr'));
    }

    /**
     * @return array{
     *   jwt: string,
     *   settings: OidcRpSettings,
     *   metadata: OidcProviderMetadata,
     *   nonce: string,
     *   http: Client,
     *   clock: ClockInterface
     * }
     */
    private function signedIdToken(
        bool $expired = false,
        bool $omitExp = false,
        bool $emailVerified = true,
        ?string $azp = null,
        ?string $extraAudience = null,
    ): array
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($key);
        $exported = openssl_pkey_export($key, $privatePem);
        $this->assertTrue($exported);
        $this->assertIsString($privatePem);
        $this->assertNotSame('', $privatePem);
        $details = openssl_pkey_get_details($key);
        $this->assertIsArray($details);
        $publicPem = $details['key'];
        $this->assertIsString($publicPem);
        $this->assertNotSame('', $publicPem);
        $rsa = $details['rsa'] ?? null;
        $this->assertIsArray($rsa);
        $this->assertIsString($rsa['n']);
        $this->assertIsString($rsa['e']);

        $jwks = json_encode([
            'keys' => [[
                'kty' => 'RSA',
                'kid' => 'test-key',
                'alg' => 'RS256',
                'use' => 'sig',
                'n' => HttpUtils::base64url_encode($rsa['n']),
                'e' => HttpUtils::base64url_encode($rsa['e']),
            ]],
        ], JSON_THROW_ON_ERROR);

        $now = new DateTimeImmutable('2026-01-15 12:00:00');
        $settings = $this->settings();
        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($privatePem),
            InMemory::plainText($publicPem),
        );
        $audiences = $extraAudience === null
            ? [$settings->clientId]
            : [$settings->clientId, $extraAudience];
        $builder = $configuration->builder()
            ->issuedBy($settings->issuer)
            ->permittedFor(...$audiences)
            ->relatedTo('user-1')
            ->issuedAt($now)
            ->withHeader('kid', 'test-key')
            ->withClaim('nonce', 'login-nonce')
            ->withClaim('preferred_username', 'admin')
            ->withClaim('email', 'admin@example.com')
            ->withClaim('email_verified', $emailVerified);
        if ($azp !== null) {
            $builder = $builder->withClaim('azp', $azp);
        }
        if (!$omitExp) {
            $builder = $builder->expiresAt($expired ? $now->modify('-2 minutes') : $now->modify('+5 minutes'));
        }
        $jwt = $builder->getToken($configuration->signer(), $configuration->signingKey())->toString();

        $http = new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], $jwks),
            ])),
        ]);

        return [
            'jwt' => $jwt,
            'settings' => $settings,
            'metadata' => new OidcProviderMetadata(
                issuer: $settings->issuer,
                authorizationEndpoint: $settings->issuer . '/protocol/openid-connect/auth',
                tokenEndpoint: $settings->issuer . '/protocol/openid-connect/token',
                jwksUri: $settings->issuer . '/protocol/openid-connect/certs',
                endSessionEndpoint: '',
            ),
            'nonce' => 'login-nonce',
            'http' => $http,
            'clock' => new class ($now) implements ClockInterface {
                public function __construct(private readonly DateTimeImmutable $now)
                {
                }

                public function now(): DateTimeImmutable
                {
                    return $this->now;
                }
            },
        ];
    }

    private function settings(
        string $newUserAcl = 'Clinicians',
        string $redirectUri = '',
        string $newUserGroup = 'Default',
    ): OidcRpSettings {
        return new OidcRpSettings(
            enabled: true,
            issuer: 'https://idp.example/realms/openemr',
            clientId: 'openemr-sso',
            clientSecret: 'secret',
            buttonLabel: 'Sign in with SSO',
            usernameClaim: 'preferred_username',
            autoRedirect: false,
            hideLocalLogin: false,
            matchEmail: true,
            createUsers: false,
            newUserAcl: $newUserAcl,
            newUserGroup: $newUserGroup,
            allowHttp: false,
            redirectUriOverride: $redirectUri,
            scopes: ['openid', 'profile', 'email'],
        );
    }
}
