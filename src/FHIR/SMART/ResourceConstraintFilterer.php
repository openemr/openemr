<?php
/*
 * ResourceConstraintFilterer.php  Would like a better name for this but for now...
 * This class handles checking if a given FHIR resource can be accessed based on the constraints given in the
 * HttpRestRequest's access token scopes for the currently requested endpoint.
 *
 * It currently handles any constraint that maps to a getter on the resource that is a FHIRCodeableConcept, FHIRCoding, or FHIRCode
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;

class ResourceConstraintFilterer {

    use SystemLoggerAwareTrait;

    public function canAccessResource(FHIRDomainResource $resource, HttpRestRequest $request): bool {
        $endpointScope = $request->getRequestRequiredScope();
        // TODO: @adunsulag we could move this all into the HttpRestRequest class... but it seems heavy, is there a better
        // class with more cohesion to put this logic into?
        $scopeEntities = $request->getAllContainedScopesForScopeEntity($endpointScope);
        foreach ($scopeEntities as $scopeEntity) {
            // Check if this scope entity matches or is contained by the given scope
            // add any constraints to the endpoint scope
            $endpointScope->addScopePermissions($scopeEntity);
        }
        $constraints = $endpointScope->getPermissions()->getConstraints();
        if (!empty($constraints)) {
            // the scope has constraints, so we need to add them to the request query parameters
            // the scope constraint may be category=value1,value2,value3 etc and the query may request category=value2,value4
            // we need to make sure that the final query only contains values that are allowed by the scope constraints
            foreach ($constraints as $key => $constraintValues) {
                // TODO: @adunsulag we should fix the getConstraints to make this an array always
                $constraintValues = is_array($constraintValues) ? $constraintValues : [$constraintValues];
                $resourceValue = $this->getResourceValueForKey($resource, $key);
                if ($this->checkResourceValueWithConstraints($resource, $resourceValue, $constraintValues, $key)) {
                    return true;
                }
            }
            return false;
        }
        // no constraints, allow access
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

    private function checkResourceValueWithConstraints(FHIRDomainResource $resource, array|FHIRCodeableConcept|FHIRCoding|FHIRCode|null $resourceValue
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
