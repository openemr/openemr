<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail\Message;

/**
 * InMemory transport
 *
 * This transport will just store the message in memory.  It is helpful
 * when unit testing, or to prevent sending email when in development or
 * testing.
 */
class InMemory implements TransportInterface
{
    /**
     * @var Message
     */
    protected $lastMessage;

    /**
     * Takes the last message and saves it for testing.
     *
     * @param Message $message
     */
    public function send(Message $message)
    {
        $this->lastMessage = $message;
    }

    /**
     * Get the last message sent.
     *
     * @return Message
     */
    public function getLastMessage()
    {
        return $this->lastMessage;
    }
}
