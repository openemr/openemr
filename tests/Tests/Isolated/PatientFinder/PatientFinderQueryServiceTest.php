<?php

/**
 * Isolated PatientFinderQueryService Test
 *
 * Exercises the finder orchestration without a database: a fake event
 * dispatcher drives the registration / filter / enrich events, and the DB
 * accessors and cell formatters are injected. Covers column resolution, row
 * assembly (name concat, formatted patient_data cells, enriched cells) and the
 * full handle() pipeline including the DataTables response shape and counts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent;
use OpenEMR\Events\PatientFinder\PatientFinderRowEnrichEvent;
use OpenEMR\Services\PatientFinder\PatientFinderAppointments;
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistry;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistryFactory;
use OpenEMR\Services\PatientFinder\PatientFinderQueryBuilder;
use OpenEMR\Services\PatientFinder\PatientFinderQueryService;
use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use OpenEMR\Services\PatientFinder\PatientFinderValue;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PatientFinderQueryServiceTest extends TestCase
{
    /**
     * A real dispatcher with one registered listener per event name.
     *
     * @param array<string, callable> $listeners
     */
    private function dispatcher(array $listeners = []): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener($eventName, $listener);
        }
        return $dispatcher;
    }

    /**
     * A registry factory backed by the test's fetcher, with the patient_data fields the
     * tests' column lists use, so nothing reads the database.
     *
     * @param callable(string, array<int, string|int>): list<array<mixed>> $fetch
     */
    private static function registryFactory(
        EventDispatcherInterface $dispatcher,
        callable $fetch
    ): PatientFinderColumnRegistryFactory {
        return new PatientFinderColumnRegistryFactory(
            $dispatcher,
            $fetch,
            patientDataFields: static fn(): array => ['DOB', 'sex', 'postal_code', 'street', 'city', 'state']
        );
    }

    public function testResolveActiveColumnsFollowsClientOrderAndDropsUnknown(): void
    {
        $registry = new PatientFinderColumnRegistry();
        $registry->add(PatientFinderColumn::fromTable('name', 'Name'));
        $registry->add(PatientFinderColumn::fromTable('DOB', 'DOB'));
        $registry->add(PatientFinderColumn::fromTable('sex', 'Sex'));

        $service = new PatientFinderQueryService($this->dispatcher());
        $request = PatientFinderRequest::fromArray(['sColumns' => 'sex,name,unknown']);

        $resolved = $service->resolveActiveColumns($registry, $request);
        $keys = array_map(static fn(PatientFinderColumn $c): string => $c->key, $resolved);

        $this->assertSame(['sex', 'name'], $keys);
    }

    public function testResolveActiveColumnsPreservesClientIndexesWithGaps(): void
    {
        // An unknown key in the middle must leave a gap so the surviving columns
        // keep their client index (which the per-column search/sort rely on).
        $registry = new PatientFinderColumnRegistry();
        $registry->add(PatientFinderColumn::fromTable('name', 'Name'));
        $registry->add(PatientFinderColumn::fromTable('sex', 'Sex'));

        $service = new PatientFinderQueryService($this->dispatcher());
        $request = PatientFinderRequest::fromArray(['sColumns' => 'name,unknown,sex']);

        $resolved = $service->resolveActiveColumns($registry, $request);

        $this->assertSame([0, 2], array_keys($resolved));
        $this->assertSame('name', $resolved[0]->key);
        $this->assertSame('sex', $resolved[2]->key);
    }

    public function testResolveActiveColumnsFallsBackToFullRegistry(): void
    {
        $registry = new PatientFinderColumnRegistry();
        $registry->add(PatientFinderColumn::fromTable('name', 'Name'));

        $service = new PatientFinderQueryService($this->dispatcher());
        $resolved = $service->resolveActiveColumns($registry, PatientFinderRequest::fromArray([]));

        $this->assertCount(1, $resolved);
    }

    public function testAssembleRowsHandlesNameFormattedAndEnrichedCells(): void
    {
        $service = new PatientFinderQueryService(
            $this->dispatcher(),
            null,
            null,
            null,
            static fn(mixed $v): string => PatientFinderValue::string($v),              // attr = identity
            static fn(array $info, mixed $v): string => 'FMT(' . PatientFinderValue::string($v) . ')'
        );

        $columns = [
            PatientFinderColumn::fromTable('name', 'Name'),
            PatientFinderColumn::fromTable('DOB', 'DOB'),
            PatientFinderColumn::enriched('ihi', 'IHI'),
        ];
        $rows = [
            ['pid' => 1, 'lname' => 'Smith', 'fname' => 'John', 'mname' => 'Q', 'DOB' => '1980-01-01'],
        ];
        $fieldsInfo = ['DOB' => ['field_id' => 'DOB', 'data_type' => '4', 'list_id' => '']];

        $enrich = new PatientFinderRowEnrichEvent([1], [$columns[2]]);
        $enrich->setValue(1, 'ihi', '539305455928516268');

        $aaData = $service->assembleRows($columns, $rows, $fieldsInfo, $enrich, count($columns));

        $this->assertSame([
            [
                'DT_RowId' => 'pid_1',
                0 => 'Smith, John Q',
                1 => 'FMT(1980-01-01)',
                2 => '539305455928516268',
            ],
        ], $aaData);
    }

    public function testBuildColumnRegistrySeedsPtlistcolsAndRunsEvent(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [['option_id' => 'name', 'title' => 'Full Name'], ['option_id' => 'DOB', 'title' => 'DOB']]
            : [];

        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static function (PatientFinderColumnRegistrationEvent $e): void {
                    $e->addColumn(PatientFinderColumn::enriched('ihi', 'IHI'));
                    $e->removeColumn('DOB');
                },
        ]);

        $service = new PatientFinderQueryService(
            $dispatcher,
            null,
            $fetch,
            registryFactory: self::registryFactory($dispatcher, $fetch)
        );
        $registry = $service->buildColumnRegistry();

        $this->assertSame(['name', 'ihi'], $registry->keys());
    }

    public function testHandleProducesDataTablesResponse(): void
    {
        $dataRow = ['pid' => 1, 'lname' => 'Smith', 'fname' => 'John', 'mname' => '', 'DOB' => '1980-01-01'];
        $fetch = static function (string $sql, array $binds) use ($dataRow): array {
            if (in_array('ptlistcols', $binds, true)) {
                return [['option_id' => 'name', 'title' => 'Name'], ['option_id' => 'DOB', 'title' => 'DOB']];
            }
            if (str_contains($sql, 'data_type')) { // fetchFieldsInfo
                return [['field_id' => 'DOB', 'data_type' => '4', 'list_id' => '']];
            }
            if (str_contains($sql, 'FROM patient_data')) { // the data query
                return [$dataRow];
            }
            return [];
        };

        // Two count queries in order: total then filtered.
        $counts = [['count' => 5], ['count' => 1]];
        $sqlQuery = static function (string $sql, array $binds) use (&$counts) {
            return array_shift($counts);
        };

        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static fn(PatientFinderColumnRegistrationEvent $e) => $e->addColumn(
                    PatientFinderColumn::enriched('ihi', 'IHI')
                ),
            PatientFinderRowEnrichEvent::EVENT_HANDLE =>
                static fn(PatientFinderRowEnrichEvent $e) => $e->setValue(1, 'ihi', 'IHI-123'),
        ]);

        $builder = new PatientFinderQueryBuilder(
            static fn(string $col, array $tables): string => '`' . $col . '`',
            static fn(string $term): string => $term
        );
        $service = new PatientFinderQueryService(
            $dispatcher,
            $builder,
            $fetch,
            $sqlQuery,
            static fn(mixed $v): string => PatientFinderValue::string($v),
            static fn(array $info, mixed $v): string => PatientFinderValue::string($v),
            self::registryFactory($dispatcher, $fetch)
        );

        $out = $service->handle([
            'sEcho' => '3',
            'sColumns' => 'name,DOB,ihi',
        ]);

        $this->assertSame(3, $out['sEcho']);
        $this->assertSame(5, $out['iTotalRecords']);
        $this->assertSame(1, $out['iTotalDisplayRecords']);
        $this->assertSame([
            [
                'DT_RowId' => 'pid_1',
                0 => 'Smith, John',
                1 => '1980-01-01',
                2 => 'IHI-123',
            ],
        ], $out['aaData']);
    }

    /**
     * The data query the service runs for one global search in the given mode.
     */
    private function dataSqlForSearchMode(string $mode): string
    {
        $dataSql = '';
        $fetch = static function (string $sql, array $binds) use (&$dataSql): array {
            if (in_array('ptlistcols', $binds, true)) {
                return [['option_id' => 'name', 'title' => 'Name'], ['option_id' => 'DOB', 'title' => 'DOB']];
            }
            if (str_contains($sql, 'FROM patient_data')) {
                $dataSql = $sql;
            }
            return [];
        };
        $builder = new PatientFinderQueryBuilder(
            static fn(string $col, array $tables): string => '`' . $col . '`',
            static fn(string $term): string => $term
        );
        $service = new PatientFinderQueryService(
            $this->dispatcher(),
            $builder,
            $fetch,
            static fn(string $sql, array $binds): array => ['count' => 0],
            static fn(mixed $v): string => PatientFinderValue::string($v),
            registryFactory: self::registryFactory($this->dispatcher(), $fetch)
        );
        $service->handle(['sColumns' => 'name,DOB', 'sSearch' => '021', 'searchMode' => $mode]);
        return $dataSql;
    }

    public function testSearchAllModeMatchesTheDisplayedColumns(): void
    {
        $sql = $this->dataSqlForSearchMode('all');

        $this->assertStringContainsString('lname LIKE ? ESCAPE \'|\'', $sql);
        $this->assertStringContainsString('`DOB` LIKE ? ESCAPE \'|\'', $sql);
        $this->assertStringNotContainsString('postal_code', $sql);
    }

    public function testSearchNameModeMatchesOnlyTheName(): void
    {
        $sql = $this->dataSqlForSearchMode('name');

        $this->assertStringContainsString('lname LIKE ? ESCAPE \'|\' OR fname LIKE ? ESCAPE \'|\' OR mname LIKE ? ESCAPE \'|\'', $sql);
        $this->assertStringNotContainsString('`DOB` LIKE ? ESCAPE \'|\'', $sql);
    }

    public function testSearchZipModeMatchesOnlyThePostalCode(): void
    {
        $sql = $this->dataSqlForSearchMode('zip');

        $this->assertStringContainsString('( `postal_code` LIKE ? ESCAPE \'|\' )', $sql);
        $this->assertStringNotContainsString('lname LIKE ? ESCAPE \'|\'', $sql);
        $this->assertStringNotContainsString('`DOB` LIKE ? ESCAPE \'|\'', $sql);
    }

    public function testSearchAddressModeMatchesEveryAddressField(): void
    {
        $sql = $this->dataSqlForSearchMode('address');

        $this->assertStringContainsString(
            '( `street` LIKE ? ESCAPE \'|\' OR `street_line_2` LIKE ? ESCAPE \'|\' OR `city` LIKE ? ESCAPE \'|\' OR `state` LIKE ? ESCAPE \'|\' OR `postal_code` LIKE ? ESCAPE \'|\' )',
            $sql
        );
        $this->assertStringNotContainsString('lname LIKE ? ESCAPE \'|\'', $sql);
        $this->assertStringNotContainsString('`DOB` LIKE ? ESCAPE \'|\'', $sql);
    }

    /**
     * The column-list ids and data query the service uses for one Recent Patients request.
     *
     * @return array{lists: list<mixed>, dataSql: string, dataBinds: array<mixed>}
     */
    private function recentRequest(bool $shared): array
    {
        $seen = ['lists' => [], 'dataSql' => '', 'dataBinds' => []];
        $fetch = static function (string $sql, array $binds) use (&$seen): array {
            if (str_contains($sql, 'FROM list_options') && str_contains($sql, 'activity = 1')) {
                $seen['lists'][] = $binds[0] ?? null;
                return [['option_id' => 'name', 'title' => 'Name']];
            }
            if (str_contains($sql, 'FROM patient_data')) {
                $seen['dataSql'] = $sql;
                $seen['dataBinds'] = $binds;
            }
            return [];
        };
        $service = new PatientFinderQueryService(
            $this->dispatcher(),
            new PatientFinderQueryBuilder(
                static fn(string $col, array $tables): string => '`' . $col . '`',
                static fn(string $term): string => $term
            ),
            $fetch,
            static fn(string $sql, array $binds): array => ['count' => 0],
            static fn(mixed $v): string => PatientFinderValue::string($v),
            null,
            null,
            static fn(): array => [12, 4],
            $shared
        );
        $service->handle(['sColumns' => 'finder_detail,name', 'finderList' => 'recent']);
        return $seen;
    }

    public function testRecentPatientsAreLimitedToTheUsersRecentListMostRecentFirst(): void
    {
        $seen = $this->recentRequest(false);

        $this->assertStringContainsString('WHERE patient_data.pid IN (?, ?)', $seen['dataSql']);
        $this->assertStringContainsString('ORDER BY FIELD(patient_data.pid, ?, ?)', $seen['dataSql']);
        $this->assertSame([12, 4, 12, 4], $seen['dataBinds']);
    }

    public function testRecentPatientsUseTheirOwnListUnlessShared(): void
    {
        $this->assertSame(['recent_patient_columns'], $this->recentRequest(false)['lists']);
        $this->assertSame(['ptlistcols'], $this->recentRequest(true)['lists']);
    }

    /**
     * A Recent Patients request to a service built without the recent patients and the
     * site's setting fails, rather than showing an empty list.
     */
    public function testRecentPatientsWithoutTheirSourceFails(): void
    {
        $service = new PatientFinderQueryService(
            $this->dispatcher(),
            null,
            static fn(string $sql, array $binds): array => [],
            static fn(string $sql, array $binds): array => ['count' => 0],
            static fn(mixed $v): string => PatientFinderValue::string($v)
        );

        $this->expectException(\LogicException::class);
        $service->handle(['sColumns' => 'name', 'finderList' => 'recent']);
    }

    /**
     * The recent patient list becomes its pids, in order, leaving out anything that is not one.
     */
    public function testRecentPidsOfReadsThePatientServiceList(): void
    {
        $this->assertSame(
            [5, 2],
            PatientFinderQueryService::recentPidsOf([['pid' => 5], ['pid' => '2'], ['pid' => 0], 'junk'])
        );
    }

    /**
     * The list's Next Appt cells come from PatientFinderAppointments for the page's patients,
     * after the query, which never names the column.
     */
    public function testNextAppointmentIsFilledForThePagesPatients(): void
    {
        $dataSql = '';
        $fetch = static function (string $sql, array $binds) use (&$dataSql): array {
            if (in_array('ptlistcols', $binds, true)) {
                return [
                    ['option_id' => 'DOB', 'title' => 'DOB'],
                    ['option_id' => 'next_appointment', 'title' => 'Next Appt'],
                ];
            }
            if (str_contains($sql, 'FROM patient_data')) {
                $dataSql = $sql;
                return [['pid' => 7, 'DOB' => '1980-01-01']];
            }
            return [];
        };
        $dispatcher = $this->dispatcher();
        $service = new PatientFinderQueryService(
            $dispatcher,
            new PatientFinderQueryBuilder(
                static fn(string $col, array $tables): string => '`' . $col . '`',
                static fn(string $term): string => $term
            ),
            $fetch,
            static fn(string $sql, array $binds): array => ['count' => 1],
            static fn(mixed $v): string => PatientFinderValue::string($v),
            static fn(array $info, mixed $v): string => PatientFinderValue::string($v),
            new PatientFinderColumnRegistryFactory(
                $dispatcher,
                $fetch,
                aclCheck: static fn(string $section, string $value): bool => true,
                patientDataFields: static fn(): array => ['DOB']
            ),
            appointments: new PatientFinderAppointments(
                static fn(string $sql, array $binds): array => $binds[0] === '7'
                    ? [['pc_eid' => 1, 'pc_eventDate' => '2026-12-01', 'pc_startTime' => '09:30:00', 'pc_recurrtype' => 0]]
                    : [],
                static fn(string $from, string $to, int $eid): array => [],
                new FrozenClock(new DateTimeImmutable('2026-11-13 12:00:00'))
            )
        );

        $out = $service->handle(['sEcho' => '1', 'sColumns' => 'DOB,next_appointment']);

        $this->assertSame([['DT_RowId' => 'pid_7', 0 => '1980-01-01', 1 => '2026-12-01 09:30']], $out['aaData']);
        $this->assertStringNotContainsString('next_appointment', $dataSql);
    }
}
