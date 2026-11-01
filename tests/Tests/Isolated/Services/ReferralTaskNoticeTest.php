<?php

/**
 * The referrer's notice when a referral's Task is accepted, rejected or completed:
 * ReferralTaskNotice (#14710).
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
     * @return array<string, array{string, ?string, string}>
     */
    public static function notices(): array
    {
        // Task status, Referral Date => the notice
        return [
            'accepted' => ['accepted', '2026-10-10', 'Zq Pantry accepted the referral made on 2026-10-10.'],
            'rejected' => ['rejected', '2026-10-10', 'Zq Pantry rejected the referral made on 2026-10-10.'],
            'completed' => ['completed', '2026-10-10', 'Zq Pantry completed the referral made on 2026-10-10.'],
            'accepted, no Referral Date' => ['accepted', null, 'Zq Pantry accepted the referral.'],
            'rejected, no Referral Date' => ['rejected', null, 'Zq Pantry rejected the referral.'],
            'completed, no Referral Date' => ['completed', null, 'Zq Pantry completed the referral.'],
        ];
    }

    #[DataProvider('notices')]
    public function testEachNoticeIsOneSentence(string $status, ?string $referralDate, string $expected): void
    {
        $this->assertSame($expected, ReferralTaskNotice::from($status)->text('Zq Pantry', $referralDate));
    }
}
