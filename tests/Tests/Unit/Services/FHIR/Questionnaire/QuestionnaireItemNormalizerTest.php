<?php

/**
 * QuestionnaireItemNormalizerTest unit tests the shared repair/validation logic
 * for FHIR Questionnaire item array-typed fields: double-encoded strings are
 * decoded back to arrays; unrepairable values are reported (tolerant read path)
 * or rejected with a precise path (strict import path).
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Services\FHIR\Questionnaire;

use OpenEMR\Services\FHIR\Questionnaire\QuestionnaireItemNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QuestionnaireItemNormalizerTest extends TestCase
{
    public function testWellFormedItemPassesThroughUnchanged(): void
    {
        $item = [
            'linkId' => '1',
            'text' => 'Do you smoke?',
            'type' => 'boolean',
            'enableWhen' => [['question' => '0', 'operator' => '=', 'answerBoolean' => true]],
            'code' => [['system' => 'http://loinc.org', 'code' => '72166-2']],
        ];
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem($item);
        $this->assertSame($item, $normalized);
        $this->assertSame([], $repaired);
        $this->assertSame([], $unrepairable);
    }

    #[DataProvider('doubleEncodedFieldProvider')]
    public function testDoubleEncodedArrayFieldsAreRepaired(string $field): void
    {
        $value = [['some' => 'object']];
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem(
            ['linkId' => '1', $field => json_encode($value)]
        );
        $this->assertSame($value, $normalized[$field]);
        $this->assertSame([$field], $repaired);
        $this->assertSame([], $unrepairable);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function doubleEncodedFieldProvider(): array
    {
        return [
            'enableWhen' => ['enableWhen'],
            'code' => ['code'],
            'answerOption' => ['answerOption'],
            'initial' => ['initial'],
            'extension' => ['extension'],
            'modifierExtension' => ['modifierExtension'],
            'item' => ['item'],
        ];
    }

    #[DataProvider('unrepairableFieldValueProvider')]
    public function testUnrepairableFieldIsReportedNotModified(mixed $badValue): void
    {
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem(
            ['linkId' => '1', 'enableWhen' => $badValue]
        );
        $this->assertSame($badValue, $normalized['enableWhen'], "Unrepairable value is reported, not modified");
        $this->assertSame([], $repaired);
        $this->assertSame(['enableWhen'], $unrepairable);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unrepairableFieldValueProvider(): array
    {
        return [
            'non-json string' => ['always'],
            'json scalar string' => ['42'],
            'integer' => [7],
            'boolean' => [true],
        ];
    }

    /**
     * An empty string means the item has no value for the field, so the field is
     * removed and counted as repaired rather than rejected. Every field in
     * ITEM_ARRAY_FIELDS is 0..* in FHIR, so absent is valid; refusing it would cost the
     * whole questionnaire for a value that carries nothing.
     *
     * @param non-empty-string $field
     */
    #[DataProvider('doubleEncodedFieldProvider')]
    public function testEmptyStringFieldIsDroppedNotRejected(string $field): void
    {
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem(
            ['linkId' => '1', 'type' => 'boolean', $field => '']
        );

        $this->assertArrayNotHasKey($field, $normalized, "an empty {$field} must be removed");
        $this->assertSame([$field], $repaired);
        $this->assertSame([], $unrepairable);
        $this->assertSame('1', $normalized['linkId'], 'the rest of the item is untouched');
    }

    /**
     * @param non-empty-string $whitespace
     */
    #[DataProvider('blankStringProvider')]
    public function testWhitespaceOnlyFieldIsAlsoDropped(string $whitespace): void
    {
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem(
            ['linkId' => '1', 'enableWhen' => $whitespace]
        );

        $this->assertArrayNotHasKey('enableWhen', $normalized);
        $this->assertSame(['enableWhen'], $repaired);
        $this->assertSame([], $unrepairable);
    }

    /**
     * @return array<string, array{non-empty-string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function blankStringProvider(): array
    {
        return [
            'single space' => [' '],
            'tab' => ["\t"],
            'newline' => ["\n"],
            'mixed whitespace' => [" \t\n "],
        ];
    }

    /**
     * The distinction the drop relies on: a string that failed to decode but held
     * something lost real data, and must keep being refused. Dropping it would hide the
     * loss instead of reporting it.
     */
    public function testAStringThatHeldDataIsStillUnrepairable(): void
    {
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeItem(
            ['linkId' => '1', 'enableWhen' => '[object Object]']
        );

        $this->assertSame('[object Object]', $normalized['enableWhen']);
        $this->assertSame([], $repaired);
        $this->assertSame(['enableWhen'], $unrepairable);
    }

    /**
     * The real shape from the field: a questionnaire whose items mix well-formed
     * enableWhen arrays with an empty string on one item, at both top level and nested.
     * Strict mode must import it rather than refuse the document.
     */
    public function testStrictModeImportsAQuestionnaireWithAnEmptyEnableWhen(): void
    {
        $good = [['question' => '/BS/MDS03', 'operator' => '=', 'answerBoolean' => true]];
        $questionnaire = [
            'resourceType' => 'Questionnaire',
            'item' => [
                ['linkId' => '/BI', 'type' => 'group', 'enableWhen' => ''],
                [
                    'linkId' => '/SO',
                    'type' => 'group',
                    'enableWhen' => $good,
                    'item' => [
                        ['linkId' => '/SO/MDS15Follow', 'type' => 'choice', 'enableWhen' => ''],
                        ['linkId' => '/SO/MDS16', 'type' => 'choice', 'enableWhen' => $good],
                    ],
                ],
            ],
        ];

        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeQuestionnaire(
            $questionnaire,
            true
        );

        $this->assertSame([], $unrepairable);
        $this->assertSame(
            ['item[/BI].enableWhen', 'item[/SO].item[/SO/MDS15Follow].enableWhen'],
            $repaired,
            'both empty conditions are reported by path, at top level and nested'
        );

        // Narrowed a step at a time: normalizeQuestionnaire() returns array<mixed>, so
        // each offset is mixed until asserted. Same shape as the nested-repair test above.
        $items = $normalized['item'] ?? null;
        $this->assertIsArray($items);

        $topLevel = $items[0] ?? null;
        $this->assertIsArray($topLevel);
        $this->assertArrayNotHasKey('enableWhen', $topLevel, 'top-level empty condition removed');
        $this->assertSame('/BI', $topLevel['linkId'] ?? null, 'the item itself survives');

        $group = $items[1] ?? null;
        $this->assertIsArray($group);
        $this->assertSame($good, $group['enableWhen'] ?? null, 'a real condition survives');

        $children = $group['item'] ?? null;
        $this->assertIsArray($children);

        $emptyChild = $children[0] ?? null;
        $this->assertIsArray($emptyChild);
        $this->assertArrayNotHasKey('enableWhen', $emptyChild, 'nested empty condition removed');
        $this->assertSame('/SO/MDS15Follow', $emptyChild['linkId'] ?? null);
        $this->assertSame('choice', $emptyChild['type'] ?? null);

        $goodChild = $children[1] ?? null;
        $this->assertIsArray($goodChild);
        $this->assertSame($good, $goodChild['enableWhen'] ?? null);
    }

    public function testNormalizeQuestionnaireRepairsNestedItems(): void
    {
        $enableWhen = [['question' => '1', 'operator' => '=', 'answerBoolean' => false]];
        $answerOption = [['valueString' => 'Yes'], ['valueString' => 'No']];
        $questionnaire = [
            'resourceType' => 'Questionnaire',
            'item' => [
                ['linkId' => '1', 'text' => 'ok'],
                [
                    'linkId' => '2',
                    'enableWhen' => json_encode($enableWhen),
                    'item' => [
                        ['linkId' => '2.1', 'answerOption' => json_encode($answerOption)],
                    ],
                ],
            ],
        ];
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire);
        $items = $normalized['item'] ?? null;
        $this->assertIsArray($items);
        $item2 = $items[1] ?? null;
        $this->assertIsArray($item2);
        $this->assertSame($enableWhen, $item2['enableWhen'] ?? null);
        $childItems = $item2['item'] ?? null;
        $this->assertIsArray($childItems);
        $child = $childItems[0] ?? null;
        $this->assertIsArray($child);
        $this->assertSame($answerOption, $child['answerOption'] ?? null);
        $this->assertSame(['item[2].enableWhen', 'item[2].item[2.1].answerOption'], $repaired);
        $this->assertSame([], $unrepairable);
        $this->assertSame($questionnaire['item'][0], $items[0] ?? null, "Well-formed items untouched");
    }

    public function testNonStrictModeReportsUnrepairablePathsWithoutModifying(): void
    {
        $questionnaire = [
            'item' => [
                ['linkId' => 'q1', 'code' => 'not-an-array'],
            ],
        ];
        [$normalized, , $unrepairable] = QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire, false);
        $items = $normalized['item'] ?? null;
        $this->assertIsArray($items);
        $item = $items[0] ?? null;
        $this->assertIsArray($item);
        $this->assertSame('not-an-array', $item['code'] ?? null);
        $this->assertSame(['item[q1].code'], $unrepairable);
    }

    public function testStrictModeThrowsWithItemPathAndField(): void
    {
        $questionnaire = [
            'item' => [
                ['linkId' => '1', 'text' => 'fine'],
                ['linkId' => '2', 'item' => [['linkId' => '2.1', 'enableWhen' => 'always']]],
            ],
        ];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('item[2].item[2.1].enableWhen must be an array of objects, string given');
        QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire, true);
    }

    public function testStrictModeThrowsOnNonArrayItem(): void
    {
        $questionnaire = ['item' => ['just a string']];
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('item[0] must be an object, string given');
        QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire, true);
    }

    public function testStrictModeRepairsDoubleEncodingWithoutThrowing(): void
    {
        $enableWhen = [['question' => '0', 'operator' => '=', 'answerBoolean' => true]];
        $questionnaire = ['item' => [['linkId' => '1', 'enableWhen' => json_encode($enableWhen)]]];
        [$normalized, $repaired] = QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire, true);
        $items = $normalized['item'] ?? null;
        $this->assertIsArray($items);
        $item = $items[0] ?? null;
        $this->assertIsArray($item);
        $this->assertSame($enableWhen, $item['enableWhen'] ?? null);
        $this->assertSame(['item[1].enableWhen'], $repaired);
    }

    public function testQuestionnaireWithoutItemsPassesThrough(): void
    {
        $questionnaire = ['resourceType' => 'Questionnaire', 'title' => 'Empty'];
        [$normalized, $repaired, $unrepairable] = QuestionnaireItemNormalizer::normalizeQuestionnaire($questionnaire, true);
        $this->assertSame($questionnaire, $normalized);
        $this->assertSame([], $repaired);
        $this->assertSame([], $unrepairable);
    }
}
