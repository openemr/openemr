<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console;

use Laminas\Stdlib\Message;
use Laminas\Stdlib\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    /**
     * @var bool
     */
    protected $contentSent = false;

    /**
     * Check if content was sent
     *
     * @return bool
     * @deprecated
     */
    public function contentSent()
    {
        return $this->contentSent;
    }

    /**
     * Set the error level that will be returned to shell.
     *
     * @param int   $errorLevel
     * @return Response
     */
    public function setErrorLevel($errorLevel)
    {
        if (is_string($errorLevel) && ! ctype_digit($errorLevel)) {
            return $this;
        }

        $this->setMetadata('errorLevel', $errorLevel);
        return $this;
    }

    /**
     * Get response error level that will be returned to shell.
     *
     * @return int|0
     */
    public function getErrorLevel()
    {
        return $this->getMetadata('errorLevel', 0);
    }

    /**
     * Send content
     *
     * @return Response
     * @deprecated
     */
    public function sendContent()
    {
        if ($this->contentSent()) {
            return $this;
        }
        echo $this->getContent();
        $this->contentSent = true;
        return $this;
    }

    /**
     * @deprecated
     */
    public function send()
    {
        $this->sendContent();
        $errorLevel = (int) $this->getMetadata('errorLevel', 0);
        exit($errorLevel);
    }
}
