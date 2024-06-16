<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoGen;

class WenoPharmaciesJson
{
    private CryptoGen $cryptoGen;
    private string $encrypted;

    public function __construct(CryptoGen $cryptoGen)
    {
        $this->cryptoGen = $cryptoGen;
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
        return text(json_encode($jobJson));
    }

    public function storePharmacyData(): ?string
    {
        $downloadWenoPharmacies = new DownloadWenoPharmacies();

        $url = $this->wenoPharmacyDirectoryLink() . "?useremail=" . urlencode($this->providerEmail()) . "&data=" . urlencode($this->encrypted);
        $storageLocation = $storeLocation = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/";
        $path_to_extract = $storageLocation;
        $storeLocation .= "weno_pharmacy.zip";
        $downloadWenoPharmacies->retrieveDataFile($url, $storageLocation);
        return $downloadWenoPharmacies->extractFile($path_to_extract, $storeLocation);
    }

    private function providerEmail()
    {
        if (empty($GLOBALS['weno_admin_username'])) {
            return '';
        }
        return $GLOBALS['weno_admin_username'];
    }

    private function providerPassword(): string
    {
        if (empty($GLOBALS['weno_admin_password'])) {
            return '';
        }
        return md5($this->cryptoGen->decryptStandard($GLOBALS['weno_admin_password']));
    }

    private function wenoEncryptionKey(): bool|string
    {
        if (empty($GLOBALS['weno_encryption_key'])) {
            return '';
        }
        return $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
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
