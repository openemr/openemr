<?php

/**
 * RealWorldTesting class.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports;

use OpenEMR\Common\Database\QueryUtils;

class RealWorldTesting
{
    private readonly string $beginDate;
    private readonly string $endDate;

    public function __construct(string $beginDate, string $endDate)
    {
        $this->beginDate = $beginDate . ' 00:00:00';
        $this->endDate = $endDate . ' 23:59:59';
    }

    public function renderReport(): string
    {
        $output = text(xl('Date') . ': ' . oeFormatShortDate()) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 1') . '</span><br />';
        $output .= text($this->metric1()) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 2') . '</span><br />';
        $output .= nl2br(text(implode("\n", $this->metric2()))) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 3') . '</span><br />';
        $output .= text($this->metric3()) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 4') . '</span><br />';
        $output .= text($this->metric4()) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 5') . '</span><br />';
        $output .= nl2br(text(implode("\n", $this->metric5()))) . '<br /><br />';
        $output .= "<span class='font-weight-bold'>" . xlt('Metric 6') . '</span><br />';
        $output .= text($this->metric6()) . '<br /><br />';
        return $output;
    }

    // Number of generated CCDA documents.
    private function metric1(): string
    {
        $count = $this->periodCount(
            "SELECT count(`id`) AS `count` FROM `ccda`
             WHERE `updated_date` >= ? AND `updated_date` <= ?"
        );
        return $count > 0
            ? xl('Number of generated CCDA documents') . ': ' . $count
            : xl('No generated CCDA documents.');
    }

    /**
     * Number of Direct messages sent and received.
     *
     * @return list<string>
     */
    private function metric2(): array
    {
        $sent = $this->periodCount(
            "SELECT count(`id`) AS `count` FROM `direct_message_log`
             WHERE `status` = 'S' AND `create_ts` >= ? AND `create_ts` <= ?"
        );
        $received = $this->periodCount(
            "SELECT count(`id`) AS `count` FROM `direct_message_log`
             WHERE `status` = 'R' AND `create_ts` >= ? AND `create_ts` <= ?"
        );

        return [
            $sent > 0
                ? xl('Number of sent Direct messages') . ': ' . $sent
                : xl('No sent Direct messages.'),
            $received > 0
                ? xl('Number of received Direct messages') . ': ' . $received
                : xl('No received Direct messages.'),
        ];
    }

    // Number of QRDA imports.
    private function metric3(): string
    {
        $count = $this->periodCount(
            "SELECT count(`id`) AS `count` FROM `audit_master`
             WHERE `is_qrda_document` = '1' AND `created_time` >= ? AND `created_time` <= ?"
        );
        return $count > 0
            ? xl('Number QRDA imports') . ': ' . $count
            : xl('No QRDA imports.');
    }

    // Number of generated CQM QRDA 3 reports.
    private function metric4(): string
    {
        $count = $this->periodCount(
            "SELECT count(`id`) AS `count` FROM `log`
             WHERE `event` = 'qrda3-export' AND `success` = '1'
               AND `date` >= ? AND `date` <= ?"
        );
        return $count > 0
            ? xl('Number CQM QRDA 3 reports') . ': ' . $count
            : xl('No CQM QRDA 3 reports.');
    }

    /**
     * API use analytics: successful and unsuccessful requests, requests by
     * users and by patients, and requests per data category.
     *
     * @return list<string>
     */
    private function metric5(): array
    {
        $metrics = $this->apiUseMetrics();

        $result = [];
        $result[] = xl('Successful API requests') . ': ' . $metrics->successful;
        $result[] = xl('Unsuccessful API requests') . ': ' . $metrics->unsuccessful;
        $result[] = xl('API requests by users') . ': ' . $metrics->byUsers;
        $result[] = xl('API requests by patients') . ': ' . $metrics->byPatients;
        foreach ($metrics->byResource as $resource => $count) {
            $result[] = xl('API requests for resource') . ' ' . $resource . ': ' . $count;
        }
        return $result;
    }

    /**
     * Every metric counts rows in the same reporting period, and `COUNT()`
     * columns are typed `mixed`, so the bind and the narrowing live here.
     */
    private function periodCount(string $sql): int
    {
        return MetricCount::fromColumn(
            QueryUtils::querySingleRow($sql, [$this->beginDate, $this->endDate]),
            'count'
        );
    }

    /**
     * Aggregating in SQL keeps the result set proportional to the number of
     * distinct resources rather than to the number of API requests in the
     * period, which the previous row-by-row loop read into PHP in full.
     *
     * `log`.`success` is a nullable tinyint; NULL and 0 both count as a failure,
     * matching the `empty()` test this replaced. A request is attributed to a
     * user when `user_id` is set, and only otherwise to a patient.
     */
    private function apiUseMetrics(): ApiUseMetrics
    {
        $period = [$this->beginDate, $this->endDate];

        $totals = QueryUtils::querySingleRow(
            "SELECT
                 SUM(l.`success` IS NULL OR l.`success` = 0) AS `fail_count`,
                 SUM(l.`success` IS NOT NULL AND l.`success` <> 0) AS `success_count`,
                 SUM(l.`success` IS NOT NULL AND l.`success` <> 0
                     AND al.`user_id` <> 0) AS `user_count`,
                 SUM(l.`success` IS NOT NULL AND l.`success` <> 0
                     AND al.`user_id` = 0 AND al.`patient_id` <> 0) AS `patient_count`
             FROM `log` AS l
             INNER JOIN `api_log` AS al ON l.`id` = al.`log_id`
             WHERE l.`date` >= ? AND l.`date` <= ?",
            $period
        );

        // `request <> '0'` preserves the previous empty() test, which treated the
        // string '0' as absent. Ordering by resource replaces the old insertion
        // order, which was whatever the unordered scan happened to return.
        $resourceRows = QueryUtils::fetchRecords(
            "SELECT al.`request` AS `resource`, COUNT(*) AS `request_count`
             FROM `log` AS l
             INNER JOIN `api_log` AS al ON l.`id` = al.`log_id`
             WHERE l.`date` >= ? AND l.`date` <= ?
               AND l.`success` IS NOT NULL AND l.`success` <> 0
               AND al.`request` <> '' AND al.`request` <> '0'
             GROUP BY al.`request`
             ORDER BY al.`request`",
            $period
        );

        return ApiUseMetrics::fromRows($totals, $resourceRows);
    }

    // Number of Electronic Health Information (EHI) Exports.
    private function metric6(): string
    {
        // ehi_export_job_tasks only exists where the Electronic Health
        // Information Exporter module is installed.
        $table = QueryUtils::querySingleRow("SHOW TABLES LIKE 'ehi_export_job_tasks'");
        if (!is_array($table) || $table === []) {
            return xl('No Electronic Health Information (EHI) Exports.');
        }

        $count = $this->periodCount(
            "SELECT count(`ehi_task_id`) AS `count` FROM `ehi_export_job_tasks`
             WHERE `status` = 'completed'
               AND `completion_date` >= ? AND `completion_date` <= ?"
        );
        return $count > 0
            ? xl('Number of Electronic Health Information (EHI) Exports') . ': ' . $count
            : xl('No Electronic Health Information (EHI) Exports.');
    }
}
