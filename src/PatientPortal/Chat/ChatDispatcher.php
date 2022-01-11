<?php

/**
 *  Chat Class ChatDispatcher
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PatientPortal\Chat;

use RuntimeException;

abstract class ChatDispatcher
{
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    protected $_currentAction, $_defaultModel;

    public const ACTION_POSTFIX = 'Action';
    public const ACTION_DEFAULT = 'indexAction';

    public function __construct()
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session = &$_SESSION;
        $this->init();
    }

    public function init()
    {
        $this->dispatchActions();
        $this->render();
    }

    public function dispatchActions()
    {
        $action = $this->getQuery('action');
        if ($action && $action .= self::ACTION_POSTFIX) {
            if (method_exists($this, $action)) {
                $this->setResponse(
                    call_user_func(array($this, $action), array())
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
            }
        } else {
            $this->setResponse(
                $this->{self::ACTION_DEFAULT}(array())
            );
        }

        return $this->_response;
    }

    public function render()
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new RuntimeException('Response content must be scalar');
            }
            exit;
        }
    }

    public function indexAction()
    {
        return null;
    }

    public function setResponse($content)
    {
        $this->_response = $content;
    }

    public function setHeader($params)
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

    public function setModel($chatmodel)
    {
        $this->_defaultModel = $chatmodel;
        return $this;
    }

    public function setSession($key, $value)
    {
        $_SESSION[$key] = $value;
        return $this;
    }

    public function setCookie($key, $value, $seconds = 3600)
    {
        $this->_cookies[$key] = $value;
        if (!headers_sent()) {
            setcookie($key, $value, time() + $seconds);
            return $this;
        }
    }

    public function getRequest($param = null, $default = null)
    {
        if ($param) {
            return $this->_request[$param] ?? $default;
        }

        return $this->_request;
    }

    public function getQuery($param = null, $default = null)
    {
        if ($param) {
            return $this->_query[$param] ?? $default;
        }

        return $this->_query;
    }

    public function getPost($param = null, $default = null)
    {
        if ($param) {
            return $this->_post[$param] ?? $default;
        }

        return $this->_post;
    }

    public function getServer($param = null, $default = null)
    {
        if ($param) {
            return $this->_server[$param] ?? $default;
        }

        return $this->_server;
    }

    public function getSession($param = null, $default = null)
    {
        if ($param) {
            return $this->_session[$param] ?? $default;
        }

        return $this->_session;
    }

    public function getCookie($param = null, $default = null)
    {
        if ($param) {
            return $this->_cookies[$param] ?? $default;
        }

        return $this->_cookies;
    }

    public function getUser()
    {
        return $this->_session['ptName'] ?: $this->_session['authUser'];
    }

    public function getIsPortal()
    {
        return IS_PORTAL;
    }

    public function getIsFullScreen()
    {
        return IS_FULLSCREEN;
    }

    public function getModel()
    {
        if ($this->_defaultModel && class_exists($this->_defaultModel)) {
            return new $this->_defaultModel();
        }
    }

    public function sanitize($string, $quotes = ENT_QUOTES, $charset = 'utf-8')
    {
        return htmlentities($string, $quotes, $charset);
    }
}
