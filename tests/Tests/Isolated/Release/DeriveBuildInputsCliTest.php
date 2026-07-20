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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * The build-release workflow appends derive-build-inputs.php stdout
 * directly to $GITHUB_OUTPUT. Errors must therefore not leak into
 * stdout, or a failing run will write `<error>...</error>` lines into
 * the runner's output file and trigger an "Invalid format" step failure
 * that obscures the real error.
 */
final class DeriveBuildInputsCliTest extends TestCase
{
    private const BIN = __DIR__ . '/../../../../tools/release/bin/derive-build-inputs.php';

    public function testValidFlagsWriteKeyValueLinesToStdoutOnly(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--release-tag=v8_1_0',
            '--release-branch=rel-810',
        ]);
        $process->run();

        self::assertSame(0, $process->getExitCode(), 'expected success exit code');
        self::assertSame('', $process->getErrorOutput(), 'no stderr on success');
        self::assertSame(
            "version=8.1.0\nversion_branch=rel-810\nrelease_tag=v8_1_0\n",
            $process->getOutput(),
        );
    }

    public function testPayloadFileStdinWritesKeyValueLinesToStdoutOnly(): void
    {
        // The openemr-tag dispatch path: the workflow pipes the
        // client_payload envelope on stdin via --payload-file=-.
        $process = new Process(['php', self::BIN, '--payload-file=-']);
        $process->setInput((string) json_encode([
            'event' => 'openemr-tag',
            'data' => ['version' => '8.1.0', 'tag' => 'v8_1_0', 'branch' => 'rel-810'],
        ]));
        $process->run();

        self::assertSame(0, $process->getExitCode(), 'expected success exit code');
        self::assertSame('', $process->getErrorOutput(), 'no stderr on success');
        self::assertSame(
            "version=8.1.0\nversion_branch=rel-810\nrelease_tag=v8_1_0\n",
            $process->getOutput(),
        );
    }

    public function testValidationErrorsGoToStderrAndStdoutStaysEmpty(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--release-version=8.1.0',
            '--release-tag=BAD',
            '--release-branch=rel-810',
        ]);
        $process->run();

        self::assertSame(1, $process->getExitCode(), 'expected failure exit code');
        self::assertSame('', $process->getOutput(), 'stdout must stay empty so $GITHUB_OUTPUT is not corrupted');
        self::assertStringContainsString('field tag does not match expected shape', $process->getErrorOutput());
    }

    public function testMalformedJsonPayloadGoesToStderrAndStdoutStaysEmpty(): void
    {
        // A truncated or garbled client_payload must abort cleanly, not
        // append `<error>` lines to the runner's $GITHUB_OUTPUT file.
        $process = new Process(['php', self::BIN, '--payload-file=-']);
        $process->setInput('this is not json');
        $process->run();

        self::assertSame(1, $process->getExitCode(), 'expected failure exit code');
        self::assertSame('', $process->getOutput(), 'stdout must stay empty so $GITHUB_OUTPUT is not corrupted');
        self::assertStringContainsString('Payload is not valid JSON', $process->getErrorOutput());
    }

    public function testMissingRequiredFlagsGoToStderr(): void
    {
        $process = new Process(['php', self::BIN]);
        $process->run();

        self::assertSame(1, $process->getExitCode());
        self::assertSame('', $process->getOutput());
        self::assertStringContainsString('Provide either --payload-file', $process->getErrorOutput());
    }

    public function testMutuallyExclusiveSourcesRejected(): void
    {
        $process = new Process([
            'php',
            self::BIN,
            '--payload-file=-',
            '--release-version=8.1.0',
        ]);
        $process->setInput('{}');
        $process->run();

        self::assertSame(1, $process->getExitCode());
        self::assertSame('', $process->getOutput());
        self::assertStringContainsString('mutually exclusive', $process->getErrorOutput());
    }
}
