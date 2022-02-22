<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage, PatientsPage};

class PatientsPageTest extends PantherTestCase
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
    public function testCreatePatientFormIsNotSubmittedWhenFirstNameIsMissing()
    {
        $session = $this->start();
        $data = ["form_fname"=>"", "form_lname"=>"testlastname", "form_DOB"=>"2022-01-31", "form_sex"=>"Male"];
        (new PatientsPage)->findCreatePatientValidationError($session, $data, 'First Name is not valid');
        $session[1]->quit();
    }

    /** @test */
    public function testCreatePatientFormIsNotSubmittedWhenLastNameIsMissing()
    {
        $session = $this->start();
        $data = ["form_fname"=>"testfirstname", "form_lname"=>"", "form_DOB"=>"2022-01-31", "form_sex"=>"Male"];
        (new PatientsPage)->findCreatePatientValidationError($session, $data, 'Last Name is not valid');
        $session[1]->quit();
    }

    /** @test */
    public function testCreatePatientFormIsNotSubmittedWhenDOBIsMissing()
    {
        $session = $this->start();
        $data = ["form_fname"=>"testfirstname", "form_lname"=>"testlastname", "form_DOB"=>"", "form_sex"=>"Male"];
        (new PatientsPage)->findCreatePatientValidationError($session, $data, 'Date of Birth is not valid');
        $session[1]->quit();
    }

    /** @test */
    public function testCreatePatientFormIsNotSubmittedWhenSexIsMissing()
    {
        $session = $this->start();
        $data = ["form_fname"=>"testfirstname", "form_lname"=>"testlastname", "form_DOB"=>"2022-01-31", "form_sex"=>""];
        (new PatientsPage)->findCreatePatientValidationError($session, $data, 'Sex is not valid');
        $session[1]->quit();
    }
}