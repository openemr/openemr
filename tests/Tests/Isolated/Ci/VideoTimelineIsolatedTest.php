<?php

/**
 * Isolated test: the E2e test timeline becomes correct video navigation.
 *
 * The timeline is recorded in wall-clock time and the video knows only
 * offsets from its own first frame, so every assertion here is really about
 * one thing: that a test's chapter lands where that test actually appears in
 * the recording.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Ci;

use JsonException;
use OpenEMR\PHPUnit\Timeline\TestOutcome;
use OpenEMR\PHPUnit\Timeline\VideoTimeline;
use PHPUnit\Framework\TestCase;

class VideoTimelineIsolatedTest extends TestCase
{
    private const RECORDING_START = 1_760_000_000.0;

    public function testChaptersAreOffsetFromTheStartOfTheRecording(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(10.5, 'started', 'AaLoginTest::testGoToOpenemrLoginPage'),
                $this->event(22.25, 'finished', 'AaLoginTest::testGoToOpenemrLoginPage'),
            ],
            self::RECORDING_START,
        );

        $chapters = $timeline->chapters();
        $this->assertCount(1, $chapters);
        $this->assertSame('AaLoginTest::testGoToOpenemrLoginPage', $chapters[0]->label);
        $this->assertSame(10.5, $chapters[0]->startOffset);
        $this->assertSame(22.25, $chapters[0]->endOffset);
        $this->assertSame(TestOutcome::Passed, $chapters[0]->outcome);
    }

    public function testChaptersAreOrderedByWhenTheyAppearInTheRecording(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(30.0, 'started', 'BbSecondTest::testTwo'),
                $this->event(40.0, 'finished', 'BbSecondTest::testTwo'),
                $this->event(10.0, 'started', 'AaFirstTest::testOne'),
                $this->event(20.0, 'finished', 'AaFirstTest::testOne'),
            ],
            self::RECORDING_START,
        );

        $labels = array_map(static fn($chapter): string => $chapter->label, $timeline->chapters());
        $this->assertSame(['AaFirstTest::testOne', 'BbSecondTest::testTwo'], $labels);
    }

    public function testAFailedTestIsMarkedSoItCanBeFoundByScrubbing(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(1.0, 'started', 'HhMainMenuLinksTest::testMainMenuLink#Calendar'),
                $this->event(9.0, 'failed', 'HhMainMenuLinksTest::testMainMenuLink#Calendar'),
                $this->event(9.5, 'finished', 'HhMainMenuLinksTest::testMainMenuLink#Calendar'),
            ],
            self::RECORDING_START,
        );

        $chapters = $timeline->chapters();
        $this->assertSame(TestOutcome::Failed, $chapters[0]->outcome);
        $this->assertSame('FAIL HhMainMenuLinksTest::testMainMenuLink#Calendar', $chapters[0]->title());
    }

    /**
     * A crash mid-test leaves no end event. That test owns the rest of the
     * recording, which is exactly the footage someone will be looking for.
     */
    public function testATestThatNeverEndedRunsToTheEndOfTheRecording(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [$this->event(12.0, 'started', 'CcCreatePatientTest::testAddPatient')],
            self::RECORDING_START,
            90.0,
        );

        $this->assertSame(90.0, $timeline->chapters()[0]->endOffset);
    }

    public function testChaptersStayInsideTheRecording(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                // The suite starts before the recorder and outlives it.
                $this->event(-5.0, 'started', 'AaLoginTest::testEarly'),
                $this->event(120.0, 'finished', 'AaLoginTest::testEarly'),
            ],
            self::RECORDING_START,
            60.0,
        );

        $chapters = $timeline->chapters();
        $this->assertSame(0.0, $chapters[0]->startOffset);
        $this->assertSame(60.0, $chapters[0]->endOffset);
    }

    public function testWebVttNamesTheRunningTest(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(3661.5, 'started', 'AaLoginTest::testLoginUnauthorized'),
                $this->event(3663.0, 'finished', 'AaLoginTest::testLoginUnauthorized'),
            ],
            self::RECORDING_START,
        );

        $expected = implode(PHP_EOL, [
            'WEBVTT',
            '',
            '1',
            '01:01:01.500 --> 01:01:03.000',
            'PASS AaLoginTest::testLoginUnauthorized',
            '',
        ]);
        $this->assertSame($expected, $timeline->toWebVtt());
    }

    public function testFfMetadataDescribesTheChapterInMilliseconds(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(2.0, 'started', 'AaLoginTest::testGoToOpenemrLoginPage'),
                $this->event(4.5, 'finished', 'AaLoginTest::testGoToOpenemrLoginPage'),
            ],
            self::RECORDING_START,
        );

        $expected = implode(PHP_EOL, [
            ';FFMETADATA1',
            '',
            '[CHAPTER]',
            'TIMEBASE=1/1000',
            'START=2000',
            'END=4500',
            'title=PASS AaLoginTest::testGoToOpenemrLoginPage',
            '',
        ]);
        $this->assertSame($expected, $timeline->toFfMetadata());
    }

    public function testTheIndexReadsAsAListOfTimestamps(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                $this->event(65.0, 'started', 'AaLoginTest::testGoToOpenemrLoginPage'),
                $this->event(70.0, 'finished', 'AaLoginTest::testGoToOpenemrLoginPage'),
            ],
            self::RECORDING_START,
        );

        $this->assertSame(
            '00:01:05.000  PASS AaLoginTest::testGoToOpenemrLoginPage' . PHP_EOL,
            $timeline->toIndex(),
        );
    }

    public function testBlankAndUnknownRecordsAreIgnored(): void
    {
        $timeline = VideoTimeline::fromJsonLines(
            [
                '',
                '   ',
                $this->event(1.0, 'invented-by-a-later-phpunit', 'AaLoginTest::testGoToOpenemrLoginPage'),
            ],
            self::RECORDING_START,
        );

        $this->assertSame([], $timeline->chapters());
    }

    public function testAMalformedTimelineIsReportedRatherThanSilentlyDropped(): void
    {
        $this->expectException(JsonException::class);

        VideoTimeline::fromJsonLines(['{"event": "started"}'], self::RECORDING_START);
    }

    /**
     * @param float $offset seconds after the start of the recording
     */
    private function event(float $offset, string $event, string $test): string
    {
        return json_encode(
            [
                'time' => self::RECORDING_START + $offset,
                'event' => $event,
                'id' => 'OpenEMR\\Tests\\E2e\\' . $test,
                'class' => 'OpenEMR\\Tests\\E2e\\' . explode('::', $test)[0],
                'method' => explode('::', $test)[1],
            ],
            JSON_THROW_ON_ERROR,
        );
    }
}
