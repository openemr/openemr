<?php

/**
 * A facility edit commits the row and its users together, or saves nothing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Facility\FacilityUpdatedEvent;
use OpenEMR\Services\FacilityService;
use PHPUnit\Framework\TestCase;

class FacilitySaveTransactionTest extends TestCase
{
    private int $facilityId = 0;

    /**
     * Insert the one facility row these cases edit.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->facilityId = QueryUtils::sqlInsert(
            'INSERT INTO facility (name, street, city, state, postal_code, country_code, federal_ein,'
            . ' facility_npi, tax_id_type, color, oid, pos_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                'ZZ save transaction',
                '1 Main',
                'Testville',
                'TX',
                '564701234',
                'US',
                '12-3456789',
                '1234567893',
                'EI',
                '#000000',
                '',
                11,
            ]
        );
    }

    /**
     * Remove only the row this test inserted.
     */
    protected function tearDown(): void
    {
        if ($this->facilityId > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM facility WHERE id = ?',
                [$this->facilityId]
            );
        }
        parent::tearDown();
    }

    /**
     * A missing id is not saved, and listeners are not told it changed.
     */
    public function testMissingIdSavesNothing(): void
    {
        $heard = false;
        $listener = function () use (&$heard): void {
            $heard = true;
        };
        $dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
        $dispatcher->addListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        try {
            $saved = (new FacilityService())->updateFacilityAndUsers([
                'id' => '999999999',
                'name' => 'ZZ should not save',
            ]);
        } finally {
            $dispatcher->removeListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        }

        $this->assertFalse($saved);
        $this->assertFalse($heard);
        $this->assertSame('ZZ save transaction', $this->storedName());
    }

    /**
     * A failed user-name update leaves the facility row as it was.
     */
    public function testFailedUserUpdateRollsTheFacilityBack(): void
    {
        $heard = false;
        $listener = function () use (&$heard): void {
            $heard = true;
        };
        $dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
        $dispatcher->addListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        try {
            $saved = (new FacilityUsersUpdateFails())->updateFacilityAndUsers([
                'id' => (string) $this->facilityId,
                'name' => 'ZZ should roll back',
            ]);
        } finally {
            $dispatcher->removeListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        }

        $this->assertFalse($saved);
        $this->assertFalse($heard);
        $this->assertSame('ZZ save transaction', $this->storedName());
    }

    /**
     * A finished edit stores the new name and then tells listeners.
     */
    public function testSavedEditTellsListenersAfterTheNameIsStored(): void
    {
        $seen = null;
        $listener = function (FacilityUpdatedEvent $event) use (&$seen): void {
            $data = $event->getNewFacilityData();
            $seen = is_array($data) ? ($data['name'] ?? null) : null;
        };
        $dispatcher = OEGlobalsBag::getInstance()->getKernel()->getEventDispatcher();
        $dispatcher->addListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        try {
            $saved = (new FacilityService())->updateFacilityAndUsers([
                'id' => (string) $this->facilityId,
                'name' => 'ZZ save transaction renamed',
            ]);
        } finally {
            $dispatcher->removeListener(FacilityUpdatedEvent::EVENT_HANDLE, $listener);
        }

        $this->assertTrue($saved);
        $this->assertSame('ZZ save transaction renamed', $seen);
        $this->assertSame('ZZ save transaction renamed', $this->storedName());
    }

    /**
     * The name currently stored for the row this test inserted.
     */
    private function storedName(): string
    {
        $row = QueryUtils::querySingleRow(
            'SELECT name FROM facility WHERE id = ?',
            [$this->facilityId]
        );
        if (!is_array($row) || !is_string($row['name'] ?? null)) {
            $this->fail('The facility row was not stored');
        }

        return $row['name'];
    }
}

/**
 * Facility service whose user-name update fails.
 */
final class FacilityUsersUpdateFails extends FacilityService
{
    /**
     * Refuse the user-name update so the facility write has to roll back.
     */
    public function updateUsersFacility(string $facility_name, int|string $facility_id): never
    {
        throw new \RuntimeException('The facility users could not be saved');
    }
}
