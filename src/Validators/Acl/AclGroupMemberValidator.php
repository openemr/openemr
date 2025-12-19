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
use OpenEMR\Services\Acl\AclGroupService;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\UserValidator;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

class AclGroupMemberValidator extends BaseValidator
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            UserValidator::getInstance(),
            AclGroupService::getInstance(),
        );
    }

    public function __construct(
        private readonly UserValidator $userValidator,
        private readonly AclGroupService $aclGroupService,
    ) {
        parent::__construct();
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $validator
            ->optional('group_id', 'Group ID')
            ->integer()
            ->callback(fn (string|int $id): bool => $this->aclGroupService->isIdValid((int) $id) ?: throw new InvalidValueException(
                sprintf('Group with ID %s does not exists.', $id),
                'group_id',
            ))
        ;

        $validator
            ->optional('user_id', 'User ID')
            ->integer()
            ->callback(fn (string|int $id): bool => $this->userValidator->isUserIdExists((int) $id) ?: throw new InvalidValueException(
                sprintf('User with ID %s does not exists.', $id),
                'user_id',
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
