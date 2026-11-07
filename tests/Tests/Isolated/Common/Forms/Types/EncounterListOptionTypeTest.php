<?php

/**
 * Isolated EncounterListOptionType Test
 *
 * The encounter select on the immunization, prescription and dispense screens:
 * every encounter offered, and the stored one selected.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Forms\Types;

use OpenEMR\Common\Forms\Types\EncounterListOptionType;
use OpenEMR\Services\EncounterService;
use PHPUnit\Framework\TestCase;

final class EncounterListOptionTypeTest extends TestCase
{
    private const FROW = ['field_id' => 'encounter_id', 'title' => 'Select Encounter', 'empty_name' => '-- Select Encounter --'];

    protected function setUp(): void
    {
        // Prevent code_types.inc.php from issuing DB queries when autoloading
        // BaseService (which EncounterService extends).
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }
    }

    private function widget(int $encounters): EncounterListOptionType
    {
        $list = ['ids' => [], 'dates' => [], 'categories' => []];
        for ($i = 1; $i <= $encounters; $i++) {
            $list['ids'][] = (string) (2660 + $i);
            $list['dates'][] = sprintf('2026-01-%02d', $i);
            $list['categories'][] = 'Office Visit';
        }
        $service = $this->createStub(EncounterService::class);
        $service->method('getPatientEncounterListWithCategories')->willReturn($list);
        $widget = new EncounterListOptionType(42);
        $widget->setEncounterService($service);
        return $widget;
    }

    /**
     * @return list<string> the value of each option, in order, with " selected" after a selected one
     */
    private static function options(string $html): array
    {
        preg_match_all('/<option value="([^"]*)"( selected)?>/', $html, $matches, PREG_SET_ORDER);
        return array_map(fn(array $m): string => $m[1] . ($m[2] ?? ''), $matches);
    }

    public function testEveryEncounterIsOfferedAfterThePlaceholder(): void
    {
        $options = self::options($this->widget(7)->buildFormView(self::FROW, ''));
        self::assertSame(['', '2667', '2666', '2665', '2664', '2663', '2662', '2661'], $options);
    }

    public function testTheStoredEncounterIsSelected(): void
    {
        $options = self::options($this->widget(7)->buildFormView(self::FROW, '2663'));
        self::assertSame(['2663 selected'], array_values(array_filter($options, fn(string $o): bool => str_ends_with($o, ' selected'))));
    }

    /**
     * No encounter is stored as 0 (an empty post into the BIGINT column under
     * the empty sql_mode), and one outside the patient's chart isn't offered:
     * either way the placeholder shows, as on master.
     */
    public function testNoEncounterOrOneOutsideTheChartLeavesThePlaceholder(): void
    {
        foreach (['0', '', '9999'] as $stored) {
            $options = self::options($this->widget(3)->buildFormView(self::FROW, $stored));
            self::assertSame(['', '2663', '2662', '2661'], $options, "stored '$stored'");
        }
    }
}
