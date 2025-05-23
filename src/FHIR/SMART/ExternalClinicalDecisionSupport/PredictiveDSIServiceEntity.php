<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;

class PredictiveDSIServiceEntity extends DecisionSupportInterventionEntity
{
    const TYPE = 'predictive';
    public function __construct(?ClientEntity $clientEntity = null)
    {
        parent::__construct(self::TYPE, $clientEntity);
    }

    public function populateServiceWithFhirQuestionnaire(string $questionnaire, string $response = null)
    {

        return $this->populateServiceWithFhirQuestionnaireForType(self::TYPE, $questionnaire, $response);
    }
}
