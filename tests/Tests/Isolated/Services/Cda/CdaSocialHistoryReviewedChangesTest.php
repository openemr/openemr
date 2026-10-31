<?php

/**
 * Isolated tests for CdaSocialHistoryWriter::reviewedChanges(), what a document approved on the review screen
 * carries of a patient's social history.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\CdaSocialHistoryWriter;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class CdaSocialHistoryReviewedChangesTest extends TestCase
{
    /** The review screen's alcohol fields when the document has no alcohol section; fixDate('') is false. */
    private const NO_ALCOHOL = ['', '', false];

    public function testASmokingStatusAloneLeavesAlcoholOut(): void
    {
        $this->assertSame(
            ['tobacco' => 'imported|1|2024-01-21|449868002'],
            CdaSocialHistoryWriter::reviewedChanges(['imported', '1', '2024-01-21', '449868002'], self::NO_ALCOHOL)
        );
    }

    public function testAlcoholAloneLeavesTobaccoOut(): void
    {
        $this->assertSame(
            ['alcohol' => 'social|currentalcohol|2020-01-01'],
            CdaSocialHistoryWriter::reviewedChanges(['', '', false, ''], ['social', 'currentalcohol', '2020-01-01'])
        );
    }

    public function testBothAreJoinedAsTheReviewPathStoresThem(): void
    {
        $this->assertSame(
            ['tobacco' => '|1||449868002', 'alcohol' => 'social||'],
            CdaSocialHistoryWriter::reviewedChanges(['', '1', false, '449868002'], ['social', '', false])
        );
    }

    public function testNothingCarriedIsNoChange(): void
    {
        $this->assertSame([], CdaSocialHistoryWriter::reviewedChanges(['', '', false, ''], self::NO_ALCOHOL));
        $this->assertSame([], CdaSocialHistoryWriter::reviewedChanges([null, null, false, null], [null, null, false]));
    }
}
