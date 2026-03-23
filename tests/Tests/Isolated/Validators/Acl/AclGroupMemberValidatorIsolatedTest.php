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

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Acl;

use OpenEMR\Services\Acl\AclGroupService;
use OpenEMR\Tests\Common\AssertValidNamedArgumentsTrait;
use OpenEMR\Tests\Isolated\Validators\Checker\AclGroupIdCheckerAwareTestTrait;
use OpenEMR\Tests\Isolated\Validators\Checker\UserUuidCheckerAwareTestTrait;
use OpenEMR\Validators\Acl\AclGroupMemberValidator;
use OpenEMR\Validators\BaseValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('validator')]
#[Group('user-validator')]
#[CoversClass(AclGroupMemberValidator::class)]
#[CoversMethod(AclGroupMemberValidator::class, 'configureValidatorContext')]
class AclGroupMemberValidatorIsolatedTest extends TestCase
{
    use AssertValidNamedArgumentsTrait;
    use UserUuidCheckerAwareTestTrait;
    use AclGroupIdCheckerAwareTestTrait;

    #[Test]
    #[DataProvider('validateDataProvider')]
    public function validateValidationTest(
        array $data,
        array $expectedValidationErrors,
    ): void {
        $aclGroupService = $this->createMock(AclGroupService::class);
        $aclGroupService->method('isIdValid')->willReturnCallback(
            fn (int $id): bool => self::GROUP_ID_EXISTING === $id,
        );

        $userValidator = new AclGroupMemberValidator(
            $this->getAclGroupIdCheckerMock(),
            $this->getUserUuidCheckerMock(),
        );

        $result = $userValidator->validate($data, BaseValidator::DATABASE_INSERT_CONTEXT);
        $this->assertEquals($expectedValidationErrors, $result->getValidationMessages());
    }

    public static function validateDataProvider(): iterable
    {
        // Empty
        yield 'Empty data' => [[], [
            'group_id' => [
                'Required::NON_EXISTENT_KEY' => 'group_id must be provided, but does not exist',
            ],
            'uuid' => [
                'Required::NON_EXISTENT_KEY' => 'uuid must be provided, but does not exist',
            ],
        ]];

        // Invalid Group ID
        yield 'Invalid Group ID - Not an integer' => [[
            'group_id' => 'not-integer',
            'uuid' => self::UUID_EXISTING,
        ], [
            'group_id' => [
                'Integer::NOT_AN_INTEGER' => 'Group ID must be an integer',
            ],
        ]];

        yield 'Invalid Group ID - Not existing' => [[
            'group_id' => 1,
            'uuid' => self::UUID_EXISTING,
        ], [
            'group_id' => [
                'Group::ID' => 'Group with ID 1 does not exists.'
            ],
        ]];

        // Invalid UUID
        yield 'Invalid UUID - Integer' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => 1,
        ], [
            'uuid' => [
                'Uuid::INVALID_UUID' => 'User UUID must be a valid UUID (valid format)',
                'User::UUID' => 'User with UUID 1 does not exists.',
            ],
        ]];

        yield 'Invalid UUID - String' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => 'not-an-uuid',
        ], [
            'uuid' => [
                'Uuid::INVALID_UUID' => 'User UUID must be a valid UUID (valid format)',
                'User::UUID' => 'User with UUID not-an-uuid does not exists.',
            ],
        ]];

        yield 'Invalid UUID - Not existing' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
        ], [
            'uuid' => [
                'User::UUID' => 'User with UUID 550e8400-e29b-41d4-a716-446655440001 does not exists.',
            ],
        ]];

        // Invalid order
        yield 'Invalid order - Not an integer' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
            'order' => 'string',
        ], [
            'order' => [
                'Integer::NOT_AN_INTEGER' => 'Member sort order must be an integer',
            ],
        ]];

        // Invalid hidden
        yield 'Invalid order - Not a boolean' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
            'hidden' => 'string',
        ], [
            'hidden' => [
                'BOOL::NOT_BOOL' => 'Is member hidden? must be either true or false',
            ],
        ]];

        // Valid
        yield 'Valid - minimal' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
        ], []];

        yield 'Valid - With order' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
            'order' => 7,
        ], []];

        yield 'Valid - With hidden' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
            'hidden' => true,
        ], []];

        yield 'Valid - Full' => [[
            'group_id' => self::GROUP_ID_EXISTING,
            'uuid' => self::UUID_EXISTING,
            'order' => 7,
            'hidden' => false,
        ], []];
    }
}
