<?php

/**
 * Unit tests for Plaintext wrapper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use OpenEMR\Encryption\Plaintext;
use PHPUnit\Framework\TestCase;

final class PlaintextTest extends TestCase
{
    public function testStoresPlaintext(): void
    {
        $data = 'sensitive patient data';

        $plaintext = new Plaintext($data);

        self::assertSame($data, $plaintext->wrapped);
    }

    public function testDebugInfoRedactsContent(): void
    {
        $data = 'secret_data_should_not_appear';

        $plaintext = new Plaintext($data);
        $debugInfo = $plaintext->__debugInfo();

        self::assertArrayHasKey('wrapped', $debugInfo);
        self::assertSame('****', $debugInfo['wrapped']);
        self::assertStringNotContainsString('secret', print_r($debugInfo, true));
    }

    public function testIsReadonly(): void
    {
        $plaintext = new Plaintext('test');

        $reflection = new \ReflectionClass($plaintext);

        self::assertTrue($reflection->isReadOnly());
    }
}
