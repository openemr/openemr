<?php

/**
 * Builds SQL filter clauses for email queue reporting without DB access.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Reports\Email;

final class EmailQueueFilterBuilder
{
    /**
     * Get a normalized non-empty string filter value.
     *
     * @param array<string, mixed> $filters
     */
    public function getFilterValue(array $filters, string $key): ?string
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

    public function normalizeIntValue(mixed $value): int
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
    public function buildFilterClause(array $filters): array
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
}
