<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use Laminas\View\Model\ViewModel as BaseViewModel;

class ViewModel extends BaseViewModel
{
    const RESULT = 'result';

    /**
     * Console output does not support containers.
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
     */
    public function setErrorLevel($errorLevel)
    {
        $this->options['errorLevel'] = $errorLevel;
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
     * @return self
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
