<?php

/**
 * PatientFinderColumnRegistryFactory
 *
 * Builds the Patient Finder column registry the same way for both the finder
 * page (dynamic_finder.php) and its server-side endpoint
 * (dynamic_finder_ajax.php), so the displayed and queried column sets always
 * agree. It seeds the active columns of one of the finder's column lists
 * ({@see PatientFinderColumnList}: `ptlistcols` for the Patient List tab,
 * `recent_patient_columns` for Recent Patients) in seq order — carrying each
 * column's configured initial sort direction (its `toggle_setting_1`, resolved
 * through the `Sort_Direction` list). An entry whose option id is one of
 * {@see PatientFinderBuiltInColumns::keys()} is seeded as that combined column
 * rather than as a `patient_data` field. An entry's notes hold its display width
 * (see {@see PatientFinderColumn::withWidth()}). It then dispatches
 * {@see PatientFinderColumnRegistrationEvent} so modules can add, remove or
 * reorder columns.
 *
 * Database access and the sort-direction lookup are injected (defaulting to
 * core), so the factory is unit-testable without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class PatientFinderColumnRegistryFactory
{
    /** @var callable(string, array<int, string|int>): list<array<mixed>> */
    private $fetchRecords;

    /** @var (callable(string): array<array-key, mixed>)|null */
    private $listMapResolver;

    /** @var callable(string, string): bool */
    private $aclCheck;

    /** @var array<array-key, mixed>|null Cached Sort_Direction map. */
    private ?array $sortDirectionMap = null;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(string): array<array-key, mixed>|null $listMapResolver
     *        Resolves a list id to a value => title map (defaults to generate_list_map()).
     * @param PatientFinderBuiltInColumns|null $builtInColumns
     *        The combined columns (defaults to one using the site's date and time formats).
     * @param callable(string, string): bool|null $aclCheck
     *        Whether the current user has an ACL section and value (defaults to AclMain::aclCheckCore()).
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        ?callable $fetchRecords = null,
        ?callable $listMapResolver = null,
        private ?PatientFinderBuiltInColumns $builtInColumns = null,
        ?callable $aclCheck = null
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->listMapResolver = $listMapResolver;
        $this->aclCheck = $aclCheck ?? static fn(string $section, string $value): bool
            => AclMain::aclCheckCore($section, $value);
    }

    /**
     * Build the registry: the list's active columns the user may see, then any columns listeners add.
     */
    public function create(
        PatientFinderColumnList $columnList = PatientFinderColumnList::PatientList
    ): PatientFinderColumnRegistry {
        $registry = new PatientFinderColumnRegistry();
        $rows = ($this->fetchRecords)(
            "SELECT option_id, title, toggle_setting_1, notes FROM list_options "
            . "WHERE list_id = ? AND activity = 1 ORDER BY seq, title",
            [$columnList->value]
        );
        foreach ($rows as $row) {
            $key = PatientFinderValue::string($row['option_id'] ?? null);
            if ($key === '') {
                continue;
            }
            $title = PatientFinderValue::string($row['title'] ?? null) ?: $key;
            if (function_exists('xl_list_label')) {
                $title = PatientFinderValue::string(xl_list_label($title));
            }
            $sortDir = $this->sortDirection(PatientFinderValue::string($row['toggle_setting_1'] ?? null));
            if (PatientFinderBuiltInColumns::has($key)) {
                // A combined column the user may not see is left out, never read as a table field.
                $column = $this->builtInColumn($key, $title, $sortDir);
                if ($column === null) {
                    continue;
                }
            } else {
                $column = PatientFinderColumn::fromTable($key, $title, defaultSortDir: $sortDir);
            }
            $registry->add($column->withWidth(PatientFinderValue::string($row['notes'] ?? null)));
        }

        $this->dispatcher->dispatch(
            new PatientFinderColumnRegistrationEvent($registry, $columnList),
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE
        );

        return $registry;
    }

    /**
     * A combined column, or null when the user lacks the ACL it needs.
     */
    private function builtInColumn(string $key, string $title, string $sortDir): ?PatientFinderColumn
    {
        $acl = PatientFinderBuiltInColumns::requiredAcl($key);
        if ($acl !== null && !($this->aclCheck)($acl[0], $acl[1])) {
            return null;
        }
        $this->builtInColumns ??= PatientFinderBuiltInColumns::fromGlobals(OEGlobalsBag::getInstance());
        return $this->builtInColumns->column($key, $title, $sortDir);
    }

    /**
     * Resolve a column's stored toggle_setting_1 to an "asc"/"desc" direction via
     * the Sort_Direction list, defaulting to "asc".
     */
    private function sortDirection(string $toggleSetting): string
    {
        if ($toggleSetting === '') {
            return 'asc';
        }
        $this->sortDirectionMap ??= $this->listMapResolver !== null
            ? ($this->listMapResolver)('Sort_Direction')
            : generate_list_map('Sort_Direction');
        $dir = PatientFinderValue::string($this->sortDirectionMap[$toggleSetting] ?? 'asc');
        return strtolower($dir) === 'desc' ? 'desc' : 'asc';
    }
}
