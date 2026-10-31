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
    /**
     * The review screen's alcohol fields when the document has no alcohol section: an empty note field, no status
     * radio checked, and fixDate('') is false. The tobacco parts are the note (the screen has no tobacco note
     * field), the status radio (quittobacco), the date and the Smoking Status list's option id (3 is Former
     * smoker; the select always posts one).
     */
    private const NO_ALCOHOL = ['', null, false];

    public function testASmokingStatusAloneLeavesAlcoholOut(): void
    {
        $this->assertSame(
            ['tobacco' => '|quittobacco|2024-01-21|3'],
            CdaSocialHistoryWriter::reviewedChanges([null, 'quittobacco', '2024-01-21', '3'], self::NO_ALCOHOL)
        );
    }

    public function testAlcoholAloneLeavesTobaccoOut(): void
    {
        $this->assertSame(
            ['alcohol' => '|currentalcohol|2020-01-01'],
            CdaSocialHistoryWriter::reviewedChanges([null, null, false, null], ['', 'currentalcohol', '2020-01-01'])
        );
    }

    public function testBothAreJoinedAsTheReviewPathStoresThem(): void
    {
        $this->assertSame(
            ['tobacco' => '|currenttobacco||1', 'alcohol' => '|quitalcohol|'],
            CdaSocialHistoryWriter::reviewedChanges([null, 'currenttobacco', false, '1'], ['', 'quitalcohol', false])
        );
    }

    public function testNothingCarriedIsNoChange(): void
    {
        $this->assertSame([], CdaSocialHistoryWriter::reviewedChanges(['', '', false, ''], self::NO_ALCOHOL));
        $this->assertSame([], CdaSocialHistoryWriter::reviewedChanges([null, null, false, null], [null, null, false]));
    }
}
