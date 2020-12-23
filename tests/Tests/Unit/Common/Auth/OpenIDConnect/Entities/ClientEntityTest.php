<?php

/**
 * Handles unit tests of the ClientEntity
 *
 * @package OpenEMR\RestControllers\SMART
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Auth\OpenIDConnect\Entities;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use PHPUnit\Framework\TestCase;

class ClientEntityTest extends TestCase
{
    /**
     * Checks to make sure the hasScope method is working properly
     */
    public function testHasScope()
    {
        $client = new ClientEntity();
        $client->setScopes('openid email phone address launch api:oemr api:fhir api:port api:pofh');

        $this->assertFalse($client->hasScope("bacon"), "invalid scope should not return true");
        $this->assertTrue($client->hasScope("launch"), "launch scope should have been found");
        $this->assertFalse($client->hasScope("launch/patient", "scope should not match against a prefix"));
    }
}
