<?php

/**
 * SqlQueryException is thrown when a sql statement error has occurred and allows the system to catch and handle the
 * problem.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use Throwable;

class SqlQueryException extends \RuntimeException
{
    /**
     * @var string The sql statement that threw an error.
     */
    private $sqlStatement;

    public function __construct($sqlStatement = "", $message = "", $code = 0, Throwable $previous = null)
    {
        $this->sqlStatement = $sqlStatement;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string The statement that was attempted to execute
     */
    public function getSqlStatement()
    {
        return $this->sqlStatement;
    }
}
