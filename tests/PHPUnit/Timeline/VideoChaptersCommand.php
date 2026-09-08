<?php

/**
 * Writes navigation sidecars for the E2e video recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use DateTimeImmutable;
use InvalidArgumentException;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'e2e:video-chapters',
    description: 'Turn a recorded E2e test timeline into WebVTT and chapter sidecars for the video',
)]
final class VideoChaptersCommand extends Command
{
    public const VTT_FILE = 'video.vtt';
    public const FFMETADATA_FILE = 'video.chapters.ffmetadata';
    public const INDEX_FILE = 'video.chapters.txt';

    protected function configure(): void
    {
        $this
            ->addOption(
                'timeline',
                null,
                InputOption::VALUE_REQUIRED,
                'Timeline file recorded by the E2e suite',
                TimelineRecorder::DEFAULT_OUTPUT_FILE,
            )
            ->addOption(
                'out-dir',
                null,
                InputOption::VALUE_REQUIRED,
                'Directory holding the recording, where the sidecars are written',
                'selenium-videos',
            )
            ->addOption(
                'recording-started-at',
                null,
                InputOption::VALUE_REQUIRED,
                'When the recorder started, as any date string PHP understands',
            )
            ->addOption(
                'recording-finished-at',
                null,
                InputOption::VALUE_REQUIRED,
                'When the recorder stopped, as any date string PHP understands',
            )
            ->addOption(
                'recording-duration',
                null,
                InputOption::VALUE_REQUIRED,
                'Length of the recording in seconds',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeline = VideoTimeline::fromJsonLines(
            $this->lines($this->requiredStringOption($input, 'timeline')),
            $this->recordingStart($input),
            $this->floatOption($input, 'recording-duration'),
        );

        $outDir = $this->requiredStringOption($input, 'out-dir');
        $filesystem = new Filesystem();
        $filesystem->dumpFile($outDir . '/' . self::VTT_FILE, $timeline->toWebVtt());
        $filesystem->dumpFile($outDir . '/' . self::FFMETADATA_FILE, $timeline->toFfMetadata());
        $filesystem->dumpFile($outDir . '/' . self::INDEX_FILE, $timeline->toIndex());

        $output->writeln(sprintf('Wrote %d chapters to %s', count($timeline->chapters()), $outDir));

        return self::SUCCESS;
    }

    /**
     * The recorder writes frames continuously until it exits, so working back
     * from the end of the recording pins the first frame exactly. The
     * container's start time is the fallback: it precedes the first frame by
     * however long the virtual display took to come up.
     */
    private function recordingStart(InputInterface $input): float
    {
        $duration = $this->floatOption($input, 'recording-duration');
        $finishedAt = $this->stringOption($input, 'recording-finished-at');
        if ($duration !== null && $finishedAt !== null) {
            return $this->epochSeconds($finishedAt) - $duration;
        }

        $startedAt = $this->stringOption($input, 'recording-started-at');
        if ($startedAt === null) {
            throw new InvalidArgumentException(
                'Pass --recording-started-at, or --recording-finished-at with --recording-duration',
            );
        }

        return $this->epochSeconds($startedAt);
    }

    /** @return iterable<string> */
    private function lines(string $file): iterable
    {
        $handle = new SplFileObject($file);
        $handle->setFlags(SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE | SplFileObject::SKIP_EMPTY);
        foreach ($handle as $line) {
            if (is_string($line)) {
                yield $line;
            }
        }
    }

    private function epochSeconds(string $date): float
    {
        return (float) (new DateTimeImmutable($date))->format('U.u');
    }

    private function requiredStringOption(InputInterface $input, string $name): string
    {
        $value = $this->stringOption($input, $name);
        if ($value === null) {
            throw new InvalidArgumentException(sprintf('Option --%s is required', $name));
        }

        return $value;
    }

    private function stringOption(InputInterface $input, string $name): ?string
    {
        $value = $input->getOption($name);
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Option --%s takes a single value', $name));
        }

        return $value;
    }

    private function floatOption(InputInterface $input, string $name): ?float
    {
        $value = $this->stringOption($input, $name);
        if ($value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(sprintf('Option --%s takes a number, got "%s"', $name, $value));
        }

        return (float) $value;
    }
}
