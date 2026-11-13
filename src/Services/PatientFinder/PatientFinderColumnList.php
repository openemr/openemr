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

use OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * shows with the column picker, sort direction and width. Takes the List editor's
     * untyped list id as it is, so its callers share one check.
     */
    public static function isColumnList(mixed $listId): bool
    {
        return is_string($listId) && self::tryFrom($listId) !== null;
    }

    /**
     * Whether an option id names a column the finder can show: its Name column, one of
     * the combined columns it builds itself, a column a module adds, or a patient_data field.
     *
     * @param callable(string): bool $isPatientDataField
     * @param list<string> $moduleColumnKeys see moduleColumnKeys()
     */
    public static function isValidColumnId(
        string $optionId,
        callable $isPatientDataField,
        array $moduleColumnKeys = []
    ): bool {
        if ($optionId === '' || $optionId === '*') {
            return false;
        }
        return $optionId === PatientFinderQueryBuilder::NAME_KEY
            || PatientFinderBuiltInColumns::has($optionId)
            || in_array($optionId, $moduleColumnKeys, true)
            || $isPatientDataField($optionId);
    }

    /**
     * The keys of the columns modules add to this list's tab, sorted, so the List editor can
     * offer them: an entry with a module column's key sets that column's place, sort
     * direction and width. Asks the modules through the same event the finder dispatches.
     *
     * @return list<string>
     */
    public function moduleColumnKeys(EventDispatcherInterface $dispatcher): array
    {
        $registry = new PatientFinderColumnRegistry();
        $dispatcher->dispatch(
            new PatientFinderColumnRegistrationEvent($registry, $this),
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE
        );
        $keys = array_values(array_filter(
            $registry->keys(),
            static fn(string $key): bool => $key !== PatientFinderQueryBuilder::NAME_KEY
                && !PatientFinderBuiltInColumns::has($key)
        ));
        sort($keys);
        return $keys;
    }

    /**
     * The choices the List editor's column picker offers for one entry: the finder's own
     * columns (its Name column and the combined ones), the columns modules add, the
     * patient_data fields sorted by name, and the entry's own id when it is in none of
     * those, such as a disabled module's, so saving the list never changes it.
     *
     * @param array<array-key, mixed> $patientDataFields the patient_data column names
     * @param array<array-key, mixed> $moduleColumnKeys see moduleColumnKeys()
     * @return array{combined: list<string>, module: list<string>, fields: list<string>, other: list<string>}
     */
    public static function columnChoices(
        string $currentId,
        array $patientDataFields,
        array $moduleColumnKeys = []
    ): array {
        $combined = array_merge([PatientFinderQueryBuilder::NAME_KEY], PatientFinderBuiltInColumns::keys());
        $fields = array_values(array_filter($patientDataFields, is_string(...)));
        sort($fields);
        // A module's column with a patient_data field's name is listed with the fields.
        $module = array_values(array_diff(array_filter($moduleColumnKeys, is_string(...)), $combined, $fields));
        $known = array_merge($combined, $module, $fields);
        $isOther = $currentId !== '' && !in_array($currentId, $known, true);
        return [
            'combined' => $combined,
            'module' => $module,
            'fields' => $fields,
            'other' => $isOther ? [$currentId] : [],
        ];
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
