<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Model;

class ConsoleModel extends ViewModel
{
    const RESULT = 'result';

    /**
     * Console output doesn't support containers.
     *
     * @var string
     */
    protected $captureTo = null;

    /**
     * Console output should always be terminal.
     *
     * @var bool
     */
    protected $terminate = true;

    /**
     * Set error level to return after the application ends.
     *
     * @param int $errorLevel
     * @return $this
     */
    public function setErrorLevel($errorLevel)
    {
        $this->options['errorLevel'] = $errorLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getErrorLevel()
    {
        if (array_key_exists('errorLevel', $this->options)) {
            return $this->options['errorLevel'];
        }
    }

    /**
     * Set result text.
     *
     * @param string  $text
     * @return \Laminas\View\Model\ConsoleModel
     */
    public function setResult($text)
    {
        $this->setVariable(self::RESULT, $text);
        return $this;
    }

    /**
     * Get result text.
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this->getVariable(self::RESULT);
    }
}
