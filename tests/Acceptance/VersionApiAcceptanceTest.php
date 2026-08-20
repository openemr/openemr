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
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Post-install / post-upgrade assertion that `/api/version` returns
 * the version the operator expected, in the shape external tooling
 * relies on.
 *
 * `/api/version` is on the SkipAuthorizationStrategy allowlist
 * (`src/RestControllers/Subscriber/AuthorizationListener.php:98`) so
 * no Bearer token is required, but the endpoint STILL requires the
 * REST API to be globally enabled — hence the `api-enabled` group
 * gate so this only runs after `tests/Acceptance/bin/api-enable.php`
 * has flipped the `rest_api` global.
 *
 * Currently `VersionRestController` reads from the `version` DB row
 * via `VersionService->fetch()`, so this signal overlaps with the
 * shell-level DB-version assertion in `boot-package.sh` /
 * `upgrade-package.sh`. Kept independent because:
 *
 *   1. `/api/version` is the operator-facing programmatic-version
 *      discovery endpoint — a routing/serialization regression here
 *      is a real customer-facing outage even if the DB row is
 *      correct.
 *   2. There's a reasonable future path where `/api/version` shifts
 *      to reading `version.php` at request-time (matching the model
 *      About page already uses via `SoftwareVersion::fromGlobals()`).
 *      Independent coverage now means we catch that shift as a
 *      signal rather than a coverage gap.
 *
 * Companion signals (all part of openemr/openemr#13634):
 *   - **DB version** — shell-asserted in `boot-package.sh` and
 *     `upgrade-package.sh` (direct mysql query).
 *   - **File version** — asserted in `VersionDisplayAcceptanceTest`
 *     (About page render).
 *
 * Tagged `version-api` (a dedicated group, not `api-enabled`) for
 * the same reason `VersionDisplayAcceptanceTest` uses `version-display`
 * — piggybacking `api-enabled` would leak this into
 * `acceptance-docker.yml`, which today does not resolve floating
 * image tags into X.Y.Z at runtime and so can't set
 * `ACCEPTANCE_EXPECTED_VERSION`. Docker parity is a bounded
 * follow-up. Runs only after `api-enable.php` has flipped the
 * `rest_api` global (workflow ordering).
 */
#[Group('version-api')]
final class VersionApiAcceptanceTest extends TestCase
{
    /**
     * `/apis/default/api/version` — the REST dispatcher strips the
     * `/apis/default/` prefix before matching routes, so
     * `AuthorizationListener`'s `/api/version` skip-list entry
     * matches on the tail. Same shape ApiSmokeTest uses for
     * `/apis/default/api/facility`. Single constant so every
     * assertion diagnostic reports the exact URL actually requested
     * (single source of truth — avoids CI-triage drift where a
     * failure message names a different endpoint than the test hit).
     */
    private const VERSION_ENDPOINT = '/apis/default/api/version';

    public function testVersionEndpointReturnsExpectedVersion(): void
    {
        $expected = getenv('ACCEPTANCE_EXPECTED_VERSION');
        self::assertNotFalse(
            $expected,
            'ACCEPTANCE_EXPECTED_VERSION env is unset — the acceptance-package.yml matrix cell must set this so the test knows which version to assert against. Passing an empty string is not a valid override.',
        );
        self::assertMatchesRegularExpression(
            '/^\d+\.\d+\.\d+$/',
            $expected,
            "ACCEPTANCE_EXPECTED_VERSION='{$expected}' does not match required X.Y.Z shape",
        );

        $browser = ArtifactBrowser::create();
        $browser->request('GET', ArtifactBrowser::baseUrl() . self::VERSION_ENDPOINT);
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            self::VERSION_ENDPOINT . ' must return 200 on an api-enabled install — a 404 means the REST API bootstrap (api-enable.php) did not flip the rest_api global before this test ran, a 500 means VersionRestController itself is broken, and any 3xx means the auth-skip allowlist regressed',
        );

        // Assert JSON media type BEFORE json_decode — a JSON-shaped
        // body served with text/html would still parse but signal a
        // real content-negotiation regression that machine-readable
        // clients would break on.
        self::assertStringStartsWith(
            'application/json',
            ResponseHeaders::first($response, 'Content-Type'),
            self::VERSION_ENDPOINT . ' must be served as application/json — a different media type means RestControllerHelper::responseHandler has regressed',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            self::VERSION_ENDPOINT . ' body must be a JSON object — a non-array decode result means the endpoint returned an empty body, a scalar, or malformed JSON',
        );

        foreach (['v_major', 'v_minor', 'v_patch'] as $key) {
            self::assertArrayHasKey(
                $key,
                $body,
                self::VERSION_ENDPOINT . " payload must include '{$key}' — missing key means VersionService->fetch() shape regressed",
            );
            self::assertIsInt(
                $body[$key],
                self::VERSION_ENDPOINT . " payload '{$key}' must be an integer — the DB stores these as ints and VersionService casts to int; a non-int here means the response shape or cast regressed",
            );
        }

        $actual = "{$body['v_major']}.{$body['v_minor']}.{$body['v_patch']}";
        self::assertSame(
            $expected,
            $actual,
            self::VERSION_ENDPOINT . " returned version '{$actual}' does not match expected '{$expected}'. "
            . 'Currently this endpoint reads the `version` DB row, so a mismatch here means '
            . 'either sql_upgrade.php did not bump the version row (silent-no-op class, see '
            . '#13586/#13587 for the shape) OR install-helper.php seeded the wrong row. See '
            . 'openemr/openemr#13634 for the full signal-source rationale.',
        );
    }
}
