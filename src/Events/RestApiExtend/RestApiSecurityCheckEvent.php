<?php

/**
 * RestApiSecurityCheckEvent is fired when the HttpRestRouteHandler is going to check the security of the current api
 * call. The consumer of the event can tell the core api to skip the security check and return a valid security check.
 * This allows the consumer to implement their own security checks, either completely replacing the security system
 * or enhancing it with additional checks.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\RestApiExtend;

use OpenEMR\Common\Http\HttpRestRequest;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\EventDispatcher\Event;

class RestApiSecurityCheckEvent extends Event
{
    const EVENT_HANDLE = 'api.route.security.check';

    /**
     * @var HttpRestRequest
     */
    private $restRequest;

    private $scopeType;

    /**
     * @var bool Whether to skip the OpenEMR core security checks and return a valid security check response
     */
    private bool $skipSecurityCheck;

    /**
     * @var ResponseInterface A http response to return to the api caller for security check failed response
     */
    private ?ResponseInterface $securityCheckFailedResponse;

    /**
     * @var string The resource / api endpoint we are working
     */
    private string $resource;

    /**
     * @var string The permission required for this api, such as read / write and eventually with SMART 2.0 create,update, etc.
     */
    private string $permission;

    /**
     * @param HttpRestRequest|null $request
     */
    public function __construct(HttpRestRequest $request = null)
    {
        $this->permission = "";
        $this->scopeType = "";
        $this->resource = "";
        $this->restRequest = $request;
        $this->securityCheckFailedResponse = null;
        $this->skipSecurityCheck = false;
    }

    /**
     * @param HttpRestRequest $restRequest
     */
    public function setRestRequest(HttpRestRequest $restRequest): void
    {
        $this->restRequest = $restRequest;
    }

    public function getRestRequest(): HttpRestRequest
    {
        return $this->restRequest;
    }

    public function setScopeType(string $scopeType)
    {
        $this->scopeType = $scopeType;
    }

    public function getScopeType(): string
    {
        return $this->scopeType;
    }

    public function setResource(string $resource)
    {
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setPermission(string $permission)
    {
        $this->permission = $permission;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function hasSecurityCheckFailedResponse(): bool
    {
        return $this->getSecurityCheckFailedResponse() != null;
    }

    public function getSecurityCheckFailedResponse(): ?ResponseInterface
    {
        return $this->securityCheckFailedResponse;
    }

    public function setSecurityCheckFailedResponse(ResponseInterface $response)
    {
        $this->securityCheckFailedResponse = $response;
    }

    public function shouldSkipSecurityCheck()
    {
        return $this->skipSecurityCheck;
    }

    public function skipSecurityCheck(bool $shouldSecuritySkip)
    {
        $this->skipSecurityCheck = $shouldSecuritySkip;
    }
}
