<?php

/**
 * Isolated EncounterOptions Test
 *
 * EncounterService returns three parallel lists, so the number of encounters
 * is the length of one of them. Counting the outer array instead offered only
 * three encounters whatever the patient had.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms\Types;

use OpenEMR\Common\Forms\Types\EncounterOptions;
use PHPUnit\Framework\TestCase;

final class EncounterOptionsTest extends TestCase
{
    /**
     * @param list<array{string, string, string}> $encounters eid, date, category
     * @return array<array-key, mixed>
     */
    private static function encounterList(array $encounters): array
    {
        $list = ['ids' => [], 'dates' => [], 'categories' => []];
        foreach ($encounters as $index => [$eid, $date, $category]) {
            $list['ids'][$index] = $eid;
            $list['dates'][$index] = $date;
            $list['categories'][$index] = $category;
        }
        return $list;
    }

    public function testListsEveryEncounterNotJustThree(): void
    {
        $encounters = [];
        for ($i = 1; $i <= 7; $i++) {
            $encounters[] = ["26$i", sprintf('2026-01-%02d', $i), 'Office Visit'];
        }
        $options = EncounterOptions::fromEncounterList(self::encounterList($encounters));
        self::assertCount(7, $options);
    }

    public function testMostRecentEncounterComesFirst(): void
    {
        // Deliberately not in date order: the service applies no ORDER BY.
        $options = EncounterOptions::fromEncounterList(self::encounterList([
            ['2661', '2002-12-17', 'Office Visit'],
            ['2679', '2026-06-06', 'Encounter for symptom'],
            ['2670', '2013-01-01', 'Office Visit'],
        ]));
        self::assertSame(['2679', '2670', '2661'], array_column($options, 'value'));
    }

    public function testLabelCombinesDateAndCategory(): void
    {
        $options = EncounterOptions::fromEncounterList(self::encounterList([
            ['2679', '2026-06-06', 'Encounter for symptom'],
        ]));
        self::assertSame('2026-06-06 - Encounter for symptom', $options[0]['label']);
    }

    public function testLabelOmitsAMissingCategory(): void
    {
        $options = EncounterOptions::fromEncounterList(self::encounterList([
            ['2679', '2026-06-06', ''],
        ]));
        self::assertSame('2026-06-06', $options[0]['label']);
    }

    public function testEncountersWithoutAnIdAreSkipped(): void
    {
        $options = EncounterOptions::fromEncounterList(self::encounterList([
            ['', '2026-06-06', 'Office Visit'],
            ['2679', '2026-06-05', 'Office Visit'],
        ]));
        self::assertSame(['2679'], array_column($options, 'value'));
    }

    public function testIntegerIdsAndDatesAreAccepted(): void
    {
        $options = EncounterOptions::fromEncounterList([
            'ids' => [0 => 2679],
            'dates' => [0 => '2026-06-06'],
            'categories' => [0 => 'Office Visit'],
        ]);
        self::assertSame('2679', $options[0]['value']);
    }

    public function testAnEmptyOrMalformedListGivesNoOptions(): void
    {
        self::assertSame([], EncounterOptions::fromEncounterList([]));
        self::assertSame([], EncounterOptions::fromEncounterList(['ids' => 'not an array']));
        self::assertSame([], EncounterOptions::fromEncounterList(['ids' => [], 'dates' => [], 'categories' => []]));
    }

    public function testContainsFindsAnOfferedEncounter(): void
    {
        $options = EncounterOptions::fromEncounterList(self::encounterList([
            ['2679', '2026-06-06', 'Office Visit'],
            ['2661', '2002-12-17', 'Office Visit'],
        ]));
        self::assertTrue(EncounterOptions::contains($options, '2661'));
        self::assertFalse(EncounterOptions::contains($options, '9999'));
        self::assertFalse(EncounterOptions::contains($options, ''));
    }
}
