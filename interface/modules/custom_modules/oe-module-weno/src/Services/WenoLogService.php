<?php

/**
 * WenoPharmacyService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 omega systems group international <info@omegasystemsgroup.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Database\QueryUtils;

class WenoLogService
{
    /**
     * Status prefix written by a successful full-directory rebuild.
     *
     * These values are persisted to weno_download_log.status and are read back by
     * getLastFullRebuildDate(). Changing either string orphans every historical
     * row, so treat them as frozen: a rename needs a data migration. Both keep the
     * 'Success' prefix that the dashboard widgets match on.
     */
    public const FULL_REBUILD_STATUS = 'Success full rebuild';

    /** @see self::FULL_REBUILD_STATUS - persisted, effectively frozen. */
    public const DAILY_UPDATE_STATUS = 'Success daily update';

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

    /**
     * When did the last successful full-directory rebuild finish?
     *
     * Returns null when the log has no record of one, which is treated the same
     * as an overdue rebuild by callers.
     */
    public function getLastFullRebuildDate(): ?string
    {
        $row = QueryUtils::querySingleRow(
            "SELECT `created_at` FROM `weno_download_log`
              WHERE `value` = ? AND `status` LIKE ?
              ORDER BY `created_at` DESC, `id` DESC LIMIT 1",
            ['Pharmacy Directory', self::FULL_REBUILD_STATUS . '%']
        );
        if (!is_array($row)) {
            return null;
        }
        $createdAt = $row['created_at'] ?? null;

        return is_string($createdAt) && $createdAt !== '' ? $createdAt : null;
    }

    /**
     * True when no full rebuild has completed inside $maxAgeDays.
     *
     * The weekly rebuild is what re-bases the directory: daily deltas never
     * remove pharmacies Weno dropped. If Monday's run failed nothing retried it,
     * so the table would drift on deltas until the following Monday. Callers use
     * this to force a rebuild on the next run instead of waiting.
     */
    public function isFullRebuildOverdue(int $maxAgeDays = 7): bool
    {
        return self::isRebuildDateStale($this->getLastFullRebuildDate(), $maxAgeDays);
    }

    /**
     * The staleness arithmetic, split out so it can be tested without a database.
     *
     * A missing or unparsable date counts as stale: if we cannot prove a rebuild
     * happened recently, rebuilding is the safe answer.
     *
     * @param int|null $now Unix time to measure against; defaults to time().
     */
    public static function isRebuildDateStale(?string $lastRebuild, int $maxAgeDays = 7, ?int $now = null): bool
    {
        if ($lastRebuild === null || trim($lastRebuild) === '') {
            return true;
        }
        $lastTime = strtotime($lastRebuild);
        if ($lastTime === false) {
            return true;
        }

        return (($now ?? time()) - $lastTime) > ($maxAgeDays * 86400);
    }

    /**
     * One summary of pharmacy directory health for the UI to render.
     *
     * Both the prescribe fragment and the pharmacy selector were each deciding
     * this for themselves off the raw log status string. Deciding it once keeps
     * the two views from disagreeing, and keeps internal status wording out of
     * the templates.
     *
     * @return array{count: int, lastSuccess: string, lastRunFailed: bool, isStale: bool, isHealthy: bool}
     */
    public function getPharmacyDirectoryHealth(): array
    {
        $log = $this->getLastPharmacyDownloadStatus('Success');
        $log = is_array($log) ? $log : [];

        $countRaw = $log['count'] ?? 0;
        $count = is_numeric($countRaw) ? (int) $countRaw : 0;

        $status = $log['status'] ?? '';
        $status = is_string($status) ? $status : '';

        $lastSuccess = $log['created_at'] ?? '';
        $lastSuccess = is_string($lastSuccess) && $lastSuccess !== 'Never' ? $lastSuccess : '';

        $lastRunFailed = !str_starts_with($status, 'Success');
        $isStale = $this->isFullRebuildOverdue();

        return [
            'count' => $count,
            'lastSuccess' => $lastSuccess,
            'lastRunFailed' => $lastRunFailed,
            'isStale' => $isStale,
            'isHealthy' => $count > 0 && !$lastRunFailed && !$isStale,
        ];
    }

    public function insertWenoLog($value, $status, $data_in_context = ''): bool|string
    {
        $bind = [$value, $status, $data_in_context];
        $sql = "INSERT INTO weno_download_log SET value = ?, status = ?, data_in_context = ?";
        try {
            sqlInsert($sql, $bind);
        } catch (\Throwable $e) {
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
        $content = trim((string) preg_replace("/\r?\n|\r/", '</p><p>', (string) $content));
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

    public function validateTable(): bool
    {
        $isIt = sqlQuery("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'weno_download_log' AND COLUMN_NAME = 'data_in_context'");
        if (empty($isIt)) {
            sqlStatement("ALTER TABLE `weno_download_log` ADD `data_in_context` TEXT");
            return true;
        }
        return false;
    }
}
