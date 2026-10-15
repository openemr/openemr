<?php

/**
 * Tests the MedEx cancelled appointment status setting and the SQL clause built from it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\MedEx;

use MedExApi\Base;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// API.php calls error_reporting(0) when loaded; don't let that leak into the rest of the suite.
$errorReportingLevel = error_reporting();
require_once __DIR__ . '/../../../../library/MedEx/API.php';
error_reporting($errorReportingLevel);

class CancelledApptStatusesTest extends TestCase
{
    private const GLOBAL_NAME = 'medex_cancelled_apptstatus';

    private const TEST_PID = 9914237;

    /** Custom status ids with characters an allowlist would reject. */
    private const DOTTED_STATUS = 'phpunit.r/s';

    private const QUOTED_STATUS = "phpunit'q";

    private bool $globalWasSet = false;

    private mixed $savedGlobal = null;

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $this->globalWasSet = $globals->has(self::GLOBAL_NAME);
        $this->savedGlobal = $globals->get(self::GLOBAL_NAME);

        $this->removeFixtures();
        QueryUtils::sqlInsert(
            "INSERT INTO list_options (list_id, option_id, title, seq, activity) VALUES ('apptstat', ?, ?, 990, 1), ('apptstat', ?, ?, 991, 0)",
            [self::DOTTED_STATUS, 'PHPUnit rescheduled', self::QUOTED_STATUS, 'PHPUnit retired']
        );
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();

        if ($this->globalWasSet) {
            OEGlobalsBag::getInstance()->set(self::GLOBAL_NAME, $this->savedGlobal);
        } else {
            $this->removeGlobal();
        }
    }

    #[Test]
    public function defaultsToCancelledStatusesWhenTheGlobalIsMissing(): void
    {
        $this->removeGlobal();

        $this->assertSame(['%', 'x'], $this->statuses());
    }

    #[Test]
    public function keepsKnownStatusesWhateverTheirCharactersAndDropsUnknownOnes(): void
    {
        $this->setGlobal(['%', 'x', self::DOTTED_STATUS, self::QUOTED_STATUS, 'phpunit-not-in-list']);

        // the quoted status is inactive; appointments can still carry a retired status
        $this->assertSame(['%', 'x', self::DOTTED_STATUS, self::QUOTED_STATUS], $this->statuses());
    }

    #[Test]
    public function clauseExcludesOnlyTheConfiguredStatuses(): void
    {
        foreach (['-', 'x', '%', self::DOTTED_STATUS, self::QUOTED_STATUS] as $status) {
            QueryUtils::sqlInsert(
                'INSERT INTO openemr_postcalendar_events (pc_pid, pc_apptstatus, pc_eventDate, pc_multiple) VALUES (?, ?, CURDATE(), 0)',
                [self::TEST_PID, $status]
            );
        }
        $this->setGlobal(['%', 'x', self::DOTTED_STATUS]);

        $remaining = QueryUtils::fetchTableColumn(
            'SELECT pc_apptstatus FROM openemr_postcalendar_events WHERE pc_pid = ?' . $this->clause() . ' ORDER BY pc_apptstatus',
            'pc_apptstatus',
            [self::TEST_PID]
        );

        $this->assertSame(['-', self::QUOTED_STATUS], $remaining);
    }

    #[Test]
    public function quotesStatusesSafelyInTheClause(): void
    {
        $this->setGlobal([self::QUOTED_STATUS]);

        $clause = $this->clause();

        $this->assertStringContainsString("'phpunit\\'q'", $clause);
        // the clause must still be valid SQL
        QueryUtils::fetchRecords('SELECT pc_eid FROM openemr_postcalendar_events WHERE 1 = 1' . $clause . ' LIMIT 1');
    }

    #[Test]
    public function emptySettingExcludesNothing(): void
    {
        OEGlobalsBag::getInstance()->set(self::GLOBAL_NAME, '');

        $this->assertSame([], $this->statuses());
        $this->assertSame('', $this->clause());
    }

    /**
     * @param list<string> $statuses
     */
    private function setGlobal(array $statuses): void
    {
        OEGlobalsBag::getInstance()->set(self::GLOBAL_NAME, implode(';', $statuses));
    }

    /**
     * OEGlobalsBag::set() writes through to $GLOBALS and has()/get() read it back,
     * but remove() only clears the bag's own copy, so clear both.
     */
    private function removeGlobal(): void
    {
        OEGlobalsBag::getInstance()->remove(self::GLOBAL_NAME);
        unset($GLOBALS[self::GLOBAL_NAME]);
    }

    /**
     * @return list<string>
     */
    private function statuses(): array
    {
        $statuses = (new \ReflectionMethod(Base::class, 'cancelledApptStatuses'))->invoke($this->newBase());
        $this->assertIsArray($statuses);
        return array_values(array_filter($statuses, is_string(...)));
    }

    private function clause(): string
    {
        $clause = (new \ReflectionMethod(Base::class, 'cancelledApptStatusClause'))->invoke($this->newBase());
        $this->assertIsString($clause);
        return $clause;
    }

    /**
     * A fresh instance per call, since Base caches the resolved statuses per object.
     */
    private function newBase(): Base
    {
        return new Base((object) ['curl' => null]);
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM list_options WHERE list_id = 'apptstat' AND option_id IN (?, ?)",
            [self::DOTTED_STATUS, self::QUOTED_STATUS]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM openemr_postcalendar_events WHERE pc_pid = ?',
            [self::TEST_PID]
        );
    }
}
