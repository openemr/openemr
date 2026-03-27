<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Keys\Id;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class IdTest extends TestCase
{
    public function testWrapping(): void
    {
        $raw = 'some-key-123';
        $id = new Id($raw);
        self::assertSame($raw, $id->id);
    }
}
