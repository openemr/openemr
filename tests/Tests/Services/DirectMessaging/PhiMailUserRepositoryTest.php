<?php

/**
 * Database tests for the phiMail receiver's user lookups.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\DirectMessaging;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\DirectMessaging\PhiMailUserRepository;
use PHPUnit\Framework\TestCase;

final class PhiMailUserRepositoryTest extends TestCase
{
    private PhiMailUserRepository $users;

    protected function setUp(): void
    {
        $this->users = new PhiMailUserRepository();
        $this->deleteServiceUser();
    }

    protected function tearDown(): void
    {
        $this->deleteServiceUser();
    }

    public function testFindsAUsername(): void
    {
        $id = QueryUtils::fetchSingleValue('SELECT id FROM users WHERE username = ?', 'id', ['admin']);
        self::assertIsNumeric($id);

        self::assertSame('admin', $this->users->findUsername((int) $id));
    }

    public function testFindsNoUsernameForAMissingOrUnknownUser(): void
    {
        self::assertNull($this->users->findUsername(null));
        self::assertNull($this->users->findUsername(PHP_INT_MAX));
    }

    public function testFallsBackToTheFirstUserWithoutAServiceUser(): void
    {
        self::assertSame(1, $this->users->serviceUserId());
    }

    public function testFindsTheServiceUser(): void
    {
        $id = QueryUtils::sqlInsert("INSERT INTO users (username, authorized, active) VALUES ('phimail-service', 0, 1)");

        self::assertSame($id, $this->users->serviceUserId());
    }

    public function testFindsThePortalUser(): void
    {
        // The installer creates portal-user.
        $id = QueryUtils::fetchSingleValue('SELECT id FROM users WHERE username = ?', 'id', ['portal-user']);
        self::assertIsNumeric($id);

        self::assertEquals($id, $this->users->portalUserId());
    }

    private function deleteServiceUser(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM users WHERE username = 'phimail-service'");
    }
}
