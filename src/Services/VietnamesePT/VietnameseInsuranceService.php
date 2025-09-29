<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class VietnameseInsuranceService extends BaseService
{
    private const TABLE = "vietnamese_insurance_info";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getPatientInsurance($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ? AND is_active = 1
                ORDER BY valid_from DESC";

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

        $query = $this->buildInsertColumns($data);
        $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
        $insertId = QueryUtils::sqlInsert($sql, $query['bind']);

        if ($insertId) {
            $processingResult->addData(['id' => $insertId]);
        }

        return $processingResult;
    }

    public function isInsuranceValid($patientId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . "
                WHERE patient_id = ?
                AND is_active = 1
                AND valid_from <= CURDATE()
                AND (valid_to IS NULL OR valid_to >= CURDATE())";

        $result = sqlQuery($sql, [$patientId]);
        return ($result['count'] ?? 0) > 0;
    }
}
