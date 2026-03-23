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

use OpenEMR\Common\Database\Repository\User\UserRepository;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @see UserRepository::normalize()
 *
 * @mixin TestCase
 */
trait AssertValidUserAwareTrait
{
    protected function assertValidUser(array $data): void
    {
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);

        $this->assertArrayHasKey('uuid', $data);
        $this->assertTrue(
            Uuid::isValid($data['uuid']),
            sprintf(
                'Expected valid UUID string, got: %s',
                $data['uuid'],
            ),
        );

        $this->assertArrayHasKey('username', $data);
        $this->assertArrayHasKey('email', $data);

        $this->assertArrayHasKey('suffix', $data);
        $this->assertArrayHasKey('fname', $data);
        $this->assertArrayHasKey('mname', $data);
        $this->assertArrayHasKey('lname', $data);

        $this->assertArrayHasKey('specialty', $data);
        $this->assertArrayHasKey('organization', $data);

        $this->assertArrayHasKey('authorized', $data);
        $this->assertArrayHasKey('active', $data);
    }
}
