<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\tests;
use Symfony\Component\Panther\PantherTestCase;
use OpenEMR\Tests\E2e\pages\{LoginPage, FacilitiesTab};

class FacilitiesTabTest extends PantherTestCase
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
    public function testCreateFacilityFormIsNotSubmittedWhenNameIsMissing()
    {
        $session = $this->start();
        $data = ["facility"=>"", "ncolor"=>"#ffffff"];
        (new FacilitiesTab)->findCreateFacilityValidationError($session, $data, 'is not valid');
        $session[1]->quit();
    }

    /** @test */
    public function testCreateFacilityFormIsNotSubmittedWhenColorIsMissing()
    {
        $session = $this->start();
        $data = ["facility"=>"Test Facility Name", "ncolor"=>""];
        (new FacilitiesTab)->findCreateFacilityValidationError($session, $data, 'is not valid');
        $session[1]->quit();
    }
}