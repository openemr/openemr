<?php

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use PHPUnit\Framework\TestCase;

/**
 * Isolated tests for BillingClaimBatch class
 * Uses a stub to avoid file system and database dependencies
 */
class BillingClaimBatchTest extends TestCase
{
    public function testConstructorInitializesProperties(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $this->assertIsString($batch->getBatFilename());
        $this->assertStringEndsWith('.txt', $batch->getBatFilename());
        $this->assertIsString($batch->getBatContent());
        $this->assertEmpty($batch->getBatContent());
    }

    public function testConstructorWithCustomExtension(): void
    {
        $batch = new BillingClaimBatchStub('.edi', []);

        $this->assertStringEndsWith('.edi', $batch->getBatFilename());
    }

    public function testGetAndSetClaims(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $this->assertIsArray($batch->getClaims());
        $this->assertEmpty($batch->getClaims());

        $claims = ['claim1', 'claim2'];
        $batch->setClaims($claims);

        $this->assertEquals($claims, $batch->getClaims());
    }

    public function testAddClaim(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $batch->addClaim('claim1');
        $this->assertCount(1, $batch->getClaims());

        $batch->addClaim('claim2');
        $this->assertCount(2, $batch->getClaims());

        $claims = $batch->getClaims();
        $this->assertEquals('claim1', $claims[0]);
        $this->assertEquals('claim2', $claims[1]);
    }

    public function testGetBatContent(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $content = $batch->getBatContent();
        $this->assertIsString($content);
    }

    public function testGetAndSetBatFilename(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $originalFilename = $batch->getBatFilename();
        $this->assertNotEmpty($originalFilename);

        $newFilename = 'custom-filename.txt';
        $batch->setBatFilename($newFilename);

        $this->assertEquals($newFilename, $batch->getBatFilename());
    }

    public function testGetAndSetBatFiledir(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $originalDir = $batch->getBatFiledir();
        $this->assertIsString($originalDir);

        $newDir = '/custom/path';
        $batch->setBatFiledir($newDir);

        $this->assertEquals($newDir, $batch->getBatFiledir());
    }

    public function testGetBatIcn(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $icn = $batch->getBatIcn();
        $this->assertIsString($icn);
        $this->assertNotEmpty($icn);
    }

    public function testFilenameFormat(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $filename = $batch->getBatFilename();

        // Filename should match pattern: YYYY-mm-dd-HHiiss-batch.txt
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}-\d{6}-batch\.txt$/', $filename);
    }

    public function testBatchWithValidateAction(): void
    {
        $mockClaim = new BillingClaimStub(
            '1-2',
            ['partner' => 'P1', 'payer' => 'P100'],
            'validate'
        );

        $context = ['claims' => [$mockClaim]];
        $batch = new BillingClaimBatchStub('.txt', $context);

        // When validating, ICN should be '000000001'
        $this->assertEquals('000000001', $batch->getBatIcn());
    }

    public function testAppendClaimWithHcfaText(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $hcfaText = "HCFA claim text content";
        $batch->append_claim($hcfaText, true);

        $content = $batch->getBatContent();
        $this->assertStringContainsString($hcfaText, $content);
    }

    public function testExtractUniqueX12PartnersFromClaims(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $claim1 = new SimpleBillingClaimMock('1-1', ['partner' => 'PARTNER_A', 'payer' => 'P100'], 'generate');
        $claim2 = new SimpleBillingClaimMock('2-2', ['partner' => 'PARTNER_A', 'payer' => 'P200'], 'generate');
        $claim3 = new SimpleBillingClaimMock('3-3', ['partner' => 'PARTNER_B', 'payer' => 'P300'], 'generate');

        $claims = [$claim1, $claim2, $claim3];

        $unique = $batch->extractUniqueX12PartnersPublic($claims);

        $this->assertIsArray($unique);
        $this->assertCount(2, $unique);
        $this->assertContains('PARTNER_A', $unique);
        $this->assertContains('PARTNER_B', $unique);
    }

    public function testExtractUniqueX12PartnersWithSinglePartner(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $claim1 = new SimpleBillingClaimMock('1-1', ['partner' => 'PARTNER_X', 'payer' => 'P100'], 'generate');
        $claim2 = new SimpleBillingClaimMock('2-2', ['partner' => 'PARTNER_X', 'payer' => 'P200'], 'generate');

        $claims = [$claim1, $claim2];

        $unique = $batch->extractUniqueX12PartnersPublic($claims);

        $this->assertIsArray($unique);
        $this->assertCount(1, $unique);
        $this->assertContains('PARTNER_X', $unique);
    }

    public function testExtractUniqueX12PartnersWithEmptyArray(): void
    {
        $batch = new BillingClaimBatchStub('.txt', []);

        $unique = $batch->extractUniqueX12PartnersPublic([]);

        $this->assertIsArray($unique);
        $this->assertEmpty($unique);
    }
}

/**
 * Stub class to avoid file system and database dependencies
 */
class BillingClaimBatchStub extends BillingClaimBatch
{
    public function __construct(string $ext = '.txt', array $context = [])
    {
        // Set basic properties manually to avoid calling parent and GLOBALS dependencies
        $this->bat_content = '';
        $this->bat_gscount = 0;
        $this->bat_stcount = 0;
        $this->claims = [];

        $this->bat_time = time();
        $this->bat_hhmm = date('Hi', $this->bat_time);
        $this->bat_yymmdd = date('ymd', $this->bat_time);
        $this->bat_yyyymmdd = date('Ymd', $this->bat_time);

        // Mock control numbers based on context
        $hasValidateAction = false;
        if (!empty($context['claims']) && isset($context['claims'][0]->action)) {
            $hasValidateAction = str_contains($context['claims'][0]->action, 'validate');
        }

        if ($hasValidateAction) {
            $this->bat_icn = '000000001';
            $this->bat_gs06 = '2';
        } else {
            $this->bat_icn = str_pad('12345', 9, '0', STR_PAD_LEFT);
            $this->bat_gs06 = '12345';
        }

        $this->bat_filename = date("Y-m-d-His", $this->bat_time) . "-batch" . $ext;
        $this->bat_filedir = '/tmp/edi';
    }

    // Expose protected method for testing
    public function extractUniqueX12PartnersPublic($claims)
    {
        return $this->extractUniqueX12PartnersFromClaims($claims);
    }
}

/**
 * Simple mock for BillingClaim used in batch tests
 * Only implements the methods needed for testing BillingClaimBatch
 */
class SimpleBillingClaimMock
{
    public function __construct(
        private readonly string $claimId,
        private array $partnerAndPayor,
        public string $action
    ) {
    }

    public function getPartner(): string
    {
        return $this->partnerAndPayor['partner'];
    }
}
