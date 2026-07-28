<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * FHIR discovery-endpoint smoke test.
 *
 * Both endpoints exercised here are REQUIRED to be publicly accessible
 * per their governing specs (FHIR R4 + SMART-on-FHIR) — no OAuth token,
 * no session cookie, nothing. Purpose: prove the artifact's FHIR routing
 * infrastructure is intact end-to-end. If /apis/default/fhir/* returns a
 * 404 or 5xx here, no FHIR-consuming client can even discover the server
 * exists.
 *
 * Fires in both fresh-install and post-upgrade scenarios: a schema
 * migration bug can break FHIR discovery just as easily as an
 * install-time misconfiguration, and either failure mode blocks every
 * downstream FHIR interaction.
 *
 * Authenticated FHIR resource access (Patient, Observation, etc.)
 * lands in Phase 4a-2, which introduces the OAuth2 dynamic-client-
 * registration + authorization-code helper.
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class FhirSmokeTest extends TestCase
{
    public function testMetadataEndpointReturnsCapabilityStatement(): void
    {
        // Per FHIR spec (https://hl7.org/fhir/http.html#capabilities),
        // `GET [base]/metadata` returns a CapabilityStatement resource
        // and must be accessible without authentication so clients can
        // discover the server's capabilities before initiating auth.
        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/apis/default/fhir/metadata',
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'FHIR /metadata must return 200 without auth per FHIR spec — 401/403 means the routing added an auth check',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            'FHIR /metadata should return a JSON object; a non-JSON body means the response is an HTML error page',
        );
        self::assertSame(
            'CapabilityStatement',
            $body['resourceType'] ?? null,
            'Response resourceType must be CapabilityStatement per the FHIR /metadata contract',
        );
        self::assertSame(
            '4.0.1',
            $body['fhirVersion'] ?? null,
            'FHIR version should be R4 (4.0.1) — mismatch here means the FhirMetaDataRestController is emitting the wrong version and clients will refuse to interoperate',
        );
    }

    public function testSmartConfigurationEndpointDiscoverable(): void
    {
        // Per SMART-on-FHIR conformance
        // (https://hl7.org/fhir/smart-app-launch/conformance.html),
        // .well-known/smart-configuration carries the OAuth endpoint
        // URLs SMART clients need. Must be unauthenticated (it's the
        // discovery step BEFORE any auth handshake).
        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/apis/default/fhir/.well-known/smart-configuration',
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'SMART .well-known/smart-configuration must return 200 without auth per the SMART conformance spec',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            'SMART well-known should return a JSON object',
        );
        // authorization_endpoint + token_endpoint are the minimum
        // signals a SMART client uses to bootstrap the auth flow. If
        // either is missing OR is not a usable URL, no SMART client
        // can proceed. Per the SMART conformance spec both fields are
        // string values that MUST be absolute URLs — assert not just
        // "present" but "valid enough for a client to actually hit."
        self::assertArrayHasKey(
            'authorization_endpoint',
            $body,
            'SMART config must expose authorization_endpoint — clients can discover the server but cannot start auth without it',
        );
        self::assertArrayHasKey(
            'token_endpoint',
            $body,
            'SMART config must expose token_endpoint — clients cannot exchange codes for tokens without it',
        );
        self::assertIsString($body['authorization_endpoint']);
        self::assertNotFalse(
            filter_var($body['authorization_endpoint'], FILTER_VALIDATE_URL),
            'authorization_endpoint must be a valid absolute URL per SMART spec — empty string or malformed value means clients cannot start the auth flow',
        );
        self::assertIsString($body['token_endpoint']);
        self::assertNotFalse(
            filter_var($body['token_endpoint'], FILTER_VALIDATE_URL),
            'token_endpoint must be a valid absolute URL per SMART spec — empty string or malformed value means clients cannot exchange codes for tokens',
        );
    }
}
