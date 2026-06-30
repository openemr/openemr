<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Database;

use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use OpenEMR\Common\Database\ConnectionManager;
use OpenEMR\Common\Database\ConnectionType;
use PHPUnit\Framework\TestCase;

class ConnectionManagerTest extends TestCase
{
    public function testGetReturnsConnectionFromFactory(): void
    {
        $manager = new ConnectionManager();
        $mockConn = $this->createMock(Connection::class);

        $manager->register(ConnectionType::Main, fn() => $mockConn);

        $result = $manager->get(ConnectionType::Main);

        self::assertSame($mockConn, $result);
    }

    public function testGetReturnsSameInstanceOnSubsequentCalls(): void
    {
        $manager = new ConnectionManager();
        $callCount = 0;
        $mockConn = $this->createMock(Connection::class);

        $manager->register(ConnectionType::Main, function () use ($mockConn, &$callCount) {
            $callCount++;
            return $mockConn;
        });

        $first = $manager->get(ConnectionType::Main);
        $second = $manager->get(ConnectionType::Main);

        self::assertSame($first, $second);
        self::assertSame(1, $callCount, 'Factory should only be called once');
    }

    public function testGetThrowsWhenNoFactoryRegistered(): void
    {
        $manager = new ConnectionManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No factory registered for connection type "Main"');

        $manager->get(ConnectionType::Main);
    }

    public function testDifferentTypesGetDifferentConnections(): void
    {
        $manager = new ConnectionManager();
        $mainConn = $this->createMock(Connection::class);
        $auditConn = $this->createMock(Connection::class);

        $manager->register(ConnectionType::Main, fn() => $mainConn);
        $manager->register(ConnectionType::NonAudited, fn() => $auditConn);

        self::assertSame($mainConn, $manager->get(ConnectionType::Main));
        self::assertSame($auditConn, $manager->get(ConnectionType::NonAudited));
        self::assertNotSame(
            $manager->get(ConnectionType::Main),
            $manager->get(ConnectionType::NonAudited)
        );
    }

    public function testFactoryCanReferenceOtherConnections(): void
    {
        $manager = new ConnectionManager();
        $auditConn = $this->createMock(Connection::class);
        $mainConn = $this->createMock(Connection::class);
        $auditWasFetched = false;

        $manager->register(ConnectionType::NonAudited, fn() => $auditConn);
        $manager->register(ConnectionType::Main, function () use ($manager, $mainConn, &$auditWasFetched) {
            // Simulate middleware setup that needs the audit connection
            $manager->get(ConnectionType::NonAudited);
            $auditWasFetched = true;
            return $mainConn;
        });

        // Getting main should work and internally fetch audit
        $result = $manager->get(ConnectionType::Main);
        self::assertSame($mainConn, $result);
        self::assertTrue($auditWasFetched);
    }
}
