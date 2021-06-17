<?php

/**
 * FhirDiagnosticReportService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * FhirDiagnosticReportService
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;


use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRDiagnosticReport;
use OpenEMR\Services\FHIR\DocumentReference\FhirClinicalNotesService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirDiagnosticReportService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use MappedServiceTrait;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->addMappedService(new FhirClinicalNotesService());
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, ['uuid']),
        ];
    }

    /**
     * Retrieves all of the fhir observation resources mapped to the underlying openemr data elements.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return processing result
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        $diagnosticReport = new FHIRDiagnosticReport();
        $diagnosticReport->setStatus("registered");
        $diagnosticReport->addCategory(UtilsService::createCodeableConcept(["LP29684-5" => "Radiology"], FhirCodeSystemUris::LOINC));
        $diagnosticReport->setCode(UtilsService::createCodeableConcept(
            ["1000-9" => "DBG Ab [Presence] in Serum or Plasma from Blood product unit"],
            FhirCodeSystemUris::LOINC
        ));
        $diagnosticReport->setSubject(UtilsService::createRelativeReference("Patient", ""));
        $diagnosticReport->setEffectiveDateTime(gmdate('c'));
        $diagnosticReport->setIssued(gmdate('c'));
        $diagnosticReport->addPerformer(UtilsService::createRelativeReference("Practitioner", ""));
        $fhirSearchResult->addData($diagnosticReport);
        return $fhirSearchResult;
//        try {
//            if (isset($fhirSearchParameters['_id'])) {
//                $result = $this->populateSurrogateSearchFieldsForUUID($fhirSearchParameters['_id'], $fhirSearchParameters);
//                if ($result instanceof ProcessingResult) { // failed to populate so return the results
//                    return $result;
//                }
//            }
//
//            if (isset($puuidBind)) {
//                $field = $this->getPatientContextSearchField();
//                $fhirSearchParameters[$field->getName()] = $puuidBind;
//            }
//
//            if (isset($fhirSearchParameters['category'])) {
//                /**
//                 * @var TokenSearchField
//                 */
//                $category = $fhirSearchParameters['category'];
//
//                $service = $this->getServiceForCategory($category, 'clinical-notes');
//                $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
//            } else if (isset($fhirSearchParameters['code'])) {
//                // TODO: @adunsulag should there be a default code here?  Look at the method signature
//                $service = $this->getServiceForCode($fhirSearchParameters['code'], '');
//                // if we have a service let's search on that
//                if (isset($service)) {
//                    $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
//                } else {
//                    $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
//                }
//            } else {
//                $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
//            }
//        } catch (SearchFieldException $exception) {
//            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
//                'field' => $exception->getField()]);
//            // put our exception information here
//            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
//        }
//        return $fhirSearchResult;
    }

    public function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-diagnosticreport-note'
        ];
    }
}
