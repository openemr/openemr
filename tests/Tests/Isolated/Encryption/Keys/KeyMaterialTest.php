<?php

/**
 * Unit tests for KeyMaterial wrapper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude <noreply@anthropic.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Keys\KeyMaterial;
use PHPUnit\Framework\Attributes\DataProvider;
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

    /**
     * @return array{int}[]
     *
     * @codeCoverageIgnore
     */
    public static function byteLengths(): array
    {
        return [
            '2' => [2],
            '32' => [32],
            '48' => [48],
            '64' => [64],
        ];
    }

    /**
     * @param int<1, max> $bytes
     */
    #[DataProvider('byteLengths')]
    public function testGenerate(int $bytes): void
    {
        $km = KeyMaterial::generate($bytes);
        self::assertSame($bytes, strlen($km->key), 'Wrong number of bytes');
    }
}
