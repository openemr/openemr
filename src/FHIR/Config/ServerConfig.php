<?php

/**
 * ServerConfig handles common configuration addresses and elements for FHIR.  Many of these values were being scattered
 * across the codebase and in order to centralize them we've stored them into this class.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\Config;

use http\Exception\RuntimeException;
use OpenEMR\Common\Auth\OAuth2KeyConfig;

class ServerConfig
{
    /**
     * @var string The site id that is used (if not in multisite this is set to 'default')
     */
    private string $siteId;

    /**
     * @var string The schema, hostname, and port that is used for the FHIR server
     */
    private string $oauthAddress;

    /**
     * @var string The web root address for the fhir server
     */
    private string $webRoot;

    private string $webServerRoot;

    public function __construct()
    {
        // we may let these be injected at another point in time but for now we set this up as globals
        $this->siteId = $_SESSION['site_id'] ?? '';
        $this->oauthAddress = $GLOBALS['site_addr_oath'] ?? $_SERVER['HTTP_HOST'];
        $this->webServerRoot = $GLOBALS['fileroot'] ?? '';
        $this->webRoot = $GLOBALS['web_root'] ?? '';
    }

    /**
     * Returns the URL for the server's fhir endpoint.  This is often used for the audience or issuer URL as well.
     * @return string
     */
    public function getFhirUrl(): string
    {
        return $this->getBaseApiUrl() . "/fhir";
    }

    public function getStandardApiUrl(): string
    {
        return $this->getBaseApiUrl() . "/api";
    }

    public function getInternalBaseApiUrl(): string
    {
        return $this->webRoot . '/apis/' . $this->siteId;
    }

    public function getBaseApiUrl(): string
    {
        return $this->oauthAddress . $this->getInternalBaseApiUrl();
    }

    public function getFhir3rdPartyAppRequirementsDocument(): string
    {
        return $this->oauthAddress . $this->webRoot . "/FHIR_README.md#3rd-party-smart-apps";
    }

    /**
     * @return string
     */
    public function getSiteId(): string
    {
        return $this->siteId;
    }

    /**
     * @param string $siteId
     * @return ServerConfig
     */
    public function setSiteId(string $siteId): ServerConfig
    {
        $this->siteId = $siteId;
        return $this;
    }

    /**
     * @return string
     */
    public function getOauthAddress(): string
    {
        return $this->oauthAddress;
    }

    /**
     * @param string $oauthAddress
     * @return ServerConfig
     */
    public function setOauthAddress(string $oauthAddress): ServerConfig
    {
        $this->oauthAddress = $oauthAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebRoot(): string
    {
        return $this->webRoot;
    }

    /**
     * @param string $webRoot
     * @return ServerConfig
     */
    public function setWebRoot(string $webRoot): ServerConfig
    {
        $this->webRoot = $webRoot;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebServerRoot(): string
    {
        return $this->webServerRoot;
    }

    /**
     * @param string $webServerRoot
     */
    public function setWebServerRoot(string $webServerRoot): void
    {
        $this->webServerRoot = $webServerRoot;
    }

    public function getPublicRestKey()
    {
        // TODO: @adunsulag we have redundancy here in OAuth2KeyConfig and ServerConfig.  We should probably merge these.
        $site = $this->getSiteId() ?? "default";
        $webServerRoot = $this->getWebServerRoot() ?? $GLOBALS['fileroot'] ?? "";
        // if we can't get the web server root then we can't get the public key
        if (empty($webServerRoot)) {
            throw new \RuntimeException("Unable to determine web server root");
        }
        return $webServerRoot . "/sites/" . $site . "/documents/certificates/oapublic.key";
    }
    public function getJsonWebKeySetUrl(): string
    {
        // this is the URL that the FHIR server will use to get the public key for the OAuth2 server
        return $this->getOauthAuthorizationUrl() . "/jwk";
    }

    public function getOauthAuthorizationUrl(): string
    {
        return $this->oauthAddress . $this->webRoot . "/oauth2/" . $this->getSiteId();
    }
    public function getTokenUrl(): string
    {
        return $this->getOauthAuthorizationUrl() . "/token";
    }

    public function getAuthorizeUrl(): string
    {
        return $this->getOauthAuthorizationUrl() . "/authorize";
    }
    public function getRegistrationUrl(): string
    {
        return $this->getOauthAuthorizationUrl() . "/registration";
    }
    public function getIntrospectionUrl(): string
    {
        return $this->getOauthAuthorizationUrl() . "/introspect";
    }

    public function areSystemScopesEnabled()
    {
        return $GLOBALS['rest_system_scopes_api'] === '1';
    }
}
