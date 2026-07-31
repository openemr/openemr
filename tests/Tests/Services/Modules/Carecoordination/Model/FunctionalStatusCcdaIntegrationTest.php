<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\CareCoordination\Model;

use Carecoordination\Model\EncounterccdadispatchTable;
use DOMDocument;
use DOMElement;
use DOMXPath;
use OpenEMR\Cda\InternalToCdaConverter;
use OpenEMR\Tests\Fixtures\FunctionalStatusFixtureManager;
use PHPUnit\Framework\TestCase;

/**
 * End-to-end (DB-backed) test for functional-status C-CDA generation.
 *
 * Exercises the full path the reviewer's fix lives on: seeded form data ->
 * EncounterccdadispatchTable extraction SQL -> internal XML -> the PHP
 * InternalToCdaConverter. Asserts the Functional Status Organizer contains the
 * Functional Status Observation (4.67) and Self Care Activities (4.128) as two
 * separate member observations, with 4.128 not nested inside 4.67.
 */
class FunctionalStatusCcdaIntegrationTest extends TestCase
{
    private FunctionalStatusFixtureManager $fixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = new FunctionalStatusFixtureManager();
    }

    protected function tearDown(): void
    {
        $this->fixtures->removeFixtures();
        parent::tearDown();
    }

    public function testSeededFunctionalStatusRendersSeparateSelfCareObservation(): void
    {
        $patient = $this->fixtures->createTestPatient();
        $encounter = $this->fixtures->createTestEncounter($patient['pid']);
        $this->fixtures->createTestFunctionalStatus($patient, $encounter);

        // DB -> internal XML via the real extraction query.
        $dispatchTable = new EncounterccdadispatchTable();
        $fragment = $dispatchTable->getFunctionalCognitiveStatus($patient['pid']);
        self::assertStringContainsString('<functional_status>', $fragment, 'Extraction should emit a functional_status element');

        // internal XML -> CDA via the PHP converter.
        $cda = (new InternalToCdaConverter())->convert("<CCDA>$fragment</CCDA>");

        $dom = new DOMDocument();
        self::assertTrue($dom->loadXML($cda), 'Converter output must be valid XML');
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('hl7', 'urn:hl7-org:v3');

        $organizers = $xpath->query("//hl7:organizer[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.66']]");
        self::assertNotFalse($organizers, 'Organizer query must be valid');
        self::assertSame(1, $organizers->length, 'Expected exactly one Functional Status Organizer');
        $organizer = $organizers->item(0);
        self::assertInstanceOf(DOMElement::class, $organizer, 'Organizer node must be an element');

        $allObs = $xpath->query('hl7:component/hl7:observation', $organizer);
        self::assertNotFalse($allObs, 'Component observation query must be valid');
        self::assertSame(2, $allObs->length, 'Organizer must contain two separate member observations');

        $funcObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]", $organizer);
        self::assertNotFalse($funcObs, 'Functional Status Observation query must be valid');
        self::assertSame(1, $funcObs->length, 'Functional Status Observation (4.67) must be its own component');

        $selfCareObs = $xpath->query("hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']]", $organizer);
        self::assertNotFalse($selfCareObs, 'Self Care Activities query must be valid');
        self::assertSame(1, $selfCareObs->length, 'Self Care Activities (4.128) must be its own component');

        $misplaced = $xpath->query(
            "hl7:component/hl7:observation[hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.67']]"
            . "/hl7:templateId[@root='2.16.840.1.113883.10.20.22.4.128']",
            $organizer
        );
        self::assertNotFalse($misplaced, 'Misplaced-templateId query must be valid');
        self::assertSame(0, $misplaced->length, 'Self Care Activities templateId must not be nested in the Functional Status Observation');
    }
}
