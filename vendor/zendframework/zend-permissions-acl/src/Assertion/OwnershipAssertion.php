<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-acl for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-acl/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Permissions\Acl\Assertion;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\RoleInterface;
use Zend\Permissions\Acl\Resource\ResourceInterface;
use Zend\Permissions\Acl\ProprietaryInterface;

/**
 * Makes sure that some Resource is owned by certain Role.
 */
class OwnershipAssertion implements AssertionInterface
{
    public function assert(Acl $acl, RoleInterface $role = null, ResourceInterface $resource = null, $privilege = null)
    {
        //Assert passes if role or resource is not proprietary
        if (! $role instanceof ProprietaryInterface || ! $resource instanceof ProprietaryInterface) {
            return true;
        }

        //Assert passes if resources does not have an owner
        if ($resource->getOwnerId() === null) {
            return true;
        }

        return ($resource->getOwnerId() === $role->getOwnerId());
    }
}
