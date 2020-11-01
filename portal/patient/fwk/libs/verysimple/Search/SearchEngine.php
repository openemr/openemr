<?php

/**
 * @package    verysimple::Search
 */

/**
 * include required files
 */
require_once("SearchRank.php");

/**
 * This is a base class for providing SearchEngine functionality
 *
 * @package verysimple::Search
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class SearchEngine
{
    protected $Key;
    protected $Pass;
    public $FailedRequests = 0;

    /**
     * Constructor for SearchEngine instantiation
     *
     * @param string $key
     *          the API key for this search engine
     * @param string $pass
     *          the API password (if required)
     */
    final function __construct($key, $pass = "")
    {
        $this->Key = $key;
        $this->Pass = $pass;

        $this->Init();
    }

    /**
     * Init is called by the constructor and can be overridden by inherited classes
     */
    function Init()
    {
    }
}
