<?php

/**
 * Fax SMS Module Member
 *
 * Declarative UI profile for messageUI.php.
 *
 * Historically messageUI.php decided which tabs and table columns to show for
 * each service three different ways at once: PHP branches on magic service
 * numbers ('1','3','6'), CSS hide/show classes baked into the markup
 * (.rc-hide, .twilio, .etherfax, .signalwire, .*-hide ...), and a JS map that
 * toggled those classes on load. The same tabs were physically duplicated 2-4
 * times with slightly different columns.
 *
 * This class makes the per-vendor differences what they actually are - data.
 * Given the active {@see ServiceType} and channel ('fax'|'sms'|'email') it
 * returns the ordered set of visible tabs and, per tab, the table column
 * headers. messageUI.php then renders ONE generic nav + tab-content loop from
 * it. No magic numbers, no hide-classes, no JS visibility map.
 *
 * Note on email: email is the EmailClient's own channel only. Fax/SMS clients
 * expose "email" solely as a forward/backup action, never as a rendered tab, so
 * no fax/sms profile here lists an email tab.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS\View;

use OpenEMR\Modules\FaxSMS\Enums\ServiceType;

final class MessageUiProfile
{
    /**
     * Ordered tab descriptors for the active service + channel.
     *
     * Each descriptor:
     *   id       - tab pane id AND nav anchor (e.g. 'received', 'alertlogs', 'upLoad')
     *   navLabel - already-translated nav text
     *   type     - 'table' (default) or 'dropzone'
     *   tableId  - table element id (table type only)
     *   columns  - list of headers; each entry is either an already-translated
     *              string (rendered as <th>label</th>) or ['raw' => '<th>...</th>']
     *              for special header cells (trash icon, extracted-eye toggle)
     *   refresh  - JS call string for the nav refresh icon, or null
     *
     * @return array<string, array<string, mixed>>
     */
    public static function tabs(ServiceType $service, string $channel): array
    {
        return match (true) {
            $service === ServiceType::RINGCENTRAL && $channel === 'fax' => self::ringCentralFax(),
            $service === ServiceType::RINGCENTRAL                       => self::ringCentralSms(),
            $service === ServiceType::ETHERFAX                          => self::etherFax(),
            $service === ServiceType::SIGNALWIRE                        => self::signalWire(),
            $service === ServiceType::TWILIO_SMS                        => self::twilioSms(),
            $service === ServiceType::CLICKATELL_SMS                    => self::clickatellSms(),
            $service === ServiceType::EMAIL                             => self::email(),
            // Safe generic fallback (mirrors the old "all others" SMS layout).
            default                                                     => self::twilioSms(),
        };
    }

    /** @return array<string, array<string, mixed>> */
    private static function ringCentralFax(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Start Time'), self::t('End Time'), self::t('Pages'), self::t('From'), self::t('To'), self::t('Status'), self::t('Actions'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Start Time'), self::t('End Time'), self::t('Pages'), self::t('From'), self::t('To'), self::t('Status'), self::t('Actions'),
                self::trash('sent'),
            ]),
            'logs' => self::callLog(),
            'upload' => self::dropzone(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function ringCentralSms(): array
    {
        $cols = [self::t('Date'), self::t('Status'), self::t('From'), self::t('To'), self::t('Result'), self::t('Message'), self::t('Actions')];

        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), $cols, "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), $cols),
            'messages' => self::table('messages', 'msgdetails', self::t('SMS Log'), $cols),
            'logs' => self::callLog(),
            'alerts' => self::alerts(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function etherFax(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Time'), self::t('Caller #'), self::t('Caller Id'), self::t('To'), self::t('Pages'), self::t('Length'), self::t('Status'),
                self::extractedEye(), self::t('MRN Match'), self::t('Actions'), self::trash('received'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Time'), self::t('Caller #'), self::t('Caller Id'), self::t('To'), self::t('Pages'), self::t('Name'), self::t('Status'), self::t('Actions'),
                self::trash('sent'),
            ]),
            'logs' => self::callLog(),
            'upload' => self::dropzone(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function signalWire(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Time'), self::t('Caller #'), self::t('To'), self::t('Pages'), self::t('Status'), self::t('Actions'), self::trash('received'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Time'), self::t('Caller #'), self::t('To'), self::t('Pages'), self::t('Status'), self::t('Actions'), self::trash('sent'),
            ]),
            'upload' => self::dropzone(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function twilioSms(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Time'), self::t('Type'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'), self::t('Reply'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Start Time'), self::t('Price'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'), self::t('Reply'),
            ]),
            'messages' => self::smsLog(),
            'logs' => self::callLog(),
            'alerts' => self::alerts(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function clickatellSms(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Time'), self::t('Type'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'), self::t('Reply'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Start Time'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'),
            ]),
            'messages' => self::smsLog(),
            'logs' => self::callLog(),
            'alerts' => self::alerts(),
        ];
    }

    /** @return array<string, array<string, mixed>> */
    private static function email(): array
    {
        return [
            'received' => self::table('received', 'rcvdetails', self::t('Received'), [
                self::t('Time'), self::t('Type'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'), self::t('Reply'),
            ], "retrieveMsgs('', this)"),
            'sent' => self::table('sent', 'sent-details', self::t('Sent'), [
                self::t('Start Time'), self::t('Message'), self::t('From'), self::t('To'), self::t('Result'),
            ]),
            'alerts' => self::alerts(),
        ];
    }

    // ---- shared tab builders -------------------------------------------------

    /** @return array<string, mixed> */
    private static function smsLog(): array
    {
        return self::table('messages', 'msgdetails', self::t('SMS Log'), [
            self::t('Date'), self::t('Type'), self::t('Send By'), self::t('To'), self::t('Result'), self::t('View'),
        ]);
    }

    /** @return array<string, mixed> */
    private static function callLog(): array
    {
        return self::table('logs', 'logdetails', self::t('Call Log'), [
            self::t('Date'), self::t('Type'), self::t('From'), self::t('To'), self::t('Action'), self::t('Result'), self::t('Id'),
        ]);
    }

    /** @return array<string, mixed> */
    private static function alerts(): array
    {
        return self::table('alertlogs', 'alertdetails', self::t('Reminder Notifications Log'), [
            self::t('Id'), self::t('Date Sent'), self::t('Appt Date Time'), self::t('Patient'), self::t('Message'),
        ], "getNotificationLog(event, this)");
    }

    /** @return array<string, mixed> */
    private static function dropzone(): array
    {
        return [
            'id' => 'upLoad',
            'navLabel' => self::t('Fax Drop Box'),
            'type' => 'dropzone',
            'refresh' => null,
        ];
    }

    // ---- primitives ----------------------------------------------------------

    /**
     * @param list<string|array{raw: string}> $columns
     *
     * @return array<string, mixed>
     */
    private static function table(string $id, string $tableId, string $navLabel, array $columns, ?string $refresh = null): array
    {
        return [
            'id' => $id,
            'tableId' => $tableId,
            'navLabel' => $navLabel,
            'type' => 'table',
            'columns' => $columns,
            'refresh' => $refresh,
        ];
    }

    /**
     * Trailing delete-selected trash header cell.
     *
     * @return array{raw: string}
     */
    private static function trash(string $which): array
    {
        $title = self::a('Delete selected fax documents');

        return ['raw' => '<th><i role="button" id="delete-selected-' . $which
            . '" title="' . $title . '" class="delete-selected-items text-danger fa fa-trash"></i></th>'];
    }

    /**
     * "Extracted" header with the collapse-toggle eye button.
     *
     * @return array{raw: string}
     */
    private static function extractedEye(): array
    {
        return ['raw' => "<th><a role='button' href='#' class='btn btn-link fa fa-eye' "
            . "onclick=\"toggleDetail('collapse')\"></a>" . self::t('Extracted') . "</th>"];
    }
    /** Text-escaped translated label. */
    private static function t(string $key): string
    {
        return xlt($key);
    }

    /** Attribute-escaped translated label. */
    private static function a(string $key): string
    {
        return xla($key);
    }
}
