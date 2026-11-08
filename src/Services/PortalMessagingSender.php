<?php

/**
 * Portal Messaging Sender Resolver
 *
 * Resolves the authoritative sender identity for a patient-portal message
 * submission. Staff/dashboard sessions must use server-derived identity
 * (preventing impersonation via client-supplied sender_id / sender_name);
 * patient-portal sessions use POST values (validated upstream in handle_note.php).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final class PortalMessagingSender
{
    /**
     * Resolve sender identity. If staff identity is available (i.e. the request
     * came from an authenticated staff session) it takes priority over any
     * client-supplied sender_id / sender_name. Empty strings are returned in
     * place of nulls so the result can flow into addPnote() / sendMail() which
     * declare string parameters.
     *
     * @return array{0: string, 1: string} tuple of [sender_id, sender_name]
     */
    public function resolve(
        ?string $staffSenderId,
        ?string $staffSenderName,
        ?string $postedSenderId,
        ?string $postedSenderName,
    ): array {
        if ($staffSenderId !== null && $staffSenderName !== null) {
            return [$staffSenderId, $staffSenderName];
        }
        return [$postedSenderId ?? '', $postedSenderName ?? ''];
    }
}
