<?php

/**
 * Invalid Site Name Exception
 *
 * Thrown when a site name fails validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Exceptions;

class InvalidSiteNameException extends SiteAdminException
{
    public function __construct(private readonly string $siteName, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $finalMessage = $message ?: "Invalid site name format: {$this->siteName}";
        parent::__construct($finalMessage, $code, $previous);
    }

    public function getSiteName(): string
    {
        return $this->siteName;
    }
}
