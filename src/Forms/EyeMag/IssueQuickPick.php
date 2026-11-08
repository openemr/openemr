<?php

/**
 * Quick-pick option lists offered next to each eye exam issue panel.
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
 * The issue panels that offer a quick-pick list.
 *
 * A panel's quick picks are the titles this provider has used most in the last
 * 30 days; until a provider has built up a history, the stock `list_options`
 * entries stand in. Panels with no case here (family history, social history,
 * review of systems) build their picks elsewhere.
 */
enum IssueQuickPick: string
{
    case PMH = 'PMH';
    case Medication = 'Medication';
    case Surgery = 'Surgery';
    case Allergy = 'Allergy';
    case POH = 'POH';
    case POS = 'POS';
    case EyeMeds = 'Eye Meds';

    /**
     * Below this many recent titles the provider is treated as just starting
     * out, and the stock list is used instead.
     */
    public const MIN_RECENT_TITLES = 4;

    /**
     * The stock OpenEMR ISSUE_TYPE this panel draws from.
     */
    public function issueType(): string
    {
        return match ($this) {
            self::PMH, self::POH => 'medical_problem',
            self::Surgery, self::POS => 'surgery',
            self::Medication, self::EyeMeds => 'medication',
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
            self::PMH, self::Medication, self::Surgery, self::Allergy => SubtypeFilter::Blank,
        };
    }

    /**
     * How many recent titles to offer.
     */
    public function recentLimit(): int
    {
        return match ($this) {
            self::PMH => 20,
            self::Medication, self::Surgery, self::Allergy, self::POH, self::POS, self::EyeMeds => 10,
        };
    }

    /**
     * The `list_options` list backing this panel's stock picks.
     */
    public function listOptionsId(): string
    {
        return match ($this) {
            self::PMH, self::POH => 'medical_problem_issue_list',
            self::Surgery, self::POS => 'surgery_issue_list',
            self::Medication, self::EyeMeds => 'medication_issue_list',
            self::Allergy => 'allergy_issue_list',
        };
    }

    /**
     * The titles this provider has used most over the last 30 days.
     */
    public function recentTitlesQuery(int $providerId): SqlFragment
    {
        $subtype = $this->subtypeFilter()->condition();

        // The only interpolation is $subtype->sql, which SubtypeFilter builds from
        // a closed set of literals. Every value is bound, including the limit.
        $sql = <<<SQL
            SELECT title,
                   title AS option_id,
                   diagnosis AS codes,
                   COUNT(title) AS freq
              FROM lists
             WHERE type LIKE ?
               {$subtype->sql}
               AND pid IN (
                   SELECT pid
                     FROM form_encounter
                    WHERE provider_id = ?
                      AND date BETWEEN NOW() - INTERVAL 30 DAY AND NOW()
                   )
             GROUP BY title
             ORDER BY freq DESC
             LIMIT ?
            SQL;

        return new SqlFragment(
            $sql,
            [$this->issueType(), ...$subtype->params, $providerId, $this->recentLimit()],
        );
    }

    /**
     * The stock picks to fall back on, split the same ophthalmic/general way the
     * panels are.
     */
    public function stockTitlesQuery(): SqlFragment
    {
        return match ($this->subtypeFilter()) {
            SubtypeFilter::Eye => new SqlFragment(
                'SELECT * FROM list_options WHERE list_id = ? AND subtype = ?',
                [$this->listOptionsId(), 'eye'],
            ),
            SubtypeFilter::Blank, SubtypeFilter::BlankOrNull, SubtypeFilter::Any => new SqlFragment(
                'SELECT * FROM list_options WHERE list_id = ? AND subtype NOT LIKE ?',
                [$this->listOptionsId(), 'eye'],
            ),
        };
    }
}
