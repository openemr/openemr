<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;

class EvidenceBasedDSIServiceEntity
{
    const TYPE = 'evidence-based';

    public function __construct(?ClientEntity $clientEntity)
    {
        parent::__construct(self::TYPE, $clientEntity);
    }

    public function populateServiceWithFhirQuestionnaire(string $questionnaire, string $response = null)
    {

        return $this->populateFromQuestionnaire(self::TYPE, $questionnaire, $response);
    }
}
