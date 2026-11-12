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
    /** The field starts with the term. */
    case Start;
    /** The term is anywhere in the field. */
    case Anywhere;
    /** The field ends with the term, as the last digits of an SSN. */
    case End;
    /** The term is a whole or partial date, read in the site's date format. */
    case Date;

    /**
     * The LIKE pattern for a term. A date is always read as a date; otherwise an exact
     * search matches the whole field.
     *
     * @param callable(string): string $datePattern turns a whole or partial date into a LIKE pattern
     */
    public function pattern(string $term, bool $exact, callable $datePattern): string
    {
        return match ($this) {
            self::Date => $datePattern($term),
            self::Start => $exact ? $term : $term . '%',
            self::Anywhere => $exact ? $term : '%' . $term . '%',
            self::End => $exact ? $term : '%' . $term,
        };
    }
}
