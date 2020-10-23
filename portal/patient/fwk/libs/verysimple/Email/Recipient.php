<?php

/** @package    verysimple::Email */

/**
 * Object representation of an Email recipient
 *
 * @package verysimple::Email
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license LGPL
 * @version 1.1
 */
class Recipient
{
    var $Email;
    var $RealName;
    function __construct($email, $name = "")
    {
        if ($name) {
            $this->Email = $email;
            $this->RealName = $name != "" ? $name : $email;
        } else {
            $this->Parse($email);
        }
    }

    /**
     * Returns true if the provided email address appears to be valid.
     * This does not determine if the address is actually legit, only
     * if it is formatted properly
     *
     * @param
     *          string email address to validate
     * @return bool 1 (true) if $email is valid, 0 (false) if not
     */
    static function IsEmailInValidFormat($email)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,25})$/i", $email);
    }

    /**
     * Parses an address in the either format:
     * "Real Name <email@address.com>" or "email@address.com"
     *
     * @param string $val
     *          to parse
     */
    function Parse($val)
    {
        $pair = explode("<", $val);

        if (isset($pair [1])) {
            $this->RealName = trim($pair [0]);
            $this->Email = trim(str_replace(">", "", $pair [1]));
        } else {
            $this->Email = $val;
            $this->RealName = $val;
        }

        // just in case there was no realname
        if ($this->RealName == "") {
            $this->RealName = $this->Email;
        }
    }

    /**
     * Returns true if $this->Email appears to be a valid email
     *
     * @return bool
     */
    function IsValidEmail()
    {
        return Recipient::IsEmailInValidFormat($this->Email);
    }
}
