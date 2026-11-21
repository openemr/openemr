<?php

/**
 * ProductRegistrationService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEEnvBag;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\VersionService;

require_once(__DIR__ . '/../../interface/product_registration/exceptions/generic_product_registration_exception.php');

class ProductRegistrationService
{
    private readonly ClientInterface $httpClient;

    private readonly OEEnvBag $env;

    private readonly OEGlobalsBag $globals;

    public function __construct(
        ?ClientInterface $httpClient = null,
        private readonly VersionServiceInterface $versionService = new VersionService(),
        ?OEEnvBag $env = null,
        ?OEGlobalsBag $globals = null,
    ) {
        $this->httpClient = $httpClient ?? ServiceContainer::getGuzzle();
        $this->env = $env ?? OEEnvBag::getInstance();
        $this->globals = $globals ?? OEGlobalsBag::getInstance();
    }

    private function currentVersion(): string
    {
        return $this->versionService->getSoftwareVersion()->__toString();
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
        $currentVersion = $this->currentVersion();
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
        $row = QueryUtils::querySingleRow('SELECT `email` FROM `product_registration`');
        $email = is_array($row) ? $row['email'] ?? null : null;
        return is_string($email) ? $email : '';
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
    public function registerProduct(string $email): ?string
    {
        if ($email === '') {
            $this->optOutStrategy();
            return null;
        } else {
            return $this->optInStrategy($email);
        }
    }

    private function optInStrategy(string $email): string
    {
        // build the information array
        $info = ['email' => $email, 'version' => $this->currentVersion()];
        $distribution = $this->env->getString('OPENEMR_DOCKER_ENV_TAG');
        if ($distribution !== '') {
            // this will add standard package information if it exists
            $info['distribution'] = $distribution;
        }

        try {
            $reply = $this->httpClient->request('POST', 'https://reg.open-emr.org/api/registration', [
                RequestOptions::FORM_PARAMS => $info,
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::ALLOW_REDIRECTS => false,
                RequestOptions::VERIFY => $this->globals->getBoolean('http_verify_ssl', true),
            ]);
        } catch (GuzzleException $e) {
            throw new \GenericProductRegistrationException(xl('Server error: try again later'), 0, $e);
        }
        $responseCode = $reply->getStatusCode();

        $currentVersion = $this->currentVersion();
        switch ($responseCode) {
            case 201:
                $entry = $this->entryExist();
                if ($entry !== null) {
                    sqlStatement("UPDATE `product_registration` SET `email` = ?, `opt_out` = 0, `last_ask_version` = ? WHERE `id` = ?", [$email, $currentVersion, $entry]);
                } else {
                    sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`, `last_ask_version`) VALUES (?, 0, ?)", [$email, $currentVersion]);
                }
                return $email;
            default:
                throw new \GenericProductRegistrationException(xl('Server error: try again later'));
        }
    }

    // void... don't bother checking for success/failure.
    private function optOutStrategy()
    {
        $currentVersion = $this->currentVersion();
        $entry = $this->entryExist();
        if ($entry !== null) {
            sqlStatement("UPDATE `product_registration` SET `email` = null, `opt_out` = 1, `last_ask_version` = ? WHERE `id` = ?", [$currentVersion, $entry]);
        } else {
            sqlStatement("INSERT INTO `product_registration` (`email`, `opt_out`, `last_ask_version`) VALUES (null, 1, ?)", [$currentVersion]);
        }
    }

    private function entryExist(): ?int
    {
        $row = QueryUtils::querySingleRow('SELECT `id` FROM `product_registration`');
        $id = is_array($row) ? $row['id'] ?? null : null;
        return match (true) {
            is_int($id) => $id,
            is_string($id) && ctype_digit($id) => intval($id),
            default => null,
        };
    }
}
