<?php

/**
 * Records wall-clock timings for E2e tests.
 *
 * The E2e suite is recorded end to end as a single screen capture by the
 * `selenium/video` container. That recording is only navigable if something
 * says which test was running at which second, so this recorder appends one
 * JSON object per test lifecycle event, timestamped with the wall clock the
 * recorder container shares. `ci/e2e-video-chapters.php` turns the resulting
 * file into WebVTT and chapter sidecars.
 *
 * Nothing here touches the browser: annotating the recording costs the test
 * suite no wall-clock time at all.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use Symfony\Component\Filesystem\Filesystem;

final class TimelineRecorder
{
    /**
     * Only E2e tests are recorded: they are the only suite the video
     * container captures, and a timeline file for the unit suite would be
     * noise.
     */
    public const E2E_NAMESPACE_PREFIX = 'OpenEMR\\Tests\\E2e\\';

    public const DEFAULT_OUTPUT_FILE = 'e2e-timeline.jsonl';

    private readonly Filesystem $filesystem;

    /**
     * Truncation is deferred to the first recorded event so that a run
     * without E2e tests leaves any existing timeline alone.
     */
    private bool $truncated = false;

    public function __construct(private readonly string $outputFile = self::DEFAULT_OUTPUT_FILE)
    {
        $this->filesystem = new Filesystem();
    }

    public function record(Test $test, TimelineEventType $event): void
    {
        if (!$test instanceof TestMethod) {
            return;
        }
        if (!str_starts_with($test->className(), self::E2E_NAMESPACE_PREFIX)) {
            return;
        }
        if (!$this->truncated) {
            $this->filesystem->dumpFile($this->outputFile, '');
            $this->truncated = true;
        }
        $this->filesystem->appendToFile($this->outputFile, $this->encode([
            'time' => microtime(true),
            'event' => $event->value,
            'id' => $test->id(),
            'class' => $test->className(),
            'method' => $test->methodName(),
        ]) . PHP_EOL);
    }

    /**
     * Wraps json_encode so its string|false return never escapes this class.
     *
     * @param array<string, float|string> $record
     * @throws \JsonException
     */
    private function encode(array $record): string
    {
        return json_encode($record, JSON_THROW_ON_ERROR);
    }
}
