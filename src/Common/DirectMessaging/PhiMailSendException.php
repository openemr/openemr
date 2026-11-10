<?php

/**
 * Thrown inside PhiMailSender when the server refuses a step of a send.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

/**
 * @internal PhiMailSender::send() catches it and returns $userMessage
 */
final class PhiMailSendException extends PhiMailException
{
    /**
     * @param string $userMessage the translated failure to show the user
     */
    public function __construct(public readonly string $userMessage)
    {
        parent::__construct('phiMail server refused the send');
    }
}
