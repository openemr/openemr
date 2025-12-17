<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\Admin\Acl;

use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclGroupSettingRestController;
use OpenEMR\Tests\Api\Standard\TestClientAwareApiTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-admin-acl')]
#[CoversClass(AdminAclGroupSettingRestController::class)]
#[CoversMethod(AdminAclGroupSettingRestController::class, 'getAll')]
class AdminAclGroupSettingApiTest extends TestCase
{
    use TestClientAwareApiTestTrait;

    /**
     * @see AdminAclGroupSettingRestController::getAll()
     */
    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/admin/acl/group/setting');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertGreaterThan(0, count($json['data']));

        foreach ($json['data'] as $searchResult) {
            $this->assertArrayHasKey('group_id', $searchResult);
            $this->assertIsNumeric($searchResult['group_id']);

            $this->assertArrayHasKey('section_id', $searchResult);
            $this->assertIsNumeric($searchResult['section_id']);

            $this->assertArrayHasKey('allowed', $searchResult);
            $this->assertIsNumeric($searchResult['allowed']);
        }
    }
}
