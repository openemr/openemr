<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use OpenEMR\PostCalendar\CalendarFacilityResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarFacilityResolverTest extends TestCase
{
    /**
     * @return iterable<string, array{int|string, array<string, mixed>, array<string, mixed>, int|string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function selectionProvider(): iterable
    {
        yield 'date-only request preserves session selection' => [7, [], ['date' => '20260813'], 7];
        yield 'POST changes selection' => [7, ['pc_facility' => '9'], [], '9'];
        yield 'GET changes selection' => [7, [], ['pc_facility' => '11'], '11'];
        yield 'GET retains precedence over POST' => [7, ['pc_facility' => '9'], ['pc_facility' => '11'], '11'];
        yield 'integer zero resets selection' => [7, ['pc_facility' => 0], [], 0];
        yield 'string zero resets selection' => [7, [], ['pc_facility' => '0'], '0'];
        yield 'empty selection resets selection' => [7, ['pc_facility' => ''], [], 0];
    }

    #[DataProvider('selectionProvider')]
    public function testExplicitAndImplicitSelection(
        int|string $currentFacility,
        array $post,
        array $get,
        int|string $expected
    ): void {
        self::assertSame($expected, CalendarFacilityResolver::resolve(
            $currentFacility,
            $post,
            $get,
            false,
            3,
            false,
            null,
            false,
            []
        ));
    }

    public function testLoginFacilityCannotBeOverriddenByRequestOrSession(): void
    {
        self::assertSame(3, CalendarFacilityResolver::resolve(
            7,
            ['pc_facility' => 9],
            ['pc_facility' => 11],
            true,
            3,
            true,
            5,
            false,
            []
        ));
    }

    public function testCookieIsOnlyUsedAsAnUnsetSessionDefault(): void
    {
        self::assertSame('5', CalendarFacilityResolver::resolve(
            null,
            [],
            [],
            false,
            null,
            true,
            '5',
            false,
            []
        ));
        self::assertSame(0, CalendarFacilityResolver::resolve(
            0,
            [],
            [],
            false,
            null,
            true,
            '5',
            false,
            []
        ));
    }

    public function testRestrictedUserKeepsAllowedSelectionAndFallsBackFromDisallowedSelection(): void
    {
        self::assertSame('7', CalendarFacilityResolver::resolve(
            5,
            ['pc_facility' => '7'],
            [],
            false,
            null,
            true,
            '9',
            true,
            [5, 7]
        ));
        self::assertSame(5, CalendarFacilityResolver::resolve(
            7,
            ['pc_facility' => 99],
            [],
            false,
            null,
            false,
            null,
            true,
            [5, 7]
        ));
    }
}
