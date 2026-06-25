<?php

/**
 * Isolated tests for special-population statuses and subtype validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\SpecialPopulation;

use DomainException;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulation;
use OpenEMR\FQHC\SpecialPopulation\SpecialPopulationStatus;
use PHPUnit\Framework\TestCase;

final class SpecialPopulationStatusTest extends TestCase
{
    public function testAcceptsValidSubtype(): void
    {
        $status = new SpecialPopulationStatus(SpecialPopulation::Homeless, 'shelter');

        self::assertSame('Sheltered', $status->subtypeLabel());
        self::assertSame('Homeless — Sheltered', $status->displayLabel());
    }

    public function testAgriculturalWorkerSubtype(): void
    {
        $status = new SpecialPopulationStatus(SpecialPopulation::AgriculturalWorker, 'migratory');

        self::assertSame('Agricultural worker — Migratory', $status->displayLabel());
    }

    public function testPopulationWithoutSubtypeDisplaysPlainLabel(): void
    {
        $status = new SpecialPopulationStatus(SpecialPopulation::Veteran);

        self::assertNull($status->subtypeLabel());
        self::assertSame('Veteran', $status->displayLabel());
    }

    public function testRejectsSubtypeInvalidForPopulation(): void
    {
        $this->expectException(DomainException::class);
        new SpecialPopulationStatus(SpecialPopulation::Homeless, 'migratory');
    }

    public function testRejectsSubtypeOnPopulationThatHasNone(): void
    {
        $this->expectException(DomainException::class);
        new SpecialPopulationStatus(SpecialPopulation::Veteran, 'shelter');
    }

    public function testSubtypeOptionsCounts(): void
    {
        self::assertCount(2, SpecialPopulation::AgriculturalWorker->subtypeOptions());
        self::assertCount(6, SpecialPopulation::Homeless->subtypeOptions());
        self::assertCount(0, SpecialPopulation::PublicHousing->subtypeOptions());
    }
}
