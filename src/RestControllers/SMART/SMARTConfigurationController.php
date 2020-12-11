<?php

namespace OpenEMR\RestControllers\SMART;

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
class SMARTConfigurationController
{
    /**
     * @var \OpenEMR\RestControllers\AuthorizationController
     */
    private $authServer;

    public function __construct(\OpenEMR\RestControllers\AuthorizationController $authServer)
    {
        $this->authServer = $authServer;
    }

    public function getConfig()
    {
        $authServer = $this->authServer;
        // TODO: should we abstract the innards of the REST controller into its own class
        // so we don't violate single responsibility principle?
        $metadataController = new \OpenEMR\RestControllers\FHIR\FhirMetaDataRestController();
        $statement = $metadataController->getMetaData();

        // TODO: merge these with the OAUTH scopes
        $scopesSupported = [
            "openid"
            , "profile"
//            , "launch"
            , "launch/patient"
            , "patient/*.*"
//            , "user/*.*"
//            , "offline_access"
        ];
        // create hash dictionary
        $scopes_dict = array_combine($scopesSupported, $scopesSupported);
        $restAPIs = $statement->getRest();
        foreach ($restAPIs as $api) {
            $resources = $api->getResource();
            foreach ($resources as $resource) {
                // annoying that we switch into JSON instead of objects here
                // violates the least surprise principle...
                $interactions = $resource['interaction'];
                $resourceType = $resource['type'];
                foreach ($interactions as $interaction) {
                    $scopeRead = $resourceType . ".read";
                    $scopeWrite = $resourceType . ".write";
                    switch ($interaction['code']) {
                        case 'read':
                        {
                            if (empty($scopes_dict[$scopeRead])) {
                                $scopes_dict[$scopeRead] = $scopeRead;
                            }
                        }
                        break;
                        case 'insert': // checkstyle doesn't like fallthrough statements apparently
                        {
                            if (empty($scopes_dict[$scopeWrite])) {
                                $scopes_dict[$scopeWrite] = $scopeWrite;
                            }
                        }
                        break;
                        case 'update':
                        {
                            if (empty($scopes_dict[$scopeWrite])) {
                                $scopes_dict[$scopeWrite] = $scopeWrite;
                            }
                        }
                        break;
                    }
                }
            }
        }
        $scopesSupported = array_keys($scopes_dict);
        sort($scopesSupported);

        /**
         * @see http://www.hl7.org/fhir/smart-app-launch/conformance/index.html#using-well-known
         * authorization_endpoint: REQUIRED, URL to the OAuth2 authorization endpoint.
        token_endpoint: REQUIRED, URL to the OAuth2 token endpoint.
        token_endpoint_auth_methods: OPTIONAL, array of client authentication methods supported by the token endpoint. The options are “client_secret_post” and “client_secret_basic”.
        registration_endpoint: OPTIONAL, if available, URL to the OAuth2 dynamic registration endpoint for this FHIR server.
        scopes_supported: RECOMMENDED, array of scopes a client may request. See scopes and launch context.
        response_types_supported: RECOMMENDED, array of OAuth2 response_type values that are supported
        management_endpoint: RECOMMENDED, URL where an end-user can view which applications currently have access to data and can make adjustments to these access rights.
        introspection_endpoint : RECOMMENDED, URL to a server’s introspection endpoint that can be used to validate a token.
        revocation_endpoint : RECOMMENDED, URL to a server’s revoke endpoint that can be used to revoke a token.
        capabilities: REQUIRED, array of strings representing SMART capabilities (e.g., single-sign-on or launch-standalone) that the server supports.
         */

        $config = [
            "authorization_endpoint" => $authServer->getAuthorizeUrl(),
            "token_endpoint" => $authServer->getTokenUrl(),
            "registration_endpoint" => $authServer->getRegistrationUrl(),
            "scopes_supported" => [
                $scopesSupported
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
            "introspection_endpoint" => $authServer->getIntrospectionUrl(),
            // we don't revoke tokens right now
            //    "revocation_endpoint" => "https://ehr.example.com/user/revoke",
            "capabilities" => \OpenEMR\RestControllers\FHIR\FhirMetaDataRestController::SMART_CAPABILITIES
        ];
        return $config;
    }
}
