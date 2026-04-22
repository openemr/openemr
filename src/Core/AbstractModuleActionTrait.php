<?php

/**
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;

/**
 * Some useful functions.
 */
trait AbstractModuleActionTrait
{
    /**
     * @return array
     */
    public static function getLoggedInUser(): array
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $id = $session->get('authUserID', 1);
        $query = "SELECT fname, lname, fax, facility, username FROM users WHERE id = ?";
        $result = sqlQuery($query, [$id]);

        return $result;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getPost($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_post[$param] ?? $default;
        }

        return $this->_post;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getQuery($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_query[$param] ?? $default;
        }

        return $this->_query;
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getRequest($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_request[$param] ?? $default;
        }

        return $this->_request;
    }

    /**
     * Returns a session value by key. The $param is required to prevent callers
     * from obtaining the raw SessionInterface object and writing to it directly,
     * which would be silently lost with read_and_close sessions. Use
     * SessionUtil::setSession() / SessionUtil::unsetSession() for writes.
     *
     * @param string $param
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getSession(string $param, mixed $default = null): mixed
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        return $session->get($param, $default);
    }

    /**
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getServer($param = null, $default = null): mixed
    {
        if ($param) {
            return $this->_server[$param] ?? $default;
        }

        return $this->_server;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setHeader($params): static
    {
        if (!headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach ($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function setSession(string $key, $value): static
    {
        SessionUtil::setSession($key, $value);
        return $this;
    }
}
