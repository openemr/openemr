<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance\Support;

use RuntimeException;

/**
 * Central resolver for the `ACCEPTANCE_*` env contract that carries
 * runtime context from CI matrix cells into acceptance tests.
 *
 * Acceptance tests are black-box HTTP callers against a booted artifact
 * (docker image or extracted tarball). The matrix cell that boots the
 * artifact knows which version it's booting, what URL it's serving on,
 * whether the endpoint uses a self-signed cert, and (for upgrade
 * scenarios) which prior version it upgraded from. Tests need read
 * access to that context to assert against the right expectations.
 *
 * Historically each test reached directly into `getenv()`, duplicating
 * the fail-fast + shape-validation code and giving no single place to
 * reason about "what is this test running against?" That coupling made
 * plumbing regressions (e.g., G35: `ACCEPTANCE_EXPECTED_VERSION` misread
 * as master's `8.5.0-dev` instead of the shipped target `8.4.0`) show
 * up as cascading business-assertion failures across every acceptance
 * cell instead of as a single clear "context misconfigured" error at
 * the resolver layer. Item 1 of the post-8.4.0 acceptance-surface
 * refactor plan (see `docs/artifact-acceptance-testing-plan.md`)
 * introduces this class as that seam.
 *
 * The class is intentionally minimal at introduction: only the env
 * reads that already exist in tests today are consolidated here.
 * Scenario-name / feature-flag / workflow-context accessors are Item
 * 2/3 work and NOT in scope here — those need the group-tag split
 * (Item 2) and the boot-orchestration composite (Item 3) to have
 * landed first so the resolver knows where its inputs come from.
 *
 * Convention: every accessor for an optional env var pairs a
 * `hasFoo(): bool` predicate with a `foo(): string` getter, so
 * callers that want to skip cleanly (e.g., docker floating-tag runs
 * where `expectedVersion` isn't knowable) can gate on the predicate
 * without a try/catch. Required-but-unset env vars fail fast via
 * `RuntimeException` from the getter — tests should never see a
 * partially-configured context.
 */
final class AcceptanceContext
{
    /**
     * The version this test run should assert the artifact reports.
     * Set by the acceptance-package.yml (and eventually docker) matrix
     * cell that booted the artifact.
     *
     * Required for tests tagged with the version-check groups
     * (`version-display`, `version-api`) — those cells always set it,
     * so an unset value there is a matrix-plumbing bug worth failing
     * loudly on.
     *
     * @throws RuntimeException when the env is unset or empty.
     * @throws RuntimeException when the env doesn't match X.Y.Z shape.
     */
    public static function expectedVersion(): string
    {
        $raw = getenv('ACCEPTANCE_EXPECTED_VERSION');
        if ($raw === false || $raw === '') {
            throw new RuntimeException(
                'ACCEPTANCE_EXPECTED_VERSION env is unset — the acceptance-package.yml matrix cell must set this so the test knows which version to assert against. Passing an empty string is not a valid override.',
            );
        }
        if (preg_match('/^\d+\.\d+\.\d+$/', $raw) !== 1) {
            throw new RuntimeException(
                "ACCEPTANCE_EXPECTED_VERSION='{$raw}' does not match required X.Y.Z shape",
            );
        }
        return $raw;
    }

    /**
     * Whether an expected-version signal is available in this context.
     * Tests that can run in contexts without a knowable version (e.g.,
     * Item 4's future docker floating-tag runs) should gate on this
     * before calling `expectedVersion()`.
     */
    public static function hasExpectedVersion(): bool
    {
        $raw = getenv('ACCEPTANCE_EXPECTED_VERSION');
        if ($raw === false || $raw === '') {
            return false;
        }
        return preg_match('/^\d+\.\d+\.\d+$/', $raw) === 1;
    }

    /**
     * Base URL of the artifact under test. Defaults to
     * `http://localhost:8580` (the port `tests/Acceptance/bin/boot-docker.sh`
     * binds by default) when the env is unset.
     *
     * Trailing slashes are stripped so callers can uniformly append
     * paths like `/interface/login/login.php` without double-slash risk.
     *
     * @return non-empty-string
     */
    public static function artifactUrl(): string
    {
        $url = getenv('ACCEPTANCE_ARTIFACT_URL');
        if ($url !== false && $url !== '') {
            $trimmed = rtrim($url, '/');
            if ($trimmed !== '') {
                return $trimmed;
            }
        }
        return 'http://localhost:8580';
    }

    /**
     * Whether the artifact endpoint should be trusted despite a
     * self-signed cert. Opt-in via `ACCEPTANCE_TRUST_SELF_SIGNED=1`
     * (or `=true`) for cases where the artifact URL doesn't parse as
     * an obviously-local host but the operator knows the endpoint is
     * safe (e.g., a sidecar container reachable only over a private
     * network).
     *
     * The typical local-artifact case (loopback, container-runtime
     * host aliases) is handled by `ArtifactBrowser::isLocalArtifact()`
     * independently — this predicate is the explicit-opt-in escape
     * hatch, not the whole trust decision.
     */
    public static function trustSelfSigned(): bool
    {
        $optIn = getenv('ACCEPTANCE_TRUST_SELF_SIGNED');
        return $optIn === '1' || $optIn === 'true';
    }
}
