<?php

/**
 * Parses a UCUM unit expression into a unit, ported from ucum-lhc 7.1.9's
 * UnitString; see UcumData for the ucum-lhc notice.
 *
 * The parse keeps ucum-lhc's behaviour exactly, including its repairs:
 * where it substitutes a likely meaning (adding brackets, a unit name for
 * its code, an implied multiplication) it still returns a unit but changes
 * the returned expression, which is what makes the original invalid. Its
 * messages are not kept, except that an annotation error stops the parse.
 *
 * Parens and annotations are swapped for numbered placeholders before the
 * expression is split on its operators, so this holds per-parse state; use
 * a new instance for each parse.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Ucum;

use OpenEMR\Cqm\Cql\Util\Fdlibm;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final class UnitStringParser
{
    private const PARENS_FLAG = 'parens_placeholder';
    private const BRACE_FLAG = 'braces_placeholder';
    private const VALID_ANNOTATION = '/^\{[!-z|~]*\}$/D';
    /** A number run into a code ("2mg"), which ucum-lhc reads as a product. */
    private const START_NUMBER = '/(^[0-9]+)(\[?[a-zA-Z\_0-9a-zA-Z\_]+\]?$)/D';
    /** Codes that contain operator characters, swapped out while splitting. */
    private const SPECIAL_UNITS = [
        'B[10.nV]' => 'specialUnitOne',
        '[m/s2/Hz^(1/2)]' => 'specialUnitTwo',
    ];
    /** The boundaries a repaired code must sit between to be replaced in the expression. */
    private const LEADING_BOUNDARY = '(^|[.\/(])';
    private const TRAILING_BOUNDARY = '($|[.\/)\-\d{])';
    private const UNABLE_TO_UPDATE = '  (Unable to update the unit expression with a suggested replacement.)';

    /** @var array<int, UcumUnit> */
    private array $parensUnits = [];

    /** @var list<string> */
    private array $annotations = [];

    public function __construct(private readonly Ucum $ucum)
    {
    }

    /**
     * @return array{UcumUnit|null, ?string} the unit, and the
     *     expression it was read as (null after an annotation error)
     * @throws UcumException where ucum-lhc throws
     */
    public function parse(string $unitString): array
    {
        $unitString = JavaScript::trim($unitString);
        if ($unitString === '') {
            throw new UcumException('Please specify a unit expression to be validated.');
        }
        $this->parensUnits = [];
        $this->annotations = [];
        $origString = $unitString;
        $string = $this->extractAnnotations($unitString);
        if ($string === null) {
            return [null, null];
        }
        foreach (self::SPECIAL_UNITS as $code => $placeholder) {
            while (str_contains($string, $code)) {
                $string = JavaScript::replaceFirst($string, $code, $placeholder);
            }
        }
        if (str_contains($string, ' ')) {
            throw new UcumException('Blank spaces are not allowed in unit expressions.');
        }
        return $this->parseTheString($string, $origString);
    }

    /**
     * Swaps each {annotation} for a placeholder; null when one is unclosed,
     * unopened or holds a character UCUM does not allow.
     */
    private function extractAnnotations(string $string): ?string
    {
        $open = strpos($string, '{');
        while ($open !== false) {
            $close = strpos($string, '}');
            if ($close === false) {
                return null;
            }
            $brace = JavaScript::substring($string, $open, $close + 1);
            if (preg_match(self::VALID_ANNOTATION, $brace) !== 1) {
                return null;
            }
            $index = count($this->annotations);
            $string = JavaScript::replaceFirst($string, $brace, self::BRACE_FLAG . $index . self::BRACE_FLAG);
            $this->annotations[] = $brace;
            $open = strpos($string, '{');
        }
        return str_contains($string, '}') ? null : $string;
    }

    /**
     * @return array{UcumUnit|null, string}
     */
    private function parseTheString(string $string, string $origString): array
    {
        [$string, $origString, $stop] = $this->processParens($string, $origString);
        if ($stop) {
            return [null, $origString];
        }
        [$terms, $origString, $stop] = $this->makeUnitsArray($string, $origString);
        if ($stop) {
            return [null, $origString];
        }
        $values = [];
        foreach ($terms as [$operator, $code]) {
            if (self::isIntegerUnit($code)) {
                $value = JavaScript::toNumber($code);
            } elseif (str_contains($code, self::PARENS_FLAG)) {
                [$value, $stop] = $this->getParensUnit($code, $origString);
                if ($stop || $value === null) {
                    return [null, $origString];
                }
            } else {
                [$value, $origString] = $this->makeUnit($code, $origString);
                if ($value === null) {
                    return [null, $origString];
                }
            }
            $values[] = [$operator, $value];
        }
        return [$this->performUnitArithmetic($values), $origString];
    }

    /**
     * Parses each top-level parenthesized group on its own and leaves a
     * numbered placeholder in its place.
     *
     * @return array{string, string, bool} the string, the expression, and whether to stop
     */
    private function processParens(string $string, string $origString): array
    {
        $parts = [];
        $stop = false;
        $next = $this->parensUnits === [] ? 0 : max(array_keys($this->parensUnits)) + 1;
        while ($string !== '' && !$stop) {
            $openPos = strpos($string, '(');
            if ($openPos === false) {
                $parts[] = $string;
                if (str_contains($string, ')')) {
                    $stop = true;
                } else {
                    $string = '';
                }
                continue;
            }
            if ($openPos > 0) {
                $parts[] = substr($string, 0, $openPos);
            }
            $length = strlen($string);
            $opened = 1;
            $closed = 0;
            $c = $openPos + 1;
            for (; $c < $length && $opened !== $closed; $c++) {
                if ($string[$c] === '(') {
                    $opened++;
                } elseif ($string[$c] === ')') {
                    $closed++;
                }
            }
            if ($opened !== $closed) {
                $parts[] = substr($origString, $openPos);
                $stop = true;
                continue;
            }
            $parts[] = self::PARENS_FLAG . $next . self::PARENS_FLAG;
            [$unit, $innerOrigString] = $this->parseTheString(JavaScript::substring($string, $openPos + 1, $c - 1), $origString);
            if ($unit === null || ($string[$openPos + 1] ?? '') === '/') {
                $stop = true;
            } else {
                $origString = $innerOrigString;
                $this->parensUnits[$next++] = $unit;
                $string = substr($string, $c);
            }
        }
        if ($stop) {
            $this->parensUnits = [];
        }
        return [implode('', $parts), $origString, $stop];
    }

    /**
     * Splits on the operators into [operator, code] terms, repairing a
     * number run into a code by making the product explicit.
     *
     * @return array{list<array{string, string}>, string, bool} the terms, the expression, and whether to stop
     */
    private function makeUnitsArray(string $string, string $origString): array
    {
        if (preg_match_all('/([.\/]|[^.\/]+)/', $string, $m) === 0) {
            // ucum-lhc reads the first element of a failed match.
            throw new UcumException('Empty unit expression');
        }
        $parts = $m[0];
        if ($parts[0] === '/') {
            array_unshift($parts, '1');
        } elseif ($parts[0] === '.') {
            return [[], $origString, true];
        }
        if (!JavaScript::isNumericString($parts[0]) && $this->startsWithNumber($parts[0], $number)) {
            [, $digits, $rest] = $number;
            $display = $rest;
            if (str_contains($rest, self::PARENS_FLAG)) {
                [$unit, $stop] = $this->getParensUnit($rest, $origString);
                $rest = self::codeOf($unit);
                $display = "($rest)";
                if ($stop) {
                    return [[], $origString, true];
                }
            }
            $origString = JavaScript::replaceFirst($origString, $digits . $display, "$digits.$display");
            $parts[0] = $rest;
            array_unshift($parts, $digits, '.');
        }

        $terms = [['', $parts[0]]];
        $count = count($parts);
        for ($n = 1; $n < $count; $n++) {
            $operator = $parts[$n++];
            $code = $parts[$n] ?? null;
            if ($code === null || $code === '.' || $code === '/') {
                return [$terms, $origString, true];
            }
            if (JavaScript::isNumericString($code) || !$this->startsWithNumber($code, $number)) {
                $terms[] = [$operator, $code];
                continue;
            }
            [$whole, $digits, $rest] = $number;
            if (str_contains($rest, self::PARENS_FLAG)) {
                [$unit, $stop] = $this->getParensUnit($rest, $origString);
                $invalid = '(' . self::codeOf($unit) . ')';
                if ($stop) {
                    return [$terms, $origString, true];
                }
                $parensString = "($digits.$invalid)";
                $origString = JavaScript::replaceFirst($origString, $digits . $invalid, $parensString);
                [$placeholder, , $stop] = $this->processParens($parensString, $origString);
                if ($stop) {
                    return [$terms, $origString, true];
                }
                $terms[] = [$operator, $placeholder];
            } else {
                $parensString = "($digits.$rest)";
                [$placeholder, , $stop] = $this->processParens($parensString, $origString);
                if ($stop) {
                    return [$terms, $origString, true];
                }
                $origString = JavaScript::replaceFirst($origString, $whole, $parensString);
                $terms[] = [$operator, $placeholder];
            }
        }
        return [$terms, $origString, false];
    }

    /**
     * @param-out array{string, string, string} $match the whole match, the digits and the rest
     */
    private function startsWithNumber(string $code, mixed &$match = null): bool
    {
        if (preg_match(self::START_NUMBER, $code, $m) !== 1 || str_starts_with($m[2], self::BRACE_FLAG)) {
            $match = ['', '', ''];
            return false;
        }
        $match = [$m[0], $m[1], $m[2]];
        return true;
    }

    /**
     * The code ucum-lhc reads from a parens unit; it reads one that is
     * missing (an exponent after the parens) as a TypeError.
     */
    private static function codeOf(?UcumUnit $unit): string
    {
        if ($unit === null) {
            throw new UcumException('Missing parenthesized unit');
        }
        return $unit->csCode;
    }

    /**
     * The unit of a parens placeholder, with a number or annotation before
     * it, or an annotation after it.
     *
     * @return array{UcumUnit|null, bool} the unit and whether to stop
     */
    private function getParensUnit(string $string, string $origString): array
    {
        $flagLength = strlen(self::PARENS_FLAG);
        $start = strpos($string, self::PARENS_FLAG);
        $end = strrpos($string, self::PARENS_FLAG);
        if ($start === false || $end === false) {
            throw new UcumException('Processing error - missing parens placeholder');
        }
        $before = $start > 0 ? substr($string, 0, $start) : null;
        $after = $end + $flagLength < strlen($string) ? substr($string, $end + $flagLength) : null;
        $indexText = JavaScript::substring($string, $start + $flagLength, $end);
        if (!JavaScript::isNumericString($indexText)) {
            throw new UcumException('Processing error - invalid parens number');
        }
        $index = JavaScript::toNumber($indexText);
        $unit = floor($index) === $index ? ($this->parensUnits[(int) $index] ?? null) : null;
        if ($unit === null) {
            throw new UcumException('Processing error - no unit for parens number');
        }
        $code = $unit->csCode;
        if ($before !== null) {
            if (JavaScript::isNumericString($before)) {
                $unit->magnitude *= JavaScript::toNumber($before);
                $code = "$before.$code";
            } elseif (str_contains($before, self::BRACE_FLAG)) {
                [$annotation, $startText, $endText] = $this->getAnnoText($before);
                if ($startText !== null || $endText !== null) {
                    throw new UcumException('Text found before the parentheses included an annotation along with other text');
                }
                $code .= $annotation;
            } else {
                return [$unit, true];
            }
        }
        if ($after !== null) {
            if (!str_contains($after, self::BRACE_FLAG)) {
                // An exponent after the parens (invalid since UCUM 1.9), or other text.
                return [JavaScript::isNumericString($after) ? null : $unit, true];
            }
            [$annotation, $startText, $endText] = $this->getAnnoText($after);
            if ($startText !== null || $endText !== null) {
                throw new UcumException('Text found after the parentheses included an annotation along with other text');
            }
            $code .= $annotation;
        }
        $unit->csCode = $code;
        return [$unit, false];
    }

    /**
     * The annotation of a brace placeholder, with any text before and
     * after it.
     *
     * @return array{string, ?string, ?string}
     */
    private function getAnnoText(string $string): array
    {
        $flagLength = strlen(self::BRACE_FLAG);
        $start = strpos($string, self::BRACE_FLAG);
        if ($start === false) {
            throw new UcumException('Processing error - missing annotation placeholder');
        }
        $startText = $start > 0 ? substr($string, 0, $start) : null;
        $string = substr($string, $start);
        $end = strpos($string, self::BRACE_FLAG, 1);
        if ($end === false) {
            throw new UcumException('Processing error - unterminated annotation placeholder');
        }
        $endText = $end + $flagLength < strlen($string) ? substr($string, $end + $flagLength) : null;
        $indexText = JavaScript::substring($string, $flagLength, $end);
        $index = JavaScript::isNumericString($indexText) ? JavaScript::toNumber($indexText) : NAN;
        if (is_nan($index) || $index >= count($this->annotations) || $index < 0 || floor($index) !== $index) {
            throw new UcumException('Processing error - invalid annotation index');
        }
        return [$this->annotations[(int) $index], $startText, $endText];
    }

    /**
     * A single code: from the table, an annotation, a code with a prefix
     * and/or an exponent, or a repaired code (added brackets or a unit
     * name).
     *
     * @return array{UcumUnit|null, string}
     */
    private function makeUnit(string $code, string $origString): array
    {
        $unit = $this->ucum->unitByCode($code);
        if ($unit !== null) {
            return [clone $unit, $origString];
        }
        if (str_contains($code, self::BRACE_FLAG)) {
            return $this->getUnitWithAnnotation($code, $origString);
        }
        if (str_contains($code, '^')) {
            $unit = $this->ucum->unitByCode(JavaScript::replaceFirst($code, '^', '*'));
            if ($unit !== null) {
                $unit = clone $unit;
                $unit->csCode = JavaScript::replaceFirst($unit->csCode, '*', '^');
                return [$unit, $origString];
            }
        }
        foreach (self::SPECIAL_UNITS as $special => $placeholder) {
            if (str_contains($code, $placeholder)) {
                $code = JavaScript::replaceFirst($code, $placeholder, $special);
            }
        }
        $unit = $this->ucum->unitByCode($code);
        if ($unit !== null) {
            return [clone $unit, $origString];
        }

        $origCode = $code;
        $exponentText = null;
        $origUnit = null;
        $isIntegerUnitWithExp = false;
        if (preg_match('/(^[^\-\+]+?)([\-\+\d]+)$/D', $code, $m) === 1) {
            $code = $m[1];
            $exponentText = $m[2];
            $isIntegerUnitWithExp = self::isIntegerUnit($code);
            $origUnit = $isIntegerUnitWithExp
                ? UcumUnit::forNumber($code, JavaScript::toNumber($code))
                : $this->ucum->unitByCode($code);
        }
        if ($exponentText !== null && is_nan(JavaScript::toNumber($exponentText))) {
            return [null, $origString];
        }

        $prefix = null;
        $prefixCode = '';
        if ($origUnit === null && strlen($code) > 1) {
            do {
                $prefixCode .= $code[0];
                $code = substr($code, 1);
                $prefix = $this->ucum->prefix($prefixCode);
                if ($prefix !== null) {
                    $origUnit = $this->ucum->unitByCode($code);
                }
            } while ($origUnit === null && strlen($prefixCode) < 2 && strlen($code) > 1);
            if ($origUnit !== null && $origUnit->fromLoinc) {
                $origUnit = null;
            }
        }
        if ($origUnit === null) {
            [$unit, $origString] = $this->getUnitAfterAddingBrackets($origCode, $origString);
            if ($unit === null) {
                [$unit, $origString] = $this->getUnitByName($origCode, $origString);
            }
            return [$unit, $origString];
        }

        $unit = clone $origUnit;
        $magnitude = $unit->magnitude;
        $prefixValue = $prefix[0] ?? 1.0;
        $exponent = 0;
        if ($exponentText !== null) {
            if ($unit->isSpecial) {
                return [null, $origString];
            }
            $exponent = (int) JavaScript::parseSignedDigits($exponentText);
            $unit->dim = array_map(static fn (int $d): int => $d * $exponent, $unit->dim);
            $unit->equivalentExp *= $exponent;
            $unit->moleExp *= $exponent;
            $magnitude = Fdlibm::pow($magnitude, $exponent);
            $unit->magnitude = $magnitude;
            if ($prefix !== null) {
                $prefixValue = $prefix[1] !== null
                    ? Fdlibm::pow(10.0, $exponent * $prefix[1])
                    : Fdlibm::pow($prefixValue, $exponent);
            }
        }
        if ($prefix !== null) {
            if ($unit->cnv !== null) {
                $unit->cnvPfx = $prefixValue;
            } else {
                $unit->magnitude = $magnitude * $prefixValue;
            }
            $unit->csCode = $prefixCode . $unit->csCode;
        }
        if ($exponent !== 0) {
            $sign = $isIntegerUnitWithExp && $exponent > 0 ? '+' : '';
            $unit->csCode .= $sign . $exponent;
        }
        return [$unit, $origString];
    }

    /**
     * @return array{UcumUnit|null, string}
     */
    private function getUnitAfterAddingBrackets(string $code, string $origString): array
    {
        $bracketed = '[' . $code . ']';
        $unit = $this->ucum->unitByCode($bracketed);
        if ($unit === null) {
            return [null, $origString];
        }
        return [clone $unit, self::substituteCode($origString, $code, $bracketed)];
    }

    /**
     * @return array{UcumUnit|null, string}
     */
    private function getUnitByName(string $name, string $origString): array
    {
        $unit = $this->ucum->unitByName($name);
        if ($unit === null) {
            return [null, $origString];
        }
        return [clone $unit, self::substituteCode($origString, $name, $unit->csCode)];
    }

    /** Puts the repaired code in the expression, or notes that it could not. */
    private static function substituteCode(string $origString, string $code, string $replacement): string
    {
        $pattern = '/' . self::LEADING_BOUNDARY . '(' . preg_quote($code, '/') . ')' . self::TRAILING_BOUNDARY . '/D';
        $updated = JavaScript::replaceFirstMatch($origString, $pattern, '$1' . $replacement . '$3');
        return $updated === $origString ? $origString . self::UNABLE_TO_UPDATE : $updated;
    }

    /**
     * @return array{UcumUnit|null, string}
     */
    private function getUnitWithAnnotation(string $code, string $origString): array
    {
        [$annotation, $before, $after] = $this->getAnnoText($code);
        if ($before === null && $after === null) {
            // ucum-lhc tries the text as a bracketed code only for a message.
            $this->makeUnit('[' . JavaScript::substring($annotation, 1, strlen($annotation) - 1) . ']', $origString);
            return [new UcumUnit($annotation), $origString];
        }
        if ($before !== null && $after === null) {
            if (self::isIntegerUnit($before)) {
                return [UcumUnit::forNumber($before . $annotation, JavaScript::toNumber($before)), $origString];
            }
            [$unit, $unitOrigString] = $this->makeUnit($before, $origString);
            if ($unit === null) {
                return [null, $origString];
            }
            $unit->csCode .= $annotation;
            return [$unit, $unitOrigString];
        }
        if ($before === null) {
            if (self::isIntegerUnit($after)) {
                // ucum-lhc's message for this case is a malformed template
                // literal, which throws.
                throw new UcumException('Annotation before an integer');
            }
            [$unit] = $this->makeUnit($after, $origString);
            if ($unit === null) {
                return [null, $origString];
            }
            $unit->csCode .= $annotation;
            return [$unit, $unit->csCode];
        }
        return [null, $origString];
    }

    /**
     * Multiplies and divides the terms left to right.
     *
     * @param list<array{string, UcumUnit|float}> $terms
     */
    private function performUnitArithmetic(array $terms): ?UcumUnit
    {
        $result = self::numberAsUnit($terms[0][1]);
        $count = count($terms);
        for ($i = 1; $i < $count; $i++) {
            [$operator, $next] = $terms[$i];
            $next = self::numberAsUnit($next);
            try {
                $result = $operator === '/' ? $result->divide($next) : $result->multiplyThese($next);
            } catch (UcumException) {
                return null;
            }
        }
        return $result;
    }

    private static function numberAsUnit(UcumUnit|float $value): UcumUnit
    {
        if (!is_float($value)) {
            return $value;
        }
        $code = JavaScript::numberToString($value);
        if (!self::isIntegerUnit($code)) {
            // An integer of 22 or more digits, which ucum-lhc goes on to
            // multiply as a bare number; not supported here.
            throw new UcumException('Integer unit too large');
        }
        return UcumUnit::forNumber($value == 0 ? '' : $code, $value);
    }

    private static function isIntegerUnit(string $string): bool
    {
        return preg_match('/^\d+$/D', $string) === 1;
    }
}
