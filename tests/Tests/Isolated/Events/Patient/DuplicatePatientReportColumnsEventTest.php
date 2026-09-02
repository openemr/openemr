<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events\Patient;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Patient\DuplicatePatientReportColumnsEvent;
use OpenEMR\Services\Patient\DuplicatePatientColumn;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DuplicatePatientReportColumnsEventTest extends TestCase
{
    protected function setUp(): void
    {
        // DuplicatePatientColumn::defaults() translates its labels, and xl() reaches for the
        // translation tables unless this is set. Declared here rather than inherited from whichever
        // class happened to run first.
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    private static function column(string $key): DuplicatePatientColumn
    {
        return DuplicatePatientColumn::forField($key, ucfirst($key));
    }

    /**
     * @return list<string>
     */
    private static function keys(DuplicatePatientReportColumnsEvent $event): array
    {
        return array_map(
            static fn(DuplicatePatientColumn $c): string => $c->key,
            $event->getColumns()
        );
    }

    private static function eventWith(string ...$keys): DuplicatePatientReportColumnsEvent
    {
        return new DuplicatePatientReportColumnsEvent(
            array_values(array_map(self::column(...), $keys))
        );
    }

    #[Test]
    public function startsEmptyByDefault(): void
    {
        $this->assertSame([], (new DuplicatePatientReportColumnsEvent())->getColumns());
    }

    #[Test]
    public function addAppends(): void
    {
        $event = self::eventWith('a', 'b');
        $event->add(self::column('c'));

        $this->assertSame(['a', 'b', 'c'], self::keys($event));
    }

    /**
     * Adding a key that is already present replaces it rather than showing the column twice.
     */
    #[Test]
    public function addReplacesAnExistingKey(): void
    {
        $event = self::eventWith('a', 'b', 'c');
        $event->add(self::column('b'));

        $this->assertSame(['a', 'c', 'b'], self::keys($event));
    }

    #[Test]
    public function insertAfterPlacesTheColumnDirectlyAfterTheNamedOne(): void
    {
        $event = self::eventWith('a', 'b', 'c');
        $event->insertAfter('a', self::column('x'));

        $this->assertSame(['a', 'x', 'b', 'c'], self::keys($event));
    }

    #[Test]
    public function insertAfterAnUnknownKeyAppends(): void
    {
        $event = self::eventWith('a', 'b');
        $event->insertAfter('nope', self::column('x'));

        $this->assertSame(['a', 'b', 'x'], self::keys($event));
    }

    /**
     * Moving an existing column must not leave a copy behind at its old position.
     */
    #[Test]
    public function insertAfterMovesAColumnThatIsAlreadyPresent(): void
    {
        $event = self::eventWith('a', 'b', 'c');
        $event->insertAfter('c', self::column('a'));

        $this->assertSame(['b', 'c', 'a'], self::keys($event));
    }

    #[Test]
    public function removeDropsTheColumnAndReindexes(): void
    {
        $event = self::eventWith('a', 'b', 'c');
        $event->remove('b');

        $this->assertSame(['a', 'c'], self::keys($event));
        $this->assertSame([0, 1], array_keys($event->getColumns()), 'the list must stay a list');
    }

    #[Test]
    public function removeIgnoresAnUnknownKey(): void
    {
        $event = self::eventWith('a');
        $event->remove('nope');

        $this->assertSame(['a'], self::keys($event));
    }

    #[Test]
    public function hasReportsMembership(): void
    {
        $event = self::eventWith('a');

        $this->assertTrue($event->has('a'));
        $this->assertFalse($event->has('b'));
    }

    #[Test]
    public function setColumnsReplacesTheWholeList(): void
    {
        $event = self::eventWith('a', 'b');
        $event->setColumns([self::column('z')]);

        $this->assertSame(['z'], self::keys($event));
    }

    /**
     * The shape a module reaching for a different report actually uses.
     */
    #[Test]
    public function supportsSwappingCoreColumnsForSiteSpecificOnes(): void
    {
        $event = new DuplicatePatientReportColumnsEvent(DuplicatePatientColumn::defaults());
        $event->remove('scope');
        $event->remove('sex');
        $event->insertAfter('DOB', DuplicatePatientColumn::forField('ss', 'SSN'));
        $event->add(DuplicatePatientColumn::forField('home_facility', 'Home Facility'));

        $this->assertSame(
            ['score', 'pid', 'pubpid', 'name', 'DOB', 'ss', 'email', 'phones', 'regdate', 'street', 'home_facility'],
            self::keys($event)
        );
    }
}
