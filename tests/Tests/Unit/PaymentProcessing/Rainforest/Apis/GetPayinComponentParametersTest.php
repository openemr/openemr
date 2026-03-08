<?php

/**
 * Tests for the GetPayinComponentParameters API class
 *
 * @package   OpenEMR
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR, Inc
 * @license   GNU General Public License 3
 * @link      https://www.open-emr.org
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\PaymentProcessing\Rainforest\Apis;

use JsonException;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\PaymentProcessing\Rainforest\Apis\GetPayinComponentParameters;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

final class GetPayinComponentParametersTest extends TestCase
{
    private Psr17Factory $factory;

    protected function setUp(): void
    {
        $this->factory = new Psr17Factory();
    }

    // ------- JSON parsing -------

    public function testThrowsOnInvalidJson(): void
    {
        $request = $this->buildRequest('not valid json');
        $bag = $this->createMock(OEGlobalsBag::class);

        $this->expectException(JsonException::class);
        GetPayinComponentParameters::parseRawRequest($request, $bag);
    }

    public function testThrowsOnEmptyBody(): void
    {
        $request = $this->buildRequest('');
        $bag = $this->createMock(OEGlobalsBag::class);

        $this->expectException(JsonException::class);
        GetPayinComponentParameters::parseRawRequest($request, $bag);
    }

    // ------- Amount validation -------

    public function testThrowsOnZeroAmount(): void
    {
        $request = $this->buildRequest(json_encode([
            'dollars' => '0.00',
            'patientId' => '123',
            'encounters' => [],
        ], JSON_THROW_ON_ERROR));
        $bag = $this->createMock(OEGlobalsBag::class);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Payment amount must be positive');
        GetPayinComponentParameters::parseRawRequest($request, $bag);
    }

    public function testThrowsOnNegativeAmount(): void
    {
        $request = $this->buildRequest(json_encode([
            'dollars' => '-5.00',
            'patientId' => '123',
            'encounters' => [],
        ], JSON_THROW_ON_ERROR));
        $bag = $this->createMock(OEGlobalsBag::class);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Payment amount must be positive');
        GetPayinComponentParameters::parseRawRequest($request, $bag);
    }

    // ------- Happy path -------

    public function testValidRequestReturnsComponentParameters(): void
    {
        // Needs Api to be injectable rather than constructed via static
        // Api::makeFromGlobals() call before this can be tested.
        $this->markTestIncomplete('Requires Api dependency injection to mock the Rainforest API call');
    }

    // ------- Helpers -------

    private function buildRequest(string $body): ServerRequestInterface
    {
        $request = $this->factory->createServerRequest('POST', '/payment');
        $request = $request->withHeader('Content-Type', 'application/json');
        $request = $request->withBody($this->factory->createStream($body));
        return $request;
    }
}
