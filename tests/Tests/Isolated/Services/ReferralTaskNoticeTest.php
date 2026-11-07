<?php

/**
 * The referrer's notice when a referral's Task is accepted, rejected, completed, failed or
 * cancelled: ReferralTaskNotice (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\ReferralTaskNotice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralTaskNoticeTest extends TestCase
{
    private bool $translationBefore;

    protected function setUp(): void
    {
        // xl() reaches for the translation tables unless this is set
        $this->translationBefore = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('disable_translation', $this->translationBefore);
    }

    /**
     * @return array<string, array{string, ?string, ?string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function notices(): array
    {
        // Task status, Referral Date, the recipient's reason => the notice
        return [
            'accepted' => ['accepted', '2026-10-10', null, 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked accepted.'],
            'rejected' => ['rejected', '2026-10-10', null, 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked rejected.'],
            'completed' => ['completed', '2026-10-10', null, 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked completed.'],
            'failed' => ['failed', '2026-10-10', null, 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked failed.'],
            'cancelled' => ['cancelled', '2026-10-10', null, 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked cancelled.'],
            'accepted, no Referral Date' => ['accepted', null, null, 'The Task for the referral to Zq Pantry was marked accepted.'],
            'rejected, no Referral Date' => ['rejected', null, null, 'The Task for the referral to Zq Pantry was marked rejected.'],
            'completed, no Referral Date' => ['completed', null, null, 'The Task for the referral to Zq Pantry was marked completed.'],
            'failed, no Referral Date' => ['failed', null, null, 'The Task for the referral to Zq Pantry was marked failed.'],
            'cancelled, no Referral Date' => ['cancelled', null, null, 'The Task for the referral to Zq Pantry was marked cancelled.'],
            'rejected, with a reason' => ['rejected', '2026-10-10', 'Outside our service area', 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked rejected: Outside our service area'],
            'rejected, with a reason, no Referral Date' => ['rejected', null, 'Outside our service area', 'The Task for the referral to Zq Pantry was marked rejected: Outside our service area'],
            'failed, with a reason' => ['failed', '2026-10-10', 'The client could not be reached', 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked failed: The client could not be reached'],
            'failed, with a reason, no Referral Date' => ['failed', null, 'The client could not be reached', 'The Task for the referral to Zq Pantry was marked failed: The client could not be reached'],
            'cancelled, with a reason' => ['cancelled', '2026-10-10', 'Client moved away', 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked cancelled: Client moved away'],
            'cancelled, with a reason, no Referral Date' => ['cancelled', null, 'Client moved away', 'The Task for the referral to Zq Pantry was marked cancelled: Client moved away'],
            // Only a rejection, a failure or a cancellation carries the reason.
            'accepted, with a reason' => ['accepted', '2026-10-10', 'Seen at intake', 'The Task for the referral to Zq Pantry made on 2026-10-10 was marked accepted.'],
            'completed, with a reason, no Referral Date' => ['completed', null, 'Seen at intake', 'The Task for the referral to Zq Pantry was marked completed.'],
        ];
    }

    #[DataProvider('notices')]
    public function testEachNoticeIsOneSentence(string $status, ?string $referralDate, ?string $reason, string $expected): void
    {
        $this->assertSame($expected, ReferralTaskNotice::from($status)->text('Zq Pantry', $referralDate, $reason));
    }
}
