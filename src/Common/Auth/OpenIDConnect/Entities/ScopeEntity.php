<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
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
     * SMART launch contexts that legitimately use the colon delimiter and must
     * NOT be rejected (e.g. api:oemr, api:fhir, api:port, site:default). Every
     * other context (system, user, patient, or any future context) using the
     * colon delimiter is rejected at parse time so downstream substring
     * searches for 'system/' / 'user/' / 'patient/' cannot be bypassed by
     * submitting the colon variant. See rejectNonLegacyColonDelimiter().
     */
    private const COLON_LEGACY_CONTEXTS = ['api', 'site'];

    /**
     * @param string $scopeString
     * @throws \InvalidArgumentException if the scope string is invalid or if
     *         a non-legacy context uses the colon delimiter
     * @return ScopeEntity
     */
    public static function createFromString(string $scopeString): ScopeEntity
    {
        // Reject colon-form delimiter for permission-bearing contexts (system,
        // user, patient, and anything else outside COLON_LEGACY_CONTEXTS). The
        // parser regex accepts both '/' and ':' between context and resource,
        // which historically allowed 'system:Patient.read' to sneak past
        // literal-substring 'system/' searches. Runtime helpers on this class
        // (arrayHasContext / scopeListHasContext) already handle both spellings
        // correctly for their known call sites; this parse-time rejection adds
        // a model-layer defense so any future caller that reads getIdentifier()
        // or getScopeLookupKey() cannot receive a colon-form privileged scope.
        // Legacy colon-form contexts (api:oemr, api:fhir, api:port,
        // site:default) are allowed through unchanged.
        self::rejectNonLegacyColonDelimiter($scopeString);

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
            } elseif (!empty($operationOrPermission)) {
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

    /**
     * Throw when a scope string uses the colon delimiter for a context that is
     * NOT one of the SMART launch contexts allow-listed in
     * COLON_LEGACY_CONTEXTS. Preserves api:oemr / api:fhir / api:port /
     * site:default as-is. Scopes that do not contain a colon after their
     * leading context word are unaffected.
     *
     * @throws \InvalidArgumentException
     */
    private static function rejectNonLegacyColonDelimiter(string $scopeString): void
    {
        // Match only the leading '<context>:<resource>' pattern; anything past
        // the first colon that isn't a bare context+resource segment (e.g.
        // 'api:fhir?something', 'system/Patient.read.something') is left to
        // the main parser regex.
        if (!preg_match('/^([a-zA-Z_]+):[a-zA-Z0-9\*_\-]+/', $scopeString, $matches)) {
            return;
        }
        if (in_array($matches[1], self::COLON_LEGACY_CONTEXTS, true)) {
            return;
        }
        throw new \InvalidArgumentException(sprintf(
            "Scope '%s' uses the colon delimiter for context '%s'; use the slash form ('%s/...') instead.",
            $scopeString,
            $matches[1],
            $matches[1]
        ));
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

    /**
     * Determine whether any scope in the given array resolves to the given SMART
     * context ("system", "user", "patient", ...).
     *
     * Each scope is parsed into a canonical ScopeEntity and compared on its parsed
     * context rather than by substring-matching the raw registration string. This
     * ensures every equivalent spelling of a privileged scope (for example the
     * slash form "system/Patient.read" and the colon form "system:Patient.read")
     * is treated identically, so context-based authorization gates cannot be
     * bypassed by respelling the delimiter. Scope strings that cannot be parsed
     * are ignored for the purposes of context detection; they cannot be granted
     * (getScopeEntityByIdentifier() rejects them with the same parser), so they
     * cannot smuggle in a privileged context.
     *
     * @param string[] $scopes The requested scope strings
     * @param string $context The SMART context to look for (e.g. "system", "user")
     * @return bool
     */
    public static function arrayHasContext(array $scopes, string $context): bool
    {
        foreach ($scopes as $scope) {
            try {
                $parsed = self::createFromString((string) $scope);
            } catch (\InvalidArgumentException) {
                continue;
            }
            if ($parsed->getContext() === $context) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convenience wrapper for a raw, whitespace-delimited scope list string (as
     * received in an OAuth2 registration or authorization request). Splits the
     * list and delegates to arrayHasContext().
     *
     * @param string $scopeList Whitespace-delimited scope list
     * @param string $context The SMART context to look for
     * @return bool
     */
    public static function scopeListHasContext(string $scopeList, string $context): bool
    {
        $scopes = preg_split('/\s+/', trim($scopeList)) ?: [];
        return self::arrayHasContext(array_filter($scopes, static fn($s): bool => $s !== ''), $context);
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
        $containsScope = true;
        if ($otherPermissions->v1Read && !$this->permissions->v1Read) {
            $containsScope = false; // We don't have v1 read permission
        }
        if ($otherPermissions->v1Write && !$this->permissions->v1Write) {
            $containsScope = false; // We don't have v1 write permission
        }
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
