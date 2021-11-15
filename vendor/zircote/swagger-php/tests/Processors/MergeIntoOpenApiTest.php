<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi\Tests\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Tests\OpenApiTestCase;

class MergeIntoOpenApiTest extends OpenApiTestCase
{
    public function testProcessor()
    {
        $openapi = new OpenApi([]);
        $info = new Info([]);
        $analysis = new Analysis(
            [
            $openapi,
            $info,
            ]
        );
        $this->assertSame($openapi, $analysis->openapi);
        $this->assertSame(Generator::UNDEFINED, $openapi->info);
        $analysis->process(new MergeIntoOpenApi());
        $this->assertSame($openapi, $analysis->openapi);
        $this->assertSame($info, $openapi->info);
        $this->assertCount(0, $analysis->unmerged()->annotations);
    }
}
