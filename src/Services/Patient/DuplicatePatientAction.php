<?php

/**
 * DuplicatePatientAction is what an operator can choose against a row of the Duplicate Patient
 * Management report.
 *
 * Backed by string because the values travel to the browser as <option> values and come back as
 * form input. The two merge actions are resolved in the browser -- they are links to the Merge
 * Patients page -- while the other two are handled by the controller.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

enum DuplicatePatientAction: string
{
    /** Declare the group's chart not a duplicate. Handled server side. */
    case MarkUnique = 'U';

    /** Rescore the group's chart on its own. Handled server side. */
    case Recompute = 'R';

    /** Merge the group into this row's chart. A link to the merge page. */
    case MergeKeep = 'MK';

    /** Merge this row's chart away into the group's chart. Also a link to the merge page. */
    case MergeDiscard = 'MD';

    /**
     * The values the page's JavaScript and its <option> elements are built from, so the markup and
     * the server-side dispatch cannot drift apart.
     *
     * @return array<string, string>
     */
    public static function forTemplate(): array
    {
        return [
            'markUnique' => self::MarkUnique->value,
            'recompute' => self::Recompute->value,
            'mergeKeep' => self::MergeKeep->value,
            'mergeDiscard' => self::MergeDiscard->value,
        ];
    }
}
