<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\SessionConfiguration;
use PHPUnit\Framework\TestCase;

class SessionConfigurationTest extends TestCase
{
    public function testToSessionStartOptionsReturnsValidPhpSessionKeys(): void
    {
        $config = new SessionConfiguration(
            name: 'TestSession',
            cookiePath: '/test',
        );

        $options = $config->toSessionStartOptions();

        $expectedKeys = [
            'name',
            'cookie_path',
            'gc_maxlifetime',
            'use_strict_mode',
            'use_cookies',
            'use_only_cookies',
            'cookie_samesite',
            'cookie_secure',
            'cookie_httponly',
            'read_and_close',
        ];

        self::assertSame(
            $expectedKeys,
            array_keys($options),
            'toSessionStartOptions must return exactly the keys PHP session_start() expects',
        );

        self::assertSame('TestSession', $options['name'], 'name should match constructor value');
        self::assertSame('/test', $options['cookie_path'], 'cookie_path should match constructor value');
    }
}
