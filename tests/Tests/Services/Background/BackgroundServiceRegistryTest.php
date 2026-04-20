<?php

/**
 * Integration tests for BackgroundServiceRegistry.
 *
 * These tests run against a live database and exercise the full
 * register/unregister/get/list/setActive/exists lifecycle.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Aaron Marcelo <prot52486@gmail.com>
 * @copyright Copyright (c) 2026 Aaron Marcelo
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Background;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('background-services')]
class BackgroundServiceRegistryTest extends TestCase
{
    private const TEST_PREFIX = '_e2e_bsr_';

    private BackgroundServiceRegistry $registry;

    /** @var list<string> names of services created during the test — read in tearDown() */
    private array $createdServices = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new BackgroundServiceRegistry();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdServices as $name) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `background_services` WHERE `name` = ?',
                [$name],
                true,
            );
        }

        parent::tearDown();
    }

    public function testRegisterAndGet(): void
    {
        $def = $this->makeDefinition('register');
        $this->registry->register($def);

        $fetched = $this->registry->get($def->name);

        $this->assertNotNull($fetched);
        $this->assertSame($def->name, $fetched->name);
        $this->assertSame($def->title, $fetched->title);
        $this->assertSame($def->function, $fetched->function);
        $this->assertSame($def->requireOnce, $fetched->requireOnce);
        $this->assertSame($def->executeInterval, $fetched->executeInterval);
        $this->assertSame($def->sortOrder, $fetched->sortOrder);
        $this->assertSame($def->active, $fetched->active);
    }

    public function testRegisterUpsertUpdatesFieldsButPreservesActive(): void
    {
        $original = $this->makeDefinition('upsert', active: true);
        $this->registry->register($original);

        // Re-register with different title and active=false.
        // The upsert should update title but NOT overwrite active.
        $updated = new BackgroundServiceDefinition(
            name: $original->name,
            title: 'Updated Title',
            function: 'updatedFunction',
            requireOnce: '/updated/path.php',
            executeInterval: 99,
            sortOrder: 200,
            active: false,
        );
        $this->registry->register($updated);

        $fetched = $this->registry->get($original->name);

        $this->assertNotNull($fetched);
        $this->assertSame('Updated Title', $fetched->title);
        $this->assertSame('updatedFunction', $fetched->function);
        $this->assertSame('/updated/path.php', $fetched->requireOnce);
        $this->assertSame(99, $fetched->executeInterval);
        $this->assertSame(200, $fetched->sortOrder);
        // active should remain true (admin decision preserved)
        $this->assertTrue($fetched->active);
    }

    public function testRegisterRespectsActiveOnFirstInsertWhenTrue(): void
    {
        // First install wins: a definition shipping active=true is honored
        // on the initial INSERT, so a module can enable its service by default.
        $def = $this->makeDefinition('first_insert_true', active: true);
        $this->registry->register($def);

        $fetched = $this->registry->get($def->name);
        $this->assertNotNull($fetched);
        $this->assertTrue($fetched->active);
    }

    public function testRegisterUpsertPreservesActiveFalseWhenReRegisteredTrue(): void
    {
        // Inverse of testRegisterUpsertUpdatesFieldsButPreservesActive:
        // a service that an admin has disabled stays disabled even if the
        // module later re-registers with active=true. Runtime state always
        // wins over package defaults.
        $original = $this->makeDefinition('upsert_false_preserved', active: false);
        $this->registry->register($original);

        $reRegistered = new BackgroundServiceDefinition(
            name: $original->name,
            title: $original->title,
            function: $original->function,
            requireOnce: $original->requireOnce,
            executeInterval: $original->executeInterval,
            sortOrder: $original->sortOrder,
            active: true,
        );
        $this->registry->register($reRegistered);

        $fetched = $this->registry->get($original->name);
        $this->assertNotNull($fetched);
        $this->assertFalse($fetched->active);
    }

    public function testUnregisterRemovesService(): void
    {
        $def = $this->makeDefinition('unregister');
        $this->registry->register($def);
        $this->assertTrue($this->registry->exists($def->name));

        $this->registry->unregister($def->name);

        $this->assertFalse($this->registry->exists($def->name));
        $this->assertNull($this->registry->get($def->name));
    }

    public function testUnregisterNonexistentIsNoop(): void
    {
        // Should not throw
        $this->registry->unregister(self::TEST_PREFIX . 'nonexistent');
        $this->assertFalse($this->registry->exists(self::TEST_PREFIX . 'nonexistent'));
    }

    public function testGetReturnsNullForMissingService(): void
    {
        $this->assertNull($this->registry->get(self::TEST_PREFIX . 'missing'));
    }

    public function testExistsReturnsTrueForRegisteredService(): void
    {
        $def = $this->makeDefinition('exists');
        $this->registry->register($def);

        $this->assertTrue($this->registry->exists($def->name));
    }

    public function testExistsReturnsFalseForUnknownService(): void
    {
        $this->assertFalse($this->registry->exists(self::TEST_PREFIX . 'unknown'));
    }

    public function testSetActiveTogglesActiveFlag(): void
    {
        $def = $this->makeDefinition('active', active: false);
        $this->registry->register($def);

        $fetched = $this->registry->get($def->name);
        $this->assertNotNull($fetched);
        $this->assertFalse($fetched->active);

        $this->registry->setActive($def->name, true);
        $fetchedAfterEnable = $this->registry->get($def->name);
        $this->assertNotNull($fetchedAfterEnable);
        $this->assertTrue($fetchedAfterEnable->active);

        $this->registry->setActive($def->name, false);
        $fetchedAfterDisable = $this->registry->get($def->name);
        $this->assertNotNull($fetchedAfterDisable);
        $this->assertFalse($fetchedAfterDisable->active);
    }

    public function testListReturnsRegisteredServices(): void
    {
        $a = $this->makeDefinition('list_a', sortOrder: 1);
        $b = $this->makeDefinition('list_b', sortOrder: 2);
        $this->registry->register($a);
        $this->registry->register($b);

        $all = $this->registry->list();
        $names = array_map(fn(BackgroundServiceDefinition $d) => $d->name, $all);

        $this->assertContains($a->name, $names);
        $this->assertContains($b->name, $names);
    }

    public function testListFiltersByActiveStatus(): void
    {
        $active = $this->makeDefinition('filter_active', active: true);
        $inactive = $this->makeDefinition('filter_inactive', active: false);
        $this->registry->register($active);
        $this->registry->register($inactive);

        $activeList = $this->registry->list(activeFilter: true);
        $activeNames = array_map(fn(BackgroundServiceDefinition $d) => $d->name, $activeList);

        $inactiveList = $this->registry->list(activeFilter: false);
        $inactiveNames = array_map(fn(BackgroundServiceDefinition $d) => $d->name, $inactiveList);

        $this->assertContains($active->name, $activeNames);
        $this->assertNotContains($inactive->name, $activeNames);

        $this->assertContains($inactive->name, $inactiveNames);
        $this->assertNotContains($active->name, $inactiveNames);
    }

    public function testListRespectsNullFilterReturnsAll(): void
    {
        $def = $this->makeDefinition('filter_null');
        $this->registry->register($def);

        $all = $this->registry->list(activeFilter: null);
        $names = array_map(fn(BackgroundServiceDefinition $d) => $d->name, $all);

        $this->assertContains($def->name, $names);
    }

    public function testListOrdersBySortOrder(): void
    {
        $high = $this->makeDefinition('sort_high', sortOrder: 9999);
        $low = $this->makeDefinition('sort_low', sortOrder: 1);
        $this->registry->register($high);
        $this->registry->register($low);

        $all = $this->registry->list();
        $names = array_map(fn(BackgroundServiceDefinition $d) => $d->name, $all);

        $lowIdx = array_search($low->name, $names, true);
        $highIdx = array_search($high->name, $names, true);

        $this->assertIsInt($lowIdx);
        $this->assertIsInt($highIdx);
        $this->assertLessThan($highIdx, $lowIdx);
    }

    private function makeDefinition(
        string $suffix,
        bool $active = false,
        int $sortOrder = 100,
    ): BackgroundServiceDefinition {
        $name = self::TEST_PREFIX . $suffix;
        $this->createdServices[] = $name;

        return new BackgroundServiceDefinition(
            name: $name,
            title: "Test: {$suffix}",
            function: "test_{$suffix}_fn",
            requireOnce: null,
            executeInterval: 5,
            sortOrder: $sortOrder,
            active: $active,
        );
    }
}
