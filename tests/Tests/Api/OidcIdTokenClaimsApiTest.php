<?php

/**
 * API-tier regression gates for the OIDC id_token claims structure.
 *
 * `/oauth2/default/token` returns an id_token whenever the auth-code
 * flow carried the `openid` scope. OIDC Core 1.0 §3.1.3.6-7 defines
 * five required claims (`iss`, `sub`, `aud`, `exp`, `iat`) and §5.4
 * defines the optional profile claims pulled in by the `profile`
 * scope. The SMART-on-FHIR profile adds `fhirUser` as a URL pointer
 * to the FHIR Practitioner/Patient resource the subject represents.
 *
 * `IdTokenSMARTResponse::getBuilder` builds the token; isolated-tier
 * tests cover the extra-params `scope` echo (added for SMART
 * compatibility — see PR #14658). No end-to-end test previously
 * minted a token with various scope combinations, decoded the
 * id_token, and asserted the claims structure matches the discovery
 * advertisement + OIDC spec. This file closes that gap:
 *
 *   1. Minimum `openid` scope → the five OIDC §3.1.3.6 required
 *      claims are present with the right shapes.
 *   2. `openid profile` → §5.4 profile claims (`name`, `given_name`,
 *      `family_name`, etc) ride along in the id_token.
 *   3. `openid fhirUser` → the SMART `fhirUser` claim is a URL
 *      pointing at a FHIR Practitioner (or Patient) resource.
 *   4. `nonce` parameter → the id_token echoes it verbatim per OIDC
 *      §3.1.2.1 (replay protection for implicit/hybrid flows).
 *   5. JWT header `alg` → matches the single value advertised in
 *      `id_token_signing_alg_values_supported` from the
 *      `/.well-known/openid-configuration` discovery document.
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
use GuzzleHttp\Cookie\CookieJar;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Tests\Support\OAuth2\AuthCodeFlow;
use OpenEMR\Tests\Support\OAuth2\DcrClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OidcIdTokenClaimsApiTest extends TestCase
{
    private string $baseUrl;

    private ?string $originalSiteAddrOathGlobalRow = null;
    private bool $siteAddrOathGlobalRowWasInserted = false;
    private bool $siteAddrOathCaptured = false;

    /** @var list<string> */
    private array $clientsToRemove = [];
    /** @var list<string> */
    private array $tearDownFailures = [];

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
        if (stripos($probe->getHeaderLine('Server'), 'nginx') !== false) {
            $this->markTestSkipped('Auth-code flow test skipped on nginx config');
        }

        try {
            $this->siteAddrOathCaptured = true;
            $this->persistGlobal(
                'site_addr_oath',
                $this->baseUrl,
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        } catch (\Throwable $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: ' . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure
                );
            }
            throw $setUpFailure;
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->clientsToRemove as $cid) {
            $this->safeRun(function () use ($cid): void {
                DcrClient::remove($cid);
            });
        }
        $this->safeRun(function (): void {
            if (!$this->siteAddrOathCaptured) {
                return;
            }
            $this->restoreGlobal(
                'site_addr_oath',
                $this->originalSiteAddrOathGlobalRow,
                $this->siteAddrOathGlobalRowWasInserted
            );
        });

        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                "tearDown cleanup had " . count($failures) . " failure(s); "
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures)
            );
        }
    }

    #[Test]
    public function testIdTokenCarriesRequiredOidcClaimsWithOpenidScope(): void
    {
        // OIDC Core 1.0 §3.1.3.6: `iss`, `sub`, `aud`, `exp`, `iat` are
        // REQUIRED on an id_token issued from an auth-code grant. If a
        // refactor dropped any of these, OIDC-conformant clients would
        // reject the token (or silently miss identity info and treat
        // the user as anonymous).
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'OidcClaims-min-' . bin2hex(random_bytes(3)),
            'openid',
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $idToken = $mint['response_body']['id_token'] ?? null;
        $this->assertIsString($idToken, 'Token response must carry an id_token when openid scope is granted');
        $claims = $this->decodeJwtPayload($idToken);

        foreach (['iss', 'sub', 'aud', 'exp', 'iat'] as $required) {
            $this->assertArrayHasKey(
                $required,
                $claims,
                sprintf('OIDC Core §3.1.3.6: id_token MUST carry the "%s" claim', $required)
            );
        }

        $this->assertIsString($claims['iss']);
        $this->assertNotSame('', $claims['iss']);
        $this->assertStringStartsWith('http', $claims['iss'], 'iss must be a URL');

        $this->assertIsString($claims['sub']);
        $this->assertNotSame('', $claims['sub'], 'sub must identify the end-user');

        // `aud` must match the client the token was issued for — not
        // another client, not empty. league/oauth2-server uses a
        // single-string aud per OIDC §2 "StringOrURI".
        $this->assertSame(
            $mint['client_id'],
            $claims['aud'],
            'aud claim must be bound to the client that initiated the flow'
        );

        $this->assertIsInt($claims['iat']);
        $this->assertIsInt($claims['exp']);
        $this->assertGreaterThan(
            $claims['iat'],
            $claims['exp'],
            'exp must be strictly after iat (token is forward-dated)'
        );
    }

    #[Test]
    public function testIdTokenHeaderAlgMatchesDiscoveryAdvertisement(): void
    {
        // OpenEMR advertises `RS256` as the sole id_token signing
        // algorithm via /.well-known/openid-configuration (per
        // OAuth2DiscoveryController). If the issuer silently switched
        // to another alg (e.g. HS256), OIDC clients would start
        // rejecting the id_token with "unsupported alg".
        //
        // Lock the invariant: the header `alg` must be one of the
        // values discovery advertises.
        $discoveryResp = $this->buildClient()->get(
            $this->baseUrl . '/oauth2/default/.well-known/openid-configuration'
        );
        $this->assertSame(200, $discoveryResp->getStatusCode(), 'Discovery endpoint must be reachable');
        $discovery = json_decode((string) $discoveryResp->getBody(), true);
        $this->assertIsArray($discovery);
        $this->assertArrayHasKey('id_token_signing_alg_values_supported', $discovery);
        $advertised = $discovery['id_token_signing_alg_values_supported'];
        $this->assertIsArray($advertised);
        $this->assertNotEmpty($advertised, 'Discovery must advertise at least one signing alg');

        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'OidcClaims-alg-' . bin2hex(random_bytes(3)),
            'openid',
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $idToken = $mint['response_body']['id_token'] ?? null;
        $this->assertIsString($idToken);
        $header = $this->decodeJwtHeader($idToken);
        $this->assertArrayHasKey('alg', $header);
        $this->assertContains(
            $header['alg'],
            $advertised,
            sprintf(
                'id_token header alg "%s" must appear in id_token_signing_alg_values_supported (%s)',
                is_string($header['alg']) ? $header['alg'] : var_export($header['alg'], true),
                implode(',', array_filter($advertised, is_string(...)))
            )
        );
    }

    #[Test]
    public function testIdTokenEchoesNonceVerbatim(): void
    {
        // OIDC Core §3.1.2.1 + §15.5.2: when the authorize request
        // includes a `nonce`, the id_token MUST echo it verbatim. This
        // is the replay-protection anchor for the id_token — a client
        // compares `nonce` in the decoded id_token against what it
        // generated to detect a token issued for a different request.
        //
        // AuthCodeFlow::runToConsentPage always sends a random nonce;
        // this test checks that the id_token carries it back and that
        // it survived the roundtrip unmodified.
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'OidcClaims-nonce-' . bin2hex(random_bytes(3)),
            'openid',
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $idToken = $mint['response_body']['id_token'] ?? null;
        $this->assertIsString($idToken);
        $claims = $this->decodeJwtPayload($idToken);
        $this->assertArrayHasKey(
            'nonce',
            $claims,
            'OIDC §3.1.3.7-11: id_token MUST include nonce if nonce was in authorize request'
        );
        $this->assertIsString($claims['nonce']);
        // Verbatim-echo gate: the id_token nonce MUST be the exact
        // value the authorize request sent, not just the same shape.
        // Rabbit flagged that a format-only match (regex /^[0-9a-f]{16}$/)
        // would still pass if the server regenerated the nonce
        // internally — breaking the replay-protection anchor OIDC
        // Core §15.5.2 relies on. mintUserRoleToken now surfaces the
        // nonce generated during flow prep so this test can compare
        // the exact value.
        $this->assertSame(
            $mint['nonce'],
            $claims['nonce'],
            'id_token nonce must match the authorize-request nonce exactly'
        );
    }

    #[Test]
    public function testIdTokenCarriesProfileClaimsWhenProfileScopeGranted(): void
    {
        // OIDC §5.4: the `profile` scope requests the standard profile
        // claims (`name`, `given_name`, `family_name`, `preferred_username`,
        // etc). OpenEMR populates these in UserEntity::getClaims from
        // the users row. The ClaimExtractor filters them by granted
        // scope — without `profile` they are stripped before signing.
        //
        // Admin seed data has empty firstname/lastname so we only
        // assert the keys are present (OIDC-conformant: a missing
        // display name may be empty-string, but the key still exists
        // when `profile` was granted). A refactor that dropped the
        // ClaimExtractor wiring would regress here.
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'OidcClaims-profile-' . bin2hex(random_bytes(3)),
            'openid profile',
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $idToken = $mint['response_body']['id_token'] ?? null;
        $this->assertIsString($idToken);
        $claims = $this->decodeJwtPayload($idToken);
        foreach (['name', 'given_name', 'family_name', 'preferred_username'] as $profileClaim) {
            $this->assertArrayHasKey(
                $profileClaim,
                $claims,
                sprintf(
                    'OIDC §5.4: profile scope must surface "%s" in id_token',
                    $profileClaim
                )
            );
        }
    }

    #[Test]
    public function testIdTokenCarriesFhirUserWhenFhirUserScopeGranted(): void
    {
        // SMART-on-FHIR identity profile: granting the `fhirUser` scope
        // requires the id_token to carry a `fhirUser` claim whose value
        // is an absolute URL pointing at the FHIR Practitioner or
        // Patient resource the subject represents. See
        // http://hl7.org/fhir/smart-app-launch/scopes-and-launch-context.html
        //
        // Admin logs in as user_role=api, so FhirUserClaim resolves
        // the identifier to a Practitioner resource.
        $mint = AuthCodeFlow::mintUserRoleToken(
            $this->baseUrl,
            'OidcClaims-fhiruser-' . bin2hex(random_bytes(3)),
            'openid fhirUser',
        );
        $this->clientsToRemove[] = $mint['client_id'];

        $idToken = $mint['response_body']['id_token'] ?? null;
        $this->assertIsString($idToken);
        $claims = $this->decodeJwtPayload($idToken);
        $this->assertArrayHasKey(
            'fhirUser',
            $claims,
            'SMART identity: id_token must carry fhirUser when fhirUser scope was granted'
        );
        $this->assertIsString($claims['fhirUser']);
        $this->assertMatchesRegularExpression(
            '#^https?://.+/(Practitioner|Patient|Person)/[0-9a-f-]{36}$#',
            $claims['fhirUser'],
            sprintf(
                'fhirUser must be an absolute URL to a FHIR Practitioner/Patient/Person resource. Got: %s',
                $claims['fhirUser']
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'id_token must be a compact-serialization JWT (header.payload.signature)');
        $payloadRaw = base64_decode(strtr($parts[1], '-_', '+/'), true);
        $this->assertIsString($payloadRaw, 'id_token payload must base64url-decode cleanly');
        $decoded = json_decode($payloadRaw, true);
        $this->assertIsArray($decoded, 'id_token payload must be a JSON object');
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJwtHeader(string $jwt): array
    {
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'id_token must be a compact-serialization JWT');
        $headerRaw = base64_decode(strtr($parts[0], '-_', '+/'), true);
        $this->assertIsString($headerRaw);
        $decoded = json_decode($headerRaw, true);
        $this->assertIsArray($decoded, 'id_token header must be a JSON object');
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    private function persistGlobal(
        string $name,
        string $value,
        ?string &$originalCapture,
        bool &$insertedCapture
    ): void {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalCapture = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$value, $name]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $insertedCapture = true;
        }
    }

    private function restoreGlobal(string $name, ?string $originalRow, bool $wasInserted): void
    {
        if ($wasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                [$name]
            );
        } elseif ($originalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$originalRow, $name]
            );
        }
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            $this->tearDownFailures[] = $e->getMessage();
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
