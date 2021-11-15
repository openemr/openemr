<?php

namespace Facebook\WebDriver\Remote;

class WebDriverCommand
{
    /** @var string */
    protected $sessionID;
    /** @var string */
    protected $name;
    /** @var array */
    protected $parameters;

    /**
     * @param string $session_id
     * @param string $name Constant from DriverCommand
     * @param array $parameters
     * @todo In 2.0 force parameters to be an array, then remove is_array() checks in HttpCommandExecutor
     */
    public function __construct($session_id, $name, $parameters)
    {
        $this->sessionID = $session_id;
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSessionID()
    {
        return $this->sessionID;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}
