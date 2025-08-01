<?php
/*
 * ServerScopeListEntity.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth\OpenIDConnect\Entities;

class ServerScopeListEntity {

    private bool $systemScopesEnabled = false;

    private array $v1ResourceFhirScopes = [];

    private array $v2ResourceFhirScopes = [];

    private array $v1ApiScopes = [];

    private array $v2ApiScopes = [];

    public function __construct()
    {
    }

    public function setSystemScopesEnabled(bool $enabled): void
    {
        $this->systemScopesEnabled = $enabled;
        // reset the cached scopes so we can rebuild them
        $this->clearCachedScopes();
    }

    private function clearCachedScopes(): void
    {
        $this->v1ResourceFhirScopes = [];
        $this->v2ResourceFhirScopes = [];
        $this->v1ApiScopes = [];
        $this->v2ApiScopes = [];
    }

    public function requiredSmartOnFhirScopes(): array
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
        if ($this->systemScopesEnabled) {
            $requiredSmart[] = 'system/Patient.$export';
            $requiredSmart[] = 'system/Group.$export';
            $requiredSmart[] = 'system/*.$bulkdata-status';
            $requiredSmart[] = 'system/*.$export';
        }
        return $requiredSmart;
    }


    public function fhirResourceScopesV1(): array
    {
        if (empty($this->v1ResourceFhirScopes)) {
            $fhirReadResources = [
                'AllergyIntolerance',
                'Appointment',
                'CarePlan',
                'CareTeam',
                'Condition',
                'Coverage',
                'Device',
                'DiagnosticReport',
                'DocumentReference',
                'Binary',
                'Encounter',
                'Goal',
                'Group',
                'Immunization',
                'Location',
                'Medication',
                'MedicationRequest',
                'Observation',
                'Organization',
                'Patient',
                'Person',
                'Practitioner',
                'PractitionerRole',
                'Procedure',
                'Provenance',
                'ValueSet',
                'OperationDefinition',
            ];
            $fhirWriteResources = [
                'Patient'
                , 'Practitioner'
                , 'Organization'
            ];
            $fhirScopes = [];
            $systemEnabled = $this->systemScopesEnabled;
            foreach ($fhirReadResources as $resource) {
                $fhirScopes[] = "patient/$resource.read";
                $fhirScopes[] = "user/$resource.read";
                if ($systemEnabled) {
                    $fhirScopes[] = "system/$resource.read";
                }
            }
            foreach ($fhirWriteResources as $resource) {
                $fhirScopes[] = "user/$resource.write";
            }

            $fhirScopes[] = 'patient/DocumentReference.$docref';
            $fhirScopes[] = 'user/DocumentReference.$docref';

            $this->v1ResourceFhirScopes = $fhirScopes;
        }
        return $this->v1ResourceFhirScopes;
    }

    public function fhirResourceScopesV2(): array
    {
        if (empty($this->v2ResourceFhirScopes)) {
            $resources = [
                'AllergyIntolerance',
                'CarePlan',
                'CareTeam',
                'Condition',
                'Coverage',
                'Device',
                'DiagnosticReport',
                'DocumentReference',
                'Encounter',
                'Goal',
                'Immunization',
                'MedicationDispense',
                'MedicationRequest',
                'Observation',
                'Organization',
                'Patient',
                'Practitioner',
                'PractitionerRole',
                'Procedure',
                'Provenance',
                'QuestionnaireResponse',
                'RelatedPerson',
                'ServiceRequest',
                'Specimen'
            ];
            $scopes = [];
            $systemEnabled = $this->systemScopesEnabled;
            foreach ($resources as $resource) {
                // we'll ignore write for now
                $scopes[] = "patient/$resource.rs";
                $scopes[] = "user/$resource.rs";
                if ($systemEnabled) {
                    $scopes[] = "system/$resource.rs";
                }
            }
            // now add the restrictions
            $restrictedScopes = [
                'Condition' => [
                    'category=http://hl7.org/fhir/us/core/CodeSystem/condition-category|health-concern'
                    , 'category=http://terminology.hl7.org/CodeSystem/condition-category|encounter-diagnosis'
                    , 'category=http://terminology.hl7.org/CodeSystem/condition-category|problem-list-item'
                ]
                , 'Observation' => [
                    'category=http://hl7.org/fhir/us/core/CodeSystem/us-core-category|sdoh'
                    , 'category=http://terminology.hl7.org//CodeSystem-observation-category|social-history'
                    , 'category=http://terminology.hl7.org/CodeSystem/observation-category|laboratory'
                    , 'category=http://terminology.hl7.org/CodeSystem/observation-category|survey'
                    , 'category=http://terminology.hl7.org/CodeSystem/observation-category|vital-signs'
                ]
                , 'DocumentReference' => [
                    'category=http://hl7.org/fhir/us/core/CodeSystem/us-core-documentreference-category|clinical-note'
                ]
            ];
            foreach ($restrictedScopes as $resource => $restrictions) {
                foreach ($restrictions as $restriction) {
                    $scopes[] = "patient/$resource.rs?$restriction";
                    $scopes[] = "user/$resource.rs?$restriction";
                    if ($systemEnabled) {
                        $scopes[] = "system/$resource.rs?$restriction";
                    }
                }
            }
            $this->v2ResourceFhirScopes = $scopes;
        }
        return $this->v2ResourceFhirScopes;
    }

    public function apiScopes(): array
    {
        if (empty($this->v1ApiScopes)) {
            $this->v1ApiScopes = [
                "patient/patient.read",
                "patient/appointment.read",
                "patient/encounter.read",
                "user/allergy.read",
                "user/allergy.write",
                "user/appointment.read",
                "user/appointment.write",
                "user/dental_issue.read",
                "user/dental_issue.write",
                "user/document.read",
                "user/document.write",
                "user/drug.read",
                "user/employer.read",
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
                "user/message.write",
                "user/patient.read",
                "user/patient.write",
                "user/practitioner.read",
                "user/practitioner.write",
                "user/prescription.read",
                "user/procedure.read",
                "user/product.read",
                "user/soap_note.read",
                "user/soap_note.write",
                "user/surgery.read",
                "user/surgery.write",
                "user/transaction.read",
                "user/transaction.write",
                "user/user.read",
                "user/version.read",
                "user/vital.read",
                "user/vital.write",
            ];
        }
        return $this->v1ApiScopes;
    }

    public function getV2ApiScopes() : array {
        if (empty($this->v2ApiScopes)) {
            // if the scope format changes... we want to keep these separate
            // yet, its not as efficient to do so many implodes.
            $userResources = [
                'allergy' => ['c','r','u','d','s']
                ,'appointment' => ['c','r','u','d','s']
                ,'dental_issue' => ['c','r','u','d','s']
                ,'document' => ['c','r','s']
                ,'drug' => ['r','s']
                ,'employer' => ['s']
                ,'encounter' => ['c','r','u','s']
                ,'facility' => ['c', 'r', 'u','s']
                ,'immunization' => ['r','s']
                ,'insurance' => ['c','r','u','s']
                ,'insurance_company' => ['c','r','u','s']
                ,'insurance_type' => ['s']
                ,'list' => ['r']
                ,'medical_problem' => ['c','r','u','d','s']
                ,'medication' => ['c','r','u','d','s']
                ,'message' => ['c','u','d']
                ,'patient' => ['c','r','u','s']
                ,'practitioner' => ['c','r','u','s']
                ,'prescription' => ['r','s']
                ,'procedure' => ['r','s']
                ,'product' => ['s']
                ,'soap_note' => ['c','r','u','s']
                ,'surgery' => ['c','r','u','d','s']
                ,'transaction' => ['c','u','d','s']
                ,'user' => ['r','s']
                ,'version' => ['s']
                ,'vital' => ['c','r','u','s']
            ];
            $scopes = [];
            foreach ($userResources as $resource => $actions) {
                $actionString = implode('', $actions);
                $scopes[] = "user/$resource.$actionString";
            }
            $scopes[] = 'user/insurance.$swap-insurance';
            $patientResources = [
                'patient' => ['s']
                ,'encounter' => ['r','s']
                ,'appointment' => ['r','s']
            ];
            foreach ($patientResources as $resource => $actions) {
                $actionString = implode('', $actions);
                $scopes[] = "patient/$resource.$actionString";
            }
            $this->v2ApiScopes = $scopes;
        }
        return $this->v2ApiScopes;
    }

    public function getOpenIDConnectScopes() : array
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

    public function getAllSupportedScopesList() : array {

        $oidcScopes = $this->getOpenIDConnectScopes();
        $requiredSmartScopes = $this->requiredSmartOnFhirScopes();
        $standardApiScopes = $this->apiScopes();
        $standardApiScopesV2 = $this->getV2ApiScopes();
        $resourceV1Scopes = $this->fhirResourceScopesV1();
        $resourceV2Scopes = $this->fhirResourceScopesV2();
        $allScopes = array_merge($oidcScopes, $requiredSmartScopes, $resourceV1Scopes, $resourceV2Scopes, $standardApiScopes, $standardApiScopesV2);
        return array_keys(array_combine($allScopes, $allScopes));
    }
}
