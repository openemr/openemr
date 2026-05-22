<?php

/**
 * Seeds the database fixtures the layout-field renderer reads.
 *
 * Each layout-field data_type branch in library/options.inc.php may read from
 * list_options, users, facility, patient_data, pharmacies, address_book,
 * libreehr_groups, insurance_companies, openemr_postcalendar_categories, etc.
 * This manager seeds the minimum world each branch needs so that
 * generate_form_field / generate_display_field / generate_print_field can be
 * exercised against deterministic data. Every seeded row carries the
 * {@see self::SENTINEL} string in its title so cleanup can scope its DELETE
 * statements to rows this manager created — never to rows a developer or
 * another test may have left in the same list_id.
 *
 * Seeders are added incrementally per data-type as cases come online in
 * FieldRenderingSnapshotTest. Tables whose columns differ across OpenEMR
 * schema revisions are seeded lazily on demand rather than eagerly here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

final class LayoutFieldFixtureManager
{
    public const SENTINEL = '__test_layout_field__';

    public const LIST_ID = 'test_layout_field_options';

    /** @var list<string> option_ids seeded into LIST_ID */
    public const LIST_OPTION_IDS = ['opt1', 'opt2', 'opt3'];

    public function seed(): void
    {
        $this->seedListOptions();
    }

    public function cleanup(): void
    {
        // Match on the sentinel-prefixed title so we only delete rows this
        // manager seeded. A developer or another test may legitimately have
        // rows under the same list_id; widening the DELETE to all list_id
        // matches would silently destroy unrelated data.
        $titlePrefix = self::SENTINEL . '%';
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM list_options WHERE list_id = ? AND title LIKE ?',
            [self::LIST_ID, $titlePrefix]
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM list_options WHERE list_id = 'lists' AND option_id = ? AND title LIKE ?",
            [self::LIST_ID, $titlePrefix]
        );
    }

    private function seedListOptions(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT IGNORE INTO list_options
                (list_id, option_id, title, seq, is_default, activity)
             VALUES ('lists', ?, ?, 999, 0, 1)",
            [self::LIST_ID, self::SENTINEL . ' list']
        );
        foreach (self::LIST_OPTION_IDS as $index => $optionId) {
            QueryUtils::sqlStatementThrowException(
                "INSERT IGNORE INTO list_options
                    (list_id, option_id, title, seq, is_default, activity)
                 VALUES (?, ?, ?, ?, 0, 1)",
                [self::LIST_ID, $optionId, self::SENTINEL . ' ' . $optionId, ($index + 1) * 10]
            );
        }
    }
}
