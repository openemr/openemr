<?php

/**
 * Isolated tests for the UDS ZIP residence value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use DomainException;
use OpenEMR\FQHC\Reporting\ZipResidence;
use PHPUnit\Framework\TestCase;

final class ZipResidenceTest extends TestCase
{
    public function testOfZipKeepsFiveDigitCode(): void
    {
        $residence = ZipResidence::ofZip('02118');

        self::assertSame('02118', $residence->zip);
        self::assertFalse($residence->isUnknown());
        self::assertSame('02118', $residence->key());
        self::assertSame('02118', $residence->label());
    }

    public function testOfZipRejectsNonFiveDigit(): void
    {
        $this->expectException(DomainException::class);

        ZipResidence::ofZip('123');
    }

    public function testUnknownResidenceHasItsOwnKeyAndLabel(): void
    {
        $residence = ZipResidence::unknown();

        self::assertTrue($residence->isUnknown());
        self::assertNull($residence->zip);
        self::assertSame('Unknown Residence', $residence->label());
        self::assertNotSame($residence->key(), ZipResidence::ofZip('00000')->key());
    }

    public function testFromRawZipExtractsFivedigitPrefixFromZipPlusFour(): void
    {
        self::assertSame('94110', ZipResidence::fromRawZip('94110-1234')->zip);
    }

    public function testFromRawZipFallsBackToUnknownForUnusableInput(): void
    {
        self::assertTrue(ZipResidence::fromRawZip(null)->isUnknown());
        self::assertTrue(ZipResidence::fromRawZip('  ')->isUnknown());
        self::assertTrue(ZipResidence::fromRawZip('ABCDE')->isUnknown());
    }
}
