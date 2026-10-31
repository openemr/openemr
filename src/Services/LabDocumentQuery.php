<?php

/**
 * Builds the SQL behind the Lab Documents list (interface/main/display_documents.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Apostolos Stergiakis
 * @copyright Copyright (c) 2026 Apostolos Stergiakis
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final class LabDocumentQuery
{
    /**
     * @param string|null $fromDate Y-m-d, or null for no lower bound
     * @param string|null $toDate   Y-m-d, or null for no upper bound
     * @return array{sql: string, binds: list<string|int>}
     */
    public function build(int $categoryId, ?string $fromDate, ?string $toDate): array
    {
        $binds = [$categoryId];
        $conditions = [];
        if ($fromDate !== null) {
            $conditions[] = 'DATE(d.date) >= ?';
            $binds[] = $fromDate;
        }
        if ($toDate !== null) {
            $conditions[] = 'DATE(d.date) <= ?';
            $binds[] = $toDate;
        }
        $where = $conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $sql = "SELECT d.*,CONCAT(pd.fname,' ',pd.lname) AS pname,GROUP_CONCAT(n.note ORDER BY n.date DESC SEPARATOR '|') AS docNotes,
            GROUP_CONCAT(n.date ORDER BY n.date DESC SEPARATOR '|') AS docDates FROM documents d
            INNER JOIN patient_data pd ON d.foreign_id = pd.pid
            INNER JOIN categories_to_documents ctd ON d.id = ctd.document_id AND ctd.category_id = ?
            LEFT JOIN notes n ON d.id = n.foreign_id
            $where GROUP BY d.id ORDER BY date DESC";

        return ['sql' => $sql, 'binds' => $binds];
    }
}
