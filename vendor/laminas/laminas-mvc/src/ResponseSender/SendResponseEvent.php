<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\ResponseSender;

use Laminas\EventManager\Event;
use Laminas\Stdlib\ResponseInterface;

use function spl_object_hash;

class SendResponseEvent extends Event
{
    /**#@+
     * Send response events triggered by eventmanager
     */
    const EVENT_SEND_RESPONSE = 'sendResponse';
    /**#@-*/

    /**
     * @var string Event name
     */
    protected $name = 'sendResponse';

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $headersSent = [];

    /**
     * @var array
     */
    protected $contentSent = [];

    /**
     * @param ResponseInterface $response
     * @return SendResponseEvent
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->setParam('response', $response);
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set content sent for current response
     *
     * @return SendResponseEvent
     */
    public function setContentSent()
    {
        $response = $this->getResponse();
        $contentSent = $this->getParam('contentSent', []);
        $responseObjectHash = spl_object_hash($response);
        $contentSent[$responseObjectHash] = true;
        $this->setParam('contentSent', $contentSent);
        $this->contentSent[$responseObjectHash] = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function contentSent()
    {
        $response = $this->getResponse();
        if (isset($this->contentSent[spl_object_hash($response)])) {
            return true;
        }
        return false;
    }

    /**
     * Set headers sent for current response object
     *
     * @return SendResponseEvent
     */
    public function setHeadersSent()
    {
        $response = $this->getResponse();
        $headersSent = $this->getParam('headersSent', []);
        $responseObjectHash = spl_object_hash($response);
        $headersSent[$responseObjectHash] = true;
        $this->setParam('headersSent', $headersSent);
        $this->headersSent[$responseObjectHash] = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function headersSent()
    {
        $response = $this->getResponse();
        if (isset($this->headersSent[spl_object_hash($response)])) {
            return true;
        }
        return false;
    }
}
