<?php

/**
 * The Ensora eRx settings the launch page reads.
 *
 * interface/eRxGlobals.php implements this, so code in src/ never names a
 * class that only interface/ pages load.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

interface ErxGlobalsInterface
{
    /** Empty when unset */
    public function getOpenEMRSiteDirectory(): string;

    /** The URL eRx requests post to; empty when unset */
    public function getPath(): string;

    public function getAccountId(): string;

    /** Whether only active prescriptions and medications are uploaded */
    public function getUploadActive(): bool;

    public function getDefaultPatientCountry(): string;

    /**
     * @return array<mixed> index 0 partner name, 1 account name, 2 password
     */
    public function getCredentials(): array;

    /** Debug flags: 1 logs the request XML, 2 logs the response */
    public function getDebugSetting(): int;
}
