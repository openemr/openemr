<?php

/** @package    verysimple::DB::Reflection */

/**
 * DBConnectionString specifies the connection information
 *
 * @package verysimple::DB::Reflection
 * @author Jason Hinkle
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class DBConnectionString
{
    /**
     * Create a new instance of a DBConnectionString
     *
     * @access public
     * @param string $Host
     * @param string $Port
     * @param string $Username
     * @param string $Password
     * @param string $$dbname
     */
    function __construct(public $Host = "", public $Port = "", public $Username = "", public $Password = "", public $DBName = "", public $Type = "mysql")
    {
    }
}
