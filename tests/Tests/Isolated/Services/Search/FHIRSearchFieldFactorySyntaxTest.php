<?php

/**
 * The search field factory names a date it can't parse a SearchParameterSyntaxException, and leaves every other
 * rejected value an ordinary InvalidArgumentException, as FHIR R4 search 3.1.1.3 separates them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use InvalidArgumentException;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\FHIRSearchFieldFactory;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchParameterSyntaxException;
use OpenEMR\Services\Search\ServiceField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FHIRSearchFieldFactorySyntaxTest extends TestCase
{
    private function factory(): FHIRSearchFieldFactory
    {
        return new FHIRSearchFieldFactory([
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'birthdate' => new FhirSearchParameterDefinition('birthdate', SearchFieldType::DATE, ['DOB']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            'period' => new FhirSearchParameterDefinition('period', SearchFieldType::DATETIME, ['start', 'end']),
        ]);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedDateProvider(): array
    {
        return [
            'not a date' => ['birthdate', 'not-a-date'],
            'unknown comparator' => ['birthdate', 'xx2020-01-01'],
            'no year' => ['date', '--05-01'],
            'one of two OR values' => ['date', '2020-01-01,not-a-date'],
            'a malformed value beside an empty one' => ['date', 'not-a-date,'],
            'a date mapped to two columns' => ['period', 'not-a-date'],
            'the ap prefix before something that is not a date' => ['birthdate', 'apnot-a-date'],
        ];
    }

    #[DataProvider('malformedDateProvider')]
    public function testAMalformedDateIsASyntaxError(string $parameter, string $value): void
    {
        $this->expectException(SearchParameterSyntaxException::class);
        $this->factory()->buildSearchField($parameter, $value);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function emptyDateProvider(): array
    {
        return [
            'empty' => [''],
            'only a comma' => [','],
            'a valid value and an empty one' => ['2020-01-01,'],
        ];
    }

    /**
     * R4 search: "Empty parameters are not an error". An empty date value fails as it did before this change.
     */
    #[DataProvider('emptyDateProvider')]
    public function testAnEmptyDateValueIsNotASyntaxError(string $value): void
    {
        try {
            $this->factory()->buildSearchField('birthdate', $value);
            $this->fail('The date field still refuses an empty value, as before');
        } catch (InvalidArgumentException $exception) {
            $this->assertNotInstanceOf(SearchParameterSyntaxException::class, $exception);
        }
    }

    /**
     * R4 defines the ap prefix, but the date parser here doesn't support it. The value is well formed, so it fails
     * as it did before this change.
     */
    public function testTheApPrefixBeforeAWellFormedDateIsNotASyntaxError(): void
    {
        try {
            $this->factory()->buildSearchField('birthdate', 'ap2020-01-01');
            $this->fail('The date field still refuses the ap prefix, as before');
        } catch (InvalidArgumentException $exception) {
            $this->assertNotInstanceOf(SearchParameterSyntaxException::class, $exception);
        }
    }

    /**
     * The date values Inferno's US Core test kit sends (us_core_test_kit/search_test.rb: search_param_value() and
     * date_comparator_value()): a resource's own date at year, month, day or second precision, and gt/ge/lt/le
     * before a date one day off, written by Ruby's DateTime#xmlschema with a numeric offset. Each must parse, so
     * none of them is answered with a 400. A parameter mapped to two columns builds a composite of date fields.
     *
     * @return array<string, array{string, string, class-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function infernoUsCoreDateProvider(): array
    {
        return [
            'birthDate as exported' => ['birthdate', '1980-07-04', DateSearchField::class],
            'a year' => ['date', '2020', DateSearchField::class],
            'a month' => ['date', '2020-01', DateSearchField::class],
            'a day' => ['date', '2020-01-31', DateSearchField::class],
            'a dateTime in UTC' => ['date', '2020-01-31T13:45:00Z', DateSearchField::class],
            'a dateTime with an offset' => ['date', '2020-01-31T13:45:00+00:00', DateSearchField::class],
            'a dateTime with a negative offset' => ['date', '2020-01-31T08:45:00-05:00', DateSearchField::class],
            'a dateTime with fractional seconds' => ['date', '2020-01-31T13:45:00.123+00:00', DateSearchField::class],
            'gt before a day, as xmlschema writes it' => ['date', 'gt2020-01-30T00:00:00+00:00', DateSearchField::class],
            'ge before a day' => ['date', 'ge2020-01-30T13:45:00+00:00', DateSearchField::class],
            'lt after a day' => ['date', 'lt2020-02-01T13:45:00+00:00', DateSearchField::class],
            'le after a day' => ['date', 'le2020-02-01T13:45:00+00:00', DateSearchField::class],
            'gt from a year, as the kit converts it' => ['date', 'gt2019-12-31T00:00:00+00:00', DateSearchField::class],
            'gt on a period start' => ['period', 'gt2020-01-30T13:45:00+00:00', CompositeSearchField::class],
            'lt on a period end' => ['period', 'lt2020-02-01T13:45:00+00:00', CompositeSearchField::class],
        ];
    }

    /**
     * @param class-string $fieldClass
     */
    #[DataProvider('infernoUsCoreDateProvider')]
    public function testTheDatesInfernoSendsParse(string $parameter, string $value, string $fieldClass): void
    {
        $this->assertInstanceOf($fieldClass, $this->factory()->buildSearchField($parameter, $value));
    }

    public function testAWellFormedDateStillBuildsADateField(): void
    {
        $this->assertInstanceOf(DateSearchField::class, $this->factory()->buildSearchField('birthdate', 'ge2020-01-01'));
    }

    /**
     * Date parameters don't support :missing here, so its value is an unsupported parameter, not a malformed date.
     */
    public function testADateWithAModifierIsNotASyntaxError(): void
    {
        try {
            $this->factory()->buildSearchField('birthdate:missing', 'true');
            $this->fail('A date field still refuses a :missing value, as before');
        } catch (InvalidArgumentException $exception) {
            $this->assertNotInstanceOf(SearchParameterSyntaxException::class, $exception);
        }
    }

    public function testAnIdThatIsNotAUuidIsNotASyntaxError(): void
    {
        try {
            $this->factory()->buildSearchField('_id', 'not-a-uuid');
            $this->fail('A uuid column still refuses a value that is not a uuid');
        } catch (InvalidArgumentException $exception) {
            $this->assertNotInstanceOf(SearchParameterSyntaxException::class, $exception);
        }
    }
}
