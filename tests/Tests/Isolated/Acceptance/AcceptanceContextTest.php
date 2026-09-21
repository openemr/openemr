<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Acceptance;

use OpenEMR\Tests\Acceptance\Support\AcceptanceContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * `AcceptanceContext` is the single-seam resolver every acceptance test
 * reads its runtime context through. When its plumbing regresses,
 * every downstream acceptance cell fails at business-assertion time
 * with confusing messages (the G35 pattern -- see
 * `docs/release-mechanism-gaps.md`). These tests pin the resolver's
 * behavior at the layer that owns the concern so a break shows up
 * here first, with a clear message.
 */
#[Group('isolated')]
class AcceptanceContextTest extends TestCase
{
    /**
     * @var array<string, string|false> pristine values to restore after each test
     */
    private array $savedEnv = [];

    protected function setUp(): void
    {
        foreach (['ACCEPTANCE_EXPECTED_VERSION', 'ACCEPTANCE_ARTIFACT_URL', 'ACCEPTANCE_TRUST_SELF_SIGNED'] as $name) {
            $this->savedEnv[$name] = getenv($name);
            putenv($name);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $name => $value) {
            if ($value === false) {
                putenv($name);
                continue;
            }
            putenv("{$name}={$value}");
        }
    }

    public function testExpectedVersionReturnsValueWhenSetToXYZShape(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.4.1');
        self::assertSame('8.4.1', AcceptanceContext::expectedVersion());
    }

    public function testExpectedVersionThrowsWhenUnset(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACCEPTANCE_EXPECTED_VERSION env is unset/');
        AcceptanceContext::expectedVersion();
    }

    public function testExpectedVersionThrowsWhenEmpty(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/ACCEPTANCE_EXPECTED_VERSION env is unset/');
        AcceptanceContext::expectedVersion();
    }

    public function testExpectedVersionThrowsOnDevSuffix(): void
    {
        // The G35 shape: master's version.php is `8.5.0-dev`, and if
        // that leaks into ACCEPTANCE_EXPECTED_VERSION the caller must
        // hear about it here, not via downstream assertion failures.
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.5.0-dev');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/does not match required X\.Y\.Z shape/');
        AcceptanceContext::expectedVersion();
    }

    public function testExpectedVersionThrowsOnPartialShape(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.4');
        $this->expectException(RuntimeException::class);
        AcceptanceContext::expectedVersion();
    }

    public function testExpectedVersionThrowsOnFourSegments(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.4.1.0');
        $this->expectException(RuntimeException::class);
        AcceptanceContext::expectedVersion();
    }

    public function testHasExpectedVersionTrueWhenSetAndValid(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.4.1');
        self::assertTrue(AcceptanceContext::hasExpectedVersion());
    }

    public function testHasExpectedVersionFalseWhenUnset(): void
    {
        self::assertFalse(AcceptanceContext::hasExpectedVersion());
    }

    public function testHasExpectedVersionFalseWhenEmpty(): void
    {
        putenv('ACCEPTANCE_EXPECTED_VERSION=');
        self::assertFalse(AcceptanceContext::hasExpectedVersion());
    }

    public function testHasExpectedVersionFalseWhenMalformed(): void
    {
        // The predicate is stricter than "env is set" -- it also
        // rejects malformed values so callers that gate on it don't
        // then hit the shape check inside expectedVersion() and get
        // a surprise throw.
        putenv('ACCEPTANCE_EXPECTED_VERSION=8.5.0-dev');
        self::assertFalse(AcceptanceContext::hasExpectedVersion());
    }

    public function testArtifactUrlDefaultsToLocalhost8580WhenUnset(): void
    {
        self::assertSame('http://localhost:8580', AcceptanceContext::artifactUrl());
    }

    public function testArtifactUrlDefaultsToLocalhost8580WhenEmpty(): void
    {
        putenv('ACCEPTANCE_ARTIFACT_URL=');
        self::assertSame('http://localhost:8580', AcceptanceContext::artifactUrl());
    }

    public function testArtifactUrlStripsTrailingSlash(): void
    {
        putenv('ACCEPTANCE_ARTIFACT_URL=https://openemr.example.com/');
        self::assertSame('https://openemr.example.com', AcceptanceContext::artifactUrl());
    }

    public function testArtifactUrlStripsMultipleTrailingSlashes(): void
    {
        putenv('ACCEPTANCE_ARTIFACT_URL=https://openemr.example.com///');
        self::assertSame('https://openemr.example.com', AcceptanceContext::artifactUrl());
    }

    public function testArtifactUrlFallsBackToDefaultWhenTrimmedToEmpty(): void
    {
        // An env value of "/" alone rtrims to empty and would break
        // callers if returned as-is; fall back to default.
        putenv('ACCEPTANCE_ARTIFACT_URL=/');
        self::assertSame('http://localhost:8580', AcceptanceContext::artifactUrl());
    }

    public function testTrustSelfSignedFalseByDefault(): void
    {
        self::assertFalse(AcceptanceContext::trustSelfSigned());
    }

    public function testTrustSelfSignedTrueOn1(): void
    {
        putenv('ACCEPTANCE_TRUST_SELF_SIGNED=1');
        self::assertTrue(AcceptanceContext::trustSelfSigned());
    }

    public function testTrustSelfSignedTrueOnTrue(): void
    {
        putenv('ACCEPTANCE_TRUST_SELF_SIGNED=true');
        self::assertTrue(AcceptanceContext::trustSelfSigned());
    }

    public function testTrustSelfSignedFalseOnOtherValues(): void
    {
        // Explicit strict allowlist -- any other value (yes, y, 0,
        // "TRUE", etc.) is false. Prevents typo-driven silent-trust
        // regressions.
        foreach (['yes', 'y', 'TRUE', 'True', '0', 'false', 'no'] as $value) {
            putenv("ACCEPTANCE_TRUST_SELF_SIGNED={$value}");
            self::assertFalse(
                AcceptanceContext::trustSelfSigned(),
                "ACCEPTANCE_TRUST_SELF_SIGNED='{$value}' must NOT enable trust",
            );
        }
    }
}
