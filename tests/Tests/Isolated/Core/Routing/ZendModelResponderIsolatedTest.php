<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core\Routing;

use OpenEMR\Core\Routing\ZendModelResponder;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\FixtureActionController;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\JsonModelStateFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Group('isolated')]
#[Group('core')]
class ZendModelResponderIsolatedTest extends TestCase
{
    public function testViewModelDelegatesToRenderer(): void
    {
        // A real ViewModel sourced from the inherited AbstractActionController
        // default action, so the deprecated view model is never named in test
        // code (the deprecation stays confined to vendor/baselined controllers).
        $viewModel = (new FixtureActionController())->indexAction();

        $responder = new ZendModelResponder(
            fn($m): string => '<rendered/>',
        );
        $response = $responder->toResponse($viewModel);

        $this->assertSame('<rendered/>', $response->getContent());
    }

    public function testResponsePassThrough(): void
    {
        $original = new Response('already a response', 201);
        $responder = new ZendModelResponder(fn($m): string => 'unused');

        $this->assertSame($original, $responder->toResponse($original));
    }

    public function testStringBecomesHtmlResponse(): void
    {
        $responder = new ZendModelResponder(fn($m): string => 'unused');
        $response = $responder->toResponse('<p>plain</p>');

        $this->assertSame('<p>plain</p>', $response->getContent());
    }

    public function testUnsupportedResultThrows(): void
    {
        $responder = new ZendModelResponder(fn($m): string => 'unused');

        $this->expectException(\RuntimeException::class);
        $responder->toResponse(42);
    }

    public function testJsonModelWithTraversableVariablesIsSerialized(): void
    {
        // A JsonModel whose variables container is a Traversable (not a plain
        // array): modelVariables() drains it via iterator_to_array so the keys
        // survive into the JSON body.
        $responder = new ZendModelResponder(fn($m): string => 'unused');

        $response = $responder->toResponse(JsonModelStateFactory::withTraversableVariables());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('{"alpha":1,"beta":2}', $response->getContent());
    }

    public function testJsonModelWithArrayAccessOnlyVariablesYieldsEmptyJson(): void
    {
        // A JsonModel whose variables container is ArrayAccess but not
        // Traversable carries no enumerable keys, so modelVariables() returns an
        // empty array and the response serializes to "[]".
        $responder = new ZendModelResponder(fn($m): string => 'unused');

        $response = $responder->toResponse(JsonModelStateFactory::withArrayAccessOnlyVariables());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame('[]', $response->getContent());
    }
}
