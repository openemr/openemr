<?php

/**
 * FhirDiagnosticReportService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FHIR\DiagnosticReport\FhirDiagnosticReportClinicalNotesService;
use OpenEMR\Services\FHIR\DiagnosticReport\FhirDiagnosticReportLaboratoryService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceCodeTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirDiagnosticReportService extends FhirServiceBase implements IPatientCompartmentResourceService, IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use MappedServiceCodeTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->addMappedService(new FhirDiagnosticReportClinicalNotesService($fhirApiURL));
        $this->addMappedService(new FhirDiagnosticReportLaboratoryService($fhirApiURL));
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
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
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
        try {
            if (isset($puuidBind)) {
                $field = $this->getPatientContextSearchField();
                $fhirSearchParameters[$field->getName()] = $puuidBind;
            }

            if (isset($fhirSearchParameters['category'])) {
                /**
                 * @var TokenSearchField
                 */
                $category = $fhirSearchParameters['category'];
                $categorySearchField = new TokenSearchField('category', $category);
                $service = $this->getServiceForCategory(
                    $categorySearchField,
                    'LAB'
                );
                $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
            } else if (isset($fhirSearchParameters['code'])) {
                $service = $this->getServiceForCode(
                    new TokenSearchField('code', $fhirSearchParameters['code']),
                    ''
                );
                // if we have a service let's search on that
                if (isset($service)) {
                    $fhirSearchResult = $service->getAll($fhirSearchParameters, $puuidBind);
                } else {
                    $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
                }
            } else {
                $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
            }
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    public function getProfileURIs(): array
    {
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-diagnosticreport-note'
            ,'http://hl7.org/fhir/us/core/StructureDefinition/us-core-diagnosticreport-lab'
        ];
    }
}
