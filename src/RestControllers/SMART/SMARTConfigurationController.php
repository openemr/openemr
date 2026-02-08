<?php

/**
 * Handles the creation of the smart-configuration used for SMART apps and complies with
 * SMART on FHIR Core Capabilities 1.0.0
 * @see http://hl7.org/fhir/smart-app-launch/conformance/index.html
 *
 * @package OpenEMR\RestControllers\SMART
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\SMART;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\FHIR\Config\ServerConfig;
use OpenEMR\FHIR\SMART\Capability;

class SMARTConfigurationController
{
    /**
     * @var ServerConfig
     */
    private readonly ServerConfig $serverConfig;

    public function __construct()
    {
        $this->serverConfig = new ServerConfig();
    }

    public function getConfig(): array
    {
        // combine all possible supported scopes(OIDC & SMART on FHIR)
        // and reduce to only scopes supported by existing FHIR api resources.
        $scopeRepository = new ScopeRepository();
        $scopesSupported = $scopeRepository->getCurrentSmartScopes();

        /**
         * US Core Version 3.1.0 / SMART ON FHIR 1.0.0 @see https://hl7.org/fhir/smart-app-launch/1.0.0/conformance/index.html#using-well-known
         * US Core Version 7.0.0 / SMART ON FHIR Version 2.2.0 @see https://hl7.org/fhir/smart-app-launch/STU2/conformance.html
         *
         * issuer ()
         * authorization_endpoint: REQUIRED (3.1.0/7.0.0), URL to the OAuth2 authorization endpoint.
         * token_endpoint: REQUIRED, URL to the OAuth2 token endpoint.
         * token_endpoint_auth_methods: OPTIONAL, array of client authentication methods supported by the token endpoint. The options are “client_secret_post” and “client_secret_basic”.
         * registration_endpoint: OPTIONAL, if available, URL to the OAuth2 dynamic registration endpoint for this FHIR server.
         * scopes_supported: RECOMMENDED, array of scopes a client may request. See scopes and launch context.
         * response_types_supported: RECOMMENDED, array of OAuth2 response_type values that are supported
         * management_endpoint: RECOMMENDED, URL where an end-user can view which applications currently have access to data and can make adjustments to these access rights.
         * introspection_endpoint : RECOMMENDED, URL to a server’s introspection endpoint that can be used to validate a token.
         * revocation_endpoint : RECOMMENDED, URL to a server’s revoke endpoint that can be used to revoke a token.
         * capabilities: REQUIRED, array of strings representing SMART capabilities (e.g., single-sign-on or launch-standalone) that the server supports.
         */

        $config = [
            // required fields for SMART v2
            "issuer" => $this->serverConfig->getFhirUrl(),
             "jwks_uri" => $this->serverConfig->getJsonWebKeySetUrl(),
            "authorization_endpoint" => $this->serverConfig->getAuthorizeUrl(),
            "grant_types_supported" => ['client_credentials', 'authorization_code'],
            "token_endpoint" => $this->serverConfig->getTokenUrl(),
            "capabilities" => Capability::SUPPORTED_CAPABILITIES,
            // added for PKCE support.
            "code_challenge_methods_supported" => ['S256'],
            "scopes_supported" => [
                $scopesSupported
            ],
            "introspection_endpoint" => $this->serverConfig->getIntrospectionUrl(),
            // end required fields for SMART v2


            "token_endpoint_auth_methods_supported" => [
                "client_secret_basic",
                "private_key_jwt"
            ],
            "response_types_supported" => [
                "code",
                "token",
                "id_token",
                "code token",
                "code id_token",
                "token id_token",
                "code token id_token"
            ],
            // we don't support a management endpoint right now
            //    "management_endpoint" => "https://ehr.example.com/user/manage",

            // we don't support API revocation of tokens right now
            //    "revocation_endpoint" => "https://ehr.example.com/user/revoke",

        ];

        return $config;
    }
}
