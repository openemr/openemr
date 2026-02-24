<?php

/**
 * Immutable value object representing the result of parsing an HL7 result message.
 *
 * Replaces the raw $rhl7_return array used by receive_hl7_results().
 * Messages prefixed with '*' are errors, '>' are informational — preserves the
 * existing convention in rhl7LogMsg().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

class Hl7ResultParseResult
{
    /**
     * @param list<string> $messages  Log messages ('*' prefix = error, '>' prefix = info)
     * @param bool         $fatal     Whether a fatal error occurred
     * @param bool         $needsMatch Whether this result is pending a patient match request
     */
    public function __construct(
        public readonly array $messages,
        public readonly bool $fatal = false,
        public readonly bool $needsMatch = false
    ) {
    }

    /**
     * Construct from the legacy array returned by receive_hl7_results().
     *
     * @param array<string, mixed> $result Legacy array with keys 'mssgs', 'fatal', 'needmatch'
     */
    public static function fromLegacyArray(array $result): self
    {
        /** @var list<string> $messages */
        $messages = $result['mssgs'] ?? [];

        return new self(
            messages: $messages,
            fatal: isset($result['fatal']) && $result['fatal'] !== false,
            needsMatch: isset($result['needmatch']) && $result['needmatch'] !== false
        );
    }

    /**
     * Convert back to the legacy array format for backward compatibility.
     *
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
    {
        $result = ['mssgs' => $this->messages];
        if ($this->fatal) {
            $result['fatal'] = true;
        }

        if ($this->needsMatch) {
            $result['needmatch'] = true;
        }

        return $result;
    }
}
