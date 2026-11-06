<?php

/**
 * Isolated tests for how eRxXMLBuilder turns rows into elements and posts the request.
 *
 * interface/eRxXMLBuilder.php, eRxGlobals.php and eRxStore.php declare nothing
 * but their classes, so they can be required on their own. The store is
 * mocked, so no test touches the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\ERx;

use DOMElement;
use eRxGlobals;
use eRxStore;
use eRxXMLBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

class ERxXMLBuilderTest extends TestCase
{
    private const ERX_URL = 'https://erx.example.test/RxEntry.aspx';

    public static function setUpBeforeClass(): void
    {
        require_once __DIR__ . '/../../../../interface/eRxGlobals.php';
        require_once __DIR__ . '/../../../../interface/eRxStore.php';
        require_once __DIR__ . '/../../../../interface/eRxXMLBuilder.php';
    }

    protected function setUp(): void
    {
        // xl() returns its input when translation is disabled
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->remove('disable_translation');
    }

    public function testCreateElementTextCleansTheValue(): void
    {
        $value = 'take "as needed"' . chr(13) . chr(10) . chr(9) . 'with food';

        $element = $this->builder()->createElementText('sig', $value);

        $this->assertSame('sig', $element->tagName);
        $this->assertSame("take 'as needed'" . chr(10) . 'with food', $element->textContent);
    }

    public function testCheckErrorPostsTheXmlFormEncoded(): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([new Response(200, [], 'eRx page')]));
        $stack->push(Middleware::history($history));
        $builder = new eRxXMLBuilder($this->globals(['erx_newcrop_path' => self::ERX_URL]), null, new Client(['handler' => $stack]));
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
        $builder = new eRxXMLBuilder($this->globals(['erx_newcrop_path' => self::ERX_URL]), null, $client);

        $this->assertSame('error page', $builder->checkError('<NCScript/>'));
    }

    public function testCheckErrorRequiresThePath(): void
    {
        $builder = new eRxXMLBuilder($this->globals([]), null, new Client());

        $this->expectException(UnexpectedValueException::class);

        $builder->checkError('<NCScript/>');
    }

    public function testDestinationFollowsTheRoleUnlessAPageIsRequested(): void
    {
        $store = $this->createMock(eRxStore::class);
        $store->method('getUserById')->willReturn(['newcrop_user_role' => 'erxmanager']);
        $builder = $this->builder($store);

        $this->assertSame('manager', $builder->getDestination(7)->textContent);
        $this->assertSame('medentry', $builder->getDestination(7, 'medentry')->textContent);
    }

    public function testManagersSendStaffWithoutALocation(): void
    {
        $store = $this->createMock(eRxStore::class);
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
        $store = $this->createMock(eRxStore::class);
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

    public function testPrescriptionsAreSentAsOutsidePrescriptions(): void
    {
        $store = $this->createMock(eRxStore::class);
        $store->method('getPrescriptionById')->with(12)->willReturn([
            'prescid' => '12',
            'date_added' => '20260102',
            'docname' => 'Dr Who',
            'drug' => 'Amoxicillin',
            'quantity' => '30',
            'size' => '500',
            'title4' => 'mg',
            'dosage' => '1',
            'title1' => 'tablet',
            'title2' => 'by mouth',
            'title3' => 'twice daily',
            'per_refill' => '2',
        ]);
        $builder = $this->builder($store);

        $elements = $builder->getPatientPrescriptions([12]);

        $this->assertSame([12], $builder->getSentPrescriptionIds());
        $this->assertSame([
            'externalId' => '12',
            'date' => '20260102',
            'doctorName' => 'Dr Who',
            'drug' => 'Amoxicillin',
            'dispenseNumber' => '30',
            'sig' => '0500 mg 1 In tablet by mouth twice daily',
            'refillCount' => '2',
            'prescriptionType' => 'reconcile',
        ], $this->children($elements[0]));
    }

    public function testMedicationsRecordTheIdsTheySend(): void
    {
        $store = $this->createMock(eRxStore::class);
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
        $store = $this->createMock(eRxStore::class);
        $store->expects($this->never())->method('getPatientByPatientId');

        $this->assertSame([], $this->builder($store)->getPatientElements(0, 60, [12]));
    }

    public function testDiagnosesComeFromTheProblemRows(): void
    {
        $store = $this->createMock(eRxStore::class);
        $store->method('getPatientDiagnosisByPatientId')->with(42)->willReturn([
            ['diagnosis' => 'ICD10:I10', 'begdate' => '2026-01-02 00:00:00', 'enddate' => null, 'title' => 'Hypertension', 'date' => '2026-01-03 09:00:00'],
        ]);

        $elements = $this->builder($store)->getPatientDiagnosis(42);

        $this->assertCount(1, $elements);
        $this->assertSame('I10', $this->children($elements[0])['diagnosisID'] ?? null);
    }

    private function builder(?eRxStore $store = null): eRxXMLBuilder
    {
        return new eRxXMLBuilder(null, $store, new Client());
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function globals(array $configuration): eRxGlobals
    {
        return new eRxGlobals($configuration);
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
