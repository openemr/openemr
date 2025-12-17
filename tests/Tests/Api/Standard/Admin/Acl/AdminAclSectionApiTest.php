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

use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclSectionRestController;
use OpenEMR\Services\Acl\AclSectionService;
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
#[CoversClass(AdminAclSectionRestController::class)]
#[CoversMethod(AdminAclSectionRestController::class, 'getAll')]
class AdminAclSectionApiTest extends TestCase
{
    use TestClientAwareApiTestTrait;

    /**
     * @see AdminAclSectionRestController::getAll()
     * @see AclSectionService::getAll()
     */
    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/admin/acl/section');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertGreaterThan(0, count($json['data']));

        foreach ($json['data'] as $searchResult) {
            $this->assertArrayHasKey('parent_section', $searchResult);
            $this->assertIsNumeric($searchResult['parent_section']);

            $this->assertArrayHasKey('section_id', $searchResult);
            $this->assertIsNumeric($searchResult['section_id']);

            $this->assertArrayHasKey('section_name', $searchResult);
            $this->assertArrayHasKey('section_identifier', $searchResult);

            $this->assertArrayHasKey('module_id', $searchResult);
            $this->assertIsNumeric($searchResult['module_id']);
        }
    }
}
