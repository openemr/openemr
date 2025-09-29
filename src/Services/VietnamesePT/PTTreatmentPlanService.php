<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTTreatmentPlanService extends BaseService
{
    private const TABLE = "pt_treatment_plans";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getActivePlans($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ? AND plan_status = 'active'
                ORDER BY start_date DESC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$patientId]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $data['created_at'] = date('Y-m-d H:i:s');

        if (isset($data['goals_short_term']) && is_array($data['goals_short_term'])) {
            $data['goals_short_term'] = json_encode($data['goals_short_term']);
        }

        if (isset($data['goals_long_term']) && is_array($data['goals_long_term'])) {
            $data['goals_long_term'] = json_encode($data['goals_long_term']);
        }

        $query = $this->buildInsertColumns($data);
        $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
        $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

        if ($insertId) {
            $processingResult->addData(['id' => $insertId]);
        }

        return $processingResult;
    }

    public function updateStatus($id, $status): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $validStatuses = ['active', 'completed', 'on_hold', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            $processingResult->addValidationMessage('status', 'invalid status');
            return $processingResult;
        }

        $sql = "UPDATE " . self::TABLE . "
                SET plan_status = ?, updated_at = ?
                WHERE id = ?";

        sqlStatement($sql, [$status, date('Y-m-d H:i:s'), $id]);
        $processingResult->addData(['id' => $id, 'status' => $status]);

        return $processingResult;
    }
}
