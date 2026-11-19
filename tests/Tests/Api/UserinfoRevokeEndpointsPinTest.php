<?php

/**
 * Lock current /oauth2/default/userinfo + /oauth2/default/revoke
 * behavior until those endpoints are implemented.
 *
 * Both endpoints are tracked in `docs/oauth2-fhir-api-test-coverage.md`
 * §8.3 as "unimplemented spec-compliance" items:
 *   - `/userinfo` — OIDC Core §5.3. OpenEMR advertises it in the
 *     `.well-known/openid-configuration` discovery doc but has
 *     no handler, so any OIDC client that follows discovery and
 *     calls the endpoint gets a 404 with an empty body.
 *   - `/revoke` — RFC 7009. Not advertised in discovery (the
 *     SMART config has a commented-out `revocation_endpoint`)
 *     and no handler, so any client that hard-codes the URL per
 *     RFC 7009 gets a 404 with an empty body.
 *
 * Same lock-then-fix pattern PR #15320 used for the
 * `/$bulkdata-status` 500 pin — when the eventual implementation PR
 * lands, these `CurrentlyReturns404` tests flip to failure and the
 * implementer updates them in the same PR. The companion
 * discovery-shape tests also catch a regression that tried to
 * *un*-advertise `userinfo_endpoint` (which would hide the
 * unimplemented state from OIDC clients without fixing it).
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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserinfoRevokeEndpointsPinTest extends TestCase
{
    private const DISCOVERY_PATH = '/oauth2/default/.well-known/openid-configuration';
    private const USERINFO_PATH = '/oauth2/default/userinfo';
    private const REVOKE_PATH = '/oauth2/default/revoke';

    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
    }

    #[Test]
    public function testDiscoveryStillAdvertisesUserinfoEndpoint(): void
    {
        // Pin the advertised state. OIDC Core §3.1.2.5 permits but does
        // not require userinfo_endpoint in the metadata; OpenEMR's
        // `OAuth2DiscoveryController` emits it, so any regression that
        // dropped it would hide the unimplemented-but-advertised
        // discrepancy from this test file. When /userinfo is
        // implemented, this test continues to pass — the advertisement
        // is correct once the handler exists.
        $metadata = $this->fetchDiscoveryMetadata();
        $advertised = $metadata['userinfo_endpoint'] ?? null;
        $this->assertIsString(
            $advertised,
            'Discovery metadata must advertise userinfo_endpoint'
        );
        // Host+port varies per worktree/CI runner; pin the path only
        // so a cross-worktree test run does not fail on a URL-base
        // mismatch. The path is the stable part of the OIDC Core
        // §3.1.2.5 contract. Compare parsed path rather than
        // string-ends-with so a stray query-string suffix cannot
        // smuggle the matching bytes into a non-path position.
        $path = parse_url($advertised, PHP_URL_PATH);
        $this->assertSame(
            self::USERINFO_PATH,
            $path,
            'userinfo_endpoint path must be /oauth2/default/userinfo'
        );
    }

    #[Test]
    public function testUserinfoCurrentlyReturns404(): void
    {
        // Pin current (unimplemented) behavior. OIDC Core §5.3.2 says
        // the endpoint must accept GET or POST with a Bearer token and
        // return a 200 with JSON claims. OpenEMR has no handler, so a
        // GET to the advertised URL returns 404 with an empty body.
        // When the endpoint is implemented, flip this assertion to
        // check the OIDC UserInfo response shape (Content-Type
        // application/json with at least a `sub` claim).
        $response = $this->buildClient()->get(
            $this->baseUrl . self::USERINFO_PATH,
            ['http_errors' => false]
        );
        $this->assertSame(
            404,
            $response->getStatusCode(),
            'userinfo_endpoint is advertised in discovery but returns 404 today — see §8.3 register'
        );
    }

    #[Test]
    public function testDiscoveryDoesNotAdvertiseRevocationEndpoint(): void
    {
        // Pin the un-advertised state. RFC 8414 §2 adds
        // revocation_endpoint to OAuth2 Authorization Server Metadata;
        // OpenEMR does not implement /revoke so does not advertise
        // the URL (the SMART config has a commented-out line). When
        // /revoke is implemented, this test flips to failure and the
        // implementer adds revocation_endpoint to discovery + updates
        // this assertion to pin the advertised URL.
        $metadata = $this->fetchDiscoveryMetadata();
        $this->assertArrayNotHasKey(
            'revocation_endpoint',
            $metadata,
            'Discovery must NOT advertise revocation_endpoint until /revoke is implemented'
        );
    }

    #[Test]
    public function testRevokeCurrentlyReturns404(): void
    {
        // Pin current (unimplemented) behavior. RFC 7009 §2 says a
        // POST to the revocation endpoint with a token parameter must
        // return 200 (with an empty body, per §2.2) on success and
        // specific error codes on failure. OpenEMR has no handler, so
        // a POST returns 404 with an empty body regardless of token
        // value. When /revoke is implemented, flip this assertion to
        // check the RFC 7009 response shape.
        $response = $this->buildClient()->post(
            $this->baseUrl . self::REVOKE_PATH,
            [
                'http_errors' => false,
                'form_params' => [
                    'token' => 'arbitrary-string-that-will-404-today',
                    'token_type_hint' => 'access_token',
                ],
            ]
        );
        $this->assertSame(
            404,
            $response->getStatusCode(),
            '/revoke returns 404 today because the endpoint is unimplemented — see §8.3 register'
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private function fetchDiscoveryMetadata(): array
    {
        $response = $this->buildClient()->get(
            $this->baseUrl . self::DISCOVERY_PATH,
            ['http_errors' => false]
        );
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'OIDC discovery must be served from ' . self::DISCOVERY_PATH
        );
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded, 'Discovery metadata must be a JSON object');
        return $decoded;
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'timeout' => 30,
        ]);
    }
}
