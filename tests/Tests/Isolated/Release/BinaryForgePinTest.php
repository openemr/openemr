<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\BinaryForgePin;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Locks the forge naming scheme down. Every string this class builds is
 * pasted into a download URL by docker/binary/Dockerfile, so a silent
 * change here shows up as a build-time 404 rather than a test failure —
 * hence the literal expectations copied from a real forge release.
 */
final class BinaryForgePinTest extends TestCase
{
    public function testDerivesThePhpSelectorTheDockerfileWay(): void
    {
        self::assertSame('php85', (new BinaryForgePin('8_3_0', '08232026', '8.5'))->phpSelector());
        self::assertSame('php84', (new BinaryForgePin('8_3_0', '08232026', '8.4'))->phpSelector());
    }

    public function testBuildsTheForgeReleaseTagForEachArch(): void
    {
        $pin = new BinaryForgePin('8_3_0', '08232026', '8.5');

        self::assertSame('linux_amd64-php85-openemr-v8_3_0-amd64-08232026', $pin->forgeTag('amd64'));
        self::assertSame('linux_arm64-php85-openemr-v8_3_0-arm64-08232026', $pin->forgeTag('arm64'));
    }

    public function testRequiredAssetsMatchTheDockerfileDownloads(): void
    {
        $pin = new BinaryForgePin('8_3_0', '08232026', '8.5');

        self::assertSame(
            ['php-fpm-v8_3_0-linux-arm64', 'php-cli-v8_3_0-linux-arm64', 'openemr.phar'],
            $pin->requiredAssets('arm64'),
        );
    }

    public function testOpenemrTagPrefixesTheVersion(): void
    {
        self::assertSame('v7_0_3_4', (new BinaryForgePin('7_0_3_4', '12072025', '8.5'))->openemrTag());
    }

    /**
     * The whole point of the reordering: MMDDYYYY sorts December 2025
     * above March 2026 as a plain string, which would let a re-cut of an
     * older line look newer than the current pin.
     */
    public function testChronologicalDateOrdersAcrossYearBoundaries(): void
    {
        $december2025 = (new BinaryForgePin('7_0_4', '12292025', '8.5'))->chronologicalDate();
        $march2026 = (new BinaryForgePin('8_0_0', '03102026', '8.5'))->chronologicalDate();

        self::assertSame('20251229', $december2025);
        self::assertSame('20260310', $march2026);
        self::assertGreaterThan($december2025, $march2026);
    }

    public function testDottedVersionFeedsVersionCompare(): void
    {
        self::assertSame('7.0.3.4', (new BinaryForgePin('7_0_3_4', '12072025', '8.5'))->dottedVersion());
    }

    /**
     * The case that matters: a re-cut of an older line carrying a build
     * date *later* than the current pin. Comparing dates alone accepts it
     * and walks the image backwards.
     */
    public function testARecutOfAnOlderLineNeverSupersedes(): void
    {
        $current = new BinaryForgePin('8_0_0', '03102026', '8.5');
        $olderLineRebuiltLater = new BinaryForgePin('7_0_4', '09012026', '8.5');

        self::assertFalse($current->isSupersededBy($olderLineRebuiltLater));
    }

    public function testANewerVersionSupersedesEvenWithAnEarlierBuildDate(): void
    {
        $current = new BinaryForgePin('8_0_0', '03102026', '8.5');
        $newerLineBuiltEarlier = new BinaryForgePin('8_3_0', '01052026', '8.5');

        self::assertTrue($current->isSupersededBy($newerLineBuiltEarlier));
    }

    public function testALaterRebuildOfTheSameVersionSupersedes(): void
    {
        $current = new BinaryForgePin('7_0_4', '12282025', '8.5');

        self::assertTrue($current->isSupersededBy(new BinaryForgePin('7_0_4', '12292025', '8.5')));
        self::assertFalse($current->isSupersededBy(new BinaryForgePin('7_0_4', '12262025', '8.5')));
        self::assertFalse($current->isSupersededBy(new BinaryForgePin('7_0_4', '12282025', '8.5')));
    }

    /**
     * A four-segment patch line sorts below the three-segment release it
     * patches from, so `7_0_3_4` must not look newer than `7_0_4`.
     */
    public function testPatchLineSegmentsCompareNumericallyNotLexically(): void
    {
        $current = new BinaryForgePin('7_0_4', '12292025', '8.5');

        self::assertFalse($current->isSupersededBy(new BinaryForgePin('7_0_3_4', '09012026', '8.5')));
    }

    public function testStringFormIsTheUpdatecliWireFormat(): void
    {
        self::assertSame('8_3_0/08232026', (string)new BinaryForgePin('8_3_0', '08232026', '8.5'));
    }

    #[DataProvider('malformedPinProvider')]
    public function testRejectsMalformedPins(string $version, string $date, string $php): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new BinaryForgePin($version, $date, $php);
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedPinProvider(): array
    {
        return [
            'dotted version' => ['8.3.0', '08232026', '8.5'],
            'single-segment version' => ['8', '08232026', '8.5'],
            'v-prefixed version' => ['v8_3_0', '08232026', '8.5'],
            'short date' => ['8_3_0', '0823206', '8.5'],
            'dashed date' => ['8_3_0', '2026-08-23', '8.5'],
            'underscored php version' => ['8_3_0', '08232026', '8_5'],
            'three-segment php version' => ['8_3_0', '08232026', '8.5.1'],
        ];
    }
}
