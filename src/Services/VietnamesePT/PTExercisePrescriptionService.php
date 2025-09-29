<?php

namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\VietnamesePT\PTExercisePrescriptionValidator;

class PTExercisePrescriptionService extends BaseService
{
    private const TABLE = "pt_exercise_prescriptions";
    private $validator;

    public function __construct()
    {
        parent::__construct(self::TABLE);
        $this->validator = new PTExercisePrescriptionValidator();
    }

    public function getAll($search = []): ProcessingResult
    {
        $sql = "SELECT e.*, p.fname, p.lname, u.username as prescribed_by_name
                FROM " . self::TABLE . " e
                LEFT JOIN patient_data p ON e.patient_id = p.pid
                LEFT JOIN users u ON e.prescribed_by = u.id
                WHERE 1=1";
        
        $bindArray = [];
        
        if (!empty($search['patient_id'])) {
            $sql .= " AND e.patient_id = ?";
            $bindArray[] = $search['patient_id'];
        }
        
        if (!empty($search['is_active'])) {
            $sql .= " AND e.is_active = ?";
            $bindArray[] = $search['is_active'];
        }
        
        $sql .= " ORDER BY e.start_date DESC";
        
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $bindArray);
        
        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($row);
        }
        
        return $processingResult;
    }

    public function getOne($id): ProcessingResult
    {
        return $this->getAll(['id' => $id]);
    }

    public function getPatientPrescriptions($patientId): ProcessingResult
    {
        return $this->getAll(['patient_id' => $patientId, 'is_active' => 1]);
    }

    public function insert($data): ProcessingResult
    {
        $processingResult = $this->validator->validate($data);

        if ($processingResult->isValid()) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $query = $this->buildInsertColumns($data);
            $sql = "INSERT INTO " . self::TABLE . " SET " . $query['set'];
            
            $insertId = QueryUtils::sqlInsert($sql, $query['bind']);
            
            if ($insertId) {
                $processingResult->addData(['id' => $insertId]);
            } else {
                $processingResult->addInternalError("Error inserting exercise prescription");
            }
        }

        return $processingResult;
    }

    public function update($id, $data): ProcessingResult
    {
        $processingResult = $this->validator->validate($data, true);

        if ($processingResult->isValid()) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            unset($data['id'], $data['created_at']);
            
            $query = $this->buildUpdateColumns($data);
            $sql = "UPDATE " . self::TABLE . " SET " . $query['set'] . " WHERE id = ?";
            
            sqlStatement($sql, array_merge($query['bind'], [$id]));
            $processingResult->addData(['id' => $id]);
        }

        return $processingResult;
    }

    public function delete($id): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $sql = "UPDATE " . self::TABLE . " SET is_active = 0 WHERE id = ?";
        sqlStatement($sql, [$id]);
        $processingResult->addData(['id' => $id]);
        return $processingResult;
    }
}
