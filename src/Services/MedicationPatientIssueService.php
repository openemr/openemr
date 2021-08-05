<?php

/**
 * MedicationPatientIssueService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Search\TokenSearchField;

class MedicationPatientIssueService extends BaseService
{
    const TABLE_NAME = "lists_medication";

    const LIST_OPTION_MEDICATION_REQUEST_INTENT = "medication-request-intent";
    const LIST_OPTION_MEDICATION_USAGE_CATEGORY = "medication-usage-category";

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function createIssue($record)
    {
        if (!empty($record['id'])) {
            throw new \InvalidArgumentException("Cannot insert record with existing id");
        }
        if (empty($record['list_id'])) {
            throw new \InvalidArgumentException("issue list_id is required to create this record");
        }

        $whiteListDict = $this->filterData($record);
        $this->populateListOptionValues($whiteListDict);
        $insert = $this->buildInsertColumns($whiteListDict);

        $sql = "INSERT INTO " . self::TABLE_NAME . " SET " . $insert['set'];
        QueryUtils::sqlStatementThrowException($sql, $insert['bind']);
    }

    public function updateIssue($record)
    {
        if (empty($record['id'])) {
            throw new \InvalidArgumentException("Cannot update record without id");
        }
        if (empty($record['list_id'])) {
            throw new \InvalidArgumentException("issue list_id is required to update this record");
        }

        $whiteListDict = $this->filterData($record);
        $this->populateListOptionValues($whiteListDict);
        $update = $this->buildUpdateColumns($whiteListDict);

        $sql = "UPDATE " . self::TABLE_NAME . " SET " . $update['set'] . " WHERE id = ? AND list_id = ?";
        $update['bind'][] = $record['id'];
        $update['bind'][] = $record['list_id'];
        QueryUtils::sqlStatementThrowException($sql, $update['bind']);
    }

    private function populateListOptionValues(&$dataRecord)
    {
        $listService = new ListService();
        // we save off the title information because administrators can change list option values and we need a historical record.
        if (!empty($dataRecord['request_intent'])) {
            $option = $listService->getListOption(self::LIST_OPTION_MEDICATION_REQUEST_INTENT, $dataRecord['request_intent']);
            $dataRecord['request_intent_title'] = $option['title'] ?? null;
        }
        if (!empty($dataRecord['usage_category'])) {
            $option = $listService->getListOption(self::LIST_OPTION_MEDICATION_USAGE_CATEGORY, $dataRecord['usage_category']);
            $dataRecord['usage_category_title'] = $option['title'] ?? null;
        }
    }

    public function getRecordByIssueListId($list_id)
    {
        $records = $this->search(['list_id' => new TokenSearchField('list_id', [$list_id])]);
        if (!empty($records->getData())) {
            return array_pop($records->getData());
        }
        return null;
    }
}
