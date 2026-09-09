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
     * @return iterable<string, array{mixed, mixed, mixed, int|string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function selectionProvider(): iterable
    {
        yield 'date-only request preserves session selection' => [7, null, null, 7];
        yield 'POST changes selection' => [7, '9', null, '9'];
        yield 'GET changes selection' => [7, null, '11', '11'];
        yield 'GET retains precedence over POST' => [7, '9', '11', '11'];
        yield 'integer zero resets selection' => [7, 0, null, 0];
        yield 'string zero resets selection' => [7, null, '0', '0'];
        yield 'empty selection resets selection' => [7, '', null, 0];
        yield 'array POST selection is ignored' => [7, ['9'], null, 7];
        yield 'array GET selection is ignored' => [7, null, ['11'], 7];
        yield 'malformed scalar session selection is preserved' => ['facility-seven', null, null, 'facility-seven'];
        yield 'malformed scalar request selection is preserved' => [
            7,
            'facility-nine',
            null,
            'facility-nine',
        ];
        yield 'null session without cookies selects all facilities' => [null, null, null, 0];
    }

    #[DataProvider('selectionProvider')]
    public function testExplicitAndImplicitSelection(
        mixed $currentFacility,
        mixed $postFacility,
        mixed $getFacility,
        int|string $expected
    ): void {
        self::assertSame($expected, CalendarFacilityResolver::resolve(
            $currentFacility,
            $postFacility,
            $getFacility,
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
            9,
            11,
            true,
            3,
            true,
            5,
            false,
            []
        ));
    }

    public function testRestrictedDisallowedLoginFacilityFallsBackToFirstAllowedFacility(): void
    {
        self::assertSame(5, CalendarFacilityResolver::resolve(
            7,
            9,
            11,
            true,
            3,
            true,
            13,
            true,
            [5, 7]
        ));
    }

    public function testRestrictedAllowedLoginFacilityCannotBeOverriddenByRequestValues(): void
    {
        self::assertSame('3', CalendarFacilityResolver::resolve(
            7,
            9,
            11,
            true,
            '3',
            true,
            13,
            true,
            [3, 5]
        ));
    }

    public function testCookieIsOnlyUsedAsAnUnsetSessionDefault(): void
    {
        self::assertSame('5', CalendarFacilityResolver::resolve(
            null,
            null,
            null,
            false,
            null,
            true,
            '5',
            false,
            []
        ));
        self::assertSame(0, CalendarFacilityResolver::resolve(
            0,
            null,
            null,
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
            '7',
            null,
            false,
            null,
            true,
            '9',
            true,
            [5, 7]
        ));
        self::assertSame(5, CalendarFacilityResolver::resolve(
            7,
            99,
            null,
            false,
            null,
            false,
            null,
            true,
            [5, 7]
        ));
    }

    public function testRestrictedMatchingRetainsLooseNumericCandidateType(): void
    {
        self::assertSame('7', CalendarFacilityResolver::resolve(
            null,
            '7',
            null,
            false,
            null,
            false,
            null,
            true,
            [7]
        ));
    }

    public function testRestrictedAllFacilitiesSelectionFallsBackToFirstAllowedFacility(): void
    {
        self::assertSame(5, CalendarFacilityResolver::resolve(
            0,
            null,
            null,
            false,
            null,
            false,
            null,
            true,
            [5, 7]
        ));
    }

    public function testRestrictedEmptyAllowedListPreservesLegacyNullFallback(): void
    {
        self::assertNull(CalendarFacilityResolver::resolve(
            7,
            null,
            null,
            false,
            null,
            false,
            null,
            true,
            []
        ));
    }
}
