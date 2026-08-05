<?php

/**
 * Interface for the OpenEMR installer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Installer;

use Psr\Log\LoggerInterface;

/**
 * @phpstan-type InstallParams array{
 *   iuser?: string,
 *   iuserpass?: string,
 *   iuname?: string,
 *   iufname?: string,
 *   igroup?: string,
 *   i2faenable?: string,
 *   i2fasecret?: string,
 *   server?: string,
 *   loginhost?: string,
 *   port?: int|string,
 *   root?: string,
 *   rootpass?: string,
 *   login?: string,
 *   pass?: string,
 *   dbname?: string,
 *   collate?: string,
 *   site?: string,
 *   source_site_id?: string,
 *   clone_database?: string,
 *   no_root_db_access?: string,
 *   development_translations?: string,
 *   new_theme?: string,
 *   custom_globals?: string,
 * }
 */
interface InstallerInterface
{
    public function setLogger(LoggerInterface $logger): void;

    /**
     * @param InstallParams $params
     */
    public function install(array $params): bool;

    public function getErrorMessage(): string;
}
