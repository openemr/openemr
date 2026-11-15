<?php

/**
 * Isolated PatientFinderColumnRegistryFactory Test
 *
 * Verifies the shared registry is seeded from ptlistcols (with each column's
 * configured initial sort direction resolved through the Sort_Direction list)
 * and that the registration event lets modules adjust the set — all without a
 * database.
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
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnList;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistryFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PatientFinderColumnRegistryFactoryTest extends TestCase
{
    /**
     * The patient_data fields the tests' lists use.
     *
     * @return list<string>
     */
    private static function fields(): array
    {
        return ['fname', 'mname', 'lname', 'DOB', 'sex', 'phone_home', 'ss', 'pubpid', 'street', 'city', 'state', 'postal_code'];
    }

    /**
     * An entry that is neither the Name column, a combined column nor a patient_data
     * field is left out, so it cannot break the finder's query.
     */
    public function testLeavesOutAnIdTheFinderCannotRead(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [
                ['option_id' => 'name', 'title' => 'Full Name', 'toggle_setting_1' => ''],
                ['option_id' => 'module_gone', 'title' => 'Old Module Column', 'toggle_setting_1' => ''],
                ['option_id' => 'age', 'title' => 'Age', 'toggle_setting_1' => ''],
                ['option_id' => 'DOB', 'title' => 'DOB', 'toggle_setting_1' => ''],
            ]
            : [];

        $registry = (new PatientFinderColumnRegistryFactory(
            $this->dispatcher(),
            $fetch,
            patientDataFields: self::fields(...)
        ))->create();

        $this->assertSame(['name', 'age', 'DOB'], $registry->keys());
    }

    /** @param array<string, callable> $listeners */
    private function dispatcher(array $listeners = []): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener($eventName, $listener);
        }
        return $dispatcher;
    }

    /**
     * A module column keeps the place an administrator gave its list entry: the listener's
     * column replaces the entry where it stands.
     */
    public function testModuleColumnKeepsItsListPosition(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [
                ['option_id' => 'name', 'title' => 'Full Name', 'toggle_setting_1' => ''],
                ['option_id' => 'ihi', 'title' => 'IHI', 'toggle_setting_1' => '', 'notes' => '9rem'],
                ['option_id' => 'DOB', 'title' => 'DOB', 'toggle_setting_1' => ''],
            ]
            : [];
        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static fn(PatientFinderColumnRegistrationEvent $e) => $e->addColumn(
                    PatientFinderColumn::enriched('ihi', 'IHI')
                ),
        ]);

        $registry = (new PatientFinderColumnRegistryFactory(
            $dispatcher,
            $fetch,
            patientDataFields: self::fields(...)
        ))->create();

        $this->assertSame(['name', 'ihi', 'DOB'], $registry->keys());
        $ihi = $registry->get('ihi');
        $this->assertNotNull($ihi);
        $this->assertTrue($ihi->isEnriched());
        $this->assertSame('9rem', $ihi->width, 'the list entry sets the module column width');
    }

    public function testSeedsColumnsWithSortDirectionAndRunsEvent(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [
                ['option_id' => 'name', 'title' => 'Full Name', 'toggle_setting_1' => '0'],
                ['option_id' => 'DOB', 'title' => 'DOB', 'toggle_setting_1' => '1'],
            ]
            : [];

        $listMap = static fn(string $listId): array => $listId === 'Sort_Direction'
            ? ['0' => 'asc', '1' => 'desc']
            : [];

        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static fn(PatientFinderColumnRegistrationEvent $e) => $e->addColumn(
                    PatientFinderColumn::enriched('ihi', 'IHI')
                ),
        ]);

        $registry = (new PatientFinderColumnRegistryFactory($dispatcher, $fetch, $listMap, patientDataFields: self::fields(...)))->create();

        $this->assertSame(['name', 'DOB', 'ihi'], $registry->keys());
        $this->assertSame('asc', $registry->get('name')?->defaultSortDir);
        $this->assertSame('desc', $registry->get('DOB')?->defaultSortDir);
    }

    public function testBlankToggleDefaultsToAscWithoutConsultingList(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [['option_id' => 'sex', 'title' => 'Sex', 'toggle_setting_1' => '']]
            : [];

        // If the resolver were consulted for a blank toggle it would throw here.
        $listMap = static function (string $listId): array {
            throw new \RuntimeException('should not resolve the list for a blank toggle');
        };

        $registry = (new PatientFinderColumnRegistryFactory($this->dispatcher(), $fetch, $listMap, patientDataFields: self::fields(...)))->create();

        $this->assertSame('asc', $registry->get('sex')?->defaultSortDir);
    }

    public function testSeedsTheRequestedListAndTellsListenersWhichOne(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('recent_patient_columns', $binds, true)
            ? [['option_id' => 'fname', 'title' => 'First Name', 'toggle_setting_1' => '']]
            : [['option_id' => 'DOB', 'title' => 'DOB', 'toggle_setting_1' => '']];
        $seen = [];
        $dispatcher = $this->dispatcher([
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE =>
                static function (PatientFinderColumnRegistrationEvent $e) use (&$seen): void {
                    $seen[] = $e->getColumnList();
                },
        ]);
        $factory = new PatientFinderColumnRegistryFactory($dispatcher, $fetch, patientDataFields: self::fields(...));

        $this->assertSame(['fname'], $factory->create(PatientFinderColumnList::Recent)->keys());
        $this->assertSame(['DOB'], $factory->create()->keys());
        $this->assertSame([PatientFinderColumnList::Recent, PatientFinderColumnList::PatientList], $seen);
    }
}
