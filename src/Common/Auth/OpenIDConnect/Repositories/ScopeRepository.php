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
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ScopeEntity;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\RestControllers\RestControllerHelper;
use Psr\Log\LoggerInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct()
    {
        $this->logger = SystemLogger::instance();
    }

    /**
     * @param string $identifier
     * @return ScopeEntity|null
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntity
    {
        $validationScopes = $this->buildScopeValidatorArray();

        if (array_key_exists($identifier, $validationScopes) === false && stripos($identifier, 'site:') === false) {
            $this->logger->error("ScopeRepository->getScopeEntityByIdentifier() request access to invalid scope", ["scope" => $identifier]);
            return null;
        }

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
        // If a nonce is passed in, add a nonce scope for id token nonce claim
        if (!empty($_SESSION['nonce'])) {
            $scope = new ScopeEntity();
            $scope->setIdentifier('nonce');
            $scopes[] = $scope;
        }
        // Need a site id for our apis
        if ($_SESSION['site_id']) {
            $siteScope = "site:" . $_SESSION['site_id'];
        } else {
            $siteScope = "site:default";
        }
        $scope = new ScopeEntity();
        $scope->setIdentifier($siteScope);
        $scopes[] = $scope;

        return $scopes;
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
            "api:pofh",
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
            "api:port",
            "api:pofh"
        ];
    }

    public function fhirRequiredSmartScopes(): array
    {
        return [
            "openid",
            "fhirUser",
            "online_access",
            "offline_access",
            "launch",
            "launch/patient",
            "api:oemr",
            "api:fhir",
            "api:port",
            "api:pofh"
        ];
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
        $permitted = [
            "patient/Account.read",
            "patient/AllergyIntolerance.read",
            "patient/AllergyIntolerance.write",
            "patient/Appointment.read",
            "patient/Appointment.write",
            "patient/CarePlan.read",
            "patient/CareTeam.read",
            "patient/Condition.read",
            "patient/Condition.write",
            "patient/Consent.read",
            "patient/Coverage.read",
            "patient/Coverage.write",
            "patient/Device.read",
            "patient/DocumentReference.read",
            "patient/DocumentReference.write",
            "patient/Encounter.read",
            "patient/Encounter.write",
            "patient/Goal.read",
            "patient/Immunization.read",
            "patient/Immunization.write",
            "patient/Location.read",
            "patient/MedicationRequest.read",
            "patient/MedicationRequest.write",
            "patient/NutritionOrder.read",
            "patient/Observation.read",
            "patient/Observation.write",
            "patient/Organization.read",
            "patient/Organization.write",
            "patient/Patient.read",
            "patient/Patient.write",
            "patient/Person.read",
            "patient/Procedure.read",
            "patient/Procedure.write",
            "patient/Provenance.read",
            "patient/Provenance.write",
            "patient/RelatedPerson.read",
            "patient/RelatedPerson.write",
            "patient/Schedule.read",
            "patient/ServiceRequest.read",
            "user/Account.read",
            "user/AllergyIntolerance.read",
            "user/AllergyIntolerance.write",
            "user/Appointment.read",
            "user/Appointment.write",
            "user/CarePlan.read",
            "user/CareTeam.read",
            "user/Condition.read",
            "user/Condition.write",
            "user/Consent.read",
            "user/Coverage.read",
            "user/Coverage.write",
            "user/DocumentReference.read",
            "user/DocumentReference.write",
            "user/Encounter.read",
            "user/Encounter.write",
            "user/Goal.read",
            "user/Immunization.read",
            "user/Immunization.write",
            "user/Location.read",
            "user/MedicationRequest.read",
            "user/MedicationRequest.write",
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
            "user/Procedure.read",
            "user/Procedure.write",
            "user/Provenance.read",
            "user/Provenance.write",
            "user/RelatedPerson.read",
            "user/RelatedPerson.write",
            "user/Schedule.read",
            "user/ServiceRequest.read"
        ];

        return array_merge($permitted, $this->systemScopes());
    }

    public function systemScopes(): array
    {
        return [
            "system/Account.read",
            "system/AllergyIntolerance.read",
            "system/AllergyIntolerance.write",
            "system/Appointment.read",
            "system/Appointment.write",
            "system/CarePlan.read",
            "system/CareTeam.read",
            "system/Condition.read",
            "system/Condition.write",
            "system/Consent.read",
            "system/Coverage.read",
            "system/Coverage.write",
            "system/DocumentReference.read",
            "system/DocumentReference.write",
            "system/Encounter.read",
            "system/Encounter.write",
            "system/Goal.read",
            "system/Immunization.read",
            "system/Immunization.write",
            "system/Location.read",
            "system/MedicationRequest.read",
            "system/MedicationRequest.write",
            "system/NutritionOrder.read",
            "system/Observation.read",
            "system/Observation.write",
            "system/Organization.read",
            "system/Organization.write",
            "system/Patient.read",
            "system/Patient.write",
            "system/Person.read",
            "system/Practitioner.read",
            "system/Practitioner.write",
            "system/Procedure.read",
            "system/Procedure.write",
            "system/Provenance.read",
            "system/Provenance.write",
            "system/RelatedPerson.read",
            "system/RelatedPerson.write",
            "system/Schedule.read",
            "system/ServiceRequest.read",
        ];
    }

    public function apiScopes(): array
    {
        return [
            "patient/allergy.read",
            "patient/appointment.read",
            "patient/drug.read",
            "patient/facility.read",
            "patient/facility.write",
            "patient/immunization.read",
            "patient/insurance_company.read",
            "patient/insurance_company.write",
            "patient/list.read",
            "patient/medical_problem.read",
            "patient/patient.read",
            "patient/patient.write",
            "patient/practitioner.read",
            "patient/practitioner.write",
            "patient/prescription.read",
            "patient/procedure.read",
            "system/allergy.read",
            "system/appointment.read",
            "system/drug.read",
            "system/facility.read",
            "system/facility.write",
            "system/immunization.read",
            "system/insurance_company.read",
            "system/insurance_company.write",
            "system/list.read",
            "system/medical_problem.read",
            "system/patient.read",
            "system/patient.write",
            "system/practitioner.read",
            "system/practitioner.write",
            "system/prescription.read",
            "system/procedure.read",
            "user/allergy.read",
            "user/appointment.read",
            "user/drug.read",
            "user/facility.read",
            "user/facility.write",
            "user/immunization.read",
            "user/insurance_company.read",
            "user/insurance_company.write",
            "user/list.read",
            "user/medical_problem.read",
            "user/patient.read",
            "user/patient.write",
            "user/practitioner.read",
            "user/practitioner.write",
            "user/prescription.read",
            "user/procedure.read"
        ];
    }

    public function getOidcSupportedScopes(): array
    {
        return $this->oidcScopes();
    }

    public function getFhirSupportedScopes($role = 'user'): array
    {
        $permitted = $this->fhirScopes();
        $standard = null;
        if ($role === 'user') {
            $standard = array_merge($this->fhirRequiredSmartScopes(), $permitted);
        }
        if ($role === 'patient') {
            $standard = $this->fhirRequiredSmartScopes();
            foreach ($permitted as $readOnly) {
                if (stripos($readOnly, '.read') === false) {
                    continue;
                }
                $standard[] = $readOnly;
            }
        }

        return $standard;
    }

    public function getSystemFhirSupportedScopes(): array
    {
        return $this->systemScopes();
    }

    public function getStandardApiSupportedScopes(): array
    {
        return $this->apiScopes();
    }

    /**
     * Method will qualify current scopes based on active FHIR resources.
     * Allowed permissions are validated from the default scopes and client role.
     *
     * @param string $role
     * @return array
     */
    public function getCurrentSmartScopes($role = 'user'): array
    {
        $gbl = \RestConfig::GetInstance();
        $restHelper = new RestControllerHelper();
        $routes = $gbl::$FHIR_ROUTE_MAP;
        $restAPIs = $restHelper->getCapabilityRESTJSON($routes, "http://hl7.org/fhir/StructureDefinition/");
        // Collect all currently enabled FHIR resources.
        // Then assign all permissions the resource is capable.
        $scopes_api = null;
        foreach ($restAPIs as $resources) {
            foreach ($resources as $resource) {
                $interactions = $resource['interaction'];
                $resourceType = $resource['type'];
                foreach ($interactions as $interaction) {
                    $scopeRead = $resourceType . ".read";
                    $scopeWrite = $resourceType . ".write";
                    switch ($interaction['code']) {
                        case 'read':
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
            }
        }
        $scopesSupported = $this->fhirScopes();
        $scopes_dict = array_combine($scopesSupported, $scopesSupported);
        $fhir = array_combine($this->fhirRequiredSmartScopes(), $this->fhirRequiredSmartScopes());
        $oidc = array_combine($this->oidcScopes(), $this->oidcScopes());
        $scopesSupported = null;
        // verify scope permissions are allowed for role being used.
        foreach ($scopes_api as $key => $scope) {
            if (empty($scopes_dict[$key])) {
                continue;
            }
            $scopesSupported[$key] = $scope;
        }
        asort($scopesSupported);
        $scopesSupported = array_keys(array_merge($fhir, $oidc, $scopesSupported));

        return $scopesSupported;
    }

    public function getCurrentStandardScopes($role = 'user'): array
    {
        $gbl = \RestConfig::GetInstance();
        $restHelper = new RestControllerHelper();
        $routes = $gbl::$ROUTE_MAP;
        $restAPIs = $restHelper->getCapabilityRESTJSON($routes, "OpenEMR\\Services", "http://hl7.org/fhir/StructureDefinition/");
        // Collect all currently enabled FHIR resources.
        // Then assign all permissions the resource is capable.
        $scopes_api = null;
        foreach ($restAPIs as $resources) {
            foreach ($resources as $resource) {
                $interactions = $resource['interaction'];
                $resourceType = $resource['type'];
                foreach ($interactions as $interaction) {
                    $scopeRead = $resourceType . ".read";
                    $scopeWrite = $resourceType . ".write";
                    switch ($interaction['code']) {
                        case 'read':
                            $scopes_api['patient/' . $scopeRead] = 'patient/' . $scopeRead;
                            $scopes_api['user/' . $scopeRead] = 'user/' . $scopeRead;
                            $scopes_api['system/' . $scopeRead] = 'system/' . $scopeRead;
                            break;
                        case 'put':
                        case 'insert':
                        case 'update':
                            $scopes_api['patient/' . $scopeWrite] = 'patient/' . $scopeWrite;
                            $scopes_api['user/' . $scopeWrite] = 'user/' . $scopeWrite;
                            $scopes_api['system/' . $scopeWrite] = 'system/' . $scopeWrite;
                            break;
                    }
                }
            }
        }
        $scopesSupported = $this->apiScopes();
        $scopes_dict = array_combine($scopesSupported, $scopesSupported);
        $scopesSupported = null;
        // verify scope permissions are allowed for role being used.
        foreach ($scopes_api as $key => $scope) {
            if (empty($scopes_dict[$key])) {
                continue;
            }
            $scopesSupported[$key] = $scope;
        }
        asort($scopesSupported);

        return array_keys($scopesSupported);
    }

    // made public for now!
    public function buildScopeValidatorArray(): array
    {
        $role = $_SESSION['client_role'] ?? 'user';
        $isFhir = preg_match('(fhirUser|api:fhir|api:pofh)', $_REQUEST['scope'])
            || preg_match('(fhirUser|api:fhir|api:pofh)', $_SESSION['scopes']);

        $scopes = null;
        if (!empty($isFhir)) {
            $scopes = $this->getCurrentSmartScopes($role);
        } else {
            $scopes = $this->getOidcSupportedScopes();
        }

        foreach ($scopes as $scope) {
            $scopes[$scope] = ['description' => 'OpenId Connect'];
        }

        return $scopes;
    }
}
