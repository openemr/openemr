<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTOutcomeMeasuresService extends BaseService
{
    private const TABLE = "pt_outcome_measures";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getPatientOutcomes($patientId): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE patient_id = ?
                ORDER BY measurement_date DESC";

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

    public function getProgressTracking($patientId, $measureType): array
    {
        $sql = "SELECT measurement_date, score_value, interpretation_vi
                FROM " . self::TABLE . "
                WHERE patient_id = ? AND measure_type = ?
                ORDER BY measurement_date ASC";

        $result = sqlStatement($sql, [$patientId, $measureType]);
        $tracking = [];

        while ($row = sqlFetchArray($result)) {
            $tracking[] = $row;
        }

        return $tracking;
    }
}
