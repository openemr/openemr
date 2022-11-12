<?php

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\PractitionerService;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRHumanName;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

/**
 * FHIR Practitioner Service
 *
 * @coversDefaultClass OpenEMR\Services\FHIR\FhirPractitionerService
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class FhirPractitionerService extends FhirServiceBase implements IFhirExportableResourceService, IResourceUSCIGProfileService
{
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var PractitionerService
     */
    private $practitionerService;

    public function __construct()
    {
        parent::__construct();
        $this->practitionerService = new PractitionerService();
    }

    /**
     * Returns an array mapping FHIR Practitioner Resource search parameters to OpenEMR Practitioner search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'active' => new FhirSearchParameterDefinition('active', SearchFieldType::TOKEN, ['active']),
            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ["phonew1", "phone", "phonecell"]),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ["email", "phone", "phonew1", "phonecell"]),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "streetb", "zip", "city", "state"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['zip']),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ["lname"]),
            'given' => new FhirSearchParameterDefinition('given', SearchFieldType::STRING, ["fname", "mname"]),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ["title", "fname", "mname", "lname"])
        ];
    }


    /**
     * Parses an OpenEMR practitioner record, returning the equivalent FHIR Practitioner Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param boolean $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitioner
     */
    public function parseOpenEMRRecord($dataRecord = array(), $encode = false)
    {
        $practitionerResource = new FHIRPractitioner();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        $practitionerResource->setMeta($meta);

        $practitionerResource->setActive($dataRecord['active'] == "1" ? true : false);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $practitionerResource->setId($id);

        $narrativeText = '';
        if (isset($dataRecord['fname'])) {
            $narrativeText = $dataRecord['fname'];
        }
        if (isset($dataRecord['lname'])) {
            $narrativeText .= ' ' . $dataRecord['lname'];
        }
        // why in some cases are users with an empty name... that seems so wierd but we have them so we are supporting them.
        if (empty(trim($narrativeText))) {
            $practitionerResource->addName(UtilsService::createDataMissingExtension());
        } else {
            $text = array(
                'status' => 'generated',
                'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
            );
            $practitionerResource->setText($text);

            $practitionerResource->addName(UtilsService::createHumanNameFromRecord($dataRecord));
        }
        $address = UtilsService::createAddressFromRecord($dataRecord);
        if (isset($address)) {
            $practitionerResource->addAddress($address);
        }

        if (!empty($dataRecord['phone'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'home'
            ));
        }

        if (!empty($dataRecord['phonew1'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonew1'],
                'use' => 'work'
            ));
        }

        if (!empty($dataRecord['phonecell'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'phone',
                'value' => $dataRecord['phonecell'],
                'use' => 'mobile'
            ));
        }

        if (!empty($dataRecord['email'])) {
            $practitionerResource->addTelecom(array(
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'home'
            ));
        }

        if (!empty($dataRecord['npi'])) {
            $identifier = new FHIRIdentifier();
            $identifier->setSystem(FhirCodeSystemConstants::PROVIDER_NPI);
            $identifier->setValue($dataRecord['npi']);
            $practitionerResource->addIdentifier($identifier);
        } else {
            $practitionerResource->addIdentifier(UtilsService::createDataMissingExtension());
        }

        if ($encode) {
            return json_encode($practitionerResource);
        } else {
            return $practitionerResource;
        }
    }

    /**
     * Parses a FHIR Practitioner Resource, returning the equivalent OpenEMR practitioner record.
     *
     * @param array $fhirResource The source FHIR resource
     * @return array a mapped OpenEMR data record (array)
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!$fhirResource instanceof FHIRPractitioner) {
            throw new \BadMethodCallException("fhir resource must be of type " . FHIRPractitioner::class);
        }

        $data = array();
        $data['uuid'] = (string)$fhirResource->getId() ?? null;

        if (!empty($fhirResource->getName())) {
            $name = new FHIRHumanName();
            foreach ($fhirResource->getName() as $sub_name) {
                if ((string)$sub_name->getUse() === 'official') {
                    $name = $sub_name;
                    break;
                }
            }
            $data['lname'] = (string)$name->getFamily() ?? null;

            $given = $name->getGiven() ?? [];
            // we cast due to the way FHIRString works
            $data['fname'] = (string)($given[0] ?? null);
            $data['mname'] = (string)($given[1] ?? null);

            $prefix = $name->getPrefix() ?? [];
            // we don't support updating the title right now, it requires updating another table which is breaking
            // the service class.  As far as I can tell, this was never tested and never worked.
//            $data['title'] = $prefix[0] ?? null;
        }
        // we don't support updating the user's sex right now, as far as I can tell there is no column for this on the
        // user level.  As far as I can tell, this was never tested and never worked.
//        $data['sex'] = (string)$fhirResource->getGender() ?? null;

        $addresses = $fhirResource->getAddress();
        if (!empty($addresses)) {
            $activeAddress = $addresses[0];
            $mostRecentPeriods = UtilsService::getPeriodTimestamps($activeAddress->getPeriod());
            foreach ($fhirResource->getAddress() as $address) {
                $addressPeriod = UtilsService::getPeriodTimestamps($address->getPeriod());
                if (empty($addressPeriod['end'])) {
                    $activeAddress = $address;
                } else if (!empty($mostRecentPeriods['end']) && $addressPeriod['end'] > $mostRecentPeriods['end']) {
                    // if our current period is more recent than our most recent address we want to grab that one
                    $mostRecentPeriods = $addressPeriod;
                    $activeAddress = $address;
                }
            }

            $lineValues = array_map(function ($val) {
                return (string)$val;
            }, $activeAddress->getLine() ?? []);
            $data['street'] = implode("\n", $lineValues) ?? null;
            $data['zip'] = (string)$activeAddress->getPostalCode() ?? null;
            $data['city'] = (string)$activeAddress->getCity() ?? null;
            $data['state'] = (string)$activeAddress->getState() ?? null;
        }

        $telecom = $fhirResource->getTelecom();
        if (!empty($telecom)) {
            foreach ($telecom as $contactPoint) {
                $systemValue = (string)$contactPoint->getSystem() ?? "contact_other";
                $contactValue = (string)$contactPoint->getValue();
                if ($systemValue === 'email') {
                    $data[$systemValue] = (string)$contactValue;
                } else if ($systemValue == "phone") {
                    $use = (string)$contactPoint->getUse() ?? "work";
                    $useMapping = ['mobile' => 'phonecell', 'home' => 'phone', 'work' => 'phonew1'];
                    if (isset($useMapping[$use])) {
                        $data[$useMapping[$use]] = $contactValue;
                    }
                }
            }
        }

        foreach ($fhirResource->getIdentifier() as $index => $identifier) {
            if ((string)$identifier->getSystem() == FhirCodeSystemConstants::PROVIDER_NPI) {
                $data['npi'] = (string)$identifier->getValue() ?? null;
            }
        }

        return $data;
    }

    /**
     * Inserts an OpenEMR record into the sytem.
     *
     * @param array $openEmrRecord OpenEMR practitioner record
     * @return ProcessingResult
     */
    public function insertOpenEMRRecord($openEmrRecord)
    {
        // practitioners HAVE to have a username
        if (!isset($openEmrRecord['username'])) {
            $username = $openEmrRecord['lname'] ?? '';
            $username .= $openEmrRecord['fname'] ?? '';
            $openEmrRecord['username'] = uniqid($username);
        }
        return $this->practitionerService->insert($openEmrRecord);
    }


    /**
     * Updates an existing OpenEMR record.
     *
     * @param $fhirResourceId //The OpenEMR record's FHIR Resource ID.
     * @param $updatedOpenEMRRecord //The "updated" OpenEMR record.
     * @return ProcessingResult
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord)
    {
        $processingResult = $this->practitionerService->update($fhirResourceId, $updatedOpenEMRRecord);
        return $processingResult;
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array openEMRSearchParameters OpenEMR search fields
     * @param $puuidBind - NOT USED
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters, $puuidBind = null): ProcessingResult
    {
        return $this->practitionerService->getAll($openEMRSearchParameters, true);
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
            'http://hl7.org/fhir/us/core/StructureDefinition/us-core-practitioner'
        ];
    }
}
