<?php

/**
 * AclMainTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * Handles unit tests of the AclMain class
 *
 * @package OpenEMR\RestControllers\SMART
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Acl;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;

class AclMainTest extends TestCase
{
    /**
     * Unit test to explore the ACLs and verify the checks are working properly.
     */
    public function testAclCheckCore()
    {
        // make sure we've cleared all GACL caches here...
        AclMain::clearGaclCache();

        // we assume in our unit tests that our admin user will have access to certain parts of the database
        $adminUsername = getenv("OE_USER", true) ?: "admin";
        $userService = new UserService();

        $admin = $userService->getUserByUsername($adminUsername);
        $this->assertNotEmpty($admin, "Admin user should be in database for unit tests to execute");

        $accessToPatientDemo = AclMain::aclCheckCore('patients', 'demo', $adminUsername);
        $this->assertTrue($accessToPatientDemo, "Admin has access to view patient list");

        $isSuperUser = AclMain::aclCheckCore('admin', 'users', $adminUsername);
        $this->assertTrue($isSuperUser, "Has access to admin section");

        // TODO: we need to write a WHOLE lot more ACL tests here.
    }
}
