<?php

/**
 * Isolated tests for the OpenEMR PSR-17 factory wrapper.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mehedi Hasan <43575088+Mahdi-Hasan@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Mehedi Hasan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Http\Psr17Factory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;

#[Group('isolated')]
class Psr17FactoryIsolatedTest extends TestCase
{
    protected function setUp(): void
    {
        ServiceContainer::reset();
    }

    protected function tearDown(): void
    {
        ServiceContainer::reset();
    }

    private function factory(): ResponseFactoryInterface
    {
        $factory = ServiceContainer::getResponseFactory();
        self::assertInstanceOf(Psr17Factory::class, $factory);
        return $factory;
    }

    public function testCreateResponseUsesStandardPhraseWhenNoneGiven(): void
    {
        $factory = $this->factory();

        self::assertSame('Not Found', $factory->createResponse(404)->getReasonPhrase());
        self::assertSame('Not Found', $factory->createResponse(404, '')->getReasonPhrase());
    }

    public function testCreateResponseKeepsCustomPhrase(): void
    {
        $response = $this->factory()->createResponse(404, 'Gone Fishing');

        self::assertSame('Gone Fishing', $response->getReasonPhrase());
    }

    public function testCreateResponseLeavesUnknownCodeWithoutPhrase(): void
    {
        self::assertSame('', $this->factory()->createResponse(599)->getReasonPhrase());
    }
}
