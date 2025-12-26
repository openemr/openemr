<?php

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for BillingClaim class
 * Uses a stub to override database calls
 */
class BillingClaimTest extends TestCase
{
    public function testConstructorParsesClaimIdPrimary(): void
    {
        $claim = new BillingClaimStub(
            '123-456',
            ['partner' => 'PARTNER1', 'payer' => 'P789'],
            'generate'
        );

        $this->assertEquals('123-456', $claim->getId());
        $this->assertEquals('123', $claim->getPid());
        $this->assertEquals('456', $claim->getEncounter());
        $this->assertEquals('PARTNER1', $claim->getPartner());
        $this->assertEquals('789', $claim->getPayorId());
        $this->assertEquals(BillingClaim::PRIMARY, $claim->getPayorType());
    }

    public function testConstructorParsesClaimIdSecondary(): void
    {
        $claim = new BillingClaimStub(
            '100-200',
            ['partner' => 'PARTNER2', 'payer' => 'S555'],
            'validate'
        );

        $this->assertEquals('100', $claim->getPid());
        $this->assertEquals('200', $claim->getEncounter());
        $this->assertEquals('555', $claim->getPayorId());
        $this->assertEquals(BillingClaim::SECONDARY, $claim->getPayorType());
    }

    public function testConstructorParsesClaimIdTertiary(): void
    {
        $claim = new BillingClaimStub(
            '999-111',
            ['partner' => 'PARTNER3', 'payer' => 'T333'],
            'generate'
        );

        $this->assertEquals('999', $claim->getPid());
        $this->assertEquals('111', $claim->getEncounter());
        $this->assertEquals('333', $claim->getPayorId());
        $this->assertEquals(BillingClaim::TERTIARY, $claim->getPayorType());
    }

    public function testConstructorParsesClaimIdUnknownPayorType(): void
    {
        $claim = new BillingClaimStub(
            '50-60',
            ['partner' => 'PARTNER4', 'payer' => 'X999'],
            'generate'
        );

        $this->assertEquals('999', $claim->getPayorId());
        $this->assertEquals(BillingClaim::UNKNOWN, $claim->getPayorType());
    }

    public function testIsLastDefaultsFalse(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'P100'],
            'generate'
        );

        $this->assertFalse($claim->getIsLast());
    }

    public function testSetIsLast(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'P100'],
            'generate'
        );

        $claim->setIsLast(true);
        $this->assertTrue($claim->getIsLast());

        $claim->setIsLast(false);
        $this->assertFalse($claim->getIsLast());
    }

    public function testSetAndGetTarget(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'P100'],
            'generate'
        );

        $claim->setTarget('test_target');
        $this->assertEquals('test_target', $claim->getTarget());
    }

    public function testSetId(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'P100'],
            'generate'
        );

        $claim->setId('999-888');
        $this->assertEquals('999-888', $claim->getId());
    }

    public function testJsonSerialize(): void
    {
        $claim = new BillingClaimStub(
            '123-456',
            ['partner' => 'PARTNER1', 'payer' => 'P789'],
            'test_action'
        );

        $claim->setIsLast(true);

        $json = $claim->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertEquals('123-456', $json['id']);
        $this->assertEquals('123', $json['pid']);
        $this->assertEquals('456', $json['encounter']);
        $this->assertEquals('PARTNER1', $json['partner']);
        $this->assertEquals('789', $json['payor_id']);
        $this->assertEquals(BillingClaim::PRIMARY, $json['payor_type']);
        $this->assertTrue($json['is_last']);
        $this->assertEquals('test_action', $json['action']);
    }

    public function testPayorTypeConstants(): void
    {
        $this->assertEquals(1, BillingClaim::PRIMARY);
        $this->assertEquals(2, BillingClaim::SECONDARY);
        $this->assertEquals(3, BillingClaim::TERTIARY);
        $this->assertEquals(0, BillingClaim::UNKNOWN);
    }

    public function testStatusConstants(): void
    {
        $this->assertEquals(-1, BillingClaim::STATUS_LEAVE_UNCHANGED);
        $this->assertEquals(1, BillingClaim::STATUS_LEAVE_UNBILLED);
        $this->assertEquals(2, BillingClaim::STATUS_MARK_AS_BILLED);
    }

    public function testBillProcessConstants(): void
    {
        $this->assertEquals(-1, BillingClaim::BILL_PROCESS_LEAVE_UNCHANGED);
        $this->assertEquals(0, BillingClaim::BILL_PROCESS_OPEN);
        $this->assertEquals(1, BillingClaim::BILL_PROCESS_IN_PROGRESS);
        $this->assertEquals(2, BillingClaim::BILL_PROCESS_BILLED);
    }

    public function testLowercasePayorTypeCharacter(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'p100'],
            'generate'
        );

        // Should handle lowercase 'p' as PRIMARY
        $this->assertEquals(BillingClaim::PRIMARY, $claim->getPayorType());
    }

    public function testEmptyPayorString(): void
    {
        $claim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => ''],
            'generate'
        );

        // Empty string should result in UNKNOWN
        $this->assertEquals(BillingClaim::UNKNOWN, $claim->getPayorType());
        $this->assertEquals('', $claim->getPayorId());
    }
}

/**
 * Stub class to override database method in BillingClaim constructor
 */
class BillingClaimStub extends BillingClaim
{
    public function __construct($claimId, $partner_and_payor, $action)
    {
        // Assume this is not the last claim in the "loop" unless explicitly set.
        $this->is_last = false;

        // The encounter and PID are in the claimId separated by '-' so parse them out
        $ta = explode("-", (string) $claimId);
        $this->id = $claimId;
        $this->pid = $ta[0];
        $this->encounter = $ta[1];

        $this->partner = $partner_and_payor['partner'];

        // The payor ID is in the 'payer' part, the first character is the payer type
        $this->payor_id = substr((string) $partner_and_payor['payer'], 1);

        // The payor type comes in on the payor ID part as a single character prefix
        $payor_type_char = substr(strtoupper((string) $partner_and_payor['payer']), 0, 1);
        if ($payor_type_char == 'P') {
            $this->payor_type = self::PRIMARY;
        } elseif ($payor_type_char == 'S') {
            $this->payor_type = self::SECONDARY;
        } elseif ($payor_type_char == 'T') {
            $this->payor_type = self::TERTIARY;
        } else {
            $this->payor_type = self::UNKNOWN;
        }

        // Mock the database query for target
        $this->target = 'mocked_target';
        $this->action = $action;
    }
}
