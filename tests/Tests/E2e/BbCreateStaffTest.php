<?php

/**
 * BbCreateStaffTest class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @auther    Bartosz Spyrko-Smietanko
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Bartosz Spyrko-Smietanko
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTrait;
use OpenEMR\Tests\E2e\User\UserAddTrait;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;

class BbCreateStaffTest extends PantherTestCase
{
    use BaseTrait;
    use LoginTrait;
    use UserAddTrait;

    private $client;
    private $crawler;

    protected function setUp(): void
    {
        // clean up in case still left over from prior testing
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        // remove the created user
        $delete = "DELETE FROM users WHERE username = ?";
        sqlStatement($delete, array('foobar'));

        $delete = "DELETE FROM users_secure WHERE username = ?";
        sqlStatement($delete, array('foobar'));
    }
}
