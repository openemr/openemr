<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

class WenoPharmaciesJson
{
    /** A full rebuild older than this forces the next run to rebuild. */
    private const FULL_REBUILD_MAX_AGE_DAYS = 7;

    private readonly string $encrypted;

    /** True when buildJson() asked Weno for a daily delta rather than the full directory. */
    private bool $isDailyImport = false;

    public function __construct(private readonly CryptoInterface $cryptoGen)
    {
        // Build the JSON data
        $jobJson = $this->buildJson();
        // Define encryption method and key
        $method = "aes-256-cbc";
        $key = substr(hash('sha256', $this->wenoEncryptionKey(), true), 0, 32);
        // Define initialization vector (IV)
        $iv = str_repeat(chr(0x0), 16);
        // Encrypt the JSON data
        $this->encrypted = base64_encode(openssl_encrypt($jobJson, $method, $key, OPENSSL_RAW_DATA, $iv));
    }

    public function getEncryptedData(): string
    {
        return $this->encrypted;
    }

    private function buildJson(): string
    {
        // Weno's flag is inverted from how we think about it: Daily = "N" asks
        // for the whole directory, "Y" asks for that day's delta.
        $needsFullRebuild = $this->needsFullRebuild();

        $jobJson = [
            "UserEmail" => $this->providerEmail(),
            "MD5Password" => $this->providerPassword(),
            "ExcludeNonWenoTest" => "N",
            "Daily" => $needsFullRebuild ? 'N' : 'Y',
        ];

        // Remember which payload we asked for; the importer has to match it. A
        // daily delta must upsert, a full directory rebuilds the table.
        $this->isDailyImport = !$needsFullRebuild;

        return text(json_encode($jobJson));
    }

    /**
     * Decide whether this run rebuilds the whole directory.
     *
     * Rebuild when any of these hold:
     *   - the pharmacy table is empty (nothing to apply a delta to),
     *   - it is Monday (the normal weekly rebase),
     *   - no full rebuild has succeeded within FULL_REBUILD_MAX_AGE_DAYS.
     *
     * That last one is the recovery path. Deltas never remove pharmacies Weno
     * dropped, so if Monday's rebuild failed the table drifts all week. Checking
     * the log means the next run picks the rebuild back up instead of waiting
     * for the following Monday.
     */
    private function needsFullRebuild(): bool
    {
        $wenoLog = new WenoLogService();

        if (!(new PharmacyService())->checkWenoDb()) {
            return true;
        }

        if (date("l") === "Monday") {
            return true;
        }

        if ($wenoLog->isFullRebuildOverdue(self::FULL_REBUILD_MAX_AGE_DAYS)) {
            $lastRebuild = $wenoLog->getLastFullRebuildDate() ?? 'never';
            $wenoLog->insertWenoLog(
                "Pharmacy Directory",
                "Full rebuild overdue (last: " . $lastRebuild . ") - forcing a full directory download"
            );
            return true;
        }

        return false;
    }

    public function storePharmacyData(): int|false
    {
        $wenoLog = new WenoLogService();
        $downloadWenoPharmacies = new DownloadWenoPharmacies();

        $url = $this->wenoPharmacyDirectoryLink() . "?useremail=" . urlencode((string) $this->providerEmail()) . "&data=" . urlencode($this->encrypted);
        // Log the endpoint only. The query string carries the admin email and the
        // encrypted credential payload, and weno_download_log.data_in_context is
        // readable from the download log viewer.
        $wenoLog->insertWenoLog("Pharmacy Directory", "Background Initiated Download started", $this->wenoPharmacyDirectoryLink());
        error_log('Background Initiated Pharmacy Download Started.');

        // Match the import mode to the payload requested in buildJson(): a daily
        // delta upserts against the unique NCPDP_safe key, a full directory
        // rebuilds. Passing insert-only for a daily payload makes every repeated
        // row a duplicate-key failure and rolls the whole import back.
        return $downloadWenoPharmacies->downloadAndImport($url, !$this->isDailyImport, 'Pharmacy Directory');
    }

    private function providerEmail()
    {
        if (empty(OEGlobalsBag::getInstance()->get('weno_admin_username'))) {
            return '';
        }
        return OEGlobalsBag::getInstance()->get('weno_admin_username');
    }

    private function providerPassword(): string
    {
        if (empty(OEGlobalsBag::getInstance()->get('weno_admin_password'))) {
            return '';
        }
        return md5($this->cryptoGen->decryptFromDatabase(OEGlobalsBag::getInstance()->get('weno_admin_password')));
    }

    private function wenoEncryptionKey(): string
    {
        $key = OEGlobalsBag::getInstance()->getString('weno_encryption_key');
        if ($key === '') {
            throw new \RuntimeException('Weno key missing');
        }
        return $this->cryptoGen->decryptFromDatabase($key);
    }

    private function wenoPharmacyDirectoryLink(): string
    {
        return "https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory";
    }

    public function checkBackgroundService(): bool|string
    {
        $sql = "SELECT `active` FROM background_services WHERE `name` = 'WenoExchangePharmacies'";
        $activeStatus = sqlQuery($sql);
        if ($activeStatus['active'] == 0) {
            sqlStatement("UPDATE `background_services` SET `active` = 1 WHERE `name` = 'WenoExchangePharmacies'");
            error_log("WenoExchangePharmacies background service reactivated.");
        }
        return true;
    }
}
