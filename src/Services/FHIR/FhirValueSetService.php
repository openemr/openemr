<?php

/**
 * FhirDeviceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Robert Jones (Analog Informatics Corporation) <robert@analoginfo.com>, <robert@justjones.org>
 * @copyright Copyright (c) 2023 Analog Informatics Corporation <https://analoginfo.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRValueSet;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetCompose;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetInclude;
use OpenEMR\FHIR\R4\FHIRResource\FHIRValueSet\FHIRValueSetConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirValueSetService extends FhirServiceBase implements IResourceUSCIGProfileService, IFhirExportableResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var AppointmentService
     */
    private $appointmentService;

    /**
     * @var ListService
     */
    private $listOptionService;

    /**
     * NB: started w Device as a base, but DocumentReference is what I needed... no underlying service either
     *  pc_catid => openemr_postcalendar_categories
     *  doc category => categories
     *   facility POS code
     *  customlists ??
     *  option_lists by list_id
     *
     *  MariaDB [openemr]> select list_id    , option_id         , title                 , seq , is_default , option_value , mapping , notes, codes from list_options limit 40;
     *  +---------------------------------+-------------------+-----------------------+-----+------------+--------------+---------+-------+-------+
     *  | list_id                         | option_id         | title                 | seq | is_default | option_value | mapping | notes | codes |
     *  +---------------------------------+-------------------+-----------------------+-----+------------+--------------+---------+-------+-------+
     *  | abook_type                      | bill_svc          | Billing Service       | 120 |          0 |            3 |         | NULL  |       |
     *  | abook_type                      | ccda              | Care Coordination     |  35 |          0 |            2 |         | NULL  |       |
     *  | abook_type                      | dist              | Distributor           |  30 |          0 |            3 |         | NULL  |       |
     *  | abook_type                      | emr_direct        | EMR Direct            | 105 |          0 |            4 |         | NULL  |       |
     *  | abook_type                      | external_org      | External Organization | 120 |          0 |            1 |         | NULL  |       |
     *  | abook_type                      | external_provider | External Provider     | 110 |          0 |            1 |         | NULL  |       |
     *  | abook_type                      | ord_img           | Imaging Service       |   5 |          0 |            3 |         | NULL  |       |
     *  | abook_type                      | ord_imm           | Immunization Service  |  10 |          0 |            3 |         | NULL  |       |
     *  | abook_type                      | ord_lab           | Lab Service           |  15 |          0 |            3 |         | NULL  |       |
     *  | abook_type                      | oth               | Other                 |  95 |          0 |            1 |         | NULL  |       |
     *  | abook_type                      | spe               | Specialist            |  20 |          0 |            2 |         | NULL  |       |
     *  | abook_type                      | vendor            | Vendor                |  25 |          0 |            3 |         | NULL  |       |
     *  | address-types                   | both              | Postal & Physical     |  30 |          0 |            0 |         | NULL  |       |
     *  | address-types                   | physical          | Physical              |  20 |          0 |            0 |         | NULL  |       |
     *  | address-types                   | postal            | Postal                |  10 |          0 |            0 |         | NULL  |       |
     *  | address-uses                    | billing           | Billing               |  50 |          0 |            0 |         | NULL  |       |
     *  | address-uses                    | home              | Home                  |  10 |          0 |            0 |         | NULL  |       |
     *  | address-uses                    | old               | Old/Incorrect         |  40 |          0 |            0 |         | NULL  |       |
     *  | address-uses                    | temp              | Temporary             |  30 |          0 |            0 |         | NULL  |       |
     *  | address-uses                    | work              | Work                  |  20 |          0 |            0 |         | NULL  |       |
     *  | adjreason                       | Adm adjust        | Adm adjust            |   5 |          0 |            1 |         | NULL  |       |
     *  | adjreason                       | After hrs calls   | After hrs calls       |  10 |          0 |            1 |         | NULL  |       |
     *  | adjreason                       | Bad check         | Bad check             |  15 |          0 |            1 |         | NULL  |       |
     *  | adjreason                       | Bad debt          | Bad debt              |  20 |          0 |            1 |         | NULL  |       |
     *  | adjreason                       | Coll w/o          | Coll w/o              |  25 |          0 |            1 |         | NULL  |       |
     *
     */


    const USCGI_PROFILE_URI = 'http://hl7.org/fhir/StructureDefinition/shareablevalueset';
    const APPOINTMENT_TYPE = 'appointment-type';

    public function __construct()
    {
        parent::__construct();
        // TODO: @adunsulag we need to look at adding a mapping service here in order to get our value sets out.
        $this->appointmentService = new AppointmentService();
        $this->listOptionService = new ListService();
    }

    /**
     * Returns an array mapping FHIR Resource search parameters to OpenEMR search parameters
     */
    protected function loadSearchParameters()
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('id', ServiceField::TYPE_STRING)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['sublist_updated_date', 'last_updated']);
    }

    private function getLastModifiedSearchFieldForAppointmentCategories()
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['pc_last_updated']);
    }

    /**
     * Retrieves all of the fhir observation resources mapped to the underlying openemr data elements.
     * @param $fhirSearchParameters The FHIR resource search parameters
     * @return processing result
     */
    public function getAll($fhirSearchParameters, $puuidBind = null): ProcessingResult
    {
        $fhirSearchResult = new ProcessingResult();
        try {
            // we don't really deal with provenance for ValueSet pieces so we will ignore this property
            if (isset($fhirSearchParameters['_revinclude'])) {
                unset($fhirSearchParameters['_revinclude']);
            }

            $this->addAppointmentCategoriesValueSetForSearch($fhirSearchResult, $fhirSearchParameters);

            $this->addListOptionsValueSetsForSearch($fhirSearchResult, $fhirSearchParameters, $puuidBind);
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->errorLogCaller("search exception thrown", ['message' => $exception->getMessage(),
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
    function getProfileURIs(): array
    {
        return [self::USCGI_PROFILE_URI];
    }

    private function addAppointmentCategoriesValueSetForSearch(ProcessingResult $fhirSearchResult, array $fhirSearchParameters, string $puuidBind = null)
    {
        $this->getSearchFieldFactory()->setSearchFieldDefinition('_lastUpdated', $this->getLastModifiedSearchFieldForAppointmentCategories());
        $oeSearchParameters = $this->createOpenEMRSearchParameters($fhirSearchParameters, $puuidBind);
        if (
            !isset($oeSearchParameters['_id'])
            // could be array (AND) or comma-delimited string value (OR)
            // check array first but should only be len 1 ("AND", becuase cannot be 2 simultaneous)
            || $oeSearchParameters['_id']->hasCodeValue(self::APPOINTMENT_TYPE)
        ) {
            if (!isset($oeSearchParameters['_id'])) {
                // if we have any match on categories we want to return everything... hate the double db call
                // but rather than mess with a complex query we will just do it this way
                $processingResult = $this->appointmentService->searchCalendarCategories($oeSearchParameters);
                // nothing to do here as we have no categories matching so we return
                if (!$processingResult->hasData()) {
                    return $fhirSearchResult;
                }
            }
            $calendarCategories = $this->appointmentService->getCalendarCategories();
            $valueSet = new FHIRValueSet();
            $valueSet->setId(self::APPOINTMENT_TYPE);
            $compose = new FHIRValueSetCompose();
            $include = new FHIRValueSetInclude();
            foreach ($calendarCategories as $category) {
                if ($category["pc_cattype"] != 0) {
                    continue; // only cat_type==0
                }
                $concept = new FHIRValueSetConcept();
                $code = new FHIRCode();
                $code->setValue($category["pc_constant_id"]);
                $concept->setCode($code);
                $concept->setDisplay($category["pc_catname"]);
                $include->addConcept($concept);
            }
            $compose->addInclude($include);
            $valueSet->setCompose($compose);
            $fhirSearchResult->addData($valueSet);
        }
        return $fhirSearchResult;
    }

    private function addListOptionsValueSetsForSearch(ProcessingResult $fhirSearchResult, array $fhirSearchParameters, ?string $puuidBind = null)
    {
        $this->getSearchFieldFactory()->setSearchFieldDefinition('_lastUpdated', $this->getLastModifiedSearchField());
        $oeSearchParameters = $this->createOpenEMRSearchParameters($fhirSearchParameters, $puuidBind);

        // Now the same for list_options selected in $listNames
        $listsResult = $this->listOptionService->searchLists($oeSearchParameters);
        if (!$listsResult->hasData()) {
            $fhirSearchResult->addProcessingResult($listsResult);
            return $fhirSearchResult;
        }
        foreach ($listsResult->getData() as $listRecord) {
            $listName = $listRecord["option_id"];
            $options = $this->listOptionService->getOptionsByListName($listName); // does not return title
            if (count($options) == 0) {
                continue;
            }
            $valueSet = new FHIRValueSet();
            $valueSet->setId($listName);
            $compose = new FHIRValueSetCompose();
            $include = new FHIRValueSetInclude();
            foreach ($options as $option) {
                $concept = new FHIRValueSetConcept();
                $code = new FHIRCode();
                $code->setValue($option["option_id"]);
                $concept->setCode($code);
                $concept->setDisplay($option["title"]);
                $include->addConcept($concept);
            }
            $compose->addInclude($include);
            $valueSet->setCompose($compose);
            $fhirSearchResult->addData($valueSet);
        }
        return $fhirSearchResult;
    }
}
