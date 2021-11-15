<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\ResponseSender;

use Laminas\Console\Response;
use Laminas\Mvc\ResponseSender\ResponseSenderInterface;
use Laminas\Mvc\ResponseSender\SendResponseEvent;

class ConsoleResponseSender implements ResponseSenderInterface
{
    /**
     * Send content
     *
     * @param  SendResponseEvent $event
     * @return ConsoleResponseSender
     */
    public function sendContent(SendResponseEvent $event)
    {
        if ($event->contentSent()) {
            return $this;
        }
        $response = $event->getResponse();
        echo $response->getContent();
        $event->setContentSent();
        return $this;
    }

    /**
     * Send the response
     *
     * @param  SendResponseEvent $event
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (! $response instanceof Response) {
            return;
        }

        $this->sendContent($event);
        $errorLevel = (int) $response->getMetadata('errorLevel', 0);
        $event->stopPropagation(true);
        exit($errorLevel);
    }
}
