<?php

/**
 * PT Assessment Template Service with ACL, Audit Logging, and Error Handling
 *
 * AI-GENERATED CODE - Claude Sonnet 4.5 (2025-01-22)
 * Enhanced with ACL integration, audit logging, and error handling
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class PTAssessmentTemplateService extends BaseService
{
    private const TABLE = "pt_assessment_templates";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Get active templates
     * AI-GENERATED CODE START
     */
    public function getActiveTemplates(): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check - templates are admin-level configuration
        if (!AclMain::aclCheckCore('admin', 'super')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE is_active = 1
                    ORDER BY category, template_name ASC";

            $result = QueryUtils::sqlStatementThrowException($sql);

            while ($row = sqlFetchArray($result)) {
                if (!empty($row['assessment_fields'])) {
                    $row['assessment_fields'] = json_decode($row['assessment_fields'], true);
                }
                $processingResult->addData($row);
            }

            // Audit log access
            EventAuditLogger::instance()->newEvent(
                'pt-template-access',
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 0,
                1,
                "Accessed PT Assessment Templates",
                null
            );
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Assessment Template getActiveTemplates failed', [
                'error' => $e->getMessage()
            ]);
            $processingResult->addInternalError('Failed to retrieve templates: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END

    /**
     * Get templates by category
     * AI-GENERATED CODE START
     */
    public function getByCategory($category): ProcessingResult
    {
        $processingResult = new ProcessingResult();

        // ACL check
        if (!AclMain::aclCheckCore('admin', 'super')) {
            $processingResult->setValidationMessages(['Access Denied']);
            return $processingResult;
        }

        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE category = ? AND is_active = 1
                    ORDER BY template_name ASC";

            $result = QueryUtils::sqlStatementThrowException($sql, [$category]);

            while ($row = sqlFetchArray($result)) {
                $processingResult->addData($row);
            }
        } catch (\Exception $e) {
            SystemLogger::instance()->error('PT Assessment Template getByCategory failed', [
                'error' => $e->getMessage(),
                'category' => $category
            ]);
            $processingResult->addInternalError('Failed to retrieve templates: ' . $e->getMessage());
        }

        return $processingResult;
    }
    // AI-GENERATED CODE END
}
