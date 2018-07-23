<?php
/**
 * @see       https://github.com/zendframework/zend-permissions-acl for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-permissions-acl/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Permissions\Acl;

/**
 * Applicable to Resources and Roles.
 *
 * Provides information about the owner of some object. Used in conjunction
 * with the Ownership assertion.
 */
interface ProprietaryInterface
{
    /**
     * @return mixed
     */
    public function getOwnerId();
}
