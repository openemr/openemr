<?php

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\InvoiceSummary;
use PHPUnit\Framework\TestCase;

/**
 * Service tests for InvoiceSummary class
 * This class requires database access for all its methods
 *
 * Note: These are smoke tests that verify the methods execute without errors.
 * They use non-existent patient/encounter IDs to avoid dependencies on test fixtures.
 * For thorough testing of business logic with real data, consider adding fixture-based tests.
 */
class InvoiceSummaryTest extends TestCase
{
    public function testArGetInvoiceSummaryReturnsArray(): void
    {
        // Use fake patient/encounter IDs - the method should handle non-existent data gracefully
        $summary = InvoiceSummary::arGetInvoiceSummary(999999, 999999);

        $this->assertIsArray($summary);
        $this->assertEmpty($summary, 'Non-existent patient/encounter should return empty array');
    }

    public function testArGetInvoiceSummaryWithoutDetailParameter(): void
    {
        // Test with explicit false for detail parameter
        $summary = InvoiceSummary::arGetInvoiceSummary(999999, 999999, false);

        $this->assertIsArray($summary);
        $this->assertEmpty($summary);
    }

    public function testArGetInvoiceSummaryWithDetailParameter(): void
    {
        // Test with explicit true for detail parameter
        $summary = InvoiceSummary::arGetInvoiceSummary(999999, 999999, true);

        $this->assertIsArray($summary);
        $this->assertEmpty($summary);
    }

    public function testArResponsiblePartyReturnsInteger(): void
    {
        $responsibleParty = InvoiceSummary::arResponsibleParty(999999, 999999);

        $this->assertIsInt($responsibleParty);
        // Should return -1, 0, 1, 2, or 3
        $this->assertContains($responsibleParty, [-1, 0, 1, 2, 3]);
    }

    public function testArResponsiblePartyWithNonexistentEncounter(): void
    {
        $responsibleParty = InvoiceSummary::arResponsibleParty(999999, 999999);

        // Should return -1 (Nobody) for nonexistent encounter
        $this->assertEquals(-1, $responsibleParty);
    }

    public function testArResponsiblePartyWithNonexistentPatient(): void
    {
        $responsibleParty = InvoiceSummary::arResponsibleParty(999999, 999998);

        // Should return -1 (Nobody) for nonexistent patient
        $this->assertEquals(-1, $responsibleParty);
    }

    public function testArGetInvoiceSummaryMethodExists(): void
    {
        // Verify the static method exists and is callable
        $this->assertTrue(method_exists(InvoiceSummary::class, 'arGetInvoiceSummary'));
        $this->assertTrue(is_callable(InvoiceSummary::arGetInvoiceSummary(...)));
    }

    public function testArResponsiblePartyMethodExists(): void
    {
        // Verify the static method exists and is callable
        $this->assertTrue(method_exists(InvoiceSummary::class, 'arResponsibleParty'));
        $this->assertTrue(is_callable(InvoiceSummary::arResponsibleParty(...)));
    }
}
