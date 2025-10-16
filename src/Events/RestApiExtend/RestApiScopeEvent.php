<?php

/**
 * RestApiScopeEvent.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\RestApiExtend;

use Symfony\Contracts\EventDispatcher\Event;
use InvalidArgumentException;

class RestApiScopeEvent extends Event
{
    const API_TYPE_STANDARD = "standard";
    const API_TYPE_FHIR = "fhir";

    const EVENT_TYPE_GET_SUPPORTED_SCOPES = "api.scope.get-supported-scopes";

    private array $scopes;
    private string $apiType;

    private bool $systemScopesEnabled = false;

    public function __construct()
    {
        $this->scopes = [];
        $this->apiType = self::API_TYPE_STANDARD;
    }

    /**
     * @return array The scopes for the API
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     * @return RestApiScopeEvent
     */
    public function setScopes(array $scopes): RestApiScopeEvent
    {
        $this->scopes = $scopes;
        return $this;
    }

    public function addScope($context, $resource, $permission): void
    {
        $this->scopes[] = $context . '/' . $resource . '.' . $permission;
    }

    /**
     * @return string
     */
    public function getApiType(): string
    {
        return $this->apiType;
    }

    /**
     * @param string $apiType
     * @return RestApiScopeEvent
     */
    public function setApiType(string $apiType): RestApiScopeEvent
    {
        if (!in_array($apiType, [self::API_TYPE_STANDARD, self::API_TYPE_FHIR])) {
            throw new InvalidArgumentException("Invalid API type: " . $apiType);
        }
        $this->apiType = $apiType;
        return $this;
    }

    public function setSystemScopesEnabled(bool $areSystemScopesEnabled): RestApiScopeEvent
    {
        $this->systemScopesEnabled = $areSystemScopesEnabled;
        return $this;
    }
    public function isSystemScopesEnabled(): bool
    {
        return $this->systemScopesEnabled ?? false;
    }
}
