<?php

/**
 * Tests for CashReceiptsRepository
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\CashReceipts;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository;

/**
 * Test suite for CashReceiptsRepository
 *
 * @coversDefaultClass \OpenEMR\Reports\CashReceipts\Repository\CashReceiptsRepository
 */
class CashReceiptsRepositoryTest extends TestCase
{
    /**
     * @var CashReceiptsRepository
     */
    private CashReceiptsRepository $repository;

    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new CashReceiptsRepository();
    }

    /**
     * Test getCopayReceipts with basic filters
     */
    public function testGetCopayReceiptsWithBasicFilters(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $result = $this->repository->getCopayReceipts($filters);
        
        $this->assertIsArray($result);
        // If there are results, verify structure
        if (!empty($result)) {
            $firstRecord = $result[0];
            $this->assertArrayHasKey('fee', $firstRecord);
            $this->assertArrayHasKey('pid', $firstRecord);
            $this->assertArrayHasKey('encounter', $firstRecord);
            $this->assertArrayHasKey('docid', $firstRecord);
            $this->assertArrayHasKey('trans_id', $firstRecord);
        }
    }

    /**
     * Test getCopayReceipts with facility filter
     */
    public function testGetCopayReceiptsWithFacilityFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'facility_id' => 1,
        ];

        $result = $this->repository->getCopayReceipts($filters);
        $this->assertIsArray($result);
    }

    /**
     * Test getCopayReceipts with provider filter
     */
    public function testGetCopayReceiptsWithProviderFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'provider_id' => 1,
        ];

        $result = $this->repository->getCopayReceipts($filters);
        $this->assertIsArray($result);
    }

    /**
     * Test getArActivityReceipts with basic filters
     */
    public function testGetArActivityReceiptsWithBasicFilters(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
        ];

        $result = $this->repository->getArActivityReceipts($filters);
        
        $this->assertIsArray($result);
        // If there are results, verify structure
        if (!empty($result)) {
            $firstRecord = $result[0];
            $this->assertArrayHasKey('pid', $firstRecord);
            $this->assertArrayHasKey('encounter', $firstRecord);
            $this->assertArrayHasKey('pay_amount', $firstRecord);
            $this->assertArrayHasKey('code', $firstRecord);
        }
    }

    /**
     * Test getArActivityReceipts with procedure filter
     */
    public function testGetArActivityReceiptsWithProcedureFilter(): void
    {
        $filters = [
            'from_date' => '2024-01-01',
            'to_date' => '2024-12-31',
            'procedure_code_type' => 'CPT4',
            'procedure_code' => '99213',
        ];

        $result = $this->repository->getArActivityReceipts($filters);
        $this->assertIsArray($result);
    }

    /**
     * Test hasDiagnosisCode returns bool
     */
    public function testHasDiagnosisCodeReturnsBool(): void
    {
        $result = $this->repository->hasDiagnosisCode(1, 1, 'ICD10', 'Z00.00');
        $this->assertIsBool($result);
    }

    /**
     * Test getInvoiceAmount returns float
     */
    public function testGetInvoiceAmountReturnsFloat(): void
    {
        $result = $this->repository->getInvoiceAmount(1, 1, 'CPT4', '99213');
        $this->assertIsFloat($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test getInsuranceCompanyName returns string
     */
    public function testGetInsuranceCompanyNameReturnsString(): void
    {
        $result = $this->repository->getInsuranceCompanyName(1);
        $this->assertIsString($result);
    }

    /**
     * Test getInsuranceCompanyName returns empty for zero id
     */
    public function testGetInsuranceCompanyNameReturnsEmptyForZeroId(): void
    {
        $result = $this->repository->getInsuranceCompanyName(0);
        $this->assertSame('', $result);
    }

    /**
     * Test getProviderName returns string
     */
    public function testGetProviderNameReturnsString(): void
    {
        $result = $this->repository->getProviderName(1);
        $this->assertIsString($result);
    }

    /**
     * Test getProviderName returns unknown for invalid id
     */
    public function testGetProviderNameReturnsUnknownForInvalidId(): void
    {
        $result = $this->repository->getProviderName(999999);
        $this->assertSame('Unknown', $result);
    }

    /**
     * Test getAuthorizedProviders returns array
     */
    public function testGetAuthorizedProvidersReturnsArray(): void
    {
        $result = $this->repository->getAuthorizedProviders();
        $this->assertIsArray($result);
        
        // Verify structure if there are results
        if (!empty($result)) {
            $firstProvider = $result[0];
            $this->assertArrayHasKey('id', $firstProvider);
            $this->assertArrayHasKey('fname', $firstProvider);
            $this->assertArrayHasKey('lname', $firstProvider);
        }
    }
}
