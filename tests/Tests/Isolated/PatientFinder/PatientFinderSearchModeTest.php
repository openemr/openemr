<?php

/**
 * Isolated PatientFinderSearchMode and PatientFinderTermMatch Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderSearchMode;
use OpenEMR\Services\PatientFinder\PatientFinderTermMatch;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientFinderSearchModeTest extends TestCase
{
    #[DataProvider('patternProvider')]
    public function testPattern(PatientFinderTermMatch $match, bool $exact, string $expected): void
    {
        $datePattern = static fn(string $term): string => 'date:' . $term;

        $this->assertSame($expected, $match->pattern('abc', $exact, $datePattern));
    }

    /**
     * @return array<string, array{PatientFinderTermMatch, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function patternProvider(): array
    {
        return [
            'start' => [PatientFinderTermMatch::Start, false, 'abc%'],
            'anywhere' => [PatientFinderTermMatch::Anywhere, false, '%abc%'],
            'end' => [PatientFinderTermMatch::End, false, '%abc'],
            'date' => [PatientFinderTermMatch::Date, false, 'date:abc'],
            'exact start' => [PatientFinderTermMatch::Start, true, 'abc'],
            'exact anywhere' => [PatientFinderTermMatch::Anywhere, true, 'abc'],
            'exact end is still the end, so the last 4 of an SSN match' => [PatientFinderTermMatch::End, true, '%abc'],
            'exact date is still a date' => [PatientFinderTermMatch::Date, true, 'date:abc'],
        ];
    }

    /**
     * A term's own %, _ and the escape character match only themselves.
     */
    public function testWildcardsInTheTermMatchOnlyThemselves(): void
    {
        $datePattern = static fn(string $term): string => $term;

        $this->assertSame('%a|%b|_c||%', PatientFinderTermMatch::Anywhere->pattern('a%b_c|', false, $datePattern));
        $this->assertSame('j|_smith', PatientFinderTermMatch::Start->pattern('j_smith', true, $datePattern));
        $this->assertSame('a\\b%', PatientFinderTermMatch::Start->pattern('a\\b', false, $datePattern), 'a backslash is plain text');
    }

    /**
     * A phone number typed with a country code of 1 to 3 digits also gives the number
     * without it, when at least 9 digits are left; other modes give none.
     */
    public function testPhoneTermWithoutCountryCode(): void
    {
        $this->assertSame(
            ['5554443333', '554443333'],
            PatientFinderSearchMode::Phone->withoutCountryCode('15554443333')
        );
        $this->assertSame(
            ['4207123456789', '207123456789', '07123456789'],
            PatientFinderSearchMode::Phone->withoutCountryCode('44207123456789')
        );
        // A 7-digit local number never comes out, so it cannot match a number from another area.
        $this->assertNotContains('4443333', PatientFinderSearchMode::Phone->withoutCountryCode('8134443333'));
        $this->assertSame([], PatientFinderSearchMode::Phone->withoutCountryCode('4443333'), 'too short to hold a code');
        $this->assertSame([], PatientFinderSearchMode::Ssn->withoutCountryCode('15554443333'));
    }

    public function testPhoneAndSsnCompareOnlyDigits(): void
    {
        $this->assertSame('5554443333', PatientFinderSearchMode::Phone->normalizeTerm('(555) 444-3333'));
        $this->assertSame('5544', PatientFinderSearchMode::Ssn->normalizeTerm('55-44'));
        $this->assertSame('', PatientFinderSearchMode::Phone->normalizeTerm('abc'));
        $this->assertSame('(555) 444', PatientFinderSearchMode::Address->normalizeTerm('(555) 444'));
    }

    public function testDigitColumnsStripSeparatorsFromTheField(): void
    {
        $columns = PatientFinderSearchMode::Ssn->globalSearchColumns() ?? [];

        $this->assertCount(1, $columns);
        $this->assertTrue($columns[0]->isSql());
        $expression = $columns[0]->searchExpression;
        $this->assertNotNull($expression);
        $this->assertStringContainsString("REPLACE(patient_data.ss, '-', '')", $expression);
    }

    public function testEveryModeIsAMenuValue(): void
    {
        foreach (PatientFinderSearchMode::cases() as $mode) {
            $this->assertSame($mode, PatientFinderSearchMode::fromRequestValue($mode->value));
        }
        $this->assertSame(PatientFinderSearchMode::All, PatientFinderSearchMode::fromRequestValue('nope'));
    }
}
