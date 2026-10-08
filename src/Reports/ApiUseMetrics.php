<?php

/**
 * API use analytics for the Real World Testing report.
 *
 * The counts are produced by aggregate queries, so every value arrives from
 * the database as `mixed`; MetricCount narrows them.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Reports;

final readonly class ApiUseMetrics
{
    /**
     * @param array<string, int> $byResource keyed by FHIR resource, request count
     */
    private function __construct(
        public int $successful,
        public int $unsuccessful,
        public int $byUsers,
        public int $byPatients,
        public array $byResource,
    ) {
    }

    /**
     * @param array<array-key, mixed>|false|null $totals single row of SUM() columns
     * @param iterable<mixed> $resourceRows request/count pairs as fetchRecords() returns
     */
    public static function fromRows(array|false|null $totals, iterable $resourceRows): self
    {
        $byResource = [];
        foreach ($resourceRows as $resourceRow) {
            if (!is_array($resourceRow)) {
                continue;
            }
            $resource = $resourceRow['resource'] ?? null;
            if (!is_string($resource) || $resource === '') {
                continue;
            }
            $byResource[$resource] = MetricCount::fromValue($resourceRow['request_count'] ?? null);
        }

        return new self(
            MetricCount::fromColumn($totals, 'success_count'),
            MetricCount::fromColumn($totals, 'fail_count'),
            MetricCount::fromColumn($totals, 'user_count'),
            MetricCount::fromColumn($totals, 'patient_count'),
            $byResource,
        );
    }
}
