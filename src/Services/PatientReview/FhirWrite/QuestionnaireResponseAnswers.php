<?php

/**
 * Flattens a held QuestionnaireResponse into question and answer rows for staff to read.
 *
 * Nested items (groups, and questions under an answer) are walked in order. The question is
 * the item's text, or its linkId when the app sent no text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

final class QuestionnaireResponseAnswers
{
    /** How deep nested items are followed; a resource is at most 256 KB, but not unbounded. */
    private const MAX_DEPTH = 20;

    /**
     * @param array<array-key, mixed> $resource
     * @return list<array{question: string, answers: list<string>}>
     */
    public static function of(array $resource): array
    {
        $rows = [];
        self::walk($resource['item'] ?? null, $rows, 0);

        return $rows;
    }

    /**
     * @param list<array{question: string, answers: list<string>}> $rows
     */
    private static function walk(mixed $items, array &$rows, int $depth): void
    {
        if (!is_array($items) || $depth > self::MAX_DEPTH) {
            return;
        }
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $answers = [];
            $nested = [];
            foreach (is_array($item['answer'] ?? null) ? $item['answer'] : [] as $answer) {
                if (!is_array($answer)) {
                    continue;
                }
                $value = self::answerValue($answer);
                if ($value !== null) {
                    $answers[] = $value;
                }
                $nested[] = $answer['item'] ?? null;
            }
            if ($answers !== []) {
                $text = $item['text'] ?? null;
                $linkId = $item['linkId'] ?? null;
                $rows[] = [
                    'question' => is_string($text) && $text !== '' ? $text : (is_string($linkId) ? $linkId : ''),
                    'answers' => $answers,
                ];
            }
            foreach ($nested as $answerItems) {
                self::walk($answerItems, $rows, $depth + 1);
            }
            self::walk($item['item'] ?? null, $rows, $depth + 1);
        }
    }

    /**
     * @param array<array-key, mixed> $answer
     */
    private static function answerValue(array $answer): ?string
    {
        foreach (['valueString', 'valueDate', 'valueDateTime', 'valueTime', 'valueUri'] as $key) {
            if (is_string($answer[$key] ?? null)) {
                return $answer[$key];
            }
        }
        foreach (['valueInteger', 'valueDecimal'] as $key) {
            $number = $answer[$key] ?? null;
            if (is_int($number) || is_float($number)) {
                return (string) $number;
            }
        }
        if (is_bool($answer['valueBoolean'] ?? null)) {
            return $answer['valueBoolean'] ? 'Yes' : 'No';
        }
        $coding = $answer['valueCoding'] ?? null;
        if (is_array($coding)) {
            $label = $coding['display'] ?? ($coding['code'] ?? null);
            return is_string($label) ? $label : null;
        }
        $quantity = $answer['valueQuantity'] ?? null;
        if (is_array($quantity) && (is_int($quantity['value'] ?? null) || is_float($quantity['value'] ?? null))) {
            $unit = $quantity['unit'] ?? ($quantity['code'] ?? null);
            return (string) $quantity['value'] . (is_string($unit) ? ' ' . $unit : '');
        }

        return null;
    }
}
