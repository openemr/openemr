<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Storage;

use OpenEMR\Encryption\Storage\KeyMaterialId;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class KeyMaterialIdTest extends TestCase
{
    public function testWrapping(): void
    {
        $raw = 'some-key-123';
        $id = new KeyMaterialId($raw);
        self::assertSame($raw, $id->id);
    }
}
