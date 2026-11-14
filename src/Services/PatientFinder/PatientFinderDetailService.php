<?php

/**
 * PatientFinderDetailService
 *
 * The values shown when a Patient Finder row is expanded. What is shown is the
 * Patient Finder Detail layout ({@see self::LAYOUT_ID}), which an administrator
 * edits in Administration > Layouts like any other. A field in it is either a
 * `patient_data` field or one of the combined columns in
 * {@see PatientFinderBuiltInColumns}; any other field id is left out.
 *
 * Only the fields the layout shows are read, and a patient that a
 * {@see PatientFinderFilterEvent} listener hides from the finder is not found
 * here either, so expanding a row can never show more than the finder would.
 *
 * Values are returned raw. The layout renderer escapes each one when it builds
 * the HTML, except for the NationNotes type, which it prints as is; a field of
 * that type is never read, so it shows empty. A combined column that needs an
 * ACL the user lacks (the appointments need patients/appt) is not read either.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\BoundFilter;
use OpenEMR\Events\PatientFinder\PatientFinderFilterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PatientFinderDetailService
{
    /**
     * The layout that decides what an expanded row shows. Not an LBF id on purpose: every
     * active LBF layout is offered as an encounter form.
     */
    public const LAYOUT_ID = 'PTFINDER';

    /** The NationNotes data type, which the layout renderer prints without escaping. */
    private const UNESCAPED_DATA_TYPE = 34;

    /**
     * Data types the layout renderer fills from the session's or the global patient rather
     * than from the values it is given: allergies (24), previous names (52), and the
     * address, telecom and relation lists (54, 55, 56). The renderer shows every row of the
     * layout, so one of these would show the open chart's data under another patient.
     */
    private const SESSION_PATIENT_DATA_TYPES = [24, 52, 54, 55, 56];

    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var callable(): list<string> */
    private $patientDataFields;

    /** @var callable(string, string): bool */
    private $aclCheck;

    /** @var callable(string, array<string, string>): string */
    private $renderLayout;

    private readonly PatientFinderBuiltInColumns $builtInColumns;

    /** @var list<array<mixed>>|null The detail layout's active rows, read once. */
    private ?array $layoutRows = null;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(): list<string>|null $patientDataFields the patient_data column names
     * @param PatientFinderBuiltInColumns|null $builtInColumns the combined columns
     * @param callable(string, string): bool|null $aclCheck whether the current user has an ACL
     *        section and value (defaults to AclMain::aclCheckCore())
     * @param callable(string, array<string, string>): string|null $renderLayout a layout's HTML
     *        for the given values (defaults to display_layout_tabs_data() from options.inc.php)
     * @param PatientFinderAppointments|null $appointments the Next and Last Appt values (defaults
     *        to the site's date and time formats)
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        ?callable $fetchRecords = null,
        ?callable $patientDataFields = null,
        ?PatientFinderBuiltInColumns $builtInColumns = null,
        ?callable $aclCheck = null,
        ?callable $renderLayout = null,
        private ?PatientFinderAppointments $appointments = null
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->patientDataFields = $patientDataFields ?? self::listPatientDataFields(...);
        $this->aclCheck = $aclCheck ?? static fn(string $section, string $value): bool
            => AclMain::aclCheckCore($section, $value);
        $this->renderLayout = $renderLayout ?? self::renderLayoutTabsData(...);
        $this->builtInColumns = $builtInColumns ?? new PatientFinderBuiltInColumns();
    }

    /**
     * The detail layout's HTML for one patient, or null when there is no such patient or the
     * finder would not show them.
     */
    public function html(int $pid): ?string
    {
        if ($this->layoutReadsSessionPatient()) {
            return null;
        }
        $values = $this->values($pid);
        return $values === null ? null : ($this->renderLayout)(self::LAYOUT_ID, $values);
    }

    /**
     * Whether the detail layout shows anything; the finder offers to expand a row only then.
     * A layout with a row that reads the session's patient is never shown (see
     * SESSION_PATIENT_DATA_TYPES).
     */
    public function isEnabled(): bool
    {
        return !$this->layoutReadsSessionPatient() && $this->layoutFieldIds() !== [];
    }

    /**
     * Whether an active row of the detail layout has a data type the renderer fills from the
     * session's or the global patient.
     */
    private function layoutReadsSessionPatient(): bool
    {
        foreach ($this->layoutRows() as $row) {
            if (in_array(PatientFinderValue::int($row['data_type'] ?? null), self::SESSION_PATIENT_DATA_TYPES, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The detail layout's active rows, in its order.
     *
     * @return list<array<mixed>>
     */
    private function layoutRows(): array
    {
        return $this->layoutRows ??= ($this->fetchRecords)(
            'SELECT field_id, data_type FROM layout_options WHERE form_id = ? AND uor > 0'
            . ' ORDER BY group_id, seq',
            [self::LAYOUT_ID]
        );
    }

    /**
     * The layout's values for one patient, keyed by field id, or null when there is no such
     * patient or the finder would not show them.
     *
     * @return array<string, string>|null
     */
    public function values(int $pid): ?array
    {
        if ($pid <= 0) {
            return null;
        }

        $select = ['patient_data.pid'];
        $appointmentColumns = [];
        $patientDataFields = ($this->patientDataFields)();
        foreach ($this->layoutFieldIds() as $fieldId) {
            if (PatientFinderBuiltInColumns::has($fieldId)) {
                $acl = PatientFinderBuiltInColumns::requiredAcl($fieldId);
                if ($acl !== null && !($this->aclCheck)($acl[0], $acl[1])) {
                    continue;
                }
                $builtIn = PatientFinderBuiltInColumn::tryFrom($fieldId);
                if ($builtIn !== null && $builtIn->isAppointment()) {
                    // Worked out once the patient is found, as the list does.
                    $appointmentColumns[] = $builtIn;
                    continue;
                }
                $column = $this->builtInColumns->column($fieldId, $fieldId);
                if ($column !== null && $column->selectExpression !== null) {
                    $select[] = $column->selectExpression . ' AS `' . $fieldId . '`';
                }
            } elseif (in_array($fieldId, $patientDataFields, true)) {
                $select[] = 'patient_data.`' . $fieldId . '`';
            }
        }

        $filter = $this->finderFilter();
        $rows = ($this->fetchRecords)(
            'SELECT ' . implode(', ', $select) . ' FROM patient_data'
            . ' WHERE patient_data.pid = ? AND (' . $filter->getFilterClause() . ') LIMIT 1',
            array_merge([$pid], $filter->getBoundValues())
        );
        $row = $rows[0] ?? null;
        if ($row === null) {
            return null;
        }

        $values = [];
        foreach ($row as $key => $value) {
            if ($key !== 'pid') {
                $values[(string) $key] = PatientFinderValue::string($value);
            }
        }
        if ($appointmentColumns !== []) {
            $this->appointments ??= PatientFinderAppointments::fromGlobals(OEGlobalsBag::getInstance());
            $values += $this->appointments->values([$pid], $appointmentColumns)[$pid] ?? [];
        }
        return $values;
    }

    /**
     * The field ids the detail layout shows, in its order, each once. An id is left out
     * entirely when any active row for it is a NationNotes field: the renderer looks values up
     * by id, so a second row with the same id would otherwise feed it the value unescaped.
     *
     * @return list<string>
     */
    private function layoutFieldIds(): array
    {
        $rows = $this->layoutRows();
        $ids = [];
        $unescaped = [];
        foreach ($rows as $row) {
            $id = PatientFinderValue::string($row['field_id'] ?? null);
            // A field id is only ever used as a quoted identifier, but keep it to the
            // characters field ids are made of.
            if ($id === '' || preg_match('/^\w+$/', $id) !== 1) {
                continue;
            }
            if (PatientFinderValue::int($row['data_type'] ?? null) === self::UNESCAPED_DATA_TYPE) {
                $unescaped[$id] = true;
            }
            $ids[$id] = true;
        }
        return array_values(array_filter(
            array_keys($ids),
            static fn(string $id): bool => !isset($unescaped[$id])
        ));
    }

    /**
     * The finder's patient filter, so a patient a module hides from the finder stays hidden.
     */
    private function finderFilter(): BoundFilter
    {
        $event = new PatientFinderFilterEvent(new BoundFilter(), [], []);
        $this->dispatcher->dispatch($event, PatientFinderFilterEvent::EVENT_HANDLE);
        return $event->getBoundFilter();
    }

    /**
     * The layout renderer prints its HTML, so it is captured. The caller has loaded
     * options.inc.php, which defines it.
     *
     * @param array<string, string> $values
     */
    private static function renderLayoutTabsData(string $layoutId, array $values): string
    {
        ob_start();
        display_layout_tabs_data($layoutId, $values);
        return (string) ob_get_clean();
    }

    /**
     * @return list<string>
     */
    private static function listPatientDataFields(): array
    {
        return array_values(QueryUtils::listTableFields('patient_data'));
    }
}
