<?php

/*
 * FhirMediaService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRMedia;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FhirMediaService extends FhirServiceBase implements IFhirExportableResourceService, IPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    /**
     * @var DocumentService The OpenEMR Document Service
     */
    private DocumentService $service;

    public function __construct(?string $fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new DocumentService();
    }

    public function setSession(SessionInterface $session): void
    {
        parent::setSession($session);
        $this->service->setSession($session);
    }

    /**
     * Returns an array mapping FHIR Media Resource search parameters to OpenEMR Media search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters(): array
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('uuid', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
            'patient' => $this->getPatientContextSearchField(),
            'content-type' => new FhirSearchParameterDefinition('content-type', SearchFieldType::STRING, ['mimetype']),
            'title' => new FhirSearchParameterDefinition('title', SearchFieldType::STRING, ['name']),
        ];
    }


    public function getPatientContextSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('patient', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['date']);
    }

    /**
     * @inheritDoc
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRMedia|string|false
    {
        $fhirMedia = new FHIRMedia();
        $fhirMeta = new FHIRMeta();
        $fhirMeta->setVersionId('1');
        if (!empty($dataRecord['date'])) {
            $fhirMeta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['date']));
        } else {
            $fhirMeta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $fhirMedia->setMeta($fhirMeta);

        $fhirMedia->setId($dataRecord['uuid']);
        $fhirMedia->setStatus('completed'); // if the file is uploaded it is completed

        if (!empty($dataRecord['puuid'])) {
            $fhirMedia->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
        }

        $attachment = new FHIRAttachment();
        $url = $this->getFhirApiURL() . '/fhir/Binary/' . $dataRecord['uuid'];
        $attachment->setContentType($dataRecord['mimetype']);
        $attachment->setUrl(new FHIRUrl($url));
        $attachment->setTitle($dataRecord['name'] ?? '');
        $fhirMedia->setContent($attachment);

        if ($encode) {
            return json_encode($fhirMedia);
        }
        return $fhirMedia;
    }

    /**
     * @inheritDoc
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (isset($openEMRSearchParameters['patient'])) {
            // make sure that no other modifier such as NOT_EQUALS, OR missing=true is sent which would let system file names be
            // leaked out in the API
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::EXACT);
        } else {
            // make sure we only return documents that are tied to patients
            $openEMRSearchParameters['patient'] = new TokenSearchField('puuid', [new TokenSearchValue(false, null)]);
            $openEMRSearchParameters['patient']->setModifier(SearchModifier::MISSING);
        }
        // only pull in media with supported mimetypes
        $supportedMimetypes = ['application/dicom', 'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'video/mp4', 'video/quicktime'];
        if (isset($openEMRSearchParameters['mimetype'])) {
            // grab all of the mime type codes
            /**
             * @var StringSearchField
             */
            $mimetype = $openEMRSearchParameters['mimetype'];
            $mimetypeValues = $mimetype->getValues();
            $filteredMimetypes = array_intersect($mimetypeValues, $supportedMimetypes);
            $openEMRSearchParameters['mimetype'] = new StringSearchField(
                'mimetype',
                array_values($filteredMimetypes),
                SearchModifier::EXACT
            );
        } else {
            $openEMRSearchParameters['mimetype'] = new StringSearchField(
                'mimetype',
                $supportedMimetypes,
                SearchModifier::EXACT
            );
        }

        return $this->service->search($openEMRSearchParameters);
    }
}
