<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://www.opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background;

use OpenEMR\Services\Background\BackgroundServiceDefinition;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('background-services')]
class BackgroundServiceDefinitionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'test_svc',
            title: 'Test Service',
            function: 'runTestService',
            requireOnce: '/path/to/file.php',
            executeInterval: 5,
            sortOrder: 50,
            active: true,
            running: false,
            nextRun: '2026-03-28 10:00:00',
        );

        $this->assertSame('test_svc', $def->name);
        $this->assertSame('Test Service', $def->title);
        $this->assertSame('runTestService', $def->function);
        $this->assertSame('/path/to/file.php', $def->requireOnce);
        $this->assertSame(5, $def->executeInterval);
        $this->assertSame(50, $def->sortOrder);
        $this->assertTrue($def->active);
        $this->assertFalse($def->running);
        $this->assertSame('2026-03-28 10:00:00', $def->nextRun);
    }

    public function testDefaultValues(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'minimal',
            title: 'Minimal',
            function: 'doWork',
        );

        $this->assertNull($def->requireOnce);
        $this->assertSame(0, $def->executeInterval);
        $this->assertSame(100, $def->sortOrder);
        $this->assertFalse($def->active);
        $this->assertFalse($def->running);
        $this->assertNull($def->nextRun);
    }

    public function testFromDatabaseRow(): void
    {
        // Use string values to match ADOdb runtime behavior (numeric-string)
        $row = [
            'name' => 'phimail',
            'title' => 'phiMail Direct Messaging',
            'function' => 'phimail_check',
            'require_once' => '/library/phimail.php',
            'execute_interval' => '5',
            'sort_order' => '100',
            'active' => '1',
            'running' => '0',
            'next_run' => '2026-03-28 10:15:00',
        ];

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertSame('phimail', $def->name);
        $this->assertSame('phiMail Direct Messaging', $def->title);
        $this->assertSame('phimail_check', $def->function);
        $this->assertSame('/library/phimail.php', $def->requireOnce);
        $this->assertSame(5, $def->executeInterval);
        $this->assertSame(100, $def->sortOrder);
        $this->assertTrue($def->active);
        $this->assertFalse($def->running);
        $this->assertSame('2026-03-28 10:15:00', $def->nextRun);
    }

    public function testFromDatabaseRowWithNullRequireOnce(): void
    {
        $row = [
            'name' => 'svc',
            'title' => 'Service',
            'function' => 'doWork',
            'require_once' => null,
            'execute_interval' => '10',
            'sort_order' => '200',
            'active' => '0',
            'running' => '0',
            'next_run' => '1970-01-01 00:00:00',
        ];

        $def = BackgroundServiceDefinition::fromDatabaseRow($row);

        $this->assertNull($def->requireOnce);
        $this->assertSame('1970-01-01 00:00:00', $def->nextRun);
        $this->assertFalse($def->active);
    }

    public function testToArray(): void
    {
        $def = new BackgroundServiceDefinition(
            name: 'test_svc',
            title: 'Test',
            function: 'runTest',
            requireOnce: '/path.php',
            executeInterval: 5,
            sortOrder: 50,
            active: true,
            running: false,
            nextRun: '2026-03-28 10:00:00',
        );

        $array = $def->toArray();

        $this->assertSame('test_svc', $array['name']);
        $this->assertSame('Test', $array['title']);
        $this->assertSame('runTest', $array['function']);
        $this->assertSame('/path.php', $array['require_once']);
        $this->assertSame('5', $array['execute_interval']);
        $this->assertSame('50', $array['sort_order']);
        $this->assertSame('1', $array['active']);
        $this->assertSame('0', $array['running']);
    }

    public function testRoundTrip(): void
    {
        $original = new BackgroundServiceDefinition(
            name: 'roundtrip',
            title: 'Roundtrip Test',
            function: 'doRoundtrip',
            requireOnce: '/lib/roundtrip.php',
            executeInterval: 15,
            sortOrder: 75,
            active: true,
            running: true,
            nextRun: '2026-03-28 12:00:00',
        );

        $restored = BackgroundServiceDefinition::fromDatabaseRow($original->toArray());

        $this->assertSame($original->name, $restored->name);
        $this->assertSame($original->title, $restored->title);
        $this->assertSame($original->function, $restored->function);
        $this->assertSame($original->requireOnce, $restored->requireOnce);
        $this->assertSame($original->executeInterval, $restored->executeInterval);
        $this->assertSame($original->sortOrder, $restored->sortOrder);
        $this->assertSame($original->active, $restored->active);
        $this->assertSame($original->running, $restored->running);
        $this->assertSame($original->nextRun, $restored->nextRun);
    }
}
