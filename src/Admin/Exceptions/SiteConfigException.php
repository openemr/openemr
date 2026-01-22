<?php

/**
 * Site Config Exception
 *
 * Thrown when site configuration is missing or invalid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Exceptions;

class SiteConfigException extends SiteAdminException
{
    public function __construct(string $message = '', private readonly string $configPath = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getConfigPath(): string
    {
        return $this->configPath;
    }
}
