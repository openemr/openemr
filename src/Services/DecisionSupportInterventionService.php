<?php

namespace OpenEMR\Services;

use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport\DecisionSupportInterventionEntity;
use OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport\EvidenceBasedDSIServiceEntity;
use OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport\PredictiveDSIServiceEntity;

class DecisionSupportInterventionService extends BaseService
{
    const TABLE_NAME = "dsi_source_attributes";
    const LIST_ID_PREDICTIVE_DSI = 'dsi_predictive_source_attributes';
    const LIST_ID_EVIDENCE_DSI = 'dsi_evidence_source_attributes';

    const DSI_TYPES = [
        ClientEntity::DSI_TYPE_NONE => 'none',
        ClientEntity::DSI_TYPE_EVIDENCE => 'evidence',
        ClientEntity::DSI_TYPE_PREDICTIVE => 'predictive'
    ];
    // note these correspond with the enum values in ClientEntity
    const DSI_TYPES_CLIENT_STRING_NAMES = [
        "DSI_TYPE_PREDICTIVE" => 'predictive',
        "DSI_TYPE_EVIDENCE" => 'evidence',
        "DSI_TYPE_NONE" => 'none'
    ];

    const DSI_TYPES_BY_STRING_NAME = [
        'predictive' => ClientEntity::DSI_TYPE_PREDICTIVE,
        'evidence' => ClientEntity::DSI_TYPE_EVIDENCE,
        'none' => ClientEntity::DSI_TYPE_NONE
    ];

    private bool $inNestedTransaction = false;

    protected ?ClientRepository $clientRepository = null;

    public function __construct(?ClientRepository $clientRepository = null)
    {
        parent::__construct(self::TABLE_NAME);
        $this->clientRepository = $clientRepository;
    }

    public function setClientRepository(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getClientRepository(): ClientRepository
    {
        // lazy load the client repository
        if ($this->clientRepository === null) {
            $this->clientRepository = new ClientRepository();
        }
        return $this->clientRepository;
    }

    public function setInNestedTransaction(bool $inNestedTransaction)
    {
        $this->inNestedTransaction = $inNestedTransaction;
    }

    /**
     * Note this may at some point return an array of services... but for now we are keeping it simple
     * @param ClientEntity $clientEntity
     * @return EvidenceBasedDSIServiceEntity|PredictiveDSIServiceEntity
     */
    public function getServiceForClient(ClientEntity $clientEntity, bool $isSummary = true)
    {
        // right now its a 1:1, but we may allow multiple services for a single entity... not sure here but best
        // to keep it flexible
        $attributes = [];
        if ($clientEntity->hasPredictiveDSI()) {
            $service = new PredictiveDSIServiceEntity($clientEntity);
            if (!$isSummary) {
                $attributes = $this->getPredictiveDSIAttributes($clientEntity->getIdentifier());
            }
        } else if ($clientEntity->hasEvidenceDSI()) {
            $service = new EvidenceBasedDSIServiceEntity($clientEntity);
            if (!$isSummary) {
                $attributes = $this->getEvidenceDSIAttributes($clientEntity->getIdentifier());
            }
        } else {
            throw new \InvalidArgumentException("Client does not have a DSI service");
        }
        foreach ($attributes as $attr) {
            $service->setField($attr['option_id'], xl_list_label($attr['title']), $attr['source_value'] ?? '');
        }
        return $service;
    }

    public function getEmptyService(int $type): DecisionSupportInterventionEntity
    {
        if ($type === ClientEntity::DSI_TYPE_PREDICTIVE) {
            $service = new PredictiveDSIServiceEntity();
            $attributes = $this->getPredictiveDSIAttributes();
        } else if ($type === ClientEntity::DSI_TYPE_EVIDENCE) {
            $service = new EvidenceBasedDSIServiceEntity();
            $attributes = $this->getEvidenceDSIAttributes();
        }
        foreach ($attributes as $attr) {
            $service->setField($attr['option_id'], xl_list_label($attr['title']), $attr['source_value'] ?? '');
        }
        return $service;
    }

    public function insertAttribute($dsiServiceId, $attribute, $userId)
    {
        $query = "INSERT INTO " . self::TABLE_NAME . " (client_id, list_id, option_id, source_value, created_by, last_updated_by) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$dsiServiceId, $attribute['list_id'], $attribute['option_id'], $attribute['source_value'], $userId, $userId];
        QueryUtils::sqlStatementThrowException($query, $params);
    }
    public function updateAttribute($id, $value, $userId)
    {
        // business requirement we allow nullable created_by for external api registrations... but we will always have a last_updated_by
        if (empty($userId)) {
            throw new \InvalidArgumentException("User id is required");
        }
        $query = "UPDATE " . self::TABLE_NAME . " SET source_value = ?, last_updated_by = ? WHERE id = ?";
        $params = [$value, $userId, $id];
        QueryUtils::sqlStatementThrowException($query, $params);
    }

    public function deleteAttribute($id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . " WHERE id = ?";
        $params = [$id];
        QueryUtils::sqlStatementThrowException($query, $params);
    }
    public function updatePredictiveDSIAttributes($dsiServiceId, $userId, $attributes)
    {
        $this->updateDSIAttributes($dsiServiceId, self::LIST_ID_PREDICTIVE_DSI, $userId, $attributes);
    }
    public function updateEvidenceDSIAttributes($dsiServiceId, $userId, $attributes)
    {
        $this->updateDSIAttributes($dsiServiceId, self::LIST_ID_EVIDENCE_DSI, $userId, $attributes);
    }
    private function updateDSIAttributes($dsiServiceId, $listId, $userId, $attributes)
    {
        $inTransaction = false;
        try {
            if (!$this->inNestedTransaction) {
                QueryUtils::startTransaction();
            } else {
                $inTransaction = true;
            }
            $currentAttributes = $this->getAttributes($listId, $dsiServiceId);
            $currentAttributesMap = [];
            foreach ($currentAttributes as $attribute) {
                $currentAttributesMap[$attribute['option_id']] = $attribute;
            }
            foreach ($attributes as $attribute) {
                $optionId = $attribute['name'];
                if (!array_key_exists($optionId, $currentAttributesMap)) {
                    // doesn't exist so we will skip over this.
                    continue;
                }
                $currentAttribute = $currentAttributesMap[$optionId];
                if (isset($currentAttribute['id'])) {
                    if ($currentAttribute['value'] !== $attribute['value']) {
                        $this->updateAttribute($currentAttribute['id'], $attribute['value'], $userId);
                    }
                } else {
                    $currentAttribute['source_value'] = $attribute['value'];
                    $currentAttribute['client_id'] = $dsiServiceId;
                    $this->insertAttribute($dsiServiceId, $currentAttribute, $userId);
                }
                unset($currentAttributesMap[$optionId]);
            }

            foreach ($currentAttributesMap as $currentAttribute) {
                $this->deleteAttribute($currentAttribute['id']);
            }
            if (!$this->inNestedTransaction) {
                QueryUtils::commitTransaction();
            }
        } catch (\Exception $e) {
            if ($inTransaction && !$this->inNestedTransaction) {
                QueryUtils::rollbackTransaction();
            }
            throw $e;
        }
        return true;
    }
    public function getPredictiveDSIAttributes(?string $dsiServiceId = null)
    {
        return $this->getAttributes(self::LIST_ID_PREDICTIVE_DSI, $dsiServiceId);
    }
    public function getEvidenceDSIAttributes(?string $dsiServiceId = null)
    {
        return $this->getAttributes(self::LIST_ID_EVIDENCE_DSI, $dsiServiceId);
    }

    /**
     * Returns the list of attributes for a given service.  If the service is null
     * then it will return the default attributes for the list.
     * @param $listId
     * @param $dsiServiceId
     * @return array
     */
    private function getAttributes($listId, ?string $dsiServiceId)
    {
        if (empty($dsiServiceId)) {
            $query =  "SELECT "
                . "lo.list_id, lo.option_id, lo.title, lo.seq "
                . ", dsi.id,dsi.client_id,dsi.clinical_rule_id,dsi.source_value,dsi.created_by,dsi.last_updated_by"
                . ", dsi.created_at,dsi.last_updated_at FROM list_options lo "
                . " CROSS JOIN (select NULL as id, NULL as client_id, NULL as clinical_rule_id, NULL as source_value, NULL as created_by, NULL as last_updated_by, NULL as created_at, NULL as last_updated_at) dsi "
                . " WHERE lo.list_id = ? ORDER BY lo.seq ASC";
            $params = [$listId];
        } else {
            $query = "SELECT "
                . "lo.list_id, lo.option_id, lo.title, lo.seq "
                . ", dsi.id,dsi.client_id,dsi.clinical_rule_id,dsi.source_value,dsi.created_by,dsi.last_updated_by"
                . ", dsi.created_at,dsi.last_updated_at FROM list_options lo LEFT JOIN " . self::TABLE_NAME
                . " dsi ON lo.list_id = dsi.list_id AND dsi.option_id = lo.option_id AND dsi.client_id = ? "
                . " WHERE lo.list_id = ? ORDER BY lo.seq ASC";
            $params = [$dsiServiceId, $listId];
        }
        $attributes = QueryUtils::fetchRecords($query, $params);
        return $attributes;
    }

    /**
     * @return DecisionSupportInterventionEntity[]
     */
    public function getServices(bool $isSummary = false)
    {
        $repository = $this->getClientRepository();
        $clientEntities = $repository->listClientEntities();
        $clientEntities = array_filter($clientEntities, function ($clientEntity) {
            return $clientEntity->hasPredictiveDSI() || $clientEntity->hasEvidenceDSI();
        });
        return array_map(function ($clientEntity) use ($isSummary) {
            return $this->getServiceForClient($clientEntity, $isSummary);
        }, $clientEntities);
    }

    public function getService($serviceId, bool $isSummary = false)
    {
        $repository = $this->getClientRepository();
        $clientEntity = $repository->getClientEntity($serviceId);
        if ($clientEntity != null && ($clientEntity->hasPredictiveDSI() || $clientEntity->hasEvidenceDSI())) {
            return $this->getServiceForClient($clientEntity, $isSummary);
        }
        return null;
    }

    public function getDsiTypeForStringName(string $dsiTypeName)
    {
        if (!array_key_exists($dsiTypeName, self::DSI_TYPES_BY_STRING_NAME)) {
            throw new \InvalidArgumentException("Invalid DSI type name");
        }
        return self::DSI_TYPES_BY_STRING_NAME[$dsiTypeName];
    }

    public function getDsiTypeStringName(int $dsiType)
    {
        if (!array_key_exists($dsiType, self::DSI_TYPES)) {
            throw new \InvalidArgumentException("Invalid DSI type");
        }
        return self::DSI_TYPES[$dsiType];
    }

    public function updateService(DecisionSupportInterventionEntity $service, ?int $userId)
    {
        $fields = $service->getFields();
        if ($service->getType() == PredictiveDSIServiceEntity::TYPE) {
            $this->updatePredictiveDSIAttributes($service->getId(), $userId, $fields);
        } else {
            $this->updateEvidenceDSIAttributes($service->getId(), $userId, $fields);
        }
    }
}
