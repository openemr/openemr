<?php

/**
 * Turns a recorded E2e test timeline into navigation sidecars for the video.
 *
 * The timeline holds wall-clock timestamps; the video knows only offsets from
 * when its recorder started. Given that one reference point, every test
 * becomes a span in the recording, and the spans render as WebVTT subtitles
 * (so a player names the running test), ffmpeg chapter metadata (so a player
 * offers chapter jumps), and a plain-text index (so a reader can find the
 * failure without a player at all).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPUnit\Timeline;

use JsonException;

final readonly class VideoTimeline
{
    /**
     * Chapter titles are compared against the recording, which is captured at
     * a fixed frame rate; milliseconds are as fine as any of this gets.
     */
    private const MILLISECONDS_PER_SECOND = 1000;

    /** @param list<VideoChapter> $chapters */
    private function __construct(private array $chapters)
    {
    }

    /** @return list<VideoChapter> */
    public function chapters(): array
    {
        return $this->chapters;
    }

    /**
     * @param iterable<string> $jsonLines     the recorded timeline, one JSON object per line
     * @param float            $recordingStart wall-clock epoch seconds at which the recording began
     * @param float|null       $recordingDuration
     *        length of the recording in seconds, used to close out a test that
     *        never reported an end (the suite died mid-test) and to keep every
     *        chapter inside the video
     * @throws JsonException on a malformed timeline
     */
    public static function fromJsonLines(
        iterable $jsonLines,
        float $recordingStart,
        ?float $recordingDuration = null,
    ): self {
        /** @var array<string, array{start: float, end: float|null, outcome: TestOutcome}> $spans */
        $spans = [];
        $lastEventTime = $recordingStart;
        foreach ($jsonLines as $line) {
            $record = self::decode($line);
            if ($record === null) {
                continue;
            }
            [$id, $time, $event] = $record;
            $lastEventTime = max($lastEventTime, $time);
            $span = $spans[$id] ?? ['start' => $time, 'end' => null, 'outcome' => TestOutcome::Passed];
            $outcome = TestOutcome::fromEventType($event);
            $spans[$id] = [
                'start' => $event === TimelineEventType::Started ? $time : $span['start'],
                'end' => $event === TimelineEventType::Finished ? $time : $span['end'],
                'outcome' => $outcome ?? $span['outcome'],
            ];
        }

        // A test with no end event died with the suite, so it owns the
        // recording through to whichever came last: the final event or, when
        // the recording's length is known, the final frame.
        $end = $recordingDuration ?? $lastEventTime - $recordingStart;
        $chapters = [];
        foreach ($spans as $id => $span) {
            $startOffset = self::clamp($span['start'] - $recordingStart, $end);
            $endOffset = $span['end'] === null ? $end : self::clamp($span['end'] - $recordingStart, $end);
            $chapters[] = new VideoChapter(
                self::label($id),
                $startOffset,
                max($startOffset, $endOffset),
                $span['outcome'],
            );
        }
        usort($chapters, static fn(VideoChapter $a, VideoChapter $b): int => $a->startOffset <=> $b->startOffset);

        return new self($chapters);
    }

    public function toWebVtt(): string
    {
        $cues = ['WEBVTT', ''];
        foreach ($this->chapters as $index => $chapter) {
            $cues[] = (string) ($index + 1);
            $cues[] = self::formatTimestamp($chapter->startOffset) . ' --> ' . self::formatTimestamp($chapter->endOffset);
            $cues[] = $chapter->title();
            $cues[] = '';
        }

        return implode(PHP_EOL, $cues);
    }

    /**
     * ffmetadata, as consumed by `ffmpeg -i video.mp4 -i chapters.ffmetadata
     * -map_metadata 1 -codec copy chaptered.mp4`.
     */
    public function toFfMetadata(): string
    {
        $lines = [';FFMETADATA1', ''];
        foreach ($this->chapters as $chapter) {
            $lines[] = '[CHAPTER]';
            $lines[] = 'TIMEBASE=1/' . self::MILLISECONDS_PER_SECOND;
            $lines[] = 'START=' . self::toMilliseconds($chapter->startOffset);
            $lines[] = 'END=' . self::toMilliseconds($chapter->endOffset);
            // ffmetadata escapes =, ;, # and \ with a backslash.
            $lines[] = 'title=' . addcslashes($chapter->title(), '=;#\\');
            $lines[] = '';
        }

        return implode(PHP_EOL, $lines);
    }

    public function toIndex(): string
    {
        $lines = [];
        foreach ($this->chapters as $chapter) {
            $lines[] = self::formatTimestamp($chapter->startOffset) . '  ' . $chapter->title();
        }
        $lines[] = '';

        return implode(PHP_EOL, $lines);
    }

    /**
     * @return array{string, float, TimelineEventType}|null null for a blank
     *         line or an event this build of the timeline does not know
     * @throws JsonException
     */
    private static function decode(string $line): ?array
    {
        $line = trim($line);
        if ($line === '') {
            return null;
        }
        $record = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($record)) {
            throw new JsonException('Timeline record is not an object: ' . $line);
        }
        $id = $record['id'] ?? null;
        $time = $record['time'] ?? null;
        $event = $record['event'] ?? null;
        if (!is_string($id) || !is_string($event) || (!is_float($time) && !is_int($time))) {
            throw new JsonException('Timeline record is missing id, time or event: ' . $line);
        }
        $eventType = TimelineEventType::tryFrom($event);
        if ($eventType === null) {
            return null;
        }

        return [$id, (float) $time, $eventType];
    }

    /**
     * Test ids carry the full namespace; the recording only has room for what
     * distinguishes one test from the next.
     */
    private static function label(string $id): string
    {
        if (str_starts_with($id, TimelineRecorder::E2E_NAMESPACE_PREFIX)) {
            return substr($id, strlen(TimelineRecorder::E2E_NAMESPACE_PREFIX));
        }

        return $id;
    }

    private static function clamp(float $offset, float $end): float
    {
        return min(max($offset, 0.0), $end);
    }

    private static function toMilliseconds(float $offset): int
    {
        return (int) round($offset * self::MILLISECONDS_PER_SECOND);
    }

    private static function formatTimestamp(float $offset): string
    {
        $milliseconds = self::toMilliseconds($offset);

        return sprintf(
            '%02d:%02d:%02d.%03d',
            intdiv($milliseconds, 3600 * self::MILLISECONDS_PER_SECOND),
            intdiv($milliseconds, 60 * self::MILLISECONDS_PER_SECOND) % 60,
            intdiv($milliseconds, self::MILLISECONDS_PER_SECOND) % 60,
            $milliseconds % self::MILLISECONDS_PER_SECOND,
        );
    }
}
