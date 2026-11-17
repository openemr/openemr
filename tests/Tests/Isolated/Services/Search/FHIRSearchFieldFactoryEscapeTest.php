<?php

/**
 * Isolated tests for escaped characters in FHIR search values as a request sends them to FHIRSearchFieldFactory
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FHIRSearchFieldFactoryEscapeTest extends TestCase
{
    private function buildField(string $parameter, string $value): ISearchField
    {
        $factory = new FHIRSearchFieldFactory([
            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ['lname']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['fname', 'lname']),
            'identifier' => new FhirSearchParameterDefinition('identifier', SearchFieldType::TOKEN, ['ss']),
        ]);
        return $factory->buildSearchField($parameter, $value);
    }

    /**
     * The bound values of the LIKE matches use ! as their escape character.
     *
     * @return array<string, array{string, string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function escapedValueProvider(): array
    {
        return [
            'escaped comma is one string value' => [
                'family', 'Smith\,Jones',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['Smith,Jones%'],
            ],
            'unescaped comma still gives two string values' => [
                'family', 'Smith,Jones',
                ' WHERE (lname LIKE ? ESCAPE \'!\' OR lname LIKE ? ESCAPE \'!\')', ['Smith%', 'Jones%'],
            ],
            'escaped backslash before a comma gives two string values' => [
                'family:exact', 'a\\\\,b',
                ' WHERE (BINARY lname = ? OR BINARY lname = ?)', ['a\\', 'b'],
            ],
            'escaped dollar and pipe in a string value' => [
                'family', 'a\$b\|c',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['a$b|c%'],
            ],
            'escaped comma in a string value on several columns' => [
                'name', 'Smith\,Jones',
                ' WHERE (fname LIKE ? ESCAPE \'!\' OR lname LIKE ? ESCAPE \'!\')', ['Smith,Jones%', 'Smith,Jones%'],
            ],
            'escaped backslash with the default prefix match' => [
                'family', 'O\\\\Brien',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['O\\Brien%'],
            ],
            'escaped backslash with :contains' => [
                'family:contains', 'O\\\\Brien',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['%O\\Brien%'],
            ],
            'escaped backslash with :suffix' => [
                'family:suffix', 'O\\\\Brien',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['%O\\Brien'],
            ],
            'escaped backslash with :exact is not a LIKE pattern' => [
                'family:exact', 'O\\\\Brien',
                ' WHERE BINARY lname = ?', ['O\\Brien'],
            ],
            'escaped backslash on several columns' => [
                'name', 'O\\\\Brien',
                ' WHERE (fname LIKE ? ESCAPE \'!\' OR lname LIKE ? ESCAPE \'!\')', ['O\\Brien%', 'O\\Brien%'],
            ],
            'percent, underscore and the escape character are plain characters' => [
                'family:contains', '100%_x!',
                ' WHERE lname LIKE ? ESCAPE \'!\'', ['%100!%!_x!!%'],
            ],
            'escaped pipe in a token code' => [
                'identifier', 'urn:oid:1.2.3|a\|b',
                ' WHERE ss = ?', ['a|b'],
            ],
            'escaped comma in a token code' => [
                'identifier', 'urn:oid:1.2.3|a\,b',
                ' WHERE ss = ?', ['a,b'],
            ],
            'escaped backslash in a token code' => [
                'identifier', 'urn:oid:1.2.3|a\\\\b',
                ' WHERE ss = ?', ['a\\b'],
            ],
        ];
    }

    /**
     * @param list<string> $expectedBound
     */
    #[DataProvider('escapedValueProvider')]
    public function testEscapedValueFromRequest(string $parameter, string $value, string $expectedWhere, array $expectedBound): void
    {
        $fragment = FhirSearchWhereClauseBuilder::build([$parameter => $this->buildField($parameter, $value)]);

        $this->assertSame($expectedWhere, $fragment->getFragment());
        $this->assertSame($expectedBound, $fragment->getBoundValues());
    }

    public function testParameterOnSeveralColumnsKeepsTheParsedValues(): void
    {
        $field = $this->buildField('name', 'Smith\,Jones,O\\\\Brien');

        $this->assertInstanceOf(CompositeSearchField::class, $field);
        $this->assertSame(['Smith,Jones', 'O\\Brien'], $field->getValues());
    }
}
