<?php

/**
 * Issue-list panels of the eye exam PMSFH (past medical, surgical, family history).
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
 * The PMSFH panels that are built from the `lists` table.
 *
 * Family history, social history and review of systems are also PMSFH panels,
 * but they are assembled from other sources and so have no case here —
 * {@see self::tryFrom()} returning null is how callers recognize them.
 *
 * Each panel is one stock ISSUE_TYPE narrowed by a subtype, which is how the
 * form separates ophthalmic problems, surgeries and medications from general
 * ones without adding ISSUE_TYPES to the base install.
 */
enum PmsfhPanel: string
{
    case POH = 'POH';
    case POS = 'POS';
    case EyeMeds = 'Eye Meds';
    case PMH = 'PMH';
    case Surgery = 'Surgery';
    case Medication = 'Medication';
    case Allergy = 'Allergy';

    /**
     * The stock OpenEMR ISSUE_TYPE this panel draws from.
     */
    public function issueType(): string
    {
        return match ($this) {
            self::POH, self::PMH => 'medical_problem',
            self::POS, self::Surgery => 'surgery',
            self::EyeMeds, self::Medication => 'medication',
            self::Allergy => 'allergy',
        };
    }

    /**
     * How this panel narrows its ISSUE_TYPE.
     */
    public function subtypeFilter(): SubtypeFilter
    {
        return match ($this) {
            self::POH, self::POS, self::EyeMeds => SubtypeFilter::Eye,
            // fee_sheet stores '' where other paths leave NULL, so both count as "not ophthalmic".
            self::PMH, self::Surgery => SubtypeFilter::BlankOrNull,
            self::Medication, self::Allergy => SubtypeFilter::Any,
        };
    }

    /**
     * Sort order for the panel. Surgeries read most-recent-first; everything
     * else reads alphabetically.
     */
    public function orderBy(): string
    {
        return match ($this) {
            self::Surgery => 'ORDER BY begdate DESC',
            self::POH, self::POS, self::EyeMeds, self::PMH, self::Medication, self::Allergy => 'ORDER BY title',
        };
    }

    /**
     * The issues to list in this panel for one patient.
     */
    public function issuesQuery(string $pid): SqlFragment
    {
        $subtype = $this->subtypeFilter()->condition();

        // The only interpolation is the subtype predicate and the sort order,
        // both of which this class and SubtypeFilter build from closed sets of
        // literals. Every value is bound.
        $sql = <<<SQL
            SELECT *
              FROM lists
             WHERE pid = ?
               AND type = ?
               {$subtype->sql}
             {$this->orderBy()}
            SQL;

        return new SqlFragment($sql, [$pid, $this->issueType(), ...$subtype->params]);
    }
}
