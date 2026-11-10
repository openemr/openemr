<?php

/**
 * Converts an LHC form definition to a FHIR R4 Questionnaire.
 *
 * NLM publishes LOINC panels only in the LHC format. LForms used to do this conversion in the
 * browser; with LForms gone it happens here, and targets what the native runtime renders.
 *
 * Anything that cannot be carried over is reported rather than dropped silently, so an
 * administrator sees it while reviewing the result.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Questionnaire;

use InvalidArgumentException;

final class LhcFormToFhirConverter
{
    private const LOINC_SYSTEM = 'http://loinc.org';
    private const ITEM_CONTROL_SYSTEM = 'http://hl7.org/fhir/questionnaire-item-control';
    private const EXT_ITEM_CONTROL = 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl';
    private const EXT_ORDINAL = 'http://hl7.org/fhir/StructureDefinition/ordinalValue';
    private const EXT_UNIT = 'http://hl7.org/fhir/StructureDefinition/questionnaire-unit';
    private const EXT_UNIT_OPTION = 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption';
    private const EXT_CALCULATED = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';

    /** enableWhen operators for the numeric LHC skip-logic triggers */
    private const RANGE_OPERATORS = [
        'minInclusive' => '>=',
        'minExclusive' => '>',
        'maxInclusive' => '<=',
        'maxExclusive' => '<',
        'notEqual' => '!=',
    ];

    /** @var array<string, array{linkId: string, type: string}> source key => target */
    private array $sources = [];

    /** @var array<string, true> */
    private array $usedLinkIds = [];

    /** @var list<array{issue: LhcFormConversionIssue, question: string}> */
    private array $issues = [];

    /**
     * @param array<mixed> $form the decoded LHC definition
     * @throws InvalidArgumentException if the definition has no items
     */
    public function convert(array $form): LhcFormConversion
    {
        $items = self::listOfArrays($form['items'] ?? null);
        if ($items === []) {
            throw new InvalidArgumentException('LHC form definition has no items');
        }

        $this->sources = [];
        $this->usedLinkIds = [];
        $this->issues = [];

        $assigned = $this->assignLinkIds($items, '');
        $questionnaire = array_filter([
            'resourceType' => 'Questionnaire',
            'status' => 'active',
            'title' => self::text($form['name'] ?? null),
            'description' => self::plain($form['codingInstructions'] ?? null),
            'copyright' => self::repairMojibake(self::text($form['copyrightNotice'] ?? null)),
            'code' => self::formCode($form),
            'item' => $this->convertSiblings($assigned),
        ], static fn(mixed $value): bool => $value !== '' && $value !== []);

        return new LhcFormConversion($questionnaire, $this->issues);
    }

    /**
     * First pass: every item gets its FHIR linkId and type, so skip logic can point at an
     * item that comes later in the form.
     *
     * @param list<array<mixed>> $items
     * @return list<array{item: array<mixed>, linkId: string, type: string, children: list<mixed>}>
     */
    private function assignLinkIds(array $items, string $parentPath): array
    {
        $assigned = [];
        foreach ($items as $item) {
            $code = self::text($item['questionCode'] ?? null);
            $linkId = $this->uniqueLinkId(self::text($item['linkId'] ?? null), $parentPath . '/' . $code);
            $children = $this->assignLinkIds(self::listOfArrays($item['items'] ?? null), $linkId);
            $type = $this->fhirType($item, $children !== []);

            $this->sources[$linkId] = ['linkId' => $linkId, 'type' => $type];
            if ($code !== '') {
                $this->sources[$code] ??= ['linkId' => $linkId, 'type' => $type];
            }
            $assigned[] = ['item' => $item, 'linkId' => $linkId, 'type' => $type, 'children' => $children];
        }

        return $assigned;
    }

    /**
     * A linkId not used yet in this form: the declared one, or the fallback when none is
     * declared, with _2, _3 ... added when it repeats.
     */
    private function uniqueLinkId(string $declared, string $fallback): string
    {
        $base = $declared !== '' ? $declared : $fallback;
        $candidate = $base;
        for ($suffix = 2; isset($this->usedLinkIds[$candidate]); $suffix++) {
            $candidate = $base . '_' . $suffix;
        }
        $this->usedLinkIds[$candidate] = true;

        return $candidate;
    }

    /**
     * @param array<mixed> $item
     */
    private function fhirType(array $item, bool $hasChildren): string
    {
        $dataType = self::text($item['dataType'] ?? null);
        if (($item['header'] ?? false) === true || $dataType === 'SECTION') {
            return $hasChildren ? 'group' : 'display';
        }

        $hasMultipleUnits = count(self::listOfArrays($item['units'] ?? null)) > 1;
        $hasOther = array_filter(
            self::listOfArrays($item['answers'] ?? null),
            self::isOtherAnswer(...)
        ) !== [];

        switch ($dataType) {
            case 'CNE':
                return $hasOther ? 'open-choice' : 'choice';
            case 'CWE':
                return 'open-choice';
            case 'ST':
            case 'EMAIL':
            case 'PHONE':
            case '':
                return 'string';
            case 'TX':
                return 'text';
            case 'INT':
                return $hasMultipleUnits ? 'quantity' : 'integer';
            case 'REAL':
            case 'NR':
                return $hasMultipleUnits ? 'quantity' : 'decimal';
            case 'QTY':
                return 'quantity';
            case 'DT':
                return 'date';
            case 'DTM':
                return 'dateTime';
            case 'TM':
                return 'time';
            case 'BL':
                return 'boolean';
            case 'URL':
                return 'url';
            case 'TITLE':
                return 'display';
            default:
                $this->report(LhcFormConversionIssue::UnknownDataType, $item);

                return 'string';
        }
    }

    /**
     * @param list<mixed> $assigned
     * @return list<array<string, mixed>>
     */
    private function convertSiblings(array $assigned): array
    {
        $converted = [];
        foreach ($assigned as $entry) {
            /** @var array{item: array<mixed>, linkId: string, type: string, children: list<mixed>} $entry */
            $converted[] = $this->convertItem($entry, $assigned);
        }

        return $converted;
    }

    /**
     * @param array{item: array<mixed>, linkId: string, type: string, children: list<mixed>} $entry
     * @param list<mixed> $siblings
     * @return array<string, mixed>
     */
    private function convertItem(array $entry, array $siblings): array
    {
        $item = $entry['item'];
        $type = $entry['type'];
        $converted = [
            'linkId' => $entry['linkId'],
            'text' => self::text($item['question'] ?? null),
            'type' => $type,
        ];

        $code = self::itemCode($item);
        if ($code !== []) {
            $converted['code'] = $code;
        }

        $extensions = array_merge($this->unitExtensions($item), $this->calculationExtensions($item, $entry, $siblings));
        if ($extensions !== []) {
            $converted['extension'] = $extensions;
        }
        if (self::hasExtension($extensions, self::EXT_CALCULATED)) {
            $converted['readOnly'] = true;
        }

        $enableWhen = $this->enableWhen($item);
        if ($enableWhen !== []) {
            $converted['enableWhen'] = $enableWhen;
            if (count($enableWhen) > 1) {
                $converted['enableBehavior'] = strtoupper(self::text(self::arrayOf($item['skipLogic'] ?? null)['logic'] ?? null)) === 'ALL'
                    ? 'all'
                    : 'any';
            }
        }

        if (self::cardinalityRepeats($item['questionCardinality'] ?? null) || self::cardinalityRepeats($item['answerCardinality'] ?? null)) {
            $converted['repeats'] = true;
        }
        if (self::isRequired($item['answerCardinality'] ?? null)) {
            $converted['required'] = true;
        }

        $this->applyRestrictions($item, $converted);

        if (in_array($type, ['choice', 'open-choice'], true)) {
            $options = self::answerOptions($item);
            if ($options !== []) {
                $converted['answerOption'] = $options;
            }
        }

        $initial = self::initialValue($item, $type);
        if ($initial !== null) {
            $converted['initial'] = [$initial];
        }

        $children = $type === 'display' ? [] : $this->convertSiblings($entry['children']);
        $help = self::plain($item['codingInstructions'] ?? null);
        if ($help !== '' && $type !== 'display') {
            $children[] = self::helpItem($entry['linkId'] . '_help', $help);
        }
        if ($children !== []) {
            $converted['item'] = $children;
        }

        return $converted;
    }

    /**
     * @return array<string, mixed>
     */
    private static function helpItem(string $linkId, string $text): array
    {
        return [
            'linkId' => $linkId,
            'text' => $text,
            'type' => 'display',
            'extension' => [[
                'url' => self::EXT_ITEM_CONTROL,
                'valueCodeableConcept' => [
                    'coding' => [['system' => self::ITEM_CONTROL_SYSTEM, 'code' => 'help', 'display' => 'Help-Button']],
                ],
            ]],
        ];
    }

    /**
     * @param array<mixed> $form
     * @return list<array<string, string>>
     */
    private static function formCode(array $form): array
    {
        $code = self::text($form['code'] ?? null);
        if ($code === '') {
            return [];
        }

        return [array_filter([
            'system' => self::text($form['codeSystem'] ?? null) ?: self::LOINC_SYSTEM,
            'code' => $code,
            'display' => self::text($form['name'] ?? null),
        ], static fn(string $value): bool => $value !== '')];
    }

    /**
     * @param array<mixed> $item
     * @return list<array<string, string>>
     */
    private static function itemCode(array $item): array
    {
        $code = self::text($item['questionCode'] ?? null);
        if ($code === '') {
            return [];
        }
        $system = self::text($item['questionCodeSystem'] ?? null);
        $coding = ['code' => $code];
        if ($system !== '') {
            $coding = ['system' => $system === 'LOINC' ? self::LOINC_SYSTEM : $system, 'code' => $code];
        }
        $display = self::text($item['question'] ?? null);
        if ($display !== '') {
            $coding['display'] = $display;
        }

        return [$coding];
    }

    /**
     * @param array<mixed> $item
     * @return list<array<string, mixed>>
     */
    private static function answerOptions(array $item): array
    {
        $options = [];
        foreach (self::listOfArrays($item['answers'] ?? null) as $answer) {
            if (self::isOtherAnswer($answer)) {
                continue;
            }
            $coding = self::answerCoding($answer);
            if (is_numeric($answer['score'] ?? null)) {
                $coding['extension'] = [['url' => self::EXT_ORDINAL, 'valueDecimal' => $answer['score'] + 0]];
            }
            if ($coding !== []) {
                $options[] = ['valueCoding' => $coding];
            }
        }

        return $options;
    }

    /**
     * @param array<mixed> $answer
     */
    private static function isOtherAnswer(array $answer): bool
    {
        return !in_array($answer['other'] ?? null, [null, '', false], true);
    }

    /**
     * @param array<mixed> $answer
     * @return array<string, mixed>
     */
    private static function answerCoding(array $answer): array
    {
        return array_filter([
            'system' => self::text($answer['system'] ?? null),
            'code' => self::text($answer['code'] ?? null),
            'display' => self::text($answer['text'] ?? null),
        ], static fn(string $value): bool => $value !== '');
    }

    /**
     * @param array<mixed> $item
     * @return list<array<string, mixed>>
     */
    private function unitExtensions(array $item): array
    {
        $units = self::listOfArrays($item['units'] ?? null);
        $codings = array_map(static fn(array $unit): array => array_filter([
            'system' => self::text($unit['system'] ?? null),
            'code' => self::text($unit['code'] ?? null),
            'display' => self::text($unit['name'] ?? null),
        ], static fn(string $value): bool => $value !== ''), $units);

        if (count($codings) === 1) {
            return [['url' => self::EXT_UNIT, 'valueCoding' => $codings[0]]];
        }

        return array_map(
            static fn(array $coding): array => ['url' => self::EXT_UNIT_OPTION, 'valueCoding' => $coding],
            $codings
        );
    }

    /**
     * @param array<mixed> $item
     * @param array{item: array<mixed>, linkId: string, type: string, children: list<mixed>} $entry
     * @param list<mixed> $siblings
     * @return list<array<string, mixed>>
     */
    private function calculationExtensions(array $item, array $entry, array $siblings): array
    {
        $method = self::text(self::arrayOf($item['calculationMethod'] ?? null)['name'] ?? null);
        if ($method === '') {
            return [];
        }

        $scored = $method === 'TOTALSCORE' ? self::scoredLinkIds($siblings, $entry['linkId']) : [];
        if ($scored === []) {
            $this->report(LhcFormConversionIssue::ScoreDropped, $item);

            return [];
        }

        $matches = implode(' or ', array_map(
            static fn(string $linkId): string => "linkId = '" . str_replace("'", "\\'", $linkId) . "'",
            $scored
        ));

        return [[
            'url' => self::EXT_CALCULATED,
            'valueExpression' => [
                'description' => 'Total score',
                'language' => 'text/fhirpath',
                'expression' => '%resource.repeat(item).where(' . $matches . ').answer.value.weight().sum()',
            ],
        ]];
    }

    /**
     * A total sums the scored answers beside it, including those nested under a sibling.
     *
     * @param list<mixed> $entries
     * @return list<string>
     */
    private static function scoredLinkIds(array $entries, string $excludeLinkId): array
    {
        $found = [];
        foreach ($entries as $entry) {
            /** @var array{item: array<mixed>, linkId: string, type: string, children: list<mixed>} $entry */
            if ($entry['linkId'] === $excludeLinkId) {
                continue;
            }
            foreach (self::listOfArrays($entry['item']['answers'] ?? null) as $answer) {
                if (is_numeric($answer['score'] ?? null)) {
                    $found[] = $entry['linkId'];
                    break;
                }
            }
            array_push($found, ...self::scoredLinkIds($entry['children'], $excludeLinkId));
        }

        return $found;
    }

    /**
     * @param array<mixed> $item
     * @return list<array<string, mixed>>
     */
    private function enableWhen(array $item): array
    {
        $skipLogic = self::arrayOf($item['skipLogic'] ?? null);
        if ($skipLogic === []) {
            return [];
        }
        if (strtolower(self::text($skipLogic['action'] ?? null) ?: 'show') !== 'show') {
            $this->report(LhcFormConversionIssue::SkipLogicDropped, $item);

            return [];
        }

        $conditions = [];
        foreach (self::listOfArrays($skipLogic['conditions'] ?? null) as $condition) {
            $source = $this->sources[self::text($condition['source'] ?? null)] ?? null;
            $converted = $source === null ? null : self::condition($condition, $source);
            if ($converted === null) {
                $this->report(LhcFormConversionIssue::SkipLogicDropped, $item);

                return [];
            }
            $conditions[] = $converted;
        }

        return $conditions;
    }

    /**
     * @param array<mixed> $condition
     * @param array{linkId: string, type: string} $source
     * @return ?array<string, mixed>
     */
    private static function condition(array $condition, array $source): ?array
    {
        $trigger = self::arrayOf($condition['trigger'] ?? null);
        $base = ['question' => $source['linkId']];

        if (array_key_exists('exists', $trigger) && is_bool($trigger['exists'])) {
            return $base + ['operator' => 'exists', 'answerBoolean' => $trigger['exists']];
        }
        // One trigger can hold both ends of a range, which a single enableWhen cannot. Report
        // it rather than keep one bound and show the item where the source form hides it.
        $bounds = array_intersect_key($trigger, self::RANGE_OPERATORS);
        if (count($bounds) > 1) {
            return null;
        }
        foreach (self::RANGE_OPERATORS as $key => $operator) {
            if (array_key_exists($key, $trigger)) {
                $answer = self::conditionAnswer($trigger[$key], $source['type']);

                return $answer === null ? null : $base + ['operator' => $operator] + $answer;
            }
        }

        // Older definitions put the answer's code straight on the trigger.
        $value = array_key_exists('value', $trigger) ? $trigger['value'] : ($trigger !== [] ? $trigger : null);
        $answer = self::conditionAnswer($value, $source['type']);

        return $answer === null ? null : $base + ['operator' => '='] + $answer;
    }

    /**
     * @return ?array<string, mixed>
     */
    private static function conditionAnswer(mixed $value, string $sourceType): ?array
    {
        if (is_array($value)) {
            $coding = self::answerCoding($value);

            return $coding === [] ? null : ['answerCoding' => $coding];
        }
        if (is_bool($value)) {
            return ['answerBoolean' => $value];
        }
        if (is_int($value) || is_float($value)) {
            return $sourceType === 'integer' ? ['answerInteger' => (int)$value] : ['answerDecimal' => $value];
        }
        if (!is_string($value) || $value === '') {
            return null;
        }

        return match ($sourceType) {
            'integer' => is_numeric($value) ? ['answerInteger' => (int)$value] : null,
            'decimal', 'quantity' => is_numeric($value) ? ['answerDecimal' => $value + 0] : null,
            'date' => ['answerDate' => $value],
            'dateTime' => ['answerDateTime' => $value],
            'time' => ['answerTime' => $value],
            'choice', 'open-choice' => ['answerCoding' => ['code' => $value]],
            default => ['answerString' => $value],
        };
    }

    /**
     * @param array<mixed> $item
     * @param array<string, mixed> $converted
     */
    private function applyRestrictions(array $item, array &$converted): void
    {
        foreach (self::arrayOf($item['restrictions'] ?? null) as $name => $value) {
            if ($name === 'maxLength' && is_numeric($value)) {
                $converted['maxLength'] = (int)$value;
                continue;
            }
            $this->report(LhcFormConversionIssue::RestrictionDropped, $item);
        }
    }

    /**
     * @param array<mixed> $item
     * @return ?array<string, mixed>
     */
    private static function initialValue(array $item, string $type): ?array
    {
        $default = $item['defaultAnswer'] ?? null;
        if ($default === null || $default === '') {
            return null;
        }

        if (in_array($type, ['choice', 'open-choice'], true)) {
            foreach (self::listOfArrays($item['answers'] ?? null) as $answer) {
                $wanted = is_array($default) ? [$default['code'] ?? null, $default['text'] ?? null] : [$default];
                if (in_array($answer['code'] ?? null, $wanted, true) || in_array($answer['text'] ?? null, $wanted, true)) {
                    return ['valueCoding' => self::answerCoding($answer)];
                }
            }

            return null;
        }

        return match ($type) {
            'string', 'text', 'url' => is_string($default) ? ['value' . ucfirst($type === 'text' ? 'string' : ($type === 'url' ? 'uri' : $type)) => $default] : null,
            'integer' => is_numeric($default) ? ['valueInteger' => (int)$default] : null,
            'decimal' => is_numeric($default) ? ['valueDecimal' => $default + 0] : null,
            'boolean' => is_bool($default) ? ['valueBoolean' => $default] : null,
            'date' => is_string($default) ? ['valueDate' => $default] : null,
            default => null,
        };
    }

    /**
     * Whether an LHC cardinality allows more than one answer.
     */
    private static function cardinalityRepeats(mixed $cardinality): bool
    {
        $max = self::arrayOf($cardinality)['max'] ?? null;

        return $max === '*' || (is_numeric($max) && (int)$max > 1);
    }

    /**
     * Whether an LHC cardinality requires at least one answer.
     */
    private static function isRequired(mixed $cardinality): bool
    {
        $min = self::arrayOf($cardinality)['min'] ?? null;

        return is_numeric($min) && (int)$min >= 1;
    }

    /**
     * @param list<array<string, mixed>> $extensions
     */
    private static function hasExtension(array $extensions, string $url): bool
    {
        return in_array($url, array_column($extensions, 'url'), true);
    }

    /**
     * @param array<mixed> $item
     */
    private function report(LhcFormConversionIssue $issue, array $item): void
    {
        $this->issues[] = ['issue' => $issue, 'question' => self::text($item['question'] ?? null)];
    }

    /**
     * @return array<mixed>
     */
    private static function arrayOf(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @return list<array<mixed>>
     */
    private static function listOfArrays(mixed $value): array
    {
        return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
    }

    /**
     * A trimmed string, or an empty one for anything that is not a string.
     */
    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    /**
     * Like text(), with any HTML tags removed.
     */
    private static function plain(mixed $value): string
    {
        return trim(strip_tags(self::text($value)));
    }

    /**
     * Some NLM definitions carry a copyright sign double-encoded.
     */
    private static function repairMojibake(string $text): string
    {
        return str_replace("\u{00C2}\u{00A9}", "\u{00A9}", $text);
    }
}
