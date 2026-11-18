<?php

/**
 * Evaluate statement rules against LBF field values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

class StatementEngine
{
    /**
     * @param array<string, string> $values field_id → stored string
     * @param list<array<string, mixed>> $rules
     * @return list<array{
     *   sentence: string,
     *   source_field_id: string,
     *   source_value: string,
     *   source_field_id_2: ?string,
     *   source_value_2: ?string
     * }>
     */
    public function evaluate(string $formId, array $values, array $rules, ?string $patientSex = null): array
    {
        Identifiers::assertFieldId($formId);
        /** @var array<int, array{sentence: string, source_field_id: string, source_value: string, source_field_id_2: ?string, source_value_2: ?string}|null> $pending */
        $pending = [];
        /** @var array<string, array<int, array<string, mixed>>> $bandGroups */
        $bandGroups = [];
        foreach ($rules as $index => $rule) {
            if (isset($rule['enabled']) && Values::asInt($rule['enabled'], 1) === 0) {
                continue;
            }
            $ruleForm = Values::asString($rule['form_id'] ?? '');
            if ($ruleForm !== '' && $ruleForm !== $formId) {
                continue;
            }
            if (!$this->sexAllows($rule, $patientSex)) {
                continue;
            }
            if (Values::asString($rule['op'] ?? '') === 'band') {
                $key = Values::asString($rule['source_field_id'] ?? '')
                    . "\0" . Values::asString($rule['source_field_id_2'] ?? '')
                    . "\0" . trim(Values::asString($rule['match_token'] ?? ''));
                $bandGroups[$key][$index] = $rule;
                continue;
            }
            if ($this->ruleMatches($rule, $values)) {
                $pending[$index] = $this->actionFromRule($rule, $values);
            }
        }
        foreach ($bandGroups as $group) {
            foreach ($this->winningBandActions($group, $values) as $index => $action) {
                $pending[$index] = $action;
            }
        }
        ksort($pending);
        $actions = [];
        foreach ($pending as $action) {
            if ($action !== null) {
                $actions[] = $action;
            }
        }
        return $actions;
    }

    /**
     * A band token, when set, is the patient sex that band applies to.
     *
     * @param array<string, mixed> $rule
     */
    private function sexAllows(array $rule, ?string $patientSex): bool
    {
        if (Values::asString($rule['op'] ?? '') !== 'band') {
            return true;
        }
        $token = trim(Values::asString($rule['match_token'] ?? ''));
        if ($token === '') {
            return true;
        }
        if ($patientSex === null) {
            return false;
        }
        return strcasecmp($token, trim($patientSex)) === 0;
    }

    /**
     * @param array<int, array<string, mixed>> $group
     * @param array<string, string> $values
     * @return array<int, array{sentence: string, source_field_id: string, source_value: string, source_field_id_2: ?string, source_value_2: ?string}|null>
     */
    private function winningBandActions(array $group, array $values): array
    {
        $first = null;
        foreach ($group as $rule) {
            $first = $rule;
            break;
        }
        if ($first === null) {
            return [];
        }
        [$measuredField, $raw] = $this->measuredBand($first, $values);
        $span = Values::measurementSpan($raw);
        if ($span === null) {
            return [];
        }
        [$low, $high] = $span;
        /** @var array<int, array<string, mixed>> $chosen */
        $chosen = [];
        if ($low === $high) {
            foreach ($group as $index => $rule) {
                if ($this->pointInBand($rule, $low)) {
                    $chosen[$index] = $rule;
                }
            }
        } else {
            $contained = [];
            foreach ($group as $index => $rule) {
                if ($this->pointInBand($rule, $low) && $this->pointInBand($rule, $high)) {
                    $contained[$index] = $rule;
                }
            }
            if ($contained !== []) {
                $chosen = $contained;
            } else {
                $chosen = $this->widestOverlap($group, $low, $high);
            }
        }
        $out = [];
        foreach ($chosen as $index => $rule) {
            $out[$index] = $this->actionFromMeasured($rule, $values, $measuredField, $raw);
        }
        return $out;
    }

    /**
     * The primary box wins. A blank primary reads the second field.
     *
     * @param array<string, mixed> $rule
     * @param array<string, string> $values
     * @return array{0: string, 1: string}
     */
    private function measuredBand(array $rule, array $values): array
    {
        $sourceId = Values::asString($rule['source_field_id'] ?? '');
        $raw = $values[$sourceId] ?? '';
        if (trim($raw) === '') {
            $source2 = Values::asString($rule['source_field_id_2'] ?? '');
            if ($source2 !== '') {
                $fallback = $values[$source2] ?? '';
                if (trim($fallback) !== '') {
                    return [$source2, $fallback];
                }
            }
        }
        return [$sourceId, $raw];
    }

    /**
     * A range that crosses a boundary uses the band that covers more of it.
     * A point on the boundary does not beat a band that covers a stretch.
     *
     * @param array<int, array<string, mixed>> $group
     * @return array<int, array<string, mixed>>
     */
    private function widestOverlap(array $group, float $low, float $high): array
    {
        $best = -1.0;
        /** @var array<int, array<string, mixed>> $winners */
        $winners = [];
        foreach ($group as $index => $rule) {
            $length = $this->overlapLength($rule, $low, $high);
            if ($length < 0) {
                continue;
            }
            if ($length > $best + 0.0000001) {
                $best = $length;
                $winners = [$index => $rule];
            } elseif (abs($length - $best) <= 0.0000001) {
                $winners[$index] = $rule;
            }
        }
        if ($winners === []) {
            return [];
        }
        if (count($winners) === 1) {
            return $winners;
        }
        $lower = [];
        foreach ($winners as $index => $rule) {
            if ($this->pointInBand($rule, $low)) {
                $lower[$index] = $rule;
            }
        }
        if (count($lower) === 1) {
            return $lower;
        }
        if ($lower !== []) {
            return $this->lowestMinimum($lower);
        }
        return $this->lowestMinimum($winners);
    }

    /**
     * @param array<int, array<string, mixed>> $rules
     * @return array<int, array<string, mixed>>
     */
    private function lowestMinimum(array $rules): array
    {
        $bestIndex = null;
        $bestMin = INF;
        foreach ($rules as $index => $rule) {
            $min = Values::asFloatOrNull($rule['min_value'] ?? null);
            $rank = $min ?? -INF;
            if ($bestIndex === null || $rank < $bestMin) {
                $bestIndex = $index;
                $bestMin = $rank;
            }
        }
        if ($bestIndex === null) {
            return [];
        }
        return [$bestIndex => $rules[$bestIndex]];
    }

    /**
     * Length of the overlap, 0 when only a point is inside, -1 when none.
     *
     * @param array<string, mixed> $rule
     */
    private function overlapLength(array $rule, float $low, float $high): float
    {
        $bmin = Values::asFloatOrNull($rule['min_value'] ?? null);
        $bmax = Values::asFloatOrNull($rule['max_value'] ?? null);
        $start = $bmin === null ? $low : max($low, $bmin);
        $end = $bmax === null ? $high : min($high, $bmax);
        if ($end < $start) {
            return -1.0;
        }
        if ($end === $start) {
            return $this->pointInBand($rule, $start) ? 0.0 : -1.0;
        }
        return $end - $start;
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function pointInBand(array $rule, float $value): bool
    {
        return $this->inBound($value, $rule['min_value'] ?? null, Values::asBool($rule['min_inclusive'] ?? 1), true)
            && $this->inBound($value, $rule['max_value'] ?? null, Values::asBool($rule['max_inclusive'] ?? 1), false);
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, string> $values
     * @return array{
     *   sentence: string,
     *   source_field_id: string,
     *   source_value: string,
     *   source_field_id_2: ?string,
     *   source_value_2: ?string
     * }|null
     */
    private function actionFromMeasured(array $rule, array $values, string $measuredField, string $raw): ?array
    {
        $source = Values::asString($rule['source_field_id'] ?? '');
        $overlay = $values;
        if ($source !== '') {
            $overlay[$source] = $raw;
        }
        $sentence = trim($this->interpolate(Values::asString($rule['statement_text'] ?? ''), $rule, $overlay));
        if ($sentence === '') {
            return null;
        }
        return [
            'sentence' => $sentence,
            'source_field_id' => $measuredField,
            'source_value' => $raw,
            'source_field_id_2' => null,
            'source_value_2' => null,
        ];
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, string> $values
     */
    private function ruleMatches(array $rule, array $values): bool
    {
        $op = Values::asString($rule['op'] ?? '');
        $sourceId = Values::asString($rule['source_field_id'] ?? '');
        if ($sourceId === '') {
            return false;
        }
        $raw = $values[$sourceId] ?? '';
        $source2 = Values::asString($rule['source_field_id_2'] ?? '');
        $raw2 = $source2 !== '' ? ($values[$source2] ?? '') : '';

        return match ($op) {
            'ratio_lt' => $this->matchRatio($rule, $raw, $raw2, true),
            'ratio_gt' => $this->matchRatio($rule, $raw, $raw2, false),
            'parse_severity' => $this->matchSeverity($rule, $raw),
            default => false,
        };
    }

    /**
     * ratio_lt: both sources > min_value (when set) and v1/v2 < max_value.
     * ratio_gt: v1/v2 > min_value.
     *
     * @param array<string, mixed> $rule
     */
    private function matchRatio(array $rule, string $raw, string $raw2, bool $lessThan): bool
    {
        $v1 = Values::asFloatOrNull($raw);
        $v2 = Values::asFloatOrNull($raw2);
        if ($v1 === null || $v2 === null || $v2 === 0.0) {
            return false;
        }
        $ratio = $v1 / $v2;
        if ($lessThan) {
            $floor = Values::asFloatOrNull($rule['min_value'] ?? null);
            if ($floor !== null && ($v1 <= $floor || $v2 <= $floor)) {
                return false;
            }
            return $this->inBound($ratio, $rule['max_value'] ?? null, Values::asBool($rule['max_inclusive'] ?? 1), false);
        }
        return $this->inBound($ratio, $rule['min_value'] ?? null, Values::asBool($rule['min_inclusive'] ?? 1), true)
            && $this->inBound($ratio, $rule['max_value'] ?? null, Values::asBool($rule['max_inclusive'] ?? 1), false);
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function matchSeverity(array $rule, string $raw): bool
    {
        $tokens = Values::asString($rule['match_token'] ?? '');
        if ($tokens === '') {
            $tokens = Values::asString($rule['statement_text'] ?? '');
        }
        if ($tokens === '' || trim($raw) === '') {
            return false;
        }
        $parts = preg_split('/\s*,\s*/', $tokens, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return false;
        }
        foreach ($parts as $token) {
            $token = trim(Values::asString($token));
            if ($token === '') {
                continue;
            }
            if ($this->sourceMatchesToken($raw, $token)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Exact option_id (ids may contain spaces or '+'), pipe membership
     * for checkbox fields, then a word-boundary match.
     */
    private function sourceMatchesToken(string $raw, string $token): bool
    {
        $raw = trim($raw);
        if ($raw === '' || $token === '') {
            return false;
        }
        if (strcasecmp($raw, $token) === 0) {
            return true;
        }
        foreach (explode('|', $raw) as $part) {
            $part = trim($part);
            if ($part !== '' && strcasecmp($part, $token) === 0) {
                return true;
            }
        }
        $rawNum = Values::asFloatOrNull($raw);
        $tokenNum = Values::asFloatOrNull($token);
        if ($rawNum !== null && $tokenNum !== null && abs($rawNum - $tokenNum) < 0.0000001) {
            return true;
        }
        $quoted = preg_quote($token, '/');
        return preg_match('/(?:^|[^A-Za-z0-9_])' . $quoted . '(?:[^A-Za-z0-9_]|$)/i', $raw) === 1;
    }

    /**
     * True when $value sits on the correct side of an open or closed bound.
     */
    private function inBound(float $value, mixed $bound, bool $inclusive, bool $isMin): bool
    {
        $b = Values::asFloatOrNull($bound);
        if ($b === null) {
            return true;
        }
        if ($isMin) {
            return $inclusive ? $value >= $b : $value > $b;
        }
        return $inclusive ? $value <= $b : $value < $b;
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, string> $values
     * @return array{
     *   sentence: string,
     *   source_field_id: string,
     *   source_value: string,
     *   source_field_id_2: ?string,
     *   source_value_2: ?string
     * }|null
     */
    private function actionFromRule(array $rule, array $values): ?array
    {
        $sentence = trim($this->interpolate(Values::asString($rule['statement_text'] ?? ''), $rule, $values));
        if ($sentence === '') {
            return null;
        }
        $src = Values::asString($rule['source_field_id'] ?? '');
        $src2 = Values::asString($rule['source_field_id_2'] ?? '');
        return [
            'sentence' => $sentence,
            'source_field_id' => $src,
            'source_value' => $src !== '' ? ($values[$src] ?? '') : '',
            'source_field_id_2' => $src2 !== '' ? $src2 : null,
            'source_value_2' => $src2 !== '' ? ($values[$src2] ?? '') : null,
        ];
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, string> $values
     */
    private function interpolate(string $template, array $rule, array $values): string
    {
        if ($template === '') {
            return '';
        }
        $source = Values::asString($rule['source_field_id'] ?? '');
        $source2 = Values::asString($rule['source_field_id_2'] ?? '');
        $out = strtr($template, [
            '{source}' => $values[$source] ?? '',
            '{source_2}' => $values[$source2] ?? '',
            '{value}' => $values[$source] ?? '',
        ]);
        $replaced = preg_replace_callback(
            '/\{([A-Za-z0-9_-]+)\}/',
            static fn (array $m): string => $values[$m[1]] ?? $m[0],
            $out
        );
        return is_string($replaced) ? $replaced : $out;
    }
}
