<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Transport;

use Zend\Stdlib\AbstractOptions;

class Envelope extends AbstractOptions
{
    /**
     * @var string|null
     */
    protected $from;

    /**
     * @var string|null
     */
    protected $to;

    /**
     * Get MAIL FROM
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set MAIL FROM
     *
     * @param  string $from
     */
    public function setFrom($from)
    {
        $this->from = (string) $from;
    }

    /**
     * Get RCPT TO
     *
     * @return string|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set RCPT TO
     *
     * @param  string $to
     */
    public function setTo($to)
    {
        $this->to = $to;
    }
}
