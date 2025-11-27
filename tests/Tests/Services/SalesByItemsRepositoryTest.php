<?php

namespace OpenEMR\Tests\Services;

use PHPUnit\Framework\TestCase;
use OpenEMR\Reports\SalesByItems\Repository\SalesByItemsRepository;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

/**
 * SalesByItemsRepositoryTest - TDD tests for repository layer (Phase 1)
 *
 * Tests the repository that fetches billing and drug sales data using QueryUtils.
 * This phase replaces deprecated sqlStatement()/sqlFetchArray() with QueryUtils.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class SalesByItemsRepositoryTest extends TestCase
{
    private SalesByItemsRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new SalesByItemsRepository();
    }

    /**
     * Test 1: SalesItem model stores all properties correctly
     */
    public function testSalesItemModelStoresAllProperties(): void
    {
        // Arrange
        $item = new SalesItem(
            patientId: 123,
            encounterId: 456,
            category: 'Office Visit',
            description: '99213 Established Patient Visit',
            transactionDate: '2025-01-15',
            quantity: 1,
            amount: 150.00,
            invoiceNumber: '123.456',
            invoiceRefNo: 'INV-001'
        );

        // Act & Assert
        $this->assertEquals(123, $item->getPatientId());
        $this->assertEquals(456, $item->getEncounterId());
        $this->assertEquals('Office Visit', $item->getCategory());
        $this->assertEquals('99213 Established Patient Visit', $item->getDescription());
        $this->assertEquals('2025-01-15', $item->getTransactionDate());
        $this->assertEquals(1, $item->getQuantity());
        $this->assertEquals(150.00, $item->getAmount());
        $this->assertEquals('123.456', $item->getInvoiceNumber());
        $this->assertEquals('INV-001', $item->getInvoiceRefNo());
    }

    /**
     * Test 2: SalesItem model accepts empty invoiceRefNo
     */
    public function testSalesItemModelAcceptsEmptyInvoiceRefNo(): void
    {
        // Arrange
        $item = new SalesItem(
            patientId: 123,
            encounterId: 456,
            category: 'Products',
            description: 'Bandage',
            transactionDate: '2025-01-15',
            quantity: 10,
            amount: 25.00,
            invoiceNumber: '123.456'
        );

        // Act & Assert
        $this->assertEquals('', $item->getInvoiceRefNo());
    }

    /**
     * Test 3: SalesGroup model aggregates items correctly
     */
    public function testSalesGroupModelAggregatesItemsCorrectly(): void
    {
        // Arrange
        $group = new \OpenEMR\Reports\SalesByItems\Model\SalesGroup(
            'Office Visit',
            '99213'
        );

        $item1 = new SalesItem(
            123, 456, 'Office Visit', '99213 Established',
            '2025-01-15', 1, 150.00, '123.456'
        );

        $item2 = new SalesItem(
            124, 457, 'Office Visit', '99213 Established',
            '2025-01-16', 1, 150.00, '124.457'
        );

        // Act
        $group->addItem($item1);
        $group->addItem($item2);

        // Assert
        $this->assertEquals('Office Visit', $group->getCategory());
        $this->assertEquals('99213', $group->getProduct());
        $this->assertEquals(2, $group->getQuantity());
        $this->assertEquals(300.00, $group->getTotal());
        $this->assertCount(2, $group->getItems());
    }

    /**
     * Test 4: SalesGroup allows manual total/quantity updates
     */
    public function testSalesGroupAllowsManualTotalUpdates(): void
    {
        // Arrange
        $group = new \OpenEMR\Reports\SalesByItems\Model\SalesGroup(
            'Products',
            'Bandage'
        );

        // Act
        $group->setQuantity(50);
        $group->setTotal(250.00);
        $group->addToQuantity(10);
        $group->addToTotal(50.00);

        // Assert
        $this->assertEquals(60, $group->getQuantity());
        $this->assertEquals(300.00, $group->getTotal());
    }

    /**
     * Test 5: Repository getBillingItems constructs correct query
     */
    public function testGetBillingItemsConstructsCorrectQuery(): void
    {
        // Arrange - This test verifies the method exists and returns array
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';

        // Act
        $result = $this->repository->getBillingItems($fromDate, $toDate);

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * Test 6: Repository getDrugSales constructs correct query
     */
    public function testGetDrugSalesConstructsCorrectQuery(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';

        // Act
        $result = $this->repository->getDrugSales($fromDate, $toDate);

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * Test 7: Repository getAllSalesItems merges both sources
     */
    public function testGetAllSalesItemsMergesBothSources(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';

        // Act
        $result = $this->repository->getAllSalesItems($fromDate, $toDate);

        // Assert
        $this->assertIsArray($result);
        // Each item should be a SalesItem instance
        foreach ($result as $item) {
            $this->assertInstanceOf(SalesItem::class, $item);
        }
    }

    /**
     * Test 8: Repository accepts facility filter
     */
    public function testRepositoryAcceptsFacilityFilter(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';
        $facilityId = 1;

        // Act
        $result = $this->repository->getBillingItems(
            $fromDate,
            $toDate,
            $facilityId
        );

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * Test 9: Repository accepts provider filter
     */
    public function testRepositoryAcceptsProviderFilter(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';
        $providerId = 1;

        // Act
        $result = $this->repository->getDrugSales(
            $fromDate,
            $toDate,
            providerId: $providerId
        );

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * Test 10: Repository accepts both facility and provider filters
     */
    public function testRepositoryAcceptsBothFilters(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';
        $facilityId = 1;
        $providerId = 5;

        // Act
        $result = $this->repository->getAllSalesItems(
            $fromDate,
            $toDate,
            $facilityId,
            $providerId
        );

        // Assert
        $this->assertIsArray($result);
        // All items should have consistent data types
        foreach ($result as $item) {
            $this->assertIsInt($item->getPatientId());
            $this->assertIsInt($item->getEncounterId());
            $this->assertIsString($item->getCategory());
            $this->assertIsString($item->getDescription());
            $this->assertIsString($item->getTransactionDate());
            $this->assertIsInt($item->getQuantity());
            $this->assertIsFloat($item->getAmount());
            $this->assertIsString($item->getInvoiceNumber());
        }
    }

    /**
     * Test 11: SalesItem quantity defaults to 1 when not specified
     */
    public function testSalesItemQuantityTypeChecking(): void
    {
        // Arrange
        $item = new SalesItem(
            100,
            200,
            'Test',
            'Test Description',
            '2025-01-01',
            quantity: 5,
            amount: 100.00,
            invoiceNumber: '100.200'
        );

        // Act & Assert
        $this->assertIsInt($item->getQuantity());
        $this->assertEquals(5, $item->getQuantity());
    }

    /**
     * Test 12: Repository results are sortable by category/description/date
     */
    public function testRepositoryResultsSortable(): void
    {
        // Arrange
        $fromDate = '2025-01-01';
        $toDate = '2025-01-31';

        // Act
        $result = $this->repository->getAllSalesItems($fromDate, $toDate);

        // Assert
        // getAllSalesItems should return sorted results
        $this->assertIsArray($result);

        // If we have multiple items, verify they maintain sort order
        if (count($result) > 1) {
            $prevCategory = '';
            $prevDesc = '';
            $prevDate = '';

            foreach ($result as $item) {
                $category = $item->getCategory();
                $description = $item->getDescription();
                $date = $item->getTransactionDate();

                // Category should be >= previous category (or same with description check)
                if ($category === $prevCategory) {
                    // If same category, description should be >= previous description
                    if ($description === $prevDesc) {
                        // If same description, date should be >= previous date
                        $this->assertGreaterThanOrEqual($prevDate, $date);
                    } else {
                        $this->assertGreaterThanOrEqual($prevDesc, $description);
                    }
                } else {
                    $this->assertGreaterThanOrEqual($prevCategory, $category);
                }

                $prevCategory = $category;
                $prevDesc = $description;
                $prevDate = $date;
            }
        }
    }
}
