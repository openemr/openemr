<?php

/**
 * The Fax/SMS module's User Permissions apply to every active login user.
 *
 * Setup Services listed only users without an address book type, while its
 * save wrote a permission for every active user. A provider with a type (a
 * specialist, say) was hidden from the list and reset to no permission on
 * every save, losing the Fax menu. Both now use PermissionUsers::active().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\FaxSMS;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\FaxSMS\Service\PermissionUsers;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Service/PermissionUsers.php';

class PermissionUsersTest extends TestCase
{
    /** Marks the fixture users (lname), so cleanup removes only those. */
    private const FIXTURE_LNAME = 'faxsms-permission-users-test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    public function testListsLoginUsersWhateverTheirAddressBookType(): void
    {
        $staff = $this->insertUser('fax-test-staff', 'Staff', '', 1);
        $specialist = $this->insertUser('fax-test-specialist', 'Natalie', 'spe', 1);
        $noFirstName = $this->insertUser('fax-test-nofname', '', 'oth', 1);
        $contact = $this->insertUser('', 'Contact', 'spe', 1);
        $inactive = $this->insertUser('fax-test-inactive', 'Gone', '', 0);

        $byId = [];
        foreach (PermissionUsers::active() as $user) {
            $byId[$user['id']] = $user;
        }

        $this->assertArrayHasKey($staff, $byId, 'A login user without an address book type is listed');
        $this->assertArrayHasKey($specialist, $byId, 'A login user with an address book type is listed');
        $this->assertArrayHasKey($noFirstName, $byId, 'A login user without a first name is listed');
        $this->assertArrayNotHasKey($contact, $byId, 'An address book contact without a login is not listed');
        $this->assertArrayNotHasKey($inactive, $byId, 'An inactive user is not listed');

        $this->assertSame(
            ['id' => $specialist, 'username' => 'fax-test-specialist', 'fname' => 'Natalie', 'lname' => self::FIXTURE_LNAME],
            $byId[$specialist]
        );
        $this->assertSame('', $byId[$noFirstName]['fname']);
    }

    private function insertUser(string $username, string $fname, string $abookType, int $active): int
    {
        return QueryUtils::sqlInsert(
            'INSERT INTO `users` (`username`, `password`, `fname`, `lname`, `abook_type`, `active`, `authorized`) VALUES (?, ?, ?, ?, ?, ?, 0)',
            [$username, 'NoLogin', $fname, self::FIXTURE_LNAME, $abookType, $active]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `lname` = ?', [self::FIXTURE_LNAME]);
    }
}
