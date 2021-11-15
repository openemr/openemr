<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\ResponseSender;

use Laminas\Http\Header\MultipleHeaderInterface;

abstract class AbstractResponseSender implements ResponseSenderInterface
{
    /**
     * Send HTTP headers
     *
     * @param  SendResponseEvent $event
     * @return self
     */
    public function sendHeaders(SendResponseEvent $event)
    {
        if (headers_sent() || $event->headersSent()) {
            return $this;
        }

        $response = $event->getResponse();

        foreach ($response->getHeaders() as $header) {
            if ($header instanceof MultipleHeaderInterface) {
                header($header->toString(), false);
                continue;
            }
            header($header->toString());
        }

        $status = $response->renderStatusLine();
        header($status);

        $event->setHeadersSent();
        return $this;
    }
}
