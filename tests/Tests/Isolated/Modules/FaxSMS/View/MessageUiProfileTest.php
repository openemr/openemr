<?php

/**
 * Characterization tests for MessageUiProfile.
 *
 * MessageUiProfile is the single source of truth for which tabs and table
 * columns messageUI.php renders per service + channel. It replaced a tangle of
 * magic-number PHP branches, CSS hide-classes, and a JS show/hide map, so the
 * risk now is a silent transcription slip: someone edits a column list and the
 * dashboard quietly grows/loses a column or a whole tab.
 *
 * These tests pin the matrix exactly as shipped:
 *   - the ordered tab-key set per service+channel,
 *   - the column count per tab (the thing that distinguishes vendors, e.g.
 *     etherFAX received = 11 cells incl. the extracted-eye + trash raw cells,
 *     SignalWire received = 7),
 *   - the dropzone (Fax Drop Box) only appears on fax services,
 *   - the table element ids the extracted messageUI.js depends on
 *     (rcvdetails / sent-details / msgdetails / logdetails / alertdetails),
 *   - email exposes no fax/sms-only tabs (email is the EmailClient's channel;
 *     fax/sms clients treat email purely as a forward/backup action).
 *
 * Pure static method, no globals beyond the xlt()/xla() label helpers, which
 * are stubbed below so the isolated suite needs no OpenEMR gettext bootstrap.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\View {
    // Identity stubs for the label helpers MessageUiProfile calls. Declared IN
    // the class-under-test's namespace - matching the in-namespace stub style of
    // Notification/runner_test_stubs.php - so they shadow the unqualified
    // xlt()/xla() calls and cannot collide with the global namespace (or other
    // suites' stubs) when the isolated suite shares a process.
    if (!function_exists('OpenEMR\Modules\FaxSMS\View\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
    if (!function_exists('OpenEMR\Modules\FaxSMS\View\xla')) {
        function xla(string $s): string
        {
            return $s;
        }
    }
}

namespace {
    // Module classes are not covered by the root composer autoloader; register a
    // PSR-4 loader for the module namespace pointed at its src/ dir (mirrors the
    // module Bootstrap and the rest of the isolated suite).
    spl_autoload_register(static function (string $class): void {
        $prefix = 'OpenEMR\\Modules\\FaxSMS\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = __DIR__
            . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/'
            . $relative;
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\View {

    use OpenEMR\Modules\FaxSMS\Enums\ServiceType;
    use OpenEMR\Modules\FaxSMS\View\MessageUiProfile;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;

    final class MessageUiProfileTest extends TestCase
    {
        /**
         * @return array<string, array{ServiceType, string, list<string>}>
         */
        public static function tabKeyProvider(): array
        {
            return [
                'RingCentral fax' => [ServiceType::RINGCENTRAL, 'fax', ['received', 'sent', 'logs', 'upload']],
                'RingCentral sms' => [ServiceType::RINGCENTRAL, 'sms', ['received', 'sent', 'messages', 'logs', 'alerts']],
                'etherFAX'        => [ServiceType::ETHERFAX, 'fax', ['received', 'sent', 'logs', 'upload']],
                'SignalWire'      => [ServiceType::SIGNALWIRE, 'fax', ['received', 'sent', 'upload']],
                'Twilio sms'      => [ServiceType::TWILIO_SMS, 'sms', ['received', 'sent', 'messages', 'logs', 'alerts']],
                'Clickatell sms'  => [ServiceType::CLICKATELL_SMS, 'sms', ['received', 'sent', 'messages', 'logs', 'alerts']],
                'Email'           => [ServiceType::EMAIL, 'email', ['received', 'sent', 'alerts']],
            ];
        }

        /**
         * @param list<string> $expectedKeys
         */
        #[DataProvider('tabKeyProvider')]
        public function testTabKeySetAndOrder(ServiceType $service, string $channel, array $expectedKeys): void
        {
            $tabs = MessageUiProfile::tabs($service, $channel);

            // Order matters: the first tab becomes the active pane, and the JS
            // initial load assumes 'received' is present and first.
            self::assertSame($expectedKeys, array_keys($tabs));
            self::assertSame('received', array_key_first($tabs));
        }

        /**
         * The per-vendor column counts are exactly what the old branches +
         * hide-classes produced. These are the values most likely to drift.
         *
         * @return array<string, array{ServiceType, string, string, int}>
         */
        public static function columnCountProvider(): array
        {
            return [
                // etherFAX keeps Caller Id / Length / extracted-eye / MRN Match.
                'etherFAX received (11)'   => [ServiceType::ETHERFAX, 'fax', 'received', 11],
                'etherFAX sent (9)'        => [ServiceType::ETHERFAX, 'fax', 'sent', 9],
                // SignalWire drops those columns.
                'SignalWire received (7)'  => [ServiceType::SIGNALWIRE, 'fax', 'received', 7],
                'SignalWire sent (7)'      => [ServiceType::SIGNALWIRE, 'fax', 'sent', 7],
                'RC fax received (7)'      => [ServiceType::RINGCENTRAL, 'fax', 'received', 7],
                'RC sms received (7)'      => [ServiceType::RINGCENTRAL, 'sms', 'received', 7],
                'RC sms alerts (5)'        => [ServiceType::RINGCENTRAL, 'sms', 'alerts', 5],
                // Twilio sent carries Price + Reply; Clickatell sent does not.
                'Twilio sent (7)'          => [ServiceType::TWILIO_SMS, 'sms', 'sent', 7],
                'Clickatell sent (5)'      => [ServiceType::CLICKATELL_SMS, 'sms', 'sent', 5],
                'Twilio messages (6)'      => [ServiceType::TWILIO_SMS, 'sms', 'messages', 6],
            ];
        }

        #[DataProvider('columnCountProvider')]
        public function testColumnCounts(ServiceType $service, string $channel, string $tabKey, int $expected): void
        {
            $tabs = MessageUiProfile::tabs($service, $channel);

            self::assertArrayHasKey($tabKey, $tabs, "missing tab '$tabKey'");
            $cols = $tabs[$tabKey]['columns'] ?? null;
            self::assertIsArray($cols);
            self::assertCount($expected, $cols);
        }

        /**
         * The Fax Drop Box (dropzone) is fax-only; sms/email never get it, and
         * where present it is a dropzone (no table) with the id messageUI.js
         * targets.
         *
         * @return array<string, array{ServiceType, string, bool}>
         */
        public static function dropzoneProvider(): array
        {
            return [
                'RingCentral fax' => [ServiceType::RINGCENTRAL, 'fax', true],
                'etherFAX'        => [ServiceType::ETHERFAX, 'fax', true],
                'SignalWire'      => [ServiceType::SIGNALWIRE, 'fax', true],
                'RingCentral sms' => [ServiceType::RINGCENTRAL, 'sms', false],
                'Twilio sms'      => [ServiceType::TWILIO_SMS, 'sms', false],
                'Email'           => [ServiceType::EMAIL, 'email', false],
            ];
        }

        #[DataProvider('dropzoneProvider')]
        public function testDropzoneOnlyOnFax(ServiceType $service, string $channel, bool $expected): void
        {
            $tabs = MessageUiProfile::tabs($service, $channel);

            self::assertSame($expected, isset($tabs['upload']));
            if ($expected) {
                self::assertSame('dropzone', $tabs['upload']['type']);
                self::assertSame('upLoad', $tabs['upload']['id']);
            }
        }

        /**
         * The table ids are a contract with messageUI.js, which empties/fills
         * #rcvdetails, #sent-details, #msgdetails, #logdetails, #alertdetails.
         */
        public function testTableIdsAreStableContract(): void
        {
            $expected = [
                'received' => 'rcvdetails',
                'sent' => 'sent-details',
                'messages' => 'msgdetails',
                'logs' => 'logdetails',
                'alerts' => 'alertdetails',
            ];

            // RingCentral sms exercises every table-backed tab in one profile.
            $tabs = MessageUiProfile::tabs(ServiceType::RINGCENTRAL, 'sms');
            foreach (['received', 'sent', 'messages', 'logs', 'alerts'] as $key) {
                self::assertSame('table', $tabs[$key]['type']);
                self::assertSame($expected[$key], $tabs[$key]['tableId']);
            }
        }

        public function testReceivedRefreshesAndPanesCarryAnId(): void
        {
            foreach (self::tabKeyProvider() as [$service, $channel, $keys]) {
                $tabs = MessageUiProfile::tabs($service, $channel);

                self::assertNotEmpty($tabs['received']['refresh'], 'received tab should wire a refresh');

                foreach ($tabs as $key => $tab) {
                    self::assertArrayHasKey('id', $tab, "tab '$key' must carry a pane id");
                    self::assertNotSame('', $tab['id']);
                }
            }
        }

        public function testEmailExposesNoFaxOrSmsOnlyTabs(): void
        {
            $tabs = MessageUiProfile::tabs(ServiceType::EMAIL, 'email');

            foreach (['messages', 'logs', 'upload'] as $forbidden) {
                self::assertArrayNotHasKey($forbidden, $tabs, "email must not render a '$forbidden' tab");
            }
        }

        public function testEtherFaxReceivedHasTrashAndExtractedRawCells(): void
        {
            $cols = MessageUiProfile::tabs(ServiceType::ETHERFAX, 'fax')['received']['columns'] ?? null;
            self::assertIsArray($cols);

            $rawHtml = '';
            foreach ($cols as $faxsmsCol) {
                if (is_array($faxsmsCol) && isset($faxsmsCol['raw']) && is_string($faxsmsCol['raw'])) {
                    $rawHtml .= $faxsmsCol['raw'];
                }
            }

            self::assertStringContainsString('delete-selected-received', $rawHtml);
            self::assertStringContainsString('fa-eye', $rawHtml);
        }
    }
}
