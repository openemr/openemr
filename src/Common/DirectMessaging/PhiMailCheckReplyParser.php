<?php

/**
 * Parses the server's reply to the phiMail CHECK command.
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final class PhiMailCheckReplyParser
{
    private const NONE = "NONE\n";

    /**
     * @param string $line the reply line, including its line feed
     * @throws PhiMailUnexpectedResponseException for a FAIL reply, or any reply that is not a well-formed NONE, STATUS, or MAIL
     */
    public static function parse(string $line): PhiMailNoneReply|PhiMailStatusReply|PhiMailMailReply
    {
        if ($line === self::NONE) {
            return new PhiMailNoneReply();
        }

        $fields = explode(' ', trim($line));
        return match ($fields[0]) {
            'STATUS' => self::status($line),
            'MAIL' => self::mail($line),
            default => throw new PhiMailUnexpectedResponseException($line),
        };
    }

    /**
     * STATUS message-id status-code [additional-information]
     *
     * @throws PhiMailUnexpectedResponseException
     */
    private static function status(string $line): PhiMailStatusReply
    {
        $fields = explode(' ', trim($line), 4);
        if (count($fields) < 3 || in_array('', $fields, true)) {
            throw new PhiMailUnexpectedResponseException($line);
        }
        return new PhiMailStatusReply(
            messageId: $fields[1],
            status: PhiMailDeliveryStatus::fromStatusCode($fields[2]),
            info: $fields[3] ?? '',
            line: $line,
        );
    }

    /**
     * MAIL recipient sender attachment-count message-id
     *
     * @throws PhiMailUnexpectedResponseException
     */
    private static function mail(string $line): PhiMailMailReply
    {
        $fields = explode(' ', trim($line));
        if (count($fields) !== 5 || in_array('', $fields, true) || !ctype_digit($fields[3])) {
            throw new PhiMailUnexpectedResponseException($line);
        }
        return new PhiMailMailReply(
            recipient: $fields[1],
            sender: $fields[2],
            attachmentCount: (int) $fields[3],
            messageId: $fields[4],
            line: $line,
        );
    }
}
