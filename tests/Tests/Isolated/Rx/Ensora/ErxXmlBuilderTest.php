<?php

/**
 * Isolated tests for how ErxXmlBuilder turns rows into elements and posts the request.
 *
 * The settings and the store are stubbed, so no test touches the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use DOMElement;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\Plaintext;
use OpenEMR\Rx\Ensora\ErxGlobalsInterface;
use OpenEMR\Rx\Ensora\ErxLaunchBlock;
use OpenEMR\Rx\Ensora\ErxLaunchBlockedException;
use OpenEMR\Rx\Ensora\ErxStoreInterface;
use OpenEMR\Rx\Ensora\ErxXmlBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

class ErxXmlBuilderTest extends TestCase
{
    private const ERX_URL = 'https://erx.example.test/RxEntry.aspx';

    private bool $hadDisableTranslation = false;
    private mixed $previousDisableTranslation = null;

    protected function setUp(): void
    {
        // xl() returns its input when translation is disabled. OEGlobalsBag
        // writes through to $GLOBALS, so tearDown() restores $GLOBALS itself.
        $this->hadDisableTranslation = array_key_exists('disable_translation', $GLOBALS);
        $this->previousDisableTranslation = $GLOBALS['disable_translation'] ?? null;
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        if ($this->hadDisableTranslation) {
            $GLOBALS['disable_translation'] = $this->previousDisableTranslation;
        } else {
            unset($GLOBALS['disable_translation']);
        }
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function elementTextProvider(): array
    {
        return [
            'plain' => ['Lisinopril 10 mg', 'Lisinopril 10 mg'],
            'carriage return' => ['one' . chr(13) . chr(10) . 'two', 'one' . chr(10) . 'two'],
            'tab' => ['tw' . chr(9) . 'o', 'two'],
            'double quotes kept' => ['take "as needed"', 'take "as needed"'],
            'markup characters kept' => ['HTN & HLD <2>', 'HTN & HLD <2>'],
            'other control characters dropped' => ['a' . chr(11) . 'b' . chr(27) . 'c', 'abc'],
            'invalid UTF-8 replaced' => ['bad' . chr(255) . 'byte', "bad\u{FFFD}byte"],
            'noncharacters U+FFFE and U+FFFF dropped' => ["a\u{FFFE}b\u{FFFF}c", 'abc'],
            'other characters in the same block kept' => ["a\u{FFFD}\u{FFEE}b", "a\u{FFFD}\u{FFEE}b"],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('elementTextProvider')]
    public function testCreateElementTextKeepsOnlyTextXmlCanCarry(string $value, string $expected): void
    {
        $element = $this->builder()->createElementText('sig', $value);

        $this->assertSame('sig', $element->tagName);
        $this->assertSame($expected, $element->textContent);
    }

    public function testInvalidUtf8IsReplacedWhateverTheMbstringSubstitution(): void
    {
        $previous = mb_substitute_character();
        mb_substitute_character('none');
        try {
            $text = $this->builder()->createElementText('sig', 'bad' . chr(255) . 'byte')->textContent;
        } finally {
            mb_substitute_character($previous);
        }

        $this->assertSame("bad\u{FFFD}byte", $text);
    }

    public function testTheRedactedXmlHidesOnlyThePassword(): void
    {
        $globals = $this->createStub(ErxGlobalsInterface::class);
        $globals->method('getPartnerName')->willReturn('partner');
        $globals->method('getAccountName')->willReturn('account');
        $globals->method('getAccountPassword')->willReturn(new Plaintext('p' . chr(9) . 'ss<1'));
        $builder = new ErxXmlBuilder($globals, $this->createStub(ErxStoreInterface::class), new Client(), true);
        $builder->getNCScript()->appendChild($builder->getCredentials());

        $redacted = $builder->getRedactedXml();

        $this->assertStringContainsString('<password>pss&lt;1</password>', $builder->getXml());
        $this->assertStringContainsString('<password>[redacted]</password>', $redacted);
        $this->assertStringContainsString('<partnerName>partner</partnerName>', $redacted);
        $this->assertStringNotContainsString('pss', $redacted);
        $this->assertSame('pss<1', $builder->getSentPassword()?->bytes);
    }

    public function testCheckErrorPostsTheXmlFormEncoded(): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], 'eRx page')]));
        $stack->push(Middleware::history($history));
        $builder = new ErxXmlBuilder($this->globals(self::ERX_URL), $this->createStub(ErxStoreInterface::class), new Client(['handler' => $stack]), true);
        $xml = '<?xml version="1.0"?><a b="1&amp;2">x &lt; y + 50%20 = z</a>';

        $this->assertSame('eRx page', $builder->checkError($xml));

        $this->assertIsArray($history);
        $this->assertCount(1, $history);
        $sent = $history[0] ?? null;
        $this->assertIsArray($sent);
        $request = $sent['request'] ?? null;
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame(self::ERX_URL, (string) $request->getUri());
        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
        $this->assertSame('', $request->getHeaderLine('Cookie'));
        parse_str((string) $request->getBody(), $fields);
        $this->assertSame(['RxInput' => $xml], $fields);
    }

    public function testCheckErrorReturnsThePageOfAnErrorStatus(): void
    {
        $client = new Client(['handler' => HandlerStack::create(new MockHandler([new Response(500, [], 'error page')]))]);
        $builder = new ErxXmlBuilder($this->globals(self::ERX_URL), $this->createStub(ErxStoreInterface::class), $client, true);

        $this->assertSame('error page', $builder->checkError('<NCScript/>'));
    }

    /**
     * @return array<string, array{bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function verifySslProvider(): array
    {
        return [
            'verified' => [true],
            'not verified' => [false],
        ];
    }

    #[DataProvider('verifySslProvider')]
    public function testCheckErrorVerifiesTheCertificateAsConfigured(bool $verifySsl): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], 'eRx page')]));
        $stack->push(Middleware::history($history));
        $builder = new ErxXmlBuilder($this->globals(self::ERX_URL), $this->createStub(ErxStoreInterface::class), new Client(['handler' => $stack]), $verifySsl);

        $builder->checkError('<NCScript/>');

        $this->assertIsArray($history);
        $sent = $history[0] ?? null;
        $this->assertIsArray($sent);
        $options = $sent['options'] ?? null;
        $this->assertIsArray($options);
        $this->assertSame($verifySsl, $options['verify'] ?? null);
    }

    public function testCheckErrorRequiresThePath(): void
    {
        $builder = new ErxXmlBuilder($this->globals(''), $this->createStub(ErxStoreInterface::class), new Client(), true);

        $this->expectException(UnexpectedValueException::class);

        $builder->checkError('<NCScript/>');
    }

    public function testDestinationFollowsTheRoleUnlessAPageIsRequested(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getUserById')->willReturn(['newcrop_user_role' => 'erxmanager']);
        $builder = $this->builder($store);

        $this->assertSame('manager', $builder->getDestination(7)->textContent);
        $this->assertSame('medentry', $builder->getDestination(7, 'medentry')->textContent);
    }

    public function testAUserWithoutARoleBlocksTheLaunch(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getUserById')->willReturn(['newcrop_user_role' => null]);

        $reason = null;
        try {
            $this->builder($store)->getUserRole(7);
        } catch (ErxLaunchBlockedException $e) {
            $reason = $e->reason;
        }

        $this->assertSame(ErxLaunchBlock::UnauthorizedRole, $reason);
    }

    public function testAPrimaryFacilityWithoutATaxIdBlocksTheLaunch(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getFacilityPrimary')->willReturn([]);

        $reason = null;
        try {
            $this->builder($store)->getAccount();
        } catch (ErxLaunchBlockedException $e) {
            $reason = $e->reason;
        }

        $this->assertSame(ErxLaunchBlock::MissingTaxId, $reason);
    }

    public function testManagersSendStaffWithoutALocation(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getUserById')->willReturn([
            'newcrop_user_role' => 'erxmanager',
            'username' => 'frontdesk',
            'lname' => "O'Neil",
            'fname' => 'Pat',
            'mname' => null,
            'state_license_number' => '',
        ]);
        $store->expects($this->never())->method('getUserFacility');

        $elements = $this->builder($store)->getStaffElements(7, 'compose');

        $this->assertCount(1, $elements);
        $this->assertSame('Staff', $elements[0]->tagName);
        $this->assertSame('frontdesk', $elements[0]->getAttribute('ID'));
    }

    public function testAllergiesRecordTheIdsTheySend(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getPatientAllergiesByPatientId')->with(42)->willReturn([
            ['id' => '5', 'title1' => 'Penicillin', 'title2' => 'Severe', 'comments' => ''],
            ['id' => 9, 'title1' => 'Latex', 'title2' => 'Unassigned', 'comments' => null],
        ]);
        $builder = $this->builder($store);

        $elements = $builder->getPatientFreeformAllergy(42);

        $this->assertSame([5, 9], $builder->getSentAllergyIds());
        $this->assertSame(['allergyName' => 'Penicillin', 'allergySeverityTypeID' => 'Severe'], $this->children($elements[0]));
        $this->assertSame(['allergyName' => 'Latex'], $this->children($elements[1]));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function prescriptionQuantityProvider(): array
    {
        return [
            'number' => ['30', '500 mg 1 In tablet by mouth twice daily'],
            'number and text' => ['30x', 'x500 mg 1 In tablet by mouth twice daily'],
        ];
    }

    #[DataProvider('prescriptionQuantityProvider')]
    public function testPrescriptionsAreSentAsOutsidePrescriptions(string $quantity, string $sig): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getPrescriptionById')->with(12, 42)->willReturn([
            'prescid' => '12',
            'date_added' => '20260102',
            'docname' => 'Dr Who',
            'drug' => 'Amoxicillin',
            'quantity' => $quantity,
            'size' => '500',
            'title4' => 'mg',
            'dosage' => '1',
            'title1' => 'tablet',
            'title2' => 'by mouth',
            'title3' => 'twice daily',
            'per_refill' => '2',
        ]);
        $builder = $this->builder($store);

        $elements = $builder->getPatientPrescriptions(42, [12]);

        $this->assertSame([12], $builder->getSentPrescriptionIds());
        $this->assertSame([
            'externalId' => '12',
            'date' => '20260102',
            'doctorName' => 'Dr Who',
            'drug' => 'Amoxicillin',
            'dispenseNumber' => '30',
            'sig' => $sig,
            'refillCount' => '2',
            'prescriptionType' => 'reconcile',
        ], $this->children($elements[0]));
    }

    public function testPrescriptionsThatAreNotThePatientsAreSkipped(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getPrescriptionById')->with(12, 42)->willReturn([]);
        $builder = $this->builder($store);

        $this->assertSame([], $builder->getPatientPrescriptions(42, [12]));
        $this->assertSame([], $builder->getSentPrescriptionIds());
    }

    public function testMedicationsRecordTheIdsTheySend(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('selectMedicationsNotUploadedByPatientId')->with(42, 1, 60)->willReturn([
            ['id' => '3', 'begdate' => '2026-01-02', 'title' => 'Metformin'],
        ]);
        $builder = $this->builder($store);

        $elements = $builder->getPatientMedication(42, 1, 60);

        $this->assertSame([3], $builder->getSentMedicationIds());
        $this->assertSame('Metformin', $this->children($elements[0])['drug'] ?? null);
    }

    public function testNoPatientSendsNoPatientElements(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->expects($this->never())->method('getPatientByPatientId');

        $this->assertSame([], $this->builder($store)->getPatientElements(0, 60, [12]));
    }

    public function testDiagnosesComeFromTheProblemRows(): void
    {
        $store = $this->createMock(ErxStoreInterface::class);
        $store->method('getPatientDiagnosisByPatientId')->with(42)->willReturn([
            ['diagnosis' => 'ICD10:I10', 'begdate' => '2026-01-02 00:00:00', 'enddate' => null, 'title' => 'Hypertension', 'date' => '2026-01-03 09:00:00'],
        ]);

        $elements = $this->builder($store)->getPatientDiagnosis(42);

        $this->assertCount(1, $elements);
        $this->assertSame('I10', $this->children($elements[0])['diagnosisID'] ?? null);
    }

    private function builder(?ErxStoreInterface $store = null): ErxXmlBuilder
    {
        return new ErxXmlBuilder(
            $this->createStub(ErxGlobalsInterface::class),
            $store ?? $this->createStub(ErxStoreInterface::class),
            new Client(),
            true,
        );
    }

    private function globals(string $path): ErxGlobalsInterface
    {
        $globals = $this->createStub(ErxGlobalsInterface::class);
        $globals->method('getPath')->willReturn($path);

        return $globals;
    }

    /**
     * @return array<string, string>
     */
    private function children(DOMElement $element): array
    {
        $children = [];
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $children[$child->tagName] = $child->textContent;
            }
        }

        return $children;
    }
}
