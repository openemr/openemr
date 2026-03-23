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

namespace OpenEMR\Tests\Api\Standard\User;

use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Allow to quickly get user data from /user/me endpoint
 *
 * @mixin TestCase
 */
trait MeAwareTrait
{
    private ApiTestClient $testClient;

    private function getMyUser(): array
    {
        $response = $this->testClient->request('GET', '/apis/default/api/user/me');
        Assert::eq(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        return $json['data'];
    }

    private function getMyUuid(): string
    {
        $uuidString = $this->getMyUser()['uuid'];
        Assert::true(
            Uuid::isValid($uuidString),
            sprintf('/user/me returned not valid UUID %s', $uuidString),
        );

        return $uuidString;
    }

}
