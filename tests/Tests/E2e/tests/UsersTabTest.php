<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage, UsersTab};

class UsersTabTest extends PantherTestCase
{
    private $e2eBaseUrl;

    protected function setUp(): void
    {
        $this->e2eBaseUrl = getenv('OPENEMR_BASE_URL_E2E', true) ?: 'http://localhost';
    }

    public function start()
    {      
        $driver = static::createPantherClient(['external_base_uri' => $this->e2eBaseUrl]);

        return (new LoginPage)->login($driver, $this);
    }
    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenUsernameIsMissing()
    {
        $session = $this->start();
        $data = ["rumple"=>"", "stiltskin"=>"test_Password1$", "fname"=>"Foo", "lname"=>"Bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserValidationError($session, $data, 'Required field missing: Please enter the User Name');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenFirstnameIsMissing()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"test_Password1$", "fname"=>"", "lname"=>"Bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserValidationError($session, $data, 'Required field missing: Please enter the First name');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenLastnameIsMissing()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"test_Password1$", "fname"=>"foo", "lname"=>"", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserValidationError($session, $data, 'Required field missing: Please enter the Last name');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordIsMissing()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserValidationError($session, $data, 'Please enter the password');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenAdminPasswordIsMissing()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"test_Password1$", "fname"=>"foo", "lname"=>"bar", "adminPass"=>""];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password update error!');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordHasOneOrMoreCharactersButLessThanNine()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"aaa", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password too short.');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordHasNineOrMoreCharactersButIsMissingANumber()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"Aaaaaaaaa$", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password does not meet minimum requirements');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordHasNineOrMoreCharactersButIsMissingALowercaseLetter()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"AAAAAAAAAAAA1$", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password does not meet minimum requirements');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordHasNineOrMoreCharactersButIsMissingAnUppercaseLetter()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"aaaaaaaaaaaa1$", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password does not meet minimum requirements');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateUserFormIsNotSubmittedWhenPasswordHasNineOrMoreCharactersButIsMissingASpecialCharacter()
    {
        $session = $this->start();
        $data = ["rumple"=>"testuser", "stiltskin"=>"aaaaaaaaaAAaa1", "fname"=>"foo", "lname"=>"bar", "adminPass"=>'pass'];
        (new UsersTab)->findCreateUserPasswordValidationError($session, $data, 'Password does not meet minimum requirements');
        $session[1]->quit();
    }
}