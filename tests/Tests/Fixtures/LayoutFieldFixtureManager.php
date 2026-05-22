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

// @codeCoverageIgnoreStart
// Seed and cleanup execute from FieldRenderingSnapshotTest's setUpBeforeClass
// and tearDownAfterClass hooks, which PHPUnit runs outside the coverage
// instrumentation window. The class-level `@codeCoverageIgnore` annotation in
// a docblock alone is not honored by the php-code-coverage parser when the
// file is autoloaded from a context that never executes its body under
// instrumentation; the inline Start/End markers are unambiguous.
final class LayoutFieldFixtureManager
{
    public const SENTINEL = '__test_layout_field__';

    public const LIST_ID = 'test_layout_field_options';

    /** @var list<string> option_ids seeded into LIST_ID */
    public const LIST_OPTION_IDS = ['opt1', 'opt2', 'opt3'];

    /**
     * Foreign reference the renderer's address/telecom/relation templates pass
     * to ContactService::getOrCreateForEntity when blank_form is true. A
     * `contact` row is created on first call; the corresponding cleanup
     * removes it so the integration database stays clean across runs.
     */
    private const BLANK_CONTACT_FOREIGN_TABLE = 'patient_data';
    private const BLANK_CONTACT_FOREIGN_ID = 0;

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
        $this->cleanupBlankFormContact();
    }

    private function seedListOptions(): void
    {
        // ON DUPLICATE KEY UPDATE rather than INSERT IGNORE: if a row already
        // exists for (list_id, option_id), overwrite its title/seq/activity
        // with the sentinel values so the snapshot suite gets deterministic
        // output even on a database with leftover non-sentinel rows. The
        // overwrite is reversed at cleanup() because we only delete rows
        // whose title starts with the sentinel.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO list_options (list_id, option_id, title, seq, is_default, activity)
             VALUES ('lists', ?, ?, 999, 0, 1)
             ON DUPLICATE KEY UPDATE title = VALUES(title), seq = VALUES(seq), activity = VALUES(activity)",
            [self::LIST_ID, self::SENTINEL . ' list']
        );
        foreach (self::LIST_OPTION_IDS as $index => $optionId) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO list_options (list_id, option_id, title, seq, is_default, activity)
                 VALUES (?, ?, ?, ?, 0, 1)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), seq = VALUES(seq), activity = VALUES(activity)",
                [self::LIST_ID, $optionId, self::SENTINEL . ' ' . $optionId, ($index + 1) * 10]
            );
        }
    }

    /**
     * Remove the `contact` row ContactService::getOrCreateForEntity creates
     * when the address/telecom/relation templates render with blank_form=true
     * (foreign_id=0). Without this the integration database accumulates one
     * row per CI run, which can drift the autoinc counter and affect tests
     * that come after.
     */
    private function cleanupBlankFormContact(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM contact WHERE foreign_table_name = ? AND foreign_id = ?',
            [self::BLANK_CONTACT_FOREIGN_TABLE, self::BLANK_CONTACT_FOREIGN_ID]
        );
    }
}
// @codeCoverageIgnoreEnd
