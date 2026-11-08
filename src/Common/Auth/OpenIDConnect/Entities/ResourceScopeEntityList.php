<?php

/*
 * ResourceScopeEntityList.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use ArrayObject;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ResourceScopeEntityList extends ArrayObject
{
    use EntityTrait;

    public function __construct(string $identifier, object|array $array = [], int $flags = 0, string $iteratorClass = "ArrayIterator")
    {
        $this->setIdentifier($identifier);
        foreach ($array as $item) {
            if (!$item instanceof ScopeEntity) {
                throw new \InvalidArgumentException('All items must be instances of ScopeEntityInterface');
            }
            if ($identifier !== $item->getScopeLookupKey()) {
                throw new \InvalidArgumentException('All items must have the same identifier as the list');
            }
        }
        parent::__construct($array, $flags, $iteratorClass);
    }

    public function append(mixed $item): void
    {
        $this->validateItem($item);
        parent::append($item);
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->validateItem($value);
        parent::offsetSet($key, $value);
    }

    private function validateItem(mixed $item): void
    {
        if (!$item instanceof ScopeEntity) {
            throw new \InvalidArgumentException('Item must be an instance of ScopeEntityInterface');
        }
        if ($this->getIdentifier() !== $item->getScopeLookupKey()) {
            throw new \InvalidArgumentException('Item must have the same identifier as the list');
        }
    }

    /**
     * Runtime (resource server) check: does any single item grant the candidate's permissions.
     * Constraints are ignored on purpose -- see ScopeEntity::containsScope().
     */
    public function containsScope(ScopeEntity $scope): bool
    {
        foreach ($this as $item) {
            /**
             * @var ScopeEntity $item
             */
            if ($item->containsScope($scope)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Grant-time check: may the candidate scope be issued given the scopes in this list
     * (server-supported, client-registered, or user-requested).
     *
     * Two rules the single-item containsScope() cannot express:
     *
     * 1. Union of permissions. Scopes are a set; `user/X.rs` + `user/X.cud` together grant
     *    `user/X.cruds`. v1 `.read`/`.write` are only spellings of `rs`/`cud`, so mixing
     *    v1 and v2 for the same resource is covered the same way.
     * 2. Constraints narrow, never widen. An unconstrained candidate is covered only by
     *    unconstrained items; a constrained candidate (`?category=...`) is covered by
     *    unconstrained items plus items with the identical constraint. A client registered
     *    for one Observation category can no longer be issued unrestricted Observation.
     *
     * 3. Constraints apply to reads only. The resource server narrows read and search results
     *    by constraint (ResourceConstraintFilterer) but does not check a written resource
     *    against one, so a constrained scope that also creates, updates or deletes would
     *    authorize writes outside its constraint. It is never granted.
     *
     * Operation scopes ($export, ...) and non-resource scopes (openid, launch, api:fhir, ...)
     * carry no CRUDS permissions to union, so they keep exact containment.
     */
    public function grantsScope(ScopeEntity $candidate): bool
    {
        if (!$candidate->isResourcePermissionScope()) {
            return $this->containsScope($candidate);
        }

        $wanted = $candidate->getPermissions();
        if ($candidate->hasConstraints() && ($wanted->create || $wanted->update || $wanted->delete)) {
            return false;
        }

        $create = $read = $update = $delete = $search = false;
        foreach ($this as $item) {
            /**
             * @var ScopeEntity $item
             */
            if (!$item->isResourcePermissionScope()) {
                continue;
            }
            if ($item->getContext() !== $candidate->getContext() || $item->getResource() !== $candidate->getResource()) {
                continue;
            }
            if ($item->hasConstraints() && !$item->hasSameConstraintsAs($candidate)) {
                continue;
            }
            $permissions = $item->getPermissions();
            $create = $create || $permissions->create;
            $read = $read || $permissions->read;
            $update = $update || $permissions->update;
            $delete = $delete || $permissions->delete;
            $search = $search || $permissions->search;
        }

        if (!($wanted->create || $wanted->read || $wanted->update || $wanted->delete || $wanted->search)) {
            // a resource scope with no permissions is not a grantable scope
            return false;
        }
        return (!$wanted->create || $create)
            && (!$wanted->read || $read)
            && (!$wanted->update || $update)
            && (!$wanted->delete || $delete)
            && (!$wanted->search || $search);
    }

    /**
     * Returns an array of scopes that contain the given scope.  This is useful for finding scopes that are more specific than the given scope such
     * as scopes that have a specific granular restriction.
     * For example contained scopes for user/Condition.r would include user/Condition.rs &&
     * user/Condition.rs?category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern
     * @param ScopeEntity $scope
     * @return array
     */
    public function getContainedScopes(ScopeEntity $scope): array
    {
        $containedScopes = [];
        foreach ($this as $item) {
            /**
             * @var ScopeEntity $item
             */
            if ($item->containsScope($scope)) {
                $containedScopes[] = $item;
            }
        }
        return $containedScopes;
    }
}
