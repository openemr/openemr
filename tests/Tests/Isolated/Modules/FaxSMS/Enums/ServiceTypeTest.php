<?php

/**
 * Tests for the ServiceType enum.
 *
 * ServiceType centralizes vendor identity that messageUI.php and the
 * controllers used to encode as magic numbers ('1','3','6'). The restructure
 * leans on it, so its conversions need to be pinned:
 *   - fromValue() must accept numeric ids / numeric strings and fall back to
 *     DISABLED for anything unrecognized (never throw),
 *   - the numeric value <-> case round-trip must hold (the dispatch + setup
 *     layers persist the int),
 *   - getVendorKey() credential tags must stay stable (they key the
 *     module_faxsms_credentials rows),
 *   - casesForChannel() must list exactly the vendors valid per channel.
 *
 * Pure enum logic; the methods under test do not touch globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Enums {
    // Some ServiceType label helpers call xlt(); an identity stub declared IN
    // the enum's namespace (matching runner_test_stubs.php's in-namespace style)
    // keeps the isolated suite free of OpenEMR's gettext bootstrap without
    // touching the global namespace.
    if (!function_exists('OpenEMR\Modules\FaxSMS\Enums\xlt')) {
        function xlt(string $s): string
        {
            return $s;
        }
    }
}

namespace {
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

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Enums {

    use OpenEMR\Modules\FaxSMS\Enums\ServiceType;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\TestCase;

    final class ServiceTypeTest extends TestCase
    {
        /**
         * @return array<string, array{mixed, ServiceType}>
         */
        public static function fromValueProvider(): array
        {
            return [
                'int 0 disabled'     => [0, ServiceType::DISABLED],
                'int 1 ringcentral'  => [1, ServiceType::RINGCENTRAL],
                'int 2 twilio'       => [2, ServiceType::TWILIO_SMS],
                'int 3 etherfax'     => [3, ServiceType::ETHERFAX],
                'int 4 email'        => [4, ServiceType::EMAIL],
                'int 5 clickatell'   => [5, ServiceType::CLICKATELL_SMS],
                'int 6 signalwire'   => [6, ServiceType::SIGNALWIRE],
                'int 9 voice'        => [9, ServiceType::VOICE],
                'numeric string "1"' => ['1', ServiceType::RINGCENTRAL],
                'numeric string "6"' => ['6', ServiceType::SIGNALWIRE],
                'float 1.0'          => [1.0, ServiceType::RINGCENTRAL],
            ];
        }

        #[DataProvider('fromValueProvider')]
        public function testFromValueResolvesKnownValues(mixed $value, ServiceType $expected): void
        {
            self::assertSame($expected, ServiceType::fromValue($value));
        }

        /**
         * Unrecognized input must degrade to DISABLED, never throw - setup and
         * dispatch pass through whatever is stored/requested.
         *
         * @return array<string, array{mixed}>
         */
        public static function unrecognizedProvider(): array
        {
            return [
                'gap value 7'    => [7],
                'out of range'   => [99],
                'negative'       => [-1],
                'non-numeric'    => ['etherfax'],
                'empty string'   => [''],
                'null'           => [null],
            ];
        }

        #[DataProvider('unrecognizedProvider')]
        public function testFromValueFallsBackToDisabled(mixed $value): void
        {
            self::assertSame(ServiceType::DISABLED, ServiceType::fromValue($value));
        }

        public function testNumericValueRoundTrips(): void
        {
            foreach (ServiceType::cases() as $case) {
                self::assertSame($case, ServiceType::fromValue($case->value));
            }
        }

        /**
         * @return list<array{ServiceType, string}>
         */
        public static function vendorKeyProvider(): array
        {
            return [
                [ServiceType::DISABLED, ''],
                [ServiceType::RINGCENTRAL, '_ringcentral'],
                [ServiceType::TWILIO_SMS, '_twilio'],
                [ServiceType::ETHERFAX, '_etherfax'],
                [ServiceType::EMAIL, '_email'],
                [ServiceType::CLICKATELL_SMS, '_clickatell'],
                [ServiceType::SIGNALWIRE, '_signalwire'],
                [ServiceType::VOICE, '_voice'],
            ];
        }

        #[DataProvider('vendorKeyProvider')]
        public function testVendorKeysAreStable(ServiceType $case, string $expected): void
        {
            self::assertSame($expected, $case->getVendorKey());
        }

        /**
         * @return array<string, array{string, list<ServiceType>}>
         */
        public static function channelProvider(): array
        {
            return [
                'sms'   => ['sms', [ServiceType::DISABLED, ServiceType::RINGCENTRAL, ServiceType::TWILIO_SMS, ServiceType::CLICKATELL_SMS]],
                'fax'   => ['fax', [ServiceType::DISABLED, ServiceType::RINGCENTRAL, ServiceType::ETHERFAX, ServiceType::SIGNALWIRE]],
                'email' => ['email', [ServiceType::DISABLED, ServiceType::EMAIL]],
                'voice' => ['voice', [ServiceType::DISABLED, ServiceType::VOICE]],
                'bogus' => ['nope', [ServiceType::DISABLED]],
            ];
        }

        /**
         * @param list<ServiceType> $expected
         */
        #[DataProvider('channelProvider')]
        public function testCasesForChannel(string $channel, array $expected): void
        {
            self::assertSame($expected, ServiceType::casesForChannel($channel));
        }

        /**
         * @return list<array{ServiceType, string}>
         */
        public static function displayNameProvider(): array
        {
            return [
                [ServiceType::RINGCENTRAL, 'RingCentral'],
                [ServiceType::TWILIO_SMS, 'Twilio SMS'],
                [ServiceType::ETHERFAX, 'etherFAX'],
                [ServiceType::EMAIL, 'Email'],
                [ServiceType::CLICKATELL_SMS, 'Clickatell SMS'],
                [ServiceType::SIGNALWIRE, 'SignalWire Fax'],
                [ServiceType::VOICE, 'Voice'],
            ];
        }

        #[DataProvider('displayNameProvider')]
        public function testDisplayNames(ServiceType $case, string $expected): void
        {
            self::assertSame($expected, $case->getDisplayName());
        }
    }
}
