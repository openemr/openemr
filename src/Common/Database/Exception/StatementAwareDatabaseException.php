<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database\Exception;

use Throwable;

abstract class StatementAwareDatabaseException extends DatabaseException
{
    public function __construct(
        private readonly string $statement,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                '%s Statement: %s',
                $message,
                $statement,
            ),
            $code,
            $previous
        );
    }

    public function getSqlStatement(): string
    {
        return $this->statement;
    }
}
