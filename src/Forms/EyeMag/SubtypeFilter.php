<?php

/**
 * Subtype filters applied to `lists` rows by the eye exam form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * The eye exam form splits the stock ISSUE_TYPES (`medical_problem`, `surgery`,
 * `medication`) into ophthalmic and general lists using `lists.subtype`. Each
 * case is one of the subtype predicates the form needs.
 */
enum SubtypeFilter
{
    /** Ophthalmic rows only. */
    case Eye;

    /** Rows whose subtype was explicitly stored as an empty string. */
    case Blank;

    /** Rows with no subtype, however it was stored (fee_sheet writes ''; other paths leave NULL). */
    case BlankOrNull;

    /** No subtype predicate at all. */
    case Any;

    /**
     * The predicate to append to a query over the `lists` table.
     */
    public function condition(): SqlFragment
    {
        return match ($this) {
            self::Eye => new SqlFragment('AND subtype = ?', ['eye']),
            self::Blank => new SqlFragment('AND subtype = ?', ['']),
            self::BlankOrNull => new SqlFragment('AND (subtype = ? OR subtype IS NULL)', ['']),
            self::Any => new SqlFragment(''),
        };
    }
}
