<?php

/**
 * Database Query Exception
 *
 * Thrown when database queries fail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Exceptions;

class DatabaseQueryException extends SiteAdminException
{
    public function __construct(
        string $message,
        private readonly string $sanitizedQuery = '',
        private readonly string $errorMessage = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getSanitizedQuery(): string
    {
        return $this->sanitizedQuery;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
