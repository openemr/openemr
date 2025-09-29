<?php
namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTAssessmentTemplateService extends BaseService
{
    private const TABLE = "pt_assessment_templates";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getActiveTemplates(): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE is_active = 1
                ORDER BY category, template_name ASC";

        $result = QueryUtils::sqlStatementThrowException($sql);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            if (!empty($row['assessment_fields'])) {
                $row['assessment_fields'] = json_decode($row['assessment_fields'], true);
            }
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    public function getByCategory($category): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE category = ? AND is_active = 1
                ORDER BY template_name ASC";

        $result = QueryUtils::sqlStatementThrowException($sql, [$category]);
        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($result)) {
            $processingResult->addData($row);
        }

        return $processingResult;
    }
}
