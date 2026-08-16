<?php

/**
 * Isolated test: the timeline recorder writes for E2e tests and nothing else.
 *
 * The recorder is registered for every PHPUnit run in the project, so the
 * suite gate is what keeps a unit-test run from writing a stray timeline
 * file — and what keeps the E2e recording annotated when it matters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Ci;

use OpenEMR\PHPUnit\Timeline\TimelineEventType;
use OpenEMR\PHPUnit\Timeline\TimelineRecorder;
use OpenEMR\PHPUnit\Timeline\VideoTimeline;
use OpenEMR\Tests\E2e\AaLoginTest;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use Symfony\Component\Filesystem\Filesystem;

class TimelineRecorderIsolatedTest extends TestCase
{
    private string $outputFile;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $this->outputFile = $filesystem->tempnam(sys_get_temp_dir(), 'e2e-timeline-', '.jsonl');
        // The recorder creates the file on first write; a pre-existing empty
        // file would hide a failure to write anything at all.
        $filesystem->remove($this->outputFile);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->outputFile);
    }

    public function testAnE2eTestIsRecordedAsASpan(): void
    {
        $recorder = new TimelineRecorder($this->outputFile);
        $test = $this->testMethod(AaLoginTest::class, 'testGoToOpenemrLoginPage');

        $before = microtime(true);
        $recorder->record($test, TimelineEventType::Started);
        $recorder->record($test, TimelineEventType::Finished);
        $after = microtime(true);

        $chapters = VideoTimeline::fromJsonLines($this->recordedLines(), $before)->chapters();
        $this->assertCount(1, $chapters);
        $this->assertSame('AaLoginTest::testGoToOpenemrLoginPage', $chapters[0]->label);
        $this->assertLessThanOrEqual($after - $before, $chapters[0]->endOffset);
    }

    public function testATestFromAnotherSuiteIsNotRecorded(): void
    {
        $recorder = new TimelineRecorder($this->outputFile);

        $recorder->record(
            // This very test: isolated, not E2e, nothing to annotate.
            $this->testMethod(self::class, __FUNCTION__),
            TimelineEventType::Started,
        );

        $this->assertFileDoesNotExist($this->outputFile);
    }

    public function testAPreviousRunsTimelineIsReplacedRatherThanAppendedTo(): void
    {
        (new Filesystem())->dumpFile($this->outputFile, 'stale line from the last run' . PHP_EOL);
        $recorder = new TimelineRecorder($this->outputFile);

        $recorder->record(
            $this->testMethod(AaLoginTest::class, 'testGoToOpenemrLoginPage'),
            TimelineEventType::Started,
        );

        $this->assertCount(1, $this->recordedLines());
    }

    /** @return list<string> */
    private function recordedLines(): array
    {
        $contents = (new Filesystem())->readFile($this->outputFile);

        return array_values(array_filter(explode(PHP_EOL, $contents), static fn(string $line): bool => $line !== ''));
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $methodName
     */
    private function testMethod(string $className, string $methodName): TestMethod
    {
        return new TestMethod(
            $className,
            $methodName,
            __FILE__,
            __LINE__,
            new TestDox($className, $methodName, $methodName),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }
}
