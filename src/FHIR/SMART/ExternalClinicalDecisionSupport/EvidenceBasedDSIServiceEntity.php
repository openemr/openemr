<?php

namespace OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;

class EvidenceBasedDSIServiceEntity extends DecisionSupportInterventionEntity
{
    const TYPE = 'evidence-based';

    public function __construct(?ClientEntity $clientEntity = null)
    {
        parent::__construct(self::TYPE, $clientEntity);
    }
}
