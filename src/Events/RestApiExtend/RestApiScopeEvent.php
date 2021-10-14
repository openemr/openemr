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

class RestApiScopeEvent extends Event
{
    const API_TYPE_STANDARD = "standard";
    const API_TYPE_FHIR = "fhir";

    const EVENT_TYPE_GET_SUPPORTED_SCOPES = "api.scope.get-supported-scopes";

    private $scopes;
    private $apiType;

    public function __construct()
    {
        $this->scopes = [];
        $this->type = self::API_TYPE_STANDARD;
    }

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param mixed $scopes
     * @return RestApiScopeEvent
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiType()
    {
        return $this->apiType;
    }

    /**
     * @param mixed $apiType
     * @return RestApiScopeEvent
     */
    public function setApiType($apiType)
    {
        $this->apiType = $apiType;
        return $this;
    }
}
