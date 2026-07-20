<?php

/**
 * EmailQueueService class.
 * Built with Warp-Terminal
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated for PoppyBilling
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Email;

use OpenEMR\Common\Database\QueryUtils;

class EmailQueueService
{
    /**
     * Get a normalized non-empty string filter value.
     *
     * @param array<string, mixed> $filters
     */
    private function getFilterValue(array $filters, string $key): ?string
    {
        if (!array_key_exists($key, $filters)) {
            return null;
        }

        $value = trim((string) $filters[$key]);
        return $value !== '' ? $value : null;
    }
    /**
     * Get email queue records with optional filtering and pagination
     *
     * @param array $filters Optional filters: search, status (all|sent|pending|failed), template_name, date_from, date_to
     * @param int $limit Number of records to return
     * @param int $offset Starting offset for pagination
     * @return array Array of email queue records
     */
    public function getEmailQueue(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];

        // Search filter (search in recipient, subject, sender)
        $searchValue = $this->getFilterValue($filters, 'search');
        if ($searchValue !== null) {
            $searchTerm = '%' . $searchValue . '%';
            $where[] = "(recipient LIKE ? OR subject LIKE ? OR sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        $statusValue = $this->getFilterValue($filters, 'status');
        if ($statusValue !== null) {
            switch ($statusValue) {
                case 'sent':
                    $where[] = "sent = 1 AND error = 0";
                    break;
                case 'pending':
                    $where[] = "sent = 0 AND error = 0";
                    break;
                case 'failed':
                    $where[] = "error = 1";
                    break;
            }
        }

        // Template name filter
        $templateName = $this->getFilterValue($filters, 'template_name');
        if ($templateName !== null) {
            $where[] = "template_name = ?";
            $params[] = $templateName;
        }

        // Date range filters
        $dateFrom = $this->getFilterValue($filters, 'date_from');
        if ($dateFrom !== null) {
            $where[] = "datetime_queued >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        $dateTo = $this->getFilterValue($filters, 'date_to');
        if ($dateTo !== null) {
            $where[] = "datetime_queued <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = $where !== [] ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT
                    id,
                    sender,
                    recipient,
                    subject,
                    datetime_queued,
                    sent,
                    datetime_sent,
                    error,
                    error_message,
                    datetime_error,
                    template_name
                FROM email_queue
                {$whereClause}
                ORDER BY datetime_queued DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $records = QueryUtils::fetchRecords($sql, $params);

        return $records;
    }

    /**
     * Get total count of email queue records with optional filtering
     *
     * @param array $filters Optional filters: search, status, template_name, date_from, date_to
     * @return int Total count of matching records
     */
    public function getEmailQueueCount(array $filters = []): int
    {
        $where = [];
        $params = [];

        // Search filter
        $searchValue = $this->getFilterValue($filters, 'search');
        if ($searchValue !== null) {
            $searchTerm = '%' . $searchValue . '%';
            $where[] = "(recipient LIKE ? OR subject LIKE ? OR sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        $statusValue = $this->getFilterValue($filters, 'status');
        if ($statusValue !== null) {
            switch ($statusValue) {
                case 'sent':
                    $where[] = "sent = 1 AND error = 0";
                    break;
                case 'pending':
                    $where[] = "sent = 0 AND error = 0";
                    break;
                case 'failed':
                    $where[] = "error = 1";
                    break;
            }
        }

        // Template name filter
        $templateName = $this->getFilterValue($filters, 'template_name');
        if ($templateName !== null) {
            $where[] = "template_name = ?";
            $params[] = $templateName;
        }

        // Date range filters
        $dateFrom = $this->getFilterValue($filters, 'date_from');
        if ($dateFrom !== null) {
            $where[] = "datetime_queued >= ?";
            $params[] = $dateFrom . ' 00:00:00';
        }

        $dateTo = $this->getFilterValue($filters, 'date_to');
        if ($dateTo !== null) {
            $where[] = "datetime_queued <= ?";
            $params[] = $dateTo . ' 23:59:59';
        }

        $whereClause = $where !== [] ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT COUNT(*) as total FROM email_queue {$whereClause}";

        $result = QueryUtils::querySingleRow($sql, $params);
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get email queue statistics
     *
     * @return array Statistics including total, sent, pending, and failed counts
     */
    public function getStatistics(): array
    {
        $stats = [
            'total' => 0,
            'sent' => 0,
            'pending' => 0,
            'failed' => 0,
        ];

        $result = QueryUtils::querySingleRow("SELECT
            COUNT(*) as total,
            SUM(CASE WHEN sent = 1 AND error = 0 THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN sent = 0 AND error = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN error = 1 THEN 1 ELSE 0 END) as failed
            FROM email_queue");

        if ($result) {
            $stats = [
                'total' => (int)$result['total'],
                'sent' => (int)$result['sent'],
                'pending' => (int)$result['pending'],
                'failed' => (int)$result['failed'],
            ];
        }

        return $stats;
    }

    /**
     * Get unique template names from email queue
     *
     * @return array List of unique template names
     */
    public function getTemplateNames(): array
    {
        $sql = "SELECT DISTINCT template_name
                FROM email_queue
                WHERE template_name IS NOT NULL
                ORDER BY template_name";

        $result = QueryUtils::fetchRecords($sql);
        $templates = [];

        foreach ($result as $row) {
            if (isset($row['template_name']) && (string) $row['template_name'] !== '') {
                $templates[] = $row['template_name'];
            }
        }

        return $templates;
    }

    /**
     * Get email details by ID
     *
     * @param int $id Email queue ID
     * @return array|null Email record or null if not found
     */
    public function getEmailById(int $id): ?array
    {
        $sql = "SELECT * FROM email_queue WHERE id = ?";
        $result = QueryUtils::querySingleRow($sql, [$id]);

        if (!is_array($result)) {
            return null;
        }

        return $result;
    }
}
