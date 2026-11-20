<?php

/**
 * Runs the Synthea jar to generate patients.
 *
 * Java must already be installed (it needs root: `apk add openjdk17-jre` in
 * the dev container). The jar is downloaded on first use into the work
 * directory, from the same URL `openemr-cmd irp` uses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Synthea;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OpenEMR\DemoSeed\SeedFile\SyntheaOptions;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final readonly class SyntheaRunner
{
    public const JAR_URL = 'https://github.com/synthetichealth/synthea/releases/download/master-branch-latest/synthea-with-dependencies.jar';

    public function __construct(
        private ClientInterface $httpClient,
        private Filesystem $filesystem,
        private string $javaBinary = 'java',
    ) {
    }

    public function assertJavaInstalled(): void
    {
        $process = new Process([$this->javaBinary, '-version']);
        try {
            $process->run();
        } catch (\Throwable $e) {
            throw new SyntheaException('Java is not installed', 0, $e);
        }
        if (!$process->isSuccessful()) {
            throw new SyntheaException(
                'Java is not installed. In the dev container, install it as root: apk add openjdk17-jre',
            );
        }
    }

    /**
     * Downloads the jar to $jarPath unless it's already there.
     *
     * @return bool true when it was downloaded
     */
    public function ensureJar(string $jarPath): bool
    {
        if (is_file($jarPath)) {
            return false;
        }
        $this->filesystem->mkdir(dirname($jarPath));
        $partial = $jarPath . '.part';
        try {
            $this->httpClient->request('GET', self::JAR_URL, ['sink' => $partial]);
        } catch (GuzzleException $e) {
            $this->filesystem->remove($partial);
            throw new SyntheaException('Could not download the Synthea jar', 0, $e);
        }
        $this->filesystem->rename($partial, $jarPath, true);
        return true;
    }

    /**
     * Generates patients into $outputDirectory (its ccda/ and fhir/ are
     * emptied first).
     *
     * @param ?callable(string): void $onOutput receives Synthea's output as it runs
     */
    public function run(SyntheaOptions $options, string $jarPath, string $outputDirectory, ?callable $onOutput = null): void
    {
        $outputDirectory = rtrim($outputDirectory, '/');
        $this->filesystem->remove([$outputDirectory . '/ccda', $outputDirectory . '/fhir']);
        $this->filesystem->mkdir($outputDirectory);

        $process = new Process(
            [$this->javaBinary, '-jar', $jarPath, ...$options->toArguments($outputDirectory)],
            $outputDirectory,
            null,
            null,
            null,
        );
        $process->run(static function (string $type, string $buffer) use ($onOutput): void {
            if ($onOutput !== null) {
                $onOutput($buffer);
            }
        });
        if (!$process->isSuccessful()) {
            throw new SyntheaException('Synthea failed with exit code ' . ($process->getExitCode() ?? -1));
        }
    }
}
