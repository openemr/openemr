<?php

/**
 * SearchFieldStatementResolver Unit Tests
 *
 * Verifies that search field names, which are concatenated into generated SQL
 * as column identifiers and cannot be parameter bound, are validated across
 * ALL field types (string, token, date, reference, and composite children) so
 * a maliciously constructed field name can never inject SQL.  Also pins the
 * expected SQL fragments and bound values for the happy paths so refactors of
 * the resolver do not silently change query generation.
 *
 * @package   OpenEMR
 * @author    Stephen Waite <stephen.waite@open-emr.org>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@open-emr.org>
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\ReferenceSearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SearchFieldStatementResolverTest extends TestCase
{
    /**
     * Field names that must be rejected because they would otherwise be
     * concatenated into SQL as identifiers.
     *
     * @return array<string, array{string}>
     */
    public static function invalidFieldIdentifierProvider(): array
    {
        return [
            'sql injection or clause'   => ["name = '' OR 1=1 -- "],
            'statement terminator'      => ["name; DROP TABLE patient_data"],
            'subquery'                  => ["(SELECT password FROM users LIMIT 1)"],
            'backtick quoted'           => ["`name`"],
            'double dotted'             => ["a.b.c"],
            'embedded space'            => ["name OR"],
            'comment sequence'          => ["name--"],
            'union keyword with space'  => ["name UNION SELECT"],
            'empty string'              => [""],
            'hyphenated fhir param'     => ["questionnaire-code"],
            'wildcard'                  => ["*"],
            'parenthesized function'    => ["LOWER(name)"],
            'numeric literal'           => ["42"],
            'decimal numeric literal'   => ["1.2"],
            'digit-leading identifier'  => ["9col"],
            'digit-leading dotted part' => ["patient.9col"],
        ];
    }

    /**
     * Legitimate identifiers that must continue to resolve.
     *
     * @return array<string, array{string}>
     */
    public static function validFieldIdentifierProvider(): array
    {
        return [
            'simple column'  => ['name'],
            'underscored'    => ['policy_number'],
            'dotted column'  => ['patient.puuid'],
            'numeric suffix' => ['line1'],
        ];
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testStringFieldRejectsInvalidIdentifier(string $fieldName): void
    {
        $this->expectException(SearchFieldException::class);
        SearchFieldStatementResolver::resolveStringSearchField(
            new StringSearchField($fieldName, ['value'], SearchModifier::CONTAINS)
        );
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testTokenFieldRejectsInvalidIdentifier(string $fieldName): void
    {
        $searchField = new TokenSearchField($fieldName, [new TokenSearchValue(false)]);
        $searchField->setModifier(SearchModifier::MISSING);
        $this->expectException(SearchFieldException::class);
        SearchFieldStatementResolver::resolveTokenField($searchField);
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testDateFieldRejectsInvalidIdentifier(string $fieldName): void
    {
        $this->expectException(SearchFieldException::class);
        SearchFieldStatementResolver::resolveDateField(
            new DateSearchField($fieldName, ['ge2024-01-01'], DateSearchField::DATE_TYPE_DATE)
        );
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testReferenceFieldRejectsInvalidIdentifier(string $fieldName): void
    {
        $this->expectException(SearchFieldException::class);
        SearchFieldStatementResolver::resolveReferenceField(
            new ReferenceSearchField($fieldName, ['Patient/123'])
        );
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testCompositeFieldRejectsInvalidChildIdentifier(string $fieldName): void
    {
        $composite = new CompositeSearchField('combined', [], true);
        $composite->addChild(new StringSearchField('name', ['ok'], SearchModifier::EXACT));
        $composite->addChild(new StringSearchField($fieldName, ['value'], SearchModifier::CONTAINS));
        $this->expectException(SearchFieldException::class);
        SearchFieldStatementResolver::getStatementForSearchField($composite);
    }

    #[Test]
    #[DataProvider('invalidFieldIdentifierProvider')]
    public function testGuardRunsBeforeEmptyValueCheck(string $fieldName): void
    {
        // The identifier guard must fire before the empty-values check so a
        // hostile field name is never echoed back through the exception path.
        try {
            SearchFieldStatementResolver::resolveStringSearchField(
                new StringSearchField($fieldName, [], SearchModifier::CONTAINS)
            );
            $this->fail('Expected SearchFieldException was not thrown');
        } catch (SearchFieldException $exception) {
            $this->assertSame(
                'invalid search field',
                $exception->getField(),
                'Exception field must be the generic label, not the hostile input'
            );
            if ($fieldName !== '') {
                $this->assertStringNotContainsString(
                    $fieldName,
                    $exception->getMessage(),
                    'Raw hostile field name must not be reflected in the exception message'
                );
            }
        }
    }

    #[Test]
    #[DataProvider('validFieldIdentifierProvider')]
    public function testStringFieldAcceptsValidIdentifier(string $fieldName): void
    {
        $fragment = SearchFieldStatementResolver::resolveStringSearchField(
            new StringSearchField($fieldName, ['bob'], SearchModifier::CONTAINS)
        );
        $this->assertEquals($fieldName . ' LIKE ?', $fragment->getFragment());
        $this->assertEquals(['%bob%'], $fragment->getBoundValues());
    }

    #[Test]
    public function testStringFieldModifierFragments(): void
    {
        $cases = [
            [SearchModifier::PREFIX, 'name LIKE ?', ['bob%']],
            [SearchModifier::SUFFIX, 'name LIKE ?', ['%bob']],
            [SearchModifier::CONTAINS, 'name LIKE ?', ['%bob%']],
            [SearchModifier::EXACT, '(name = ? AND BINARY name = ?)', ['bob', 'bob']],
            [SearchModifier::NOT_EQUALS_EXACT, 'BINARY name != ?', ['bob']],
        ];
        foreach ($cases as [$modifier, $expectedSql, $expectedBinds]) {
            $fragment = SearchFieldStatementResolver::resolveStringSearchField(
                new StringSearchField('name', ['bob'], $modifier)
            );
            $this->assertEquals($expectedSql, $fragment->getFragment(), "modifier: " . $modifier);
            $this->assertEquals($expectedBinds, $fragment->getBoundValues(), "modifier: " . $modifier);
        }
    }

    #[Test]
    public function testStringFieldExactKeepsBinaryAloneForANumber(): void
    {
        // Compared with a number, an ENUM column gives the member's position, so only text gets the plain comparison.
        $fragment = SearchFieldStatementResolver::resolveStringSearchField(
            new StringSearchField('erx_source', [0], SearchModifier::EXACT)
        );
        $this->assertSame('BINARY erx_source = ?', $fragment->getFragment());
        $this->assertSame([0], $fragment->getBoundValues());
    }

    /**
     * @return array<string, array{bool, string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function likeWildcardProvider(): array
    {
        return [
            'literal prefix' => [true, SearchModifier::PREFIX, "name LIKE ? ESCAPE '!'", ['50!%!_a\\b!!%']],
            'literal suffix' => [true, SearchModifier::SUFFIX, "name LIKE ? ESCAPE '!'", ['%50!%!_a\\b!!']],
            'literal contains' => [true, SearchModifier::CONTAINS, "name LIKE ? ESCAPE '!'", ['%50!%!_a\\b!!%']],
            'literal exact is not a LIKE pattern' => [true, SearchModifier::EXACT, '(name = ? AND BINARY name = ?)', ['50%_a\\b!', '50%_a\\b!']],
            'wildcards stay for other callers, prefix' => [false, SearchModifier::PREFIX, 'name LIKE ?', ['50%_a\\b!%']],
            'wildcards stay for other callers, contains' => [false, SearchModifier::CONTAINS, 'name LIKE ?', ['%50%_a\\b!%']],
        ];
    }

    /**
     * @param list<string> $expectedBinds
     */
    #[Test]
    #[DataProvider('likeWildcardProvider')]
    public function testStringFieldLikeWildcards(bool $isLiteral, string $modifier, string $expectedSql, array $expectedBinds): void
    {
        $fragment = SearchFieldStatementResolver::resolveStringSearchField(
            new StringSearchField('name', ['50%_a\\b!'], $modifier, null, $isLiteral)
        );
        $this->assertSame($expectedSql, $fragment->getFragment());
        $this->assertSame($expectedBinds, $fragment->getBoundValues());
    }

    #[Test]
    public function testDateFieldEqualityUsesFuzzyBetweenRange(): void
    {
        $fragment = SearchFieldStatementResolver::resolveDateField(
            new DateSearchField('start_date', ['2024-01-01'], DateSearchField::DATE_TYPE_DATE)
        );
        $this->assertEquals('start_date BETWEEN ? AND ? ', $fragment->getFragment());
        $this->assertCount(2, $fragment->getBoundValues());
    }

    #[Test]
    public function testDateFieldComparatorOperator(): void
    {
        $fragment = SearchFieldStatementResolver::resolveDateField(
            new DateSearchField('date_end', ['ge2024-01-01'], DateSearchField::DATE_TYPE_DATE)
        );
        $this->assertEquals('date_end >= ?', $fragment->getFragment());
        $this->assertCount(1, $fragment->getBoundValues());
    }

    /**
     * Search values for 10:30 local time on a server in Europe/Berlin.  A winter and a
     * summer date are both covered, so one of them is always on the other side of a
     * daylight saving change from the day the test runs.
     *
     * @return array<string, array{string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateTimezoneProvider(): array
    {
        return [
            'winter, utc'         => ['2021-01-15T09:30:00Z', ['2021-01-15 10:30:00.000000', '2021-01-15 10:30:00.999000']],
            'summer, utc'         => ['2021-07-15T08:30:00Z', ['2021-07-15 10:30:00.000000', '2021-07-15 10:30:00.999000']],
            'winter, utc offset'  => ['2021-01-15T04:30:00-05:00', ['2021-01-15 10:30:00.000000', '2021-01-15 10:30:00.999000']],
            'winter, no timezone' => ['2021-01-15T10:30:00', ['2021-01-15 10:30:00.000000', '2021-01-15 10:30:00.999000']],
            'summer, no timezone' => ['2021-07-15T10:30:00', ['2021-07-15 10:30:00.000000', '2021-07-15 10:30:00.999000']],
        ];
    }

    /**
     * @param list<string> $expectedBinds
     */
    #[Test]
    #[DataProvider('dateTimezoneProvider')]
    public function testDateFieldBoundsUseOffsetOfSearchedDate(string $value, array $expectedBinds): void
    {
        $serverTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');
        try {
            $fragment = SearchFieldStatementResolver::resolveDateField(new DateSearchField('date', [$value]));
        } finally {
            date_default_timezone_set($serverTimezone);
        }
        $this->assertSame($expectedBinds, $fragment->getBoundValues());
    }

    #[Test]
    public function testTokenFieldMissingModifierFragments(): void
    {
        // missing=false token value => field must NOT be missing
        $notMissing = new TokenSearchField('provider', [new TokenSearchValue(false)]);
        $notMissing->setModifier(SearchModifier::MISSING);
        $fragment = SearchFieldStatementResolver::resolveTokenField($notMissing);
        $this->assertEquals(
            "(provider IS NOT NULL AND (CAST(provider AS CHAR) != '' OR TRIM(CAST(provider AS BINARY)) != '')) ",
            $fragment->getFragment()
        );
        $this->assertEmpty($fragment->getBoundValues());
    }

    #[Test]
    public function testReferenceFieldFragmentAndBinds(): void
    {
        $fragment = SearchFieldStatementResolver::resolveReferenceField(
            new ReferenceSearchField('pid', ['Patient/123'])
        );
        $this->assertEquals('pid = ?', $fragment->getFragment());
        $this->assertEquals(['123'], $fragment->getBoundValues());
    }
}
