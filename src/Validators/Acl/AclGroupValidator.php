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
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

class AclGroupValidator extends BaseValidator
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclGroupService::getInstance(),
        );
    }

    public function __construct(
        private readonly AclGroupService $groupService,
    ) {
        parent::__construct();
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $validator
            ->optional('parent_id', 'Parent Group ID')
            ->integer()
            ->callback(fn (string|int $parentId): bool => $this->groupService->isParentIdValid((int) $parentId) ?: throw new InvalidValueException(
                sprintf('Parent Group with ID %s does not exists.', $parentId),
                'parent_id',
            ))
        ;

        $validator
            ->required('value', 'Group Value')
            ->string()
            ->lengthBetween(3, 150)
            ->callback(fn (string $value): bool => $this->groupService->isGroupValueValid($value) ?: throw new InvalidValueException(
                sprintf('Value %s is not valid. Only lowercase letters (a-z) allowed.', $value),
                'value',
            ))
            ->callback(fn (string $value): bool => !$this->groupService->isGroupValueTaken($value) ?: throw new InvalidValueException(
                sprintf('Value %s is taken', $value),
                'value',
            ))
        ;

        $validator
            ->required('name', 'Group Name')
            ->string()
            ->lengthBetween(3, 255)
        ;
    }
}
