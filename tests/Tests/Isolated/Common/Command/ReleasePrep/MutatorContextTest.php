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
}
