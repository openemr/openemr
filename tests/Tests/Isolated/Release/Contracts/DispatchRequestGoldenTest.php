<?php

/**
 * Golden-envelope tests: assert that DispatchRequest::toEnvelope() produces
 * the exact wire shape captured by the good-*.json fixtures under
 * tests/Tests/Isolated/Release/fixtures/dispatch/. Complements
 * DispatchSchemaTest (fixture ↔ schema) and Dispatcher::validateAgainstSchema
 * (runtime code ↔ schema) by closing the "code ↔ fixture" gap. Without this
 * coverage, a serialization bug producing the wrong shape (e.g. empty PHP
 * array `[]` json_encoding as JSON `[]` not `{}`) would still slip past
 * every fixture-side and schema-side test until the runtime validator
 * caught it at dispatch time (which is what happened with the
 * release-targets-changed event pre-#12713).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Contracts;

use OpenEMR\Release\DispatchRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DispatchRequestGoldenTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../fixtures/dispatch';

    /**
     * @return iterable<string, array{0: string, 1: DispatchRequest}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function goldenEnvelopes(): iterable
    {
        yield 'openemr-rel-cut' => [
            'good-rel-cut.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_REL_CUT,
                repo: 'openemr/openemr',
                sha: '0123456789abcdef0123456789abcdef01234567',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T12:00:00Z',
                appToken: 'tok',
                data: ['branch' => 'rel-810', 'version' => '8.1.0', 'prev_release' => '8.0.0'],
            ),
        ];

        yield 'openemr-rel-update' => [
            'good-rel-update.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_REL_UPDATE,
                repo: 'openemr/openemr',
                sha: 'fedcba9876543210fedcba9876543210fedcba98',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T12:15:30Z',
                appToken: 'tok',
                data: ['branch' => 'rel-810', 'version' => '8.1.0', 'prev_release' => '8.0.0'],
            ),
        ];

        yield 'openemr-tag' => [
            'good-tag.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_TAG,
                repo: 'openemr/openemr',
                sha: 'abcdef0123456789abcdef0123456789abcdef01',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T12:30:00Z',
                appToken: 'tok',
                data: ['tag' => 'v8_1_0', 'branch' => 'rel-810', 'version' => '8.1.0'],
            ),
        ];

        yield 'openemr-tag (test)' => [
            'good-tag-test.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_TAG,
                repo: 'openemr/openemr',
                sha: 'abcdef0123456789abcdef0123456789abcdef01',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T12:30:00Z',
                appToken: 'tok',
                data: ['tag' => 'v8_1_0-test.abcdef0', 'branch' => 'rel-test', 'version' => '8.1.0'],
            ),
        ];

        yield 'openemr-docs-binaries' => [
            'good-docs-binaries.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_DOCS_BINARIES,
                repo: 'openemr/website-openemr',
                sha: 'deadbeefdeadbeefdeadbeefdeadbeefdeadbeef',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T13:00:00Z',
                appToken: 'tok',
                data: [
                    'version' => '8.1.0',
                    'branch' => 'rel-810',
                    'files' => ['openemr-8.1.0-ehi.tar.gz', 'openemr-8.1.0-b10.tar.gz'],
                ],
            ),
        ];

        yield 'release-targets-changed' => [
            'good-release-targets-changed.json',
            new DispatchRequest(
                event: DispatchRequest::EVENT_RELEASE_TARGETS_CHANGED,
                repo: 'openemr/openemr',
                sha: 'cafebabecafebabecafebabecafebabecafebabe',
                actor: 'openemr-release-bot',
                dispatchedAt: '2026-04-29T13:30:00Z',
                appToken: 'tok',
                data: [],
            ),
        ];
    }

    #[DataProvider('goldenEnvelopes')]
    public function testEnvelopeSerializationMatchesFixture(string $fixture, DispatchRequest $request): void
    {
        $fixtureContents = file_get_contents(self::FIXTURE_DIR . '/' . $fixture);
        self::assertNotFalse($fixtureContents, 'Could not read fixture ' . $fixture);
        // Decode in object mode so JSON `{}` stays a stdClass and JSON
        // `[]` stays an array. Array-mode decode would collapse both to
        // PHP []; the release-targets-changed event's empty-data shape
        // wouldn't be distinguishable.
        $expected = json_decode($fixtureContents, false, 512, JSON_THROW_ON_ERROR);
        $actual = json_decode(
            json_encode($request->toEnvelope(), JSON_THROW_ON_ERROR),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );
        self::assertEquals($expected, $actual, sprintf(
            'Envelope from DispatchRequest::toEnvelope() does not match golden fixture %s.',
            $fixture,
        ));
    }
}
