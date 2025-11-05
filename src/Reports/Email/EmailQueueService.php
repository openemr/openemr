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

class EmailQueueService
{
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
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(recipient LIKE ? OR subject LIKE ? OR sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
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
        if (!empty($filters['template_name'])) {
            $where[] = "template_name = ?";
            $params[] = $filters['template_name'];
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $where[] = "datetime_queued >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "datetime_queued <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

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

        $result = sqlStatement($sql, $params);
        $records = [];

        while ($row = sqlFetchArray($result)) {
            $records[] = $row;
        }

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
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = "(recipient LIKE ? OR subject LIKE ? OR sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Status filter
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
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
        if (!empty($filters['template_name'])) {
            $where[] = "template_name = ?";
            $params[] = $filters['template_name'];
        }

        // Date range filters
        if (!empty($filters['date_from'])) {
            $where[] = "datetime_queued >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "datetime_queued <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT COUNT(*) as total FROM email_queue {$whereClause}";

        $result = sqlQuery($sql, $params);
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

        $result = sqlQuery("SELECT
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

        $result = sqlStatement($sql);
        $templates = [];

        while ($row = sqlFetchArray($result)) {
            if (!empty($row['template_name'])) {
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
        $result = sqlQuery($sql, [$id]);

        return $result ?: null;
    }
}
