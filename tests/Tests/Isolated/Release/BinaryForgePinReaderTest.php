<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\BinaryForgePinReader;
use PHPUnit\Framework\TestCase;

final class BinaryForgePinReaderTest extends TestCase
{
    private string $dockerfile = '';

    protected function tearDown(): void
    {
        if ($this->dockerfile !== '' && file_exists($this->dockerfile)) {
            unlink($this->dockerfile);
        }
        parent::tearDown();
    }

    public function testReadsThePinsFromArgDefaults(): void
    {
        $path = $this->writeDockerfile(<<<'DOCKERFILE'
        # syntax=docker/dockerfile:1
        ARG ALPINE_VERSION=3.24
        FROM alpine:${ALPINE_VERSION} AS base

        ARG OPENEMR_VERSION=8_3_0
        ARG BINARY_RELEASE_DATE=08232026
        ARG PHP_VERSION=8.5
        ENV OPENEMR_VERSION=${OPENEMR_VERSION}
        DOCKERFILE);

        $pin = (new BinaryForgePinReader($path))->read();

        self::assertSame('8_3_0', $pin->openemrVersion);
        self::assertSame('08232026', $pin->releaseDate);
        self::assertSame('8.5', $pin->phpVersion);
    }

    /**
     * `ENV OPENEMR_VERSION=${OPENEMR_VERSION}` sits two lines below the
     * ARG in the real Dockerfile. Anchoring on `ARG` keeps the reader off
     * the interpolated ENV line, which would parse as the literal
     * `${OPENEMR_VERSION}`.
     */
    public function testIgnoresTheEnvLineThatMirrorsTheArg(): void
    {
        $path = $this->writeDockerfile(<<<'DOCKERFILE'
        ENV OPENEMR_VERSION=${OPENEMR_VERSION}
        ARG OPENEMR_VERSION=8_3_0
        ARG BINARY_RELEASE_DATE=08232026
        ARG PHP_VERSION=8.5
        DOCKERFILE);

        self::assertSame('8_3_0', (new BinaryForgePinReader($path))->read()->openemrVersion);
    }

    public function testFailsLoudlyWhenAnArgIsMissing(): void
    {
        $path = $this->writeDockerfile(<<<'DOCKERFILE'
        ARG OPENEMR_VERSION=8_3_0
        ARG PHP_VERSION=8.5
        DOCKERFILE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('BINARY_RELEASE_DATE');

        (new BinaryForgePinReader($path))->read();
    }

    public function testFailsLoudlyWhenTheDockerfileIsMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot read Dockerfile');

        (new BinaryForgePinReader(sys_get_temp_dir() . '/no-such-dockerfile'))->read();
    }

    /**
     * The reader is the tooling's view of the real file, so a rename or
     * reformat of the shipped ARG block has to fail here rather than in a
     * scheduled bot run nobody is watching.
     */
    public function testReadsTheShippedBinaryDockerfile(): void
    {
        $repoRoot = dirname(__DIR__, 4);
        $pin = (new BinaryForgePinReader($repoRoot . '/' . BinaryForgePinReader::DEFAULT_DOCKERFILE))->read();

        self::assertMatchesRegularExpression('/^php\d+$/', $pin->phpSelector());
    }

    private function writeDockerfile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'binary-forge-dockerfile');
        self::assertIsString($path);
        file_put_contents($path, $contents . "\n");
        $this->dockerfile = $path;

        return $path;
    }
}
