<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\ScopeTrait;

class ScopeEntity implements ScopeEntityInterface
{
    use EntityTrait;
    use ScopeTrait;

    /**
     * @var string|null The operation associated with the scope such as $bulkdata-status $export, etc.
     */
    private ?string $operation;
    /**
     * @var "site"|"patient"|"user"|"system" The context of the scope, such as "site", "patient", "user", or "system"
     */
    private ?string $context;

    /**
     * @var string|null The resource being requested such as Patient or patient, vitals, or Observation, etc.
     */
    private ?string $resource;

    /**
     * @var ScopePermissionObject The permissions associated with the scope, such as read, write, create, update, delete, search, etc.
     */
    private ScopePermissionObject $permissions;

    public function __construct()
    {
        $this->permissions = new ScopePermissionObject();
        $this->operation = null;
        $this->context = null;
        $this->resource = null;
    }

    /**
     * @param string $scopeString
     * @throws \InvalidArgumentException if the scope string is invalid
     * @return ScopeEntity
     */
    public static function createFromString(string $scopeString): ScopeEntity
    {
        $scope = new self();
        $scope->setIdentifier($scopeString);

        // Parse the scope string to set permissions, operation, context, and resource
        // This is a placeholder for actual parsing logic
        // Example: "patient.read" would set operation to "read" and resource to "patient"
        // will also handle the site:default scope format
        $regex = "/^([a-zA-Z_]+)(?:[\/:]([a-zA-Z0-9\*_\-]+)(?:\.([^\?]+))?)?(?:\?(.*))?$/";
        $matches = [];
        if (preg_match($regex, $scopeString, $matches)) {
            // This is a permission scope
            $scope->context = $matches[1];
            $scope->resource = $matches[2] ?? null;
            $operationOrPermission = $matches[3] ?? '';
            if (str_contains($operationOrPermission, '$')) {
                $scope->operation = $operationOrPermission;
            } else if (!empty($operationOrPermission)) {
                $permissionString = $operationOrPermission;
                if (!empty($matches[4])) {
                    $permissionString .= "?" . $matches[4];
                }
                $scope->permissions = ScopePermissionObject::createFromString($permissionString);
            }
        } else {
            throw new \InvalidArgumentException("Invalid scope format: " . $scopeString);
        }

        return $scope;
    }

    public function getPermissions(): ScopePermissionObject
    {
        return $this->permissions;
    }

    public function getScopeLookupKey(): string
    {
        if (!empty($this->context)) {
            if (!empty($this->resource)) {
                return $this->context . '/' . $this->resource;
            }
            return $this->context; // if no resource, just return context
        } else {
            return $this->getIdentifier();
        }
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function addScopePermissions(ScopeEntity $otherScope)
    {
        if ($this->getScopeLookupKey() != $otherScope->getScopeLookupKey()) {
            throw new \InvalidArgumentException("Cannot add permissions from different scopes");
        }
        $otherPermissions = $otherScope->getPermissions();
        if ($otherPermissions->v1Read) {
            $this->permissions->v1Read = true;
        }
        if ($otherPermissions->v1Write) {
            $this->permissions->v1Write = true;
        }
        $this->permissions->create = $this->permissions->create || $otherPermissions->create;
        $this->permissions->read = $this->permissions->read || $otherPermissions->read;
        $this->permissions->update = $this->permissions->update || $otherPermissions->update;
        $this->permissions->delete = $this->permissions->delete || $otherPermissions->delete;
        $this->permissions->search = $this->permissions->search || $otherPermissions->search;
        $this->permissions->addConstraints($otherPermissions->getConstraints());
    }

    public function containsScope(ScopeEntity $otherScope): bool
    {
        if ($this->getIdentifier() === $otherScope->getIdentifier()) {
            return true; // Identical scopes
        }
        if ($this->getContext() !== $otherScope->getContext()) {
            return false; // Different contexts
        }
        if ($this->getResource() !== $otherScope->getResource()) {
            return false; // Different resources
        }
        if ($this->getOperation() !== $otherScope->getOperation()) {
            return false; // Different operations
        }
        // Check if permissions match
        $otherPermissions = $otherScope->getPermissions();
        if ($otherPermissions->v1Read) {
            return $otherPermissions->v1Read;
        }
        if ($otherPermissions->v1Write) {
            return $otherPermissions->v1Write;
        }
        $containsScope = true;
        if ($otherPermissions->read && !$this->permissions->read) {
            $containsScope = false; // We don't have read permission
        }
        if ($otherPermissions->create && !$this->permissions->create) {
            $containsScope = false; // We don't have create permission
        }
        if ($otherPermissions->update && !$this->permissions->update) {
            $containsScope = false; // We don't have update permission
        }
        if ($otherPermissions->delete && !$this->permissions->delete) {
            $containsScope = false; // We don't have delete permission
        }
        if ($otherPermissions->search && !$this->permissions->search) {
            $containsScope = false; // We don't have search permission
        }
        return $containsScope;
    }
}
