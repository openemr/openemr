<?php

/**
 * Isolated tests for the throwing JSON helper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use JsonException;
use OpenEMR\Common\Utils\Json;
use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{
    public function testEncodeReturnsJsonText(): void
    {
        $this->assertSame('{"a":1,"b":[true,null]}', Json::encode(['a' => 1, 'b' => [true, null]]));
    }

    public function testEncodeAppliesTheGivenFlags(): void
    {
        $this->assertSame('{"path":"a/b"}', Json::encode(['path' => 'a/b'], JSON_UNESCAPED_SLASHES));
    }

    public function testEncodeThrowsOnInvalidUtf8(): void
    {
        $this->expectException(JsonException::class);
        Json::encode(chr(0xB1) . '1');
    }

    public function testEncodeThrowsEvenWhenAskedForPartialOutput(): void
    {
        $this->expectException(JsonException::class);
        Json::encode(['a' => chr(0xB1) . '1'], JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    public function testDecodeReturnsObjectsAsArrays(): void
    {
        $this->assertSame(['a' => 1, 'b' => [true, null]], Json::decode('{"a":1,"b":[true,null]}'));
    }

    public function testDecodeThrowsOnInvalidJson(): void
    {
        $this->expectException(JsonException::class);
        Json::decode('<html>not json</html>');
    }

    public function testDecodeThrowsOnEmptyText(): void
    {
        $this->expectException(JsonException::class);
        Json::decode('');
    }
}
