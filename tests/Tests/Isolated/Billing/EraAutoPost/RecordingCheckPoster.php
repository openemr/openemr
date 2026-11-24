<?php

/**
 * CheckPoster that posts nothing and remembers what it was asked, for the unattended posting tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\CheckPoster;
use OpenEMR\Billing\EraAutoPost\CheckRecord;
use OpenEMR\Billing\EraAutoPost\CheckStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\ClaimRecord;
use OpenEMR\Billing\EraAutoPost\RemittanceCheck;

final class RecordingCheckPoster implements CheckPoster
{
    /** @var list<array{string, string, int}> check number, payer, user */
    public array $posted = [];

    public function __construct(
        private readonly string $payer,
        private readonly bool $postedElsewhere,
        private readonly string $failOn,
    ) {
    }

    public function payerFor(RemittanceCheck $check): string
    {
        return $this->payer;
    }

    public function postedForAnotherPayer(RemittanceCheck $check, string $payerId): bool
    {
        return $this->postedElsewhere;
    }

    public function post(RemittanceCheck $check, string $payerId, int $postUser, \DateTimeImmutable $today): CheckRecord
    {
        if ($check->checkNumber === $this->failOn) {
            throw new \RuntimeException('database write failed');
        }
        $this->posted[] = [$check->checkNumber, $payerId, $postUser];
        $claims = array_map(
            static fn($claim): ClaimRecord => new ClaimRecord($claim, ClaimOutcome::Posted, [], 10, 20, 1, $claim->paymentAmount),
            $check->claims
        );
        return new CheckRecord($check, CheckStatus::Posted, null, $payerId, 300, $claims);
    }
}
