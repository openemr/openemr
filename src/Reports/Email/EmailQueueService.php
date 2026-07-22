<?php
declare(strict_types=1);

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
    private const EMAIL_QUEUE_COLUMNS = [
        'id',
        'sender',
        'recipient',
        'subject',
        'datetime_queued',
        'sent',
        'datetime_sent',
        'error',
        'error_message',
        'datetime_error',
        'template_name',
    ];
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
        $value = $filters[$key];
        if (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value)) {
            return null;
        }

        $normalizedValue = trim((string) $value);
        return $normalizedValue !== '' ? $normalizedValue : null;
    }

    private function normalizeIntValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return 0;
    }
    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<int, int|string>}
     */
    private function buildFilterClause(array $filters): array
    {
        $where = [];
        $params = [];

        $searchValue = $this->getFilterValue($filters, 'search');
        if ($searchValue !== null) {
            $searchTerm = '%' . $searchValue . '%';
            $where[] = "(recipient LIKE ? OR subject LIKE ? OR sender LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

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

        $templateName = $this->getFilterValue($filters, 'template_name');
        if ($templateName !== null) {
            $where[] = "template_name = ?";
            $params[] = $templateName;
        }

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

        return [$whereClause, $params];
    }

    /**
     * Get email queue records with optional filtering and pagination
     *
     * @param array<string, mixed> $filters Optional filters: search, status (all|sent|pending|failed), template_name, date_from, date_to
     * @param int $limit Number of records to return
     * @param int $offset Starting offset for pagination
     * @return array<int, array<string, int|string|null>> Array of email queue records
     */
    public function getEmailQueue(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        [$whereClause, $params] = $this->buildFilterClause($filters);
        $selectClause = implode(",\n                    ", self::EMAIL_QUEUE_COLUMNS);
        $sql = "SELECT
                    {$selectClause}
                FROM email_queue
                {$whereClause}
                ORDER BY datetime_queued DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;
        /** @var array<int, array<string, int|string|null>> $records */
        $records = QueryUtils::fetchRecords($sql, $params);
        return $records;
    }

    /**
     * Get total count of email queue records with optional filtering
     *
     * @param array<string, mixed> $filters Optional filters: search, status, template_name, date_from, date_to
     * @return int Total count of matching records
     */
    public function getEmailQueueCount(array $filters = []): int
    {
        [$whereClause, $params] = $this->buildFilterClause($filters);

        $sql = "SELECT COUNT(*) as total FROM email_queue {$whereClause}";

        $result = QueryUtils::querySingleRow($sql, $params);
        return $this->normalizeIntValue($result['total'] ?? 0);
    }

    /**
     * Get email queue statistics
     *
     * @return array<string, int> Statistics including total, sent, pending, and failed counts
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
                'total' => $this->normalizeIntValue($result['total'] ?? 0),
                'sent' => $this->normalizeIntValue($result['sent'] ?? 0),
                'pending' => $this->normalizeIntValue($result['pending'] ?? 0),
                'failed' => $this->normalizeIntValue($result['failed'] ?? 0),
            ];
        }

        return $stats;
    }

    /**
     * Get unique template names from email queue
     *
     * @return array<int, string> List of unique template names
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
            if (!array_key_exists('template_name', $row)) {
                continue;
            }

            $templateName = $row['template_name'];
            if (!is_string($templateName) || $templateName === '') {
                continue;
            }

            $templates[] = $templateName;
        }

        return $templates;
    }

    /**
     * Get email details by ID
     *
     * @param int $id Email queue ID
     * @return array<string, int|string|null>|null Email record or null if not found
     */
    public function getEmailById(int $id): ?array
    {
        $selectClause = implode(", ", self::EMAIL_QUEUE_COLUMNS);
        $sql = "SELECT {$selectClause} FROM email_queue WHERE id = ?";
        $result = QueryUtils::querySingleRow($sql, [$id]);

        if (!is_array($result)) {
            return null;
        }

        $emailRecord = [];
        foreach (self::EMAIL_QUEUE_COLUMNS as $column) {
            $value = $result[$column] ?? null;
            if (is_int($value) || is_string($value) || $value === null) {
                $emailRecord[$column] = $value;
                continue;
            }

            if (is_float($value) || is_bool($value)) {
                $emailRecord[$column] = (string) $value;
                continue;
            }

            $emailRecord[$column] = null;
        }

        return $emailRecord;
    }
}
