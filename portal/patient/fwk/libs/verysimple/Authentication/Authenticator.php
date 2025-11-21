<?php

/** @package    verysimple::Authentication */

/**
 * import supporting libraries
 */
use OpenEMR\Common\Session\SessionWrapperFactory;
require_once("IAuthenticatable.php");
require_once("AuthenticationException.php");

/**
 * Authenticator is a collection of static methods for storing a current user
 * in the session and determining if the user has necessary permissions to
 * perform an action
 *
 * @package verysimple::Authentication
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 1.0
 */
class Authenticator
{
    static $user = null;
    static $is_initialized = false;
    public static function Init()
    {
        if (! self::$is_initialized) {
            self::$is_initialized = true;

            if (session_id() == '') {
                session_start();
                error_log("DEBUG: This session_start, which is in Authenticator.php, should never be called.");
            }
        }
    }

    /**
     * Returns the currently authenticated user or null
     *
     * @access public
     * @return IAuthenticatable || null
     */
    public static function GetCurrentUser($guid = "CURRENT_USER")
    {
        if (self::$user == null) {
            self::Init();
            $session = SessionWrapperFactory::instance()->getWrapper();
            $sessionGuid = $session->get($guid);
            if (!empty($sessionGuid)) {
                self::$user = unserialize($sessionGuid);
            }
        }

        return self::$user;
    }

    /**
     * Set the given IAuthenticable object as the currently authenticated user.
     * UnsetAllSessionVars will be called before setting the current user
     *
     * @param IAuthenticatable $user
     * @param mixed $guid
     *          a unique id for this session
     *
     */
    public static function SetCurrentUser(IAuthenticatable $user, $guid = "CURRENT_USER")
    {
        self::UnsetAllSessionVars(); // this calls Init so we don't have to here
        self::$user = $user;
        $session = SessionWrapperFactory::instance()->getWrapper();
        $session->set($guid, serialize($user));
    }

    /**
     * Unsets all session variables without destroying the session
     */
    public static function UnsetAllSessionVars()
    {
        self::Init();
        $session = SessionWrapperFactory::instance()->getWrapper();
        $session->clear();
    }

    /**
     * Forcibly clear all _SESSION variables and destroys the session
     *
     * @param string $guid
     *          The GUID of this user
     */
    public static function ClearAuthentication($guid = "CURRENT_USER")
    {
        self::Init();
        self::$user = null;
        $session = SessionWrapperFactory::instance()->getWrapper();
        $session->remove($guid);

        self::UnsetAllSessionVars();

        session_destroy();
        error_log("DEBUG: This session_destroy, which is in Authenticator.php, should never be called.");
    }
}
