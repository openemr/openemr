<?php

/**
 * Isolated ScopePermissionObject Test
 *
 * Tests OAuth2 scope permission parsing and management.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopePermissionObject;
use PHPUnit\Framework\TestCase;

class ScopePermissionObjectTest extends TestCase
{
    public function testConstructorWithNoIdentifier(): void
    {
        $obj = new ScopePermissionObject();
        $this->assertFalse($obj->read);
        $this->assertFalse($obj->create);
        $this->assertFalse($obj->update);
        $this->assertFalse($obj->delete);
        $this->assertFalse($obj->search);
        $this->assertFalse($obj->v1Read);
        $this->assertFalse($obj->v1Write);
        $this->assertSame([], $obj->getConstraints());
    }

    public function testConstructorWithIdentifier(): void
    {
        $obj = new ScopePermissionObject('test-identifier');
        $this->assertSame('test-identifier', $obj->getIdentifier());
    }

    public function testCreateFromStringWithRead(): void
    {
        $obj = ScopePermissionObject::createFromString('read');
        $this->assertTrue($obj->read);
        $this->assertTrue($obj->search);
        $this->assertTrue($obj->v1Read);
        $this->assertFalse($obj->create);
        $this->assertFalse($obj->update);
        $this->assertFalse($obj->delete);
        $this->assertFalse($obj->v1Write);
    }

    public function testCreateFromStringWithWrite(): void
    {
        $obj = ScopePermissionObject::createFromString('write');
        $this->assertTrue($obj->create);
        $this->assertTrue($obj->update);
        $this->assertTrue($obj->delete);
        $this->assertTrue($obj->v1Write);
        $this->assertFalse($obj->read);
        $this->assertFalse($obj->search);
        $this->assertFalse($obj->v1Read);
    }

    public function testCreateFromStringWithCrudPermissions(): void
    {
        $obj = ScopePermissionObject::createFromString('cruds');
        $this->assertTrue($obj->create);
        $this->assertTrue($obj->read);
        $this->assertTrue($obj->update);
        $this->assertTrue($obj->delete);
        $this->assertTrue($obj->search);
        $this->assertTrue($obj->v1Read);
        $this->assertTrue($obj->v1Write);
    }

    public function testCreateFromStringWithPartialCrud(): void
    {
        $obj = ScopePermissionObject::createFromString('cr');
        $this->assertTrue($obj->create);
        $this->assertTrue($obj->read);
        $this->assertFalse($obj->update);
        $this->assertFalse($obj->delete);
        $this->assertFalse($obj->search);
        $this->assertFalse($obj->v1Read);
        $this->assertFalse($obj->v1Write);
    }

    public function testCreateFromStringWithReadSearch(): void
    {
        $obj = ScopePermissionObject::createFromString('rs');
        $this->assertTrue($obj->read);
        $this->assertTrue($obj->search);
        $this->assertTrue($obj->v1Read);
        $this->assertFalse($obj->create);
        $this->assertFalse($obj->update);
        $this->assertFalse($obj->delete);
    }

    public function testCreateFromStringWithConstraints(): void
    {
        $obj = ScopePermissionObject::createFromString('r?category=vital-signs');
        $this->assertTrue($obj->read);
        $constraints = $obj->getConstraints();
        $this->assertArrayHasKey('category', $constraints);
        $this->assertSame('vital-signs', $constraints['category']);
    }

    public function testCreateFromStringWithMultipleConstraints(): void
    {
        $obj = ScopePermissionObject::createFromString('rs?category=vital-signs&status=active');
        $this->assertTrue($obj->read);
        $this->assertTrue($obj->search);
        $constraints = $obj->getConstraints();
        $this->assertArrayHasKey('category', $constraints);
        $this->assertArrayHasKey('status', $constraints);
        $this->assertSame('vital-signs', $constraints['category']);
        $this->assertSame('active', $constraints['status']);
    }

    public function testCreateFromStringThrowsOnInvalidPermission(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid permission string: xyz');
        ScopePermissionObject::createFromString('xyz');
    }

    public function testCreateFromStringThrowsOnOutOfOrder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScopePermissionObject::createFromString('rc'); // out of order (should be 'cr')
    }

    public function testCreateFromStringThrowsOnDuplicates(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ScopePermissionObject::createFromString('rr');
    }

    public function testCreateFromStringWithEmptyString(): void
    {
        $obj = ScopePermissionObject::createFromString('');
        $this->assertFalse($obj->read);
        $this->assertFalse($obj->create);
        $this->assertFalse($obj->update);
        $this->assertFalse($obj->delete);
        $this->assertFalse($obj->search);
    }

    public function testIsOrderedCrudStringValid(): void
    {
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('c'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('r'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('u'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('d'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('s'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('cr'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('cru'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('crud'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('cruds'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('rs'));
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString('cud'));
    }

    public function testIsOrderedCrudStringInvalidCharacters(): void
    {
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('x'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('crx'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('123'));
    }

    public function testIsOrderedCrudStringOutOfOrder(): void
    {
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('rc'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('dc'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('sc'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('sdcru'));
    }

    public function testIsOrderedCrudStringDuplicates(): void
    {
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('cc'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('crr'));
        $this->assertFalse(ScopePermissionObject::isOrderedCrudString('crudds'));
    }

    public function testIsOrderedCrudStringEmptyString(): void
    {
        $this->assertTrue(ScopePermissionObject::isOrderedCrudString(''));
    }

    public function testGetPermissionsAsArray(): void
    {
        $obj = ScopePermissionObject::createFromString('cruds');
        $permissions = $obj->getPermissionsAsArray();

        $this->assertIsArray($permissions);
        $this->assertArrayHasKey('create', $permissions);
        $this->assertArrayHasKey('read', $permissions);
        $this->assertArrayHasKey('update', $permissions);
        $this->assertArrayHasKey('delete', $permissions);
        $this->assertArrayHasKey('search', $permissions);
        $this->assertArrayHasKey('v1Read', $permissions);
        $this->assertArrayHasKey('v1Write', $permissions);

        $this->assertTrue($permissions['create']);
        $this->assertTrue($permissions['read']);
        $this->assertTrue($permissions['update']);
        $this->assertTrue($permissions['delete']);
        $this->assertTrue($permissions['search']);
    }

    public function testAddConstraintsSingle(): void
    {
        $obj = new ScopePermissionObject();
        $obj->addConstraints(['category' => 'vital-signs']);

        $constraints = $obj->getConstraints();
        $this->assertSame('vital-signs', $constraints['category']);
    }

    public function testAddConstraintsMergesWithExisting(): void
    {
        $obj = new ScopePermissionObject();
        $obj->addConstraints(['category' => 'vital-signs']);
        $obj->addConstraints(['status' => 'active']);

        $constraints = $obj->getConstraints();
        $this->assertSame('vital-signs', $constraints['category']);
        $this->assertSame('active', $constraints['status']);
    }

    public function testAddConstraintsDuplicateKeyConvertsToArray(): void
    {
        $obj = new ScopePermissionObject();
        $obj->addConstraints(['category' => 'vital-signs']);
        $obj->addConstraints(['category' => 'laboratory']);

        $constraints = $obj->getConstraints();
        $this->assertIsArray($constraints['category']);
        $this->assertContains('vital-signs', $constraints['category']);
        $this->assertContains('laboratory', $constraints['category']);
    }

    public function testAddConstraintsMultipleDuplicates(): void
    {
        $obj = new ScopePermissionObject();
        $obj->addConstraints(['category' => 'vital-signs']);
        $obj->addConstraints(['category' => 'laboratory']);
        $obj->addConstraints(['category' => 'imaging']);

        $constraints = $obj->getConstraints();
        $this->assertIsArray($constraints['category']);
        $this->assertCount(3, $constraints['category']);
    }

    public function testV1WriteRequiresAllThreeCud(): void
    {
        // With only create and update, v1Write should be false
        $obj = ScopePermissionObject::createFromString('cu');
        $this->assertFalse($obj->v1Write);

        // With create, update, and delete, v1Write should be true
        $obj = ScopePermissionObject::createFromString('cud');
        $this->assertTrue($obj->v1Write);
    }

    public function testV1ReadRequiresReadAndSearch(): void
    {
        // With only read, v1Read should be false
        $obj = ScopePermissionObject::createFromString('r');
        $this->assertFalse($obj->v1Read);

        // With read and search, v1Read should be true
        $obj = ScopePermissionObject::createFromString('rs');
        $this->assertTrue($obj->v1Read);
    }
}
