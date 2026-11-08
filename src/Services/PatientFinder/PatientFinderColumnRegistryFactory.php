<?php

/**
 * PatientFinderColumnRegistryFactory
 *
 * Builds the Patient Finder column registry the same way for both the finder
 * page (dynamic_finder.php) and its server-side endpoint
 * (dynamic_finder_ajax.php), so the displayed and queried column sets always
 * agree. It seeds the active `ptlistcols` columns in seq order — carrying each
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
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

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

    /** @var array<array-key, mixed>|null Cached Sort_Direction map. */
    private ?array $sortDirectionMap = null;

    /**
     * @param callable(string, array<int, string|int>): list<array<mixed>>|null $fetchRecords
     * @param callable(string): array<array-key, mixed>|null $listMapResolver
     *        Resolves a list id to a value => title map (defaults to generate_list_map()).
     * @param PatientFinderBuiltInColumns|null $builtInColumns
     *        The combined columns (defaults to one using the site's date and time formats).
     */
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        ?callable $fetchRecords = null,
        ?callable $listMapResolver = null,
        private ?PatientFinderBuiltInColumns $builtInColumns = null
    ) {
        $this->fetchRecords = $fetchRecords ?? QueryUtils::fetchRecords(...);
        $this->listMapResolver = $listMapResolver;
    }

    public function create(): PatientFinderColumnRegistry
    {
        $registry = new PatientFinderColumnRegistry();
        $rows = ($this->fetchRecords)(
            "SELECT option_id, title, toggle_setting_1, notes FROM list_options "
            . "WHERE list_id = 'ptlistcols' AND activity = 1 ORDER BY seq, title",
            []
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
            $column = $this->builtInColumn($key, $title, $sortDir)
                ?? PatientFinderColumn::fromTable($key, $title, defaultSortDir: $sortDir);
            $registry->add($column->withWidth(PatientFinderValue::string($row['notes'] ?? null)));
        }

        $this->dispatcher->dispatch(
            new PatientFinderColumnRegistrationEvent($registry),
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE
        );

        return $registry;
    }

    private function builtInColumn(string $key, string $title, string $sortDir): ?PatientFinderColumn
    {
        if (!PatientFinderBuiltInColumns::has($key)) {
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
