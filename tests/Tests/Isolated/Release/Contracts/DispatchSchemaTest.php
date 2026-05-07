<?php

/**
 * Validate the vendored dispatch.schema.json against good and bad fixtures
 * for each of the four cross-repo repository_dispatch events. The schema is
 * authored canonically in openemr/openemr-devops; this test asserts the
 * vendored copy in this repo behaves identically.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Contracts;

use JsonSchema\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DispatchSchemaTest extends TestCase
{
    private const SCHEMA_PATH = __DIR__ . '/../../../../../tools/release/contracts/dispatch.schema.json';
    private const FIXTURE_DIR = __DIR__ . '/../fixtures/dispatch';

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function goodFixtures(): iterable
    {
        yield 'openemr-rel-cut'       => ['good-rel-cut.json'];
        yield 'openemr-rel-update'    => ['good-rel-update.json'];
        yield 'openemr-tag'           => ['good-tag.json'];
        yield 'openemr-tag (test)'    => ['good-tag-test.json'];
        yield 'openemr-docs-binaries' => ['good-docs-binaries.json'];
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function badFixtures(): iterable
    {
        yield 'openemr-rel-cut missing prev_release'    => ['bad-rel-cut.json', 'prev_release'];
        yield 'openemr-rel-update non-hex sha'          => ['bad-rel-update.json', 'sha'];
        yield 'openemr-tag with dotted version'         => ['bad-tag.json', 'tag'];
        yield 'openemr-docs-binaries empty files array' => ['bad-docs-binaries.json', 'files'];
    }

    #[DataProvider('goodFixtures')]
    public function testGoodFixtureValidates(string $fixture): void
    {
        $validator = $this->validateFixture($fixture);
        self::assertTrue(
            $validator->isValid(),
            sprintf('Expected %s to validate. %s', $fixture, $this->errorString($validator)),
        );
    }

    #[DataProvider('badFixtures')]
    public function testBadFixtureFails(string $fixture, string $expectedField): void
    {
        $validator = $this->validateFixture($fixture);
        self::assertFalse(
            $validator->isValid(),
            sprintf('Expected %s to fail validation but it passed.', $fixture),
        );
        $errorString = $this->errorString($validator);
        self::assertStringContainsString(
            $expectedField,
            $errorString,
            sprintf('Expected validation error to mention "%s". Got: %s', $expectedField, $errorString),
        );
    }

    private function validateFixture(string $fixtureName): Validator
    {
        $payload = $this->decodeJson(self::FIXTURE_DIR . '/' . $fixtureName);
        $schema = $this->decodeJson(self::SCHEMA_PATH);
        $validator = new Validator();
        $validator->validate($payload, $schema);
        return $validator;
    }

    private function decodeJson(string $path): mixed
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }
        return json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
    }

    private function errorString(Validator $validator): string
    {
        return 'Errors: ' . json_encode($validator->getErrors(), JSON_THROW_ON_ERROR);
    }
}
