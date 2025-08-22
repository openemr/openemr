<?php

/*
 * ScopeValidatorFactory.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Validators;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ResourceScopeEntityList;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;

class ScopeValidatorFactory
{
    /**
     * @param array $currentServerScopes
     * @return ResourceScopeEntityList[]
     */
    public function buildScopeValidatorArray(array $currentServerScopes): array
    {
        $scopePermissionArray = [];
        foreach ($currentServerScopes as $scope) {
            $scopeObject = ScopeEntity::createFromString($scope);
            if (empty($scopePermissionArray[$scopeObject->getScopeLookupKey()])) {
                $scopePermissionArray[$scopeObject->getScopeLookupKey()] = new ResourceScopeEntityList($scopeObject->getScopeLookupKey());
            }
            $scopePermissionArray[$scopeObject->getScopeLookupKey()][] = $scopeObject;
        }
        return $scopePermissionArray;
    }
}
