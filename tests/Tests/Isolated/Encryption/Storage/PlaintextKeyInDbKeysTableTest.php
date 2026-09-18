<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OpenEMR\Encryption\Storage\KeyMaterialId;
use OpenEMR\Encryption\Storage\PlaintextKeyInDbKeysTable;
use OutOfBoundsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class PlaintextKeyInDbKeysTableTest extends TestCase
{
    private Connection&MockObject $connection;
    private QueryBuilder&MockObject $queryBuilder;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);

        $this->queryBuilder->method('select')->willReturnSelf();
        $this->queryBuilder->method('from')->willReturnSelf();
        $this->queryBuilder->method('where')->willReturnSelf();
        $this->queryBuilder->method('setParameter')->willReturnSelf();
    }

    public function testGetKeyReturnsKeyMaterialWhenFound(): void
    {
        $rawKey = 'test_secret_key_________________';
        $encodedValue = base64_encode($rawKey);

        $this->queryBuilder->method('fetchOne')->willReturn($encodedValue);
        $this->connection->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $storage = new PlaintextKeyInDbKeysTable($this->connection);
        $result = $storage->getKey(new KeyMaterialId('test-key'));

        self::assertSame($rawKey, $result->key, 'Retrieved key should match original');
    }

    public function testGetKeyThrowsOutOfBoundsWhenNotFound(): void
    {
        $this->queryBuilder->method('fetchOne')->willReturn(false);
        $this->connection->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $storage = new PlaintextKeyInDbKeysTable($this->connection);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No key found');

        $storage->getKey(new KeyMaterialId('nonexistent-key'));
    }

    public function testGetKeyThrowsUnexpectedValueWhenResultIsNotString(): void
    {
        $this->queryBuilder->method('fetchOne')->willReturn(123);
        $this->connection->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $storage = new PlaintextKeyInDbKeysTable($this->connection);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Key found, invalid data format');

        $storage->getKey(new KeyMaterialId('bad-type-key'));
    }

    public function testGetKeyThrowsUnexpectedValueWhenBase64Invalid(): void
    {
        $this->queryBuilder->method('fetchOne')->willReturn('not-valid-base64!!!');
        $this->connection->method('createQueryBuilder')->willReturn($this->queryBuilder);

        $storage = new PlaintextKeyInDbKeysTable($this->connection);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Key found, malformed');

        $storage->getKey(new KeyMaterialId('malformed-key'));
    }

    public function testStoreKeyInsertsIntoDatabase(): void
    {
        $rawKey = 'test_secret_key_________________';
        $keyMaterial = new KeyMaterial($rawKey);
        $id = new KeyMaterialId('new-key');

        $this->connection->expects($this->once())
            ->method('insert')
            ->with(
                '`keys`',
                [
                    'name' => 'new-key',
                    'value' => base64_encode($rawKey),
                ],
            );

        $storage = new PlaintextKeyInDbKeysTable($this->connection);
        $storage->storeKey($id, $keyMaterial);
    }
}
