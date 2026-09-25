<?php

/**
 * Tests how MedEx Events::process() reports the replies to its loadAppts batches.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\MedEx;

use MedExApi\Events;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/MedEx/API.php';

class MedExProcessBatchesTest extends TestCase
{
    /** process() flushes a batch once it holds more than 100 appointments. */
    private const BATCH_SIZE = 101;

    #[Test]
    public function reportsAnEarlierBatchErrorEvenIfTheLastBatchSucceeds(): void
    {
        $curl = $this->scriptedCurl([['error' => 'batch 1 rejected'], ['success' => 'ok']]);
        $events = $this->events($curl);

        $this->assertFalse($this->process($events, self::BATCH_SIZE + 1));
        $this->assertSame('batch 1 rejected', $events->lastError);
        $this->assertSame(2, $curl->requests);
    }

    #[Test]
    public function checksTheReplyWhenTheCountEndsOnABatchBoundary(): void
    {
        $curl = $this->scriptedCurl([['error' => 'only batch rejected']]);
        $events = $this->events($curl);

        $this->assertFalse($this->process($events, self::BATCH_SIZE));
        $this->assertSame('only batch rejected', $events->lastError);
        // no empty trailing request after a full batch
        $this->assertSame(1, $curl->requests);
    }

    #[Test]
    public function returnsTheResponseWhenEveryBatchSucceeds(): void
    {
        $curl = $this->scriptedCurl([['success' => 'first'], ['success' => 'last']]);
        $events = $this->events($curl);

        $this->assertSame(['success' => 'last'], $this->process($events, self::BATCH_SIZE + 1));
        $this->assertSame('', $events->lastError);
    }

    private function process(Events $events, int $count): mixed
    {
        $appts = [];
        for ($i = 1; $i <= $count; $i++) {
            // no medex_outgoing rows match these, so process()'s bookkeeping UPDATE is a no-op
            $appts[] = ['reply' => 'SENT', 'extra' => 'QUEUED', 'pc_eid' => 'phpunit' . $i, 'C_UID' => 0, 'M_type' => 'SMS'];
        }
        return (new \ReflectionMethod(Events::class, 'process'))->invoke($events, 'phpunit-token', $appts);
    }

    private function events(object $curl): Events
    {
        return new Events(new class ($curl) {
            public function __construct(public object $curl)
            {
            }

            public function getUrl(string $path): string
            {
                return 'https://medex.invalid/' . $path;
            }
        });
    }

    /**
     * A stand-in for CurlRequest that returns the given replies in order.
     *
     * @param list<array<string, string>> $responses
     * @return object{requests: int}
     */
    private function scriptedCurl(array $responses): object
    {
        return new class ($responses) {
            public int $requests = 0;

            /**
             * @param list<array<string, string>> $responses
             */
            public function __construct(private array $responses)
            {
            }

            public function setUrl(string $url): void
            {
            }

            public function setData(mixed $data): void
            {
            }

            public function makeRequest(): void
            {
                $this->requests++;
            }

            /**
             * @return array<string, string>
             */
            public function getResponse(): array
            {
                return array_shift($this->responses) ?? [];
            }
        };
    }
}
