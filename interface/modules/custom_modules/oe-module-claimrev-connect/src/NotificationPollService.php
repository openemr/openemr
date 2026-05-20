<?php

/**
 * Polls ClaimRev for portal notifications and creates pnotes in OpenEMR
 * so users see them in their Messages inbox. Tracks delivery via
 * mod_claimrev_notifications to prevent duplicates and marks each
 * notification as read on ClaimRev after delivery.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;

class NotificationPollService
{
    public static function run(): void
    {
        require_once OEGlobalsBag::getInstance()->getString('fileroot') . "/library/pnotes.inc.php";

        $enabledRaw = OEGlobalsBag::getInstance()->get(GlobalConfig::CONFIG_ENABLE_NOTIFICATIONS) ?? '1';
        if (in_array($enabledRaw, [false, '', '0', 0], true)) {
            return;
        }

        try {
            $api = ClaimRevApi::makeFromGlobals();
        } catch (ClaimRevException) {
            return;
        }

        try {
            $notifications = $api->getPortalNotifications(false);
        } catch (ClaimRevException) {
            return;
        }

        $recipientSetting = OEGlobalsBag::getInstance()->getString(GlobalConfig::CONFIG_NOTIFICATION_RECIPIENT, 'admin');
        if ($recipientSetting === '') {
            $recipientSetting = 'admin';
        }
        $recipients = array_values(array_filter(array_map(trim(...), explode(';', $recipientSetting))));
        if ($recipients === []) {
            $recipients = ['admin'];
        }

        foreach ($notifications as $notification) {
            $portalId = TypeCoerce::asNullableInt($notification['portalNotificationId'] ?? null);
            if ($portalId === null) {
                continue;
            }

            $existing = QueryUtils::querySingleRow(
                "SELECT id FROM mod_claimrev_notifications WHERE portal_notification_id = ?",
                [$portalId]
            );
            if (is_array($existing) && $existing !== []) {
                continue;
            }

            $title = self::htmlToPlainText(TypeCoerce::asString($notification['messageTitle'] ?? 'ClaimRev Notification'));

            $bodyTextRaw = TypeCoerce::asString($notification['messageBodyText'] ?? '');
            if ($bodyTextRaw === '') {
                $bodyTextRaw = TypeCoerce::asString($notification['messageBody'] ?? '');
            }
            $body = self::htmlToPlainText($bodyTextRaw);

            $messageText = "ClaimRev: " . $title . "\n\n" . $body;

            $firstPnoteId = 0;
            foreach ($recipients as $recipient) {
                $pnoteId = addPnote(
                    0,
                    $messageText,
                    0,
                    1,
                    "ClaimRev",
                    $recipient,
                    "",
                    "New",
                    "claimrev-notifications"
                );
                if ($firstPnoteId === 0) {
                    $firstPnoteId = (int) $pnoteId;
                }
            }

            QueryUtils::sqlInsert(
                "INSERT INTO mod_claimrev_notifications (portal_notification_id, message_title, message_body, pnote_id, created_date, processed_date) VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    $portalId,
                    $title,
                    $body,
                    $firstPnoteId,
                    TypeCoerce::asString($notification['createdDate'] ?? date('Y-m-d H:i:s')),
                ]
            );

            try {
                $api->setNotificationReadStatus($portalId, true);
            } catch (ClaimRevException) {
                // Non-fatal — notification was already delivered.
            }
        }
    }

    /**
     * Convert HTML to readable plain text, preserving paragraph breaks,
     * list structure, and table rows.
     */
    public static function htmlToPlainText(string $html): string
    {
        $text = (string) preg_replace('/<head\b[^>]*>.*?<\/head>/is', '', $html);
        $text = (string) preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $text);
        $text = (string) preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $text);

        $text = (string) preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = (string) preg_replace('/<\/(?:p|div|tr|h[1-6])>/i', "\n\n", $text);

        $text = (string) preg_replace('/<li\b[^>]*>/i', "\n- ", $text);

        $text = (string) preg_replace('/<\/td>\s*<td/i', "</td>  <td", $text);

        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace('/[^\S\n]+/', ' ', $text);
        $text = (string) preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
