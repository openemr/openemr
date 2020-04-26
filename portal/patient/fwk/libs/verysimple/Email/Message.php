<?php

/** @package    verysimple::Email */

/**
 * import supporting libraries
 */
require_once("EmailMessage");

/**
 * Generic interface for sending Email.
 * This extends EmailMessage due to
 * namespace collision issues using the classname Message.
 * @depreated please use EmailMessage instead
 *
 * @package verysimple::Email
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 2.1
 */
class Message extends EmailMessage
{
}
