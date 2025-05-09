<?php

/**
 * WenoPharmacyService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use Exception;

class WenoLogService
{
    public function __construct()
    {
        $this->validateTable();
    }

    public function getLastPrescriptionLogStatus(): bool|array|null
    {
        $params  = "Sync Report";
        $sql = "SELECT * FROM weno_download_log WHERE VALUE = ?  ORDER BY `created_at` DESC, `id` DESC LIMIT 1";

        return sqlQuery($sql, [$params]);
    }

    public function getLastPharmacyDownloadStatus($lastStatus = ''): bool|array|null
    {
        $params = "Pharmacy Directory";
        $v = ['count' => 0, 'created_at' => 'Never', 'status' => 'Possibly download is in progress.'];
        $vsql = sqlQuery("SELECT * FROM `weno_download_log` WHERE `value` = ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1", [$params]);
        if (!$vsql) {
            return $v;
        }
        $v = $vsql;
        $count = sqlQuery("SELECT COUNT(`id`) as count FROM `weno_pharmacy`");
        $v['count'] = $count['count'] ?? 0;

        if (!empty($lastStatus)) {
            $vsql = sqlQuery("SELECT `created_at` FROM `weno_download_log` WHERE `value` = ? AND `status` LIKE ? ORDER BY `created_at` DESC, `id` DESC LIMIT 1", [$params, "$lastStatus%"]);
            if ($vsql) {
                $v['created_at'] = $vsql['created_at'];
            }
        }

        return $v;
    }

    public function insertWenoLog($value, $status, $data_in_context = ''): bool|string
    {
        $bind = [$value, $status, $data_in_context];
        $sql = "INSERT INTO weno_download_log SET value = ?, status = ?, data_in_context = ?";
        try {
            sqlInsert($sql, $bind);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function scrapeWenoErrorHtml($content)
    {
        $error = ['is_error' => false, 'type' => 'other', 'messageText' => '', 'messageHtml' => ''];
        if (empty($content)) {
            return $error;
        }
        $content = trim(preg_replace("/\r?\n|\r/", '</p><p>', $content));
        $content_html = strip_tags($content, '<div><nav><p><textarea>');
        $content = strip_tags($content);
        $content = preg_replace('/\s+\r\n/', ' ', $content);

        if (empty($content)) {
            return $error;
        }
        $doc = new \DOMDocument();
        @$doc->loadHTML($content_html);
        $xpath = new \DOMXPath($doc);
        $nodes = $xpath->query('//textarea');
        if ($nodes->length <= 0) {
            return $error;
        }
        $message = "";
        foreach ($nodes as $node) {
            $message .= $node->nodeValue;
        }
        $type = 'other';
        if (stripos($message, "Exceeded_download_limits") !== false) {
            $type = "Exceeded_download_limits";
        }
        return ['is_error' => true, 'type' => $type, 'messageText' => trim($message), 'messageHtml' => trim($content_html)];
    }

    public function validateTable()
    {
        $isIt = sqlQuery("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'weno_download_log' AND COLUMN_NAME = 'data_in_context'");
        if (empty($isIt)) {
            sqlStatement("ALTER TABLE `weno_download_log` ADD `data_in_context` TEXT");
            return true;
        }
        return false;
    }
}
