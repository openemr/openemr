<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\Client;
use OpenEMR\Tests\E2e\Pages\{LoginPage, MainPage};

class CreateStaffTest extends PantherTestCase
{
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv("OPENEMR_BASE_URL_E2E", true) ?: "http://localhost";
    }

    protected function tearDown(): void
    {
        // remove the created user
        $delete = "DELETE FROM users WHERE username = ?";
        sqlStatement($delete, array('foobar'));

        $delete = "DELETE FROM users_secure WHERE username = ?";
        sqlStatement($delete, array('foobar'));
    }

    /** @test */
    public function check_add_user(): void
    {
        $openEmrPage = $this->e2eBaseUrl;
        $client = static::createPantherClient(['external_base_uri' => $openEmrPage]);
        $lp = new LoginPage($client, $this);
        $mp = $lp->login('admin', 'pass');

        // add the user and then check that the user was added
        $mp->openUsers();
        $mp->assertActiveTab("Users / Group");
        $ut = $mp->selectUsersTab();
        $ut->addUser('foobar');
        $ut->assertUserPresent('foobar');
    }
}
