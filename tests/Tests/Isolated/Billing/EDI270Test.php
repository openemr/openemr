<?php

/**
 * Isolated tests for EDI270 X12 segment-creation static methods
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\EDI270;
use PHPUnit\Framework\TestCase;

class EDI270Test extends TestCase
{
    private string $segTer = '~';
    private string $compEleSep = ':';
    /** @var array<string, string> */
    private array $row;
    /** @var array<string, string> */
    private array $X12info;

    protected function setUp(): void
    {
        $this->row = [
            'lname' => 'Doe',
            'fname' => 'John',
            'mname' => 'Q',
            'dob' => '19800115',
            'sex' => 'Male',
            'pid' => '42',
            'pubpid' => '42',
            'policy_number' => 'POL123456',
            'provider_npi' => '1234567890',
            'provider_pin' => 'PIN999',
            'facility_name' => 'Test Clinic',
            'facility_npi' => '0987654321',
            'payer_name' => 'Test Insurance',
            'cms_id' => 'CMS001',
            'eligibility_id' => 'ELIG001',
            'date' => '20260101',
            'pc_eventDate' => '20260215',
            'subscriber_relationship' => 'self',
        ];

        $this->X12info = [
            'x12_sender_id' => 'SENDER01',
            'x12_receiver_id' => 'RECEIVER01',
            'x12_isa05' => 'ZZ',
            'x12_isa07' => 'ZZ',
            'x12_isa14' => '0',
            'x12_isa15' => 'T',
            'x12_dtp03' => 'A',
        ];
    }

    /**
     * Assert result is a string and parse an EDI segment into fields
     * @return list<string>
     */
    private function assertAndParseSegment(mixed $result): array
    {
        $this->assertIsString($result);
        return explode('*', rtrim($result, '~'));
    }

    public function testCreateSTReturnsCorrectSegment(): void
    {
        $result = EDI270::createST($this->row, $this->X12info, $this->segTer, $this->compEleSep);
        $this->assertIsString($result);

        $this->assertStringStartsWith('ST*270*', $result);
        $this->assertStringEndsWith('~', $result);
        $this->assertStringContainsString('005010X279A1', $result);
        $fields = $this->assertAndParseSegment($result);
        $this->assertCount(4, $fields);
        $this->assertSame('ST', $fields[0]);
        $this->assertSame('270', $fields[1]);
        $this->assertSame('000000003', $fields[2]);
        $this->assertSame('005010X279A1', $fields[3]);
    }

    public function testCreateBHTReturnsCorrectStructure(): void
    {
        $result = EDI270::createBHT($this->row, $this->X12info, $this->segTer, $this->compEleSep);
        $this->assertIsString($result);

        $this->assertStringStartsWith('BHT*0022*13*PROVTest600*', $result);
        $this->assertStringEndsWith('~', $result);
        $fields = $this->assertAndParseSegment($result);
        $this->assertCount(6, $fields);
        $this->assertSame('BHT', $fields[0]);
        $this->assertSame('0022', $fields[1]);
        $this->assertSame('13', $fields[2]);
        // BHT[4] is date in CCYYMMDD format — assert 8 chars
        $this->assertMatchesRegularExpression('/^\d{8}$/', trim($fields[4]));
        // BHT[5] is time in HHMM format — assert 4 chars
        $this->assertMatchesRegularExpression('/^\d{4}$/', trim($fields[5]));
    }

    public function testCreateHLCounter1IsInformationSource(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createHL($this->row, 1, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('HL', $fields[0]);
        $this->assertSame('1', $fields[1]);
        $this->assertSame('', $fields[2]); // no parent
        $this->assertSame('20', $fields[3]); // information source
        $this->assertSame('1', $fields[4]); // has subordinate
    }

    public function testCreateHLCounter2IsInformationReceiver(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createHL($this->row, 2, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('HL', $fields[0]);
        $this->assertSame('2', $fields[1]);
        $this->assertSame('1', $fields[2]); // parent is HL 1
        $this->assertSame('21', $fields[3]); // information receiver
        $this->assertSame('1', $fields[4]); // has subordinate
    }

    public function testCreateHLCounter3PlusIsSubscriber(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createHL($this->row, 3, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('HL', $fields[0]);
        $this->assertSame('3', $fields[1]);
        $this->assertSame('2', $fields[2]); // parent is HL 2
        $this->assertSame('22', $fields[3]); // subscriber
        $this->assertSame('0', $fields[4]); // no subordinate

        // Also test counter=5 to confirm >2 branch
        $fields5 = $this->assertAndParseSegment(
            EDI270::createHL($this->row, 5, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('5', $fields5[1]);
        $this->assertSame('22', $fields5[3]);
    }

    public function testCreateREFWithProviderPin(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createREF($this->row, '1P', $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('REF', $fields[0]);
        $this->assertSame('4A', $fields[1]);
        $this->assertSame('PIN999', $fields[2]);
    }

    public function testCreateREFWithPatientAccount(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createREF($this->row, 'EJ', $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('REF', $fields[0]);
        $this->assertSame('EJ', $fields[1]);
        $this->assertSame('42', $fields[2]);
    }

    public function testCreateDMGFormatsCorrectly(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createDMG($this->row, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('DMG', $fields[0]);
        $this->assertSame('D8', $fields[1]);
        $this->assertSame('19800115', $fields[2]);
        $this->assertSame('M', $fields[3]); // first char of "Male", uppercased
    }

    public function testCreateDMGWithFemaleSex(): void
    {
        $this->row['sex'] = 'female';
        $fields = $this->assertAndParseSegment(
            EDI270::createDMG($this->row, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('F', $fields[3]);
    }

    public function testCreateDTPWithQual102UsesInsEffectiveDate(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createDTP($this->row, '102', $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('DTP', $fields[0]);
        $this->assertSame('102', $fields[1]);
        $this->assertSame('D8', $fields[2]);
        $this->assertSame('20260101', $fields[3]); // row['date']
    }

    public function testCreateDTPWithNonQual102AndDtp03A(): void
    {
        $this->X12info['x12_dtp03'] = 'A';
        $this->row['pc_eventDate'] = '20260301';
        $fields = $this->assertAndParseSegment(
            EDI270::createDTP($this->row, '291', $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('291', $fields[1]);
        $this->assertSame('20260301', $fields[3]); // uses pc_eventDate
    }

    public function testCreateDTPWithDtp03EUsesDate(): void
    {
        $this->X12info['x12_dtp03'] = 'E';
        $this->row['date'] = '20260501';
        $fields = $this->assertAndParseSegment(
            EDI270::createDTP($this->row, '291', $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('20260501', $fields[3]);
    }

    public function testCreateDTPWithDtp03DefaultUsesToday(): void
    {
        $this->X12info['x12_dtp03'] = 'Z'; // unknown value triggers default
        $fields = $this->assertAndParseSegment(
            EDI270::createDTP($this->row, '291', $this->X12info, $this->segTer, $this->compEleSep)
        );
        // Default branch uses date("Ymd")
        $this->assertSame(date('Ymd'), $fields[3]);
    }

    public function testCreateEQReturnsFixedOutput(): void
    {
        $result = EDI270::createEQ($this->row, $this->X12info, $this->segTer, $this->compEleSep);
        $this->assertSame('EQ*30~', $result);
    }

    public function testCreateSEIncludesSegmentCount(): void
    {
        $fields = $this->assertAndParseSegment(
            EDI270::createSE($this->row, 13, $this->X12info, $this->segTer, $this->compEleSep)
        );
        $this->assertSame('SE', $fields[0]);
        $this->assertSame('13', $fields[1]);
        $this->assertSame('000000003', $fields[2]);
    }

    public function testCreateGEReturnsFixedOutput(): void
    {
        $result = EDI270::createGE($this->row, $this->X12info, $this->segTer, $this->compEleSep);
        $this->assertSame('GE*1*2~', $result);
    }

    public function testCreateIEAReturnsFixedOutput(): void
    {
        $result = EDI270::createIEA($this->row, $this->X12info, $this->segTer, $this->compEleSep);
        $this->assertSame('IEA*1*000000001~', $result);
    }

    public function testTranslateRelationshipSpouse(): void
    {
        $this->assertSame('01', EDI270::translateRelationship('spouse'));
    }

    public function testTranslateRelationshipChild(): void
    {
        $this->assertSame('19', EDI270::translateRelationship('child'));
    }

    public function testTranslateRelationshipDefault(): void
    {
        $this->assertSame('S', EDI270::translateRelationship('self'));
        $this->assertSame('S', EDI270::translateRelationship('other'));
        $this->assertSame('S', EDI270::translateRelationship(''));
    }
}
