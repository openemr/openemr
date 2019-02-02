<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Mail;

/**
 * Interface for mail transports
 */
interface TransportInterface
{
    /**
     * Send a mail message
     *
     * @param \Zend\Mail\Message $message
     * @return
     */
    public function send(Mail\Message $message);
}
