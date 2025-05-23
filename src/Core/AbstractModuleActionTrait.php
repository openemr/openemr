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
        $id = $_SESSION['authUserID'] ?? 1;
        $query = "SELECT fname, lname, fax, facility, username FROM users WHERE id = ?";
        $result = sqlQuery($query, array($id));

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
     * @param $param
     * @param $default
     * @return mixed|null
     */
    public function getSession($param = null, $default = null): mixed
    {
        if ($param) {
            return $_SESSION[$param] ?? $default;
        }

        return $this->_session;
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
     * @param $key
     * @param $value
     * @return $this
     */
    public function setSession($key, $value): static
    {
        // ensure write is allowed by using utility.
        SessionUtil::setSession($key, $value);
        return $this;
    }
}
