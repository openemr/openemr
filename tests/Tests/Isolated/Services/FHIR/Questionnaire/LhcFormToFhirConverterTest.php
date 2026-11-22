<?php

/**
 * LHC form definitions, in the shapes NLM publishes them, converted to FHIR.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Questionnaire;

use InvalidArgumentException;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormConversionIssue;
use OpenEMR\Services\FHIR\Questionnaire\LhcFormToFhirConverter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class LhcFormToFhirConverterTest extends TestCase
{
    private LhcFormToFhirConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new LhcFormToFhirConverter();
    }

    public function testFormLevelFieldsMapToTheResource(): void
    {
        $questionnaire = $this->converter->convert([
            'name' => 'PHQ-9',
            'code' => '44249-1',
            'codeSystem' => 'http://loinc.org',
            'codingInstructions' => '<b>Self</b> administered',
            'copyrightNotice' => "Copyright \u{00C2}\u{00A9} Example",
            'items' => [self::item()],
        ])->questionnaire;

        $this->assertSame('Questionnaire', $questionnaire['resourceType']);
        $this->assertSame('active', $questionnaire['status']);
        $this->assertSame('PHQ-9', $questionnaire['title']);
        $this->assertSame('Self administered', $questionnaire['description']);
        $this->assertSame("Copyright \u{00A9} Example", $questionnaire['copyright']);
        $this->assertSame(
            [['system' => 'http://loinc.org', 'code' => '44249-1', 'display' => 'PHQ-9']],
            $questionnaire['code']
        );
    }

    public function testAFormWithNoItemsIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->converter->convert(['name' => 'Empty', 'items' => []]);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dataTypeProvider(): array
    {
        return [
            'coded no exceptions' => ['CNE', 'choice'],
            'coded with exceptions' => ['CWE', 'open-choice'],
            'string' => ['ST', 'string'],
            'email' => ['EMAIL', 'string'],
            'text' => ['TX', 'text'],
            'integer' => ['INT', 'integer'],
            'real' => ['REAL', 'decimal'],
            'numeric' => ['NR', 'decimal'],
            'quantity' => ['QTY', 'quantity'],
            'date' => ['DT', 'date'],
            'date time' => ['DTM', 'dateTime'],
            'time' => ['TM', 'time'],
            'boolean' => ['BL', 'boolean'],
            'url' => ['URL', 'url'],
            'title' => ['TITLE', 'display'],
        ];
    }

    #[DataProvider('dataTypeProvider')]
    public function testDataTypesMap(string $dataType, string $fhirType): void
    {
        $this->assertSame($fhirType, $this->firstItem(self::item(['dataType' => $dataType]))['type']);
    }

    public function testAnUnknownDataTypeBecomesTextAndIsReported(): void
    {
        $conversion = $this->converter->convert(['items' => [self::item(['dataType' => 'WHAT', 'question' => 'Odd'])]]);

        $this->assertSame('string', self::firstOf($conversion->questionnaire)['type']);
        $this->assertSame([['issue' => LhcFormConversionIssue::UnknownDataType, 'question' => 'Odd']], $conversion->issues);
    }

    public function testAHeaderWithItemsIsAGroupAndKeepsItsChildren(): void
    {
        $item = $this->firstItem(self::item([
            'header' => true,
            'dataType' => 'SECTION',
            'linkId' => '/s',
            'items' => [self::item(['linkId' => '/s/q'])],
        ]));

        $this->assertSame('group', $item['type']);
        $children = $item['item'];
        $this->assertIsArray($children);
        $this->assertCount(1, $children);
    }

    public function testAHeaderWithNoItemsIsDisplayBecauseFhirGroupsNeedChildren(): void
    {
        $this->assertSame('display', $this->firstItem(self::item(['header' => true]))['type']);
    }

    public function testMissingLinkIdsAreBuiltFromThePathAndDuplicatesMadeUnique(): void
    {
        $questionnaire = $this->converter->convert(['items' => [
            self::item(['linkId' => null, 'questionCode' => '1-1']),
            self::item(['linkId' => '/1-1', 'questionCode' => '2-2']),
        ]])->questionnaire;

        $this->assertIsArray($questionnaire['item']);
        $this->assertSame(['/1-1', '/1-1_2'], array_column($questionnaire['item'], 'linkId'));
    }

    public function testAnswersBecomeCodingsWithTheirScores(): void
    {
        $item = $this->firstItem(self::item(['dataType' => 'CNE', 'answers' => [
            ['code' => 'LA1', 'text' => 'Not at all', 'score' => 0, 'system' => 'http://loinc.org'],
            ['code' => 'LA2', 'text' => 'Several days', 'score' => null, 'system' => null],
        ]]));

        $this->assertSame([
            ['valueCoding' => [
                'system' => 'http://loinc.org',
                'code' => 'LA1',
                'display' => 'Not at all',
                'extension' => [['url' => 'http://hl7.org/fhir/StructureDefinition/ordinalValue', 'valueDecimal' => 0]],
            ]],
            ['valueCoding' => ['code' => 'LA2', 'display' => 'Several days']],
        ], $item['answerOption']);
    }

    public function testAnOtherAnswerMakesTheItemOpenChoice(): void
    {
        $item = $this->firstItem(self::item(['dataType' => 'CNE', 'answers' => [
            ['code' => 'A', 'text' => 'A'],
            ['code' => 'O', 'text' => 'Other', 'other' => 'Please specify'],
        ]]));

        $this->assertSame('open-choice', $item['type']);
        $this->assertIsArray($item['answerOption']);
        $this->assertCount(1, $item['answerOption']);
    }

    public function testTotalScoreSumsOnlyTheScoredSiblings(): void
    {
        $questionnaire = $this->converter->convert(['items' => [
            self::item(['linkId' => '/a', 'dataType' => 'CNE', 'answers' => [['code' => 'x', 'text' => 'x', 'score' => 1]]]),
            self::item(['linkId' => '/plain', 'dataType' => 'CNE', 'answers' => [['code' => 'y', 'text' => 'y', 'score' => null]]]),
            self::item(['linkId' => '/b', 'dataType' => 'CNE', 'answers' => [['code' => 'z', 'text' => 'z', 'score' => 2]]]),
            self::item(['linkId' => '/total', 'dataType' => 'REAL', 'calculationMethod' => ['name' => 'TOTALSCORE']]),
        ]])->questionnaire;

        $this->assertIsArray($questionnaire['item']);
        $total = $questionnaire['item'][3];
        $this->assertIsArray($total);
        $this->assertTrue($total['readOnly']);
        $this->assertSame([[
            'url' => 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
            'valueExpression' => [
                'description' => 'Total score',
                'language' => 'text/fhirpath',
                'expression' => "%resource.repeat(item).where(linkId = '/a' or linkId = '/b').answer.value.weight().sum()",
            ],
        ]], $total['extension']);
    }

    public function testATotalWithNothingToSumIsReported(): void
    {
        $conversion = $this->converter->convert(['items' => [
            self::item(['question' => 'Total', 'calculationMethod' => ['name' => 'TOTALSCORE']]),
        ]]);

        $this->assertArrayNotHasKey('extension', self::firstOf($conversion->questionnaire));
        $this->assertSame(LhcFormConversionIssue::ScoreDropped, $conversion->issues[0]['issue']);
    }

    public function testOneUnitIsAFixedUnit(): void
    {
        $item = $this->firstItem(self::item(['dataType' => 'REAL', 'units' => [
            ['name' => '{score}', 'code' => '{score}', 'system' => 'http://unitsofmeasure.org'],
        ]]));

        $this->assertSame('decimal', $item['type']);
        $this->assertSame([[
            'url' => 'http://hl7.org/fhir/StructureDefinition/questionnaire-unit',
            'valueCoding' => ['system' => 'http://unitsofmeasure.org', 'code' => '{score}', 'display' => '{score}'],
        ]], $item['extension']);
    }

    public function testSeveralUnitsMakeAQuantityWithOptions(): void
    {
        $item = $this->firstItem(self::item(['dataType' => 'REAL', 'units' => [
            ['name' => 'feet', 'code' => null],
            ['name' => 'meters', 'code' => null],
        ]]));

        $this->assertSame('quantity', $item['type']);
        $this->assertSame([
            ['url' => 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption', 'valueCoding' => ['display' => 'feet']],
            ['url' => 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption', 'valueCoding' => ['display' => 'meters']],
        ], $item['extension']);
    }

    public function testCardinalityBecomesRepeatsAndRequired(): void
    {
        $item = $this->firstItem(self::item([
            'answerCardinality' => ['min' => '1', 'max' => '*'],
        ]));

        $this->assertTrue($item['repeats']);
        $this->assertTrue($item['required']);
    }

    public function testCodingInstructionsBecomeAHelpItem(): void
    {
        $item = $this->firstItem(self::item(['linkId' => '/q', 'codingInstructions' => '<i>Pick</i> one']));

        $this->assertIsArray($item['item']);
        $help = $item['item'][0];
        $this->assertIsArray($help);
        $this->assertSame('/q_help', $help['linkId']);
        $this->assertSame('Pick one', $help['text']);
        $this->assertSame('display', $help['type']);
    }

    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function triggerProvider(): array
    {
        return [
            'coded value' => [['value' => ['code' => 'A', 'system' => 'http://loinc.org']], ['operator' => '=', 'answerCoding' => ['system' => 'http://loinc.org', 'code' => 'A']]],
            'older coded trigger' => [['code' => 'A'], ['operator' => '=', 'answerCoding' => ['code' => 'A']]],
            'minimum' => [['minInclusive' => 5], ['operator' => '>=', 'answerDecimal' => 5]],
            'exclusive maximum' => [['maxExclusive' => 9], ['operator' => '<', 'answerDecimal' => 9]],
            'not equal' => [['notEqual' => 4], ['operator' => '!=', 'answerDecimal' => 4]],
            'exists' => [['exists' => true], ['operator' => 'exists', 'answerBoolean' => true]],
        ];
    }

    /**
     * @param array<string, mixed> $trigger
     * @param array<string, mixed> $expected
     */
    #[DataProvider('triggerProvider')]
    public function testSkipLogicBecomesEnableWhen(array $trigger, array $expected): void
    {
        $questionnaire = $this->converter->convert(['items' => [
            self::item(['linkId' => '/follow', 'skipLogic' => [
                'action' => 'show',
                'conditions' => [['source' => '/src', 'trigger' => $trigger]],
            ]]),
            self::item(['linkId' => '/src', 'dataType' => 'REAL']),
        ]])->questionnaire;

        $this->assertSame([['question' => '/src'] + $expected], self::firstOf($questionnaire)['enableWhen']);
    }

    public function testSkipLogicCanFindItsSourceByQuestionCode(): void
    {
        $questionnaire = $this->converter->convert(['items' => [
            self::item(['linkId' => '/follow', 'skipLogic' => ['conditions' => [['source' => '9-9', 'trigger' => ['value' => ['code' => 'A']]]]]]),
            self::item(['linkId' => '/nested/src', 'questionCode' => '9-9', 'dataType' => 'CNE']),
        ]])->questionnaire;

        $this->assertSame([['question' => '/nested/src', 'operator' => '=', 'answerCoding' => ['code' => 'A']]], self::firstOf($questionnaire)['enableWhen']);
    }

    public function testSeveralConditionsCarryTheirLogic(): void
    {
        $questionnaire = $this->converter->convert(['items' => [
            self::item(['linkId' => '/follow', 'skipLogic' => ['logic' => 'ALL', 'conditions' => [
                ['source' => '/a', 'trigger' => ['value' => ['code' => '1']]],
                ['source' => '/b', 'trigger' => ['value' => ['code' => '2']]],
            ]]]),
            self::item(['linkId' => '/a', 'questionCode' => 'a']),
            self::item(['linkId' => '/b', 'questionCode' => 'b']),
        ]])->questionnaire;

        $this->assertSame('all', self::firstOf($questionnaire)['enableBehavior']);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unconvertibleSkipLogicProvider(): array
    {
        return [
            'hide action' => [['action' => 'hide', 'conditions' => [['source' => '/src', 'trigger' => ['value' => ['code' => 'A']]]]]],
            'unknown source' => [['conditions' => [['source' => '/missing', 'trigger' => ['value' => ['code' => 'A']]]]]],
            'range with both bounds' => [['conditions' => [['source' => '/src', 'trigger' => ['minInclusive' => 5, 'maxExclusive' => 10]]]]],
        ];
    }

    /**
     * @param array<string, mixed> $skipLogic
     */
    #[DataProvider('unconvertibleSkipLogicProvider')]
    public function testSkipLogicThatCannotConvertIsReported(array $skipLogic): void
    {
        $conversion = $this->converter->convert(['items' => [
            self::item(['linkId' => '/follow', 'question' => 'Follow', 'skipLogic' => $skipLogic]),
            self::item(['linkId' => '/src']),
        ]]);

        $this->assertArrayNotHasKey('enableWhen', self::firstOf($conversion->questionnaire));
        $this->assertSame([['issue' => LhcFormConversionIssue::SkipLogicDropped, 'question' => 'Follow']], $conversion->issues);
    }

    public function testMaxLengthIsKeptAndOtherRestrictionsReported(): void
    {
        $conversion = $this->converter->convert(['items' => [
            self::item(['question' => 'Q', 'restrictions' => ['maxLength' => '10', 'pattern' => '[a-z]']]),
        ]]);

        $this->assertSame(10, self::firstOf($conversion->questionnaire)['maxLength']);
        $this->assertSame([['issue' => LhcFormConversionIssue::RestrictionDropped, 'question' => 'Q']], $conversion->issues);
    }

    public function testADefaultAnswerBecomesInitial(): void
    {
        $choice = $this->firstItem(self::item([
            'dataType' => 'CNE',
            'answers' => [['code' => 'A', 'text' => 'Apple']],
            'defaultAnswer' => ['code' => 'A'],
        ]));
        $this->assertSame([['valueCoding' => ['code' => 'A', 'display' => 'Apple']]], $choice['initial']);

        $this->assertSame([['valueString' => 'hi']], $this->firstItem(self::item(['defaultAnswer' => 'hi']))['initial']);
        $this->assertSame([['valueInteger' => 3]], $this->firstItem(self::item(['dataType' => 'INT', 'defaultAnswer' => '3']))['initial']);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<mixed>
     */
    private function firstItem(array $item): array
    {
        return self::firstOf($this->converter->convert(['items' => [$item]])->questionnaire);
    }

    /**
     * @param array<string, mixed> $questionnaire
     * @return array<mixed>
     */
    private static function firstOf(array $questionnaire): array
    {
        self::assertIsArray($questionnaire['item']);
        $first = $questionnaire['item'][0];
        self::assertIsArray($first);

        return $first;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function item(array $overrides = []): array
    {
        return array_merge([
            'questionCode' => '1-1',
            'questionCodeSystem' => 'http://loinc.org',
            'question' => 'Question',
            'dataType' => 'ST',
            'header' => false,
            'linkId' => '/1-1',
        ], $overrides);
    }
}
