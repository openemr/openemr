<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command\ReleasePrep;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('release-prep')]
final class MutatorContextTest extends TestCase
{
    public function testFromVersionStringParsesComponents(): void
    {
        $context = MutatorContext::fromVersionString('/tmp/proj', '8.1.2');
        self::assertSame(8, $context->major);
        self::assertSame(1, $context->minor);
        self::assertSame(2, $context->patch);
        self::assertSame('8.1.2', $context->versionString());
        self::assertNull($context->imageDigest);
    }

    public function testFromVersionStringRejectsMalformedInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/MAJOR\.MINOR\.PATCH/');
        MutatorContext::fromVersionString('/tmp/proj', '8.1');
    }

    public function testValidImageDigestIsAccepted(): void
    {
        $digest = 'sha256:' . str_repeat('a', 64);
        $context = MutatorContext::fromVersionString('/tmp/proj', '8.1.0', $digest);
        self::assertSame($digest, $context->imageDigest);
    }

    public function testInvalidImageDigestRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/imageDigest/');
        MutatorContext::fromVersionString('/tmp/proj', '8.1.0', 'not-a-digest');
    }

    public function testImageDigestWithWrongPrefixRejected(): void
    {
        try {
            $context = new MutatorContext('/tmp/proj', 8, 1, 0, 'sha512:' . str_repeat('a', 64));
            self::fail('Expected InvalidArgumentException; got ' . $context::class);
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('imageDigest', $e->getMessage());
        }
    }

    public function testImageDigestWithShortHexRejected(): void
    {
        try {
            $context = new MutatorContext('/tmp/proj', 8, 1, 0, 'sha256:' . str_repeat('a', 63));
            self::fail('Expected InvalidArgumentException; got ' . $context::class);
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('imageDigest', $e->getMessage());
        }
    }

    public function testValidPrevRelBranchIsAccepted(): void
    {
        $context = MutatorContext::fromVersionString(
            '/tmp/proj',
            '8.2.0',
            null,
            'rel-820',
            'rel-810',
        );
        self::assertSame('rel-810', $context->prevRelBranch);
        self::assertSame('rel-820', $context->relBranch);
    }

    public function testInvalidPrevRelBranchRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/prevRelBranch/');
        MutatorContext::fromVersionString(
            '/tmp/proj',
            '8.2.0',
            null,
            'rel-820',
            'master',
        );
    }

    public function testPrevRelBranchDefaultsToNull(): void
    {
        $context = MutatorContext::fromVersionString('/tmp/proj', '8.2.0', null, 'rel-820');
        self::assertNull($context->prevRelBranch);
    }

    public function testFromVersionDefaultsToNull(): void
    {
        $context = MutatorContext::fromVersionString('/tmp/proj', '8.1.1');
        self::assertNull($context->fromVersion);
    }

    public function testValidFromVersionIsAccepted(): void
    {
        $context = MutatorContext::fromVersionString(
            '/tmp/proj',
            '8.1.1',
            null,
            'rel-810',
            null,
            '8.1.0',
        );
        self::assertSame('8.1.0', $context->fromVersion);
    }

    public function testInvalidFromVersionRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/fromVersion/');
        MutatorContext::fromVersionString(
            '/tmp/proj',
            '8.1.1',
            null,
            'rel-810',
            null,
            '8.1', // missing patch component
        );
    }

    public function testFromVersionWithNonNumericComponentRejected(): void
    {
        try {
            new MutatorContext('/tmp/proj', 8, 1, 1, null, null, null, '8.x.0');
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('fromVersion', $e->getMessage());
        }
    }

    public function testFromVersionWithMismatchedMajorRejected(): void
    {
        // target 8.1.2 with fromVersion 7.1.1 — major mismatch.
        try {
            new MutatorContext('/tmp/proj', 8, 1, 2, null, null, null, '7.1.1');
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('share major.minor', $e->getMessage());
        }
    }

    public function testFromVersionWithMismatchedMinorRejected(): void
    {
        // target 8.1.2 with fromVersion 8.0.9 — manual recovery passing
        // a stale prior-minor patch would silently produce a wrong-anchor
        // context. Must be rejected.
        try {
            MutatorContext::fromVersionString(
                '/tmp/proj',
                '8.1.2',
                null,
                'rel-810',
                null,
                '8.0.9',
            );
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('share major.minor', $e->getMessage());
        }
    }

    public function testFromVersionWithNonImmediatePriorPatchRejected(): void
    {
        // target 8.1.3 with fromVersion 8.1.1 — gap of more than 1.
        try {
            MutatorContext::fromVersionString(
                '/tmp/proj',
                '8.1.3',
                null,
                'rel-810',
                null,
                '8.1.1',
            );
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('immediate prior patch', $e->getMessage());
        }
    }

    public function testFromVersionEqualToTargetPatchRejected(): void
    {
        // target 8.1.1 with fromVersion 8.1.1 — must differ by exactly 1.
        try {
            MutatorContext::fromVersionString(
                '/tmp/proj',
                '8.1.1',
                null,
                'rel-810',
                null,
                '8.1.1',
            );
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('immediate prior patch', $e->getMessage());
        }
    }

    public function testFromVersionAheadOfTargetRejected(): void
    {
        // target 8.1.1 with fromVersion 8.1.2 — backwards bump.
        try {
            MutatorContext::fromVersionString(
                '/tmp/proj',
                '8.1.1',
                null,
                'rel-810',
                null,
                '8.1.2',
            );
            self::fail('Expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            self::assertStringContainsString('immediate prior patch', $e->getMessage());
        }
    }
}
