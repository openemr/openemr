<?php

/**
 * Isolated tests for flattening a held QuestionnaireResponse into rows staff can read.
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
    public function testEveryAnswerTypeIsShownInOrderThroughNestedItems(): void
    {
        $rows = QuestionnaireResponseAnswers::of([
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
                ['linkId' => 'blank', 'text' => 'Unanswered'],
                'not an item',
            ],
        ]);

        $this->assertSame([
            ['question' => 'Name', 'answers' => ['Ann']],
            ['question' => 'age', 'answers' => ['42']],
            ['question' => 'Do you smoke?', 'answers' => ['Yes']],
            ['question' => 'Packs a day', 'answers' => ['1.5']],
            ['question' => 'Mood', 'answers' => ['Several days', 'LA2']],
            ['question' => 'Weight', 'answers' => ['70 kg']],
            ['question' => 'Since', 'answers' => ['2026-01-02']],
        ], $rows);
    }

    public function testAResponseWithNoItemsHasNoRows(): void
    {
        $this->assertSame([], QuestionnaireResponseAnswers::of(['resourceType' => 'QuestionnaireResponse']));
        $this->assertSame([], QuestionnaireResponseAnswers::of(['item' => 'not a list']));
    }

    public function testNestingStopsAtTheDepthLimit(): void
    {
        $item = ['linkId' => 'deepest', 'text' => 'Deepest', 'answer' => [['valueString' => 'x']]];
        for ($i = 0; $i < 30; $i++) {
            $item = ['linkId' => 'g' . $i, 'item' => [$item]];
        }

        $this->assertSame([], QuestionnaireResponseAnswers::of(['item' => [$item]]));
    }
}
