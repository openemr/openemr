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

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
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
            QueryUtils::startTransaction();
            $inTransaction = true;
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
            QueryUtils::commitTransaction();
        } catch (\Exception $e) {
            if ($inTransaction) {
                QueryUtils::rollbackTransaction();
            }
            throw $e;
        }
        return true;
    }
    public function getPredictiveDSIAttributes($dsiServiceId)
    {
        return $this->getAttributes(self::LIST_ID_PREDICTIVE_DSI, $dsiServiceId);
    }
    public function getEvidenceDSIAttributes($dsiServiceId)
    {
        return $this->getAttributes(self::LIST_ID_EVIDENCE_DSI, $dsiServiceId);
    }
    private function getAttributes($listId, $dsiServiceId)
    {
        $query = "SELECT "
            . "lo.list_id, lo.option_id, lo.title, lo.seq "
            . ", dsi.id,dsi.client_id,dsi.clinical_rule_id,dsi.source_value,dsi.created_by,dsi.last_updated_by"
            . ", dsi.created_at,dsi.last_updated_at FROM list_options lo LEFT JOIN " . self::TABLE_NAME
        . " dsi ON lo.list_id = dsi.list_id AND dsi.option_id = lo.option_id AND dsi.client_id = ? "
        . " WHERE lo.list_id = ? ORDER BY lo.seq ASC";
        $params = [$dsiServiceId, $listId];
        $attributes = QueryUtils::fetchRecords($query, $params);
        return $attributes;
    }

    /**
     * @return DecisionSupportInterventionEntity[]
     */
    public function getServices(bool $isSummary = false)
    {
        $repository = new ClientRepository();
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
        $repository = new ClientRepository();
        $clientEntity = $repository->getClientEntity($serviceId);
        if ($clientEntity != null && ($clientEntity->hasPredictiveDSI() || $clientEntity->hasEvidenceDSI())) {
            return $this->getServiceForClient($clientEntity, $isSummary);
        }
        return null;
    }
}
