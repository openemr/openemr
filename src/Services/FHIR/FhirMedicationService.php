<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedication;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRMedication\FHIRMedicationBatch;
use OpenEMR\Services\DrugService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Medication Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirMedicationService
 * @package            OpenEMR
 * @link               http://www.open-emr.org
 * @author             Yash Bothra <yashrajbothra786gmail.com>
 * @copyright          Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license            https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirMedicationService extends FhirServiceBase implements IResourceUSCIGProfileService
{
    /**
     * @var MedicationService
     */
    private $medicationService;

    public function __construct()
    {
        parent::__construct();
        $this->medicationService = new DrugService();
    }

    /**
     * Returns an array mapping FHIR Medication Resource search parameters to OpenEMR Medication search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
        ];
    }

    /**
     * Parses an OpenEMR medication record, returning the equivalent FHIR Medication Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param  boolean $encode     Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRMedication
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $medicationResource = new FHIRMedication();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $medicationResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $medicationResource->setId($id);

        if ($dataRecord['active'] == '1') {
            $medicationResource->setStatus("active");
        } else {
            $medicationResource->setStatus("inactive");
        }

        if (!empty($dataRecord['drug_code'])) {
            $medicationCoding = new FHIRCoding();
            $medicationCode = new FHIRCodeableConcept();
            foreach ($dataRecord['drug_code'] as $code => $codeValues) {
                $medicationCoding->setSystem($codeValues['system']);
                $medicationCoding->setCode($code);
                $medicationCoding->setDisplay($codeValues['description']);
                $medicationCode->addCoding($medicationCoding);
            }
            $medicationResource->setCode($medicationCode);
        }

        //alternative for switch case
        list($formDisplay, $formCode) = [
            '1' => ['suspension', 'C60928'],
            '2' => ['tablet', 'C42998'],
            '3' => ['capsule', 'C25158'],
            '4' => ['solution', 'C42986'],
            '5' => ['tsp', 'C48544'],
            '6' => ['ml', 'C28254'],
            '7' => ['units', 'C44278'],
            '8' => ['inhalation', 'C42944'],
            '9' => ['gtts(drops)', 'C48491'],
            '10' => ['cream', 'C28944'],
            '11' => ['ointment', 'C42966'],
            '12' => ['puff', 'C42944']
        ][$dataRecord['form']] ?? ['', ''];

        if (!empty($formCode)) {
            $form = new FHIRCodeableConcept();
            $formCoding = new FHIRCoding();
            $formCoding->setSystem("http://ncimeta.nci.nih.gov");
            $formCoding->setCode($formCode);
            $formCoding->setDisplay($formDisplay);
            $form->addCoding($formCoding);
        }

        if (isset($dataRecord['expiration']) || isset($dataRecord['expiration'])) {
            $batch = new FHIRMedicationBatch();
            if (isset($dataRecord['expiration'])) {
                $expirationDate = new FHIRDateTime();
                $expirationDate->setValue($dataRecord['expiration']);
                $batch->setExpirationDate($expirationDate);
            }
            if (isset($dataRecord['lot_number'])) {
                $batch->setLotNumber($dataRecord['lot_number']);
            }
            $medicationResource->setBatch($batch);
        }

        if ($encode) {
            return json_encode($medicationResource);
        } else {
            return $medicationResource;
        }
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param  array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - Patient uuid to return drug resources that are only visible to the current patient
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->medicationService->getAll($openEMRSearchParameters, true, $puuidBind);
    }

    public function parseFhirResource($fhirResource = array())
    {
        // TODO: If Required in Future
    }

    public function insertOpenEMRRecord($openEmrRecord)
    {
        // TODO: If Required in Future
    }

    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        // TODO: If Required in Future
    }
    public function createProvenanceResource($dataRecord = array(), $encode = false)
    {
        // TODO: If Required in Future
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
        return [
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-medication'
        ];
    }
}
