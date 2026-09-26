<?php

/**
 * JWT client-assertion claim-validation negatives.
 *
 * Covers the four claim-shape constraints in
 * JWTClientAuthenticationService::performAdditionalValidations that
 * were not previously exercised by any HTTP-level test: iss match,
 * exp bound, iat freshness, jti presence. The signature-tamper case
 * is already pinned by testAuthCodeGrantRejectsTamperedJwtAssertion
 * and testRefreshGrantRejectsTamperedJwtAssertion. The sub match is
 * unreachable from HTTP because the grant flow keys the client entity
 * off the JWT sub claim, so sub and clientId are equal by
 * construction by the time performAdditionalValidations runs.
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
use GuzzleHttp\Cookie\CookieJar;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;

class JwtClientAssertionClaimValidationTest extends TestCase
{
    private const REDIRECT_URI = 'https://client.example/cb';
    private const STATE = 'jwt-claim-state';

    private string $baseUrl;
    private ?string $clientId = null;
    private ?string $originalSiteAddrOath = null;
    private bool $siteAddrOathWasInserted = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['site_addr_oath']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalSiteAddrOath = is_string($glValue) ? $glValue : null;
            if ($this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->baseUrl, 'site_addr_oath']
                );
            }
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['site_addr_oath', $this->baseUrl]
            );
            $this->siteAddrOathWasInserted = true;
        }
    }

    protected function tearDown(): void
    {
        try {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
        } finally {
            if ($this->siteAddrOathWasInserted) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `globals` WHERE gl_name = ?',
                    ['site_addr_oath']
                );
            } elseif ($this->originalSiteAddrOath !== null && $this->originalSiteAddrOath !== $this->baseUrl) {
                QueryUtils::sqlStatementThrowException(
                    'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                    [$this->originalSiteAddrOath, 'site_addr_oath']
                );
            }
        }
    }

    #[Test]
    public function testJwtAssertionWithWrongIssIsRejected(): void
    {
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode();

        // iss set to a different value than the client_id (sub still
        // matches). Line 457-462 rejects.
        $assertion = $this->buildAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
            overrideIss: 'not-the-real-client-id'
        );
        $this->assertTokenRejected($assertion, $code);
    }

    #[Test]
    public function testJwtAssertionWithExpBeyondMaxAllowedWindowIsRejected(): void
    {
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode();

        // Max window per SMART spec is 24h — set 48h to exceed it.
        // Line 471-479 rejects.
        $now = new DateTimeImmutable();
        $assertion = $this->buildAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
            overrideExp: $now->modify('+48 hours')
        );
        $this->assertTokenRejected($assertion, $code);
    }

    #[Test]
    public function testJwtAssertionWithIatOlderThanFiveMinutesIsRejected(): void
    {
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode();

        // iat 6 minutes ago (5m is the enforced limit at line 487).
        // exp deliberately kept in the future so the token isn't
        // rejected as expired before iat is checked. Line 487-495
        // rejects.
        $now = new DateTimeImmutable();
        $assertion = $this->buildAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
            overrideIat: $now->modify('-6 minutes'),
            overrideExp: $now->modify('+1 minute')
        );
        $this->assertTokenRejected($assertion, $code);
    }

    #[Test]
    public function testJwtAssertionMissingJtiIsRejected(): void
    {
        [$privateKey, $publicKey, $code] = $this->registerJwtClientAndObtainCode();

        // jti is required for replay prevention (line 500-503).
        $assertion = $this->buildAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            (string) $this->clientId,
            omitJti: true
        );
        $this->assertTokenRejected($assertion, $code);
    }

    private function assertTokenRejected(string $assertion, string $code): void
    {
        $http = $this->buildClient();
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::REDIRECT_URI,
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'JWT with invalid claim shape must be rejected. Body: '
                . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertContains(
            $body['error'] ?? null,
            ['invalid_request', 'invalid_client'],
            'Rejection error should be invalid_request or invalid_client. '
                . 'Body: ' . (string) $response->getBody()
        );
    }

    /**
     * Build a signed JWT assertion mirroring ClientCredentialsAssertionGenerator's
     * shape (RS384, iss/sub/aud/iat/nbf/exp/jti) but with per-claim overrides
     * so a test can mutate exactly one field.
     */
    private function buildAssertion(
        Key $privateKey,
        Key $publicKey,
        string $oauthTokenUrl,
        string $clientId,
        ?string $overrideIss = null,
        ?string $overrideSub = null,
        ?DateTimeImmutable $overrideExp = null,
        ?DateTimeImmutable $overrideIat = null,
        bool $omitJti = false,
    ): string {
        $configuration = Configuration::forAsymmetricSigner(new Sha384(), $privateKey, $publicKey);
        $now = new DateTimeImmutable();
        $iat = $overrideIat ?? $now;
        $exp = $overrideExp ?? $now->modify('+60 seconds');
        $iss = $overrideIss ?? $clientId;
        $sub = $overrideSub ?? $clientId;

        $builder = $configuration->builder()
            ->issuedBy($iss)
            ->permittedFor($oauthTokenUrl)
            ->issuedAt($iat)
            ->canOnlyBeUsedAfter($iat)
            ->expiresAt($exp)
            ->relatedTo($sub);

        if (!$omitJti) {
            $builder = $builder->identifiedBy(Uuid::uuid4()->toString());
        }

        return $builder
            ->getToken($configuration->signer(), $configuration->signingKey())
            ->toString();
    }

    /**
     * DCR + /authorize + login + consent → returns keys + code.
     * Same shape as AuthorizationGrantJwtAssertionFlowTest but private
     * to this file — see the comment there on why we duplicate rather
     * than share.
     *
     * @return array{Key, Key, string}
     */
    private function registerJwtClientAndObtainCode(): array
    {
        $http = $this->buildClient();
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));
        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [self::REDIRECT_URI],
                'client_name' => 'JwtClientAssertionClaimValidationTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid fhirUser offline_access',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        $authUrl = '/oauth2/default/authorize?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => self::REDIRECT_URI,
            'response_type' => 'code',
            'scope' => 'openid fhirUser offline_access',
            'state' => self::STATE,
        ]);
        $loginPage = $http->get($this->baseUrl . $authUrl);
        $this->assertSame(200, $loginPage->getStatusCode());
        [$loginCsrf, $loginAction] = $this->parseForm(
            new Crawler((string) $loginPage->getBody()),
            ['csrf_token_form', 'username', 'password', 'email', 'persist_login', 'user_role']
        );
        $postLogin = $http->post($this->baseUrl . $loginAction, [
            'form_params' => [
                'csrf_token_form' => $loginCsrf,
                'username' => 'admin',
                'password' => 'pass',
                'email' => '',
                'persist_login' => '0',
                'user_role' => 'api',
            ],
        ]);
        $this->assertSame(200, $postLogin->getStatusCode());
        [$consentCsrf, $consentAction] = $this->parseForm(
            new Crawler((string) $postLogin->getBody()),
            ['csrf_token_form', 'proceed', 'scope[openid]', 'scope[fhirUser]', 'scope[offline_access]']
        );
        $postConsent = $http->post($this->baseUrl . $consentAction, [
            'form_params' => [
                'csrf_token_form' => $consentCsrf,
                'proceed' => '1',
                'scope' => [
                    'openid' => 'openid',
                    'fhirUser' => 'fhirUser',
                    'offline_access' => 'offline_access',
                ],
            ],
            'allow_redirects' => false,
        ]);
        $this->assertSame(302, $postConsent->getStatusCode());
        $callbackQueryString = parse_url($postConsent->getHeaderLine('Location'), PHP_URL_QUERY);
        $this->assertIsString($callbackQueryString);
        parse_str($callbackQueryString, $callbackQuery);
        $this->assertArrayHasKey('code', $callbackQuery);
        $this->assertIsString($callbackQuery['code']);

        return [$privateKey, $publicKey, $callbackQuery['code']];
    }

    /**
     * @param list<string> $required
     *
     * @return array{string, string}
     */
    private function parseForm(Crawler $crawler, array $required): array
    {
        $csrfInputs = $crawler->filterXPath('//input[@name="csrf_token_form"]');
        $this->assertGreaterThan(0, $csrfInputs->count(), 'csrf_token_form input not found');
        $csrf = (string) $csrfInputs->first()->attr('value');
        $this->assertNotSame('', $csrf, 'csrf_token_form value is empty');

        $forms = $crawler->filterXPath('//form[@id="userLogin"]');
        $this->assertGreaterThan(0, $forms->count(), 'no <form id="userLogin"> on page');
        $action = (string) $forms->first()->attr('action');
        $this->assertNotSame('', $action, 'form action is empty');

        foreach ($required as $name) {
            $matches = $forms->first()->filterXPath(
                './/input[@name="' . $name . '"]'
                . ' | .//button[@name="' . $name . '"]'
                . ' | .//select[@name="' . $name . '"]'
                . ' | .//textarea[@name="' . $name . '"]'
            );
            $this->assertGreaterThan(0, $matches->count(), 'form missing control: ' . $name);
        }
        return [$csrf, $action];
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'allow_redirects' => [
                'max' => 10,
                'strict' => true,
                'referer' => true,
                'protocols' => ['http', 'https'],
            ],
            'timeout' => 15,
        ]);
    }
}
