<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Keys\Id;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
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
