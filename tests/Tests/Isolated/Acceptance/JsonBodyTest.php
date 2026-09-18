<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Acceptance;

use OpenEMR\Tests\Acceptance\Support\JsonBody;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Response;

/**
 * The acceptance suite runs against a container that is destroyed when the CI
 * job ends, so whatever a failure message does not carry is gone. These tests
 * pin the two properties that make the message worth reading: the raw bytes
 * survive into it, and a body too large to quote whole is still quoted at both
 * ends -- openemr#13905 was a valid JSON document with a second one appended,
 * which only the tail reveals.
 */
#[Group('isolated')]
final class JsonBodyTest extends TestCase
{
    public function testDecodeReturnsTheDecodedStructure(): void
    {
        $decoded = JsonBody::decode(new Response('{"resourceType":"CapabilityStatement"}'));

        $this->assertSame(['resourceType' => 'CapabilityStatement'], $decoded);
    }

    public function testDecodeReturnsNullForANonStructure(): void
    {
        // A bare JSON scalar decodes without error but is not the object the
        // smoke tests are asserting on, so it has to read as a failure.
        $this->assertNull(JsonBody::decode(new Response('"just a string"')));
    }

    public function testDescribeReportsAnEmptyBody(): void
    {
        $this->assertSame('Response body was empty.', JsonBody::describe(new Response('')));
    }

    public function testDescribeQuotesAShortBodyInFull(): void
    {
        $description = JsonBody::describe(new Response('<html>gateway timeout</html>'));

        $this->assertStringContainsString('<html>gateway timeout</html>', $description);
        $this->assertStringContainsString('28 bytes', $description);
        $this->assertStringContainsString('Syntax error', $description);
    }

    public function testDescribeQuotesBothEndsOfALongBody(): void
    {
        // The openemr#13905 shape: a complete document with a second one
        // appended by a post-response failure. The head alone looks correct, so
        // a message that elides the tail cannot explain the decode failure.
        $body = '{"resourceType":"CapabilityStatement","filler":"' . str_repeat('x', 2000) . '"}'
            . '{"error":"An error occurred while processing the request."}';

        $description = JsonBody::describe(new Response($body));

        $this->assertStringContainsString('{"resourceType":"CapabilityStatement"', $description);
        $this->assertStringContainsString('An error occurred while processing the request.', $description);
        $this->assertStringContainsString('bytes elided', $description);
    }

    public function testDescribeDoesNotDependOnAPriorDecodeCall(): void
    {
        // json_last_error() is process-global, so describe() has to re-decode
        // rather than read the error state left by the caller's decode().
        $response = new Response('{"resourceType":"CapabilityStatement"}{"error":"boom"}');
        JsonBody::decode($response);
        json_decode('{"unrelated":true}');

        $this->assertStringContainsString('Syntax error', JsonBody::describe($response));
    }
}
