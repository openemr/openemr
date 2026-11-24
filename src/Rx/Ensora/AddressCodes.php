<?php

/**
 * Turns the state and country a facility stores into the codes Ensora accepts.
 *
 * The facility form takes both as free text, so a site may hold "Arkansas" or
 * "United States". Ensora's NCScript schema accepts only two-letter codes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

final readonly class AddressCodes
{
    /** Spellings of the United States, upper-cased with everything but letters removed */
    private const UNITED_STATES = ['US', 'USA', 'UNITEDSTATES', 'UNITEDSTATESOFAMERICA'];

    /**
     * @param array<string, string> $stateCodes two-letter code keyed by upper-cased code and name
     */
    private function __construct(private array $stateCodes)
    {
    }

    /**
     * @param list<array<mixed>> $stateOptions `list_options` rows of the site's state list
     */
    public static function fromStateOptions(array $stateOptions): self
    {
        $stateCodes = [];
        foreach ($stateOptions as $option) {
            $code = self::text($option['option_id'] ?? null);
            if (!self::isTwoLetters($code)) {
                continue;
            }
            $code = strtoupper($code);
            $stateCodes[$code] = $code;
            $title = self::text($option['title'] ?? null);
            if ($title !== '') {
                $stateCodes[mb_strtoupper($title, 'UTF-8')] = $code;
            }
        }

        return new self($stateCodes);
    }

    /** The two-letter state code, or null when the value is neither a code nor a state list name */
    public function state(string $value): ?string
    {
        $value = trim($value);
        $code = $this->stateCodes[mb_strtoupper($value, 'UTF-8')] ?? null;
        if ($code !== null) {
            return $code;
        }

        return self::isTwoLetters($value) ? strtoupper($value) : null;
    }

    /** The two-letter country code, or null when the value is neither a code nor a spelling of the United States */
    public static function country(string $value): ?string
    {
        $value = trim($value);
        if (self::isTwoLetters($value)) {
            return strtoupper($value);
        }

        $letters = strtoupper(implode('', array_filter(str_split($value), ctype_alpha(...))));

        return in_array($letters, self::UNITED_STATES, true) ? 'US' : null;
    }

    private static function isTwoLetters(string $value): bool
    {
        return strlen($value) === 2 && ctype_alpha($value);
    }

    private static function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
