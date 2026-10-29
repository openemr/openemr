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

use OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent;
use OpenEMR\Events\PatientFinder\PatientFinderRowEnrichEvent;
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistry;
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

        $aaData = $service->assembleRows($columns, $rows, $fieldsInfo, $enrich);

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
        $fetch = static fn(string $sql, array $binds): array => str_contains($sql, 'ptlistcols')
            ? [['option_id' => 'name', 'title' => 'Full Name'], ['option_id' => 'DOB', 'title' => 'DOB']]
            : [];

        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static function (PatientFinderColumnRegistrationEvent $e): void {
                    $e->addColumn(PatientFinderColumn::enriched('ihi', 'IHI'));
                    $e->removeColumn('DOB');
                },
        ]);

        $service = new PatientFinderQueryService($dispatcher, null, $fetch);
        $registry = $service->buildColumnRegistry();

        $this->assertSame(['name', 'ihi'], $registry->keys());
    }

    public function testHandleProducesDataTablesResponse(): void
    {
        $dataRow = ['pid' => 1, 'lname' => 'Smith', 'fname' => 'John', 'mname' => '', 'DOB' => '1980-01-01'];
        $fetch = static function (string $sql, array $binds) use ($dataRow): array {
            if (str_contains($sql, 'ptlistcols')) {
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
            static fn(array $info, mixed $v): string => PatientFinderValue::string($v)
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
}
