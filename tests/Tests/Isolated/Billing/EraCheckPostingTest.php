<?php

/**
 * Isolated tests for EraCheckPosting: check references and the per-request state
 * that keeps a re-posted ERA check from being posted twice.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\EraCheckPosting;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class EraCheckPostingTest extends TestCase
{
    #[Test]
    public function referencesCoverEraAndManualPostings(): void
    {
        $this->assertSame(['EFT12345', 'ePay - EFT12345'], EraCheckPosting::references('EFT12345'));
    }

    #[Test]
    public function aReusedSessionIsReportedOnce(): void
    {
        $run = new EraCheckPosting('9902');
        $this->assertNull($run->takeReusedSession('EFT12345'));

        $run->reuseSession('EFT12345', 41);
        $this->assertSame(41, $run->takeReusedSession('EFT12345'));
        $this->assertNull($run->takeReusedSession('EFT12345'));
    }

    #[Test]
    public function aPendedLineStaysPendingUntilALaterClaimPaymentAnswersIt(): void
    {
        $run = new EraCheckPosting('9902');
        $this->assertSame(0, $run->pendedLineCount(5, 501, 1));

        $run->recordClaimPayment(5, 501, 1, ['92015'], ['92014']);
        $this->assertSame(1, $run->pendedLineCount(5, 501, 1));

        $run->recordClaimPayment(5, 501, 1, [], ['92014']);
        $this->assertSame(1, $run->pendedLineCount(5, 501, 1), 'Another code does not settle it');

        $run->recordClaimPayment(5, 501, 1, [], ['92015']);
        $this->assertSame(0, $run->pendedLineCount(5, 501, 1));
    }

    #[Test]
    public function linesWithTheSameCodeAreCountedNotMerged(): void
    {
        $run = new EraCheckPosting('9902');
        // Two 92014 lines on one CLP: one pended, one paid. The paid one doesn't settle its twin.
        $run->recordClaimPayment(5, 501, 1, ['92014'], ['92014']);
        $this->assertSame(1, $run->pendedLineCount(5, 501, 1));

        // Two pended 92014 lines; a later CLP answers one of them.
        $run->recordClaimPayment(5, 501, 1, ['92014'], []);
        $this->assertSame(2, $run->pendedLineCount(5, 501, 1));
        $run->recordClaimPayment(5, 501, 1, [], ['92014']);
        $this->assertSame(1, $run->pendedLineCount(5, 501, 1));
    }

    #[Test]
    public function pendsAreKeptPerClaimAndLevel(): void
    {
        $run = new EraCheckPosting('9902');
        $run->recordClaimPayment(5, 501, 2, ['92015'], []);

        $this->assertSame(0, $run->pendedLineCount(5, 501, 1), 'Another level');
        $this->assertSame(0, $run->pendedLineCount(5, 502, 2), 'Another encounter');
        $this->assertSame(1, $run->pendedLineCount(5, 501, 2));
    }

    #[Test]
    public function oneInstancePerRequest(): void
    {
        $request = new Request(['InsId' => '9902']);
        $run = EraCheckPosting::forRequest($request);
        $run->reuseSession('EFT1', 7);

        $this->assertSame($run, EraCheckPosting::forRequest($request));
        $this->assertNotSame($run, EraCheckPosting::forRequest(new Request(['InsId' => '9902'])));
        $this->assertSame(7, EraCheckPosting::forRequest($request)->takeReusedSession('EFT1'));
    }
}
