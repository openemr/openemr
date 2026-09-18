<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\TagDispatchPayload;
use PHPUnit\Framework\TestCase;

final class TagDispatchPayloadTest extends TestCase
{
    private const VALID_DATA = ['version' => '8.1.0', 'tag' => 'v8_1_0', 'branch' => 'rel-810'];

    /**
     * @return array<string, mixed>
     */
    private function envelope(string $event = 'openemr-tag', mixed $data = self::VALID_DATA): array
    {
        return [
            'event' => $event,
            'repo' => 'openemr/openemr',
            'sha' => str_repeat('a', 40),
            'actor' => 'openemr-release-bot[bot]',
            'dispatched_at' => '2026-04-29T12:00:00Z',
            'data' => $data,
        ];
    }

    public function testParsesValidEnvelope(): void
    {
        $payload = TagDispatchPayload::fromEnvelope($this->envelope());

        self::assertSame('8.1.0', $payload->version);
        self::assertSame('v8_1_0', $payload->tag);
        self::assertSame('rel-810', $payload->branch);
    }

    public function testAcceptsTestTagSuffix(): void
    {
        $envelope = $this->envelope(data: [
            'version' => '8.1.0',
            'tag' => 'v8_1_0-test.abcdef0',
            'branch' => 'rel-test',
        ]);
        $payload = TagDispatchPayload::fromEnvelope($envelope);

        self::assertSame('v8_1_0-test.abcdef0', $payload->tag);
        self::assertSame('rel-test', $payload->branch);
    }

    public function testRejectsNonObjectEnvelope(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not a JSON object');
        TagDispatchPayload::fromEnvelope('not-an-object');
    }

    public function testRejectsWrongEvent(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected event=openemr-tag');
        TagDispatchPayload::fromEnvelope($this->envelope('openemr-rel-cut'));
    }

    public function testRejectsMissingDataObject(): void
    {
        $envelope = $this->envelope();
        unset($envelope['data']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing data object');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsMissingVersionField(): void
    {
        $envelope = $this->envelope(data: ['tag' => 'v8_1_0', 'branch' => 'rel-810']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or empty field: data.version');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsEmptyTagField(): void
    {
        $envelope = $this->envelope(data: ['version' => '8.1.0', 'tag' => '', 'branch' => 'rel-810']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or empty field: data.tag');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsNullField(): void
    {
        // jq -r emits literal "null" for missing fields, so simulate the same
        // shape getting through to PHP — null in the typed array.
        $envelope = $this->envelope(data: ['version' => '8.1.0', 'tag' => 'v8_1_0', 'branch' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('missing or empty field: data.branch');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsMalformedVersion(): void
    {
        $envelope = $this->envelope(data: ['version' => '8.1', 'tag' => 'v8_1_0', 'branch' => 'rel-810']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('field version does not match expected shape');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsMalformedTag(): void
    {
        $envelope = $this->envelope(data: ['version' => '8.1.0', 'tag' => 'v8.1.0', 'branch' => 'rel-810']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('field tag does not match expected shape');
        TagDispatchPayload::fromEnvelope($envelope);
    }

    public function testRejectsMalformedBranch(): void
    {
        $envelope = $this->envelope(data: ['version' => '8.1.0', 'tag' => 'v8_1_0', 'branch' => 'master']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('field branch does not match expected shape');
        TagDispatchPayload::fromEnvelope($envelope);
    }
}
