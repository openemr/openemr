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
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistryFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PatientFinderColumnRegistryFactoryTest extends TestCase
{
    /** @param array<string, callable> $listeners */
    private function dispatcher(array $listeners = []): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();
        foreach ($listeners as $eventName => $listener) {
            $dispatcher->addListener($eventName, $listener);
        }
        return $dispatcher;
    }

    public function testSeedsColumnsWithSortDirectionAndRunsEvent(): void
    {
        $fetch = static fn(string $sql, array $binds): array => str_contains($sql, 'ptlistcols')
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

        $registry = (new PatientFinderColumnRegistryFactory($dispatcher, $fetch, $listMap))->create();

        $this->assertSame(['name', 'DOB', 'ihi'], $registry->keys());
        $this->assertSame('asc', $registry->get('name')?->defaultSortDir);
        $this->assertSame('desc', $registry->get('DOB')?->defaultSortDir);
    }

    public function testBlankToggleDefaultsToAscWithoutConsultingList(): void
    {
        $fetch = static fn(string $sql, array $binds): array => str_contains($sql, 'ptlistcols')
            ? [['option_id' => 'sex', 'title' => 'Sex', 'toggle_setting_1' => '']]
            : [];

        // If the resolver were consulted for a blank toggle it would throw here.
        $listMap = static function (string $listId): array {
            throw new \RuntimeException('should not resolve the list for a blank toggle');
        };

        $registry = (new PatientFinderColumnRegistryFactory($this->dispatcher(), $fetch, $listMap))->create();

        $this->assertSame('asc', $registry->get('sex')?->defaultSortDir);
    }
}
