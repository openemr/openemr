<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\ResponseSender;

use Laminas\Http\Response;

class HttpResponseSender extends AbstractResponseSender
{
    /**
     * Send content
     *
     * @param  SendResponseEvent $event
     * @return HttpResponseSender
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
     * Send HTTP response
     *
     * @param  SendResponseEvent $event
     * @return HttpResponseSender
     */
    public function __invoke(SendResponseEvent $event)
    {
        $response = $event->getResponse();
        if (! $response instanceof Response) {
            return $this;
        }

        $this->sendHeaders($event)
             ->sendContent($event);
        $event->stopPropagation(true);
        return $this;
    }
}
