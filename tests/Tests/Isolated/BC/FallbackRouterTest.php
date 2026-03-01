<?php

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use OpenEMR\BC\FallbackRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(FallbackRouter::class)]
#[Small]
class FallbackRouterTest extends TestCase
{
    // TODO: add tests :)
    public function testNothing(): void
    {
        $this->markTestSkipped('Finish POC before testing');
    }
}
