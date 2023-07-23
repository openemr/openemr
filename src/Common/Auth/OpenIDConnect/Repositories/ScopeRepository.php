<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Repositories;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\System\System;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use OpenEMR\Events\RestApiExtend\RestApiScopeEvent;
use OpenEMR\RestControllers\RestControllerHelper;
use Psr\Log\LoggerInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $validationScopes;

    /**
     * @var
     */
    private $requestScopes;

    /**
     * Session string containing the scopes populated in the current session.
     * @var string
     */
    private $sessionScopes;

    /**
     * @var \RestConfig
     */
    private $restConfig;

    /**
     * Array(string => callback) Where the string is the route and the callback is the route handler
     * @var array
     */
    private $fhirRouteMap = [];

    /**
     * Array(string => callback) Where the string is the route and the callback is the route handler
     * @var array
     */
    private $routeMap = [];

    /**
     * Array(string => callback) Where the string is the route and the callback is the route handler
     * @var array
     */
    private $portalRouteMap = [];

    /**
     * ScopeRepository constructor.
     * @param $restConfig \RestConfig normally we would typesafe this, but RestConfig isn't in the autoloader so we leave it out so we can unit test this class better
     */
    public function __construct($restConfig = null)
    {
        $this->logger = new SystemLogger();
        $this->requestScopes = isset($_REQUEST['scope']) ? $_REQUEST['scope'] : null;
        $this->sessionScopes = $_SESSION['scopes'] ?? '';
        $this->restConfig = $restConfig;
        if (!empty($restConfig)) {
            $this->fhirRouteMap = $restConfig::$FHIR_ROUTE_MAP ?? [];
            $this->routeMap = $restConfig::$ROUTE_MAP ?? [];
            $this->portalRouteMap = $restConfig::$PORTAL_ROUTE_MAP ?? [];
        }
    }

    /**
     * @return array
     */
    public function getFhirRouteMap(): array
    {
        return $this->fhirRouteMap;
    }

    /**
     * @param array $fhirRouteMap
     */
    public function setFhirRouteMap(array $fhirRouteMap): void
    {
        $this->fhirRouteMap = $fhirRouteMap;
    }

    /**
     * @return array
     */
    public function getStandardRouteMap(): array
    {
        return $this->routeMap;
    }

    /**
     * @param array $routeMap
     */
    public function setStandardRouteMap(array $routeMap): void
    {
        $this->routeMap = $routeMap;
    }

    /**
     * @return array
     */
    public function getPortalRouteMap(): array
    {
        return $this->portalRouteMap;
    }

    /**
     * @param array $portalRouteMap
     */
    public function setPortalRouteMap(array $portalRouteMap): void
    {
        $this->portalRouteMap = $portalRouteMap;
    }

    /**
     * @param string $identifier
     * @return ScopeEntity|null
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntity
    {
        if (empty($this->validationScopes)) {
            $this->logger->debug("ScopeRepository->getScopeEntityByIdentifier() attempting to build validation scopes");
            $this->validationScopes = $this->buildScopeValidatorArray();
        }

        if (array_key_exists($identifier, $this->validationScopes) === false && stripos($identifier, 'site:') === false) {
            $this->logger->error("ScopeRepository->getScopeEntityByIdentifier() request access to invalid scope", [
                "scope" => $identifier
                , 'validationScopes' => $this->validationScopes]);
            return null;
        }
        $this->logger->debug("ScopeRepository->getScopeEntityByIdentifier() scope requested exists in system", ["identifier" => $identifier]);

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);

        return $scope;
    }

    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ): array {
        $finalizedScopes = [];
        $scopeListNames = [];
        $finalizedScopeNames = [];
        $clientScopes = [];
        // we only let scopes that the client initially registered with through instead of whatever they request in
        // their grant.
        if ($clientEntity instanceof ClientEntity) {
            $clientScopes = $clientEntity->getScopes();
            foreach ($scopes as $scope) {
                $scopeListNames[] = $scope->getIdentifier();
                if (\in_array($scope->getIdentifier(), $clientScopes)) {
                    $finalizedScopes[] = $scope;
                    $finalizedScopeNames[] = $scope->getIdentifier();
                }
            }
        } else {
            $this->logger->error("client entity was not an instance of ClientEntity and scopes could not be retrieved");
        }

        // If a nonce is passed in, add a nonce scope for id token nonce claim
        if (!empty($_SESSION['nonce'])) {
            $scope = new ScopeEntity();
            $scope->setIdentifier('nonce');
            $finalizedScopes[] = $scope;
            $finalizedScopeNames[] = "nonce";
        }

        // Need a site id for our apis
        $siteScope = $this->getSiteScope();
        $finalizedScopeNames[] = $siteScope->getIdentifier();
        $finalizedScopes[] = $siteScope;

            $this->logger->debug(
                "ScopeRepository->finalizeScopes() scopes finalized ",
                ['finalizedScopes' => $finalizedScopeNames, 'clientScopes' => $clientScopes
                ,
                'initialScopes' => $scopeListNames]
            );
        return $finalizedScopes;
    }

    public function getSiteScope(): ScopeEntity
    {
        // TODO: adunsulag, sjpadget Won't refresh token validation fail on multi-site since we won't
        // have the id in the session?
        if ($_SESSION['site_id']) {
            $siteScope = "site:" . $_SESSION['site_id'];
        } else {
            $siteScope = "site:default";
        }
        $scope = new ScopeEntity();
        $scope->setIdentifier($siteScope);
        return $scope;
    }

    public function setRequestScopes($scopes)
    {
        if (!is_string($scopes)) {
            (new SystemLogger())->error("Attempted to set request scopes to something other than a string", ['scopes' => $scopes]);
            throw new \InvalidArgumentException("Invalid scope parameter set");
        }

        $this->requestScopes = $scopes;
    }

    public function getRequestScopes()
    {
        return $this->requestScopes;
    }

    public function getSessionScopes()
    {
        return $this->sessionScopes;
    }

    // nonce claim and nonce scope is handled by server logic.
    // current locale is enUS need to set from openemr locale.
    public function getSupportedClaims(): array
    {
        return array(
            "profile",
            "email",
            "email_verified",
            "phone",
            "phone_verified",
            "family_name",
            "given_name",
            "fhirUser",
            "locale",
            "api:oemr",
            "api:fhir",
            "api:port",
            "aud", //client_id
            "iat", // token create time
            "iss", // token issuer(https://domain)
            "exp", // token expiry time.
            "sub" // the subject of token. usually patient UUID.
        );
    }

    public function oidcScopes(): array
    {
        return [
            "openid",
            "profile",
            "name",
            "address",
            "given_name",
            "family_name",
            "nickname",
            "phone",
            "phone_verified",
            "email",
            "email_verified",
            "offline_access",
            "api:oemr",
            "api:fhir",
            "api:port"
        ];
    }

    public function fhirRequiredSmartScopes(): array
    {
        $requiredSmart = [
            "openid",
            "fhirUser",
            "online_access",
            "offline_access",
            "launch",
            "launch/patient",
            "api:oemr",
            "api:fhir",
            "api:port",
        ];
        // we define our Bulk FHIR here
        // There really is no defined standard on how to handle SMART scopes for operations ($operation)
        // hopefully its defined in V2, but for now we are going to implement using the following scopes
        // @see https://chat.fhir.org/#narrow/stream/179170-smart/topic/SMART.20scopes.20and.20custom.20operations/near/156832330
        if (isset($this->restConfig) && $this->restConfig->areSystemScopesEnabled()) {
            $requiredSmart[] = 'system/Patient.$export';
            $requiredSmart[] = 'system/Group.$export';
            $requiredSmart[] = 'system/*.$bulkdata-status';
            $requiredSmart[] = 'system/*.$export';
        }
        return $requiredSmart;
    }

    /**
     * @Method fhirScopes
     *
     * Returns all fhir permitted scopes and permissions.
     * These will be qualified against existing and future routes
     * with role(patient, user or system facing applications).
     *
     * @return array
     */
    public function fhirScopes(): array
    {
        // Note: we only allow patient/read access for FHIR apps right now, if someone wants write access they have to
        // have a user/<resource>.write permission as the security context for patient only rights has too much risk
        // for problems.

        // we've restricted our patient scopes just to what we have in portal.  We will slowly open them up as we
        // verify the access rights on them.
        $permitted = [
//            "patient/Account.read",
            "patient/AllergyIntolerance.read",
//            "patient/AllergyIntolerance.write",
            "patient/Appointment.read",
            "patient/Binary.read",
//            "patient/Appointment.write",
            "patient/CarePlan.read",
            "patient/CareTeam.read",
            "patient/Condition.read",
//            "patient/Condition.write",
//            "patient/Consent.read",
            "patient/Coverage.read",
//            "patient/Coverage.write",
            "patient/DiagnosticReport.read",
            "patient/Device.read",
            "patient/DocumentReference.read",
            'patient/DocumentReference.$docref', // generate or view most recent CCD for the selected patient
//            "patient/DocumentReference.write",
            "patient/Encounter.read",
//            "patient/Encounter.write",
            "patient/Goal.read",
            "patient/Immunization.read",
//            "patient/Immunization.write",
            "patient/Location.read",
            "patient/MedicationRequest.read",
            "patient/Medication.read",
//            "patient/MedicationRequest.write",
//            "patient/NutritionOrder.read",
            "patient/Observation.read",
//            "patient/Observation.write",
            "patient/Organization.read",
//            "patient/Organization.write",
            "patient/Patient.read",
//            "patient/Patient.write",
            "patient/Person.read",
            "patient/Practitioner.read",
//            "patient/Practitioner.write",
//            "patient/PractitionerRole.read",
//            "patient/PractitionerRole.write",
            "patient/Procedure.read",
//            "patient/Procedure.write",
            "patient/Provenance.read",
//            "patient/Provenance.write",
//            "patient/RelatedPerson.read",
//            "patient/RelatedPerson.write",
//            "patient/Schedule.read",
//            "patient/ServiceRequest.read",
            "user/Account.read",
            "user/AllergyIntolerance.read",
            "user/AllergyIntolerance.write",
            "user/Appointment.read",
            "user/Appointment.write",
            "user/Binary.read",
            "user/CarePlan.read",
            "user/CareTeam.read",
            "user/Condition.read",
            "user/Condition.write",
            "user/Consent.read",
            "user/Coverage.read",
            "user/Coverage.write",
            "user/Device.read",
            "user/DiagnosticReport.read",
            "user/DocumentReference.read",
            "user/DocumentReference.write",
            'user/DocumentReference.$docref', // export CCD for any patient user has access to
            "user/Encounter.read",
            "user/Encounter.write",
            "user/Goal.read",
            "user/Immunization.read",
            "user/Immunization.write",
            "user/Location.read",
            "user/MedicationRequest.read",
            "user/MedicationRequest.write",
            "user/Medication.read",
            "user/NutritionOrder.read",
            "user/Observation.read",
            "user/Observation.write",
            "user/Organization.read",
            "user/Organization.write",
            "user/Patient.read",
            "user/Patient.write",
            "user/Person.read",
            "user/Practitioner.read",
            "user/Practitioner.write",
            "user/PractitionerRole.read",
            "user/PractitionerRole.write",
            "user/Procedure.read",
            "user/Procedure.write",
            "user/Provenance.read",
            "user/Provenance.write",
            "user/RelatedPerson.read",
            "user/RelatedPerson.write",
            "user/Schedule.read",
            "user/ServiceRequest.read",
        ];

        if ($this->restConfig->areSystemScopesEnabled()) {
            return array_merge($permitted, $this->systemScopes());
        }
        return $permitted;
    }

    public function systemScopes(): array
    {
        return [
            "system/Account.read",
            "system/AllergyIntolerance.read",
//            "system/AllergyIntolerance.write",
            "system/Appointment.read",
            "system/Binary.read", // used for Bulk FHIR export downloads
//            "system/Appointment.write",
            "system/CarePlan.read",
            "system/CareTeam.read",
            "system/Condition.read",
//            "system/Condition.write",
            "system/Consent.read",
            "system/Coverage.read",
//            "system/Coverage.write",
            "system/Device.read",
            "system/DocumentReference.read",
            'system/DocumentReference.$docref', // generate / view CCD for any patient in the system
            "system/DiagnosticReport.read",
//            "system/DocumentReference.write",
            "system/Encounter.read",
//            "system/Encounter.write",
            "system/Goal.read",
            "system/Group.read",
            "system/Immunization.read",
//            "system/Immunization.write",
            "system/Location.read",
            "system/MedicationRequest.read",
            "system/Medication.read",
//            "system/MedicationRequest.write",
            "system/NutritionOrder.read",
            "system/Observation.read",
//            "system/Observation.write",
            "system/Organization.read",
//            "system/Organization.write",
            "system/Patient.read",
//            "system/Patient.write",
            "system/Person.read",
            "system/Practitioner.read",
//            "system/Practitioner.write",
            "system/PractitionerRole.read",
//            "system/PractitionerRole.write",
            "system/Procedure.read",
//            "system/Procedure.write",
            "system/Provenance.read",
//            "system/Provenance.write",
            "system/RelatedPerson.read",
//            "system/RelatedPerson.write",
            "system/Schedule.read",
            "system/ServiceRequest.read",
        ];
    }

    public function apiScopes(): array
    {
        return [
            "patient/allergy.read",
            "patient/allergy.write",
            "patient/appointment.read",
            "patient/appointment.write",
            "patient/dental_issue.read",
            "patient/dental_issue.write",
            "patient/document.read",
            "patient/document.write",
            "patient/drug.read",
            "patient/encounter.read",
            "patient/encounter.write",
            "patient/facility.read",
            "patient/facility.write",
            "patient/immunization.read",
            "patient/insurance.read",
            "patient/insurance.write",
            "patient/insurance_company.read",
            "patient/insurance_company.write",
            "patient/insurance_type.read",
            "patient/list.read",
            "patient/medical_problem.read",
            "patient/medical_problem.write",
            "patient/medication.read",
            "patient/medication.write",
            "patient/message.read",
            "patient/message.write",
            "patient/patient.read",
            "patient/patient.write",
            "patient/practitioner.read",
            "patient/practitioner.write",
            "patient/prescription.read",
            "patient/procedure.read",
            "patient/soap_note.read",
            "patient/soap_note.write",
            "patient/surgery.read",
            "patient/surgery.write",
            "patient/vital.read",
            "patient/vital.write",
            "system/allergy.read",
            "system/allergy.write",
            "system/appointment.read",
            "system/appointment.write",
            "system/dental_issue.read",
            "system/dental_issue.write",
            "system/document.read",
            "system/document.write",
            "system/drug.read",
            "system/encounter.read",
            "system/encounter.write",
            "system/facility.read",
            "system/facility.write",
            "system/immunization.read",
            "system/insurance.read",
            "system/insurance.write",
            "system/insurance_company.read",
            "system/insurance_company.write",
            "system/insurance_type.read",
            "system/list.read",
            "system/medical_problem.read",
            "system/medical_problem.write",
            "system/medication.read",
            "system/medication.write",
            "system/message.read",
            "system/message.write",
            "system/patient.read",
            "system/patient.write",
            "system/practitioner.read",
            "system/practitioner.write",
            "system/prescription.read",
            "system/procedure.read",
            "system/soap_note.read",
            "system/soap_note.write",
            "system/surgery.read",
            "system/surgery.write",
            "system/vital.read",
            "system/vital.write",
            "user/allergy.read",
            "user/allergy.write",
            "user/appointment.read",
            "user/appointment.write",
            "user/dental_issue.read",
            "user/dental_issue.write",
            "user/document.read",
            "user/document.write",
            "user/drug.read",
            "user/encounter.read",
            "user/encounter.write",
            "user/facility.read",
            "user/facility.write",
            "user/immunization.read",
            "user/insurance.read",
            "user/insurance.write",
            "user/insurance_company.read",
            "user/insurance_company.write",
            "user/insurance_type.read",
            "user/list.read",
            "user/medical_problem.read",
            "user/medical_problem.write",
            "user/medication.read",
            "user/medication.write",
            "user/message.read",
            "user/message.write",
            "user/patient.read",
            "user/patient.write",
            "user/practitioner.read",
            "user/practitioner.write",
            "user/prescription.read",
            "user/procedure.read",
            "user/soap_note.read",
            "user/soap_note.write",
            "user/surgery.read",
            "user/surgery.write",
            "user/transaction.read",
            "user/transaction.write",
            "user/vital.read",
            "user/vital.write",
        ];
    }

    public function getOidcSupportedScopes(): array
    {
        return $this->oidcScopes();
    }

    public function getSystemFhirSupportedScopes(): array
    {
        return $this->systemScopes();
    }

    public function getStandardApiSupportedScopes(): array
    {
        return $this->apiScopes();
    }

    public function getServerScopes(): array
    {
        $siteScope = $this->getSiteScope();
        return [
            $siteScope->getIdentifier() => $siteScope->getIdentifier()
        ];
    }

    /**
     * Method will qualify current scopes based on active FHIR resources.
     * Allowed permissions are validated from the default scopes and client role.
     *
     * @param string $role
     * @return array
     */
    public function getCurrentSmartScopes(): array
    {
        (new SystemLogger())->debug("ScopeRepository->getCurrentSmartScopes() setting up smart scopes");
        $gbl = $this->restConfig;
        $restHelper = new RestControllerHelper();
        // Collect all currently enabled FHIR resources.
        // Then assign all permissions the resource is capable.
        $scopes_api = [];
        $restAPIs = $restHelper->getCapabilityRESTObject($this->getFhirRouteMap());
        foreach ($restAPIs->getResource() as $resource) {
            $resourceType = $resource->getType()->getValue();
            $interactions = $resource->getInteraction();
            foreach ($interactions as $interaction) {
                $scopeRead =  $resourceType . ".read";
                $scopeWrite = $resourceType . ".write";
                $interactionCode = $interaction->getCode()->getValue();
                switch ($interactionCode) {
                    case 'read':
                        $scopes_api['patient/' . $scopeRead] = 'patient/' . $scopeRead;
                        $scopes_api['user/' . $scopeRead] = 'user/' . $scopeRead;
                        $scopes_api['system/' . $scopeRead] = 'system/' . $scopeRead;
                        break;
                    case 'search-type':
                        $scopes_api['patient/' . $scopeRead] = 'patient/' . $scopeRead;
                        $scopes_api['user/' . $scopeRead] = 'user/' . $scopeRead;
                        $scopes_api['system/' . $scopeRead] = 'system/' . $scopeRead;
                        break;
                    case 'insert':
                    case 'update':
                        $scopes_api['patient/' . $scopeWrite] = 'patient/' . $scopeWrite;
                        $scopes_api['user/' . $scopeWrite] = 'user/' . $scopeWrite;
                        $scopes_api['system/' . $scopeWrite] = 'system/' . $scopeWrite;
                        break;
                }
            }
            foreach ($resource->getOperation() as $operation) {
                $operationCall = $resourceType . '.' . $operation->getName();
                $scopes_api['patient/' . $operationCall]  = 'patient/' . $operationCall;
                $scopes_api['user/' . $operationCall]  = 'user/' . $operationCall;
                $scopes_api['system/' . $operationCall]  = 'system/' . $operationCall;
            }

            // if we needed to define scopes based on operations rather than the predefined Bulk-FHIR operations
            // we would handle them here.  Leaving this commented out just for reference
            // @var array
            // $operations = $resource->getOperation();
        }

        $scopesSupported = $this->fhirScopes();
        $scopes_dict = array_combine($scopesSupported, $scopesSupported);
        $fhir = array_combine($this->fhirRequiredSmartScopes(), $this->fhirRequiredSmartScopes());
        $oidc = array_combine($this->oidcScopes(), $this->oidcScopes());
        // we need to make sure the 'site:' and any other server context vars are permitted
        $serverScopes = $this->getServerScopes();
        $scopesSupported = null;
        // verify scope permissions are allowed for role being used.
        foreach ($scopes_api as $key => $scope) {
            if (empty($scopes_dict[$key])) {
                continue;
            }
            $scopesSupported[$key] = $scope;
        }
        asort($scopesSupported);
        $scopesSupported = array_keys(array_merge($fhir, $oidc, $serverScopes, $scopesSupported));
        (new SystemLogger())->debug("ScopeRepository->getCurrentSmartScopes() scopes supported ", ["scopes" => $scopesSupported]);

        $scopesEvent = new RestApiScopeEvent();
        $scopesEvent->setApiType(RestApiScopeEvent::API_TYPE_FHIR);
        $scopesSupportedList = $scopesSupported;
        $scopesEvent->setScopes($scopesSupportedList);

        $scopesEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($scopesEvent, RestApiScopeEvent::EVENT_TYPE_GET_SUPPORTED_SCOPES, 10);

        if ($scopesEvent instanceof RestApiScopeEvent) {
            $scopesSupportedList = $scopesEvent->getScopes();
        }

        return $scopesSupportedList;
    }

    public function getCurrentStandardScopes(): array
    {
        (new SystemLogger())->debug("ScopeRepository->getCurrentStandardScopes() setting up standard api scopes");
        $restHelper = new RestControllerHelper();
        // Collect all currently enabled resources.
        // Then assign all permissions the resource is capable.
        $scopes_api = [];
        $restAPIs = $restHelper->getCapabilityRESTObject($this->getStandardRouteMap(), "OpenEMR\\Services");
        foreach ($restAPIs->getResource() as $resource) {
            $resourceType = $resource->getType()->getValue();
            $interactions = $resource->getInteraction();
            foreach ($interactions as $interaction) {
                $scopeRead =  $resourceType . ".read";
                $scopeWrite = $resourceType . ".write";
                $interactionCode = $interaction->getCode()->getValue();
                // these values come from this valuset http://hl7.org/fhir/2021Mar/valueset-type-restful-interaction.html
                // for SMART on FHIR 2.0 we will have more granular permissions than *.read and *.write
                switch ($interactionCode) {
                    case 'read':
                        $scopes_api['user/' . $scopeRead] = 'user/' . $scopeRead;
                        $scopes_api['system/' . $scopeRead] = 'system/' . $scopeRead;
                        break;
                    case 'search-type':
                        $scopes_api['user/' . $scopeRead] = 'user/' . $scopeRead;
                        $scopes_api['system/' . $scopeRead] = 'system/' . $scopeRead;
                        break;
                    case 'put':
                    case 'create':
                    case 'update':
                    case 'delete':
                        $scopes_api['user/' . $scopeWrite] = 'user/' . $scopeWrite;
                        $scopes_api['system/' . $scopeWrite] = 'system/' . $scopeWrite;
                        break;
                }
            }
        }
        $scopes_api_portal = [];
        // TODO: should we put this into the constructor? makes it hard to unit test this...
        if (!empty($GLOBALS['rest_portal_api'])) {
            $restAPIs = $restHelper->getCapabilityRESTObject($this->getPortalRouteMap(), "OpenEMR\\Services");
            foreach ($restAPIs->getResource() as $resource) {
                $resourceType = $resource->getType()->getValue();
                $interactions = $resource->getInteraction();
                foreach ($interactions as $interaction) {
                    $scopeRead =  $resourceType . ".read";
                    $scopeWrite = $resourceType . ".write";
                    $interactionCode = $interaction->getCode()->getValue();
                    // these values come from this valuset http://hl7.org/fhir/2021Mar/valueset-type-restful-interaction.html
                    // for SMART on FHIR 2.0 we will have more granular permissions than *.read and *.write
                    switch ($interactionCode) {
                        case 'read':
                            $scopes_api_portal['patient/' . $scopeRead] = 'patient/' . $scopeRead;
                            break;
                        case 'search-type':
                            $scopes_api_portal['patient/' . $scopeRead] = 'patient/' . $scopeRead;
                            break;
                        case 'put':
                        case 'create':
                        case 'update':
                        case 'delete':
                            $scopes_api_portal['patient/' . $scopeWrite] = 'patient/' . $scopeWrite;
                            break;
                    }
                }
            }
        }
        $oidc = array_combine($this->oidcScopes(), $this->oidcScopes());
        $scopes_api = array_merge($scopes_api, $scopes_api_portal);

        $scopesSupported = $this->apiScopes();
        $scopes_dict = array_combine($scopesSupported, $scopesSupported);
        $scopesSupported = null; // this is odd, why do we have this?
        // verify scope permissions are allowed for role being used.
        foreach ($scopes_api as $key => $scope) {
            if (empty($scopes_dict[$key])) {
                continue;
            }
            $scopesSupported[$key] = $scope;
        }
        asort($scopesSupported);
        $serverScopes = $this->getServerScopes();
        $scopesSupported = array_keys(array_merge($oidc, $serverScopes, $scopesSupported));

        $scopesEvent = new RestApiScopeEvent();
        $scopesEvent->setApiType(RestApiScopeEvent::API_TYPE_STANDARD);
        $scopesSupportedList = $scopesSupported;
        $scopesEvent->setScopes($scopesSupportedList);

        $scopesEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($scopesEvent, RestApiScopeEvent::EVENT_TYPE_GET_SUPPORTED_SCOPES, 10);

        if ($scopesEvent instanceof RestApiScopeEvent) {
            $scopesSupportedList = $scopesEvent->getScopes();
        }

        return $scopesSupportedList;
    }

    /**
     * Returns true if the session or request has a fhir api scope in it
     * @return bool
     */
    public function hasFhirApiScopes()
    {
        $requestScopeString = $this->getRequestScopes();
        $sessionScopeString = $this->getSessionScopes();

        $isFhir = preg_match('(fhirUser|api:fhir)', $requestScopeString)
            || preg_match('(fhirUser|api:fhir)', $sessionScopeString);
        return $isFhir !== false;
    }

    /**
     * Returns true if the session or request has a standard or portal api scope in it
     * @return bool
     */
    public function hasStandardApiScopes()
    {
        $requestScopeString = $this->getRequestScopes();
        $sessionScopeString = $this->getSessionScopes();
        $isApi = preg_match('(api:oemr|api:port)', $requestScopeString)
            || preg_match('(api:oemr|api:port)', $sessionScopeString);

        return $isApi !== false;
    }

    // made public for now!
    public function buildScopeValidatorArray(): array
    {
        $requestScopeString = $this->getRequestScopes();
        $isFhir = $this->hasFhirApiScopes();
        $isApi = $this->hasStandardApiScopes();
        (new SystemLogger())->debug(
            "ScopeRepository->buildScopeValidatorArray() ",
            ["requestScopeString" => $requestScopeString, 'isStandardApi' => $isApi, 'isFhirApi' => $isFhir]
        );

        // TODO: adunsulag check with @bradymiller and @sjpadgett on defaulting api to $isFhir not all SMART apps request
        // fhirUser and if we want to support the larger ecosystem of apps we need to not require api:fhir or fhirUser

        $scopesFhir = [];
        if ($isFhir || !$isApi) {
            $scopesFhir = $this->getCurrentSmartScopes();
        }
        $scopesApi = [];
        if ($isApi) {
            $scopesApi = $this->getCurrentStandardScopes();
        }
        $mergedScopes = array_merge($scopesFhir, $scopesApi);
        $scopes = [];

        $scopes['nonce'] = ['description' => 'Nonce value used to detect replay attacks by third parties'];

        foreach ($mergedScopes as $scope) {
            // TODO: @adunsulag look at adding the actual scope description here and what the ramifications are.
            // Looks like this line could be
            // $scopes[$scope] = ['description' => $this->lookupDescriptionForScope($scope, false)];
            $scopes[$scope] = ['description' => 'OpenId Connect'];
        }

        return $scopes;
    }

    public function lookupDescriptionForScope($scope, bool $isPatient)
    {
        $requiredSmart = [
            "openid" => xl("Permission to retrieve information about the current logged-in user"),
            "fhirUser" => xl("Identity Information - Permission to retrieve information about the current logged-in user"),
            "online_access" => xl("Request ability to access data while the current logged-in user remains logged in"),
            "offline_access" => xl("Request ability to access data even when the current logged-in user has logged out"),
            "launch" => xl("Permission to obtain information from the EHR for the current session context when app is launched from an EHR."),
            "launch/patient" => xl("When launching outside the EHR, ask for a patient to be selected at launch time."),
            "api:oemr" => xl("Permission to use the OpenEMR standard api."),
            "api:fhir" => xl("Permission to use the OpenEMR FHIR api"),
            "api:port" => xl("Permission to use the OpenEMR apis from inside the patient portal"),
            'system/Patient.$export' => xl("Permission to export Patient Compartment resources"),
            'system/Group.$export' => xl("Permission to export Patient Compartment resources connected to a Patient Group"),
            'system/*.$bulkdata-status' => xl("Permission to check the job status of a bulkdata export"),
            'system/*.$export' => xl("Permission to export the entire system dataset the is exportable")
        ];

        if (isset($requiredSmart[$scope])) {
            return $requiredSmart[$scope];
        }

        $parts = explode("/", $scope);
        $context = reset($parts);
        $resourcePerm = $parts[1] ?? "";
        $resourcePermParts = explode(".", $resourcePerm);
        $resource = $resourcePermParts[0] ?? "";
        $permission = $resourcePermParts[1] ?? "";

        if (!empty($resource)) {
            $isReadPermission = $permission == "read";
            if (strpos($permission, "$") !== false) {
                return $this->lookupDescriptionForResourceOperation($resource, $context, $isPatient, $permission);
            } else {
                return $this->lookupDescriptionForResourceScope($resource, $context, $isPatient, $isReadPermission);
            }
        } else {
            return null;
        }
    }

    private function lookupDescriptionForResourceOperation($resource, $context, $isPatient, $permission)
    {
        $description = null;
        if ($resource == "DocumentReference" && $permission == '$docref') {
            $description = xl("Create a Clinical Summary of Care Document (CCD) or retrieve the most current CCD");
            if ($context == 'user') {
                $description .= " " . xl("for a patient that the user has access to");
            } else if ($context == "system") {
                $description .= " " . xl("for a patient that exists in the system");
            };
        }
        return $description;
    }

    private function lookupDescriptionForResourceScope($resource, $context, $isPatient, $isReadPermission)
    {

        $scopesByResource[$resource] = $scopesByResource[$resource] ?? ['permissions' => []];

        $description = $isReadPermission ? xl("Read Access: View, search and access") : xl("Write Access: Create or modify");
        $description .= " ";
        switch ($resource) {
            case 'AllergyIntolerance':
                $description .= xl("allergies/adverse reactions");
                break;
            case 'Appointment':
                $description .= xl("appointments");
                break;
            case 'Observation':
                $description .= xl("observations including laboratory,vitals, and social history records");
                break;
            case 'CarePlan':
                $description .= xl("care plan information including treatment information and notes");
                break;
            case 'CareTeam':
                $description .= xl("care team information including practitioners, organizations, persons, and related individuals");
                break;
            case 'Condition':
                $description .= xl("conditions including health concerns, problems, and encounter diagnoses");
                break;
            case 'Device':
                $description .= xl("implantable medical device records");
                break;
            case 'DiagnosticReport':
                $description .= xl("diagnostic reports including laboratory,cardiology,radiology, and pathology reports");
                break;
            case 'DocumentReference':
                $description .= xl("clinical and non-clinical documents");
                break;
            case 'Encounter':
                $description .= xl("encounter information");
                break;
            case 'Goal':
                $description .= xl("goals");
                break;
            case 'Immunization':
                $description .= xl("immunization history");
                break;
            case 'MedicationRequest':
                $description .= xl("planned and prescribed medication history including self-reported medications");
                break;
            case 'Medication':
                $description .= xl("drug information related to planned and prescribed medication history");
                break;
            case 'Organization':
                $description .= xl("companies, facilities, insurances, and other organizations");
                break;
            case 'Patient':
                $description .= xl("patient basic demographics including names,communication preferences,race,ethnicity,birth sex,previous names and other administrative information");
                break;
            case 'Practitioner':
                $description .= xl("provider basic demographic information and other administrative information");
                break;
            case 'PractitionerRole':
                $description .= xl("practitioner role for a practitioner (including speciality, location, contact information)");
                break;
            case 'Procedure':
                $description .= xl("procedures");
                break;
            case 'Location':
                $description .= xl("locations associated with a patient, provider, or organization");
                break;
            case 'Provenance':
                $description .= xl("provenance information (including person(s) responsible for the information, author organizations, and transmitter organizations)");
                break;
            default:
                $description .= xl("medical records for this resource type");
                break;
        }
        if ($context == "user") {
            $description .= ". " . xl("Application is requesting access to all patient data for this resource you have access to");
        } else if ($context == "system") {
            $description .= ". " . xl("Application is requesting access to all data in entire system for this resource");
        }
        return $description;
    }

    /**
     * Checks if the given scopes array requires any manual approval by an administrator before an oauth2 client can be authorized
     * @param bool $is_confidential_client Whether the client is confidential (can keep a secret safe) or a public app
     * @param array $scopes The scopes to be checked to see if we need manual approval
     * @return bool true if there exist scopes that require manual review by an administrator, false otherwise
     */
    public function hasScopesThatRequireManualApproval(bool $is_confidential_client, array $scopes)
    {
        // note eventually this method could have a db lookup to check against if admins want to vet this
        // possibly we could have an event dispatched here as well if we want someone to provide / extend that kind of functionality

        // if a public app requests the launch scope we also do not let them through unless they've been manually
        // authorized by an administrator user.
        if (!$is_confidential_client) {
            if (array_search("launch", $scopes) !== false) {
                return true;
            }
        }
        // as not all jurisdictions have to comply with ONC rules we will still check against the globals flag in case
        // a user has turned off auto-enabling of apps and wants to lock down their installation
        if (($GLOBALS['oauth_app_manual_approval'] ?? '0') == '1') {
            return true;
        }

        if ($is_confidential_client) {
            // ONC requires that a patient be allowed to use an app of their choice and as long as it does not use user/system scopes there can be
            // no prohibiting the patient app selection due to Information Blocking Rule, EMRs must authorize the app within 2 business days
            // to deal with this we auto-enable confidential apps that ONLY use patient/* scopes even if they request offline_access scope
            // we still prohibit any confidential app that is allowing an in-EHR context to be auto-enabled since they are listed inside
            // the patient demographics screen (and other locations possibly in the future)
            if ($this->hasUserScopes($scopes) || $this->hasSystemScopes($scopes)) {
                return true;
            }
        }
        return false;
    }
    private function hasUserScopes(array $scopes)
    {
        return $this->scopeArrayHasString($scopes, 'user/');
    }
    private function hasSystemScopes(array $scopes)
    {
        return $this->scopeArrayHasString($scopes, 'system/');
    }

    private function scopeArrayHasString(array $scopes, $str)
    {
        foreach ($scopes as $scope) {
            if (strpos($scope, $str) !== false) {
                return true;
            }
        }
        return false;
    }
}
