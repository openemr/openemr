<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Tests\Fixtures\EncounterFixtureManager;
use OpenEMR\Tests\Fixtures\PatientFixtureManager;
use OpenEMR\Tests\Fixtures\ProviderFixtureManager;
use OpenEMR\Tests\Fixtures\FacilityFixtureManager;

/**
 * @coversDefaultClass OpenEMR\Reports\Encounter\EncounterReportData
 */
class EncountersReportTest extends TestCase
{
    /** @var EncounterFixtureManager */
    private $fixtureManager;

    /** @var PatientFixtureManager */
    private $patientFixtures;

    /** @var ProviderFixtureManager */
    private $providerFixtures;

    /** @var FacilityFixtureManager */
    private $facilityFixtures;

    /** @var string */
    private $testUserId;

    /** @var string */
    private $testGroup;

    /** @var string */
    private $testApiBaseUrl;

    protected function setUp(): void
    {
        $this->fixtureManager = new EncounterFixtureManager();
        $this->patientFixtures = new PatientFixtureManager();
        $this->providerFixtures = new ProviderFixtureManager();
        $this->facilityFixtures = new FacilityFixtureManager();
        
        $this->testUserId = $_SESSION['authUserID'] ?? null;
        $this->testGroup = $_SESSION['authProvider'] ?? null;
        
        // Ensure we have a valid session
        if (empty($this->testUserId) || empty($this->testGroup)) {
            $this->markTestSkipped('No active session found');
        }
    }

    protected function tearDown(): void
    {
        $this->fixtureManager->removeFixtures();
        $this->patientFixtures->removePatientFixtures();
        $this->providerFixtures->removeProviderFixtures();
        $this->facilityFixtures->removeFacilityFixtures();
    }

    /**
     * Test the encounter report data retrieval
     */
    public function testGetEncounterReportData()
    {
        // Create test data
        $provider = $this->providerFixtures->getSingleProvider();
        $facility = $this->facilityFixtures->getSingleFacility();
        $patient = $this->patientFixtures->getSinglePatient();
        
        $encounterData = [
            'provider_id' => $provider->id,
            'facility_id' => $facility->id,
            'pid' => $patient->pid,
            'encounter' => $this->fixtureManager->getUnusedEncounterId(),
            'date' => date('Y-m-d H:i:s'),
            'reason' => 'Test encounter',
            'facility' => $facility->name,
            'signed' => 1,
            'signed_time' => date('Y-m-d H:i:s'),
            'signed_by' => $provider->lname . ', ' . $provider->fname
        ];
        
        $encounter = $this->fixtureManager->createEncounter($encounterData);
        
        // Test the report data retrieval
        $reportData = new \OpenEMR\Reports\Encounter\EncounterReportData();
        $filters = [
            'date_from' => date('Y-m-d', strtotime('-1 month')),
            'date_to' => date('Y-m-d', strtotime('+1 day')),
            'provider' => $provider->id,
            'facility' => $facility->id,
            'details' => 1
        ];
        
        $result = $reportData->getEncounters($filters);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertEquals($encounter['encounter'], $result[0]['encounter']);
    }

    /**
     * Test CSRF protection
     */
    public function testCsrfProtection()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Test with invalid CSRF token
        $_POST['csrf_token_form'] = 'invalid_token';
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Authentication Failed');
        
        // This should throw an exception due to invalid CSRF
        CsrfUtils::verifyCsrfToken($_POST['csrf_token_form']);
    }

    /**
     * Test access control
     */
    public function testAclAccess()
    {
        // Test with a user who doesn't have access
        $hasAccess = AclMain::aclCheckCore('encounters', 'coding_a', 'write', 'write');
        $this->assertTrue($hasAccess, 'User should have access to encounters report');
        
        // Test with a different ACL that we know should fail
        $hasAccess = AclMain::aclCheckCore('admin', 'super', 'write', 'write');
        $this->assertFalse($hasAccess, 'Regular user should not have admin access');
    }

    /**
     * Test input validation
     */
    public function testInputValidation()
    {
        $formHandler = new \OpenEMR\Reports\Encounter\EncounterReportFormHandler();
        
        // Test with invalid dates
        $invalidData = [
            'date_from' => 'invalid-date',
            'date_to' => 'another-invalid-date'
        ];
        
        $result = $formHandler->processForm($invalidData);
        
        // The form handler should return default dates for invalid input
        $this->assertNotEquals('invalid-date', $result['date_from']);
        $this->assertNotEquals('another-invalid-date', $result['date_to']);
        
        // Test with valid dates
        $validData = [
            'date_from' => '2023-01-01',
            'date_to' => '2023-12-31'
        ];
        
        $result = $formHandler->processForm($validData);
        
        $this->assertEquals('2023-01-01', $result['date_from']);
        $this->assertEquals('2023-12-31', $result['date_to']);
    }

    /**
     * Test report formatter
     */
    public function testReportFormatter()
    {
        $formatter = new \OpenEMR\Reports\Encounter\EncounterReportFormatter();
        
        // Create test data
        $testData = [
            [
                'id' => 1,
                'date' => '2023-01-01 10:00:00',
                'provider_id' => 1,
                'provider_name' => 'Test Provider',
                'encounter_count' => 5
            ]
        ];
        
        // Test summary formatting
        $formatted = $formatter->formatSummary($testData);
        $this->assertIsArray($formatted);
        $this->assertArrayHasKey('providers', $formatted);
        $this->assertArrayHasKey('total_encounters', $formatted);
        
        // Test detailed formatting
        $detailedData = [
            [
                'id' => 1,
                'date' => '2023-01-01 10:00:00',
                'pid' => 1,
                'patient' => 'Test Patient',
                'provider' => 'Test Provider',
                'category' => 'Office Visit',
                'encounter' => 1,
                'forms' => 'Form1, Form2',
                'coding' => '99213',
                'signedby' => 'Dr. Smith'
            ]
        ];
        
        $formattedDetails = $formatter->formatEncounters($detailedData);
        $this->assertIsArray($formattedDetails);
        $this->assertCount(1, $formattedDetails);
    }

    /**
     * Test rate limiting
     */
    public function testRateLimiting()
    {
        // This would test the rate limiting functionality
        // Implementation would depend on how rate limiting is implemented
        $this->markTestIncomplete('Rate limiting test needs implementation');
    }
}
