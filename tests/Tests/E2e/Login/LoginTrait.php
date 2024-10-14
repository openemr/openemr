<?php

/**
 * LoginTrait trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Login;

use OpenEMR\Tests\E2e\Base\BaseTrait;
use OpenEMR\Tests\E2e\Login\LoginTestData;

trait LoginTrait
{
    use BaseTrait;

    public function testLoginAuthorized(): void
    {
        $this->base();
        try {
            $this->login(LoginTestData::username, LoginTestData::password);
        } catch (\Throwable $e) {
            // Close client
            $this->client->quit();
            // re-throw the exception
            throw $e;
        }
        // Close client
        $this->client->quit();
    }

    private function login(string $name, string $password, bool $goalPass = true): void
    {
        // login
        $this->crawler = $this->client->request('GET', '/interface/login/login.php?site=default');
        $form = $this->crawler->filter('#login_form')->form();
        $form['authUser'] = $name;
        $form['clearPass'] = $password;
        $this->crawler = $this->client->submit($form);
        $title = $this->client->getTitle();
        if ($goalPass) {
            $this->assertSame('OpenEMR', $title, 'Login FAILED');
        } else {
            $this->assertSame('OpenEMR Login', $title, 'Login was successful, but should of FAILED');
        }
    }
}
