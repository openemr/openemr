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

class RealWorldTesting
{
    private string $beginDate;
    private string $endDate;

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
        return $output;
    }

    // Number of generated CCDA documents.
    private function metric1(): string
    {
        $result = "";
        $check = sqlQuery("SELECT count(`id`) AS `count` FROM `ccda` WHERE `updated_date` >= ? AND `updated_date` <= ?", [$this->beginDate, $this->endDate]);
        if (!empty($check['count']) && $check['count'] > 0) {
            $result .= xl('Number of generated CCDA documents') . ': ' . $check['count'];
        } else {
            $result .= xl('No generated CCDA documents.');
        }
        return $result;
    }

    // Number of Direct messages sent and received.
    private function metric2(): array
    {
        $result = [];
        $check = sqlQuery("SELECT count(`id`) AS `count` FROM `direct_message_log` WHERE `status` = 'D' AND `create_ts` >= ? AND `create_ts` <= ?", [$this->beginDate, $this->endDate]);
        if (!empty($check['count']) && $check['count'] > 0) {
            $result[] = xl('Number of sent Direct messages') . ': ' . $check['count'];
        } else {
            $result[] = xl('No sent Direct messages.');
        }
        $check = sqlQuery("SELECT count(`id`) AS `count` FROM `direct_message_log` WHERE `status` = 'R' AND `create_ts` >= ? AND `create_ts` <= ?", [$this->beginDate, $this->endDate]);
        if (!empty($check['count']) && $check['count'] > 0) {
            $result[] = xl('Number of received Direct messages') . ': ' . $check['count'];
        } else {
            $result[] = xl('No received Direct messages.');
        }
        return $result;
    }

    // Number of QRDA imports.
    private function metric3(): string
    {
        $result = "";
        $check = sqlQuery("SELECT count(`id`) AS `count` FROM `audit_master` WHERE `is_qrda_document` = '1' AND `created_time` >= ? AND `created_time` <= ?", [$this->beginDate, $this->endDate]);
        if (!empty($check['count']) && $check['count'] > 0) {
            $result .= xl('Number QRDA imports') . ': ' . $check['count'];
        } else {
            $result .= xl('No QRDA imports.');
        }
        return $result;
    }

    // Number of generated CQM QRDA 3 reports.
    private function metric4(): string
    {
        $result = "";
        $check = sqlQuery("SELECT count(`id`) AS `count` FROM `log` WHERE `event` = 'qrda3-export' AND `success` = '1' AND `date` >= ? AND `date` <= ?", [$this->beginDate, $this->endDate]);
        if (!empty($check['count']) && $check['count'] > 0) {
            $result .= xl('Number CQM QRDA 3 reports') . ': ' . $check['count'];
        } else {
            $result .= xl('No CQM QRDA 3 reports.');
        }
        return $result;
    }

    // API use analytics, which will include number of successful requests, number of
    // unsuccessful requests, number of requests by patients, number of requests by
    // users, and number of requests categorized by each data category.
    private function metric5(): array
    {
        $result = [];
        $countSuccess = 0;
        $countFail = 0;
        $countUser = 0;
        $countPatient = 0;
        $arrayResources = [];
        $res = sqlStatement("SELECT l.`success`, al.`user_id`, al.`patient_id`, al.`request`
                             FROM `log` as l
                             INNER JOIN `api_log` as al
                             ON l.`id` = al.`log_id`
                             WHERE l.`date` >= ? AND l.`date` <= ?
                            ", [$this->beginDate, $this->endDate]);
        while ($row = sqlFetchArray($res)) {
            if (empty($row['success'])) {
                $countFail++;
            } else {
                $countSuccess++;
                if (!empty($row['user_id'])) {
                    $countUser++;
                } else if (!empty($row['patient_id'])) {
                    $countPatient++;
                }
                if (!empty($row['request'])) {
                    if (array_key_exists($row['request'], $arrayResources)) {
                        $arrayResources[$row['request']]++;
                    } else {
                        $arrayResources[$row['request']] = 1;
                    }
                }
            }
        }
        $result[] = xl('Successful API requests') . ': ' . $countSuccess;
        $result[] = xl('Unsuccessful API requests') . ': ' . $countFail;
        $result[] = xl('API requests by users') . ': ' . $countUser;
        $result[] = xl('API requests by patients') . ': ' . $countPatient;
        foreach ($arrayResources as $key => $value) {
            $result[] = xl('API requests for resource') . ' ' . $key . ': ' . $value;
        }
        return $result;
    }
}
