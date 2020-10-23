<?php

/** @package    verysimple::XML */

/**
 * Exception thrown by XMLUtil with an error occured during parsing
 *
 * @package verysimple::XML
 * @author VerySimple Inc.
 * @copyright 1997-2008 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class ParseException extends Exception
{
    /**
     * Redefine the exception so message isn't optional
     *
     * @param
     *          $message
     * @param
     *          $code
     */
    public function __construct($message, $code = 0)
    {
        parent::__construct($message, $code);
    }

    /**
     * String representation of exception
     */
    public function __toString()
    {
        $pair = explode("]", $this->message);
        return "ParseException" . (count($pair) > 1 ? $pair [1] : $pair [0]);
    }
}
