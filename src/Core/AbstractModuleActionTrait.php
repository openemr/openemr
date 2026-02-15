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
     * @param string|null $param
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getSession(string $param = null, mixed $default = null): mixed
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ($param) {
            return $session->get($param, $default);
        }

        return $session->all(); // TODO @zmilan: do we wish to return array or Session instance?
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
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // ensure writing is allowed by using utility.
        $session->set($key, $value);
        return $this;
    }
}
