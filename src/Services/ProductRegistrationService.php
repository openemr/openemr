<?php

/**
 * ProductRegistrationService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\VersionService;

require_once(__DIR__ . '/../../interface/product_registration/exceptions/generic_product_registration_exception.php');

class ProductRegistrationService
{
    private readonly ClientInterface $httpClient;

    public function __construct(?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?? ServiceContainer::getGuzzle();
    }

    public function getProductDialogStatus(): array
    {
        $row = sqlQuery("SELECT * FROM `product_registration`");
        if (empty($row)) {
            $row = [];
        }
        $email = $row['email'] ?? null;
        $lastAskVersion = $row['last_ask_version'] ?? '';
        $optOut = $row['opt_out'] ?? null;
        $telemetry_disabled = $row['telemetry_disabled'] ?? null;

        $row['allowEmail'] = 0; // if show email dialog
        $row['allowTelemetry'] = 0; // if show telemetry dialog
        $row['allowRegisterDialog'] = 0; // if show registration dialog
        $currentVersion = (string) (new VersionService())->getSoftwareVersion();
        if ($currentVersion != $lastAskVersion) {
            // Change in version (or empty entry), so ignore opt outs and show the dialog if empty email or telemetry not enabled
            //  (if do show the dialog, then return if show email and/or telemetry dialog)
            if (empty($email) || $telemetry_disabled == 1 || $telemetry_disabled == null) {
                $row['allowRegisterDialog'] = 1;
                if (empty($email)) {
                    $row['allowEmail'] = 1;
                }
                if ($telemetry_disabled == 1 || $telemetry_disabled == null) {
                    $row['allowTelemetry'] = 1;
                }
            }
        } else {
            // No change in version, so do not show the dialog if has opted out of both email and telemetry
            //  (if do show the dialog, then return if show email and/or telemetry dialog)
            if ($telemetry_disabled == null || $optOut == null) {
                $row['allowRegisterDialog'] = 1;
                if ($optOut == null) {
                    $row['allowEmail'] = 1;
                }
                if ($telemetry_disabled == null) {
                    $row['allowTelemetry'] = 1;
                }
            }
        }

        return $row;
    }

    public function getRegistrationEmail(): string
    {
        return sqlQuery("SELECT `email` FROM `product_registration`")['email'] ?? '';
    }

    public function getRegistrationStatus(): string
    {
        $row = sqlQuery("SELECT * FROM `product_registration`");
        if (empty($row)) {
            $row = [];
        }
        $email = $row['email'] ?? '';
        $optOut = $row['opt_out'] ?? null;

        return match (true) {
            empty($row) || $optOut === null => 'UNREGISTERED',
            !empty($email) => 'REGISTERED',
            $optOut == 1 => 'OPT_OUT',
            default => 'UNKNOWN', // This should never happen, but just in case
        };
    }

    /**
     * @throws \GenericProductRegistrationException
     */
    public function registerProduct($email)
    {
        if (empty($email)) {
            $this->optOutStrategy();
            return null;
        } else {
            return $this->optInStrategy($email);
        }
    }

    private function optInStrategy($email)
    {
        // build the information array
        $info = ['email' => $email, 'version' => (string) (new VersionService())->getSoftwareVersion()];
        if (!empty(getenv('OPENEMR_DOCKER_ENV_TAG', true))) {
            // this will add standard package information if it exists
            $info['distribution'] = getenv('OPENEMR_DOCKER_ENV_TAG', true);
        }

        try {
            $reply = $this->httpClient->request('POST', 'https://reg.open-emr.org/api/registration', [
                RequestOptions::FORM_PARAMS => $info,
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::VERIFY => OEGlobalsBag::getInstance()->getBoolean('http_verify_ssl', true),
            ]);
        } catch (GuzzleException $e) {
            throw new \GenericProductRegistrationException(xl('Server error: try again later'), 0, $e);
        }
        $responseCode = $reply->getStatusCode();

        $currentVersion = (string) (new VersionService())->getSoftwareVersion();
        switch ($responseCode) {
            case 201:
                $entry = $this->entryExist();
                if ($entry) {
                    sqlStatement("UPDATE `product_registration` SET `email` = ?, `opt_out` = 0, `last_ask_version` = ? WHERE `id` = ?", [$email, $currentVersion, $entry]);
                } else {
                    sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`, `last_ask_version`) VALUES (?, 0, ?)", [$email, $currentVersion]);
                }
                return $email;
                break;
            default:
                throw new \GenericProductRegistrationException(xl("Server error: try again later"));
        }
    }

    // void... don't bother checking for success/failure.
    private function optOutStrategy()
    {
        $currentVersion = (string) (new VersionService())->getSoftwareVersion();
        $entry = $this->entryExist();
        if ($this->entryExist()) {
            sqlStatement("UPDATE `product_registration` SET `email` = null, `opt_out` = 1, `last_ask_version` = ? WHERE `id` = ?", [$currentVersion, $entry]);
        } else {
            sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`, `last_ask_version`) VALUES (null, 1, ?)", [$currentVersion]);
        }
    }

    private function entryExist(): int|false
    {
        $row = sqlQuery("SELECT * FROM `product_registration`");
        return $row['id'] ?? false;
    }
}
