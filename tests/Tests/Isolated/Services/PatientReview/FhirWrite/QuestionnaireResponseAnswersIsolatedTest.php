<?php

/**
 * Isolated tests for flattening a held QuestionnaireResponse into rows staff can read, and for
 * counting what the rows can't show.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\FhirWrite\QuestionnaireResponseAnswers;
use PHPUnit\Framework\TestCase;

class QuestionnaireResponseAnswersIsolatedTest extends TestCase
{
    /**
     * Each shown answer type appears as text, in order, through groups and answer-nested items.
     */
    public function testEveryShownAnswerTypeAppearsInOrderThroughNestedItems(): void
    {
        $result = QuestionnaireResponseAnswers::of([
            'resourceType' => 'QuestionnaireResponse',
            'item' => [
                ['linkId' => 'g1', 'text' => 'About you', 'item' => [
                    ['linkId' => 'name', 'text' => 'Name', 'answer' => [['valueString' => 'Ann']]],
                    ['linkId' => 'age', 'answer' => [['valueInteger' => 42]]],
                ]],
                ['linkId' => 'smoke', 'text' => 'Do you smoke?', 'answer' => [[
                    'valueBoolean' => true,
                    'item' => [['linkId' => 'packs', 'text' => 'Packs a day', 'answer' => [['valueDecimal' => 1.5]]]],
                ]]],
                ['linkId' => 'mood', 'text' => 'Mood', 'answer' => [
                    ['valueCoding' => ['code' => 'LA1', 'display' => 'Several days']],
                    ['valueCoding' => ['code' => 'LA2']],
                ]],
                ['linkId' => 'weight', 'text' => 'Weight', 'answer' => [['valueQuantity' => ['value' => 70, 'unit' => 'kg']]]],
                ['linkId' => 'when', 'text' => 'Since', 'answer' => [['valueDate' => '2026-01-02']]],
                ['linkId' => 'doctor', 'text' => 'Your doctor', 'answer' => [['valueReference' => ['reference' => 'Practitioner/9', 'display' => 'Dr Lee']]]],
                ['linkId' => 'blank', 'text' => 'Unanswered'],
                'not an item',
            ],
        ]);

        $this->assertSame([
            ['question' => 'Name', 'answers' => ['Ann'], 'unshown' => 0],
            ['question' => 'age', 'answers' => ['42'], 'unshown' => 0],
            ['question' => 'Do you smoke?', 'answers' => ['Yes'], 'unshown' => 0],
            ['question' => 'Packs a day', 'answers' => ['1.5'], 'unshown' => 0],
            ['question' => 'Mood', 'answers' => ['Several days', 'LA2'], 'unshown' => 0],
            ['question' => 'Weight', 'answers' => ['70 kg'], 'unshown' => 0],
            ['question' => 'Since', 'answers' => ['2026-01-02'], 'unshown' => 0],
            ['question' => 'Your doctor', 'answers' => ['Dr Lee'], 'unshown' => 0],
        ], $result['rows']);
        $this->assertSame(0, $result['tooDeep']);
    }

    /**
     * A question whose only answer can't be shown still gets a row, counting it.
     */
    public function testAnAnswerThatCannotBeShownIsCountedNotDropped(): void
    {
        $result = QuestionnaireResponseAnswers::of(['item' => [
            ['linkId' => 'photo', 'text' => 'Photo of the rash', 'answer' => [
                ['valueAttachment' => ['contentType' => 'image/png', 'title' => 'rash.png', 'data' => 'iVBORw0KGgo=']],
            ]],
            ['linkId' => 'notes', 'text' => 'Anything else?', 'answer' => [
                ['valueString' => 'It itches'],
                ['valueSomethingNew' => 'x'],
            ]],
        ]]);

        $this->assertSame([
            ['question' => 'Photo of the rash', 'answers' => [], 'unshown' => 1],
            ['question' => 'Anything else?', 'answers' => ['It itches'], 'unshown' => 1],
        ], $result['rows']);
    }

    /**
     * The screen shows an answer as the chart will, so staff don't approve different text.
     */
    public function testAnswersReadAsTheChartShowsThem(): void
    {
        $result = QuestionnaireResponseAnswers::of(['item' => [
            ['linkId' => 'empty', 'text' => 'Empty', 'answer' => [['valueString' => '']]],
            ['linkId' => 'coded', 'text' => 'Coded', 'answer' => [['valueCoding' => ['code' => 'LA3', 'display' => '']]]],
        ]]);

        $this->assertSame([
            ['question' => 'Empty', 'answers' => ['N/A'], 'unshown' => 0],
            ['question' => 'Coded', 'answers' => ['LA3'], 'unshown' => 0],
        ], $result['rows']);
    }

    /**
     * No items, or an item list that isn't a list, gives no rows.
     */
    public function testAResponseWithNoItemsHasNoRows(): void
    {
        $this->assertSame(['rows' => [], 'tooDeep' => 0], QuestionnaireResponseAnswers::of(['resourceType' => 'QuestionnaireResponse']));
        $this->assertSame(['rows' => [], 'tooDeep' => 0], QuestionnaireResponseAnswers::of(['item' => 'not a list']));
    }

    /**
     * Answers past the depth limit aren't shown, but they are counted so staff know to look.
     */
    public function testAnswersPastTheDepthLimitAreCounted(): void
    {
        $item = ['linkId' => 'deepest', 'text' => 'Deepest', 'answer' => [['valueString' => 'x'], ['valueString' => 'y']]];
        for ($i = 0; $i < 30; $i++) {
            $item = ['linkId' => 'g' . $i, 'item' => [$item]];
        }

        $this->assertSame(['rows' => [], 'tooDeep' => 2], QuestionnaireResponseAnswers::of(['item' => [$item]]));
    }
}
