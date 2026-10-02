<?php

/**
 * Evaluate statement rules against LBF field values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

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
    public function evaluate(string $formId, array $values, array $rules): array
    {
        Identifiers::assertFieldId($formId);
        $actions = [];
        foreach ($rules as $rule) {
            if (isset($rule['enabled']) && Values::asInt($rule['enabled'], 1) === 0) {
                continue;
            }
            $ruleForm = Values::asString($rule['form_id'] ?? '');
            if ($ruleForm !== '' && $ruleForm !== $formId) {
                continue;
            }
            if (!$this->ruleMatches($rule, $values)) {
                continue;
            }
            $action = $this->actionFromRule($rule, $values);
            if ($action !== null) {
                $actions[] = $action;
            }
        }
        return $actions;
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
            'band' => $this->matchBand($rule, $raw, $source2, $raw2),
            'ratio_lt' => $this->matchRatio($rule, $raw, $raw2, true),
            'ratio_gt' => $this->matchRatio($rule, $raw, $raw2, false),
            'parse_severity' => $this->matchSeverity($rule, $raw),
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function matchBand(array $rule, string $raw, string $source2, string $raw2): bool
    {
        $v = Values::asFloatOrNull($raw);
        if ($v === null) {
            return false;
        }
        if ($source2 !== '' && trim($raw2) === '') {
            return false;
        }
        if (!$this->inBound($v, $rule['min_value'] ?? null, Values::asBool($rule['min_inclusive'] ?? 1), true)) {
            return false;
        }
        if (!$this->inBound($v, $rule['max_value'] ?? null, Values::asBool($rule['max_inclusive'] ?? 1), false)) {
            return false;
        }
        return true;
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
