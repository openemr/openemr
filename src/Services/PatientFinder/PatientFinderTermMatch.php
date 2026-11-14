<?php

/**
 * Where the main search box's term must match within a field.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

enum PatientFinderTermMatch
{
    /**
     * The character that makes the next %, _ or itself in a pattern match only itself. Every
     * LIKE the finder builds names it in an ESCAPE clause. Not a backslash, which a string
     * literal reads differently under the NO_BACKSLASH_ESCAPES SQL mode.
     */
    public const ESCAPE_CHARACTER = '|';

    /** The field starts with the term. */
    case Start;
    /** The term is anywhere in the field. */
    case Anywhere;
    /** The field ends with the term, as the last digits of an SSN, even in an exact search. */
    case End;
    /** The term is a whole or partial date, read in the site's date format. */
    case Date;

    /**
     * The LIKE pattern for a term. A date is always read as a date, and the end of a field
     * is always matched as its end; otherwise an exact search matches the whole field. The
     * term's own % and _ match only themselves.
     *
     * @param callable(string): string $datePattern turns a whole or partial date into a LIKE pattern
     */
    public function pattern(string $term, bool $exact, callable $datePattern): string
    {
        $literal = self::escape($term);
        return match ($this) {
            self::Date => $datePattern($term),
            self::Start => $exact ? $literal : $literal . '%',
            self::Anywhere => $exact ? $literal : '%' . $literal . '%',
            self::End => '%' . $literal,
        };
    }

    /**
     * A term as LIKE text that matches only itself: %, _ and the escape character each
     * preceded by the escape character.
     */
    public static function escape(string $term): string
    {
        $escape = self::ESCAPE_CHARACTER;
        return strtr($term, [$escape => $escape . $escape, '%' => $escape . '%', '_' => $escape . '_']);
    }
}
