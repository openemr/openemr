<?php

/*
 * ScopePermissionObjectTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopePermissionObject;
use PHPUnit\Framework\TestCase;

class ScopePermissionObjectTest extends TestCase
{
    public function test__constructNoParameter(): void
    {
        $scopePermissionObject = new ScopePermissionObject();
        $this->assertEmpty($scopePermissionObject->getIdentifier());
        $this->assertEmpty($scopePermissionObject->getConstraints());
        $this->assertEquals(false, $scopePermissionObject->read);
        $this->assertEquals(false, $scopePermissionObject->create);
        $this->assertEquals(false, $scopePermissionObject->update);
        $this->assertEquals(false, $scopePermissionObject->delete);
        $this->assertEquals(false, $scopePermissionObject->search);
        $this->assertEquals(false, $scopePermissionObject->v1Read);
        $this->assertEquals(false, $scopePermissionObject->v1Write);
    }

    public function testCreateFromStringWithEmptyValue(): void
    {
        $scopePermissionObject = ScopePermissionObject::createFromString('');
        $this->assertEmpty($scopePermissionObject->getIdentifier());
        $this->assertEmpty($scopePermissionObject->getConstraints());
        $this->assertEquals(false, $scopePermissionObject->read);
        $this->assertEquals(false, $scopePermissionObject->create);
        $this->assertEquals(false, $scopePermissionObject->update);
        $this->assertEquals(false, $scopePermissionObject->delete);
        $this->assertEquals(false, $scopePermissionObject->search);
        $this->assertEquals(false, $scopePermissionObject->v1Read);
        $this->assertEquals(false, $scopePermissionObject->v1Write);
    }

    public function testCreateFromStringWithCRUDSPermission(): void
    {
        $identifier = 'cruds';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertEquals($identifier, $scopePermissionObject->getIdentifier());
        $this->assertEmpty($scopePermissionObject->getConstraints());
        $this->assertEquals(true, $scopePermissionObject->read);
        $this->assertEquals(true, $scopePermissionObject->create);
        $this->assertEquals(true, $scopePermissionObject->update);
        $this->assertEquals(true, $scopePermissionObject->delete);
        $this->assertEquals(true, $scopePermissionObject->search);
        $this->assertEquals(true, $scopePermissionObject->v1Read);
        $this->assertEquals(true, $scopePermissionObject->v1Write);
    }

    public function testCreateFromStringWithValidPermissionString(): void
    {
        $validSubSets = [
            'c'
            , 'cr', 'cru', 'crud', 'cruds', 'cu', 'cud', 'cuds', 'cd', 'cds', 'cs'
            , 'r', 'ru', 'rud', 'ruds', 'rd', 'rds', 'rs'
            , 'u', 'ud', 'uds', 'd', 'ds'
            , 'd', 'ds'
            , 's'
        ];
        foreach ($validSubSets as $validSubset) {
            $scopePermissionObject = ScopePermissionObject::createFromString($validSubset);
            $this->assertEquals($validSubset, $scopePermissionObject->getIdentifier(), "Identifier should match for valid permission string: " . $validSubset);
            $this->assertEmpty($scopePermissionObject->getConstraints(), "Constraints should be empty for valid permission string: " . $validSubset);
            // Check permissions based on the valid subset
            $this->assertEquals(str_contains($validSubset, 'c'), $scopePermissionObject->create, "Create permission should match for valid permission string: " . $validSubset);
            $this->assertEquals(str_contains($validSubset, 'r'), $scopePermissionObject->read, "Read permission should match for valid permission string: " . $validSubset);
            $this->assertEquals(str_contains($validSubset, 'u'), $scopePermissionObject->update, "Update permission should match for valid permission string: " . $validSubset);
            $this->assertEquals(str_contains($validSubset, 'd'), $scopePermissionObject->delete, "Delete permission should match for valid permission string: " . $validSubset);
            $this->assertEquals(str_contains($validSubset, 's'), $scopePermissionObject->search, "Search permission should match for valid permission string: " . $validSubset);
            if (in_array($validSubset, ['cruds', 'cud', 'cuds', 'crud'])) {
                $this->assertEquals(true, $scopePermissionObject->v1Write, "v1Write permission should be true for valid permission string: " . $validSubset);
            } else {
                $this->assertEquals(false, $scopePermissionObject->v1Write, "v1Write permission should be false for valid permission string: " . $validSubset);
            }

            if (in_array($validSubset, ['cruds', 'ruds', 'rds', 'rs'])) {
                $this->assertEquals(true, $scopePermissionObject->v1Read, "v1Read permission should be true for valid permission string: " . $validSubset);
            } else {
                $this->assertEquals(false, $scopePermissionObject->v1Read, "v1Read permission should be false for valid permission string: " . $validSubset);
            }
        }
    }

    public function testCreateInvalidPermissionString(): void
    {
        $invalidSubSets = [
            'rc', 'rcu', 'rcud', 'rcuds', 'rdc', 'rdcu','rdcus', 'rsc', 'rscu', 'rscud', 'ruc', 'rucd', 'rucds'
            ,'cur', 'curd', 'curds', 'cudr', 'cudrs', 'cudrsc'
            ,'cc', 'rr', 'ss', 'dd', 'uu', 'csu'
            ,'uc', 'ur', 'usc', 'udc'
            ,'sc', 'sr', 'su', 'sd'
        ];
        foreach ($invalidSubSets as $invalidSubset) {
            try {
                $scopePermissionObject = ScopePermissionObject::createFromString($invalidSubset);
            } catch (\InvalidArgumentException $e) {
                $this->assertStringContainsString('Invalid permission string', $e->getMessage(), "Expected exception for invalid permission string: " . $invalidSubset);
                continue; // Continue to the next invalid subset
            }
        }
    }

    public function testCreateFromStringWithReadPermission(): void
    {
        $identifier = 'read';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertEquals($identifier, $scopePermissionObject->getIdentifier());
        $this->assertEmpty($scopePermissionObject->getConstraints());
        $this->assertEquals(true, $scopePermissionObject->read, "Read permission should be true for 'read' due to backwards compatibility");
        $this->assertEquals(false, $scopePermissionObject->create);
        $this->assertEquals(false, $scopePermissionObject->update);
        $this->assertEquals(false, $scopePermissionObject->delete);
        $this->assertEquals(true, $scopePermissionObject->search, "Search permission should be true for 'read' due to backwards compatibility");
        $this->assertEquals(true, $scopePermissionObject->v1Read, "v1Read permission should be true for identifier: " . $identifier);
        $this->assertEquals(false, $scopePermissionObject->v1Write);
    }

    public function testCreateFromStringWithWritePermission(): void
    {
        $identifier = 'write';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertEquals($identifier, $scopePermissionObject->getIdentifier());
        $this->assertEmpty($scopePermissionObject->getConstraints());
        $this->assertEquals(false, $scopePermissionObject->read);
        $this->assertEquals(true, $scopePermissionObject->create, "Create permission should be true for 'write' due to backwards compatibility");
        $this->assertEquals(true, $scopePermissionObject->update, "Update permission should be true for 'write' due to backwards compatibility");
        $this->assertEquals(true, $scopePermissionObject->delete, "Delete permission should be true for 'write' due to backwards compatibility");
        $this->assertEquals(false, $scopePermissionObject->search);
        $this->assertEquals(false, $scopePermissionObject->v1Read);
        $this->assertEquals(true, $scopePermissionObject->v1Write, "v1Write permission should be true for 'write' permission");
    }

    public function testGetConstraintsWithEmptyConstraints(): void
    {
        $identifier = 'rs';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertEmpty($scopePermissionObject->getConstraints(), 'Constraints should be empty for identifier: ' . $identifier);
    }

    public function testGetConstraintsWithPlainCategory(): void
    {
        $identifier = 'rs?category=bar';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar'], $scopePermissionObject->getConstraints(), 'Constraints should match for identifier: ' . $identifier);
    }
    public function testGetConstraintsWithSystemCategory(): void
    {
        $identifier = 'rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern'], $scopePermissionObject->getConstraints(), 'Constraints should match for identifier: ' . $identifier);
    }


    public function testAddConstraintsWithEmptyConstraints(): void
    {
        $identifier = 'rs';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertEmpty($scopePermissionObject->getConstraints(), 'Constraints should be empty for identifier: ' . $identifier);
        $scopePermissionObject->addConstraints([
            'category' => 'bar'
        ]);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty after adding constraints for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar'], $scopePermissionObject->getConstraints(), 'Constraints should match after adding constraints for identifier: ' . $identifier);

        $scopePermissionObject->addConstraints([
            'patient' => '1'
        ]);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty after adding constraints for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar', 'patient' => '1'], $scopePermissionObject->getConstraints(), 'Constraints should match after adding constraints for identifier: ' . $identifier);
    }

    public function testAddConstraintsWithExistingConstraints(): void
    {
        $identifier = 'rs?category=bar';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar'], $scopePermissionObject->getConstraints(), 'Constraints should match for identifier: ' . $identifier);

        $scopePermissionObject->addConstraints([
            'patient' => '1'
        ]);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty after adding constraints for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar', 'patient' => '1'], $scopePermissionObject->getConstraints(), 'Constraints should match after adding constraints for identifier: ' . $identifier);
    }

    public function testAddConstraintsWithDuplicateKey(): void
    {
        $identifier = 'rs?category=bar';
        $scopePermissionObject = ScopePermissionObject::createFromString($identifier);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty for identifier: ' . $identifier);
        $this->assertEquals(['category' => 'bar'], $scopePermissionObject->getConstraints(), 'Constraints should match for identifier: ' . $identifier);

        // Adding a constraint with the same key should overwrite the existing value
        $scopePermissionObject->addConstraints([
            'category' => 'baz'
        ]);
        $this->assertNotEmpty($scopePermissionObject->getConstraints(), 'Constraints should be not empty after adding constraints for identifier: ' . $identifier);
        $this->assertEquals(['category' => ['bar', 'baz']], $scopePermissionObject->getConstraints(), 'Constraints should match after adding constraints for identifier: ' . $identifier);
    }
}
