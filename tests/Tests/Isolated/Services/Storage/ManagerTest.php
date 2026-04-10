<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use OpenEMR\Services\Storage\Location;
use OpenEMR\Services\Storage\Manager;
use OpenEMR\Services\Storage\ManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Manager::class)]
final class ManagerTest extends TestCase
{
    public function testImplementsManagerInterface(): void
    {
        $manager = new Manager();

        self::assertInstanceOf(ManagerInterface::class, $manager);
    }

    public function testGetStorageReturnsRegisteredFilesystem(): void
    {
        $manager = new Manager();
        $fs = new Filesystem(new InMemoryFilesystemAdapter());
        $manager->register(Location::Documents, $fs);

        $result = $manager->getStorage(Location::Documents);

        self::assertSame($fs, $result);
    }

    public function testGetStorageThrowsWhenNotRegistered(): void
    {
        $manager = new Manager();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No filesystem registered for Documents');

        $manager->getStorage(Location::Documents);
    }
}
