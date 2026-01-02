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

namespace OpenEMR\Tests\Api\Standard\User\Setting;

use OpenEMR\RestControllers\Standard\User\Setting\UserSettingSectionRestController;
use OpenEMR\Tests\Api\Standard\TestClientAwareApiTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-user')]
#[Group('api-standard-user-setting')]
#[CoversClass(UserSettingSectionRestController::class)]
#[CoversMethod(UserSettingSectionRestController::class, 'getAll')]
class UserSettingSectionApiTest extends TestCase
{
    use TestClientAwareApiTestTrait;

    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/user/setting/section');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals([
            'appearance',
            'billing',
            'calendar',
            'carecoordination',
            'cdr',
            'connectors',
            'features',
            'locale',
            'questionnaires',
            'report',
        ], $json['data']);
    }
}
