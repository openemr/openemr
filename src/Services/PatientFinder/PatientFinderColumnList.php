<?php

/**
 * The Administration > Lists lists that choose the Patient Finder's columns: one for the
 * Patient List tab and one for the Recent Patients tab.
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
 * Backed by the list id stored in list_options, which the finder page also sends back
 * to its endpoint as the `finderList` request parameter.
 */
enum PatientFinderColumnList: string
{
    /** Patient Finder – Patient List Columns */
    case PatientList = 'ptlistcols';
    /** Patient Finder – Recent Patients Columns */
    case Recent = 'recent_patient_columns';

    /**
     * Whether a list id is one of the finder's column lists, which the List editor
     * shows with the column picker, sort direction and width.
     */
    public static function isColumnList(string $listId): bool
    {
        return self::tryFrom($listId) !== null;
    }

    /**
     * Whether an option id names a column the finder can show: its Name column, one of
     * the combined columns it builds itself, or a patient_data field.
     *
     * @param callable(string): bool $isPatientDataField
     */
    public static function isValidColumnId(string $optionId, callable $isPatientDataField): bool
    {
        if ($optionId === '' || $optionId === '*') {
            return false;
        }
        return $optionId === PatientFinderQueryBuilder::NAME_KEY
            || PatientFinderBuiltInColumns::has($optionId)
            || $isPatientDataField($optionId);
    }

    /**
     * Read the finder page's `finderList` request value; anything else is the Patient List.
     */
    public static function fromRequestValue(string $value): self
    {
        return $value === 'recent' ? self::Recent : self::PatientList;
    }

    /**
     * The list whose columns this tab shows: the Recent Patients tab uses the Patient
     * List's columns while the site's setting says so.
     */
    public function columnsSource(bool $recentSharesColumns): self
    {
        return match ($this) {
            self::PatientList => self::PatientList,
            self::Recent => $recentSharesColumns ? self::PatientList : self::Recent,
        };
    }
}
