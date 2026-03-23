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

namespace OpenEMR\Validators\Acl;

use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\Checker\AclGroupIdChecker;
use OpenEMR\Validators\Checker\UserUuidChecker;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

class AclGroupMemberValidator extends BaseValidator
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclGroupIdChecker::getInstance(),
            UserUuidChecker::getInstance(),
        );
    }

    public function __construct(
        private readonly AclGroupIdChecker $aclGroupIdChecker,
        private readonly UserUuidChecker $userUuidChecker,
    ) {
        parent::__construct();
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $validator
            ->required('group_id', 'Group ID')
            ->integer()
            ->callback(fn (int $id): bool => $this->aclGroupIdChecker->isAclGroupIdExists($id) ?: throw new InvalidValueException(
                sprintf('Group with ID %s does not exists.', $id),
                'Group::ID',
            ))
        ;

        $validator
            ->required('uuid', 'User UUID')
            ->uuid()
            ->callback(fn (string $uuid): bool => $this->userUuidChecker->isUserUuidExists($uuid) ?: throw new InvalidValueException(
                sprintf('User with UUID %s does not exists.', $uuid),
                'User::UUID',
            ))
        ;

        $validator
            ->optional('order', 'Member sort order')
            ->integer()
        ;

        $validator
            ->optional('hidden', 'Is member hidden?')
            ->bool()
        ;
    }
}
