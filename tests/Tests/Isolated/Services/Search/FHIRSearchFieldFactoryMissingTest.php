<?php

/**
 * Isolated tests for the :missing modifier as a FHIR request sends it to FHIRSearchFieldFactory
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    oldrobotdev <hi@oldrobot.us>
 * @copyright Copyright (c) 2026 oldrobotdev
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FHIRSearchFieldFactoryMissingTest extends TestCase
{
    /**
     * @param string|list<string> $value
     */
    private function whereFor(string $parameter, string|array $value): string
    {
        $factory = new FHIRSearchFieldFactory([
            'gender' => new FhirSearchParameterDefinition('gender', SearchFieldType::TOKEN, ['sex']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['fname', 'lname']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ]);
        $field = $factory->buildSearchField($parameter, $value);
        return FhirSearchWhereClauseBuilder::build([$parameter => $field])->getFragment();
    }

    /**
     * @return array<string, array{string, string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function missingProvider(): array
    {
        return [
            'several columns, missing=true needs every column empty' => [
                'name:missing', 'true',
                " WHERE ((fname IS NULL OR fname = '') AND (lname IS NULL OR lname = ''))",
            ],
            'several columns, missing=false needs any column' => [
                'name:missing', 'false',
                " WHERE ((fname IS NOT NULL AND fname != '') OR (lname IS NOT NULL AND lname != ''))",
            ],
            'uuid column, missing=false' => [
                '_id:missing', 'false',
                " WHERE (uuid IS NOT NULL AND (CAST(uuid AS CHAR) != '' OR TRIM(CAST(uuid AS BINARY)) != '')) ",
            ],
            'uuid column, missing=true' => [
                '_id:missing', 'true',
                " WHERE (uuid IS NULL OR (CAST(uuid AS CHAR) = '' AND TRIM(CAST(uuid AS BINARY)) = '')) ",
            ],
        ];
    }

    #[Test]
    #[DataProvider('missingProvider')]
    public function testMissingModifierFromRequest(string $parameter, string $value, string $expectedWhere): void
    {
        $this->assertSame($expectedWhere, $this->whereFor($parameter, $value));
    }

    /**
     * @return array<string, array{string, string|list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidMissingProvider(): array
    {
        return [
            'not true or false' => ['gender:missing', 'False'],
            'two values' => ['gender:missing', 'true,false'],
            'two values, several columns' => ['name:missing', 'true,false'],
            'a repeated parameter, several columns' => ['name:missing', ['true', 'false']],
            'a system' => ['gender:missing', 'bogus|false'],
            'an empty system' => ['gender:missing', '|false'],
        ];
    }

    /**
     * @param string|list<string> $value
     */
    #[Test]
    #[DataProvider('invalidMissingProvider')]
    public function testMissingModifierRejectsOtherValues(string $parameter, string|array $value): void
    {
        $this->expectException(SearchFieldException::class);
        $this->whereFor($parameter, $value);
    }
}
