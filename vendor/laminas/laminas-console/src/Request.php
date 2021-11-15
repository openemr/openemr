<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console;

use Laminas\Stdlib\Message;
use Laminas\Stdlib\Parameters;
use Laminas\Stdlib\RequestInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @var \Laminas\Stdlib\Parameters
     */
    protected $params = null;

    /**
     * @var \Laminas\Stdlib\Parameters
     */
    protected $envParams = null;

    /**
     * @var string
     */
    protected $scriptName = null;

    /**
     * Create a new CLI request
     *
     * @param array|null $args Console arguments. If not supplied, $_SERVER['argv'] will be used
     * @param array|null $env Environment data. If not supplied, $_ENV will be used
     * @throws Exception\RuntimeException
     */
    public function __construct(array $args = null, array $env = null)
    {
        if ($args === null) {
            if (! isset($_SERVER['argv'])) {
                $errorDescription = (ini_get('register_argc_argv') == false)
                    ? "Cannot create Console\\Request because PHP ini option 'register_argc_argv' is set Off"
                    : 'Cannot create Console\\Request because $_SERVER["argv"] is not set for unknown reason.';
                throw new Exception\RuntimeException($errorDescription);
            }
            $args = $_SERVER['argv'];
        }

        if ($env === null) {
            $env = $_ENV;
        }

        /**
         * Extract first param assuming it is the script name
         */
        if (count($args) > 0) {
            $this->setScriptName(array_shift($args));
        }

        /**
         * Store runtime params
         */
        $this->params()->fromArray($args);
        $this->setContent($args);

        /**
         * Store environment data
         */
        $this->env()->fromArray($env);
    }

    /**
     * Exchange parameters object
     *
     * @param \Laminas\Stdlib\Parameters $params
     * @return Request
     */
    public function setParams(Parameters $params)
    {
        $this->params = $params;
        $this->setContent($params);
        return $this;
    }

    /**
     * Return the container responsible for parameters
     *
     * @return \Laminas\Stdlib\Parameters
     */
    public function getParams()
    {
        if ($this->params === null) {
            $this->params = new Parameters();
        }

        return $this->params;
    }

    /**
     * Return a single parameter.
     * Shortcut for $request->params()->get()
     *
     * @param string    $name       Parameter name
     * @param string    $default    (optional) default value in case the parameter does not exist
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return $this->params()->get($name, $default);
    }

    /**
     * Return the container responsible for parameters
     *
     * @return \Laminas\Stdlib\Parameters
     */
    public function params()
    {
        return $this->getParams();
    }

    /**
     * Provide an alternate Parameter Container implementation for env parameters in this object, (this is NOT the
     * primary API for value setting, for that see env())
     *
     * @param \Laminas\Stdlib\Parameters $env
     * @return \Laminas\Console\Request
     */
    public function setEnv(Parameters $env)
    {
        $this->envParams = $env;
        return $this;
    }

    /**
     * Return a single parameter container responsible for env parameters
     *
     * @param string    $name       Parameter name
     * @param string    $default    (optional) default value in case the parameter does not exist
     * @return \Laminas\Stdlib\Parameters
     */
    public function getEnv($name, $default = null)
    {
        return $this->env()->get($name, $default);
    }

    /**
     * Return the parameter container responsible for env parameters
     *
     * @return \Laminas\Stdlib\Parameters
     */
    public function env()
    {
        if ($this->envParams === null) {
            $this->envParams = new Parameters();
        }

        return $this->envParams;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return trim(implode(' ', $this->params()->toArray()));
    }

    /**
     * Allow PHP casting of this object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param string $scriptName
     */
    public function setScriptName($scriptName)
    {
        $this->scriptName = $scriptName;
    }

    /**
     * @return string
     */
    public function getScriptName()
    {
        return $this->scriptName;
    }
}
