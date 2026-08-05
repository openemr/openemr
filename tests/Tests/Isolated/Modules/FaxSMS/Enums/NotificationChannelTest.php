<?php

/**
 * Isolated NotificationChannel Test
 *
 * Tests the NotificationChannel enum's fromLegacyType() factory, which
 * normalizes the legacy stringly-typed $TYPE global into the typed enum.
 * The factory's behavior gates which WHERE clause runs in
 * cron_GetAlertPatientData(), so its mapping must be exhaustively covered.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Enums;

use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// The faxsms module's classes are not registered in the root composer
// autoloader (the module is loaded by OpenEMR's runtime module system).
// Pull the enum file in directly so this isolated test can run without
// bootstrapping the full module loader.
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Enums/NotificationChannel.php';

class NotificationChannelTest extends TestCase
{
    #[DataProvider('legacyTypeProvider')]
    public function testFromLegacyType(?string $input, NotificationChannel $expected): void
    {
        $this->assertSame($expected, NotificationChannel::fromLegacyType($input));
    }

    /**
     * @return array<string, array{?string, NotificationChannel}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function legacyTypeProvider(): array
    {
        return [
            'uppercase EMAIL'         => ['EMAIL', NotificationChannel::EMAIL],
            'lowercase email'         => ['email', NotificationChannel::EMAIL],
            'mixed-case Email'        => ['Email', NotificationChannel::EMAIL],
            'uppercase SMS'           => ['SMS', NotificationChannel::SMS],
            'lowercase sms'           => ['sms', NotificationChannel::SMS],
            'mixed-case Sms'          => ['Sms', NotificationChannel::SMS],
            'null defaults to SMS'    => [null, NotificationChannel::SMS],
            'empty string defaults'   => ['', NotificationChannel::SMS],
            'unknown defaults to SMS' => ['fax', NotificationChannel::SMS],
            'whitespace defaults'     => ['  ', NotificationChannel::SMS],
        ];
    }
}
