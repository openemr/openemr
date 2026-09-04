<?php

/**
 * Write the paragraph onto one LBF field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

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
        if ($existing === '') {
            return $add;
        }
        $have = $this->sentenceTokens($existing);
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
        return rtrim($existing) . ' ' . implode(' ', $missing);
    }

    /**
     * @return list<string>
     */
    private function sentenceParts(string $text): array
    {
        $parts = preg_split('/(?<=[.!?])\s+/u', trim($text)) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part !== '') {
                $out[] = $part;
            }
        }
        return $out;
    }

    /**
     * Lowercase token used to compare sentence identity.
     */
    private function normalizeSentence(string $part): string
    {
        $norm = strtolower(trim($part, " \t\n\r\0\x0B.!?"));
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
