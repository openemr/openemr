<?php

/**
 * Write the paragraph onto one LBF field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

class StatementApplier
{
    /**
     * Merge generated text onto $paragraphField in overwrite or append mode.
     *
     * @param array<string, string> $current
     * @param list<array<string, mixed>> $actions
     * @return array<string, string>
     */
    public function apply(
        array $current,
        array $actions,
        string $mode,
        string $paragraphField,
        ?string $paragraphOverride = null
    ): array {
        Identifiers::assertFieldId($paragraphField);
        $paragraph = $paragraphOverride ?? StatementParagraph::fromActions($actions);
        $existing = $current[$paragraphField] ?? '';
        $out = $current;
        if ($mode === 'overwrite') {
            $out[$paragraphField] = $paragraph;
        } elseif ($paragraph !== '') {
            $out[$paragraphField] = $this->appendText($existing, $paragraph);
        }
        return $out;
    }

    /**
     * Field list the writer should persist for this paragraph.
     *
     * @return list<array{field_id:string}>
     */
    public function writeActions(string $paragraphField): array
    {
        return [['field_id' => $paragraphField]];
    }

    /**
     * Append only generated sentences that are not already in the paragraph.
     */
    private function appendText(string $existing, string $add): string
    {
        $have = $existing === '' ? [] : $this->sentenceTokens($existing);
        $missing = [];
        foreach ($this->sentenceParts($add) as $part) {
            $norm = $this->normalizeSentence($part);
            if ($norm === '' || in_array($norm, $have, true)) {
                continue;
            }
            $missing[] = $part;
            $have[] = $norm;
        }
        if ($missing === []) {
            return $existing;
        }
        if ($existing === '') {
            return implode(' ', $missing);
        }
        $separator = preg_match('/[.!?]["\']?\s*$/u', $existing) === 1 ? ' ' : '. ';

        return rtrim($existing) . $separator . implode(' ', $missing);
    }

    /**
     * @return list<string>
     */
    private function sentenceParts(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }
        $out = [];
        $start = 0;
        $length = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $length; $i++) {
            $ch = mb_substr($text, $i, 1, 'UTF-8');
            if ($ch !== '.' && $ch !== '!' && $ch !== '?') {
                continue;
            }
            $next = $i + 1 < $length ? mb_substr($text, $i + 1, 1, 'UTF-8') : '';
            if ($next !== '' && preg_match('/\s/u', $next) !== 1) {
                continue;
            }
            if ($ch === '.' && $this->periodIsAbbreviation($text, $i)) {
                continue;
            }
            $part = trim(mb_substr($text, $start, $i - $start + 1, 'UTF-8'));
            if ($part !== '') {
                $out[] = $part;
            }
            $start = $i + 1;
            while ($start < $length && preg_match('/\s/u', mb_substr($text, $start, 1, 'UTF-8')) === 1) {
                $start++;
            }
            $i = $start - 1;
        }
        $tail = trim(mb_substr($text, $start, null, 'UTF-8'));
        if ($tail !== '') {
            $out[] = $tail;
        }

        return $out;
    }

    /**
     * A period after an abbreviation stays inside the sentence. A single
     * letter is an initial, so "A. B." still splits.
     */
    private function periodIsAbbreviation(string $text, int $periodIndex): bool
    {
        $before = mb_substr($text, 0, $periodIndex, 'UTF-8');
        if (preg_match('/(\p{L}+)$/u', $before, $match) !== 1) {
            return false;
        }
        $word = mb_strtolower($match[1], 'UTF-8');
        if (mb_strlen($word, 'UTF-8') < 2) {
            return false;
        }

        return isset([
            'approx' => true,
            'cm' => true,
            'dr' => true,
            'eq' => true,
            'etc' => true,
            'fig' => true,
            'kg' => true,
            'mcg' => true,
            'mg' => true,
            'ml' => true,
            'mm' => true,
            'mr' => true,
            'mrs' => true,
            'ms' => true,
            'sig' => true,
            'st' => true,
            'vs' => true,
            'vol' => true,
        ][$word]);
    }

    /**
     * Lowercase token used to compare sentence identity.
     */
    private function normalizeSentence(string $part): string
    {
        $norm = mb_strtolower(trim($part, " \t\n\r\0\x0B.!?"), 'UTF-8');
        return preg_replace('/\s+/u', ' ', $norm) ?? $norm;
    }

    /**
     * @return list<string>
     */
    private function sentenceTokens(string $text): array
    {
        $out = [];
        foreach ($this->sentenceParts($text) as $part) {
            $norm = $this->normalizeSentence($part);
            if ($norm !== '') {
                $out[] = $norm;
            }
        }
        return $out;
    }
}
