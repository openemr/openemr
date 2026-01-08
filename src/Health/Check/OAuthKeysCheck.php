<?php

/**
 * OAuthKeysCheck - Verifies OAuth2 key files exist
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com/
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Health\Check;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Health\HealthCheckInterface;
use OpenEMR\Health\HealthCheckResult;

class OAuthKeysCheck implements HealthCheckInterface
{
    public const NAME = 'oauth_keys';

    public function getName(): string
    {
        return static::NAME;
    }

    public function check(): HealthCheckResult
    {
        try {
            $globals = OEGlobalsBag::getInstance();
            $siteDir = $globals->get('OE_SITE_DIR');

            if (empty($siteDir)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'Site directory not configured'
                );
            }

            $certsDir = $siteDir . '/documents/certificates';
            $privateKey = $certsDir . '/oaprivate.key';
            $publicKey = $certsDir . '/oapublic.key';

            if (!file_exists($privateKey)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'OAuth private key not found'
                );
            }

            if (!file_exists($publicKey)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'OAuth public key not found'
                );
            }

            if (!is_readable($privateKey) || !is_readable($publicKey)) {
                return new HealthCheckResult(
                    $this->getName(),
                    false,
                    'OAuth keys are not readable'
                );
            }

            return new HealthCheckResult($this->getName(), true);
        } catch (\Throwable $e) {
            return new HealthCheckResult(
                $this->getName(),
                false,
                $e->getMessage()
            );
        }
    }
}
