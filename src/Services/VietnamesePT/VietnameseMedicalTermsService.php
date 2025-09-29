<?php

/**
 * Vietnamese Medical Terms Service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\VietnamesePT;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Validators\ProcessingResult;

class VietnameseMedicalTermsService extends BaseService
{
    private const TABLE = "vietnamese_medical_terms";

    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    public function getAll($search = array()): ProcessingResult
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE is_active = 1";
        
        if (!empty($search['category'])) {
            $sql .= " AND category = ?";
            $bindArray = [$search['category']];
        } else {
            $bindArray = [];
        }
        
        $sql .= " ORDER BY english_term ASC";
        
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $bindArray);
        
        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($row);
        }
        
        return $processingResult;
    }

    public function searchTerms($searchTerm, $language = 'en'): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        $searchPattern = '%' . $searchTerm . '%';
        
        if ($language === 'vi') {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE (vietnamese_term LIKE ? OR synonyms_vi LIKE ?)
                    AND is_active = 1
                    ORDER BY
                        CASE
                            WHEN vietnamese_term = ? THEN 1
                            WHEN vietnamese_term LIKE ? THEN 2
                            ELSE 3
                        END,
                        vietnamese_term ASC
                    LIMIT 50";
            $bindArray = [$searchPattern, $searchPattern, $searchTerm, $searchTerm . '%'];
        } else {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE (english_term LIKE ? OR synonyms_en LIKE ?)
                    AND is_active = 1
                    ORDER BY
                        CASE
                            WHEN english_term = ? THEN 1
                            WHEN english_term LIKE ? THEN 2
                            ELSE 3
                        END,
                        english_term ASC
                    LIMIT 50";
            $bindArray = [$searchPattern, $searchPattern, $searchTerm, $searchTerm . '%'];
        }
        
        $statementResults = QueryUtils::sqlStatementThrowException($sql, $bindArray);
        
        while ($row = sqlFetchArray($statementResults)) {
            $processingResult->addData($row);
        }
        
        return $processingResult;
    }

    public function translate($term, $fromLanguage = 'en'): ?array
    {
        if ($fromLanguage === 'en') {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE english_term = ? AND is_active = 1
                    LIMIT 1";
        } else {
            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE vietnamese_term = ? AND is_active = 1
                    LIMIT 1";
        }
        
        $result = sqlQuery($sql, [$term]);
        return $result ?: null;
    }

    public function getCategories(): array
    {
        $sql = "SELECT DISTINCT category FROM " . self::TABLE . "
                WHERE is_active = 1 AND category IS NOT NULL
                ORDER BY category ASC";
        
        $result = sqlStatement($sql);
        $categories = [];
        while ($row = sqlFetchArray($result)) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
}
