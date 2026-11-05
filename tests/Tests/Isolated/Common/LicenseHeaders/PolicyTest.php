<?php

/**
 * Tests loading the license header policy and matching paths against it
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\LicenseHeaders;

use OpenEMR\Common\LicenseHeaders\Policy;
use OpenEMR\Common\LicenseHeaders\PolicyException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

#[Group('isolated')]
final class PolicyTest extends TestCase
{
    private const POLICY = <<<'YAML'
        standard: "Standard   License"
        licenses:
        - value: LGPL
          reason: Library
          paths:
          - lib/*
        - value: LGPL
          reason: One more file
          paths:
          - extra/one.php
        exempt:
        - reason: Generated
          paths:
          - gen/*
          - '*.min.js'
        YAML;

    public function testStandardLicenseIsAllowedEverywhere(): void
    {
        $policy = Policy::fromYaml(self::POLICY);
        self::assertSame('Standard License', $policy->standardLicense);
        self::assertTrue($policy->allowsLicense('any/where.php', 'Standard License'));
        self::assertTrue($policy->allowsLicense('any/where.php', " Standard \t License "));
    }

    public function testOtherLicensesAreAllowedOnlyUnderTheirPaths(): void
    {
        $policy = Policy::fromYaml(self::POLICY);
        self::assertTrue($policy->allowsLicense('lib/deep/file.php', 'LGPL'));
        self::assertTrue($policy->allowsLicense('extra/one.php', 'LGPL'));
        self::assertFalse($policy->allowsLicense('extra/two.php', 'LGPL'));
        self::assertFalse($policy->allowsLicense('lib/file.php', 'MIT'));
    }

    public function testExemptionsMatchAcrossDirectories(): void
    {
        $policy = Policy::fromYaml(self::POLICY);
        self::assertSame('Generated', $policy->exemptionReason('gen/a/b/c.php'));
        self::assertSame('Generated', $policy->exemptionReason('public/x.min.js'));
        self::assertNull($policy->exemptionReason('src/x.js'));
    }

    public function testListsAreOptional(): void
    {
        $policy = Policy::fromYaml('standard: S');
        self::assertNull($policy->exemptionReason('a.php'));
        self::assertFalse($policy->allowsLicense('a.php', 'LGPL'));
    }

    public function testLoadsFromFile(): void
    {
        $filesystem = new Filesystem();
        $path = $filesystem->tempnam(sys_get_temp_dir(), 'policy');
        try {
            $filesystem->dumpFile($path, self::POLICY);
            self::assertSame('Standard License', Policy::fromFile($path)->standardLicense);
        } finally {
            $filesystem->remove($path);
        }
    }

    #[DataProvider('invalidPolicyProvider')]
    public function testRejectsInvalidPolicy(string $yaml, string $message): void
    {
        $this->expectException(PolicyException::class);
        $this->expectExceptionMessage($message);
        Policy::fromYaml($yaml);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidPolicyProvider(): array
    {
        return [
            'not a mapping' => ['just a string', 'must be a YAML mapping'],
            'no standard' => ['licenses: []', 'non-empty `standard`'],
            'blank standard' => ['standard: " "', 'non-empty `standard`'],
            'licenses not a list' => ["standard: S\nlicenses: {a: b}", '`licenses` must be a list'],
            'entry not a mapping' => ["standard: S\nexempt: [x]", 'exempt[0] must be a mapping'],
            'license without value' => ["standard: S\nlicenses: [{reason: r, paths: [a]}]", 'licenses[0] needs a non-empty `value`'],
            'no reason' => ["standard: S\nexempt: [{paths: [a]}]", 'exempt[0] needs a non-empty `reason`'],
            'no paths' => ["standard: S\nexempt: [{reason: r}]", 'exempt[0] needs a non-empty `paths` list'],
            'empty paths' => ["standard: S\nexempt: [{reason: r, paths: []}]", 'exempt[0] needs a non-empty `paths` list'],
            'path not a string' => ["standard: S\nexempt: [{reason: r, paths: [1]}]", 'exempt[0] has a path that is not a non-empty string'],
        ];
    }
}
