<?php

/**
 * QuestionnaireItemNormalizer repairs and validates the array-typed fields of
 * FHIR Questionnaire items in their decoded-array form. Some source systems
 * (notably certain LForms conversions) emit fields such as enableWhen
 * double-encoded as JSON strings; the strict generated FHIR model classes
 * reject those at construction time.
 *
 * Two consumers with different postures share this logic:
 *  - the read path (FhirQuestionnaireFormService) tolerates legacy data:
 *    repair what is decodable, drop what is not;
 *  - the import path (QuestionnaireService::saveQuestionnaireResource) is
 *    strict: repair what is decodable, reject the import otherwise, so
 *    malformed data can no longer enter questionnaire_repository.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Questionnaire;

class QuestionnaireItemNormalizer
{
    /**
     * Fields on a Questionnaire item that the generated FHIR model requires to
     * be arrays of objects.
     */
    public const ITEM_ARRAY_FIELDS = ['enableWhen', 'code', 'answerOption', 'initial', 'extension', 'modifierExtension', 'item'];

    /**
     * Normalize the array-typed fields of a single item (non-recursive).
     * Double-encoded JSON strings are decoded back to arrays; fields that
     * cannot be repaired are reported, not modified — the caller decides
     * whether to drop them (read path) or reject the record (import path).
     *
     * @param array<mixed> $item
     * @return array{0: array<mixed>, 1: list<string>, 2: list<string>} [item, repaired field names, unrepairable field names]
     */
    public static function normalizeItem(array $item): array
    {
        $repaired = [];
        $unrepairable = [];
        foreach (self::ITEM_ARRAY_FIELDS as $field) {
            if (!isset($item[$field]) || is_array($item[$field])) {
                continue;
            }
            if (is_string($item[$field])) {
                $decoded = json_decode($item[$field], true);
                if (is_array($decoded)) {
                    $item[$field] = $decoded;
                    $repaired[] = $field;
                    continue;
                }
            }
            $unrepairable[] = $field;
        }
        return [$item, $repaired, $unrepairable];
    }

    /**
     * Recursively normalize a decoded FHIR Questionnaire array.
     *
     * In strict mode an unrepairable field throws InvalidArgumentException
     * naming the item path and field, leaving the questionnaire unsaved.
     * In non-strict mode unrepairable fields are left in place and reported so
     * the caller can decide how to degrade.
     *
     * @param array<mixed> $questionnaire Decoded Questionnaire (or item subtree holding 'item')
     * @return array{0: array<mixed>, 1: list<string>, 2: list<string>} [questionnaire, repaired paths, unrepairable paths]
     * @throws \InvalidArgumentException in strict mode for unrepairable fields or non-array items
     */
    public static function normalizeQuestionnaire(array $questionnaire, bool $strict = false, string $path = 'item'): array
    {
        $repairedPaths = [];
        $unrepairablePaths = [];
        if (!isset($questionnaire['item']) || !is_array($questionnaire['item']) || $questionnaire['item'] === []) {
            return [$questionnaire, $repairedPaths, $unrepairablePaths];
        }
        foreach ($questionnaire['item'] as $index => $item) {
            $linkId = is_array($item) && isset($item['linkId']) && is_scalar($item['linkId']) ? (string) $item['linkId'] : (string) $index;
            $itemPath = $path . '[' . $linkId . ']';
            if (!is_array($item)) {
                if ($strict) {
                    throw new \InvalidArgumentException(sprintf('%s must be an object, %s given', $itemPath, gettype($item)));
                }
                $unrepairablePaths[] = $itemPath;
                continue;
            }
            [$item, $repaired, $unrepairable] = self::normalizeItem($item);
            foreach ($repaired as $field) {
                $repairedPaths[] = $itemPath . '.' . $field;
            }
            foreach ($unrepairable as $field) {
                if ($strict) {
                    throw new \InvalidArgumentException(
                        sprintf('%s.%s must be an array of objects, %s given', $itemPath, $field, gettype($item[$field]))
                    );
                }
                $unrepairablePaths[] = $itemPath . '.' . $field;
            }
            [$item, $childRepaired, $childUnrepairable] = self::normalizeQuestionnaire($item, $strict, $itemPath . '.item');
            $repairedPaths = array_merge($repairedPaths, $childRepaired);
            $unrepairablePaths = array_merge($unrepairablePaths, $childUnrepairable);
            $questionnaire['item'][$index] = $item;
        }
        return [$questionnaire, $repairedPaths, $unrepairablePaths];
    }
}
