<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database\Exception;

use Throwable;

class DatabaseException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $message,
            $code,
            $previous,
        );
    }
}
