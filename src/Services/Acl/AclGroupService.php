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

namespace OpenEMR\Services\Acl;

use OpenEMR\Gacl\GaclApi;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @phpstan-type TAclGroup = array{
 *     id: int,
 *     parent_id: int,
 *     name: string,
 *     value: string
 * }
 */
class AclGroupService
{
    private readonly GaclApi $acl;

    public function __construct()
    {
        $this->acl = new GaclApi();
    }

    /**
     * @phpstan-return array<TAclGroup>
     */
    private function normalize(array $data): array
    {
        [$groupId, $parentGroupId, $groupValue, $groupName, $lft, $rgt] = $data;

        return [
            'id' => (int) $groupId,
            'parent_id' => (int) $parentGroupId,
            'value' => $groupValue,
            'name' => $groupName,
        ];
    }

    /**
     * @phpstan-return array<TAclGroup>
     * @throws InvalidArgumentException
     */
    public function insert(array $data): array
    {
        Assert::keyExists($data, 'name');
        Assert::keyExists($data, 'value');
        Assert::true(
            $this->isGroupValueValid($data['value']),
            sprintf('Value %s is not valid', $data['value'])
        );
        Assert::false(
            $this->isGroupValueTaken($data['value']),
            sprintf('Group with value %s already exists', $data['value'])
        );

        $parentId = (int) ($data['parent_id'] ?? $this->acl->get_root_group_id());
        Assert::true(
            $this->isParentIdValid($parentId),
            sprintf('Parent Group with ID %s does not exists', $parentId)
        );

        $groupId = $this->acl->add_group(
            $data['value'],
            $data['name'],
            $parentId
        );

        Assert::notFalse($groupId, 'Unknown error during ACL Group creation');

        return [
            'id' => $groupId,
            'parent_id' => $parentId,
            'value' => $data['value'],
            'name' => $data['name'],
        ];
    }

    /**
     * @phpstan-return array<TAclGroup>
     */
    public function getAll(): array
    {
        $rootId = $this->acl->get_root_group_id();
        return array_map(
            fn ($childGroupId): array => $this->normalize(
                $this->acl->get_group_data($childGroupId)
            ),
            array_merge(
                [$rootId], // Root group
                $this->acl->get_group_children($rootId, 'ARO', 'RECURSE'), // And its child
            )
        );
    }

    /**
     * @phpstan-return TAclGroup|null
     * @throws InvalidArgumentException
     */
    public function getOneById(int $id): ?array
    {
        $group = $this->acl->get_group_data($id);
        if (false === $group) {
            return null;
        }

        return $this->normalize($group);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteById(int $id): void
    {
        if (null === $this->getOneById($id)) {
            // Already does not exist
            return;
        }

        $childrenIds = $this->acl->get_group_children($id);
        foreach ($childrenIds as $childId) {
            $this->deleteById($childId);
        }

        $objects = $this->acl->get_group_objects($id);
        foreach ($objects as $sectionValue => $objectValues) {
            foreach ($objectValues as $objectValue) {
                $this->acl->del_group_object($id, $sectionValue, $objectValue);
            }
        }

        Assert::true(
            $this->acl->del_group($id, false),
            'Unknown error during ACL Group deletion'
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteByValue(string $value): void
    {
        // We should pass '' as a name argument to prevent warning:
        // trim(): Passing null to parameter #1 ($string) of type string is deprecated
        $groupId = $this->acl->get_group_id($value, '');
        if (false === $groupId) {
            return;
        }

        $this->deleteById($groupId);
    }

    public function isGroupValueTaken(string $value): bool
    {
        // We should pass '' as a name argument to prevent warning:
        // trim(): Passing null to parameter #1 ($string) of type string is deprecated
        return false !== $this->acl->get_group_id($value, '');
    }

    public function isGroupValueValid(string $value): bool
    {
        return 1 === preg_match('/^[a-z]+$/', $value);
    }

    public function isIdValid(int $id): bool
    {
        return null !== $this->getOneById($id);
    }

    public function isParentIdValid(int $parentId): bool
    {
        return $this->isIdValid($parentId);
    }
}
