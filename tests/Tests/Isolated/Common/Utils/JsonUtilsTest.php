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
use PHPUnit\Framework\TestCase;

class JsonUtilsTest extends TestCase
{
    public function testDecodeReturnsObjectsAsArrays(): void
    {
        self::assertSame(['a' => [1, 'b']], JsonUtils::decode('{"a":[1,"b"]}'));
    }

    public function testDecodeReturnsJsonNullAsNull(): void
    {
        self::assertNull(JsonUtils::decode('null'));
    }

    public function testDecodeThrowsOnInvalidJson(): void
    {
        $this->expectException(\JsonException::class);
        JsonUtils::decode('GPT');
    }
}
