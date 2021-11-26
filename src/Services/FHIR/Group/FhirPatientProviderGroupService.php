<?php

/**
 * FhirPatientProviderGroupService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Group;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRGroup;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRGroup\FHIRGroupMember;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\GroupService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirPatientProviderGroupService extends FhirServiceBase
{
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new GroupService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->service->searchPatientProviderGroups($openEMRSearchParameters);
    }

    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $fhirGroup = new FHIRGroup();
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId("1");
        $fhirMeta->setLastUpdated(gmdate('c'));
        $fhirGroup->setMeta($fhirMeta);

        $fhirGroup->setId($dataRecord['uuid']);
        $fhirGroup->setName($dataRecord['name']);

        if (!empty($dataRecord['patients'])) {
            foreach ($dataRecord['patients'] as $patient) {
                $fhirGroupMember = new FHIRGroupMember();
                // we could display the name of the patient here in the group list... but I think for information leakage we will leave just the reference
                $fhirGroupMember->setEntity(UtilsService::createRelativeReference("Patient", $patient['uuid']));
                $fhirGroup->addMember($fhirGroupMember);
            }
        }
        return $fhirGroup;
    }
}
