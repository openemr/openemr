<?php

/**
 * Unit tests for KeyMaterial wrapper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Keys\KeyMaterial;
use PHPUnit\Framework\TestCase;

final class KeyMaterialTest extends TestCase
{
    public function testStoresKeyMaterial(): void
    {
        $key = 'test_key_material_______________'; // 32 bytes

        $material = new KeyMaterial($key);

        self::assertSame($key, $material->key);
    }

    public function testDebugInfoRedactsKey(): void
    {
        $key = 'secret_key_should_not_appear____';

        $material = new KeyMaterial($key);
        $debugInfo = $material->__debugInfo();

        self::assertArrayHasKey('key', $debugInfo);
        self::assertSame('******', $debugInfo['key']);
        self::assertStringNotContainsString('secret', print_r($debugInfo, true));
    }

    public function testIsReadonly(): void
    {
        $material = new KeyMaterial('test');

        $reflection = new \ReflectionClass($material);

        self::assertTrue($reflection->isReadOnly());
    }
}
