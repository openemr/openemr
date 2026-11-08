<?php

/**
 * Flattens a held QuestionnaireResponse into question and answer rows for staff to read.
 *
 * Nested items (groups, and questions under an answer) are walked in order. The question is
 * the item's text, or its linkId when the app sent no text.
 *
 * Nothing is dropped silently. An answer whose type isn't shown here is counted against its
 * question, and answers nested past the depth limit are counted on their own, so the screen can
 * tell staff to open what the app sent before deciding.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\Questionnaire\QuestionnaireAnswerText;

/**
 * @phpstan-type AnswerRow array{question: string, answers: list<string>, unshown: int}
 */
final class QuestionnaireResponseAnswers
{
    /** How deep nested items are followed; a resource is at most 256 KB, but not unbounded. */
    private const MAX_DEPTH = 20;

    /** The answer types shown as text; the rest are counted as not shown. */
    private const SHOWN_TYPES = [
        'valueString', 'valueDate', 'valueDateTime', 'valueTime', 'valueUri', 'valueBoolean',
        'valueInteger', 'valueDecimal', 'valueQuantity', 'valueCoding', 'valueReference',
    ];

    /**
     * @param array<array-key, mixed> $resource
     * @return array{rows: list<AnswerRow>, tooDeep: int} tooDeep counts the answers nested past
     *                                                    the depth limit, which no row shows
     */
    public static function of(array $resource): array
    {
        $rows = [];
        $tooDeep = 0;
        self::walk($resource['item'] ?? null, $rows, $tooDeep, 0);

        return ['rows' => $rows, 'tooDeep' => $tooDeep];
    }

    /**
     * Appends a row for each answered item, depth first, in the order the app sent them.
     *
     * @param list<AnswerRow> $rows
     */
    private static function walk(mixed $items, array &$rows, int &$tooDeep, int $depth): void
    {
        if (!is_array($items)) {
            return;
        }
        if ($depth > self::MAX_DEPTH) {
            $tooDeep += self::countAnswers($items, 0);
            return;
        }
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $answers = [];
            $unshown = 0;
            $nested = [];
            foreach (is_array($item['answer'] ?? null) ? $item['answer'] : [] as $answer) {
                if (!is_array($answer)) {
                    continue;
                }
                $value = self::answerValue($answer);
                if ($value === null) {
                    $unshown++;
                } else {
                    $answers[] = $value;
                }
                $nested[] = $answer['item'] ?? null;
            }
            if ($answers !== [] || $unshown > 0) {
                $text = $item['text'] ?? null;
                $linkId = $item['linkId'] ?? null;
                $rows[] = [
                    'question' => is_string($text) && $text !== '' ? $text : (is_string($linkId) ? $linkId : ''),
                    'answers' => $answers,
                    'unshown' => $unshown,
                ];
            }
            foreach ($nested as $answerItems) {
                self::walk($answerItems, $rows, $tooDeep, $depth + 1);
            }
            self::walk($item['item'] ?? null, $rows, $tooDeep, $depth + 1);
        }
    }

    /**
     * How many answers a list of items holds, nested ones included. The count stops following
     * nesting at the same bound the walk uses, measured from where the walk stopped.
     */
    private static function countAnswers(mixed $items, int $depth): int
    {
        if (!is_array($items) || $depth > self::MAX_DEPTH) {
            return 0;
        }
        $count = 0;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (is_array($item['answer'] ?? null) ? $item['answer'] : [] as $answer) {
                if (is_array($answer)) {
                    $count++;
                    $count += self::countAnswers($answer['item'] ?? null, $depth + 1);
                }
            }
            $count += self::countAnswers($item['item'] ?? null, $depth + 1);
        }

        return $count;
    }

    /**
     * The answer as text, as the chart shows it (QuestionnaireAnswerText), or
     * null for a type this screen doesn't show. An attachment's content, and any type not listed
     * here, is only in what the app sent, so it is counted rather than labelled.
     *
     * @param array<array-key, mixed> $answer
     */
    private static function answerValue(array $answer): ?string
    {
        foreach (self::SHOWN_TYPES as $key) {
            if (array_key_exists($key, $answer)) {
                return QuestionnaireAnswerText::of($answer);
            }
        }

        return null;
    }
}
