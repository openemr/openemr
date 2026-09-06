<?php

/**
 * AuthorizationControllerJwksUriValidationIsolatedTest
 *
 * Coverage for `jwks_uri` outbound-URL validation. The OAuth2 dynamic client
 * registration endpoint previously copied `jwks_uri` verbatim into client
 * metadata without any scheme / host / DNS check, so a caller could register
 * a client whose `jwks_uri` pointed at an internal service or cloud-metadata
 * endpoint and cause an outbound fetch via token introspection.
 *
 * A shared `SsrfSafeUrlValidator` is threaded through both the registration
 * write path (AuthorizationController::clientRegistration) and the read path
 * (JWTClientAuthenticationService::validateJWTClientAssertion, which
 * revalidates any persisted row that predates the write-path check).
 * This test asserts the invariants textually against the two source files:
 * building a full OAuth registration harness (session, key generation, DB)
 * is out of scope for an isolated tier, but locking down the shape of the
 * guard is exactly what stops a silent change from shipping.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Authorization;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('security')]
class AuthorizationControllerJwksUriValidationIsolatedTest extends TestCase
{
    private string $writePathContent = '';
    private string $readPathContent = '';

    protected function setUp(): void
    {
        $writePath = realpath(__DIR__ . '/../../../../../src/RestControllers/AuthorizationController.php');
        if (!is_string($writePath)) {
            $this->markTestSkipped('AuthorizationController.php not found');
        }
        $writeContent = file_get_contents($writePath);
        if ($writeContent === false) {
            $this->markTestSkipped('Failed to read AuthorizationController.php');
        }
        $this->writePathContent = $writeContent;

        $readPath = realpath(__DIR__ . '/../../../../../src/Services/JWTClientAuthenticationService.php');
        if (!is_string($readPath)) {
            $this->markTestSkipped('JWTClientAuthenticationService.php not found');
        }
        $readContent = file_get_contents($readPath);
        if ($readContent === false) {
            $this->markTestSkipped('Failed to read JWTClientAuthenticationService.php');
        }
        $this->readPathContent = $readContent;
    }

    // ---- Write path ------------------------------------------------------

    public function testWritePathImportsSsrfSafeUrlValidator(): void
    {
        $this->assertMatchesRegularExpression(
            '/use\s+OpenEMR\\\\Common\\\\Http\\\\SsrfSafeUrlValidator;/',
            $this->writePathContent,
            'AuthorizationController must import SsrfSafeUrlValidator'
        );
    }

    public function testWritePathHasJwksUriBranchInClientRegistration(): void
    {
        // The `jwks_uri` key must have its own elseif branch in the metadata
        // loop so it is validated instead of falling through to the generic
        // `$data->get($key)` copy.
        $this->assertMatchesRegularExpression(
            "/elseif\\s*\\(\\s*\\\$key\\s*===\\s*'jwks_uri'\\s*\\)/",
            $this->writePathContent,
            'clientRegistration must handle jwks_uri in its own branch'
        );
    }

    public function testWritePathInvokesValidatorOnJwksUri(): void
    {
        // The paired testWritePathValidatorIsHttpsOnly pins the allowlist
        // shape; this test only checks that validate() runs against
        // $rawJwksUri, so it accepts either an empty arg or an explicit array.
        $this->assertMatchesRegularExpression(
            '/new\s+SsrfSafeUrlValidator\s*\([^)]*\)\s*\)\s*->\s*validate\s*\(\s*\$rawJwksUri\s*\)/',
            $this->writePathContent,
            'clientRegistration must call SsrfSafeUrlValidator->validate on the raw jwks_uri'
        );
    }

    public function testWritePathValidatorIsHttpsOnly(): void
    {
        $this->assertMatchesRegularExpression(
            "/new\\s+SsrfSafeUrlValidator\\s*\\(\\s*\\[\\s*['\"]https['\"]\\s*\\]\\s*\\)/",
            $this->writePathContent,
            'registration-write-path jwks_uri validator must use an https-only scheme allowlist'
        );
    }

    public function testWritePathThrowsInvalidClientMetadataOnRejection(): void
    {
        // Match the throw block that runs when the validator returns a
        // rejection reason. The error code MUST be `invalid_client_metadata`
        // per RFC 7591.
        $pattern = '/if\s*\(\s*\$rejectionReason\s*!==\s*null\s*\)[\s\S]{0,1200}?throw\s+new\s+OAuthServerException\s*\([\s\S]{0,300}?[\'"]invalid_client_metadata[\'"]\s*\)\s*;/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->writePathContent,
            'clientRegistration must throw invalid_client_metadata (RFC 7591) on jwks_uri rejection'
        );
    }

    public function testWritePathAuditLogsRejection(): void
    {
        $pattern = '/if\s*\(\s*\$rejectionReason\s*!==\s*null\s*\)[\s\S]{0,600}?EventAuditLogger::getInstance\(\)\s*->\s*newEvent\s*\(\s*[\'"]oauth-jwks-uri-rejected[\'"]/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->writePathContent,
            'clientRegistration must audit-log jwks_uri rejections via EventAuditLogger'
        );
    }

    public function testWritePathValidationRunsBeforePersistence(): void
    {
        // The validation branch must precede the client-insert call inside
        // the same clientRegistration method. If someone reorders it below
        // insertNewClient, the caller-supplied URL is persisted before it
        // is checked, defeating the write-path check.
        $branchOffset = strpos($this->writePathContent, "elseif (\$key === 'jwks_uri')");
        $insertOffset = strpos($this->writePathContent, 'insertNewClient(');
        $this->assertIsInt($branchOffset, 'jwks_uri branch not present');
        $this->assertIsInt($insertOffset, 'insertNewClient call not present');
        $this->assertLessThan(
            $insertOffset,
            $branchOffset,
            'jwks_uri validation must run before insertNewClient()'
        );
    }

    // ---- Read path (revalidates persisted rows) -------------------------

    public function testReadPathImportsSsrfSafeUrlValidator(): void
    {
        $this->assertMatchesRegularExpression(
            '/use\s+OpenEMR\\\\Common\\\\Http\\\\SsrfSafeUrlValidator;/',
            $this->readPathContent,
            'JWTClientAuthenticationService must import SsrfSafeUrlValidator'
        );
    }

    public function testReadPathHasValidatorAccessor(): void
    {
        $this->assertMatchesRegularExpression(
            '/function\s+getJwksUriValidator\s*\(\s*\)\s*:\s*SsrfSafeUrlValidator/',
            $this->readPathContent,
            'JWTClientAuthenticationService must expose getJwksUriValidator() for lazy construction and test override'
        );
    }

    public function testReadPathValidatesJwksUriBeforeJsonWebKeySetConstruction(): void
    {
        // The read-path validation call must precede the `new JsonWebKeySet(`
        // instantiation inside validateJWTClientAssertion. Ordering matters:
        // JsonWebKeySet fetches the URL immediately in its constructor, so
        // the check has to run first.
        $validateOffset = strpos($this->readPathContent, '$this->getJwksUriValidator()->validate($storedJwksUri)');
        $jwksOffset = strpos($this->readPathContent, 'new JsonWebKeySet(');
        $this->assertIsInt($validateOffset, 'read-path validate() call not present');
        $this->assertIsInt($jwksOffset, 'JsonWebKeySet construction not present');
        $this->assertLessThan(
            $jwksOffset,
            $validateOffset,
            'jwks_uri validation must run before JsonWebKeySet is constructed'
        );
    }

    public function testReadPathThrowsInvalidClientOnRejection(): void
    {
        // Read path returns invalid_client (per RFC 7523) — the caller is
        // presenting a client_assertion but the persisted client metadata
        // is no longer trustworthy.
        $pattern = '/if\s*\(\s*\$rejectionReason\s*!==\s*null\s*\)[\s\S]{0,1500}?throw\s+OAuthServerException::invalidClient\s*\(\s*\$request\s*\)/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->readPathContent,
            'validateJWTClientAssertion must throw invalidClient on rejected persisted jwks_uri'
        );
    }

    public function testReadPathAuditLogsRejection(): void
    {
        $pattern = '/if\s*\(\s*\$rejectionReason\s*!==\s*null\s*\)[\s\S]{0,1200}?EventAuditLogger::getInstance\(\)\s*->\s*newEvent\s*\(\s*[\'"]oauth-jwks-uri-rejected[\'"]/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->readPathContent,
            'read-path rejection must be audit-logged via EventAuditLogger'
        );
    }

    public function testReadPathValidatorIsHttpsOnly(): void
    {
        // Pin the read-path allowlist so it does not drift from the
        // registration-write-path allowlist (see the write-path variant).
        $this->assertMatchesRegularExpression(
            "/getJwksUriValidator\\s*\\(\\s*\\)\\s*:\\s*SsrfSafeUrlValidator\\s*\\{[\\s\\S]{0,2000}?new\\s+SsrfSafeUrlValidator\\s*\\(\\s*\\[\\s*['\"]https['\"]\\s*\\]\\s*\\)/",
            $this->readPathContent,
            'getJwksUriValidator() must construct the validator with an https-only scheme allowlist'
        );
    }
}
