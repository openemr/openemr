<?php

/*
 * ResourceConstraintFilterer.php  Would like a better name for this but for now...
 * This class handles checking if a given FHIR resource can be accessed based on the constraints given in the
 * HttpRestRequest's access token scopes for the currently requested endpoint.
 *
 * It currently handles any constraint that maps to a getter on the resource that is a FHIRCodeableConcept, FHIRCoding, or FHIRCode
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;

class ResourceConstraintFilterer {

    use SystemLoggerAwareTrait;

    public function canAccessResource(FHIRDomainResource $resource, HttpRestRequest $request): bool {
        if (!$request->hasRequestRequiredScope()) {
            // The in-EHR local API is authorized by the user's session and ACL, not by an access
            // token, so there are no scope constraints to apply. Any other request without a
            // recorded scope never passed the scope check and sees nothing.
            return $request->isLocalApi();
        }
        $endpointScope = $request->getRequestRequiredScope();
        // TODO: @adunsulag we could move this all into the HttpRestRequest class... but it seems heavy, is there a better
        // class with more cohesion to put this logic into?
        $scopeEntities = $request->getAllContainedScopesForScopeEntity($endpointScope);
        if ($scopeEntities === []) {
            // no granting scope carries constraints for this endpoint; endpoint access itself is
            // decided earlier by the scope check in AuthorizationListener
            return true;
        }
        // Scopes are a union and each scope is evaluated on its own: the resource is visible when
        // at least one granting scope admits it. Constraints are never merged across scopes --
        // merging let category=A&clinicalStatus=active plus category=B&clinicalStatus=resolved
        // admit an A+resolved Condition that neither scope grants, and let a sibling category
        // scope narrow an unrestricted grant.
        foreach ($scopeEntities as $scopeEntity) {
            if ($scopeEntity instanceof ScopeEntity && $this->scopeAdmitsResource($scopeEntity, $resource)) {
                return true;
            }
        }
        return false;
    }

    /**
     * A scope admits a resource when it has no constraints, or when every one of its constraint
     * keys is satisfied (AND across keys, OR across the values of one key).
     */
    private function scopeAdmitsResource(ScopeEntity $scope, FHIRDomainResource $resource): bool
    {
        foreach ($scope->getPermissions()->getConstraints() as $key => $constraintValues) {
            // TODO: @adunsulag we should fix the getConstraints to make this an array always
            $constraintValues = is_array($constraintValues) ? $constraintValues : [$constraintValues];
            $resourceValue = $this->getResourceValueForKey($resource, $key);
            if (!$this->checkResourceValueWithConstraints($resource, $resourceValue, $constraintValues, $key)) {
                return false;
            }
        }
        return true;
    }

    public function getResourceValueForKey(FHIRDomainResource $resource, string $key)
    {
        // for now we just have to handle the getCategory case for Observation and Condition
        // but this allows us to expand this in the future
        if (method_exists($resource, 'get' . ucfirst($key))) {
            $getter = 'get' . ucfirst($key);
            return $resource->$getter();
        }
        // TODO: we could try to map some common keys to resource fields here
        return null;
    }

    /**
     * Only coded values (CodeableConcept, Coding, code) can be matched. Any other element type
     * (a string, a status enum object, ...) cannot satisfy a constraint and is denied, rather
     * than raising a TypeError and turning a narrowing scope into a 500.
     */
    private function checkResourceValueWithConstraints(FHIRDomainResource $resource, mixed $resourceValue
        , array $constraintValues, int|string $key): bool
    {
        if ($resourceValue === null) {
            return false;
        }
        if (is_array($resourceValue)) {
            // multiple values, check if any match
            foreach ($resourceValue as $value) {
                if ($this->checkResourceValueWithConstraints($resource, $value, $constraintValues, $key)) {
                    return true;
                }
            }
            return false;
        } elseif ($resourceValue instanceof FHIRCodeableConcept) {
            // check each coding
            foreach ($resourceValue->getCoding() as $coding) {
                if ($this->checkResourceValueWithConstraints($resource, $coding, $constraintValues, $key)) {
                    return true;
                }
            }
            return false;
        } elseif ($resourceValue instanceof FHIRCoding) {
            // check system|code match
            foreach ($constraintValues as $constraint) {
                $parts = explode('|', (string) $constraint);
                $code = $parts[1] ?? null;
                if (count($parts) == 2) {
                    $system = $parts[0];
                    $code = $parts[1];
                } else {
                    $system = null;
                    $code = $parts[0] ?? null;
                }
                // code should never be null
                if (($system === null || $system === $resourceValue->getSystem())
                    && ($code === $resourceValue->getCode())) {
                    return true;
                }
            }
            return false;
        } elseif ($resourceValue instanceof FHIRCode) {
            return in_array($resourceValue->getValue(), $constraintValues, true);
        }
        return false;
    }
}
