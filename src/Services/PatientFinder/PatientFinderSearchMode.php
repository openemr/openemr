<?php

/**
 * Which fields the Patient Finder's main search box matches, picked from the
 * dropdown next to the box.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

/**
 * Backed because the value arrives from the browser as the `searchMode` request parameter.
 */
enum PatientFinderSearchMode: string
{
    /** Every displayed, searchable column. */
    case All = 'all';
    /** Last, first and middle name. */
    case Name = 'name';
    /** Street, street line 2, city, state and ZIP / postal code. */
    case Address = 'address';
    /** The ZIP / postal code. */
    case Zip = 'zip';

    /**
     * Read the request parameter; a missing or unknown value searches every column.
     */
    public static function fromRequestValue(string $value): self
    {
        return self::tryFrom($value) ?? self::All;
    }

    /**
     * The columns the main search box matches in this mode, or null for the displayed columns.
     *
     * @return list<PatientFinderColumn>|null
     */
    public function globalSearchColumns(): ?array
    {
        return match ($this) {
            self::All => null,
            self::Name => [PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name')],
            self::Address => [
                PatientFinderColumn::fromTable('street', 'Address'),
                PatientFinderColumn::fromTable('street_line_2', 'Address Line 2'),
                PatientFinderColumn::fromTable('city', 'City'),
                PatientFinderColumn::fromTable('state', 'State'),
                PatientFinderColumn::fromTable('postal_code', 'ZIP'),
            ],
            self::Zip => [PatientFinderColumn::fromTable('postal_code', 'ZIP')],
        };
    }
}
