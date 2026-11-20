<?php

/**
 * Isolated tests for JsonUtils
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\JsonUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonUtilsTest extends TestCase
{
    public function testDecodeArrayReturnsObjectsAsArrays(): void
    {
        self::assertSame(['a' => [1, 'b']], JsonUtils::decodeArray('{"a":[1,"b"]}'));
    }

    public function testDecodeArrayReturnsLists(): void
    {
        self::assertSame(['G', 'P'], JsonUtils::decodeArray('["G","P"]'));
    }

    #[DataProvider('nonArrayJsonProvider')]
    public function testDecodeArrayReturnsNullForOtherJson(string $json): void
    {
        self::assertNull(JsonUtils::decodeArray($json));
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nonArrayJsonProvider(): array
    {
        return [
            'null'   => ['null'],
            'number' => ['2'],
            'string' => ['"T"'],
            'bool'   => ['true'],
        ];
    }

    public function testDecodeArrayThrowsOnInvalidJson(): void
    {
        $this->expectException(\JsonException::class);
        JsonUtils::decodeArray('GPT');
    }
}
