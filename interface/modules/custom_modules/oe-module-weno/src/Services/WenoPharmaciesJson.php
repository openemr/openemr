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
    private readonly string $encrypted;
    /** True when buildJson() requested a daily delta rather than a full directory. */
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
        $checkWenoDb = new PharmacyService();
        $has_data = $checkWenoDb->checkWenoDb();
        $jobJson = [
            "UserEmail" => $this->providerEmail(),
            "MD5Password" => $this->providerPassword(),
            "ExcludeNonWenoTest" => "N",
            "Daily" => 'N'
        ];
        if (date("l") != "Monday" && $has_data) {
            $jobJson["Daily"] = "Y";
        } elseif (date("l") != "Monday" && !$has_data) {
            // get a weekly
            $jobJson["Daily"] = "N"; // in case table was emptied unintentionally
        }
        // Remember which payload we asked Weno for; the importer needs the same
        // mode. A daily delta must upsert, a full directory rebuilds the table.
        $this->isDailyImport = ($jobJson["Daily"] === "Y");

        return text(json_encode($jobJson));
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
