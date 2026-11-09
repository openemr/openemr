<?php

/**
 * What a Direct send came to: the message id the server queued it under, or why it wasn't sent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailSendResult
{
    /**
     * @param ?string $messageId the id the server queued the message under, or null when it wasn't sent
     * @param string $message PhiMailSender::SUCCESS when sent, else a message for the user saying why not
     */
    private function __construct(public ?string $messageId, public string $message)
    {
    }

    public static function sent(string $messageId): self
    {
        return new self($messageId, PhiMailSender::SUCCESS);
    }

    public static function notSent(string $message): self
    {
        return new self(null, $message);
    }

    public function wasSent(): bool
    {
        return $this->messageId !== null;
    }
}
