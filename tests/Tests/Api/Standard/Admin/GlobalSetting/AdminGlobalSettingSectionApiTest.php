<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\Admin\GlobalSetting;

use OpenEMR\RestControllers\Standard\Admin\GlobalSetting\AdminGlobalSettingSectionRestController;
use OpenEMR\Tests\Api\Standard\TestClientAwareApiTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-admin-global-setting')]
#[Group('api-standard-setting')]
#[CoversClass(AdminGlobalSettingSectionRestController::class)]
#[CoversMethod(AdminGlobalSettingSectionRestController::class, 'getAll')]
class AdminGlobalSettingSectionApiTest extends TestCase
{
    use TestClientAwareApiTestTrait;

    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/admin/global-setting/section');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals([
            "appearance",
            "billing",
            "branding",
            "calendar",
            "carecoordination",
            "cdr",
            "connectors",
            "documents",
            "e-sign",
            "encounter-form",
            "features",
            "insurance",
            "locale",
            "logging",
            "login-page",
            "miscellaneous",
            "notifications",
            "patient-banner-bar",
            "pdf",
            "portal",
            "questionnaires",
            "report",
            "rx",
            "security",
        ], $json['data']);
    }
}
