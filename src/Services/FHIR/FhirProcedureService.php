<?php

/**
 * FHIR Procedure Service
 *
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @author             Stephen Nielson <stephen@nielson.org>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\FHIR\Enum\EventStatusEnum;
use OpenEMR\Services\FHIR\Procedure\FhirProcedureOEProcedureService;
use OpenEMR\Services\FHIR\Procedure\FhirProcedureSurgeryService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\MappedServiceTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\Traits\VersionedProfileTrait;
use OpenEMR\Services\ProcedureService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirProcedureService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use MappedServiceTrait;
    use PatientSearchTrait;
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;
    use VersionedProfileTrait;

    /**
     * @deprecated use EventStatusEnum::COMPLETED.
     */
    const FHIR_PROCEDURE_STATUS_COMPLETED = EventStatusEnum::COMPLETED->value;
    /**
     * @deprecated use EventStatusEnum::IN_PROGRESS.
     */
    const FHIR_PROCEDURE_STATUS_IN_PROGRESS = EventStatusEnum::IN_PROGRESS->value;

    /**
     * @deprecated use EventStatusEnum::STOPPED.
     */
    const FHIR_PROCEDURE_STATUS_STOPPED = EventStatusEnum::STOPPED->value;

    /**
     * @deprecated use EventStatusEnum::UNKNOWN.
     */
    const FHIR_PROCEDURE_STATUS_UNKNOWN = EventStatusEnum::UNKNOWN->value;

    const PROCEDURE_STATUS_COMPLETED = EventStatusEnum::COMPLETED->value;
    const PROCEDURE_STATUS_PENDING = "pending";
    const PROCEDURE_STATUS_CANCELLED = "cancelled";
    const USCGI_PROFILE_URI = "http://hl7.org/fhir/us/core/StructureDefinition/us-core-procedure";


    /**
     * @var ProcedureService
     */
    private $procedureService;

    public function __construct()
    {
        parent::__construct();
        $this->addMappedService(new FhirProcedureOEProcedureService());
        $this->addMappedService(new FhirProcedureSurgeryService());
    }

    /**
     * Returns an array mapping FHIR Procedure Resource search parameters to OpenEMR Procedure search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            'patient' => $this->getPatientContextSearchField(),
            'date' => new FhirSearchParameterDefinition('date', SearchFieldType::DATETIME, ['report_date']),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('order_uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
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

            $fhirSearchResult = $this->searchAllServices($fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error("FhirServiceBase->getAll() exception thrown", ['message' => $exception->getMessage(),
                'field' => $exception->getField()]);
            // put our exception information here
            $fhirSearchResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }
        return $fhirSearchResult;
    }

    /**
     * Returns the Canonical URIs for the FHIR resource for each of the US Core Implementation Guide Profiles that the
     * resource implements.  Most resources have only one profile, but several like DiagnosticReport and Observation
     * has multiple profiles that must be conformed to.
     * @see https://www.hl7.org/fhir/us/core/CapabilityStatement-us-core-server.html for the list of profiles
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return $this->getProfileForVersions(self::USCGI_PROFILE_URI, $this->getSupportedVersions());
    }
}
