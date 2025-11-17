<?php
/*
 * RequestConstraintFilterer.php  Would like a better name for this but for now...
 * This class is responsible for filtering an HttpRestRequest object's search parameters to meet any granted granular
 * scope constraint restrictions for api requests.  It allows for the logic to be tested and maintained separately from the main
 * FHIR service classes.
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
use OpenEMR\Services\Search\TokenSearchField;

class RequestConstraintFilterer {

    use SystemLoggerAwareTrait;

    /**
     * Updates the request query parameters with constraints from ScopeEntity objects that match the given scope
     *
     * @param HttpRestRequest $request The request to update, will have query parameters modified if there are granular constraints
     * @param ScopeEntity $endpointScope The scope for the endpoint that we are matching against ie GET /fhir/Patient as a user would be user/Patient.rs
     * @return void
     */
    public function updateRequestWithConstraints(HttpRestRequest $request, ScopeEntity $endpointScope): HttpRestRequest
    {
        $scopeEntities = $request->getAllContainedScopesForScopeEntity($endpointScope);
        $constraints = [];

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
                if ($request->query->has($key)) {
                    // TODO: this does EXACT match with system|code we need to enhance this to support
                    // both a blank code w/o system and a system|code match while excluding everything else
                    $constraintToken = new TokenSearchField($key, $constraintValues);
                    $searchToken = new TokenSearchField($key, explode(",", $request->query->get($key)));
                    if (!$constraintToken->containsSearchToken($searchToken)) {
                        // some values were not allowed, throw unauthorized error
                        throw new AccessDeniedException($endpointScope->getContext(), $endpointScope->getResource() ?? ''
                            , "Search parameter contains unauthorized values for parameter '$key'.");
                    }
                } else {
                    $request->query->set($key, implode(',', $constraintValues));
                }
            }
            $logValues = [
                'scope' => $endpointScope->getIdentifier(),
                'constraints' => $constraints,
                'mergedQuery' => $request->query->all()
            ];
            $this->getSystemLogger()->debug("Updated request with scope constraints", $logValues);
        }
        return $request;
    }
}
